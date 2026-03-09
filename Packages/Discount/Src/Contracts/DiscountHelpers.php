<?php
namespace Packages\Discount\Contracts;

use Packages\Core\Models\Category;
use Packages\Product\Models\V2\Product;
use Packages\Product\Models\ProductVariation;
use Packages\Discount\Models\DiscountApplicable;

trait DiscountHelpers
{

    protected function getStoreProducts($store_id)
    {
        return Product::where('store_id', $store_id)->with('variations')->get();
    }

    protected function getDiscountedProducts($store_id, $discountId)
    {
        return Product::where('store_id', $store_id)->where('discount_id', $discountId)->with('variations')->get();
    }

    public function getApplicableProducts($discountId)
    {
        return Product::whereIn('product_id', function ($query) use ($discountId) {
            $query->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'product')
                ->where('excluded', false)
                ->select('applicable_id');//Applicable_id is product_id
        })->with('variations')->get();
    }

    public function getApplicableVariations($discountId)
    {
        return ProductVariation::whereIn('variation_id', function ($query) use ($discountId) {
            $query->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'variation')
                ->where('excluded', false)
                ->select('applicable_id');//Applicable_id is variation_id
        })->get();
    }

    public function getApplicableCategories($discountId)
    {
        return Category::whereIn('id', function ($query) use ($discountId) {
            $query->select('applicable_id')
                ->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'category')
                ->where('excluded', false);
        })->get();
    }

    public function getCustomDiscount($discountId, $type, $applicableId)
    {
        $customDiscount = DiscountApplicable::where('discount_id', $discountId)
            ->where('applicable_type', $type)
            ->where('applicable_id', $applicableId)
            ->select('custom_discount_type', 'custom_discount_value')
            ->first();

        return $customDiscount
            ? ['type' => $customDiscount->custom_discount_type, 'value' => $customDiscount->custom_discount_value]
            : null;
    }

    public function getExcludedProducts($discountId)
    {
        return Product::whereIn('product_id', function ($query) use ($discountId) {
            $query->select('applicable_id')
                ->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'product')
                ->where('excluded', true);
        })->with('variations')->get();
    }

    public function getProductsByCategories($storeId, $categoryIds)
    {
        return Product::where('store_id', $storeId)
            ->whereIn('product_id', function ($query) use ($categoryIds) {
                $query->select('product_id')
                    ->from('product_category')
                    ->whereIn('category_id', $categoryIds);
            })->with('variations')->get();
    }

    public function findProductById($productId)
    {
        return Product::find($productId);
    }


}