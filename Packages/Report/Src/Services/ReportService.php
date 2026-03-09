<?php

namespace Packages\Report\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Packages\Report\Config\ReportConstants;
use Packages\Product\Models\Product;
use Packages\Report\Contracts\BaseApiResponse;
use Packages\Report\Contracts\ReportHelpers;
use Packages\Report\Repositories\Eloquent\ReportRepository;


class ReportService
{
    use ReportHelpers;

    protected $reportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    public function generateStoreReport($userId, $filter, $startDate, $endDate)
    {
        $storeData = $this->reportRepository->getStoreDataWithOrders($userId, $filter, $startDate, $endDate);
        $orders = $storeData['store']->storeOrders;

        $numberOfDays = $storeData['numberOfDays'];

        return [
            'cardData' => [
                'orders' => $this->reportRepository->getOrdersPlaced($orders),
                'unitSold' => $this->reportRepository->getItemsPurchased($orders),
                'revenueCollected' => $this->reportRepository->getGrossSales($orders),
            ],
            'sales' => [
                'grossSales' => $this->reportRepository->getGrossSales($orders),
                'averageGrossSales' => $this->reportRepository->getAverageGrossSales($orders, $numberOfDays),
                'netSales' => $this->reportRepository->getNetSales($orders),
                'averageNetSales' => $this->reportRepository->getAverageNetSales($orders, $numberOfDays),
                'ordersPlaced' => $this->reportRepository->getOrdersPlaced($orders),
                'itemsPurchased' => $this->reportRepository->getItemsPurchased($orders),
                'refundedOrders' => $this->reportRepository->getRefundedOrders($orders),
                'refundedOrdersCount' => $this->reportRepository->getRefundedOrdersCount($orders),
                'chargedForShipping' => $this->reportRepository->getChargedForShipping($orders),
                'worthOfCoupons' => 0,
            ],
            'chartData' => $this->reportRepository->getChartData($orders, $filter, $startDate, $endDate),
        ];
    }

    public function getProducts($userId, $type = 'top', $filter = '', $startDate = null, $endDate = null)
    {
        $store = $this->reportRepository->getStoreWithUserId($userId);

        if (!$store) {
            return [];
        }

        if ($type === 'all') {
            return $this->reportRepository->getAllProducts($store->store_id);
        }

        return $this->reportRepository->getTopSellingProducts($store->store_id, $filter, $startDate, $endDate);
    }

    public function getSalesByProduct($userId, $productIds, $filter, $startDate = null, $endDate = null)
    {
        $query = $this->reportRepository->getStoreOrdersQuery($userId);

        // Apply Date Filters
        [$startDate, $endDate] = ReportHelpers::applyDateFilter($query, $filter, $startDate, $endDate);

        $orders = $this->reportRepository->getFilteredOrders($query, $productIds);
        $reportData = ReportHelpers::calculateOrderData($orders, $productIds);


        // Generate Chart Data
        $chartData = ReportHelpers::generateChartData($orders, $productIds, $startDate, $endDate);

        return array_merge($reportData, ['chartData' => $chartData]);
    }

    public function getCategories($userId)
    {
        $store = $this->reportRepository->getStoreByUserId($userId);
        return $this->reportRepository->getAllCategories($store->store_id);
    }

    public function getSalesByCategory($userId, $categoryIds, $filter, $startDate = null, $endDate = null)
    {
        $query = $this->reportRepository->getStoreOrdersQuery($userId);

        // Apply Date Filters
        [$startDate, $endDate] = ReportHelpers::applyDateFilter($query, $filter, $startDate, $endDate);

        $orders = $this->reportRepository->getFilteredOrdersByCategory($query, $categoryIds);
        $reportData = ReportHelpers::calculateCategoryOrderData($orders, $categoryIds);

        // Generate Chart Data
        $chartData = ReportHelpers::generateCategoryChartData($orders, $categoryIds, $startDate, $endDate);

        return array_merge($reportData, ['chartData' => $chartData]);
    }

    public function getStockReport($userId, $filter)
    {
        $store = $this->reportRepository->getStoreWithUserId($userId);
        $query = Product::where('store_id', $store->store_id)
            ->where('product_type', '!=', 'virtual')
            ->where('status', '1')
            ->select(
                'product_id',
                'products.product_id as productId',
                'products.title as product_name',
                'products.slug as product_slug',
                DB::raw("
                CASE 
                    WHEN products.product_type = 'variant' THEN (
                        SELECT SUM(current_stock) 
                        FROM product_variation 
                        WHERE product_variation.product_id = products.product_id
                    )
                    ELSE products.current_stock
                END as units_in_stock
            "),
                DB::raw("
                CASE
                    WHEN (
                        CASE 
                            WHEN products.product_type = 'variant' THEN (
                                SELECT SUM(current_stock) 
                                FROM product_variation 
                                WHERE product_variation.product_id = products.product_id
                            )
                            ELSE products.current_stock
                        END
                    ) > 0 THEN 'In Stock'
                    ELSE 'Out of Stock'
                END as stock_status
            ")
            );

        switch ($filter) {
            case ReportConstants::LOW_IN_STOCK:
                $query->havingRaw('units_in_stock > 0 AND units_in_stock <= 10')
                    ->orderBy('units_in_stock', 'asc');
                break;
            case ReportConstants::OUT_OF_STOCK:
                $query->havingRaw('units_in_stock = 0')
                    ->orderBy('products.title');
                break;
            case ReportConstants::MOST_STOCKED:
                $query->havingRaw('units_in_stock > 10')
                    ->orderBy('units_in_stock', 'desc')
                    ->limit(5);
                break;
            case null:
                $query->orderBy('products.created_at', 'ASC');
                break;
        }

        if (!$filter) {
            $query->orderBy('products.title');
        }

        $products = $query->paginate(20);
        $products->each(function ($product) {
            $product->parent_category_name = $product->parentCategories()->first()->categoryTitle;
        });

        return $products;
    }





}