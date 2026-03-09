<?php

namespace Packages\Report\Repositories\Eloquent\V2;

use Packages\Home\Models\V2\Store;
use Packages\Product\Models\V2\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Packages\Order\Models\V2\StoreOrder;
use Packages\Report\Config\ReportConstants;
use Packages\Report\Contracts\V2\ReportRepositoryInterface;

class ReportRepository implements ReportRepositoryInterface
{
    
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getStoreDataWithOrders($userId, $filter = null, $startDate = null, $endDate = null)
    {
        $numberOfDays = 1;

        $query = Store::where('user_id', $userId)->with([
            'storeOrders' => function ($query) use ($filter, $startDate, $endDate, &$numberOfDays) {
                $this->applyDateFilters($query, $filter, $startDate, $endDate, $numberOfDays);
            }
        ])->first();

        return $query ? ['store' => $query, 'numberOfDays' => $numberOfDays] : false;
    }

    private function applyDateFilters($query, $filter, $startDate, $endDate, &$numberOfDays)
    {
        $dateRanges = [
            'year' => [now()->startOfYear(), now()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_30_days' => [now()->subDays(30), now()],
            'this_month' => [now()->startOfMonth(), now()],
            'last_7_days' => [now()->subDays(6), now()],
            'last_90_days' => [now()->subDays(90), now()],
            'today' => [Carbon::today(), Carbon::today()],
        ];

        if (isset($dateRanges[$filter])) {
            $query->whereBetween('created_at', [$dateRanges[$filter][0]->startOfDay(), $dateRanges[$filter][1]->endOfDay()]);
            $numberOfDays = $dateRanges[$filter][1]->diffInDays($dateRanges[$filter][0]) + 1;
        } elseif ($filter === 'custom' && $startDate && $endDate) {
            $query->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            $numberOfDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        }
    }

    function getGrossSales($orders)
    {
        return $orders->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('total_amount');
    }

    function getAverageGrossSales($orders, $numberOfDays)
    {
        $totalGrossSales = $orders->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('total_amount');
        return $totalGrossSales / $numberOfDays;
    }

    function getNetSales($orders)
    {
        return $orders->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('payable_amount');
    }

    function getAverageNetSales($orders, $numberOfDays)
    {
        $totalNetSales = $orders->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('payable_amount');
        return $totalNetSales / $numberOfDays;
    }

    function getOrdersPlaced($orders)
    {
        return $orders->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->count();
    }

    function getItemsPurchased($orders)
    {
        $totalItems = 0;

        $orders->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->each(function ($order) use (&$totalItems) {
            $order->orderItems->each(function ($item) use (&$totalItems) {
                $totalItems += $item->quantity;
            });
        });
        return $totalItems;
    }

    function getRefundedOrders($orders)
    {
        return $orders->where('order_status', 'refunded')->sum('total_amount');
    }

    function getRefundedOrdersCount($orders)
    {
        return $orders->where('order_status', 'refunded')->count();
    }

    function getChargedForShipping($orders)
    {
        return $orders->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('shipping_amount');
    }

    function getChartData($orders, $filter, $startDate = '', $endDate = '',$averageGrossSale = '',$averageNetSale = '')
    {
        $chartData = [
            'grossSales' => [],
            'netSales' => [],
            'ordersPlaced' => [],
            'itemsPurchased' => [],
            'averageGrossSales' => [],
            'averageNetSales' => [],
            'refundedOrders' => [],
            'chargedForShipping' => [],
            'worthOfCoupons' => [],
        ];

        [$startDate, $endDate] = $this->getStartAndEndDate($filter, $startDate, $endDate);

        $ordersByDate = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        });
        $currentDate = new \DateTime($startDate);
        $dateRange = $this->generateDateRange($currentDate, $endDate);

        $previousNonZeroDate = null;
        foreach ($dateRange as $date) {
            $ordersOnDate = $ordersByDate[$date] ?? collect();
            $totalGrossSales = $ordersOnDate->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('total_amount');
            $totalNetSales = $ordersOnDate->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('payable_amount');
            $totalOrdersPlaced = $ordersOnDate->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->count();
            $totalItemsPurchased = $ordersOnDate->where('charge_status', 'paid')
                ->whereNotIn('order_status', ['refunded', 'cancelled'])
                ->sum(function ($order) {
                    return $order->orderItems->sum('quantity');
                });
            $totalRefundedOrders = $ordersOnDate->where('order_status', 'refunded')->sum('total_amount');
            $totalChargedForShipping = $ordersOnDate->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('shipping_amount');
            $totalWorthOfCoupons = 0;

            $values = [
                'grossSales' => $totalGrossSales ?: 0,
                'netSales' => $totalNetSales ?: 0,
                'ordersPlaced' => $totalOrdersPlaced ?: 0,
                'itemsPurchased' => $totalItemsPurchased ?: 0,
                'refundedOrders' => $totalRefundedOrders ?: 0,
                'chargedForShipping' => $totalChargedForShipping ?: 0,
                'worthOfCoupons' => $totalWorthOfCoupons ?: 0,
            ];

            $isNonZero = array_sum($values) >= 0 ;

            if ($isNonZero || !$previousNonZeroDate || $currentDate->diff(new \DateTime($previousNonZeroDate))->days <= 15) {
                $chartData['grossSales'][] = ['date' => $date, 'value' => $values['grossSales']];
                $chartData['netSales'][] = ['date' => $date, 'value' => $values['netSales']];
                $chartData['ordersPlaced'][] = ['date' => $date, 'value' => $values['ordersPlaced']];
                $chartData['itemsPurchased'][] = ['date' => $date, 'value' => $values['itemsPurchased']];
                $chartData['averageGrossSales'][] = ['date' => $date, 'value' => $averageGrossSale ?: 0];
                $chartData['averageNetSales'][] = ['date' => $date, 'value' => $averageNetSale ?: 0];
                $chartData['refundedOrders'][] = ['date' => $date, 'value' => $values['refundedOrders']];
                $chartData['chargedForShipping'][] = ['date' => $date, 'value' => $values['chargedForShipping']];
                $chartData['worthOfCoupons'][] = ['date' => $date, 'value' => $values['worthOfCoupons']];

                if ($isNonZero) {
                    $previousNonZeroDate = $date;
                }
            }
        }
        return $chartData;
    }

    private function getStartAndEndDate($filter, $startDate, $endDate)
    {
        switch ($filter) {
            case 'last_month':
                return [(new \DateTime('first day of last month'))->format('Y-m-d'), (new \DateTime('last day of last month'))->format('Y-m-d')];
            case 'this_month':
                return [(new \DateTime('first day of this month'))->format('Y-m-d'), (new \DateTime('now'))->format('Y-m-d')];
            case 'last_7_days':
                return [(new \DateTime('-6 days'))->format('Y-m-d'), (new \DateTime('now'))->format('Y-m-d')];
            case 'today':
                return [Carbon::today()->format('Y-m-d'), Carbon::today()->format('Y-m-d')];
            case 'last_90_days':
                return [(new \DateTime('-89 days'))->format('Y-m-d'), (new \DateTime('now'))->format('Y-m-d')];
            case 'custom':
                if (empty($startDate) || empty($endDate)) {
                    throw new \Exception("Custom filter requires start and end dates");
                }
                return [$startDate, $endDate];
            default:
                throw new \Exception("Invalid filter");
        }
    }

    private function generateDateRange($currentDate, $endDate)
    {
        $dateRange = [];
        $endDate = (new \DateTime($endDate))->modify('+1 day');

        while ($currentDate < $endDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->modify('+1 day');
        }

        return $dateRange;
    }

    public function getStoreWithUserId($userId)
    {
        return Store::where('user_id', $userId)->first();
    }

    public function getAllProducts($storeId)
    {
        return Product::where('store_id', $storeId)
            ->select('title', 'product_id')
            ->get();
    }

    public function getTopSellingProducts($storeId, $filter, $startDate, $endDate)
    {
        $query = StoreOrder::where('store_orders.store_id', $storeId)
            ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
            ->join('products', 'order_items.item_id', '=', 'products.product_id')
            ->select(
                'products.title',
                'products.product_id',
                DB::raw('SUM(COALESCE(order_items.quantity, 0)) as total_quantity'),
                DB::raw('SUM(COALESCE(order_items.price, 0) * COALESCE(order_items.quantity, 0)) as total_sales')
            )
            ->groupBy('order_items.item_id', 'products.title', 'products.product_id')
            ->havingRaw('SUM(COALESCE(order_items.quantity, 0)) > 0')
            ->orderBy('total_sales', 'desc')
            ->limit(5);

        // Apply the date filter
        switch ($filter) {
            case ReportConstants::LAST_MONTH:
                $query->whereBetween('store_orders.created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ]);
                break;
            case ReportConstants::THIS_MONTH:
                $query->whereBetween('store_orders.created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()
                ]);
                break;
            case ReportConstants::LAST_7_DAYS:
                $query->whereBetween('store_orders.created_at', [
                    Carbon::now()->subDays(6),
                    Carbon::now()
                ]);
                break;
            case ReportConstants::TODAY:
                $query->whereDate('created_at', Carbon::today()->toDateString());
                break;
            case ReportConstants::CUSTOM:
                if (!empty($startDate) && !empty($endDate)) {
                    $query->whereBetween('store_orders.created_at', [
                        Carbon::parse($startDate),
                        Carbon::parse($endDate)
                    ]);
                } else {
                    throw new \Exception("Custom filter requires start and end dates");
                }
                break;
            default:
                // No additional filter applied
                break;
        }

        return $query->get();
    }

    public function getStoreOrdersQuery($userId)
    {
        $store = $this->getStoreWithUserId($userId);
        return StoreOrder::where('store_id', $store->store_id)->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled']);
    }

    public function getFilteredOrders($query, $productIds)
    {
        return $query->whereHas('orderItems', function ($query) use ($productIds) {
            $query->whereIn('item_id', (array)$productIds);
        })->with(['orderItems' => function ($query) use ($productIds) {
            $query->whereIn('item_id', (array)$productIds);
        }])->get();
    }
    public function getProductsByIds($productIds)
    {
        return Product::whereIn('product_id', (array)$productIds)->select('title')->get();
    }

    public function getStoreByUserId($userId)
    {
        return Store::where('user_id', $userId)->first();
    }

    public function getAllCategories($storeId)
    {
        return DB::table('categories')
            ->join('product_category', 'categories.id', '=', 'product_category.category_id')
            ->join('products', 'product_category.product_id', '=', 'products.product_id')
            ->where('products.store_id', $storeId)
            ->where('categories.parentCategoryID', 0)
            ->select(
                'categories.id as categoryId',
                'categories.categoryTitle as categoryName'
            )
            ->distinct()
            ->get();
    }

    private function applyFilter($query, $filter, $startDate, $endDate)
    {
        switch ($filter) {
            case ReportConstants::YEAR:
                return  $query->whereYear('created_at', date('Y'));
            case ReportConstants::LAST_MONTH:
                return $query->whereBetween('created_at', [
                    now()->subMonth()->startOfMonth()->toDateString(),
                    now()->subMonth()->endOfMonth()->toDateString()
                ]);

            case ReportConstants::THIS_MONTH:
                return $query->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));

            case ReportConstants::LAST_7_DAYS:
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                return $query->whereBetween('created_at', [
                    $startDate,
                    $endDate
                ]);

            case ReportConstants::TODAY:
                return $query->whereDate('created_at', Carbon::today()->toDateString());

            case ReportConstants::CUSTOM:
                if (empty($startDate) || empty($endDate)) {
                    throw new \Exception("Custom filter requires start and end dates");
                }
                if ($startDate === $endDate) {
                    return $query->whereDate('created_at', $startDate);
                } else {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            default:
                return $query;
                break;
        }
    }

    public function getFilteredOrdersByCategory($query, $categoryIds)
    {
        return $query->whereHas('orderItems.products.categories', function ($query) use ($categoryIds) {
            $query->whereIn('category_id', (array)$categoryIds);
        })->with(['orderItems.products.categories' => function ($query) use ($categoryIds) {
            $query->whereIn('category_id', (array)$categoryIds);
        }])->get();
    }

}