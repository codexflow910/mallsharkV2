<?php

namespace Packages\Import\Repositories\Eloquent\V3;

use Packages\Import\Contracts\V3\CsvRepositoryInterface;
use Packages\Discount\Models\V3\Discount;
use Packages\Product\Models\V3\Product;
use Packages\Import\Contracts\V3\BaseApiResponse;
use Illuminate\Support\Facades\DB;
use Packages\Import\Contracts\V3\CsvHelpers;

class CsvRepository implements CsvRepositoryInterface
{
    use BaseApiResponse;
    use CsvHelpers;
    
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function updateSimpleProductPriceAndStock($product, array $data)
    {
        DB::beginTransaction();
        try {
            $product->price = $data['Variant Price'] ?? $product->price;
            $product->current_stock = $data['Variant Inventory'] ?? $product->current_stock;
            $product->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function applyDiscountSimpleProduct($product)
    {
        $storeId = $product->store_id;

        $discount = Discount::where([
            'store_id' => $storeId,
            'type' => 'sale',
            'status' => 'active',
            'state' => 'live',
        ])->first();

        if ($discount) {
            $this->applyDiscountLogicToEntity($product, $discount);
        }

        $product->save();
    }

    private function applyDiscountLogicToEntity($entity, $discount)
    {
        $discountValue = floatval($discount->discount_value);
        $entityPrice = floatval($entity->price);

        // Handle percent-based discount
        if ($discount->discount_value_type === 'percent') {
            $discountAmount = $entityPrice * ($discountValue / 100);
            $salePrice = $entityPrice - $discountAmount;

            $entity->on_sale = $salePrice > 0;
            $entity->sale_price = $entity->on_sale ? $salePrice : $entityPrice;
            $entity->discount_percentage = $discountValue;
        }
        // Handle fixed discount
        elseif ($discount->discount_value_type === 'fixed') {
            $salePrice = $entityPrice - $discountValue;

            $entity->on_sale = $salePrice > 0;
            $entity->sale_price = $entity->on_sale ? $salePrice : $entityPrice;
            $entity->discount_percentage = $entityPrice > 0
                ? round(($discountValue / $entityPrice) * 100, 2)
                : 0;
        }

        $entity->discount_id = $discount->id;
    }

    public function updatePriceAndStock($product, array $data)
    {
        $variations = $product->variations;

        foreach ($variations as $variation) {
            $this->updateVariationPriceAndStock($variation, $data);
        }

        $product->save();
    }

    private function updateVariationPriceAndStock($variation, array $data)
    {
        DB::beginTransaction();
        try {
            if ($this->matchVariation($variation, $data)) {
                $variation->price = $data['Variant Price'] ?? $variation->price;
                $variation->current_stock = $data['Variant Inventory'] ?? $variation->current_stock;
                $variation->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function matchVariation($variation, array $data)
    {
        // Match logic for variations (option values comparison)
        $option1Value = $this->normalization($data['Option1 Value']);
        $variationOption1 = $this->normalization($variation->option1);

        $match = $variationOption1 === $option1Value;

        if (!empty($data['Option2 Value'])) {
            $option2Value = $this->normalization($data['Option2 Value']);
            $variationOption2 = $this->normalization($variation->option2);
            $match = $match && $variationOption2 === $option2Value;
        }

        if (!empty($data['Option3 Value'])) {
            $option3Value = $this->normalization($data['Option3 Value']);
            $variationOption3 = $this->normalization($variation->option3);
            $match = $match && $variationOption3 === $option3Value;
        }

        return $match;
    }

    public function applyDiscount($product)
    {
        $storeId = $product->store_id;

        $discount = Discount::where([
            'store_id' => $storeId,
            'type' => 'sale',
            'status' => 'active',
            'state' => 'live',
        ])->first();

        if ($discount) {
            // Apply discount to the product itself
            $this->applyDiscountLogicToEntity($product, $discount);

            // Apply discount to variations
            foreach ($product->variations as $variation) {
                $this->applyDiscountLogicToEntity($variation, $discount);
            }
        }
    }

    
}
