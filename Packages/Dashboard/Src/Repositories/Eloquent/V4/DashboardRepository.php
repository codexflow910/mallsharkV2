<?php

namespace Packages\Dashboard\Repositories\Eloquent\V4;

use Carbon\Carbon;
use Packages\Store\Models\V4\Store;
use Packages\Product\Models\V4\Product;
use Packages\Order\Models\V4\StoreOrder;
use Packages\Discount\Models\V4\Discount;
use Packages\Dashboard\Models\V4\ContentPages;
use Illuminate\Support\ItemNotFoundException;
use Packages\Core\Models\V4\MallsharkPurchasedPackage;
use Packages\Dashboard\Contracts\V4\BaseApiResponse;
use Packages\Product\Config\ProductConstants;
use Packages\Dashboard\Contracts\V4\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    use BaseApiResponse;

    public function __construct()
    {
    }

    public function getStoreDataByStoreId($storeId, $status = "")
    {
        $query = Store::where('store_id', $storeId);
        if (!empty($status)) {
            $query->whereIn('store_status', ['1', '2']);
        }
        $package = MallsharkPurchasedPackage::where('store_id',$storeId)->where('status', 'active') ->first();
        $storeData = $query->first();
        
       if (!empty($package) && !empty($storeData)) {
            $storeData->package = $package; // Add package object to storeData
        }
        if ($status === "" && !empty($storeData)) {
            $storeData->policies_status = $storeData->policies;

            if ($storeData->policies == 'Yes' || $storeData->policies == '') {
                $policies = ContentPages::where('page_slug', 'shop-policies')->first();

                if (!empty($policies) && !empty($policies->page_content)) {
                    $storeData->policies = preg_replace('~>\s+<~', '><', $policies->page_content);
                }
            }
        }
        return $storeData;
    }

    public function getOrderCounts($storeId, $timeFilter = 'all_time')
    {
        try {
            $now = Carbon::now();
        
            $startDate = null;
            $endDate = null;

            switch ($timeFilter) {
                case 'this_week':
                    $startDate = $now->startOfWeek()->toDateString();
                    $endDate = $now->endOfWeek()->toDateString();
                    break;
                case 'last_week':
                    $startDate = $now->subWeek()->startOfWeek()->toDateString();
                    $endDate = $now->subWeek()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $startDate = $now->startOfMonth()->toDateString();
                    $endDate = $now->endOfMonth()->toDateString();
                    break;
                case 'today':
                    $startDate = today()->startOfDay()->toDateString();
                    $endDate = today()->endOfDay()->toDateString();
                    break;
                default: // all_time
                    // No date range set for all_time
                    break;
            }

            $query = StoreOrder::where('store_id', $storeId);

            // Apply date filter only if it's not 'all_time'
            if ($timeFilter !== 'all_time') {
                // Include the time part for accurate comparison: start at 00:00:00 and end at 23:59:59
                $startDateTime = Carbon::parse($startDate)->startOfDay();
                $endDateTime = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
            }

            return [
                'total_count' => $query->where('charge_status', 'paid')->count(),
                'pending_count' => (clone $query)->where('order_status', 'pending')->where('charge_status', 'paid')->count(),
                'cancelled_count' => (clone $query)->where('order_status', 'cancelled')->where('charge_status', 'paid')->count(),
                'shipped_count' => (clone $query)->where('order_status', 'shipped')->where('charge_status', 'paid')->count(),
                'completed_count' => (clone $query)->where('order_status', 'completed')->where('charge_status', 'paid')->count(),
                'refunded_count' => (clone $query)->where('order_status', 'refunded')->where('charge_status', 'paid')->count(),
                'disputed_count' => (clone $query)->where('order_status', 'dispute')->where('charge_status', 'paid')->count(),
            ];
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Orders not found');
        }
    }


    public function getProductCounts($storeId)
    {
        try{
            $deactivate = ProductConstants::STATUS_DEACTIVATED;
            $active = ProductConstants::STATUS_ACTIVE;
            $deleted = ProductConstants::STATUS_DELETED;
            $pending = ProductConstants::STATUS_PENDING;
            $rejected = ProductConstants::STATUS_REJECTED;
            $draft = ProductConstants::STATUS_DRAFT;

            $products = Product::with('variations')->where('store_id', $storeId)->where('status', '!=', $deleted)->where('status', '!=', $draft)->get();
            $outOfStockCount = 0;

            foreach ($products as $product) {
                if ($product->product_type == 'simple' && $product->status == $active) {
                    $stock = $product->current_stock ?? 0;

                    if ($stock <= 0) {
                        $outOfStockCount++;
                    }

                } elseif ($product->product_type == 'variant' && $product->status == $active) {
                    // Ensure each variation has a stock value
                    $product->variations->each(function ($variation) {
                        $variation->current_stock = $variation->current_stock ?? 0;
                    });

                    // Sum all variation stock
                    $totalStock = $product->variations->sum('current_stock');

                    if ($totalStock <= 0) {
                        $outOfStockCount++;
                    }
                }
            }

            return [
                'total_count' => Product::where('store_id', $storeId)->where('status', '!=', $deleted)->where('status', '!=', $draft)->count(),
                
                'deactivate_count' => Product::where('store_id', $storeId)
                    ->where('status', $deactivate) // Deactivate products
                    ->count(),

                'active_count' => Product::where('store_id', $storeId)
                    ->where('status', $active) // Active products
                    ->count(),

                'deleted_count' => Product::where('store_id', $storeId)
                    ->where('status', $deleted) // Deleted products
                    ->count(),

                'pending_count' => Product::where('store_id', $storeId)
                    ->where('status', $pending) // Pending products
                    ->count(),

                'rejected_count' => Product::where('store_id', $storeId)
                    ->where('status', $rejected) // Rejected products
                    ->count(),

                'draft_count' => Product::where('store_id', $storeId)
                    ->where('status', $draft) // Draft products
                    ->count(),

                'out_of_stock_count' => $outOfStockCount, // Out of stock products
            ];
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Products not found');
        }
    }

    public function getOrderPercentages($storeId, $filter)
    {
        try{
            $dateRange = $this->applyDateFilter($filter);

            $query = StoreOrder::where('store_id', $storeId);
            
            if($filter != 'all_time'){
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            }
            $totalOrders = $query->count();

            if ($totalOrders == 0) {
                return [
                    'order_complete_percentage' => 0,
                    'order_cancelled_percentage' => 0,
                    'order_refunded_percentage' => 0,
                ];
            }

            if($filter != 'all_time'){              
                $completedOrders = StoreOrder::where('store_id', $storeId)
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('order_status', 'completed')
                    ->count();

                $cancelledOrders = StoreOrder::where('store_id', $storeId)
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('order_status', 'cancelled')
                    ->count();

                $refundedOrders = StoreOrder::where('store_id', $storeId)
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('order_status', 'refunded')
                    ->count();
            }else{
                $completedOrders = StoreOrder::where('store_id', $storeId)
                    ->where('order_status', 'completed')
                    ->count();

                $cancelledOrders = StoreOrder::where('store_id', $storeId)    
                    ->where('order_status', 'cancelled')
                    ->count();

                $refundedOrders = StoreOrder::where('store_id', $storeId)
                    ->where('order_status', 'refunded') 
                    ->count();
            }

            return [
                'order_complete_percentage' => round(($completedOrders / $totalOrders) * 100, 2),
                'order_cancelled_percentage' => round(($cancelledOrders / $totalOrders) * 100, 2),
                'order_refunded_percentage' => round(($refundedOrders / $totalOrders) * 100, 2),
            ];
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Orders not found');
        }
    }


    private function applyDateFilter($filter)
    {
        if ($filter === 'today') {
            return [
                'start' => Carbon::today()->startOfDay()->toDateTimeString(),
                'end' => Carbon::today()->endOfDay()->toDateTimeString(),
            ];
        } elseif ($filter === 'this_week') {
            return [
                'start' => Carbon::now()->startOfWeek()->toDateTimeString(),
                'end' => Carbon::now()->endOfWeek()->toDateTimeString(),
            ];
        } elseif ($filter === 'last_week') {
            return [
                'start' => Carbon::now()->subWeek()->startOfWeek()->toDateTimeString(),
                'end' => Carbon::now()->subWeek()->endOfWeek()->toDateTimeString(),
            ];
        } elseif ($filter === 'this_month') {
            return [
                'start' => Carbon::now()->startOfMonth()->toDateTimeString(),
                'end' => Carbon::now()->endOfMonth()->toDateTimeString(),
            ];
        }

        // Default: All time
        return [
            'start' => null,
            'end' => null,
        ];
    }

    public function discountLive($storeId)
    {
        try{
            return Discount::where('store_id', $storeId)
                ->where('status', 'active')
                ->where('state', 'live')
                ->first();
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Discount not found');
        }
    }
    
}
