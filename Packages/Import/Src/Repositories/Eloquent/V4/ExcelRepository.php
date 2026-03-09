<?php

namespace Packages\Import\Repositories\Eloquent\V4;

use Packages\Product\Models\V4\Product;
use App\Models\Discount;
use Packages\Discount\Models\V4\DiscountApplicable;
use Illuminate\Support\Facades\DB;
use Packages\Product\Models\V4\ProductVariation;
use Illuminate\Support\Facades\Log;

class ExcelRepository
{
    public function getProductsByIds(array $productIds)
    {
        return Product::query()
            ->whereIn('product_id', $productIds)
            ->select('product_id','product_type', 'title', 'short_description', 'description', 'price', 'current_stock', 'sku', 'productSize_length', 'productSize_width', 'productSize_height', 'dimention_type')
            ->get();
    }

    
    /* -------------------------------------------------
     | PRODUCT CALCULATION
     |--------------------------------------------------*/

    /**
     * KEEPING SAME METHOD NAME
     * Now returns calculated data only
     */
    public function updateProduct($productId, array $data): array
    {
        $storeId = $data['store_id'] ?? null;
        $price   = $data['price'] ?? 0;

        if (!$storeId || $price <= 0) {
            $data['on_sale'] = false;
            return $data;
        }

        $discount = $this->getActiveSaleDiscount($storeId);
        if (!$discount) {
            $data['on_sale'] = false;
            return $data;
        }

        if (!$this->isApplicable($discount->id, 'product', $productId)) {
            $data['on_sale'] = false;
            return $data;
        }

        $custom = $this->getCustomDiscount($discount->id, 'product', $productId);

        $type  = $custom['type']  ?? $discount->discount_value_type;
        $value = $custom['value'] ?? $discount->discount_value;

        return array_merge(
            $data,
            $this->calculatePrice($price, $type, $value, $discount->id)
        );
    }

    /* -------------------------------------------------
     | VARIATION CALCULATION
     |--------------------------------------------------*/

    /**
     * KEEPING SAME METHOD NAME
     * No DB update anymore
     */
    public function updateVariationWithDiscount(
        int $productId,
        int $variationId,
        array $variantUpdateData,
        int $storeId
    ): array {
        $price = $variantUpdateData['price'] ?? 0;

        if ($price <= 0) {
            return $variantUpdateData;
        }

        $discountData = $this->calculateVariationDiscount(
            $productId,
            $variationId,
            $price,
            $storeId
        );

        return array_merge($variantUpdateData, $discountData);
    }

    public function calculateVariationDiscount(
        int $productId,
        int $variationId,
        float $price,
        int $storeId
    ): array {
        $discount = $this->getActiveSaleDiscount($storeId);

        if (!$discount || $price <= 0) {
            return $this->emptyDiscount();
        }

        if (!$this->isApplicable($discount->id, 'variation', $variationId)) {
            return $this->emptyDiscount();
        }

        $custom = $this->getCustomDiscount($discount->id, 'variation', $variationId);

        $type  = $custom['type']  ?? $discount->discount_value_type;
        $value = $custom['value'] ?? $discount->discount_value;

        return $this->calculatePrice($price, $type, $value, $discount->id);
    }

    /* -------------------------------------------------
     | PARENT PRODUCT PRICE DECISION
     |--------------------------------------------------*/

    /**
     * NO UPDATE HERE
     * Returns data to update
     */
    public function updateParentProductPrice($product, float $minPrice): array
    {
        $discountedVariation = $product->variations
            ->whereNotNull('sale_price')
            ->sortBy('sale_price')
            ->first();

        if ($discountedVariation) {
            return [
                'price' => $discountedVariation->price,
                'sale_price' => $discountedVariation->sale_price,
                'on_sale' => true,
                'discount_percentage' => $discountedVariation->discount_percentage,
            ];
        }

        return [
            'price' => $minPrice,
            'sale_price' => null,
            'on_sale' => false,
            'discount_percentage' => null,
        ];
    }

    /* -------------------------------------------------
     | CORE DISCOUNT LOGIC
     |--------------------------------------------------*/

    private function calculatePrice(
        float $price,
        string $type,
        float $value,
        int $discountId
    ): array {
        if ($type === 'percent') {
            $salePrice = $price - ($price * ($value / 100));

            return $salePrice >= 0.5
                ? [
                    'sale_price' => $salePrice,
                    'discount_percentage' => $value,
                    'discount_id' => $discountId,
                    'on_sale' => true
                ]
                : $this->emptyDiscount();
        }

        if ($type === 'fixed' && $price > $value) {
            $salePrice = $price - $value;

            return $salePrice >= 0.5
                ? [
                    'sale_price' => $salePrice,
                    'discount_percentage' => round(($value / $price) * 100),
                    'discount_id' => $discountId,
                    'on_sale' => true
                ]
                : $this->emptyDiscount();
        }

        return $this->emptyDiscount();
    }

    private function emptyDiscount(): array
    {
        return [
            'sale_price' => null,
            'discount_percentage' => null,
            'discount_id' => null,
            'on_sale' => false,
        ];
    }

    /* -------------------------------------------------
     | DISCOUNT HELPERS
     |--------------------------------------------------*/

    private function getActiveSaleDiscount(int $storeId)
    {
        return Discount::where([
            'store_id' => $storeId,
            'type'     => 'sale',
            'status'   => 'active',
            'state'    => 'live'
        ])->first();
    }

    private function isApplicable(int $discountId, string $type, int $id): bool
    {
        return DiscountApplicable::where([
            'discount_id'     => $discountId,
            'applicable_type' => $type,
            'applicable_id'   => $id,
            'excluded'        => false
        ])->exists();
    }

    public function getCustomDiscount($discountId, $type, $applicableId)
    {
        $custom = DiscountApplicable::where('discount_id', $discountId)
            ->where('applicable_type', $type)
            ->where('applicable_id', $applicableId)
            ->select('custom_discount_type', 'custom_discount_value')
            ->first();

        return $custom
            ? ['type' => $custom->custom_discount_type, 'value' => $custom->custom_discount_value]
            : null;
    }
}