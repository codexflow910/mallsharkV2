<?php

namespace Packages\Product\Repositories\V2;

use Packages\Product\Contracts\V2\ProductRepositoryInterface;
use Packages\Product\Models\V2\Product;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Models\V2\{ProductVariation,ProductTier};
use Packages\Discount\Models\V2\DiscountApplicable;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ElasticSearchController;
use App\Models\Discount;

class VariableProductRepository extends ProductRepository implements ProductRepositoryInterface
{
    protected $model;
    private $_esearch;

    public function __construct(Product $model)
    {
        $this->model = $model;
        $this->_esearch = new ElasticSearchController();
    
    }

   public function storeVariations($product, $variations, $productImages, $imagesGallery)
    {
        $index = 1;
        foreach ($variations as $variationData) {

            $variation = new ProductVariation();
            $variation->product_id = $product->product_id;

            // Attributes
            $variation->attribute1 = $variationData['attribute1'] ?? null;
            $variation->attribute2 = $variationData['attribute2'] ?? null;
            $variation->attribute3 = $variationData['attribute3'] ?? null;

            $variation->unit1 = $variationData['unit1'] ?? null;
            $variation->unit2 = $variationData['unit2'] ?? null;
            $variation->unit3 = $variationData['unit3'] ?? null;

            // Options
            $variation->option1 = $variationData['option1'] ?? null;
            $variation->option2 = $variationData['option2'] ?? null;
            $variation->option3 = $variationData['option3'] ?? null;

            // Basic fields
            $variation->price = $variationData['price'] ?? 0;
            $variation->sku = $variationData['sku'] ?? null;
            $variation->current_stock = $variationData['current_stock'] ?? 0;
            $variation->maxquantitylimit = $product['maxquantitylimit'] ?? null;
            $variation->product_variation_image = $variationData['product_variation_image'] ?? null;
            $variation->order = $index;
            $variation->save();
            $index++;
        }
    }


    public function getVariationsInfo($data)
    {
     

        if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation1']) && $data['data']['variation1'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::with('tiers')->where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('option2', '=', $data['data']['variation2'])
                ->where('option3', '=', $data['data']['variation3'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage','product_id')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation1']) && $data['data']['variation1'] != '') {

            return $results = ProductVariation::with('tiers')->where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('option2', '=', $data['data']['variation2'])
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage','product_id')
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::with('tiers')->where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                ->where('option2', '=', $data['data']['variation2'])
                // ->where('visibility', 1)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage','product_id')
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation1']) && $data['data']['variation1'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::with('tiers')->where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                ->where('option1', '=', $data['data']['variation1'])
                // ->where('variation_id','current_stock', '>', 0)
                // ->where('visibility', 1)
                ->select('price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage','product_id')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation1']) && $data['data']['variation1'] != '') {

            // dd($data['data']['productId']);
            return $results = ProductVariation::with('tiers')->where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                // ->where('visibility', 1)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage','product_id')
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '') {

            return $results = ProductVariation::with('tiers')->where('product_id', '=', $data['data']['productId'])
                ->where('option2', '=', $data['data']['variation2'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage','product_id')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::with('tiers')->where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage','product_id')
                ->get()
                ->toArray();
        }
    }
    public function updateVariations($product, $variations, $productImage, $imagesGallery, $variationsDeleted)
    {
        $createdVariationIds = [];
        if($product->unique_product == true){
            ProductTier::where('product_id', $product->product_id)
                ->delete();
        }
        if (!empty($variationsDeleted)) {
            foreach ($variationsDeleted as $var) {
                DiscountApplicable::where('applicable_id', $var)
                    ->where('applicable_type', 'variation')
                    ->delete();
                ProductTier::where('product_id', $product->product_id)
                    ->where('variation_id', $var)
                    ->delete();
                ProductVariation::where('product_id', $product->product_id)->where('variation_id', $var)->delete();
            }
        }
        // Store new variations
        $index = 1;
        foreach ($variations as $requestVariation) {
            if(is_null($requestVariation['variation_id'])){
                $variation = new ProductVariation();
            }else{
                $applicableVariations = null;
                $variation = ProductVariation::find($requestVariation['variation_id']);
                $discount = Discount::where(['store_id' => $product->store_id, 'status' => 'active', 'state' => 'live','type' => 'sale'])->first();
                $discountId = $discount?->id;
                if($discountId && $variation){
                    $applicableVariations = DiscountApplicable::where('applicable_id', $variation['variation_id'])
                    ->where('discount_id', $discountId)
                    ->where('applicable_type', 'variation')
                    ->where('excluded', false)
                    ->select('applicable_id')->first();
                }
                if(!is_null($applicableVariations)){
                    $customDiscount = $this->getCustomDiscount($discount->id, 'variation', $variation->variation_id);
                    if (!empty($customDiscount) && !empty($customDiscount['type']) && !empty($customDiscount['value'])) {
                        $discountType = $customDiscount['type'];
                        $discountValue = $customDiscount['value'];
                    } else {
                        $discountType = $discount->discount_value_type;
                        $discountValue = $discount->discount_value;
                    }
                    if ($discountType == 'fixed') { 
                        $discount_value = $requestVariation['price'] - $discountValue;
                        // Apply the discount only if the resulting price is greater than 0
                        if ($discount_value >= 0.5) {
                            if ($requestVariation['price'] > 0) {
                                if ($requestVariation['price'] > $discountValue) {
                                    $discount_percentage = round(floatval(($discountValue / $requestVariation['price']) * 100));
                                } else {
                                    $discount_percentage = 100;
                                }
                            } else {
                                $discount_percentage = 0; // Handle case for zero price
                            }
                            // Set sale price and discount percentage for the variation
                            $variation->sale_price = $discount_value;
                            $variation->discount_percentage = $discount_percentage;
                        } else {
                            $variation->sale_price = null;
                        }
                    } else if ($discountType == 'percent') {
                        $discount_value = $requestVariation['price'] * ($discountValue / 100);
                        $sale_price = $requestVariation['price'] - $discount_value;
                        // Apply the discount only if the resulting price is greater than 0
                        if ($sale_price >= 0.5) {
                            $variation->sale_price = $sale_price;
                            $variation->discount_percentage = $discountValue;
                        }else{
                            $variation->sale_price = null;
                        }
                    }
                }
            }
            $productID = $product->product_id;
            $variation->product_id = $product->product_id;

            // Attributes
            $variation->attribute1 = $requestVariation['attribute1'] ?? null;
            $variation->attribute2 = $requestVariation['attribute2'] ?? null;
            $variation->attribute3 = $requestVariation['attribute3'] ?? null;

            $variation->unit1 = $requestVariation['unit1'] ?? null;
            $variation->unit2 = $requestVariation['unit2'] ?? null;
            $variation->unit3 = $requestVariation['unit3'] ?? null;

            // Options
            $variation->option1 = $requestVariation['option1'] ?? null;
            $variation->option2 = $requestVariation['option2'] ?? null;
            $variation->option3 = $requestVariation['option3'] ?? null;

            // Basic fields
            $variation->price = $requestVariation['price'] ?? 0;
            $variation->sku = $requestVariation['sku'] ?? null;
            $variation->current_stock = $requestVariation['current_stock'] ?? 0;
            $variation->maxquantitylimit = $product['maxquantitylimit'] ?? null;
            $variation->product_variation_image = $requestVariation['product_variation_image'] ?? null;
            $variation->order = $index;
            $variation->save();
            $index++;
        }
        $this->updateParentProductOnSale($productID);
    }
    
            
        
    private function updateParentProductOnSale($productID)
    {
        try {
            $product = $this->findProductById($productID);
    
            if (!$product) return;
            // Fetch variations
            $variations = $product->variations;
    
            if ($variations->isEmpty()) return;
    
            // Step 1: Filter discounted variations
            $discountedVariations = $variations->filter(function ($v) {
                return $v->sale_price != null && $v->sale_price < $v->price;
            });
            
            if ($discountedVariations->isNotEmpty()) {
            
                // Use lowest discounted variation
                $lowestDiscounted = $discountedVariations->sortBy('sale_price')->first();
                $product->price = $lowestDiscounted->price;
                $product->sale_price = $lowestDiscounted->sale_price;
                $product->on_sale = true;
                $product->discount_percentage = $lowestDiscounted['discount_percentage'];
                $product->save();
    
            } else {
                // No discounts – fallback to lowest regular price variation
                $lowestVariation = $variations->sortBy('price')->first();
    
                $product->price = $lowestVariation->price;
                $product->sale_price = null; // not on sale
                $product->on_sale = false;
                $product->discount_percentage = null;
                $product->save();
            }
    
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function parentProductOnSale($data)
    {
        try {
            $onSale = $data['on_sale'];
            $discountValue = $data['discount_value'];
            $discountId = $data['discount_id'];
            $productId = $data['product_id'];
            $discountPercentage = $data['discount_percentage'];
            $actualPrice = $data['actual_price'];
            DB::update("
                UPDATE products 
                SET on_sale = ?, 
                discount_id = ?,
                discount_percentage = ?,
                sale_price = ?,
                price = ?
                WHERE product_id = ?",
                [$onSale, $discountId,$discountPercentage,$discountValue,$actualPrice, $productId]
            );
        } catch (\Exception $e) {
            throw new \Exception("Error saving product: " . $e->getMessage());
        }
    }


       public function changestatus($productId,$productStatus, $store_id,$p_status)
    {
      
        if($p_status==5){
            if ($productStatus == "Delete") {
                $product = Product::where('product_id', $productId)
                    ->update(['is_deleted' => '1', 'status' => ProductConstants::STATUS_DELETED]);
            }
            
        }
       
            $variationIds = ProductVariation::where('product_id', $productId)
            ->pluck('variation_id')
            ->toArray();
           for ($i = 0; $i < count($variationIds); $i++) {
             if($productStatus=="Out")
            {
                     $product = Product::where('product_id', $productId) 
                        ->update(['current_stock' =>'0']); 
                 $variation=  ProductVariation::select()->where('variation_id', $variationIds[$i])->update(['current_stock' =>'0']);
                  $product = Product::find($productId);
                $product->searchable();
            }
            else if($productStatus=="Active"){
                                   $product = Product::where('product_id', $productId) 
                        ->update(['status' =>ProductConstants::STATUS_ACTIVE]);
                         $product = Product::find($productId);
                $product->searchable();
            }
             else if($productStatus=="Deactive"){
                  $product = Product::where('product_id', $productId) 
                        ->update(['status' =>ProductConstants::STATUS_DEACTIVATED]);
                         $product = Product::find($productId);
                $product->unsearchable();
            }
            else if($productStatus=="Delete"){
             $product = Product::where('product_id', $productId) 
                        ->update(['is_deleted' =>'1','status'=>ProductConstants::STATUS_DELETED]);
                $product = Product::find($productId);
                $product->unsearchable();
            }
        }
    }

    public function updateVariationTiers($productId, $createdVariationIds, $deletedVariationIds, $productVariationTiers)
    {
        DB::beginTransaction();

        try {
            // 1. Delete tiers of deleted variations
            if (!empty($deletedVariationIds)) {
                ProductTier::where('product_id', $productId)
                    ->whereIn('variation_id', $deletedVariationIds)
                    ->delete();
            }

            $assignedTierIds = [];

            // 2. Loop through all created variations and reassign tiers
            foreach ($createdVariationIds as $index => $variationId) {
                $tiers = $productVariationTiers[$index] ?? null;

                if (!empty($tiers)) {
                    foreach ($tiers as $tier) {
                        $updated = ProductTier::where('id', $tier['id'])
                            ->where('product_id', $productId)
                            ->update(['variation_id' => $variationId]);

                        if ($updated) {
                            $assignedTierIds[] = $tier['id'];
                        }
                    }
                } else {
                    // If no tiers for this variation, remove any existing ones
                    ProductTier::where('product_id', $productId)
                        ->where('variation_id', $variationId)
                        ->delete();
                }
            }

            // 3. Delete any unassigned tiers
            if (!empty($assignedTierIds)) {
                ProductTier::where('product_id', $productId)
                    ->whereNotIn('id', $assignedTierIds)
                    ->delete();
            } else {
                ProductTier::where('product_id', $productId)->delete();
            }

            DB::commit();

            // 4. Return updated tiers
            return ProductTier::where('product_id', $productId)->get();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
