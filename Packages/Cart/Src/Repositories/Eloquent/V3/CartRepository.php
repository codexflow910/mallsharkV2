<?php

namespace Packages\Cart\Repositories\Eloquent\V3;

use Exception;
use Google\Service\CloudControlsPartnerService\Customer;
use Illuminate\Support\Facades\DB;
use Packages\Cart\Models\V3\ProductCart;
use Packages\Cart\Models\V3\CartItemCustomization;
use Packages\Cart\Models\V3\CustomImage;
use Packages\Product\Models\V3\ProductVariation;
use Packages\Cart\Contracts\V3\CartRepositoryInterface;
use Packages\Discount\Models\V3\CustomerCoupon;
use Packages\Discount\Models\V3\Discount;
use Packages\Product\Models\V3\Product;

class CartRepository implements CartRepositoryInterface
{

    protected $model;
    protected $productVariationModel;

    public function __construct(ProductCart $model, ProductVariation $productVariationModel)
    {
        $this->model = $model;
        $this->productVariationModel = $productVariationModel;
    }
    public function saveToCart($data)
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            throw new Exception('Failed to save cart item: ' . $e->getMessage());
        }
    }


    public function getCartProduct($id, $isGuest, $productId, $variationId = null)
    {
        $query = $this->model->where('product_id', $productId);

        if ($variationId) {
            $query->where('variation_id', $variationId);
        }

        if ($isGuest) {
            $query->where('random_key', $id);
        } else {
            $query->where('user_id', $id);
        }

        return $query->first();
    }

    /**
     * Check if a cart item has the exact same configuration (styles/options)
     */
    public function matchesConfiguration($cartItemId, array $textStyles)
    {
        if (empty($textStyles)) {
            return CartItemCustomization::where('cart_item_id', $cartItemId)->count() === 0;
        }
        $incomingGroups = [];
        if (!empty($textStyles) && array_keys($textStyles) === range(0, count($textStyles) - 1)) {
            // indexed array; check first element shape
            $first = $textStyles[0];
            if (is_array($first) && isset($first['styles'])) {
                $incomingGroups = $textStyles; // already grouped
            } else {
                // flat list -> single group
                $incomingGroups = [ ['styles' => $textStyles] ];
            }
        }

        // Fetch existing rows for the cart item as groups
        $existingRows = CartItemCustomization::where('cart_item_id', $cartItemId)->get();
        $existingGroups = $existingRows->map(function ($r) {
            return [
                'styles' => $r->styles ?? [],
                'custom_text' => $r->custom_text ?? null,
            ];
        })->toArray();

        // Compare by checking that every incoming group has an equivalent existing group
        $normalize = function ($group) {
            $styles = $group['styles'] ?? [];
            if (is_array($styles)) {
                ksort($styles);
            }
            return [
                'styles_json' => json_encode($styles),
                'custom_text' => isset($group['custom_text']) ? trim(mb_strtolower((string)$group['custom_text'])) : null,
            ];
        };

        $incomingNorm = array_map($normalize, $incomingGroups);
        $existingNorm = array_map($normalize, $existingGroups);

        // every incoming group must exist in existing groups (multiset semantics)
        $counts = [];
        foreach ($existingNorm as $e) {
            $key = $e['styles_json'] . '||' . ($e['custom_text'] ?? '');
            if (!isset($counts[$key])) $counts[$key] = 0;
            $counts[$key]++;
        }
        foreach ($incomingNorm as $inc) {
            $key = $inc['styles_json'] . '||' . ($inc['custom_text'] ?? '');
            if (!isset($counts[$key]) || $counts[$key] <= 0) {
                return false;
            }
            $counts[$key]--;
        }
        return true;
    }


    public function getVariationsInfo(array $options, int $productId)
    {
        $query = $this->productVariationModel->where('product_id', $productId);

        // Dynamically apply filters
        if (!empty($options[0])) {
            $query->where('option1', $options[0]);
        }

        if (!empty($options[1])) {
            $query->where('option2', $options[1]);
        }

        if (!empty($options[2])) {
            $query->where('option3', $options[2]);
        }

        return $query->first();
    }

    public function getVariation($id)
    {
        return $this->productVariationModel->find($id);
    }

    public function updateQuantity(array $conditions, $quantity)
    {
        return $this->model->where($conditions)->update(['product_quantity' => $quantity]);
    }

    public function getCartItem(array $conditions)
    {
        return $this->model->where($conditions)->first();
    }

    public function deleteCart($cartId)
    {
        return $this->model->where('cart_id', $cartId)->delete();
    }
    public function deleteCartV3($cartId, $customizationId, $type)
    {
        $textCount  = CartItemCustomization::where('cart_item_id', $cartId)->count();
        $imageCount = CustomImage::where('cart_item_id', $cartId)->count();

        $total = $textCount + $imageCount;

        if ($total > 1) {
            if ($type === 'text') {
                CartItemCustomization::where('id', $customizationId)->delete();
            } else {
                CustomImage::where('id', $customizationId)->delete();
            }
        } else {
            if ($type === 'text') {
                CartItemCustomization::where('id', $customizationId)->delete();
            } else {
                CustomImage::where('id', $customizationId)->delete();
            }

            $this->model->where('cart_id', $cartId)->delete();
        }

        return true;
    }


   public function toggleCart($cartId, $isCheck, $type, $customizationId)
    {
        // SIMPLE PRODUCT (no customization_id)
        if (empty($customizationId)) {
            return $this->model
                ->where('cart_id', $cartId)
                ->update(['is_check' => $isCheck]);
        }

        // CUSTOM PRODUCT
        if ($type === 'text') {
            $customization = CartItemCustomization::where('id', $customizationId)->first();
        } else {
            $customization = CustomImage::where('id', $customizationId)->first();
        }

        if (!$customization) {
            throw new Exception('Customization not found');
        }

        // Update only this customization
        $customization->update(['is_check' => $isCheck]);

        $cartId = $customization->cart_item_id;

        // Sync cart checkbox
        $anyCheckedText = CartItemCustomization::where('cart_item_id', $cartId)
            ->where('is_check', 1)
            ->exists();

        $anyCheckedImage = CustomImage::where('cart_item_id', $cartId)
            ->where('is_check', 1)
            ->exists();

        $shouldCartBeChecked = $anyCheckedText || $anyCheckedImage;

        $this->model->where('cart_id', $cartId)
            ->update(['is_check' => $shouldCartBeChecked ? 1 : 0]);

        return true;
    }


    /**
     * Save style/option rows for a cart item
     */
    public function saveCartItemStyles($cartItemId, array $textStyles)
    {
        if (empty($textStyles)) {
            return true;
        }

        DB::transaction(function () use ($cartItemId, $textStyles) {
            foreach ($textStyles as $group) {
                $styles = $group['styles'] ?? [];
                // Normalize: decode if it's a JSON string
                if (is_string($styles)) {
                    $decoded = json_decode($styles, true);
                    $styles = is_array($decoded) ? $decoded : [];
                }

                // Map styles into key => value format
                $stylesMap = [];
                if (!empty($styles) && isset($styles[0]) && (isset($styles[0]['style_id']) || isset($styles[0]['option_value']))) {
                    foreach ($styles as $s) {
                        $key = $s['style_key'] ?? (isset($s['style_id']) ? (string)$s['style_id'] : null);
                        if ($key !== null) {
                            $stylesMap[$key] = $s['option_value'] ?? ($s['option_id'] ?? null);
                        }
                    }
                } else {
                    $stylesMap = $styles;
                }

                // Create customization
                CartItemCustomization::create([
                    'cart_item_id' => $cartItemId,
                    'composite_key' => $group['composite_key'] ?? null,
                    'styles' => $stylesMap, // ✅ Always store as array, Eloquent will JSON-encode it correctly
                    'price' => $group['price'] ?? 0,
                    'custom_text' => $group['custom_text'] ?? null,
                    'instructions' => $group['instructions'] ?? null,
                    'product_quantity' => isset($group['product_quantity']) ? (int)$group['product_quantity'] : 1,
                ]);
            }
        });

        return true;
    }


    /**
     * Delete all styles for a cart item
     */
    public function deleteCartItemStyles($cartItemId)
    {
        return CartItemCustomization::where('cart_item_id', $cartItemId)->delete();
    }

    public function getCart($data)
    {
        $query = $this->model->with(['product', 'variation', 'store', 'product.store','customImages', 'cartItemCustomizations']);
        // dd($query);
        if ($data['guest'] === false && !empty($data['id'])) {
            $query->where('user_id', $data['id']);
        } elseif ($data['guest'] === true && !empty($data['id'])) {
            $query->where('random_key', $data['id']);
        }
         return $query->get(); 
    }

    public function getDiscountForUser($userId, $cartItems)
    {
        // 1. Get user's coupons
        $userCoupons = CustomerCoupon::where('user_id', $userId)
            ->pluck('coupon_id');

        if ($userCoupons->isEmpty()) {
            return [];
        }

        // 2. Get discounts with applicables
        $discounts = Discount::with('discountApplicables')
            ->whereIn('id', $userCoupons)
            ->where('type', 'coupon')
            ->where('status', 'active')
            ->where('state', 'live')
            ->get();

        if ($discounts->isEmpty()) {
            return [];
        }

        $results = [];

        foreach ($discounts as $discount) {

            $discountType  = $discount->discount_value_type;
            $discountValue = $discount->discount_value;

            $applicableProductIds = $discount->discountApplicables
                ->where('applicable_type', 'product')
                ->pluck('applicable_id')
                ->toArray();

            $applicableVariationIds = $discount->discountApplicables
                ->where('applicable_type', 'variation')
                ->pluck('applicable_id')
                ->toArray();

            $appliedOnce = false; // <-- For one_use_per_customer

            foreach ($cartItems as $item) {

                if ($discount->one_use_per_customer && $appliedOnce) {
                    break; // apply only to 1 item
                }

                $productId   = $item->product_id;
                $variationId = $item->variation_id;

                // Check applicable (match product_id OR variation_id)
                $appliesToProduct   = in_array($productId, $applicableProductIds);
                $appliesToVariation = $variationId && in_array($variationId, $applicableVariationIds);

                if (!$appliesToProduct && !$appliesToVariation) {
                    continue;
                }

                // Get customizations and custom images for this cart item
                $customizations = CartItemCustomization::where('cart_item_id', $item->cart_id)->get();
                $customImages = CustomImage::where('cart_item_id', $item->cart_id)->get();

                // Price
                $price = $variationId
                    ? ProductVariation::where('variation_id', $variationId)->value('price')
                    : Product::where('product_id', $productId)->value('price');

                // If has customizations, create separate result for each
                if ($customizations->isNotEmpty()) {
                    foreach ($customizations as $customization) {
                        $quantity = (int)$customization->product_quantity;
                        $totalActual = $price * $quantity;

                        // Apply discount
                        $totalSale = match ($discountType) {
                            'fixed'   => max(0, $totalActual - $discountValue),
                            'percent' => $totalActual - ($totalActual * ($discountValue / 100)),
                            default   => $totalActual,
                        };

                        $results[] = [
                            'product_id'        => $productId,
                            'variation_id'      => $variationId,
                            'customization_id'  => $customization->id,
                            'type'              => 'text',
                            'price'             => $price,
                            'sale_price'        => round($totalSale / $quantity, 2),
                            'discount_id'       => $discount->id,
                            'coupon_code'       => $discount->code,
                            'one_use_per_customer' => $discount->one_use_per_customer,
                            'total_actual_price'=> round($totalActual, 2),
                            'total_sale_price'  => round($totalSale, 2),
                            'product_variation_sale_price' => $variationId ? round($totalSale / $quantity, 2) : 0
                        ];

                        if ($discount->one_use_per_customer) {
                            $appliedOnce = true;
                            break;
                        }
                    }
                }
                // If has custom images, create separate result for each
                if ($customImages->isNotEmpty()) {
                    foreach ($customImages as $customImage) {
                        $quantity = (int)$customImage->product_quantity;
                        $totalActual = $price * $quantity;

                        // Apply discount
                        $totalSale = match ($discountType) {
                            'fixed'   => max(0, $totalActual - $discountValue),
                            'percent' => $totalActual - ($totalActual * ($discountValue / 100)),
                            default   => $totalActual,
                        };

                        $results[] = [
                            'product_id'        => $productId,
                            'variation_id'      => $variationId,
                            'customization_id'  => $customImage->id,
                            'type'              => 'image',
                            'price'             => $price,
                            'sale_price'        => round($totalSale / $quantity, 2),
                            'discount_id'       => $discount->id,
                            'coupon_code'       => $discount->code,
                            'one_use_per_customer' => $discount->one_use_per_customer,
                            'total_actual_price'=> round($totalActual, 2),
                            'total_sale_price'  => round($totalSale, 2),
                            'product_variation_sale_price' => $variationId ? round($totalSale / $quantity, 2) : 0
                        ];

                        if ($discount->one_use_per_customer) {
                            $appliedOnce = true;
                            break;
                        }
                    }
                }
                // Regular product without customizations
                if ($customizations->isEmpty() && $customImages->isEmpty()) {
                    $quantity = $item->product_quantity;
                    $totalActual = $price * $quantity;

                    // Apply discount
                    $totalSale = match ($discountType) {
                        'fixed'   => max(0, $totalActual - $discountValue),
                        'percent' => $totalActual - ($totalActual * ($discountValue / 100)),
                        default   => $totalActual,
                    };

                    $results[] = [
                        'product_id'        => $productId,
                        'variation_id'      => $variationId,
                        'customization_id'  => null,
                        'type'              => null,
                        'price'             => $price,
                        'sale_price'        => round($totalSale / $quantity, 2),
                        'discount_id'       => $discount->id,
                        'coupon_code'       => $discount->code,
                        'one_use_per_customer' => $discount->one_use_per_customer,
                        'total_actual_price'=> round($totalActual, 2),
                        'total_sale_price'  => round($totalSale, 2),
                        'product_variation_sale_price' => $variationId ? round($totalSale / $quantity, 2) : 0
                    ];

                    if ($discount->one_use_per_customer) {
                        $appliedOnce = true;
                    }
                }
            }
        }
        
        return $results;
    }

    /**
     * Get custom cart product by matching product, variation, and custom data
     */
    public function getCustomCartProduct($id, $isGuest, $productId, $variationId = null, $compositeKey = null, $customText = null, array $textStyles = null, array $imageStyles = null)
    {
        $query = $this->model->where('product_id', $productId);
        if ($variationId) {
            $query->where('variation_id', $variationId);
            }
            
            if ($isGuest) {
                $query->where('random_key', $id);
                } else {
                    $query->where('user_id', $id);
                    }      
                    if ((!empty($textStyles['styles']) && is_array($textStyles))) {
            $baseQuery = clone $query;

            $candidates = (clone $baseQuery)->get();
            foreach ($candidates as $cartItem) {
                $customizationQuery = CartItemCustomization::where('cart_item_id', $cartItem->cart_id);
                if (!empty($compositeKey)) {
                    $customizationQuery->where('composite_key', $compositeKey);
                }
                if ($customText !== null) {
                    $customizationQuery->where('custom_text', trim($customText));
                }
                if ($customizationQuery->exists() && $this->matchesConfiguration($cartItem->cart_id, $textStyles)) {
                    return $cartItem;
                }
            }
            return null;
        } elseif (!empty($imageStyles) && is_array($imageStyles)) {
            $baseQuery = clone $query;

            $candidates = (clone $baseQuery)->get();
            foreach ($candidates as $cartItem) {
                if ($this->matchesImagesConfiguration($cartItem->cart_id, $imageStyles)) {
                    return $cartItem;
                }
            }

            return null;
        }

        return $query->first();
    }

    /**
     * Save custom cart item with styles
     */
    public function saveCustomToCart($data, array $textStyles = [])
    {
        try {
            return DB::transaction(function () use ($data, $textStyles) {
                $cartItem = $this->model->create($data);

                if (!empty($textStyles) && is_array($textStyles)) {
                    $this->saveCartItemStyles($cartItem->cart_id, $textStyles);
                }

                return $cartItem;
            });
        } catch (Exception $e) {
            throw new Exception('Failed to save custom cart item: ' . $e->getMessage());
        }
    }

    /**
     * Save custom images for a cart item.
     * Replaces any existing images for the cart item.
     *
     * @param int $cartItemId
     * @param array $imageStyles
     * @return bool
     */
    public function saveCustomImages(int $cartItemId, array $imageStyles)
    {
        if (empty($imageStyles)) {
            return true;
        }

        DB::transaction(function () use ($cartItemId, $imageStyles) {
            foreach ($imageStyles as $img) {
                $fileName = $img['file_name'] ?? $img['image_id'] ?? $img['id'] ?? null;
                $price = isset($img['price']) ? (float)$img['price'] : 0;
                $instructions = $img['instructions'] ?? null;
                $quantity = isset($img['product_quantity']) ? (int)$img['product_quantity'] : 1;
                if ($quantity < 1) {
                    $quantity = 1;
                }
                if (empty($fileName)) {
                    continue;
                }
                $existing = CustomImage::where('cart_item_id', $cartItemId)
                    ->where('file_name', $fileName)
                    ->first();
                if ($existing) {
                    $current = (int)$existing->product_quantity ?: 1;
                    $desired = $current + $quantity;
                    if ($desired > 5) {
                        $desired = 5;
                    }
                    $existing->product_quantity = $desired;
                    $existing->price = $price; // Optionally update price/instructions
                    $existing->instructions = $instructions;
                    $existing->save();
                } else {
                    if ($quantity > 5) {
                        $quantity = 5;
                    }
                    CustomImage::create([
                        'cart_item_id' => $cartItemId,
                        'file_name' => $fileName,
                        'price' => $price,
                        'instructions' => $instructions,
                        'product_quantity' => $quantity,
                    ]);
                }
            }
        });

        return true;
    }

    /**
     * Compare existing custom_images rows for a cart item with incoming image styles.
     * Returns true if they match exactly (order-insensitive by image_id/image_path).
     *
     * @param int $cartItemId
     * @param array $imageStyles
     * @return bool
     */
    public function matchesImagesConfiguration(int $cartItemId, array $imageStyles): bool
    {
        $existing = CustomImage::where('cart_item_id', $cartItemId)
            ->get(['file_name'])
            ->map(function ($r) {
                return [
                    'file_name' => is_null($r->file_name) ? null : trim(mb_strtolower($r->file_name)),
                ];
            })
            ->values()
            ->toArray();

        $incoming = collect($imageStyles)
            ->map(function ($i) {
                return [
                    'file_name' => isset($i['file_name']) ? trim(mb_strtolower((string)$i['file_name'])) : (isset($i['image_id']) ? trim(mb_strtolower((string)$i['image_id'])) : null),
                ];
            })
            ->filter(function ($i) {
                return !empty($i['file_name']);
            })
            ->values()
            ->toArray();

        // Quick length check
        if (count($existing) !== count($incoming)) {
            return false;
        }

        // Compare as multisets (order-insensitive): count occurrences of serialized entries
        $serializeList = function ($arr) {
            $counts = [];
            foreach ($arr as $it) {
                $key = json_encode($it);
                if (!isset($counts[$key])) {
                    $counts[$key] = 0;
                }
                $counts[$key]++;
            }
            return $counts;
        };

        return $serializeList($existing) === $serializeList($incoming);
    }

    /**
     * Normalize an incoming styles array for stable comparisons and inserts
     *
     * @param array $styles
     * @return array
     */
    private function normalizeStyles(array $styles): array
    {
        return collect($styles)
            ->filter(function ($s) {
                return isset($s['style_id']) && (isset($s['option_value']) || isset($s['option_id']));
            })
            ->map(function ($s) {
                $value = isset($s['option_value']) ? (string)$s['option_value'] : (string)($s['option_id'] ?? '');
                return [
                    'style_id' => (int)$s['style_id'],
                    'option_value' => trim(mb_strtolower($value)),
                ];
            })
            ->sortBy('style_id')
            ->values()
            ->toArray();
    }
}
