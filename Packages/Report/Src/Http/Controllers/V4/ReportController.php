<?php

namespace Packages\Report\Http\Controllers\V4;


use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\ItemNotFoundException;
use Packages\Report\Contracts\V4\BaseApiResponse;
use Packages\Report\Http\Requests\V4\CategoryReportRequest;
use Packages\Report\Http\Requests\V4\ProductReportRequest;
use Packages\Report\Http\Requests\V4\ReportRequest;
use Packages\Report\Http\Requests\V4\ShippingSummaryRequest;
use Packages\Report\Http\Requests\V4\StockReportRequest;
use Packages\Report\Http\Requests\V4\FinanceReportRequest;
use Packages\Report\Http\Requests\V4\PayoutsFilterRequest;
use Packages\Report\Http\Requests\V4\FeesFilterRequest;
use Packages\Report\Http\Requests\V4\StorePerformanceRequest;
use Packages\Report\Services\V4\ReportService;
use Packages\Home\Models\V4\Store;

class ReportController extends Controller
{
    use BaseApiResponse;

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getReports(ReportRequest $request)
    {
        try {
            $userId = auth()->id();
            $filter = $request->filter;
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $reportData = $this->reportService->generateStoreReport($userId, $filter, $startDate, $endDate);
            return $this->successResponse(['reportData' => $reportData]);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    public function categoryReports(CategoryReportRequest $request)
    {
        try {
            $userId = auth()->id();
            $reportData = $this->reportService->getSalesByCategory(
                $userId,
                $request->get('category_ids'),
                $request->get('filter'),
                $request->get('start_date'),
                $request->get('end_date')
            );

            return response()->json([
                'status' => true,
                'data' => $reportData
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function categoriesList(Request $request)
    {
        try {
            $userId = auth()->id();
            $categories = $this->reportService->getCategories($userId);

            return response()->json([
                'status' => true,
                'data' => ['categorySales' => $categories]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function productReports(ProductReportRequest $request)
    {
        try {
            $userId = auth()->id();
            $productId = $request->get('product_id');
            $filter = $request->get('filter');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $reportData = $this->reportService->getSalesByProduct($userId, $productId, $filter, $startDate, $endDate);

            return response()->json([
                'status' => true,
                'data' => ['reportData' => $reportData]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function storeProducts(Request $request)
    {
        try {
            $userId = auth()->id();
            $type = $request->get('type', 'top');
            $filter = $request->get('filter');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $topProducts = $this->reportService->getProducts($userId, $type, $filter, $startDate, $endDate);

            return response()->json([
                'status' => true,
                'data' => [
                    'topSellers' => $topProducts
                ]
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function stockReports(StockReportRequest $request)
    {
        try {
                $userId = auth()->id();
                $filter = $request->get('filter');

                $reportData = $this->reportService->getStockReport($userId, $filter);

                $message = count($reportData->items()) > 0 ? 'Success' : 'No products found';

                return response()->json([
                    'status' => true,
                    'message' => $message,
                    'data' => [
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
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage ());
        }
    }
    public function getShippingSummary(ShippingSummaryRequest $request)
    {
        try {
             $userId = auth()->id();
            $store = Store::where('user_id', $userId)->first();
            if (!$store) {
                return $this->errorResponse('Store not found', 404);
            }

            $summaryData = $this->reportService->getShippingSummary(
                $store->store_id,
                $request->get('filter', 'all'),
                $request->get('start_date'),
                $request->get('end_date')
            );

            return response()->json([
                'status' => true,
                'data' => $summaryData
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getCustomerSummary(Request $request)
    {
        try {
            $userId = auth()->id();
            $store = Store::where('user_id', $userId)->first();
            if (!$store) {
                return $this->errorResponse('Store not found', 404);
            }

            $summaryData = $this->reportService->getCustomerSummary(
                $store->store_id,
                $request->get('filter', 'all'),
                $request->get('start_date'),
                $request->get('end_date')
            );

            return response()->json([
                'status' => true,
                'data' => $summaryData
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get balance summary for seller
     * GET /api/v3/report/finance/balance
     */
    public function getBalanceSummary(FinanceReportRequest $request)
    {
        try {
            $userId = auth()->id();
            $filter = $request->get('filter', 'this_month');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $data = $this->reportService->getBalanceSummary(
                $userId,
                $filter,
                $startDate,
                $endDate
            );

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get payouts list
     * GET /api/v3/report/finance/payouts
     */
    public function getPayouts(PayoutsFilterRequest $request)
    {
        try {
            $userId = auth()->id();
            $filter = $request->get('filter', 'this_month');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $status = $request->get('status');
            $search = $request->get('search');

            $data = $this->reportService->getPayouts(
                $userId,
                $filter,
                $startDate,
                $endDate,
                $status,
                $search
            );

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get fees and commissions
     * GET /api/v3/report/finance/fees-commissions
     */
    public function getFeesCommissions(FeesFilterRequest $request)
    {
        try {
            $userId = auth()->id();
            $filter = $request->get('filter', 'this_month');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $type = $request->get('type');

            $data = $this->reportService->getFeesCommissions(
                $userId,
                $filter,
                $startDate,
                $endDate,
                $type
            );

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get complete finance dashboard
     * GET /api/v3/report/finance/dashboard
     */
    public function getFinanceDashboard(FinanceReportRequest $request)
    {
        try {
            $userId = auth()->id();
            $filter = $request->get('filter', 'this_month');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $data = $this->reportService->getFinanceDashboard(
                $userId,
                $filter,
                $startDate,
                $endDate
            );

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Store Performance Report - JSON API
     * GET /api/v3/report/store-performance
     *
     * Returns summary metrics (cancellation rate, delivery rate, return rate,
     * returning customers, conversion rate, visitors, abandoned carts)
     * and graph data (orders, revenue, cancellation, return trends).
     */
    public function getStorePerformance(StorePerformanceRequest $request)
    {
        try {
            $userId = auth()->id();
            $store = Store::where('user_id', $userId)->first();

            if (!$store) {
                return $this->errorResponse('Store not found', 404);
            }

            $data = $this->reportService->getStorePerformance(
                $store->store_id,
                $request->get('filter', 'all'),
                $request->get('start_date'),
                $request->get('end_date')
            );

            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Store Performance Report - PDF Download
     * GET /api/v3/report/store-performance/pdf
     *
     * Uses the same service data as the JSON endpoint.
     * Does NOT re-query the database — reuses getStorePerformance() result.
     */
    public function downloadStorePerformancePdf(StorePerformanceRequest $request)
    {
        try {
            $userId = auth()->id();
            $store = Store::where('user_id', $userId)->first();

            if (!$store) {
                return $this->errorResponse('Store not found', 404);
            }

            // Get performance data (same as JSON endpoint)
            $data = $this->reportService->getStorePerformance(
                $store->store_id,
                $request->get('filter', 'all'),
                $request->get('start_date'),
                $request->get('end_date')
            );

            // Generate PDF from the same data — no extra DB queries
            $pdf = $this->reportService->generatePerformancePdf(
                $data,
                $request->get('start_date'),
                $request->get('end_date')
            );

            return $pdf->download('store-performance-report.pdf');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
