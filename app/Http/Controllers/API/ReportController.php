<?php

namespace App\Http\Controllers\API;

use App\Repositories\Reports\Reportsaccess;
use App\Services\ReportService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Store;
use Carbon\Carbon;
use function PHPUnit\Framework\isEmpty;

class ReportController extends Controller
{
    private $_reportobj;
    private $_request;
    private $reportService;

    public function __construct(Request $request, Reportsaccess $reportsaccess,ReportService $reportService) {
        $this->_request = $request;
        $this->_reportobj = $reportsaccess;
        $this->reportService = $reportService;
    }

    public function reportView()
    {
        return view('report/store-report');
    }

    private function getAuthenticatedUserId($request)
    {
        if ($request->is('api/*')) {
            return auth()->user()->user_id;
        } else {
            return Session::get('userData')['user_id'];
        }
    }

    public function getReports(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                 'filter' => 'nullable|string|in:year,last_month,this_month,last_7_days,last_90_days,custom,today',
                 'start_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d',
                 'end_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d|after_or_equal:start_date', 
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validator->errors()->first()
                ]);
            }
            $userId = $this->getAuthenticatedUserId($request);
            $filter = $request->filter;
          
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $storeData = $this->_reportobj->getStoreDataWithOrdersUserId($userId, $filter, $startDate, $endDate);
            if ($storeData) {
                $orders = $storeData['store']->storeOrders;
                $numberOfDays = $storeData['numberOfDays'];

                $grossSales = $this->_reportobj->getGrossSales($orders);
                $averageGrossSales = $this->_reportobj->getAverageGrossSales($orders, $numberOfDays);
                $netSales = $this->_reportobj->getNetSales($orders);
                $averageNetSales = $this->_reportobj->getAverageNetSales($orders, $numberOfDays);
                $ordersPlaced = $this->_reportobj->getOrdersPlaced($orders);
                $itemsPurchased = $this->_reportobj->getItemsPurchased($orders);
                $refundedOrders = $this->_reportobj->getRefundedOrders($orders);
                $refundedOrdersCount = $this->_reportobj->getRefundedOrdersCount($orders);
                $chargedForShipping = $this->_reportobj->getChargedForShipping($orders);
                $worthOfCoupons = 0;

                $chartData = $this->_reportobj->getChartData($orders,$filter, $startDate, $endDate,$averageGrossSales,$averageNetSales);
            } else {
                $grossSales = 0;
                $averageGrossSales = 0;
                $netSales = 0;
                $averageNetSales = 0;
                $ordersPlaced = 0;
                $itemsPurchased = 0;
                $refundedOrders = 0;
                $refundedOrdersCount = 0;
                $chargedForShipping = 0;
                $worthOfCoupons = 0;
            }
            return response()->json([
                'status' => true,
                'data' =>[
                    'reportData'=>
                        [
                           'cardData' => [
                                'orders' => $ordersPlaced,
                                'unitSold' => $itemsPurchased,
                                'revenueCollected' => $grossSales,
                            ],
                          'sales' => [
                               'grossSales' => $grossSales,
                               'averageGrossSales' => $averageGrossSales ?? 0,
                               'netSales' => $netSales,
                               'averageNetSales' => $averageNetSales ?? 0,
                               'ordersPlaced' => $ordersPlaced,
                               'itemsPurchased' => $itemsPurchased,
                               'refundedOrders' => $refundedOrders,
                               'refundedOrdersCount' => $refundedOrdersCount,
                               'chargedForShipping' => $chargedForShipping,
                               'worthOfCoupons' => $worthOfCoupons,
                               ],
                          'chartData' => $chartData,
                        ]
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'messagess' => $th->getMessage()
            ], 500);
        }
    }

    public function getReportsByCategory(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'filter' => 'nullable|string|in:year,last_month,last_30_days,this_month,last_7_days,custom,today,all',
            'start_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d',
            'end_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()->first()
            ]);
        }
        $userId = $this->getAuthenticatedUserId($request);
        $categoryIds = $request->get('category_ids');
        $filter = $request->get('filter');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
    
        try {
            $reportData = $this->reportService->getSalesByCategory($userId, $categoryIds, $filter, $startDate, $endDate);
            $chartData = [
                'labels' => [],
                'datasets' => []
            ];
    
            foreach ($reportData['categorySales'] as $category) {
                foreach ($category['dates'] as $date) {
                    if (!in_array($date['date'], $chartData['labels'])) {
                        $chartData['labels'][] = $date['date'];
                    }
                }
            }
    
            usort($chartData['labels'], function($a, $b) {
                return strcmp($a, $b);
            });
    
            foreach ($reportData['categorySales'] as $category) {
                $dataset = [
                    'label' => $category['category_name'],
                    'data' => [],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ];
                foreach ($chartData['labels'] as $label) {
                    $dateSales = array_filter($category['dates'], function($d) use ($label) {
                        return $d['date'] === $label;
                    });
                    $dataset['data'][] = $dateSales ? array_values($dateSales)[0]['sales'] : 0;
                }
                $chartData['datasets'][] = $dataset;
            }
    
            return response()->json([
                'status' => true,
                'data' =>[
                    'categorySales' => $reportData['categorySales'],
                    'chartData' => $chartData,
                    'cardData' => $reportData['cardData']
                ]
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Error fetching reports: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategoryList(Request $request){
        try {
            $userId =$this->getAuthenticatedUserId($request);
            $categories = $this->reportService->getCategories($userId);
            return response()->json([
                'status' => true,
                'data' =>[
                    'categorySales' => $categories,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messages' => $e->getMessage()
            ], 500);
        }
    }

    public function getSalesByProduct(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'filter' => 'nullable|string|in:year,last_month,this_month,last_7_days,custom,today',
                'start_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d',
                'end_date' => 'nullable|required_if:filter,custom|date_format:Y-m-d|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validator->errors()->first()
                ]);
            }
            
            $userId = $this->getAuthenticatedUserId($request);
            $productId = $request->get('product_id');
            $filter = $request->get('filter');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $reportData = $this->reportService->getSalesByProduct($userId, $productId, $filter, $startDate, $endDate);
            return response()->json([
                'status' => true,
                'data' =>['reportData' => $reportData]
                ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching reports: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProducts(Request $request) {
        try {
            $userId =$this->getAuthenticatedUserId($request);
            $type = $request->get('type', 'top');
            $filter = $request->get('filter');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $topProducts = $this->reportService->getProducts($userId,$type,$filter = '', $startDate = null, $endDate = null);
            return response()->json([
                'status' => true,
                'data' =>[
                'topSellers' => $topProducts
                    ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'messagess' => $e->getMessage()
            ], 500);
        }
    }

    public function getStockReport(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'filter' => 'nullable|string|in:low_in_stock,out_of_stock,most_stocked',
                'page' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validator->errors()->first()
                ]);
            }

            $userId = $this->getAuthenticatedUserId($request);
            $filter = $request->get('filter');

            $reportData = $this->reportService->getStockReport($userId, $filter);
            
            if (sizeof($reportData->items()) > 0){
               $message='Success';
            }else{
               $message='No products found';
            }

            return response()->json([
                'status' => true,
                'message' => $message,
                'data' =>[
                    'reportData' => $reportData->items(),
                    'pagination' => [
                        'current_page' => $reportData->currentPage(),
                        'last_page' => $reportData->lastPage(),
                        'per_page' => $reportData->perPage(),
                        'total' => $reportData->total(),
                        'next_page_url' => $reportData->nextPageUrl(),
                        'prev_page_url' => $reportData->previousPageUrl()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching stock report: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function loadMoreStock(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'filter' => 'nullable|string|in:low_in_stock,out_of_stock,most_stocked',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()->first()
                ]);
            }

            $userId = $this->getAuthenticatedUserId($request);
            $filter = $request->get('filter');
            $currentPage = $request->get('page', 1);
            $limit = $request->get('limit', 20);

            // Collect data from all previous pages including the current page
            $allData = collect([]);
            for ($page = 1; $page <= $currentPage; $page++) {
                $pageData = collect($this->reportService->getLoadMoreStockReport($userId, $filter, $page, $limit));
                $allData = $allData->merge($pageData);
            }

            // Map data to the desired format
            $mappedData = $allData->map(function ($product) {
                return [
                    'product_id' => $product->productId,
                    'product_name' => $product->product_name,
                    'product_slug' => $product->product_slug,
                    'units_in_stock' => $product->units_in_stock,
                    'stock_status' => $product->stock_status,
                    'parent_category_name' => $product->parent_category_name,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => sizeof($mappedData) > 0 ? 'Success' : 'No products found',
                'data' => [
                    'reportData' => $mappedData->toArray(),
                    'pagination' => [
                        'current_page' => $currentPage,
                        'per_page' => $limit,
                        'next_page' => $mappedData->count() == $currentPage * $limit ? $currentPage + 1 : null,
                        'prev_page' => $currentPage > 1 ? $currentPage - 1 : null
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching stock report: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
