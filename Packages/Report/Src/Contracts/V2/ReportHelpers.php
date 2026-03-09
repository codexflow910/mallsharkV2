<?php

namespace Packages\Report\Contracts\V2;

use App\Models\category;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Packages\Report\Config\ReportConstants;

trait ReportHelpers
{

    public static function applyDateFilter($query, $filter, $startDate, $endDate)
    {
        switch ($filter) {
            case ReportConstants::YEAR:
                $query->whereYear('created_at', date('Y'));
                break;
            case ReportConstants::LAST_MONTH:
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                $query->whereBetween('created_at', [$startDate, $endDate]);
                break;
            case ReportConstants::THIS_MONTH:
                $query->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
                 $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case ReportConstants::LAST_7_DAYS:
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
                break;
            case ReportConstants::TODAY:
                $query->whereDate('created_at', Carbon::today()->toDateString());
                break;
            case ReportConstants::CUSTOM:
                if (empty($startDate) || empty($endDate)) {
                    throw new \Exception("Custom filter requires start and end dates");
                }
                $query->whereBetween('created_at', [$startDate, $endDate]);
                break;
            default:
                throw new \Exception("Invalid filter");
        }

        return [$startDate, $endDate];
    }

    public static function calculateOrderData($orders, $productIds)
    {
        $totalSales = 0;
        $totalItemsSold = 0;

        $orders->each(function ($order) use ($productIds, &$totalSales, &$totalItemsSold) {
            $order->orderItems->whereIn('item_id', $productIds)->each(function ($item) use (&$totalSales, &$totalItemsSold) {
                $totalSales += $item->sale_id == 0 ? $item->price * $item->quantity : $item->sold_price * $item->quantity;
                $totalItemsSold += $item->quantity;
            });
        });

        return [
            'totalSales' => $totalSales,
            'totalItemsSold' => $totalItemsSold,
            'cardData' => [
                'orders' => $orders->count(),
                'unitSold' => $totalItemsSold,
                'revenueCollected' => $totalSales,
            ]
        ];
    }

    public static function generateChartData($orders, $productIds, $startDate, $endDate)
    {
        $ordersByDate = $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('Y-m-d');
        });

        $dateRange = [];
        $currentDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate)->endOfDay();

        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        return collect($dateRange)->map(function ($date) use ($ordersByDate, $productIds) {
            $totalSales = 0;
            $itemsSold = 0;

            if (isset($ordersByDate[$date])) {
                $ordersByDate[$date]->each(function ($order) use ($productIds, &$totalSales, &$itemsSold) {
                    $order->orderItems->whereIn('item_id', $productIds)->each(function ($item) use (&$totalSales, &$itemsSold) {
                        $totalSales += $item->sale_id ==  0 ? $item->price * $item->quantity : $item->sold_price * $item->quantity;
                        $itemsSold += $item->quantity;
                    });
                });
            }

            return ['date' => $date, 'totalSales' => $totalSales, 'itemsSold' => $itemsSold];
        })->toArray();
    }

    public static function calculateCategoryOrderData($orders, $categoryIds)
    {
        $totalSales = 0;
        $totalItemsSold = 0;

        $categorySales = [];

        // Fetch all categories to ensure missing categories are included
        $categories = category::whereIn('id', (array)$categoryIds)->get();

        foreach ($categories as $category) {
            $categorySales[$category->id] = [
                'category_id' => $category->id,
                'category_name' => $category->categoryTitle,
                'sales' => 0
            ];
        }

        $orders->each(function ($order) use ($categoryIds, &$totalSales, &$totalItemsSold, &$categorySales) {
            $order->orderItems->each(function ($item) use ($categoryIds, &$totalSales, &$totalItemsSold, &$categorySales) {
                foreach ($item->products as $product) {
                    foreach ($product->categories as $category) {
                        if (in_array($category->id, $categoryIds)) {
                            $saleAmount = $item->sale_id == 0 ? $item->price * $item->quantity : $item->sold_price * $item->quantity;

                            $categorySales[$category->id]['sales'] += $saleAmount;
                            $totalSales += $saleAmount;
                            $totalItemsSold += $item->quantity;
                        }
                    }
                }
            });
        });

        return [
            'totalSales' => $totalSales,
            'totalItemsSold' => $totalItemsSold,
            'categorySales' => array_values($categorySales),
            'cardData' => [
                'orders' => $orders->count(),
                'unitSold' => $totalItemsSold,
                'revenueCollected' => $totalSales,
            ]
        ];
    }
    public static function generateCategoryChartData($orders, $categoryIds, $startDate, $endDate)
    {
        $ordersByDate = $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('Y-m-d');
        });

        $dateRange = [];
        $currentDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate)->endOfDay();

        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        return collect($dateRange)->map(function ($date) use ($ordersByDate, $categoryIds) {
            $totalSales = 0;
            $itemsSold = 0;

            if (isset($ordersByDate[$date])) {
                $ordersByDate[$date]->each(function ($order) use ($categoryIds, &$totalSales, &$itemsSold) {
                    $order->orderItems->each(function ($item) use ($categoryIds, &$totalSales, &$itemsSold) {
                        foreach ($item->products as $product) {
                            foreach ($product->categories as $category) {
                                if (in_array($category->id, $categoryIds)) {
                                    $totalSales += $item->sold_price ? $item->sold_price * $item->quantity : $item->price * $item->quantity;
                                    $itemsSold += $item->quantity;
                                }
                            }
                        }
                    });
                });
            }

            return ['date' => $date, 'totalSales' => $totalSales, 'itemsSold' => $itemsSold];
        })->toArray();
    }

}