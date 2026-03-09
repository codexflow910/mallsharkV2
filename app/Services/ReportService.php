<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Reports\Reportsaccess;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{
    private $reportRepo;

    public function __construct(Reportsaccess $reportRepo)
    {
        $this->reportRepo = $reportRepo;
    }

    public function getSalesByCategory($userId, $categoryIds = [], $filter = null, $startDate = null, $endDate = null)
    {
        $store = $this->reportRepo->getStoreWithUserId($userId);
        $orders = $this->reportRepo->getSalesByCategory($store->store_id, $categoryIds, $filter, $startDate, endDate: $endDate);
        $isAllCategories = empty($categoryIds) || $filter === 'last_7_days';
        $categorySales = [];
        $allDates = [];
        $totalSales = 0;
        $totalItemsSold = 0;
        $ordersPlaced = 0;
        foreach ($orders as $order) {
            $orderDate = $order->created_at->toDateString();
            if (!in_array($orderDate, $allDates)) {
                $allDates[] = $orderDate;
            }
            $ordersPlaced++;
            foreach ($order->orderItems as $orderItem) {
                foreach ($orderItem->products as $product) {
                    foreach ($product->categories as $category) {
                        if ($isAllCategories || in_array($category->id, $categoryIds)) {
                            if (!isset($categorySales[$category->id])) {
                                $categorySales[$category->id] = [
                                    'category_id' => $category->id,
                                    'category_name' => $category->categoryTitle,
                                    'sales' => 0,
                                    'dates' => []
                                ];
                            }
                            $categorySales[$category->id]['sales'] += $orderItem->sale_id == 0 ? $orderItem->price  * $orderItem->quantity : $orderItem->sold_price  * $orderItem->quantity;
                            if (!isset($categorySales[$category->id]['dates'][$orderDate])) {
                                $categorySales[$category->id]['dates'][$orderDate] = 0;
                            }
                            $categorySales[$category->id]['dates'][$orderDate] += $orderItem->sale_id == 0 ? $orderItem->price  * $orderItem->quantity : $orderItem->sold_price  * $orderItem->quantity;

                            $totalSales += $orderItem->sale_id == 0 ? $orderItem->price  * $orderItem->quantity : $orderItem->sold_price  * $orderItem->quantity;
                            $totalItemsSold += $orderItem->quantity;

                        }
                    }
                }
            }
        }
        foreach ($categorySales as &$category) {
            $category['dates'] = array_map(function ($date, $sales) {
                return ['date' => $date, 'sales' => $sales];
            }, array_keys($category['dates']), $category['dates']);
        }

        // Ensure all dates are represented in each category
        $categorySales = array_map(function ($category) use ($allDates) {
            $existingDates = array_column($category['dates'], 'date');
            $missingDates = array_diff($allDates, $existingDates);
            foreach ($missingDates as $date) {
                $category['dates'][] = ['date' => $date, 'sales' => 0];
            }
            usort($category['dates'], function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });
            return $category;
        }, $categorySales);

        return [
            'categorySales' => array_values($categorySales),
            'cardData' => [
                'orders' => $ordersPlaced,
                'unitSold' => $totalItemsSold,
                'revenueCollected' => $totalSales,
            ]
        ];
    }

    public function getSalesByProduct($userId, $productIds = [], $filter, $startDate = null, $endDate = null)
    {
        $query = $this->reportRepo->getStoreOrdersQuery($userId);
        switch ($filter) {
            case 'year':
                $query->whereYear('created_at', date('Y'));
                break;
            case 'last_month':
                $query->whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ]);
                break;
            case 'this_month':
                $query->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'));
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
            case 'last_90_days':
                $query->whereBetween('created_at', [
                    Carbon::now()->subDays(89)->toDateString(),
                    Carbon::now()->toDateString()
                ]);
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
            default:
                throw new \Exception("Invalid filter");
        }

        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $orders = $query->whereHas('orderItems', function ($query) use ($productIds) {
            $query->whereIn('item_id', $productIds);
        })->with([
            'orderItems' => function ($query) use ($productIds) {
                $query->whereIn('item_id', $productIds);
            }
        ])->get();

        $totalSales = 0;
        $totalItemsSold = 0;

        $orders->each(function ($order) use ($productIds, &$totalSales, &$totalItemsSold) {
            $order->orderItems->whereIn('item_id', $productIds)->each(function ($item) use (&$totalSales, &$totalItemsSold) {
                $totalSales += $item->sold_price == 0 || $item->sold_price == null ? $item->price * $item->quantity : $item->sold_price * $item->quantity;
                $totalItemsSold += $item->quantity;
            });
        });

        $ordersPlaced = $orders->count();
        $products = $this->reportRepo->getProductsByIds($productIds);
        $productNames = $products->pluck('title')->toArray();
        $productName = implode("<br>", $productNames);

        // Group orders by date
        $ordersByDate = $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('Y-m-d');
        });

        // Determine the date range based on the filter
        switch ($filter) {
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now();
                break;
            case 'last_7_days':
                $startDate = Carbon::now()->subDays(6);
                $endDate = Carbon::now();
                break;
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'last_90_days':
                $startDate = Carbon::now()->subDays(89);
                $endDate = Carbon::now();
                break;
            case 'custom':
                $startDate = new Carbon($startDate);
                $endDate = new Carbon($endDate);
                break;
        }

        // Generate all dates in the range
        $dateRange = [];
        $currentDate = $startDate->copy();
        $endDate = $endDate->copy()->endOfDay(); // Ensure end date is included

        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $chartData = [];
        $previousNonZeroDate = null;

        foreach ($dateRange as $date) {
            $ordersOnDate = $ordersByDate[$date] ?? collect();
            $totalSalesByDate = 0;
            $ordersPlacedByDate = 0;
            $ordersOnDate->each(function ($order) use ($productIds, &$totalSalesByDate, &$ordersPlacedByDate) {
                $order->orderItems->whereIn('item_id', $productIds)->each(function ($item) use (&$totalSalesByDate, &$ordersPlacedByDate) {
                    $totalSalesByDate += $item->sold_price == 0 || $item->sold_price == null ? $item->price * $item->quantity : $item->sold_price * $item->quantity;
                    $ordersPlacedByDate += $item->quantity;
                });
            });

            $data = [
                'date' => $date,
                'totalSales' => $totalSalesByDate ?: 0,
                'ordersPlaced' => $ordersPlacedByDate ?: 0,
            ];

            $chartData[] = $data;

            if ($data['totalSales'] > 0 || $data['ordersPlaced'] > 0) {
                $previousNonZeroDate = $date;
            }
        }

        return [
            'totalSales' => $totalSales,
            'ordersPlaced' => $totalItemsSold,
            'productName' => $productName,
            'cardData' => [
                'orders' => $ordersPlaced,
                'unitSold' => $totalItemsSold,
                'revenueCollected' => $totalSales,
            ],
            'chartData' => $chartData,
        ];
    }

    public function getProducts($userId, $type = 'top', $filter, $startDate, $endDate)
    {
        $store = $this->reportRepo->getStoreWithUserId($userId);
        if ($type === 'all') {
            return $this->reportRepo->getAllProducts($store->store_id);
        }
        return $this->reportRepo->getTopSellingProducts($store->store_id, $filter, $startDate, $endDate);
    }

    public function getCategories($userId)
    {
        $store = $this->reportRepo->getStoreWithUserId($userId);
        return $this->reportRepo->getAllCategories($store->store_id);

    }

    public function getStockReport($userId, $filter)
    {
        $store = $this->reportRepo->getStoreWithUserId($userId);
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
            case 'low_in_stock':
                $query->havingRaw('units_in_stock > 0 AND units_in_stock <= 10')
                    ->orderBy('units_in_stock', 'asc');
                break;
            case 'out_of_stock':
                $query->havingRaw('units_in_stock = 0')
                    ->orderBy('products.title');
                break;
            case 'most_stocked':
                $query->havingRaw('units_in_stock > 0')
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

    public function getLoadMoreStockReport($userId, $filter, $page = 1, $limit = 20)
    {
        $store = $this->reportRepo->getStoreWithUserId($userId);
        $offset = ($page - 1) * $limit;

        $query = Product::where('store_id', $store->store_id)
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
            case 'low_in_stock':
                $query->havingRaw('units_in_stock > 0 AND units_in_stock <= 10')
                    ->orderBy('units_in_stock', 'asc');
                break;
            case 'out_of_stock':
                $query->havingRaw('units_in_stock = 0')
                    ->orderBy('products.title');
                break;
            case 'most_stocked':
                $query->havingRaw('units_in_stock > 0')
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

        $products = $query->offset($offset)->limit($limit)->get();

        $products->each(function ($product) {
            $product->parent_category_name = $product->parentCategories()->first()->categoryTitle ?? 'No Parent Category';
        });

        return $products;
    }


}