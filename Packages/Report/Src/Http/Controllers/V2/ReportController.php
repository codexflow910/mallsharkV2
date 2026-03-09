<?php

namespace Packages\Report\Http\Controllers\V2;


use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\ItemNotFoundException;
use Packages\Report\Contracts\V2\BaseApiResponse;
use Packages\Report\Http\Requests\V2\CategoryReportRequest;
use Packages\Report\Http\Requests\V2\ProductReportRequest;
use Packages\Report\Http\Requests\V2\ReportRequest;
use Packages\Report\Http\Requests\V2\StockReportRequest;
use Packages\Report\Services\V2\ReportService;

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

}
