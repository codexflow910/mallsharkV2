<?php

namespace Packages\Cart\Contracts\V3;
use Packages\Product\Models\V3\ProductTier;
use Packages\Core\Contracts\V3\CoreHelpers;
use Packages\Discount\Models\V3\{Discount, DiscountApplicable, CustomerCoupon};
trait CartHelpers
{
    public static function calculateTierDetails($productId, $variationId = null, $quantity)
    {
        // 🧩 1️⃣ Fetch all tiers for product or variation
        $tiersQuery = ProductTier::orderBy('min_quantity');
        if ($variationId) {
            $tiersQuery->where('variation_id', $variationId);
        } else {
            $tiersQuery->where('product_id', $productId);
        }

        $tiers = $tiersQuery->get(['min_quantity', 'max_quantity', 'unit_price', 'shipping_price']);

        if ($tiers->isEmpty()) {
            return [
                'total_shipping' => 0,
                'total_price' => 0,
            ];
        }

        $totalShipping = 0;
        $remainingQty = $quantity;

       // 🧠 2️⃣ Get highest tier (used for overflow quantities and price)
        $maxTier = $tiers->sortByDesc('max_quantity')->first();
        $minTier = $tiers->sortBy('min_quantity')->first();

        // 🧩 3️⃣ Case: Quantity < minimum tier’s min_quantity
        if ($quantity < $minTier->min_quantity) {
            return [
                'total_shipping' => 0, // Retail purchase, no tier shipping
                'total_price' => 0,
            ];
        }
        // 🧮 3️⃣ If quantity fits within a single tier
        $currentTier = $tiers->first(function ($tier) use ($remainingQty) {
            return $remainingQty >= $tier->min_quantity && $remainingQty <= $tier->max_quantity;
        });
        if ($currentTier) {
            $totalShipping = $currentTier->shipping_price;
            $totalPrice = $currentTier->unit_price * $quantity;
            $unitPrice = $currentTier->unit_price;
        } else {
            // 🚚 4️⃣ Quantity exceeds highest tier
            $fullGroups = intdiv($remainingQty, $maxTier->max_quantity);
            $remainder = $remainingQty % $maxTier->max_quantity;

            // Add shipping for full groups
            $totalShipping += $fullGroups * $maxTier->shipping_price;

            // Add shipping for remainder (if any)
            if ($remainder > 0) {
                $remainderTier = $tiers->first(function ($tier) use ($remainder) {
                    return $remainder >= $tier->min_quantity && $remainder <= $tier->max_quantity;
                });

                $totalShipping += $remainderTier ? $remainderTier->shipping_price : $minTier->shipping_price;
            }
            // 💰 5️⃣ Price is always from last (maximum) tier
            $totalPrice = $maxTier->unit_price * $quantity;
            $unitPrice = $maxTier->unit_price;
        }
        return [
            'unit_price' => $unitPrice,
            'total_shipping' => $totalShipping,
            'total_price' => $totalPrice,
        ];
    }

    public static function calculateTierDetailsV3($productId, $variationId = null, $quantity)
    {
        // 🧩 1️⃣ Fetch all tiers for product or variation
        $tiersQuery = ProductTier::orderBy('min_quantity');
        if ($variationId) {
            $tiersQuery->where('variation_id', $variationId);
        } else {
            $tiersQuery->where('product_id', $productId);
        }

        $tiers = $tiersQuery->get(['min_quantity', 'max_quantity', 'unit_price', 'shipping_price']);

        if ($tiers->isEmpty()) {
            return [
                'total_shipping' => 0,
                'total_price' => 0,
            ];
        }

        $totalShipping = 0;
        $remainingQty = $quantity;

       // 🧠 2️⃣ Get highest tier (used for overflow quantities and price)
        $maxTier = $tiers->sortByDesc('max_quantity')->first();
        $minTier = $tiers->sortBy('min_quantity')->first();

        // 🧩 3️⃣ Case: Quantity < minimum tier’s min_quantity
        if ($quantity < $minTier->min_quantity) {
            return [
                'total_shipping' => 0, // Retail purchase, no tier shipping
                'total_price' => 0,
            ];
        }
        // 🧮 3️⃣ If quantity fits within a single tier
        $currentTier = $tiers->first(function ($tier) use ($remainingQty) {
            return $remainingQty >= $tier->min_quantity && $remainingQty <= $tier->max_quantity;
        });
        if ($currentTier) {
            $totalShipping = $currentTier->shipping_price;
            $totalPrice = $currentTier->unit_price * $quantity;
            $unitPrice = $currentTier->unit_price;
        } else {
            // 🚚 4️⃣ Quantity exceeds highest tier
            $fullGroups = intdiv($remainingQty, $maxTier->max_quantity);
            $remainder = $remainingQty % $maxTier->max_quantity;

            // Add shipping for full groups
            $totalShipping += $fullGroups * $maxTier->shipping_price;

            // Add shipping for remainder (if any)
            if ($remainder > 0) {
                $remainderTier = $tiers->first(function ($tier) use ($remainder) {
                    return $remainder >= $tier->min_quantity && $remainder <= $tier->max_quantity;
                });

                $totalShipping += $remainderTier ? $remainderTier->shipping_price : $minTier->shipping_price;
            }
            // 💰 5️⃣ Price is always from last (maximum) tier
            $totalPrice = $maxTier->unit_price * $quantity;
            $unitPrice = $maxTier->unit_price;
        }
        return [
            'unit_price' => $unitPrice,
            'total_shipping' => $totalShipping,
            'total_price' => $totalPrice,
        ];
    }
    public static function checkCouponExists($product, $userId)
    {
        
            // -----------------------------
            // 1. Resolve product/variation
            // -----------------------------
            $type = $product['product_type'];
            $storeId = $product['store_id'];
            if ($type === 'variant') {
                $itemType = 'variation';
                $itemId = $product['variation_id'];
                $unitPrice = $product['unit_original_price'];
                $quantity = $product['product_quantity'] ?? 1;
            } else {
                $itemType = 'product';
                $itemId = $product['product_id'];
                $unitPrice = $product['unit_original_price'];
                $quantity = $product['product_quantity'] ?? 1;
            }
            // -----------------------------
            // 2. CHECK SALE DISCOUNT (priority)
            // -----------------------------
            $sale = Discount::where([
                'store_id' => $storeId,
                'status' => 'active',
                'state' => 'live',
                'type' => 'sale',
                'archive' => 0
            ])->first();
            $saleActive = false;
            $unitSalePrice = null;
            $totalAfterSale = 0;
            if ($sale) {
                $saleApplicable = DiscountApplicable::where([
                    'applicable_type' => $itemType,
                    'discount_id' => $sale->id,
                    'applicable_id' => $itemId
                ])->first();

                if ($saleApplicable) {
                    $saleActive = true;
                    $saleType = $saleApplicable->custom_discount_type ?? $sale->discount_value_type;
                    $saleValue = $saleApplicable->custom_discount_value ?? $sale->discount_value;
                }

                // Apply sale to unit price
                if ($saleActive) {
                    $unitSalePrice = $product['unit_sale_price'];
                }
            }

            

            // -----------------------------
            // 3. CHECK USER-SPECIFIC COUPON
            // -----------------------------
            $couponActive = false;
            $couponDiscountValue = 0;
            $couponDiscountType = null;
            $couponId = null;
            $couponCode = null;
            $couponCollected = false;
            if ($userId) {
                $userCouponIds = CustomerCoupon::where('user_id', $userId)
                    ->where('status', 1)
                    ->pluck('coupon_id');

                if ($userCouponIds->isNotEmpty()) {
                    $coupon = Discount::whereIn('id', $userCouponIds)
                        ->where('store_id', $storeId)
                        ->where('status', 'active')
                        ->where('state', 'live')
                        ->where('type', 'coupon')
                        ->first();
                    if ($coupon) {
                        // Check quota / one-use-per-customer
                        $alreadyUsed = CoreHelpers::isCouponUsageExceeded($coupon, $userId);
                        if (!$alreadyUsed) {
                            // Check if product/variation exists in DiscountApplicable for this coupon
                            $applicable = DiscountApplicable::where([
                                'applicable_type' => $itemType,
                                'discount_id' => $coupon->id,
                                'applicable_id' => $itemId
                            ])->exists();

                            if ($applicable) {
                                // Fetch coupon discount from default Discount table
                                $couponDiscountType = $coupon->discount_value_type;
                                $couponDiscountValue = $coupon->discount_value;
                                $couponActive = true;
                                $couponId = $coupon->id;
                                $couponCode = $coupon->code;
                                $couponCollected = true;
                            }
                        }
                    }
                }
            }

            // -----------------------------
            // 4. APPLY COUPON ON TOTAL AFTER SALE
            // -----------------------------
            $effectiveUnitPrice = ($unitSalePrice ?? $unitPrice) * $quantity;
            $finalTotal = $unitSalePrice;
            if ($couponCollected && $couponDiscountValue !== null) {
                $finalTotal = $couponDiscountType === 'percent'
                    ? $effectiveUnitPrice - ($effectiveUnitPrice * ($couponDiscountValue / 100))
                    : $effectiveUnitPrice - $couponDiscountValue;

                $finalTotal = max($finalTotal, 0);
            }
            if (!$couponCollected) {
                $coupon = Discount::where([
                    'store_id' => $storeId,
                    'status' => 'active',
                    'state' => 'live',
                    'type' => 'coupon',
                    'archive' => 0
                ])->first();

                if ($coupon) {
                    $couponApplicable = DiscountApplicable::where([
                        'applicable_type' => $itemType,
                        'discount_id' => $coupon->id,
                        'applicable_id' => $itemId
                    ])->first();

                    if ($couponApplicable) {
                        $couponActive = true;
                        $couponId = $coupon->id;
                    }
                }
            }
            // -----------------------------
            // 5. PREPARE FINAL RESULT
            // -----------------------------
            $final = [
                'on_sale' => $saleActive ? 1 : 0,
                'coupon' => $couponActive ? 1 : 0,
                'discount_id' => $saleActive ? $sale->id : null,
                'coupon_id' => $couponId,
                'coupon_collected_code' => $couponCode,
                'coupon_collected' => $couponCollected ? 1 : 0,
                'unit_original_price' => $unitPrice,
                'unit_sale_price' => round($unitSalePrice, 2),
                'coupon_unit_price' => $couponCollected ? round($finalTotal / $quantity, 2) : 0,
                'total_actual_price' => round($unitPrice * $quantity, 2),
                'total_sale_price' => round($unitSalePrice * $quantity, 2),
                'total_coupon_price' => $couponCollected ? round($finalTotal, 2) : 0,
            ];
            // dd($product,$final);
            return array_merge((array)$product, $final);
    }
}


   