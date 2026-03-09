<?php

namespace App\Repositories\Reports;

use App\BaseAccess;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Log;

class Reportsaccess extends BaseAccess
{
    function getStoreWithUserId($userId)
    {
        $query = Store::where('user_id', $userId)->first();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    function getStoreDataWithOrdersUserId($userId, $filter = null, $startDate = null, $endDate = null)
    {
        $numberOfDays = 1;
        $query = Store::where('user_id', $userId)->with([
            'storeOrders' => function ($query) use ($filter, $startDate, $endDate, &$numberOfDays) {
                if ($filter == 'year') {
                    // Get all orders for the current year
                    $query->whereBetween('created_at', [
                        now()->startOfYear()->startOfDay(),
                        now()->endOfDay()
                    ]);
                    $numberOfDays = now()->dayOfYear;
                
                } elseif ($filter == 'last_month') {
                    // Get all orders for the last month (full days)
                    $query->whereBetween('created_at', [
                        now()->subMonth()->startOfMonth()->startOfDay(),
                        now()->subMonth()->endOfMonth()->endOfDay()
                    ]);
                    $numberOfDays = now()->subMonth()->daysInMonth;
                
                } elseif ($filter == 'last_30_days') {
                    // Get all orders for the last 30 days (full days)
                    $query->whereBetween('created_at', [
                        now()->subDays(30)->startOfDay(),
                        now()->endOfDay()
                    ]);
                    $numberOfDays = 30;
                
                } elseif ($filter == 'this_month') {
                    // Get all orders for the current month (start of the month to now)
                    $query->whereBetween('created_at', [
                        now()->startOfMonth()->startOfDay(),
                        now()->endOfDay()
                    ]);
                    $numberOfDays = now()->day;
                
                } elseif ($filter == 'last_7_days') {
                    // Get all orders for the last 7 days (full days)
                    $query->whereBetween('created_at', [
                        now()->subDays(6)->startOfDay(),
                        now()->endOfDay()
                    ]);
                    $numberOfDays = 7;
                
                } elseif ($filter == 'custom' && $startDate && $endDate) {
                    // Handle custom date range
                    if ($startDate === $endDate) {
                        $query->whereBetween('created_at', [
                            Carbon::parse($startDate)->startOfDay(),
                            Carbon::parse($startDate)->endOfDay()
                        ]);
                        $numberOfDays = 1;
                    } else {
                        $query->whereBetween('created_at', [
                            Carbon::parse($startDate)->startOfDay(),
                            Carbon::parse($endDate)->endOfDay()
                        ]);
                        $numberOfDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
                    }
                
                } elseif ($filter == 'last_90_days') {
                    // Get all orders for the last 90 days (full days)
                    $query->whereBetween('created_at', [
                        now()->subDays(90)->startOfDay(),
                        now()->endOfDay()
                    ]);
                    $numberOfDays = 90;
                
                } elseif ($filter == 'today') {
                    // Get all orders for today (from start to end of the current day)
                    $query->whereBetween('created_at', [
                        Carbon::today()->startOfDay(),
                        Carbon::today()->endOfDay()
                    ]);
                    $numberOfDays = 1;
                }                

            }
        ])->first();

        if (!empty($query)) {
            return [
                'store' => $query,
                'numberOfDays' => $numberOfDays
            ];
        } else {
            return false;
        }
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

        switch ($filter) {
            case 'last_month':
                $startDate = (new \DateTime('first day of last month'))->format('Y-m-d');
                $endDate = (new \DateTime('last day of last month'))->format('Y-m-d');
                break;
            case 'this_month':
                $startDate = (new \DateTime('first day of this month'))->format('Y-m-d');
                $endDate = (new \DateTime('now'))->format('Y-m-d');
                break;
            case 'last_7_days':
                $startDate = (new \DateTime('-6 days'))->format('Y-m-d');
                $endDate = (new \DateTime('now'))->format('Y-m-d');
                break;
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'last_90_days':
                $startDate = (new \DateTime('-89 days'))->format('Y-m-d');
                $endDate = (new \DateTime('now'))->format('Y-m-d');
                break;
            case 'custom':
                if (empty($startDate) || empty($endDate)) {
                    throw new \Exception("Custom filter requires start and end dates");
                }
                break;
            default:
                throw new \Exception("Invalid filter");
        }

        $ordersByDate = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        });
        $dateRange = [];
        $currentDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);
        $endDate->modify('+1 day');
        while ($currentDate < $endDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->modify('+1 day');
        }
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
//            $avgGrossSales = $ordersOnDate->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->avg('total_amount');
//            $avgNetSales = $ordersOnDate->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->avg('payable_amount');
            $totalRefundedOrders = $ordersOnDate->where('order_status', 'refunded')->sum('total_amount');
            $totalChargedForShipping = $ordersOnDate->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled'])->sum('shipping_amount');
            $totalWorthOfCoupons = 0;

            $values = [
                'grossSales' => $totalGrossSales ?: 0,
                'netSales' => $totalNetSales ?: 0,
                'ordersPlaced' => $totalOrdersPlaced ?: 0,
                'itemsPurchased' => $totalItemsPurchased ?: 0,
//                'averageGrossSales' => $avgGrossSales ?: 0,
//                'averageNetSales' => $avgNetSales ?: 0,
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

    public function getSalesByCategory($storeId, $categoryIds = [], $filter = null, $startDate = null, $endDate = null)
    {
        $query = StoreOrder::where('store_id', $storeId)
            ->whereHas('orderItems.products.categories', function ($q) use ($categoryIds) {
                if (!empty($categoryIds)) {
                    $q->whereIn('categories.id', $categoryIds);
                }
            })->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled']);
        if ($filter) {
            switch ($filter) {
                case 'year':
                    $query->whereYear('created_at', date('Y'));
                    break;
                case 'last_month':
                    $query->whereBetween('created_at', [
                        now()->subMonth()->startOfMonth()->toDateString(),
                        now()->subMonth()->endOfMonth()->toDateString()
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
                    break;
                case 'last_7_days':
                    $startDate = now()->subDays(6)->startOfDay();
                    $endDate = now()->endOfDay();
                    $query->whereBetween('created_at', [
                        $startDate,
                        $endDate
                    ]);
                    break;
                case 'today':
                    $query->whereDate('created_at', Carbon::today()->toDateString());
                    break;
                case 'custom':
                    if (empty($startDate) || empty($endDate)) {
                        throw new \Exception("Custom filter requires start and end dates");
                    }
                    if ($startDate === $endDate) {
                        $query->whereDate('created_at', $startDate);
                    } else {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                    break;
                case 'all':
                    break;
                default:
                    throw new \Exception("Invalid filter");
                    break;
            }
        }
        return $query->with([
            'orderItems.products.categories' => function ($q) use ($categoryIds) {
                if (!empty($categoryIds)) {
                    $q->whereIn('categories.id', $categoryIds);
                }
            }
        ])->get();
    }

    public function getStoreOrdersQuery($userId)
    {
        $store = $this->getStoreWithUserId($userId);
        return StoreOrder::where('store_id', $store->store_id)->where('charge_status','paid')->whereNotIn('order_status', ['refunded', 'cancelled']);
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
            case 'last_month':
                $query->whereBetween('store_orders.created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ]);
                break;
            case 'this_month':
                $query->whereBetween('store_orders.created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()
                ]);
                break;
            case 'last_7_days':
                $query->whereBetween('store_orders.created_at', [
                    Carbon::now()->subDays(6),
                    Carbon::now()
                ]);
                break;
            case 'today':
                $query->whereDate('created_at', Carbon::today()->toDateString());
                break;
            case 'custom':
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

    public function getAllProducts($storeId)
    {
        return Product::where('store_id', $storeId)
            ->select('title', 'product_id')
            ->get();
    }

    public function getProductsByIds($productIds)
    {
        $products = Product::whereIn('product_id', $productIds)->get();
        return $products;
    }

    public function getAllCategories($storeId)
    {
        $categories = DB::table('categories')
            ->join('product_category', 'categories.id', '=', 'product_category.category_id')
            ->join('products', 'product_category.product_id', '=', 'products.product_id')
            ->where('products.store_id', $storeId)
            ->where('categories.parentCategoryID', 0) // Ensure only main categories
            ->select(
                'categories.id as categoryId',
                'categories.categoryTitle as categoryName'
            )
            ->distinct()
            ->get();
        return $categories;
    }


}