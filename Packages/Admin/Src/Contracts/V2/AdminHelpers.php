<?php
namespace Packages\Admin\Contracts\V2;
use \Packages\Order\Models\V2\OrderItems;
use Illuminate\Support\Facades\DB;
trait AdminHelpers
{

    public static function getProductSales($productId, $data = [])
    {
        $query = DB::table('order_items')
            ->join('store_orders', 'store_orders.order_id', '=', 'order_items.order_id')
            ->where('order_items.item_id', $productId)
            ->where('store_orders.charge_status', 'paid') // ✅ only paid orders
            ->where('order_items.store_id', $data['store_id']);

        // Apply filters on store_orders.created_at (not order_items)
        if (!empty($data['type']) && $data['type'] == "yearly" && empty($data['date_to']) && empty($data['date_from'])) {
            $query->whereYear('store_orders.created_at', date('Y'));

        } elseif (!empty($data['type']) && $data['type'] == "monthly") {
            $query->whereMonth('store_orders.created_at', date('m'))
                ->whereYear('store_orders.created_at', date('Y'));

        } elseif (!empty($data['type']) && $data['type'] == "lastmonth") {
            $date = now();
            $month = $date->subMonth()->format('m');
            $query->whereMonth('store_orders.created_at', $month)
                ->whereYear('store_orders.created_at', date('Y'));

        } elseif (!empty($data['date_to']) && !empty($data['date_from'])) {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($data['date_from'])));
            $query->whereBetween('store_orders.created_at', [$data['date_to'], $from]);

        } elseif (!empty($data['type']) && $data['type'] == "weekly") {
            $from = now()->startOfWeek();
            $to = now()->endOfWeek();
            $query->whereBetween('store_orders.created_at', [$from, $to]);

        } elseif (!empty($data['type']) && $data['type'] == "daily") {
            $query->whereDate('store_orders.created_at', date('Y-m-d'));
        }
        // ✅ Calculate total = price * quantity
        $totalSale = $query->select(DB::raw('SUM(
                CASE
                    WHEN (order_items.sold_price + 0) > 0 THEN (order_items.sold_price + 0) * (order_items.quantity + 0)
                    ELSE (order_items.price + 0) * (order_items.quantity + 0)
                END
            ) as total'))->value('total');
        return $totalSale ?? 0;
    }

    public static function getStoreSale($storeId = "", $data = [])
    {
        $data['store_id'] = $storeId;

        // Base query: only stores that have records in order_items
        $query = DB::table('store_orders')
            ->join('order_items', 'order_items.order_id', '=', 'store_orders.order_id')->where('store_orders.charge_status','paid')
            ->select(DB::raw('SUM(store_orders.total_amount) as total_sale'));

        if (!empty($data['store_id'])) {
            $query->where('store_orders.store_id', $data['store_id']);
        }

        // Apply filters
        if (!empty($data['date_to']) && !empty($data['date_from'])) {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($data['date_from'])));
            $query->whereBetween('store_orders.created_at', [$data['date_to'], $from]);

        } elseif (!empty($data['type']) && $data['type'] == "yearly") {
            $query->whereYear('store_orders.created_at', date('Y'));

        } elseif (!empty($data['type']) && $data['type'] == "monthly") {
            $query->whereMonth('store_orders.created_at', date('m'))
                ->whereYear('store_orders.created_at', date('Y'));

        } elseif (!empty($data['type']) && $data['type'] == "lastmonth") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query->whereMonth('store_orders.created_at', $month)
                ->whereYear('store_orders.created_at', date('Y'));

        } elseif (!empty($data['type']) && $data['type'] == "weekly") {
            $date = date('Y-m-d');
            $from = date('Y-m-d', strtotime($date));
            $to   = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query->whereBetween('store_orders.created_at', [$to, $from]);

        } elseif (!empty($data['type']) && $data['type'] == "daily") {
            $query->whereDate('store_orders.created_at', date('Y-m-d'));
        }

        $totalSale = $query->value('total_sale');

        if (!empty($totalSale)) {
            return ['store_sale' => $totalSale];
        }

        return false;
    }

}