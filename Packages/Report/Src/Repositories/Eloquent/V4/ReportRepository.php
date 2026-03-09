<?php

namespace Packages\Report\Repositories\Eloquent\V4;

use Packages\Home\Models\V4\Store;
use Packages\Product\Models\V4\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Packages\Order\Models\V4\StoreOrder;
use Packages\Report\Config\ReportConstants;
use Packages\Report\Contracts\V4\ReportRepositoryInterface;

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

    /**
     * Fill missing dates in a graph dataset so every date in range has an entry.
     *
     * @param array  $data       Array of ['date' => ..., 'count' => ...] (or 'revenue')
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $valueKey   The value field name ('count' or 'revenue')
     * @param mixed  $default    Default value for missing dates (0)
     * @return array
     */
    private function fillMissingDates(array $data, $startDate, $endDate, string $valueKey = 'count', $default = 0): array
    {
        $indexed = collect($data)->keyBy('date');
        $result = [];

        $current = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            if ($indexed->has($dateStr)) {
                $result[] = $indexed->get($dateStr);
            } else {
                $result[] = ['date' => $dateStr, $valueKey => $default];
            }
            $current->addDay();
        }

        return $result;
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

    // Shipping Summary Methods

    public function getTotalDeliveredOrders($storeId, $startDate, $endDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->where('order_status', 'shipped')
            ->where('charge_status', 'paid')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
    }

    public function getPendingShipments($storeId, $startDate, $endDate)
    {
        // `whereIn` expects an array or Countable; previously a string was passed which
        // triggered a count() error. Use a simple where clause or provide an array.
        return StoreOrder::where('store_id', $storeId)
            ->where('order_status', 'pending')
            ->where('charge_status', 'paid')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
    }

    public function getLateShipments($storeId, $startDate, $endDate)
    {
        // Extract upper-bound days from delivery_time (e.g. "3-5 Days" → 5)
        $deliveryDays = "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(order_shippings.delivery_time, '-', -1), ' ', 1) AS UNSIGNED)";
        $expectedDate = "DATE_ADD(store_orders.created_at, INTERVAL {$deliveryDays} DAY)";

        return DB::table('store_orders')
            ->join('order_shippings', 'order_shippings.order_id', '=', 'store_orders.order_id')
            ->where('store_orders.store_id', $storeId)
            ->where('store_orders.order_status', 'shipped')
            ->whereNotNull('store_orders.shipped_date')
            ->where('store_orders.charge_status', 'paid')
            ->whereNotNull('order_shippings.delivery_time')
            ->where('order_shippings.delivery_time', '!=', '')
            ->whereRaw("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(order_shippings.delivery_time, '-', -1), ' ', 1) AS UNSIGNED) > 0")
            ->whereBetween('store_orders.created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->whereRaw("NOW() > {$expectedDate}")
            ->distinct()
            ->count('store_orders.order_id');
    }

    public function getFailedReturns($storeId, $startDate, $endDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->whereIn('order_status', ['refunded', 'cancelled'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
    }

    public function getDisputedShipments($storeId, $startDate, $endDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->where('order_status', 'disputed')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
    }

    public function getOnTimeDeliveryRate($storeId, $startDate, $endDate)
    {
        $totalShipped = StoreOrder::where('store_id', $storeId)
            ->where('order_status', 'shipped')
            ->whereNotNull('shipped_date')
            ->whereBetween('shipped_date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->count();

        if ($totalShipped === 0) {
            return 0;
        }

        // Extract upper-bound days from delivery_time (e.g. "3-5 Days" → 5)
        $deliveryDays = "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(order_shippings.delivery_time, '-', -1), ' ', 1) AS UNSIGNED)";
        $expectedDate = "DATE_ADD(store_orders.created_at, INTERVAL {$deliveryDays} DAY)";

        $onTimeOrders = DB::table('store_orders')
            ->join('order_shippings', 'order_shippings.order_id', '=', 'store_orders.order_id')
            ->where('store_orders.store_id', $storeId)
            ->where('store_orders.order_status', 'shipped')
            ->whereNotNull('store_orders.shipped_date')
            ->whereNotNull('order_shippings.delivery_time')
            ->whereBetween('store_orders.shipped_date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->whereRaw("store_orders.shipped_date <= {$expectedDate}")
            ->distinct()
            ->count('store_orders.order_id');

        return round(($onTimeOrders / $totalShipped) * 100, 2);
    }
    public function getShippingLocations($storeId, $startDate, $endDate)
    {
        $cities = DB::table('store_orders')
            ->join('shipping_addresses', 'shipping_addresses.sh_id', '=', 'store_orders.shipping_address')
            ->where('store_orders.store_id', $storeId)
            ->where('store_orders.order_status', 'shipped')
            ->where('store_orders.charge_status', 'paid')
            ->whereBetween('store_orders.created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->whereNotNull('store_orders.shipping_address')
            ->whereNotNull('shipping_addresses.city')
            ->select('shipping_addresses.city', DB::raw('COUNT(DISTINCT store_orders.shipping_address) as address_count'))
            ->groupBy('shipping_addresses.city')
            ->orderByDesc('address_count')
            ->get()
            ->map(function ($row) {
                return [
                    'city' => $row->city,
                    'count' => (int) $row->address_count,
                ];
            })
            ->toArray();

        return [
            'count' => array_sum(array_column($cities, 'count')),
            'cities' => $cities,
        ];
    }

    public function getAverageShippingTime($storeId, $startDate, $endDate)
    {
        $avgHours = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereNotNull('shipped_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, shipped_date)) as avg_hours')
            ->value('avg_hours');

        return $avgHours ? (int) $avgHours : 0;
    }

    public function formatHours($hours)
    {
        if ($hours === 0) {
            return 'N/A';
        }

        $days = intval($hours / 24);
        $remainingHours = $hours % 24;

        if ($days > 0 && $remainingHours > 0) {
            return "{$days} Day " . ($days > 1 ? 's' : '') . " {$remainingHours} hrs";
        } elseif ($days > 0) {
            return "{$days} Day" . ($days > 1 ? 's' : '');
        } else {
            return "{$remainingHours} hrs";
        }
    }

    public function getTotalShippingCost($storeId, $startDate, $endDate)
    {
        return (int) DB::table('order_shippings')
            ->join('store_orders', 'order_shippings.order_id', '=', 'store_orders.order_id')
            ->where('store_orders.store_id', $storeId)
            ->where('store_orders.order_status', 'shipped')
            ->where('store_orders.charge_status', 'paid')
             ->whereNotIn('store_orders.order_status', ['refunded', 'cancelled'])
            ->whereBetween('order_shippings.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('order_shippings.shipping_amount');
    }

    public function getCarrierUsage($storeId, $startDate, $endDate)
    {
        $totalShipments = StoreOrder::where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();

        if ($totalShipments === 0) {
            return [];
        }

        $carrierData = DB::table('order_shippings')
            ->join('store_orders', 'order_shippings.order_id', '=', 'store_orders.order_id')
            ->join('shipping_carriers', 'shipping_carriers.slug', '=', 'order_shippings.service_slug')
            ->where('store_orders.store_id', $storeId)
            ->whereNotNull('order_shippings.service_slug')
            ->whereBetween('store_orders.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->select('shipping_carriers.name as carrier', 'shipping_carriers.logo', DB::raw('COUNT(*) as count'))
            ->groupBy('shipping_carriers.name', 'shipping_carriers.logo')
            ->get();

        return $carrierData->map(function ($item) use ($totalShipments) {
            return [
                'carrier' => $item->carrier,
                'percentage' => round(($item->count / $totalShipments) * 100, 2),
                'count' => $item->count,
            ];
        })->toArray();
    }

    public function getShippingCarriers($storeId, $startDate, $endDate)
    {
        return DB::table('order_shippings')
            ->join('store_orders', 'order_shippings.order_id', '=', 'store_orders.order_id')
            ->join('shipping_carriers', 'shipping_carriers.slug', '=', 'order_shippings.service_slug')
            ->where('store_orders.store_id', $storeId)
            ->whereNotNull('order_shippings.service_slug')
            ->whereBetween('store_orders.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->select('shipping_carriers.name', 'shipping_carriers.logo', 'shipping_carriers.slug')
            ->distinct()
            ->get()
            ->toArray();
    }

    public function getShippingFrequency($storeId, $startDate, $endDate)
    {
        $data = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();

        return $this->fillMissingDates($data, $startDate, $endDate);
    }

    public function getDateRange($filter, $startDate = null, $endDate = null)
    {
        $dateRanges = [
            'all' => [Carbon::parse('2000-01-01'), now()],
            'today' => [Carbon::today(), Carbon::today()],
            'last_7_days' => [now()->subDays(6), now()],
            'this_month' => [now()->startOfMonth(), now()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
        ];

        if (isset($dateRanges[$filter])) {
            return $dateRanges[$filter];
        }

        if ($startDate && $endDate) {
            return [Carbon::parse($startDate), Carbon::parse($endDate)];
        }

        return [Carbon::parse('2000-01-01'), now()];
    }

    // Customer Summary Methods

    /**
     * Total customers: distinct buyers who ordered from this store in the given time slot.
     */
    /**
     * Total customers: unique buyers who ordered within the date range.
     */
    public function getTotalCustomers($storeId, $startDate, $endDate)
{
    return DB::table('store_orders')
        ->where('store_id', $storeId)
        ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
        ->distinct()
        ->count('buyer_id');
}

public function getNewCustomers($storeId, $startDate, $endDate)
{
    // Subquery: first-ever order date for each buyer at this store
    $firstOrders = DB::table('store_orders')
        ->where('store_id', $storeId)
        ->groupBy('buyer_id')
        ->select('buyer_id', DB::raw('MIN(created_at) as first_order_date'));

    return DB::query()
        ->fromSub($firstOrders, 'first_orders')
        ->whereBetween('first_order_date', [$startDate->startOfDay(), $endDate->endOfDay()])
        ->count('buyer_id');
}

public function getOldCustomers($storeId, $startDate, $endDate)
{
    // Subquery: first-ever order date for each buyer at this store
    $firstOrders = DB::table('store_orders')
        ->where('store_id', $storeId)
        ->groupBy('buyer_id')
        ->select('buyer_id', DB::raw('MIN(created_at) as first_order_date'));

    // Buyers who ordered within the date range
    $activeBuyers = DB::table('store_orders')
        ->where('store_id', $storeId)
        ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
        ->distinct()
        ->select('buyer_id');

    return DB::query()
        ->fromSub($firstOrders, 'first_orders')
        ->joinSub($activeBuyers, 'active_buyers', function($join) {
            $join->on('first_orders.buyer_id', '=', 'active_buyers.buyer_id');
        })
        ->where('first_order_date', '<', $startDate->startOfDay())
        ->count('first_orders.buyer_id');
}

    /**
     * Abandoned carts: count of distinct users who have items in products_cart
     * for this store within the time slot (includes guest users via random_key).
     */
    public function getAbandonedCarts($storeId, $startDate, $endDate)
    {
        return DB::table('products_cart')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->selectRaw('COUNT(DISTINCT COALESCE(user_id, random_key)) as total')
            ->value('total') ?? 0;
    }

    /**
     * Reviews & ratings: count of reviews on this store's products in the time slot.
     */
    public function getReviewsCount($storeId, $startDate, $endDate)
    {
        return DB::table('reviews')
            ->join('products', 'reviews.product_id', '=', 'products.product_id')
            ->where('products.store_id', $storeId)
            ->whereBetween('reviews.created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->count();
    }

    /**
     * Refund requests: count of all refund requests on this store's orders in the time slot.
     */
    public function getRefundRequests($storeId, $startDate, $endDate)
    {
        return DB::table('product_refund')
            ->where('store_id', $storeId)
            ->where('is_refund', '!=', 'dispute')
            ->whereBetween('refund_date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->count();
    }

    /**
     * Support requests: count disputed orders on this store in the time slot.
     */
    public function getSupportRequests($storeId, $startDate, $endDate)
    {
        return DB::table('store_orders')
            ->where('store_id', $storeId)
            ->where('order_status', 'disputed')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->count();
    }

    /**
     * Visitors: count of unique visitor-days for this store.
     * Same visitor on same day = 1, same visitor on next day = 2.
     */
    public function getVisitors($storeId, $startDate, $endDate)
    {
        return DB::table('product_visitors')
            ->where('store_id', $storeId)
            ->whereBetween('visit_date', [$startDate->copy()->toDateString(), $endDate->copy()->toDateString()])
            ->distinct()
            ->count(DB::raw('visitor_id, visit_date'));
    }

    /**
     * Returning rate: percentage of customers who ordered more than once
     * from this store within the time slot.
     */
    public function getReturningRate($storeId, $startDate, $endDate)
    {
        $totalCustomers = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->distinct('buyer_id')
            ->count('buyer_id');

        if ($totalCustomers === 0) {
            return 0;
        }

        $returningCustomers = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->groupBy('buyer_id')
            ->havingRaw('COUNT(order_id) > 1')
            ->select('buyer_id')
            ->get()
            ->count();

        return round(($returningCustomers / $totalCustomers) * 100, 2);
    }

    
   public function getReturningRateWithComparison($storeId, $startDate, $endDate)
{
    $currentRate = $this->getReturningRate($storeId, $startDate, $endDate);
    // Dynamic previous period
    $daysDifference = $startDate->diffInDays($endDate) + 1;

    $prevStartDate = $startDate->copy()->subDays($daysDifference);
    $prevEndDate = $startDate->copy()->subDay();

    $previousRate = $this->getReturningRate($storeId, $prevStartDate, $prevEndDate);
    // Only difference
    $change = round($currentRate - $previousRate, 2);

    return [
        'rate' => $currentRate,
        'change_percentage' => abs($change),
        'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
    ];
}
    /**
     * Customer growth: daily new customer counts in the time slot.
     */
    public function getCustomerGrowth($storeId, $startDate, $endDate)
    {
        $data = DB::table('users')
            ->join('store_orders', 'users.user_id', '=', 'store_orders.buyer_id')
            ->where('store_orders.store_id', $storeId)
            ->whereBetween('users.created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->selectRaw('DATE(users.created_at) as date, COUNT(DISTINCT users.user_id) as count')
            ->groupByRaw('DATE(users.created_at)')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();

        return $this->fillMissingDates($data, $startDate, $endDate);
    }

    // ============================================================
    // FINANCE REPORT METHODS
    // ============================================================

    /**
     * Get date range based on filter for finance reports
     */
    public function getFinanceDateRange($filter, $startDate = null, $endDate = null)
    {
        $ranges = [
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'last_7_days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth()->startOfDay(), now()->endOfMonth()->endOfDay()],
            'last_month' => [now()->subMonth()->startOfMonth()->startOfDay(), now()->subMonth()->endOfMonth()->endOfDay()],
            'custom' => $startDate && $endDate ? 
                [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()] : 
                [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        ];

        return $ranges[$filter] ?? $ranges['today'];
    }

    /**
     * Calculate total balance for a seller store
     * Balance = Total Earnings - Payouts - Commissions/Fees
     */
    public function getTotalBalance($storeId)
    {
        $user = \App\Models\User::find($storeId);
        
        // Get total earnings from completed orders
        $totalEarnings = StoreOrder::where('store_id', $storeId)
            ->where('charge_status', 'paid')
            ->whereNotIn('order_status', ['refunded', 'cancelled'])
            ->sum('payable_amount');

        // Get total paid out from wallet transactions
        $totalPayouts = \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->whereIn('wtransac_type', ['payout', 'withdrawal'])
            ->sum('wtrans_amount');

        // Get total fees and commissions charged
        $totalFees = \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->whereIn('wtransac_type', ['commission', 'fee', 'platform_fee'])
            ->sum('wtrans_amount');

        return $totalEarnings - $totalPayouts - $totalFees;
    }

    /**
     * Get available balance for payout
     */
    public function getAvailableBalance($storeId)
    {
        $totalEarnings = StoreOrder::where('store_id', $storeId)
            ->where('charge_status', 'paid')
            ->where('payment_status', 'paid')
            ->whereNotIn('order_status', ['refunded', 'cancelled'])
            ->sum('payable_amount');

        $totalPayouts = \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->whereIn('wtransac_type', ['payout', 'withdrawal'])
            ->sum('wtrans_amount');

        return $totalEarnings - $totalPayouts;
    }

    /**
     * Get pending/processing amounts
     */
    public function getPendingBalance($storeId)
    {
        return \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->whereIn('wtransac_type', ['pending', 'processing'])
            ->sum('wtrans_amount');
    }

    /**
     * Get amounts in transit
     */
    public function getInTransitBalance($storeId)
    {
        return \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->whereIn('wtransac_type', ['in_transit', 'transfer_pending'])
            ->sum('wtrans_amount');
    }

    /**
     * Get balance summary for seller
     */
    public function getBalanceSummary($storeId, $filter = 'today', $startDate = null, $endDate = null)
    {
        [$start, $end] = $this->getFinanceDateRange($filter, $startDate, $endDate);

        // Get recent transactions for the period
        $recentTransactions = StoreOrder::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->where('charge_status', 'paid')
            ->whereNotIn('order_status', ['refunded', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => 'ORD-' . $order->order_id,
                    'type' => 'Sale',
                    'amount' => $order->payable_amount,
                    'status' => 'success',
                    'description' => 'Order ' . $order->order_id,
                    'date' => $order->created_at->format('Y-m-d'),
                    'time' => $order->created_at->format('H:i'),
                ];
            });

        return [
            'total_balance' => $this->getTotalBalance($storeId),
            'available_to_payout' => $this->getAvailableBalance($storeId),
            'pending_amount' => $this->getPendingBalance($storeId),
            'in_transit_to_bank' => $this->getInTransitBalance($storeId),
            'last_transactions' => $recentTransactions,
        ];
    }

    /**
     * Get all payouts with filters
     */
    public function getPayouts($storeId, $filter = 'this_month', $startDate = null, $endDate = null, $status = null, $search = null)
    {
        [$start, $end] = $this->getFinanceDateRange($filter, $startDate, $endDate);

        $query = \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->whereIn('wtransac_type', ['payout', 'withdrawal'])
            ->whereBetween('created_at', [$start, $end]);

        if ($status) {
            $query->where('wtransac_type', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wtrans_id', 'like', "%{$search}%")
                  ->orWhere('wtransaction_info', 'like', "%{$search}%");
            });
        }

        $payouts = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payout) {
                return [
                    'id' => $payout->wtrans_id,
                    'transaction_id' => $payout->trans_id,
                    'to_account' => 'XXXX-XXXX-' . substr($payout->wtransaction_info ?? 'N/A', -4),
                    'status' => ucfirst($payout->wtransac_type),
                    'description' => $payout->wtransaction_info,
                    'amount' => $payout->wtrans_amount,
                    'date' => $payout->created_at->format('d M Y'),
                    'time' => $payout->created_at->format('H:i'),
                ];
            });

        $totalPayout = $query->sum('wtrans_amount');

        return [
            'payouts' => $payouts,
            'total_payout' => $totalPayout,
            'count' => $payouts->count(),
        ];
    }

    /**
     * Get fees and commissions
     */
    public function getFeesCommissions($storeId, $filter = 'this_month', $startDate = null, $endDate = null, $type = null)
    {
        [$start, $end] = $this->getFinanceDateRange($filter, $startDate, $endDate);

        $query = \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->whereIn('wtransac_type', ['commission', 'fee', 'platform_fee'])
            ->whereBetween('created_at', [$start, $end]);

        if ($type) {
            $query->where('wtransac_type', $type);
        }

        $feesAndCommissions = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->wtrans_id,
                    'type' => $this->formatFeeType($item->wtransac_type),
                    'payout_date' => $item->created_at->format('d M Y'),
                    'payout_worth' => $item->wcurrent_amount ?? 0,
                    'charged' => $item->wtrans_amount,
                    'description' => $item->wtransaction_info,
                    'order_id' => $item->order_id,
                ];
            });

        $totalCharged = $query->sum('wtrans_amount');

        return [
            'fees_commissions' => $feesAndCommissions,
            'total_charged' => $totalCharged,
            'count' => $feesAndCommissions->count(),
        ];
    }

    /**
     * Format fee type for display
     */
    private function formatFeeType($type)
    {
        $types = [
            'commission' => 'Commission',
            'fee' => 'Fee',
            'platform_fee' => 'Platform Fee',
        ];

        return $types[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Get earnings for a period
     */
    public function getEarningsForPeriod($storeId, $filter = 'this_month', $startDate = null, $endDate = null)
    {
        [$start, $end] = $this->getFinanceDateRange($filter, $startDate, $endDate);

        return StoreOrder::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->where('charge_status', 'paid')
            ->whereNotIn('order_status', ['refunded', 'cancelled'])
            ->sum('payable_amount');
    }

    /**
     * Get total commissions charged
     */
    public function getTotalCommissions($storeId, $filter = 'this_month', $startDate = null, $endDate = null)
    {
        [$start, $end] = $this->getFinanceDateRange($filter, $startDate, $endDate);

        return \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->where('wtransac_type', 'commission')
            ->whereBetween('created_at', [$start, $end])
            ->sum('wtrans_amount');
    }

    /**
     * Get total platform fees
     */
    public function getTotalFees($storeId, $filter = 'this_month', $startDate = null, $endDate = null)
    {
        [$start, $end] = $this->getFinanceDateRange($filter, $startDate, $endDate);

        return \App\Models\WalletTransactions::where('wuser_id', $storeId)
            ->whereIn('wtransac_type', ['platform_fee', 'fee'])
            ->whereBetween('created_at', [$start, $end])
            ->sum('wtrans_amount');
    }

    // ============================================================
    // STORE PERFORMANCE REPORT METHODS
    // ============================================================

    /**
     * Count all orders for a store within the date range.
     */
    public function getPerformanceTotalOrders($storeId, $startDate, $endDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->count();
    }

    /**
     * Count cancelled orders for a store within the date range.
     */
    public function getPerformanceCancelledOrders($storeId, $startDate, $endDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->where('order_status', 'cancelled')
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->count();
    }

    /**
     * Count delivered orders (shipped + completed) for a store within the date range.
     */
    public function getPerformanceDeliveredOrders($storeId, $startDate, $endDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->whereIn('order_status', ['shipped', 'completed'])
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->count();
    }

    /**
     * Count orders delivered on or before the expected delivery date.
     * Uses the upper-bound from delivery_time (e.g. "3-5 Days" → 5 days).
     */
    public function getPerformanceOnTimeDeliveredOrders($storeId, $startDate, $endDate)
    {
        $deliveryDays = "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(order_shippings.delivery_time, '-', -1), ' ', 1) AS UNSIGNED)";
        $expectedDate = "DATE_ADD(store_orders.created_at, INTERVAL {$deliveryDays} DAY)";

        return DB::table('store_orders')
            ->join('order_shippings', 'order_shippings.order_id', '=', 'store_orders.order_id')
            ->where('store_orders.store_id', $storeId)
            ->whereIn('store_orders.order_status', ['shipped', 'completed'])
            ->whereNotNull('order_shippings.delivery_time')
            ->whereBetween('store_orders.created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->whereRaw("store_orders.delivery_date IS NOT NULL AND store_orders.delivery_date <= {$expectedDate}")
            ->distinct()
            ->count('store_orders.order_id');
    }

    
    public function getPerformanceReturnedOrders($storeId, $startDate, $endDate)
    {
        return StoreOrder::where('store_id', $storeId)
            ->whereIn('order_status', ['refunded', 'returned'])
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->count();
    }

    
    public function getOrdersTrendByDate($storeId, $startDate, $endDate)
    {
        $data = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();

        return $this->fillMissingDates($data, $startDate, $endDate);
    }

    
    public function getRevenueTrendByDate($storeId, $startDate, $endDate)
    {
        $data = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->where('charge_status', 'paid')
            ->whereNotIn('order_status', ['refunded', 'cancelled'])
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->selectRaw('DATE(created_at) as date, SUM(payable_amount) as revenue')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => round((float) $item->revenue, 2),
                ];
            })
            ->toArray();

        return $this->fillMissingDates($data, $startDate, $endDate, 'revenue');
    }

    
    public function getCancellationTrendByDate($storeId, $startDate, $endDate)
    {
        $data = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->where('order_status', 'cancelled')
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();

        return $this->fillMissingDates($data, $startDate, $endDate);
    }

    
    public function getReturnTrendByDate($storeId, $startDate, $endDate)
    {
        $data = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereIn('order_status', ['refunded', 'returned'])
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();

        return $this->fillMissingDates($data, $startDate, $endDate);
    }

    
    public function getConversionRateTrendByDate($storeId, $startDate, $endDate)
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        // Get daily orders by store
        $ordersPerDay = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders_count')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('date');

        // Get daily visitors from product_visitors table
        $visitorsPerDay = DB::table('product_visitors')
            ->where('store_id', $storeId)
            ->whereBetween('visit_date', [$startDate->copy()->toDateString(), $endDate->copy()->toDateString()])
            ->selectRaw('visit_date as date, COUNT(*) as visitors_count')
            ->groupBy('visit_date')
            ->pluck('visitors_count', 'date')
            ->toArray();

        // Build data from dates that have orders
        $data = $ordersPerDay->map(function ($item, $date) {
            return [
                'date' => $date,
                'count' => (int) $item->orders_count,
            ];
        })->values()->toArray();

        return $this->fillMissingDates($data, $startDate, $endDate);
    }
    

    /**
     * Unique visitors: count of distinct visitor_ids from product_visitors table.
     */
    public function getUniqueVisitors($storeId, $startDate, $endDate)
    {
        return DB::table('product_visitors')
            ->where('store_id', $storeId)
            ->whereBetween('visit_date', [$startDate->copy()->toDateString(), $endDate->copy()->toDateString()])
            ->distinct('visitor_id')
            ->count('visitor_id');
    }

    
    public function getOnTimeDeliveryTrendByDate($storeId, $startDate, $endDate)
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        // On-time delivered orders per day
        $deliveryDays = "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(order_shippings.delivery_time, '-', -1), ' ', 1) AS UNSIGNED)";
        $expectedDate = "DATE_ADD(store_orders.created_at, INTERVAL {$deliveryDays} DAY)";

        $onTimePerDay = DB::table('store_orders')
            ->join('order_shippings', 'order_shippings.order_id', '=', 'store_orders.order_id')
            ->where('store_orders.store_id', $storeId)
            ->where('store_orders.order_status', 'completed')
            ->whereNotNull('order_shippings.delivery_time')
            ->whereBetween('store_orders.created_at', [$start, $end])
            ->whereRaw("store_orders.delivery_date IS NOT NULL AND store_orders.delivery_date <= {$expectedDate}")
            ->selectRaw('DATE(store_orders.created_at) as date, COUNT(DISTINCT store_orders.order_id) as on_time_delivered')
            ->groupByRaw('DATE(store_orders.created_at)')
            ->get()
            ->keyBy('date');

        // Build data from dates that have on-time deliveries
        $data = $onTimePerDay->map(function ($item, $date) {
            return [
                'date' => $date,
                'count' => (int) $item->on_time_delivered,
            ];
        })->values()->toArray();

        return $this->fillMissingDates($data, $startDate, $endDate);
    }

    
    public function getReturningCustomersTrendByDate($storeId, $startDate, $endDate)
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        // Get daily customer counts + determine if they're repeat customers
        $dailyCustomers = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->select('buyer_id', DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->get()
            ->groupBy('date');

        $dailyRates = [];

        foreach ($dailyCustomers as $date => $customersOnDate) {
            // Count how many of today's customers are repeat customers
            // (i.e., they appear in orders before today)
            $returningCount = 0;
            foreach ($customersOnDate as $order) {
                $previousOrders = DB::table('store_orders')
                    ->where('store_id', $storeId)
                    ->where('buyer_id', $order->buyer_id)
                    ->where('created_at', '<', $date)
                    ->count();

                if ($previousOrders > 0) {
                    $returningCount++;
                }
            }

            $dailyRates[] = [
                'date' => $date,
                'count' => $returningCount,
            ];
        }

        return $this->fillMissingDates($dailyRates, $startDate, $endDate);
    }

    /**
     * Cancellation Rate: percentage of orders that were cancelled.
     * Formula: (cancelled_orders / total_orders) * 100
     *
     * @return array ['rate' => float, 'cancelled_orders' => int, 'total_orders' => int]
     */
    public function getCancellationRate($storeId, $startDate, $endDate)
    {
        $totalOrders = $this->getPerformanceTotalOrders($storeId, $startDate, $endDate);
        $cancelledOrders = $this->getPerformanceCancelledOrders($storeId, $startDate, $endDate);

        $rate = $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 1) : 0;

        return [
            'rate' => $rate,
            'cancelled_orders' => $cancelledOrders,
            'total_orders' => $totalOrders,
        ];
    }

    
   
  public function getPerformanceDeliveryRateData($storeId, $startDate, $endDate)
{
    $start = $startDate->copy()->startOfDay();
    $end = $endDate->copy()->endOfDay();

    // Total shipped orders with a shipped_date in the selected range
    $totalShippedOrders = StoreOrder::where('store_id', $storeId)
        ->where('order_status', 'shipped')
        ->where('charge_status', 'paid')
        ->whereNotNull('shipped_date')
        ->whereBetween('shipped_date', [$start, $end])
        ->count();

    // On-time shipped orders: shipped_date <= created_at + max(delivery_time) days
    $onTimeOrders = 0;
    if ($totalShippedOrders > 0) {
        // Extract upper-bound days from delivery_time (e.g. "3-5 Days" → 5, "2-4 Days" → 4)
        $deliveryDays = "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(order_shippings.delivery_time, '-', -1), ' ', 1) AS UNSIGNED)";
        $expectedDate = "DATE_ADD(store_orders.created_at, INTERVAL {$deliveryDays} DAY)";

        $onTimeOrders = DB::table('store_orders')
            ->join('order_shippings', 'order_shippings.order_id', '=', 'store_orders.order_id')
            ->where('store_orders.store_id', $storeId)
            ->where('store_orders.order_status', 'shipped')
            ->whereNotNull('store_orders.shipped_date')
            ->whereNotNull('order_shippings.delivery_time')
            ->whereBetween('store_orders.shipped_date', [$start, $end])
            ->whereRaw("store_orders.shipped_date <= {$expectedDate}")
            ->distinct()
            ->count('store_orders.order_id');
    }
    $rate = $totalShippedOrders > 0 ? round(($onTimeOrders / $totalShippedOrders) * 100, 2) : 0;

    // Dynamic previous period for comparison
    $daysDifference = $startDate->diffInDays($endDate) + 1;
    $prevStartDate = $startDate->copy()->subDays($daysDifference);
    $prevEndDate = $startDate->copy()->subDay();

    $previousRate = $this->getOnTimeDeliveryRate($storeId, $prevStartDate, $prevEndDate);

    $change = round($rate - $previousRate, 1);
    $lateOrders = $totalShippedOrders - $onTimeOrders;

    return [
        'rate' => $rate,
        'on_time_orders' => $onTimeOrders,
        // 'late_orders' => $lateOrders,
        'total_delivered_orders' => $totalShippedOrders,
        'change_percentage' => abs($change),
        'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
    ];
}

    
    public function getReturnRate($storeId, $startDate, $endDate)
    {
        $totalOrders = $this->getPerformanceTotalOrders($storeId, $startDate, $endDate);
        $returnedOrders = $this->getPerformanceReturnedOrders($storeId, $startDate, $endDate);        
        $rate = $totalOrders > 0 ? round(($returnedOrders / $totalOrders) * 100, 1) : 0;

        return [
            'rate' => $rate,
            'returned_orders' => $returnedOrders,
            'total_orders' => $totalOrders,
        ];
    }

    
    public function getConversionRate($storeId, $startDate, $endDate)
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $totalVisitors = $this->getUniqueVisitors($storeId, $startDate, $endDate);

        if ($totalVisitors === 0) {
            return 0;
        }

        // Count unique buyers (not total orders) for accurate conversion rate
        $uniqueBuyers = DB::table('store_orders')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('buyer_id')
            ->distinct()
            ->count('buyer_id');

        return round(($uniqueBuyers / $totalVisitors) * 100, 1);
    }

    
   public function getConversionRateWithComparison($storeId, $startDate, $endDate)
{
    $currentRate = $this->getConversionRate($storeId, $startDate, $endDate);
    // Dynamic previous period
    $daysDifference = $startDate->diffInDays($endDate) + 1;

    $prevStartDate = $startDate->copy()->subDays($daysDifference);
    $prevEndDate = $startDate->copy()->subDay();

    $previousRate = $this->getConversionRate($storeId, $prevStartDate, $prevEndDate);

    // Only difference
    $change = $previousRate != 0
    ? round((($currentRate - $previousRate) / $previousRate) * 100, 1)
    : ($currentRate > 0 ? 100.0 : 0.0);

    return [
    'rate'              => $currentRate,
    'change_percentage' => abs($change),
    'trend'             => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
];
}
    
    public function getPerformanceGraphs($storeId, $startDate, $endDate)
    {
        return [
            'cancellation_trend' => $this->getCancellationTrendByDate($storeId, $startDate, $endDate),
            'on_time_delivery_trend' => $this->getOnTimeDeliveryTrendByDate($storeId, $startDate, $endDate),
            'return_trend' => $this->getReturnTrendByDate($storeId, $startDate, $endDate),
            'returning_customers_trend' => $this->getReturningCustomersTrendByDate($storeId, $startDate, $endDate),
            'conversion_rate_trend' => $this->getConversionRateTrendByDate($storeId, $startDate, $endDate),
            'orders_trend' => $this->getOrdersTrendByDate($storeId, $startDate, $endDate),
            'revenue_trend' => $this->getRevenueTrendByDate($storeId, $startDate, $endDate),
        ];
    }

}