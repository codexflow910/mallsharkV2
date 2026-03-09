<?php

namespace Packages\Report\Services\V4;

use Exception;
use Illuminate\Support\Facades\DB;
use Packages\Report\Config\ReportConstants;
use Packages\Product\Models\V4\Product;
use Packages\Report\Contracts\V4\BaseApiResponse;
use Packages\Report\Contracts\V4\ReportHelpers;
use Packages\Report\Repositories\Eloquent\V4\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;


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
    public function getShippingSummary($storeId, $filter = 'all', $startDate = null, $endDate = null)
    {
        try {
            [$startDate, $endDate] = $this->reportRepository->getDateRange($filter, $startDate, $endDate);

            $delivered = $this->reportRepository->getTotalDeliveredOrders($storeId, $startDate, $endDate);
            // dump($delivered, 'deliverd');
            $pending = $this->reportRepository->getPendingShipments($storeId, $startDate, $endDate);
            // dump($pending, 'pending');
            $late = $this->reportRepository->getLateShipments($storeId, $startDate, $endDate);
            // dump($late);
            $failedReturns = $this->reportRepository->getFailedReturns($storeId, $startDate, $endDate);
            // dump($failedReturns,'failedreutrn');
            $disputed = $this->reportRepository->getDisputedShipments($storeId, $startDate, $endDate);
            // dump($disputed, 'disputed');
            $onTimeRate = $this->reportRepository->getOnTimeDeliveryRate($storeId, $startDate, $endDate);
            // dump($onTimeRate, 'onTimeRate');
            $shippingLocations = $this->reportRepository->getShippingLocations($storeId, $startDate, $endDate);
            // dump($shippingLocations, 'shippingLocations');
            $avgShippingHours = $this->reportRepository->getAverageShippingTime($storeId, $startDate, $endDate);
            // dump($avgShippingHours, 'avgShippingHours');
            $totalCost = $this->reportRepository->getTotalShippingCost($storeId, $startDate, $endDate);
            // dump($totalCost, 'totalCost');
            $carrierUsage = $this->reportRepository->getCarrierUsage($storeId, $startDate, $endDate);
            // dump($carrierUsage, 'carrierUsage');
            $shippingCarriers = $this->reportRepository->getShippingCarriers($storeId, $startDate, $endDate);
            $frequency = $this->reportRepository->getShippingFrequency($storeId, $startDate, $endDate);
            // dump($frequency, 'frequesncy');

            return [
                'summary' => [
                    'delivered' => $delivered,
                    'pending' => $pending,
                    'late' => $late,
                    'failed_returns' => $failedReturns,
                    'disputed' => $disputed,
                    'shipping_carriers' => $shippingCarriers,
                    'on_time_rate' => $onTimeRate,
                    'shipping_locations' => $shippingLocations,
                    'avg_shipping_time' => $this->reportRepository->formatHours($avgShippingHours),
                    'total_cost' => $totalCost,
                    'carrier_usage' => $carrierUsage,
                ],

                'frequency' => $frequency,
            ];
        } catch (\Throwable $th) {
            throw new Exception('Failed to generate shipping summary: ' . $th->getMessage());
        }
    }
    public function getCustomerSummary($storeId, $filter = 'all', $startDate = null, $endDate = null)
    {
        try {
            [$startDate, $endDate] = $this->reportRepository->getDateRange($filter, $startDate, $endDate);

            $totalCustomers = $this->reportRepository->getTotalCustomers($storeId, $startDate, $endDate);
            $newCustomers = $this->reportRepository->getNewCustomers($storeId, $startDate, $endDate);
            $oldCustomers = $this->reportRepository->getOldCustomers($storeId, $startDate, $endDate);
            $abandonedCarts = $this->reportRepository->getAbandonedCarts($storeId, $startDate, $endDate);
            $reviewsRatings = $this->reportRepository->getReviewsCount($storeId, $startDate, $endDate);
            $refundRequests = $this->reportRepository->getRefundRequests($storeId, $startDate, $endDate);
            $supportRequests = $this->reportRepository->getSupportRequests($storeId, $startDate, $endDate);
            $visitors = $this->reportRepository->getVisitors($storeId, $startDate, $endDate);
            $returningRate = $this->reportRepository->getReturningRate($storeId, $startDate, $endDate);
            $customerGrowth = $this->reportRepository->getCustomerGrowth($storeId, $startDate, $endDate);

            return [
                'summary' => [
                    'total_customers' => $totalCustomers,
                    'new_customers' => $newCustomers,
                    'old_customers' => $oldCustomers,
                    'abandoned_carts' => $abandonedCarts,
                    'reviews_ratings' => $reviewsRatings,
                    'refund_requests' => $refundRequests,
                    'support_requests' => $supportRequests,
                    'visitors' => $visitors,
                    'returning_rate' => $returningRate,
                ],
                'growth' => $customerGrowth,
            ];
        } catch (\Throwable $th) {
            throw new Exception('Failed to generate customer summary: ' . $th->getMessage());
        }
    }

    // ============================================================
    // FINANCE REPORT METHODS
    // ============================================================

    /**
     * Get balance summary for seller
     */
    public function getBalanceSummary($userId, $filter = 'today', $startDate = null, $endDate = null)
    {
        try {
            // Get store for user
            $store = $this->reportRepository->getStoreWithUserId($userId);

            if (!$store) {
                throw new Exception('Store not found');
            }

            // Get balance summary
            $summary = $this->reportRepository->getBalanceSummary(
                $store->store_id,
                $filter,
                $startDate,
                $endDate
            );

            return [
                'balance_summary' => [
                    'total_balance' => round($summary['total_balance'], 2),
                    'available_to_payout' => round($summary['available_to_payout'], 2),
                    'pending_amount' => round($summary['pending_amount'], 2),
                    'in_transit_to_bank' => round($summary['in_transit_to_bank'], 2),
                ],
                'last_transactions' => $summary['last_transactions'],
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get payouts list
     */
    public function getPayouts($userId, $filter = 'this_month', $startDate = null, $endDate = null, $status = null, $search = null)
    {
        try {
            // Get store for user
            $store = $this->reportRepository->getStoreWithUserId($userId);

            if (!$store) {
                throw new Exception('Store not found');
            }

            // Get payouts data
            $payoutsData = $this->reportRepository->getPayouts(
                $store->store_id,
                $filter,
                $startDate,
                $endDate,
                $status,
                $search
            );

            return [
                'payouts' => $payoutsData['payouts'],
                'summary' => [
                    'total_payout' => round($payoutsData['total_payout'], 2),
                    'count' => $payoutsData['count'],
                ],
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get fees and commissions
     */
    public function getFeesCommissions($userId, $filter = 'this_month', $startDate = null, $endDate = null, $type = null)
    {
        try {
            // Get store for user
            $store = $this->reportRepository->getStoreWithUserId($userId);

            if (!$store) {
                throw new Exception('Store not found');
            }

            // Get fees and commissions
            $feesData = $this->reportRepository->getFeesCommissions(
                $store->store_id,
                $filter,
                $startDate,
                $endDate,
                $type
            );

            return [
                'fees_commissions' => $feesData['fees_commissions'],
                'summary' => [
                    'total_charged' => round($feesData['total_charged'], 2),
                    'count' => $feesData['count'],
                ],
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get complete finance dashboard
     */
    public function getFinanceDashboard($userId, $filter = 'this_month', $startDate = null, $endDate = null)
    {
        try {
            // Get store for user
            $store = $this->reportRepository->getStoreWithUserId($userId);

            if (!$store) {
                throw new Exception('Store not found');
            }

            $storeId = $store->store_id;

            // Get all financial data
            $balanceSummary = $this->reportRepository->getBalanceSummary($storeId, $filter, $startDate, $endDate);
            $payoutsData = $this->reportRepository->getPayouts($storeId, $filter, $startDate, $endDate);
            $feesData = $this->reportRepository->getFeesCommissions($storeId, $filter, $startDate, $endDate);

            // Get summary metrics
            $earnings = $this->reportRepository->getEarningsForPeriod($storeId, $filter, $startDate, $endDate);
            $commissions = $this->reportRepository->getTotalCommissions($storeId, $filter, $startDate, $endDate);
            $fees = $this->reportRepository->getTotalFees($storeId, $filter, $startDate, $endDate);

            return [
                'overview' => [
                    'total_balance' => round($balanceSummary['total_balance'], 2),
                    'available_to_payout' => round($balanceSummary['available_to_payout'], 2),
                    'pending_amount' => round($balanceSummary['pending_amount'], 2),
                    'in_transit_to_bank' => round($balanceSummary['in_transit_to_bank'], 2),
                ],
                'period_summary' => [
                    'earnings' => round($earnings, 2),
                    'commissions' => round($commissions, 2),
                    'fees' => round($fees, 2),
                    'net_earnings' => round($earnings - $commissions - $fees, 2),
                ],
                'recent_transactions' => $balanceSummary['last_transactions'],
                'payouts_count' => $payoutsData['count'],
                'total_payout' => round($payoutsData['total_payout'], 2),
                'fees_count' => $feesData['count'],
                'total_fees' => round($feesData['total_charged'], 2),
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    // ============================================================
    // STORE PERFORMANCE REPORT METHODS
    // ============================================================

    
    public function getStorePerformance($storeId, $filter = 'all', $startDate = null, $endDate = null)
    {
        try {
            [$startDate, $endDate] = $this->reportRepository->getDateRange($filter, $startDate, $endDate);

            // Summary metrics — all from repository
            $cancellationRate = $this->reportRepository->getCancellationRate($storeId, $startDate, $endDate);
            $deliveryRate = $this->reportRepository->getPerformanceDeliveryRateData($storeId, $startDate, $endDate);
            $returnRate = $this->reportRepository->getReturnRate($storeId, $startDate, $endDate);
            $returningCustomersRate = $this->reportRepository->getReturningRateWithComparison($storeId, $startDate, $endDate);
            $conversionRate = $this->reportRepository->getConversionRateWithComparison($storeId, $startDate, $endDate);
            $totalVisitors = $this->reportRepository->getVisitors($storeId, $startDate, $endDate);
            $uniqueVisitors = $this->reportRepository->getUniqueVisitors($storeId, $startDate, $endDate);
            $abandonedCarts = $this->reportRepository->getAbandonedCarts($storeId, $startDate, $endDate);

            // Graph data — from repository
            $graphs = $this->reportRepository->getPerformanceGraphs($storeId, $startDate, $endDate);

            return [
                'summary' => [
                    'cancellation_rate' => $cancellationRate,
                    'delivered_timely' => $deliveryRate,
                    'return_rate' => $returnRate,
                    'returning_customers' => $returningCustomersRate,
                    'conversion_rate' => $conversionRate,
                    'total_visitors' => $totalVisitors,
                    'unique_visitors' => $uniqueVisitors,
                    'abandoned_carts' => $abandonedCarts,
                ],
                'graphs' => $graphs,
            ];
        } catch (\Throwable $th) {
            throw new Exception('Failed to generate store performance report: ' . $th->getMessage());
        }
    }

    /**
     * Generate PDF for store performance report.
     * Uses Blade template: resources/views/report/store-performance-pdf.blade.php
     * Reuses the same service data — does NOT re-query the database.
     *
     * @param array $performanceData Result from getStorePerformance()
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePerformancePdf($performanceData, $startDate = null, $endDate = null)
    {
        $dateRange = ($startDate && $endDate)
            ? "{$startDate} to {$endDate}"
            : 'All Time';

        return Pdf::loadView('report.store-performance-pdf', [
            'summary' => $performanceData['summary'],
            'graphs' => $performanceData['graphs'],
            'dateRange' => $dateRange,
        ])->setPaper('a4', 'portrait');
    }
}
