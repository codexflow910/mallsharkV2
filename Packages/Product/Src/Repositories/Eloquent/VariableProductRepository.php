<?php

namespace Packages\Product\Repositories\Eloquent;

use Packages\Product\Contracts\ProductRepositoryInterface;
use Packages\Product\Models\V2\Product;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Models\ProductVariation;
use Packages\Discount\Models\DiscountApplicable;
use Illuminate\Support\Facades\DB;

use App\Models\Discount;
class VariableProductRepository extends ProductRepository implements ProductRepositoryInterface
{
    protected $model;
 

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function storeVariations($product, $variations,$productImages,$imagesGallery)
    {
        
        foreach ($variations['option1'] as $index => $option1) {
            $variation = new ProductVariation();
            $variation->product_id = $product->product_id;
            $variation->attribute1 = $variations['attribute1'][0] ?? null;
            $variation->attribute2 = $variations['attribute2'][0] ?? null;
            $variation->attribute3 = $variations['attribute3'][0] ?? null;
            $variation->maxquantitylimit = $product['maxquantitylimit'] ?? null;
            $variation->unit1 = $variations['unit1'][0] ?? null;
            $variation->unit2 = isset($variations['attribute2'][0]) && isset($variations['unit2'][0]) ? $variations['unit2'][0] : null;
            $variation->unit3 = isset($variations['attribute3'][0]) && isset($variations['unit3'][0]) ? $variations['unit3'][0] : null;
            $variation->option1 = $option1;
            $variation->option2 = isset($variations['attribute2'][0]) && isset($variations['option2'][$index]) ? $variations['option2'][$index] : null;
            $variation->option3 = isset($variations['attribute3'][0]) && isset($variations['option3'][$index]) ? $variations['option3'][$index] : null;
            $variation->price = $variations['price'][$index] ?? null;
            $variation->sku = $variations['sku'][$index] ?? null;
            $variation->current_stock = $variations['current_stock'][$index] ?? null;

            $imageIndex = array_search($variations['product_variation_image'][$index], $productImages);

            if (isset($imagesGallery[$imageIndex])) {
                $variation->product_variation_image = $imagesGallery[$imageIndex];
            }
            // $variation->product_variation_image =  $variations['product_variation_image'][$index] ? $this->getAttachmentId($variations['product_variation_image'][$index], 'image') : null;
            $variation->save();
        }
    }

    public function getVariationsInfo($data)
    {
     

        if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation1']) && $data['data']['variation1'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('option2', '=', $data['data']['variation2'])
                ->where('option3', '=', $data['data']['variation3'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation1']) && $data['data']['variation1'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('option2', '=', $data['data']['variation2'])
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage')
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                ->where('option2', '=', $data['data']['variation2'])
                // ->where('visibility', 1)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage')
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation1']) && $data['data']['variation1'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                ->where('option1', '=', $data['data']['variation1'])
                // ->where('variation_id','current_stock', '>', 0)
                // ->where('visibility', 1)
                ->select('price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation1']) && $data['data']['variation1'] != '') {

            // dd($data['data']['productId']);
            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                // ->where('visibility', 1)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage')
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option2', '=', $data['data']['variation2'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage')
                ->get()
                ->toArray();
        }
    }
    public function updateVariations($product, $variations, $productImage, $imagesGallery, $variationsDeleted, $variationsDiscounts)
    {
        $createdVariationIds = [];

        if (!empty($variationsDeleted)) {
            foreach ($variationsDeleted as $var) {
                DiscountApplicable::where('applicable_id', $var)
                    ->where('applicable_type', 'variation')
                    ->delete();
            }
        }

        // Delete existing variations
        ProductVariation::where('product_id', $product->product_id)->delete();

        // Store new variations
        foreach ($variations['option1'] as $index => $option1) {

            $variation = new ProductVariation();
            $variation->product_id = $product->product_id;
            $variation->attribute1 = $variations['attribute1'][0] ?? null;
            $variation->attribute2 = $variations['attribute2'][0] ?? null;
            $variation->attribute3 = $variations['attribute3'][0] ?? null;
            $variation->maxquantitylimit = $product['maxquantitylimit'] ?? null;
            $variation->unit1 = $variations['unit1'][0] ?? null;
            $variation->unit2 = isset($variations['attribute2'][0]) && isset($variations['unit2'][0]) ? $variations['unit2'][0] : null;
            $variation->unit3 = isset($variations['attribute3'][0]) && isset($variations['unit3'][0]) ? $variations['unit3'][0] : null;
            $variation->option1 = $option1;
            $variation->option2 = isset($variations['attribute2'][0]) && isset($variations['option2'][$index]) ? $variations['option2'][$index] : null;
            $variation->option3 = isset($variations['attribute3'][0]) && isset($variations['option3'][$index]) ? $variations['option3'][$index] : null;
            $variation->price = $variations['price'][$index] ?? null;
            $variation->sku = $variations['sku'][$index] ?? null;
            $variation->current_stock = $variations['current_stock'][$index] ?? null;
            $imageIndex = array_search($variations['product_variation_image'][$index], $productImage);
            if (isset($imagesGallery[$imageIndex])) {
                $variation->product_variation_image = $imagesGallery[$imageIndex];
            }

            $variation->save();
                        
            // Handle variation-specific discount records
            if (!empty($variationsDiscounts[$index])) {
                foreach ($variationsDiscounts[$index] as $discountData) {
                    DiscountApplicable::where('applicable_id', $discountData['applicable_id'])
                        ->where('applicable_type', $discountData['applicable_type'])
                        ->update([
                            'applicable_id' => $variation->variation_id,
                        ]);
                }
            }

            $createdVariationIds[] = $variation->variation_id;
            
        }

        $discount = Discount::where(['store_id' => $product->store_id, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();

        $variationIds = $createdVariationIds;
        $discountId = $discount?->id;
        $applicableVariations = ProductVariation::whereIn('variation_id', function ($query) use ($discountId) {
            $query->from('discount_applicables')
                ->where('discount_id', $discountId)
                ->where('applicable_type', 'variation')
                ->where('excluded', false)
                ->select('applicable_id');
        })
        ->whereIn('variation_id', $variationIds)
        ->get();

        if($applicableVariations->isNotEmpty()){

            foreach ($applicableVariations as $variation) {

                $customDiscount = $this->getCustomDiscount($discount->id, 'variation', $variation->variation_id);
                if (!empty($customDiscount) && !empty($customDiscount['type']) && !empty($customDiscount['value'])) {
                    $discountType = $customDiscount['type'];
                    $discountValue = $customDiscount['value'];
                } else {
                    $discountType = $discount->discount_value_type;
                    $discountValue = $discount->discount_value;
                }

                if ($discountType == 'fixed') { 

                    $discount_value = $variation->price - $discountValue;

                    // Apply the discount only if the resulting price is greater than 0
                    if ($discount_value >= 0.5) {
                        if ($variation->price > 0) {
                            if ($variation->price > $discountValue) {
                                $discount_percentage = round(floatval(($discountValue / $variation->price) * 100));
                            } else {
                                $discount_percentage = 100;
                            }
                        } else {
                            $discount_percentage = 0; // Handle case for zero price
                        }

                        // Set sale price and discount percentage for the variation
                        $variation->sale_price = $discount_value;
                        $variation->discount_percentage = $discount_percentage;
                        // $variation->save();
                        $variation->save();
                        $this->updateParentProductOnSale($variation->product_id, $discount->id);
                    } else {
                        $variation->sale_price = null;
                        // $variation->save();
                        $variation->save();
                    }

                } else if ($discountType == 'percent') {

                    $discount_value = $variation->price * ($discountValue / 100);
                    $sale_price = $variation->price - $discount_value;

                    // Apply the discount only if the resulting price is greater than 0
                    if ($sale_price >= 0.5) {
                        $variation->sale_price = $sale_price;
                        $variation->discount_percentage = $discountValue;
                        // $variation->save();
                        $variation->save();
                        $this->updateParentProductOnSale($variation->product_id, $discount->id);
                    } else {
                        $variation->sale_price = null;
                        // $variation->save();
                        $variation->save();
                    }
                }
            }

            $productID = $variation->product_id;
           
        }
    }

    private function updateParentProductOnSale($productId, $discountId)
    {
        try {
            $product = $this->findProductById($productId);
            
            if ($product) {
                // $product->on_sale = true;
                $on_sale = true;
                $this->ParentProductOnSale($product->product_id, $on_sale, $discountId);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function ParentProductOnSale($id, $onSale, $discountId)
    {
        try {
            DB::update("
                UPDATE products 
                SET on_sale = ?, 
                discount_id = ? 
                WHERE product_id = ?",
                [$onSale, $discountId, $id]
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

}
