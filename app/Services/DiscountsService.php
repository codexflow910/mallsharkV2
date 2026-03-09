<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Discount;
use App\Models\DiscountUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Repositories\Discounts\Discountsaccess;
use App\Http\Controllers\ElasticSearchController;

class DiscountsService
{
    private $reportRepo;
    private $_esearch;

    public function __construct(Discountsaccess $reportRepo)
    {
        $this->reportRepo = $reportRepo;
        $this->_esearch = new ElasticSearchController();
    }
    public function fetchProductsWithVariation($userId)
    {
        $store = getStoreInfoByUserid($userId);
        $product = $this->reportRepo->fetchProductsWithVariation($store);
        return $product;
    }

    public function getCategories($userId, $sub)
    {
        $store = getStoreInfoByUserid($userId);
        return $this->reportRepo->getAllSubCategories($store, $sub);
    }

    public function saveDiscount($data, $storeId)
    {

        $discountData = [
            'store_id' => $storeId,
            'code' => $data['discountCode'] ?? null,
            'title' => $data['autoDiscountTitle'] ?? null,
            'type' => $data['discountActive'] == 'Sales' ? 'sale' : 'coupon',
            'platform_wide' => false,
            'discount_value_type' => $data['discount_type'] == 'percentage' ? 'percent' : 'fixed',
            'discount_value' => $data['discount_type'] == 'percentage' ? $data['percentage_value'] : $data['fixed_value'],
            'minimum_purchase_amount' => $data['min_purchase_amount_value'] ?? null,
            'maximum_purchase_amount' => $data['max_purchase_amount_value'] ?? null,
            'minimum_quantity_items' => $data['min_quantity_value'] ?? null,
            'maximum_quantity_items' => $data['max_quantity_value'] ?? null,
            'start_time' => $data['start_date'] . ' ' . ($data['start_time'] ?? '00:00:00'),
            'end_time' => $data['end_date'] . ' ' . ($data['end_time'] ?? '23:59:59'),
            'status' => 'inActive',
            'state' => 'default'
        ];
        if (isset($data['one_use_per_customer'])) {
            $discountData['one_use_per_customer'] = $data['one_use_per_customer'] == 'on' ? 1 : 0;
        } else {
            $discountData['one_use_per_customer'] = null;
        }
        if (isset($data['totalUses'])) {
            $discountData['max_usage_limit'] = $data['totalUses'];
        } else {
            $discountData['max_usage_limit'] = null;
        }

        $discount = $this->reportRepo->saveDiscount($discountData);

        if ($data['discount_applicable'] != 'wholeInventory') {
            $applicableData = [
                'discount_id' => $discount->id,
                'applicable_type' => $data['discount_applicable'] == 'product' ? 'App\Models\Product' : 'App\Models\Category',
                'applicable_id' => $data['applicable_id'], // This should be provided in the request data
                'excluded' => false,
            ];
            $this->reportRepo->saveDiscountApplicable($applicableData);
        }


        return $discount;
    }

    public function updateDiscount($data, $id)
    {
        $discountData = [
            'code' => $data['discountCode'] ?? null,
            'title' => $data['autoDiscountTitle'] ?? null,
            'type' => $data['discountActive'] == 'Sales' ? 'sale' : 'coupon',
            'platform_wide' => false,
            'discount_value_type' => $data['discount_type'] == 'percentage' ? 'percent' : 'fixed',
            'discount_value' => $data['discount_type'] == 'percentage' ? $data['percentage_value'] : $data['fixed_value'],
            'minimum_purchase_amount' => $data['min_purchase_amount_value'] ?? null,
            'maximum_purchase_amount' => $data['max_purchase_amount_value'] ?? null,
            'minimum_quantity_items' => $data['min_quantity_value'] ?? null,
            'maximum_quantity_items' => $data['max_quantity_value'] ?? null,
            'start_time' => $data['start_date'] . ' ' . ($data['start_time'] ?? '00:00:00'),
            'end_time' => $data['end_date'] . ' ' . ($data['end_time'] ?? '23:59:59'),
        ];

        // return $discountData;
        if (isset($data['totalUsesCheck'])) {
            $discountData['max_usage_limit'] = $data['totalUses'];
        } else {
            $discountData['max_usage_limit'] = null;
        }
        if (isset($data['one_use_per_customer'])) {
            $discountData['one_use_per_customer'] = $data['one_use_per_customer'] == 'on' ? 1 : 0;
        } else {
            $discountData['one_use_per_customer'] = 0;
        }

        $discount = $this->reportRepo->updateDiscount($discountData, $id);
        // return $discount;
        if ($data['discount_applicable'] != 'wholeInventory') {
            $applicableData = [
                'discount_id' => $discount->id,
                'applicable_type' => $data['discount_applicable'] == 'product' ? 'App\Models\Product' : 'App\Models\Category',
                'applicable_id' => $data['applicable_id'],
                'excluded' => false,
            ];
            $this->reportRepo->updateDiscountApplicable($applicableData, $id);
        }

        return $discount;
    }

    public function updateProductPricesAfterDiscountUpdate($discount)
    {
        $products = Product::where('discount_id', $discount->id)->with('variations')->get();

        if ($products) {
            foreach ($products as $product) {
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
                                $variation->save();
                            } else {
                                $variation->sale_price = $variation->price;
                                $variation->save();
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
                                $variation->save();
                            } else {
                                $variation->sale_price = $variation->price;
                                $variation->save();
                            }
                        }
                    }
                }

                $product->save();
                $productID = $product->product_id;
                $this->_esearch->esMApping($productID);
            }
        }
    }


    public function resetProductPrices($store_id, $discount_id)
    {
        $products = Product::where('store_id', $store_id)->where('discount_id', $discount_id)->with('variations')->get();
        if ($products->count() > 0) {
            foreach ($products as $product) {
                $product->on_sale = false;
                $product->discount_id = null;
                $product->sale_price = null;
                $product->discount_percentage = 0;
                if ($product->variations->isNotEmpty()) {
                    foreach ($product->variations as $variation) {
                        $variation->sale_price = null;
                        $variation->discount_percentage = 0;
                        $variation->save();
                    }
                }
                $product->save();
                $productID = $product->product_id;
                $this->_esearch->esMApping($productID);
            }
        }
    }

    public function updateProductPrices($store_id, $discount)
    {
        if ($discount->type == 'sale') {

            $products = Product::where('store_id', $store_id)->with('variations')->get();

            foreach ($products as $product) {


                // Handle fixed discount type
                if ($discount->discount_value_type == 'fixed') {
                    $discount_value = $product->price - $discount->discount_value;
                    // Check if the discounted price is greater than 0
                    if ($discount_value >= 0.5) {
                        // Prevent division by zero and calculate discount percentage
                        if ($product->price > 0) {
                            if ($product->price > $discount->discount_value) {
                                $discount_percentage = round(floatval(($discount->discount_value / $product->price) * 100));
                            } else {
                                $discount_percentage = 100;
                            }
                        } else {
                            $discount_percentage = 0; // Handle case for zero price
                        }

                        // Set sale price and discount percentage
                        $product->sale_price = $discount_value;
                        $product->discount_percentage = $discount_percentage;
                        $product->on_sale = true;
                        $product->discount_id = $discount->id;
                    } else {
                        $product->sale_price = $product->price;
                        $product->on_sale = false;
                    }
                    // Handle product variations
                    if ($product->variations->isNotEmpty()) {
                        foreach ($product->variations as $variation) {
                            $discount_value = $variation->price - $discount->discount_value;

                            // Apply the discount only if the resulting price is greater than 0
                            if ($discount_value >= 0.5) {
                                if ($variation->price > 0) {
                                    if ($variation->price > $discount->discount_value) {
                                        $discount_percentage = round(floatval(($discount->discount_value / $variation->price) * 100));
                                    } else {
                                        $discount_percentage = 100;
                                    }
                                } else {
                                    $discount_percentage = 0; // Handle case for zero price
                                }

                                // Set sale price and discount percentage for the variation
                                $variation->sale_price = $discount_value;
                                $variation->discount_percentage = $discount_percentage;
                                $variation->save();
                            } else {
                                $variation->sale_price = $variation->price;
                                $variation->save();
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
                        $product->sale_price = $sale_price;
                        $product->discount_percentage = $discount->discount_value;
                        $product->on_sale = true;
                        $product->discount_id = $discount->id;
                    } else {
                        $product->sale_price = $product->price;
                        $product->on_sale = false;
                    }
                    // Handle product variations
                    if ($product->variations->isNotEmpty()) {
                        foreach ($product->variations as $variation) {
                            $discount_value = $variation->price * ($discount->discount_value / 100);
                            $sale_price = $variation->price - $discount_value;

                            // Apply the discount only if the resulting price is greater than 0
                            if ($sale_price >= 0.5) {
                                $variation->sale_price = $sale_price;
                                $variation->discount_percentage = $discount->discount_value;
                                $variation->save();
                            } else {
                                $variation->sale_price = $variation->price;
                                $variation->save();
                            }
                        }
                    }
                }

                $product->save();
                $productID = $product->product_id;
                $this->_esearch->esMApping($productID);
            }
        } elseif ($discount->type == 'coupon') {
          
                $products = Product::where('store_id', $store_id)->with('variations')->get();
                if ($products->count() > 0) {
                    foreach ($products as $product) {
                        $product->on_sale = false;
                        $product->discount_id = null;
                        $product->sale_price = null;
                        $product->discount_percentage = 0;
                        if ($product->variations->isNotEmpty()) {
                            foreach ($product->variations as $variation) {
                                $variation->sale_price = null;
                                $variation->discount_percentage = 0;
                                $variation->save();
                            }
                        }
                        $product->save();
                        $productID = $product->product_id;
                        $this->_esearch->esMApping($productID);
                    }
                }
        }
    }

    public function applyCoupon($couponCode, $storeId, $cartTotal, $cartQuantity)
    {
        // Check if coupon exists
        $coupon = Discount::where('code', $couponCode)->first();
        if (!$coupon) {
            return ['success' => false, 'message' => 'Coupon does not exist.'];
        }

        // Check if coupon is platform-wide or store-specific
        if ($coupon->store_id !== (int)$storeId) {
            return ['success' => false, 'message' => 'Coupon is not valid for this store.'];
        }

        // Check coupon status and state
        if ($coupon->status !== 'active' || $coupon->state !== 'live') {
            return ['success' => false, 'message' => 'Coupon is not active.'];
        }

        // Check usage limits
        if ($coupon->usage_count >= $coupon->max_usage_limit) {
            return ['success' => false, 'message' => 'Coupon usage limit has been reached.'];
        }

        // Check if one use per customer
        $userId = Session::get('userData')['user_id'] ?? null;
        if($userId){
            $userHasUsed = DiscountUsage::where('user_id', $userId)
                                        ->where('discount_id', $coupon->id)
                                        ->exists();
            if ($coupon->one_use_per_customer && $userHasUsed) {
                return ['success' => false, 'message' => 'You have already used this coupon.'];
            }
        } else {
                return ['success' => false, 'message' => 'Please login first.'];
        }

        // Validate minimum and maximum purchase amounts if set
        if (!is_null($coupon->minimum_purchase_amount) && $cartTotal < $coupon->minimum_purchase_amount) {
            return ['success' => false, 'message' => "Minimum purchase amount is {$coupon->minimum_purchase_amount}."];
        }
        if (!is_null($coupon->maximum_purchase_amount) && $cartTotal > $coupon->maximum_purchase_amount) {
            return ['success' => false, 'message' => "Maximum purchase amount is {$coupon->maximum_purchase_amount}."];
        }

        // Validate minimum and maximum purchase quantity if set
        if (!is_null($coupon->minimum_quantity_items) && $cartQuantity < $coupon->minimum_quantity_items) {
            return ['success' => false, 'message' => "Minimum purchase quantity is {$coupon->minimum_quantity_items} items."];
        }
        if (!is_null($coupon->maximum_quantity_items) && $cartQuantity > $coupon->maximum_quantity_items) {
            return ['success' => false, 'message' => "Maximum purchase quantity is {$coupon->maximum_quantity_items} items."];
        }

        // Calculate discount amount
        $discountAmount = 0;
        if ($coupon->discount_value_type === 'percent') {
            $discountAmount = ($cartTotal * $coupon->discount_value) / 100;
        } else {
            $discountAmount = $coupon->discount_value;
        }

        // Apply discount to cart total
        $cartTotal -= $discountAmount;

        // Return success response data
        return [
            'success' => true,
            'discountAmount' => $discountAmount,
            'cartTotal' => $cartTotal
        ];
    }
}
