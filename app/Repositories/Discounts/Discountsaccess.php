<?php

namespace App\Repositories\Discounts;

use App\BaseAccess;
use App\Models\Discount;
use App\Models\DiscountApplicable;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class Discountsaccess extends BaseAccess
{
    public function fetchProductsWithVariation($store)
    {
        $query = Product::select('product_id', 'slug', 'status', 'user_id', 'title', 'price', 'quantity', 'created_at', 'description', 'short_description', 'is_feature', 'feature_Image', 'on_campaign', 'product_type', 'sales', 'current_stock')->where('products.status', '!=', '2')->where('store_id', '=', $store->store_id)
            ->orderBy('created_at', 'asc')
            ->with('variations');
        return $query;
    }

    public function getAllSubCategories($storeId, $sub)
    {
        if ($sub == 'true') {
            $operator = '!=';
        } else {
            $operator = '=';
        }
        $categories = DB::table('categories')
            ->join('product_category', 'categories.id', '=', 'product_category.category_id')
            ->join('products', 'product_category.product_id', '=', 'products.product_id')
            ->where('products.store_id', $storeId->store_id)
            ->where('products.status', '=', 1)
            ->where('categories.parentCategoryID', $operator, 0)
            ->select('categories.id as categoryId', 'categories.categoryTitle as categoryName')
            ->distinct()
            ->get();
        return $categories;
    }

    public function saveDiscount($data)
    {
        $discount = new Discount();
        $discount->store_id = $data['store_id'];
        $discount->code = $data['code'];
        $discount->title = $data['title'];
        $discount->type = $data['type'];
        $discount->platform_wide = $data['platform_wide'];
        $discount->discount_value_type = $data['discount_value_type'];
        $discount->discount_value = $data['discount_value'];
        $discount->minimum_purchase_amount = $data['minimum_purchase_amount'];
        $discount->maximum_purchase_amount = $data['maximum_purchase_amount'];
        $discount->minimum_quantity_items = $data['minimum_quantity_items'];
        $discount->maximum_quantity_items = $data['maximum_quantity_items'];
        $discount->max_usage_limit = $data['max_usage_limit'];
        $discount->one_use_per_customer = $data['one_use_per_customer'];
        $discount->start_time = $data['start_time'];
        $discount->end_time = $data['end_time'];
        $discount->status = $data['status'];
        $discount->save();

        return $discount;
    }

    public function updateDiscount($data,$id)
    {
        $discount = Discount::find($id);
        $discount->code = $data['code'];
        $discount->title = $data['title'];
        $discount->type = $data['type'];
        $discount->platform_wide = $data['platform_wide'];
        $discount->discount_value_type = $data['discount_value_type'];
        $discount->discount_value = $data['discount_value'];
        $discount->minimum_purchase_amount = $data['minimum_purchase_amount'];
        $discount->maximum_purchase_amount = $data['maximum_purchase_amount'];
        $discount->minimum_quantity_items = $data['minimum_quantity_items'];
        $discount->maximum_quantity_items = $data['maximum_quantity_items'];
        $discount->max_usage_limit = $data['max_usage_limit'];
        $discount->one_use_per_customer = $data['one_use_per_customer'];
        $discount->start_time = $data['start_time'];
        $discount->end_time = $data['end_time'];
        $discount->save();

        return $discount;
    }

    public function saveDiscountApplicable($data)
    {
        $discountApplicable = new DiscountApplicable();
        $discountApplicable->discount_id = $data['discount_id'];
        $discountApplicable->applicable_type = $data['applicable_type'];
        $discountApplicable->applicable_id = $data['applicable_id'];
        $discountApplicable->excluded = $data['excluded'];
        $discountApplicable->save();

        return $discountApplicable;
    }

    public function updateDiscountApplicable($applicablesData,$discountId)
    {
        foreach ($applicablesData as $data) {
            $discountApplicable = DiscountApplicable::where('discount_id', $discountId)
                ->where('applicable_type', $data['applicable_type'])
                ->where('applicable_id', $data['applicable_id'])
                ->first();

            if (!$discountApplicable) {
                $discountApplicable = new DiscountApplicable();
                $discountApplicable->discount_id = $discountId;
                $discountApplicable->applicable_type = $data['applicable_type'];
                $discountApplicable->applicable_id = $data['applicable_id'];
            }

            $discountApplicable->excluded = $data['excluded'];
            $discountApplicable->save();
        }
    }


}