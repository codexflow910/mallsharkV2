<?php

namespace Packages\Discount\Services\V3;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Packages\Discount\Contracts\V3\DiscountHelpers;
use App\Http\Controllers\ElasticSearchController;
use Exception;
use Packages\Core\Contracts\V3\CoreHelpers;
use Packages\Discount\Repositories\Eloquent\V3\DiscountRepository;
use Packages\Product\Models\V3\Product;
class DiscountService

{
    use DiscountHelpers;
    protected $discountRepository;
    private $_esearch;


    public function __construct(DiscountRepository $discountRepository)
    {
        $this->discountRepository = $discountRepository;
        $this->_esearch = new ElasticSearchController();
    }

    public function saveDiscount(array $data)
    {
        $user = auth()->user();
        $userId = $user->user_id;
        $store = DiscountHelpers::getAccessibleStore($userId);
        
        if (!$store) {
            throw new ItemNotFoundException('User does not belong or have access to any store.');
        }

        $storeId = $store->store_id;

        $discountData = [
            'store_id' => $storeId,
            'code' => $data['discountCode'] ?? null,
            'title' => $data['autoDiscountTitle'] ?? null,
            'type' => $data['discountActive'] == 'Coupons' ? 'coupon' : 'sale',
            'platform_wide' => false,
            'discount_value_type' => $data['discount_type'] == 'percentage' ? 'percent' : 'fixed',
            'discount_value' => $data['discount_type'] == 'percentage' ? $data['percentage_value'] : $data['fixed_value'],
            'start_time' => $data['start_date'] . ' ' . ($data['start_time'] ?? '00:00:00'),
            'end_time' => $data['end_date'] . ' ' . ($data['end_time'] ?? '23:59:59'),
            'status' => 'inActive',
            'state' => 'default',
            'minimum_purchase_amount' => $data['min_purchase_amount_value'] ?? null,
            'maximum_purchase_amount' => $data['max_purchase_amount_value'] ?? null,
            'minimum_quantity_items' => $data['min_quantity_value'] ?? null,
            'maximum_quantity_items' => $data['max_quantity_value'] ?? null,
            'max_usage_limit' => $data['totalUses'] ?? null,
            'one_use_per_customer' => isset($data['one_use_per_customer']) && $data['one_use_per_customer'] == true ? 1 : 0,
        ];

        DB::beginTransaction();

        try {
            $discount = $this->discountRepository->saveDiscount($discountData);
            
            // Handle discount applicables (Selective Discounts)
            if ($discount && !empty($data['applicable_products']) || !empty($data['applicable_variations']) || !empty($data['excluded_products']) || !empty($data['applicable_categories'])) {
                $discountApplicables = $this->saveDiscountApplicables($discount->id, $data);
            }

            DB::commit();

            return ['discount' => $discount, 'discountApplicables' => $discountApplicables];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Error saving discount: " . $e->getMessage());
        }
    }
    public function updateDiscount(array $data, $id)
    {
        $user = auth()->user();
        $userId = $user->user_id;
        $store = DiscountHelpers::getAccessibleStore($userId);

        if (!$store) {
            throw new ItemNotFoundException('User does not belong or have access to any store.');
        }

        $storeId = $store->store_id;
        $wasActive = false;

        $discountCheck = $this->discountRepository->findById($id);

        if ($discountCheck->status === 'active' && $discountCheck->state === 'live') {

            $wasActive = true;
            if ($discountCheck->type == 'sale') {
                $this->resetProductPrices($discountCheck->store_id, $discountCheck->id);
            }
        }

        $discountData = [
            'store_id' => $storeId,
            'code' => $data['discountCode'] ?? null,
            'title' => $data['autoDiscountTitle'] ?? null,
            'type' => $data['discountActive'] == 'Coupons' ? 'coupon' : 'sale',
            'platform_wide' => false,
            'discount_value_type' => $data['discount_type'] == 'percentage' ? 'percent' : 'fixed',
            'discount_value' => $data['discount_type'] == 'percentage' ? $data['percentage_value'] : $data['fixed_value'],
            'start_time' => $data['start_date'] . ' ' . ($data['start_time'] ?? '00:00:00'),
            'end_time' => $data['end_date'] . ' ' . ($data['end_time'] ?? '23:59:59'),
            'minimum_purchase_amount' => $data['min_purchase_amount_value'] ?? null,
            'maximum_purchase_amount' => $data['max_purchase_amount_value'] ?? null,
            'minimum_quantity_items' => $data['min_quantity_value'] ?? null,
            'maximum_quantity_items' => $data['max_quantity_value'] ?? null,
            'max_usage_limit' => $data['totalUses'] ?? null,
            'one_use_per_customer' => isset($data['one_use_per_customer']) && $data['one_use_per_customer'] == true ? 1 : 0,
        ];

        DB::beginTransaction();

        try {
            $discount = $this->discountRepository->updateDiscount($discountData, $id);

            if($discount) {
                // Delete old discount applicables
                $this->discountRepository->deleteDiscountApplicables($id);

                // Save new discount applicables
                $discountApplicables = $this->saveDiscountApplicables($discount->id, $data);

                // Reapply new discount prices if the discount was active
                if ($wasActive && $discount->type == 'sale') {
                    $this->updateProductPrices($discount->store_id, $discount);
                }
            }
            DB::commit();
            return ['discount' => $discount, 'discountApplicables' => $discountApplicables];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    private function saveDiscountApplicables($discountId, $data)
    {
        try {
            if(!$discountId){
                throw new itemNotFoundException("Discount ID is missing.");
            }

            $applicables = [];

            // Store applicable categories
            if (!empty($data['applicable_categories'])) {
                foreach ($data['applicable_categories'] as $categoryId) {
                    $applicables[] = [
                        'discount_id' => $discountId,
                        'applicable_type' => 'category',
                        'applicable_id' => $categoryId,
                        'custom_discount_type' => null,
                        'custom_discount_value' => null,
                        'excluded' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Store applicable products & variations with custom discount values
            if (!empty($data['applicable_products'])) {
                foreach ($data['applicable_products'] as $product) {
                    $applicables[] = [
                        'discount_id' => $discountId,
                        'applicable_type' => $product['applicable_type'],
                        'applicable_id' => $product['applicable_id'],
                        'custom_discount_type' => isset($product['custom_discount_type']) && !empty($product['custom_discount_type']) ? $product['custom_discount_type'] : null,
                        'custom_discount_value' => isset($product['custom_discount_value']) && !empty($product['custom_discount_value']) ? $product['custom_discount_value'] : null,
                        'excluded' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($applicables)) {
                return $this->discountRepository->saveDiscountApplicables($applicables);
            }
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAllDiscounts()
    {
        try {
            $user = auth()->user();
            $userId = $user->user_id;
            $store = DiscountHelpers::getAccessibleStore($userId);

            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }

            $storeId = $store->store_id;

            return $this->discountRepository->getAllDiscounts($storeId);
        } catch (\Exception $e) {
            throw new ItemNotFoundException("Discounts not found.");
        }
    }

    public function getDiscount($id)
    {
        try {
            return $this->discountRepository->getDiscount($id);
        } catch (\Exception $e) {
            throw new ItemNotFoundException("Discount not found.");
        }
    }

    public function updateDiscountStatus($id, $status,$discountType)
    {
        DB::beginTransaction();

        try {
            $discount = $this->discountRepository->findById($id);

            if (!$discount) {
                throw new \Exception('Discount not found.');
            }

            $now = Carbon::now();
            $startDate = $this->parseDiscountDateTime($discount->start_time);
            $endDate = $this->parseDiscountDateTime($discount->end_time);
            $shouldUpdateProductPrices = false;
            $shouldResetProductPrices = false;
            if ($status === 'active') {
                if ($endDate && $now->gt($endDate)) {
                    throw new \Exception('Cannot activate because the discount is expired. Update dates to activate.');
                }

                $activeDiscounts = $this->discountRepository->findActiveDiscounts($discount->store_id, $id, $discountType);

                if ($activeDiscounts->count() > 0) {
                    return [
                        'status' => false,
                        'message' => 'There is another active discount for this store. Do you want to deactivate it and activate this one?',
                        'requires_confirmation' => true,
                    ];
                }

                if ($startDate && $startDate->lte($now)) {
                    $discount->status = 'active';
                    $discount->state = 'live';
                    $discount->status_manual_override = true;
                    if ($discount->type == 'sale') {
                        $shouldUpdateProductPrices = true;
                    }
                } elseif ($startDate && $startDate->gt($now)) {
                    $discount->status = 'active';
                    $discount->state = 'scheduled';
                    $discount->status_manual_override = false;
                }

            } else {
                $discount->status = 'inActive';
                $discount->state = 'default';
                $discount->status_manual_override = false;
                if ($discount->type == 'sale') {
                    $shouldResetProductPrices = true;
                }
            }

            $this->discountRepository->save($discount);

            DB::commit();
            // Update product prices AFTER committing the discount status
            // This prevents holding locks during long-running product updates
            if ($shouldUpdateProductPrices) {
                $this->updateProductPrices($discount->store_id, $discount);
            }

            if ($shouldResetProductPrices) {
                $this->resetProductPrices($discount->store_id, $discount->id);
            }
            if ($discount && $discount->type == 'sale' && $discount->status == 'active' && $discount->state == 'live' && $discount->archive == false) {
                return ['status' => true, 'message' => 'Discount status updated and changes will be applied after 5 minutes'];
            }
            return ['status' => true, 'message' => 'Discount status updated successfully.'];

        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ItemNotFoundException) {
                throw new ItemNotFoundException($e->getMessage());
            }
            throw new \Exception("Error updating discount: " . $e->getMessage());
        }
    }

    public function confirmUpdateStatus($id,$discountType)
    {
        try {
            DB::beginTransaction();

            $discount = $this->discountRepository->findById($id);
            if (!$discount) {
                throw new \Exception('Discount not found.');
            }

            $storeId = $discount->store_id;
            $now = Carbon::now();

            $discountStart = $this->parseDiscountDateTime($discount->start_time);
            $discountEnd = $this->parseDiscountDateTime($discount->end_time);
            $shouldUpdateProductPrices = false;
            $shouldResetOldDiscount = false;
            $shouldResetScheduledDiscount = false;
            $oldDiscountId = null;
            // Deactivate existing active discounts for the same store
            $updatedDiscount = $this->discountRepository->findActiveDiscountByStore($storeId, $id, $discountType);
            if ($updatedDiscount) {
                $updatedDiscount->status = 'inActive';
                $updatedDiscount->state = 'default';
                if ($updatedDiscount->type == 'sale') {
                    $shouldResetOldDiscount = true;
                    $oldDiscountId = $updatedDiscount->id;
                }
                $this->discountRepository->save($updatedDiscount);
            }

            // Determine and update the new discount status
            if ($discountStart && $discountStart->lte($now)) {
                $discount->status = 'active';
                $discount->state = 'live';
                $discount->status_manual_override = true;
                if($discount->type == 'sale'){
                    $shouldUpdateProductPrices = true;
                }
            } elseif ($discountStart && $discountStart->gt($now)) {
                $discount->status = 'active';
                $discount->state = 'scheduled';
                $discount->status_manual_override = false;
                if ($updatedDiscount && $updatedDiscount->type == 'sale') {
                    $shouldResetScheduledDiscount = true;
                }
            }

            $this->discountRepository->save($discount);

            DB::commit();
            // Update/Reset product prices AFTER committing the discount changes
            // This prevents holding locks during long-running product updates
            if ($shouldResetOldDiscount && $oldDiscountId) {
                $this->resetProductPrices($storeId, $oldDiscountId);
            }

            if ($shouldUpdateProductPrices) {
                $this->updateProductPrices($storeId, $discount);
            }

            if ($shouldResetScheduledDiscount && $oldDiscountId) {
                $this->resetProductPrices($storeId, $oldDiscountId);
            }
            if ($discount && $discount->type == 'sale' && $discount->status == 'active' && $discount->state == 'live' && $discount->archive == false) {
                return ['status' => true, 'message' => 'Discount status updated and changes will be applied after 5 minutes'];
            }
            return ['status' => true, 'message' => 'Discount activated successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ItemNotFoundException) {
                throw new ItemNotFoundException($e->getMessage());
            }
            throw new \Exception("Error updating discount: " . $e->getMessage());
        }
    }

    public function archiveDiscount($id)
    {
        try {
            $discount = $this->discountRepository->findById($id);

            if (!$discount) {
                throw new \Exception('Discount not found.');
            }

            if ($discount->status === 'active' && $discount->state === 'live') {
                if ($discount->type == 'sale') {
                    $this->resetProductPrices($discount->store_id, $id);
                }
            }

            $discount->archive = true;
            $this->discountRepository->save($discount);

            return ['status' => true, 'message' => 'Discount archived successfully'];
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                throw new ItemNotFoundException($e->getMessage());
            }
            throw new \Exception("Error updating discount: " . $e->getMessage());
        }
    }

    public function unArchiveDiscount($id)
    {
        try {
            $discount = $this->discountRepository->findById($id);

            if (!$discount) {
                throw new \Exception('Discount not found.');
            }

            $discount->archive = false;
            $discount->status = 'inActive';
            $discount->state = 'default';

            $this->discountRepository->save($discount);

            return ['status' => true, 'message' => 'Discount unarchived successfully'];
        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                throw new ItemNotFoundException($e->getMessage());
            }
            throw new \Exception("Error updating discount: " . $e->getMessage());
        }
    }

    public function getArchivedDiscounts()
    {
        try {
            $user = auth()->user();
            $userId = $user->user_id;
            $store = DiscountHelpers::getAccessibleStore($userId);

            if (!$store) {
                throw new ItemNotFoundException('User does not belong or have access to any store.');
            }
            
            $storeId = $store->store_id;
            return $this->discountRepository->getArchivedDiscountsByStore($storeId);

        } catch (\Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                throw new ItemNotFoundException($e->getMessage());
            }
            throw new \Exception($e->getMessage());
        }
    }




    private function parseDiscountDateTime($dateTime)
    {
        if (!$dateTime || $dateTime === '0000-00-00 00:00:00') {
            return null;
        }

        return Carbon::parse($dateTime);
    }


    private function handleProductPriceUpdate($discount, $now)
    {
        try {
            if ($discount->start_time && Carbon::parse($discount->start_time)->lte($now)) {
                $discount->status_manual_override = true;
                if($discount->type == 'sale'){
                    $this->updateProductPrices($discount->store_id, $discount);
                }
            } else {
                $discount->status_manual_override = false;
                if ($discount->type == 'sale') {
                    $this->resetProductPrices($discount->store_id, $discount->id);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

   public function updateProductPrices($storeId, $discount)
    {
        try {

            // Check if the discount has specific applicable products or variations
            $applicableProducts = $this->getApplicableProducts($discount->id);
            $applicableVariations = $this->getApplicableVariations($discount->id);
            $applicableCategories = $this->getApplicableCategories($discount->id);
            // $excludedProducts = $this->getExcludedProducts($discount->id);

            $products = collect();
            
            // If no specific products or variations exist, apply the discount to all store products
            if ($applicableProducts->isEmpty() && $applicableVariations->isEmpty() && $applicableCategories->isEmpty()) {
                $products = $this->getStoreProducts($storeId);
            } else {
                $products = $applicableProducts;
            }
            
            
             $products->chunk(20)->each(function ($productChunk) use ($discount) {
                DB::beginTransaction();
                try {
                    foreach ($productChunk as $product) {
                                $discountApplied = false; // Track if discount is applied

                                $customDiscount = $this->getCustomDiscount($discount->id, 'product', $product->product_id);
                                if (!empty($customDiscount) && !empty($customDiscount['type']) && !empty($customDiscount['value'])) {
                                    $discountType = $customDiscount['type'];
                                    $discountValue = $customDiscount['value'];
                                } else {
                                    $discountType = $discount->discount_value_type;
                                    $discountValue = $discount->discount_value;
                                }
                            

                            // Handle fixed discount type
                            if ($discountType  == 'fixed') {
                                $discount_value = $product->price - $discountValue;
                                // Check if the discounted price is greater than 0
                                if ($discount_value >= 0.5) {
                                    // Prevent division by zero and calculate discount percentage
                                    if ($product->price > 0) {
                                        if ($product->price > $discountValue) {
                                            $discount_percentage = round(floatval(($discountValue / $product->price) * 100));
                                        } else {
                                            $discount_percentage = 100;
                                        }
                                    } else {
                                        $discount_percentage = 0; // Handle case for zero price
                                    }

                                    $onSale = true;
                                    $this->discountRepository->productSave($product->product_id, $discount_value, $discount_percentage, $onSale, $discount->id);
                                    $discountApplied = true;
                                    // Send push notification if product is discounted
                                    if ($discountApplied) {
                                        $this->notifyFavoriteUsers($product);
                                    }
                                } else {

                                    $onSale = false;
                                    $this->discountRepository->productSave($product->product_id, null, null, $onSale, null);
                                }
                                // Handle product variations
                                if ($product->variations->isNotEmpty()) {
                                    foreach ($product->variations as $variation) {
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
                                            $this->discountRepository->savevariations($variation);
                                        } else {
                                            $variation->sale_price = $variation->price;
                                            // $variation->save();
                                            $this->discountRepository->savevariations($variation);
                                        }
                                    }
                                }
                            }
                            // Handle percentage discount type
                            else if ($discountType == 'percent') {
                                $discount_value = $product->price * ($discountValue / 100);
                                $sale_price = $product->price - $discount_value;

                                // Apply the discount only if the resulting price is greater than 0
                                if ($sale_price >= 0.5) {

                                    $onSale = true;
                                    $this->discountRepository->productSave($product->product_id, $sale_price, $discountValue, $onSale, $discount->id);
                                    $discountApplied = true;
                                    // Send push notification if product is discounted
                                    if ($discountApplied) {
                                        $this->notifyFavoriteUsers($product);
                                    }
                                } else {

                                    $onSale = false;
                                    $this->discountRepository->productSave($product->product_id, $product->price, null, $onSale, null);
                                }
                                // Handle product variations
                                if ($product->variations->isNotEmpty()) {
                                    foreach ($product->variations as $variation) {
                                        $discount_value = $variation->price * ($discountValue / 100);
                                        $sale_price = $variation->price - $discount_value;

                                        // Apply the discount only if the resulting price is greater than 0
                                        if ($sale_price >= 0.5) {
                                            $variation->sale_price = $sale_price;
                                            $variation->discount_percentage = $discountValue;
                                            // $variation->save();
                                            $this->discountRepository->savevariations($variation);
                                        } else {
                                            $variation->sale_price = $variation->price;
                                            // $variation->save();
                                            $this->discountRepository->savevariations($variation);
                                        }
                                    }
                                }
                            }
                            $product->algolia_batch = 1;
                            $product->save();
                        }
                    DB::commit();
                    // Small delay to prevent overwhelming the database
                    usleep(5000); // 5ms delay
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error updating product prices in chunk: ' . $e->getMessage());
                    // Continue with next chunk instead of throwing
                }  
            });
            if($applicableVariations->isNotEmpty()){
                $applicableVariations->chunk(20)->each(function ($productChunk) use ($discount) {
                    DB::beginTransaction();
                    try {
                        foreach ($productChunk as $key => $variation) {

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
                                        $this->discountRepository->savevariations($variation);
                                            $data = [
                                                'discount_value' => $discount_value,
                                                'discount_percentage' => $discount_percentage,
                                                'product_id' => $variation->product_id,
                                                'discount_id' => $discount->id,
                                                'actual_price' => $variation->price
                                            ];
                                            $this->updateParentProductOnSale($data);
                                    } else {
                                        $variation->sale_price = null;
                                        // $variation->save();
                                        $this->discountRepository->savevariations($variation);
                                    }

                                } else if ($discountType == 'percent') {

                                    $discount_value = $variation->price * ($discountValue / 100);
                                    $sale_price = $variation->price - $discount_value;

                                    // Apply the discount only if the resulting price is greater than 0
                                    if ($sale_price >= 0.5) {
                                        $variation->sale_price = $sale_price;
                                        $variation->discount_percentage = $discountValue;
                                        // $variation->save();
                                        $this->discountRepository->savevariations($variation);
                                            $data = [
                                                'discount_value' => $sale_price,
                                                'discount_percentage' => $discountValue,
                                                'product_id' => $variation->product_id,
                                                'discount_id' => $discount->id,
                                                'actual_price' => $variation->price

                                            ];
                                            $this->updateParentProductOnSale($data);
                                    } else {
                                        $variation->sale_price = null;
                                        // $variation->save();
                                        $this->discountRepository->savevariations($variation);
                                    }
                                }
                                $productID = $variation->product_id;
                                $product = Product::find($productID);
                                $product->algolia_batch = 1;
                                $product->save();
                            }
                        DB::commit();
                    // Small delay to prevent overwhelming the database
                        usleep(5000); // 5ms delay
                    } catch (\Exception $e) {
                        DB::rollBack();
                        \Log::error('Error updating variation prices in chunk: ' . $e->getMessage());
                        // Continue with next chunk instead of throwing
                    }   
                });
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    private function updateParentProductOnSale($data)
    {
        try {
            $product = $this->findProductById($data['product_id']);
                
                if ($product) {
                    $existingSalePrice = $product->sale_price;
                    $newSalePrice = $data['discount_value'];
                    if ($product->on_sale){
                        if($data['discount_percentage'] > 0 && $data['discount_value'] > 0){
                            $data['on_sale'] = true;
                            $this->discountRepository->updateParentProductOnSale($data);
                        }
                        
                    }else{
                        $data['on_sale'] = true;
                            $this->discountRepository->updateParentProductOnSale($data);
                    }
                }
            
            
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function resetProductPrices($storeId, $discountId)
    {
        try {
            $products = $this->getDiscountedProducts($storeId, $discountId);

            if ($products->count() > 0) {
                // Process products in smaller batches to avoid lock timeouts
                $products->chunk(20)->each(function ($productChunk) {
                    try {
                        foreach ($productChunk as $product) {
                            $minPrice = null;
                            if ($product->variations->isNotEmpty()) {
                                foreach ($product->variations as $variation) {
                                    $variation->sale_price = null;
                                    $variation->discount_percentage = 0;
                                    if(is_null($minPrice) || $minPrice > $variation->price){
                                        $minPrice = $variation->price;
                                    }
                                    $this->discountRepository->savevariations($variation);
                                }
                            }

                            $on_sale = false;
                            $discount_id = null;
                            $sale_price = null;
                            $discount_percentage = 0;
                            $this->discountRepository->productSave($product->product_id, $sale_price, $discount_percentage, $on_sale, $discount_id,$minPrice);
                            $product->algolia_batch = 1;
                            $product->save();
                            
                        }
                        
                        // Small delay to prevent overwhelming the database
                        usleep(5000); // 5ms delay
                    } catch (\Exception $e) {
                        \Log::error('Error resetting product prices in chunk: ' . $e->getMessage());
                            // Continue with next chunk instead of throwing
                    }
                });
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateProductPricesAfterDiscountUpdate($discount)
    {
        $products = $this->discountRepository->getProductsByDiscount($discount->id);

        if ($products) {
            foreach ($products as $product) {
                $discountApplied = false; // Track if discount is applied
                // Handle fixed discount type
                if ($discount->discount_value_type == 'fixed') {
                    $discount_value = max($product->price - $discount->discount_value, 0);

                    // Check if the resulting price is greater than 0 before applying the discount
                    if ($discount_value >= 0.5) {
                        if ($product->price > 0) {
                            if ($product->price > $discount->discount_value) {
                                $discount_percentage = round(floatval(($discount->discount_value / $product->price) * 100));
                            } else {
                                $discount_percentage = 100;
                            }
                        } else {
                            $discount_percentage = 0; // Handle case for zero price
                        }

                        $product->on_sale = true;
                        $product->sale_price = $discount_value;
                        $product->discount_percentage = $discount_percentage;
                        $product->discount_id = $discount->id;
                        $discountApplied = true;
                    } else {
                        $product->on_sale = false;
                        $product->sale_price =  $product->price;
                    }

                    // Handle variations
                    if ($product->variations->isNotEmpty()) {
                        foreach ($product->variations as $variation) {
                            $discount_value = max($variation->price - $discount->discount_value, 0);

                            // Apply the discount only if the resulting price is greater than 0
                            if ($discount_value >= 0.5) {
                                if ($variation->price > $discount->discount_value) {
                                    $discount_percentage = round(floatval(($discount->discount_value / $variation->price) * 100));
                                } else {
                                    $discount_percentage = 100;
                                }

                                $variation->sale_price = $discount_value;
                                $variation->discount_percentage = $discount_percentage;
                                $this->discountRepository->savevariations($variation);
                            } else {
                                $variation->sale_price = $variation->price;
                                $this->discountRepository->savevariations($variation);
                            }
                        }
                    }
                }
                // Handle percentage discount type
                else if ($discount->discount_value_type == 'percent') {
                    $discount_value = $product->price * ($discount->discount_value / 100);
                    $sale_price = $product->price - $discount_value;

                    // Apply the discount only if the resulting price is greater than 0
                    if ($sale_price >= 0.5) {
                        $product->on_sale = true;
                        $product->sale_price = $sale_price;
                        $product->discount_percentage = $discount->discount_value;
                        $product->discount_id = $discount->id;
                        $discountApplied = true;
                    }

                    // Handle variations
                    if ($product->variations->isNotEmpty()) {
                        foreach ($product->variations as $variation) {
                            $discount_value = $variation->price * ($discount->discount_value / 100);
                            $sale_price = $variation->price - $discount_value;

                            // Apply the discount only if the resulting price is greater than 0
                            if ($sale_price >= 0.5) {
                                $variation->sale_price = $sale_price;
                                $variation->discount_percentage = $discount->discount_value;
                                $this->discountRepository->savevariations($variation);
                            } else {
                                $variation->sale_price = $variation->price;
                                $this->discountRepository->savevariations($variation);
                            }
                        }
                    }
                }

                $this->discountRepository->saveProducts($product);
                $productID = $product->product_id;
                $this->_esearch->esMApping($productID);

                 // Send push notification if product is discounted
                if ($discountApplied) {
                    $this->notifyFavoriteUsers($product);
                }
            }
        }
    }

    public function getStore()
    {
        $store = auth()->user()->store;
        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }
        return $store;
    }

    private function notifyFavoriteUsers($product)
    {
        // Get all users who have favorited this product
        $favoriteUsers = $this->getFavoriteUsersByProductId($product->product_id);

        // Check if there are any users before sending notifications
        if ($favoriteUsers && $favoriteUsers->isNotEmpty()) {
            foreach ($favoriteUsers as $userId) {
                $subject = "Your favorite product {$product->title} has been discounted!";
                $url = "/product/detail/{$product->slug}";

                $this->pushNotification($userId, $subject, 'customer', $url);
            }
        }
    }
    public function getCoupons($data)
    {
        try {
        return $this->discountRepository->coupons($data);
        } catch (\Exception $e) {
             throw new Exception($e->getMessage());
        }
    }

    public function couponProduct($id)
    {
        try {
        return $this->discountRepository->getProductsByCoupon($id);
        } catch (\Exception $e) {
             throw new Exception($e->getMessage());
        }
    }
    public function customerCoupons($data)
    {
        try {
        return $this->discountRepository->storeCustomerCoupons($data);
        } catch (\Exception $e) {
             throw new Exception($e->getMessage());
        }
    }
    public function customerCouponsDashboard($data)
    {
        try {
        return $this->discountRepository->getCustomerCouponsDashboard($data);
        } catch (\Exception $e) {
             throw new Exception($e->getMessage());
        }
    }
}
