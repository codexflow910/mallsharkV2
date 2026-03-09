<?php

namespace Packages\Cart\Services\V4;

use Exception;
use Packages\Cart\Contracts\V4\CartHelpers;
use Packages\Core\Contracts\V4\CoreHelpers;
use Packages\Product\Config\ProductConstants;
use Illuminate\Support\ItemNotFoundException;
use Packages\Cart\Repositories\Eloquent\V4\CartRepository;
use Packages\Klavio\Services\V4\KlavioService;
use Packages\Auth\Models\V4\User;
use Packages\Discount\Models\V4\{Discount,DiscountApplicable};
use Packages\Product\Models\V4\ProductTier;
use Packages\Cart\Models\V4\CartItemCustomization;
use Packages\Cart\Models\V4\CustomImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class CartService
{
    use CartHelpers;
    use CoreHelpers;

    protected $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Persist image styles for a cart item.
     *
     * @param int $cartItemId
     * @param array $imageStyles
     * @return void
     */
   private function storeImageStyles(int $cartItemId, array $imageStyles, ?int $defaultQuantity = null): void
    {
        // Normalize basic shape and delegate to repository
        $images = collect($imageStyles)
            ->map(function ($img) {
                $qty = isset($img['product_quantity']) ? (int)$img['product_quantity'] : null;
                return [
                    'file_name' => $img['file_name'] ?? null,
                    'price' => isset($img['price']) ? (float)$img['price'] : 0,
                    'instructions' => $img['instructions'] ?? null,
                    'product_quantity' => $qty,
                ];
            })
            ->filter(function ($i) {
                return !empty($i['file_name']);
                })
                ->values()
                ->toArray();

        $fallbackQty = $defaultQuantity !== null ? (int)$defaultQuantity : 1;
        if ($fallbackQty < 1) {
            $fallbackQty = 1;
        }
        if ($fallbackQty > 5) {
            $fallbackQty = 5;
        }

        foreach ($images as $idx => $img) {
            $q = isset($img['product_quantity']) ? (int)$img['product_quantity'] : 0;
            if ($q < 1) {
                $q = $fallbackQty;
            }
            if ($q > 5) {
                $q = 5;
            }
            $images[$idx]['product_quantity'] = $q;
        }

        if (empty($images)) {
            return;
        }

        $this->cartRepository->saveCustomImages($cartItemId, $images);
    }

    /**
     * Update quantity for text styles in CartItemCustomization.
     *
     * @param int $cartItemId
     * @param array $textStyles
     * @param int $increment
     * @param array $product
     * @param object $productCart
     * @return void
     */
    private function updateTextStyleQuantity(int $cartItemId, array $textStyles, int $increment, array $product, $productCart): void
    {
        $stylesToSave = [];
        foreach ($textStyles as $s) {
            $key = $s['style_key'] ?? $s['style'] ?? (isset($s['style_id']) ? (string)$s['style_id'] : null);
            $val = $s['option_value'] ?? ($s['option_id'] ?? null);
            if ($key !== null) {
                $stylesToSave[$key] = $val;
            }
        }

        $ct = $product['custom_text'] ?? null;
        $incQty = isset($textStyles[0]['product_quantity']) ? (int)$textStyles[0]['product_quantity'] : $increment;

        $candidates = CartItemCustomization::where('cart_item_id', $cartItemId)
            ->when(!empty($product['composite_key']), function($q) use ($product) {
                return $q->where('composite_key', $product['composite_key']);
            })
            ->when($ct !== null, function($q) use ($ct) {
                return $q->where('custom_text', trim($ct));
            })
            ->get();

        $matched = $candidates->first();

        if ($matched) {
            $current = ((int)$matched->product_quantity) ?: 1;
            $desired = $current + $incQty;
            if ($desired > 5) {
                throw new Exception('Maximum quantity limit reached. Max per text: 5');
            }
            $matched->product_quantity = $desired;
            $matched->save();
        } else {
            if ($incQty > 5) {
                throw new Exception('Maximum quantity limit reached. Max per text: 5');
            }

            CartItemCustomization::create([
                'cart_item_id' => $cartItemId,
                'styles' => json_encode($stylesToSave),
                'price' => $product['price'] ?? 0,
                'custom_text' => $ct,
                'instructions' => $product['instructions'] ?? ($textStyles[0]['instructions'] ?? null),
                'composite_key' => $product['composite_key'] ?? null,
                'product_quantity' => max(1, $incQty),
            ]);
        }
    }

    /**
     * Update quantity for images in CustomImage.
     *
     * @param int $cartItemId
     * @param array $imageStyles
     * @param int $increment
     * @return void
     */
    private function updateImageQuantity(int $cartItemId, array $imageStyles, int $increment): void
    {
        foreach ($imageStyles as $img) {
            $fileName = $img['file_name'] ?? null;
            if (empty($fileName)) {
                // skip entries without a file identifier
                continue;
            }

            $existing = CustomImage::where('cart_item_id', $cartItemId)
                ->when($fileName, function ($q) use ($fileName) {
                    return $q->where('file_name', $fileName);
                })
                ->first();

            if ($existing) {
                $current = (int)$existing->product_quantity ?: 1;
                $desired = $current + $increment;
                if ($desired > 5) {
                    throw new Exception('Maximum quantity limit reached. Max per image: 5');
                }
                $existing->product_quantity = $desired;
                $existing->save();
            } else {
                CustomImage::create([
                    'cart_item_id' => $cartItemId,
                    'file_name' => $fileName,
                    'instructions' => $img['instructions'] ?? null,
                    'price' => $img['price'] ?? 0,
                    'product_quantity' => $increment,
                ]);
            }
        }
    }

    /**
     * Normalize incoming styles for stable comparison and storage.
     * Accepts arrays with either `option_value` or `option_id` and returns
     * sorted array of ['style_id' => int, 'option_value' => string].
     *
     * @param array $styles
     * @return array
     */

    public function addToCart(array $data)
    {

        try {
            foreach ($data['products'] as $product) {
                // 1. Validate Store
                $store = $this->getStoreInfoById($product['store_id']);
                if (empty($store) || !isset($store[0])) {
                    throw new ItemNotFoundException('Store not found. Store ID: ' . $product['store_id']);
                }

                if ($store[0]->stripe != 1) {
                    throw new Exception('Store does not support purchases. Stripe not connected. Store ID: ' . $product['store_id']);
                }

                // 2. Validate Product
                $productData = $this->getProductById($product['product_id']);
                if (!$productData) {
                    throw new ItemNotFoundException('Product not found. Product ID: ' . $product['product_id']);
                }

                if ($product['store_id'] != $productData->store_id) {
                    throw new Exception('Product does not belong to the given store. Product ID: ' . $product['product_id']);
                }

                // 3. Check if product has variation
                $isVariation = !empty($product['variation_options']);

                $variationId = null;
                $currentStock = $productData->current_stock;

                if ($isVariation) {
                    $variation = $this->cartRepository->getVariationsInfo($product['variation_options'], $product['product_id']);
                    if (!$variation) {
                        throw new ItemNotFoundException('Variation not found. Product ID: ' . $product['product_id']);
                    }
                    $variationId = $variation->variation_id;
                    $currentStock = $variation->current_stock;
                }


                // 5. Fetch Cart Item (match by product and variation)
                $productCart = $this->cartRepository->getCartProduct(
                    $data['id'],
                    $data['guest'],
                    $product['product_id'],
                    $variationId
                );

                $updatedQuantity = $productCart
                    ? $productCart->product_quantity + $product['product_quantity']
                    : $product['product_quantity'];

                if ($updatedQuantity > $currentStock) {
                    throw new Exception('Insufficient stock. Product ID: ' . $product['product_id']);
                }
                // 6. Save or Update
                if ($productCart) {
                    $productCart->update(['product_quantity' => $updatedQuantity]);
                } else {
                    $newCartItem = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $variationId,
                        'store_id' => $product['store_id'],
                        'product_quantity' => $product['product_quantity'],
                        'user_id' => $data['guest'] ? null : $data['id'],
                        'random_key' => $data['guest'] ? $data['id'] : null,
                        'guest'     => $data['guest']
                    ];

                    $this->cartRepository->saveToCart($newCartItem);
                }

                if (!$data['guest']) {
                    $user = User::find($data['id']);
                    $productName = $productData->title ?? 'Unknown'; // your DB column is `title`\
                    $productCategory = $productData->category->name ?? 'Unkown';
                    

                    $productsId = $productData->product_id ;

                    if($user){
                        app(KlavioService::class)->trackEvent([
                            'email' => $user->email,
                            'name' => $user->name ?? null,
                            'last_name' => $user->name ?? null,
                            'product_name' => $productName,
                            'product_id' =>  $productsId,
                            'product_quantity' => $product['product_quantity'],
                            'category' => $productCategory,
                        ]);
                    }
                

                }
            }

            return true;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function checkQuantity(array $data)
    {
        try {
            $response = [];

            foreach ($data['products'] as $product) {
                $productResp = [];

                // Get product info
                $productData = $this->getProductById($product['product_id']);

                if (!$productData) {
                    throw new ItemNotFoundException('Product not found. Product ID: ' . $product['product_id']);
                }

                $productResp = [
                    'product_id' => $productData->product_id,
                    'name' => $productData->title,
                    'stock' => $productData->current_stock,
                    'max_quantity' => $productData->maxquantitylimit,
                ];

                // Handle variation
                if (!empty($product['variation_id'])) {
                    $variation = $this->cartRepository->getVariation($product['variation_id']);

                    if (!$variation) {
                        throw new ItemNotFoundException('Variation not found. Variation ID: ' . $product['variation_id']);
                    }

                    $productResp['variation'] = [
                        'variation_id' => $variation->variation_id,
                        'stock' => $variation->current_stock,
                        'max_quantity' => $variation->maxquantitylimit,
                    ];
                }

                $response[] = $productResp;
            }

            return $response;

        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateCartQuantity(array $data)
    {
        try {

            $product = $this->getProductById($data['product_id']);

            if (!$product) {
                throw new ItemNotFoundException('Product not found. Product ID: ' . $data['product_id']);
            }

            $isGuest = $data['guest'];
            $userKey = $isGuest ? 'random_key' : 'user_id';

            $conditions = [
                $userKey => $data['id'],
                'product_id' => $data['product_id'],
            ];

            if (!empty($data['variation_id'])) {
                $conditions['variation_id'] = $data['variation_id'];
            }

            $productCart = $this->cartRepository->getCartItem($conditions);

            if (!$productCart) {
                throw new ItemNotFoundException('Product or variation not found in cart. Product ID: ' . $data['product_id'] . ', Variation ID: ' . $data['variation_id']);
            }
            // Determine new quantity (increase or decrease)

             if (isset($data['product_quantity_plus'])) {
                $newQty = $productCart->product_quantity + $data['product_quantity_plus'];
            } elseif (isset($data['product_quantity_minus'])) {
                $newQty = $productCart->product_quantity - $data['product_quantity_minus'];
                if ($newQty < 1) {
                    throw new Exception('Quantity cannot be less than 1. Product ID: ' . $data['product_id']);
                }
            } else {
                throw new Exception('No quantity change provided.');
            }
            if($product->unique_product == true){
                if ($product->current_stock < $newQty) {
                    throw new Exception('Insufficient stock for product. Product ID: ' . $data['product_id']);
                }
            }
            // ==============================
            // 🔹 VARIATION HANDLING
            // ==============================
            if (!empty($data['variation_id'])) {
                $variation = $this->cartRepository->getVariation($data['variation_id']);
                if (!$variation) {
                    throw new ItemNotFoundException('Variation not found. Variation ID: ' . $data['variation_id']);
                }

                if ($variation->current_stock < $newQty) {
                    throw new Exception('Insufficient stock for variation. Variation ID: ' . $data['variation_id']);
                }

                // 🔸 Calculate tier or fallback to retail
                $tierDetails = CartHelpers::calculateTierDetails($data['product_id'], $data['variation_id'], $newQty);

                if ($tierDetails['total_price'] > 0) {
                    $variation->total_sale_price = 0;
                    $variation->total_actual_price = $tierDetails['total_price'];
                    $variation->total_shipping_price = $tierDetails['total_shipping'];
                } else {
                    // Retail fallback
                    $salePrice = $variation->sale_price ?? 0;
                    $actualPrice = $variation->price ?? 0;

                    if ($salePrice > 0) {
                        $variation->total_sale_price = $salePrice * $newQty;
                        $variation->total_actual_price = $actualPrice * $newQty;
                    } else {
                        $variation->total_sale_price = 0;
                        $variation->total_actual_price = $actualPrice * $newQty;
                    }

                    $variation->total_shipping_price = 0;
                }
                    $this->cartRepository->updateQuantity($conditions, $newQty);
                return $variation;
            }

            // ==============================
            // 🔹 PRODUCT HANDLING (no variation)
            // ==============================
            if ($product->current_stock < $newQty) {
                throw new Exception('Insufficient stock for product. Product ID: ' . $data['product_id']);
            }

            $tierDetails = CartHelpers::calculateTierDetails($data['product_id'], null, $newQty);

            if ($tierDetails['total_price'] > 0) {
                $product->total_sale_price = 0;
                $product->total_actual_price = $tierDetails['total_price'];
                $product->total_shipping_price = $tierDetails['total_shipping'];
            } else {
                // Retail fallback
                $salePrice = $product->sale_price ?? 0;
                $actualPrice = $product->price ?? 0;
                $onSale = $product->on_sale ?? false;

                if ($onSale && $salePrice > 0) {
                    $product->total_sale_price = $salePrice * $newQty;
                    $product->total_actual_price = $actualPrice * $newQty;
                } else {
                    $product->total_sale_price = 0;
                    $product->total_actual_price = $actualPrice * $newQty;
                }

                $product->total_shipping_price = 0;
            }
                $this->cartRepository->updateQuantity($conditions, $newQty);
            return $product;
        } catch (ItemNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Cart update failed: " . $e->getMessage());
        }
    }
    public function updateCartQuantityV3(array $data)
    {
        try {

            $product = $this->getProductById($data['product_id']);

            if (!$product) {
                throw new ItemNotFoundException('Product not found. Product ID: ' . $data['product_id']);
            }

            $isGuest = $data['guest'];
            $userKey = $isGuest ? 'random_key' : 'user_id';

            $conditions = [
                $userKey => $data['id'],
                'product_id' => $data['product_id'],
            ];

            if (!empty($data['variation_id'])) {
                $conditions['variation_id'] = $data['variation_id'];
            }

            $productCart = $this->cartRepository->getCartItem($conditions);

            if (!$productCart) {
                throw new ItemNotFoundException('Product or variation not found in cart. Product ID: ' . $data['product_id'] . ', Variation ID: ' . $data['variation_id']);
            }

                // =========================
                // 🔹 CUSTOM PRODUCT HANDLING
                // =========================
                if ($productCart->is_custom == true) {

                    if (!isset($data['customization_id'])) {
                        throw new Exception('Customization ID is required for custom product quantity update.');
                    }

                    // Get the customization (text or image)
                    if ($data['customization_type'] === 'text') {
                    $customization = CartItemCustomization::where('cart_item_id', $productCart->cart_id)
                                        ->where('id', $data['customization_id'])
                                        ->first();
                    } else {
                        $customization = CustomImage::where('cart_item_id', $productCart->cart_id)
                                            ->where('id', $data['customization_id'])
                                            ->first();
                    }

                    if (!$customization) {
                        throw new ItemNotFoundException('Customization not found. ID: ' . $data['customization_id']);
                    }

                    // Update the customization quantity
                    $currentQty = (int) $customization->product_quantity ?: 1;

                    if (isset($data['product_quantity_plus'])) {
                        $newQty = $currentQty + $data['product_quantity_plus'];
                    } elseif (isset($data['product_quantity_minus'])) {
                        $newQty = $currentQty - $data['product_quantity_minus'];
                        if ($newQty < 1) {
                            throw new Exception('Quantity cannot be less than 1.');
                        }
                    } else {
                        throw new Exception('No quantity change provided.');
                    }

                    if ($newQty > 5) {
                        throw new Exception('Maximum quantity limit reached. Max per style: 5');
                    }

                    $customization->update(['product_quantity' => $newQty]);

                    // Use customization quantity for total price
                    $usedQuantity = $newQty;
                    // 🔹 Custom + Variation
                    if (!empty($data['variation_id']) && isset($productCart->variation)) {
                        $salePrice   = $productCart->variation->sale_price ?? 0;
                        $actualPrice = $productCart->variation->price ?? 0;

                        if ($salePrice > 0) {
                            $productCart->total_sale_price = $salePrice * $usedQuantity;
                            $productCart->total_actual_price = $actualPrice * $usedQuantity;
                        } else {
                            $productCart->total_sale_price = 0;
                            $productCart->total_actual_price = $actualPrice * $usedQuantity;
                        }
                    }
                    // 🔹 Custom + Simple Product
                    else {
                        $salePrice   = $product->sale_price ?? 0;
                        $actualPrice = $product->price ?? 0;
                        $onSale      = $product->on_sale ?? false;
                        if ($onSale && $salePrice > 0) {
                            $productCart->total_sale_price = $salePrice * $usedQuantity;
                            $productCart->total_actual_price = $actualPrice * $usedQuantity;
                        } else {
                            $productCart->total_sale_price = 0;
                            $productCart->total_actual_price = $actualPrice * $usedQuantity;
                        }
                    }

                    // Keep main cart quantity null for custom products
                    $this->cartRepository->updateQuantity($conditions, null);

                    return $productCart;
                }


            // Determine new quantity (increase or decrease)

             if (isset($data['product_quantity_plus'])) {
                $newQty = $productCart->product_quantity + $data['product_quantity_plus'];
            } elseif (isset($data['product_quantity_minus'])) {
                $newQty = $productCart->product_quantity - $data['product_quantity_minus'];
                if ($newQty < 1) {
                    throw new Exception('Quantity cannot be less than 1. Product ID: ' . $data['product_id']);
                }
            } else {
                throw new Exception('No quantity change provided.');
            }
            if($product->unique_product == true){
                if ($product->current_stock < $newQty) {
                    throw new Exception('Insufficient stock for product. Product ID: ' . $data['product_id']);
                }
            }
            // ==============================
            // 🔹 VARIATION HANDLING
            // ==============================
            if (!empty($data['variation_id'])) {
                $variation = $this->cartRepository->getVariation($data['variation_id']);
                if (!$variation) {
                    throw new ItemNotFoundException('Variation not found. Variation ID: ' . $data['variation_id']);
                }

                if ($variation->current_stock < $newQty) {
                    throw new Exception('Insufficient stock for variation. Variation ID: ' . $data['variation_id']);
                }

                // 🔸 Calculate tier or fallback to retail
                $tierDetails = CartHelpers::calculateTierDetails($data['product_id'], $data['variation_id'], $newQty);

                if ($tierDetails['total_price'] > 0) {
                    $variation->total_sale_price = 0;
                    $variation->total_actual_price = $tierDetails['total_price'];
                    $variation->total_shipping_price = $tierDetails['total_shipping'];
                } else {
                    // Retail fallback
                    $salePrice = $variation->sale_price ?? 0;
                    $actualPrice = $variation->price ?? 0;

                    if ($salePrice > 0) {
                        $variation->total_sale_price = $salePrice * $newQty;
                        $variation->total_actual_price = $actualPrice * $newQty;
                    } else {
                        $variation->total_sale_price = 0;
                        $variation->total_actual_price = $actualPrice * $newQty;
                    }

                    $variation->total_shipping_price = 0;
                }
                    $this->cartRepository->updateQuantity($conditions, $newQty);
                return $variation;
            }

            // ==============================
            // 🔹 PRODUCT HANDLING (no variation)
            // ==============================
            if ($product->current_stock < $newQty) {
                throw new Exception('Insufficient stock for product. Product ID: ' . $data['product_id']);
            }

            $tierDetails = CartHelpers::calculateTierDetails($data['product_id'], null, $newQty);

            if ($tierDetails['total_price'] > 0) {
                $product->total_sale_price = 0;
                $product->total_actual_price = $tierDetails['total_price'];
                $product->total_shipping_price = $tierDetails['total_shipping'];
            } else {
                // Retail fallback
                $salePrice = $product->sale_price ?? 0;
                $actualPrice = $product->price ?? 0;
                $onSale = $product->on_sale ?? false;

                if ($onSale && $salePrice > 0) {
                    $product->total_sale_price = $salePrice * $newQty;
                    $product->total_actual_price = $actualPrice * $newQty;
                } else {
                    $product->total_sale_price = 0;
                    $product->total_actual_price = $actualPrice * $newQty;
                }

                $product->total_shipping_price = 0;
            }
                $this->cartRepository->updateQuantity($conditions, $newQty);
            return $product;
        } catch (ItemNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("Cart update failed: " . $e->getMessage());
        }
    }

    public function deleteCart($cartId)
    {
        try {
            return $this->cartRepository->deleteCart($cartId);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function deleteCartV3($cartId, $customizationId, $type)
    {
        try {
            return $this->cartRepository->deleteCartV3($cartId, $customizationId, $type);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function toggleCart($cartId, $isCheck, $type, $customizationId)
    {
        try {
            return $this->cartRepository->toggleCart($cartId, $isCheck, $type, $customizationId);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getCartV3(array $data)
    {
        $cartItems = $this->cartRepository->getCart($data);
        $cartProductIds = $cartItems->pluck('product_id')->toArray();
        $discounts = [];
        $userId = null;
        if (!$data['guest']) {
            $userId = $data['id'];
            $discounts = $this->cartRepository->getDiscountForUser($userId, $cartItems);
        }

        if ($cartItems->isEmpty()) {
            throw new ItemNotFoundException('No items found in the cart.');
        }
        $newItems = [];
        foreach ($cartItems as $item) {
            $item->tiers = [];
            $item->applied_tier = null;
            $product = $item->product;
            $item->is_corrupt = false;
            $item->corrupt_reason = null;
            if(!$product){
                continue;
            }
            if($item->is_custom != $product->is_custom ){
                $item->is_corrupt = true;
                $item->corrupt_reason = 'Product customization mismatch';
            }
            if ($product->status != ProductConstants::STATUS_ACTIVE) {
                $item->is_corrupt = true;
                $item->corrupt_reason = 'Product is inactive or missing';

            }

            if (!empty($item->variation_id)) {
                // Check if product type changed from variant to simple
                if ($product->product_type != 'variant') {
                    $item->is_corrupt = true;
                    $item->corrupt_reason = 'Product type changed - no longer a variant';
                } else {
                    $variation = CoreHelpers::getvariableByProduct_id($item->variation_id);
                    if (!$variation) {
                        continue;
                    } elseif (isset($variation->current_stock) && $variation->current_stock < $item->product_quantity) {
                        $item->is_corrupt = true;
                        $item->corrupt_reason = 'Insufficient variation stock';
                    }else{
                        // ✅ Fetch tiers for variation
                        $item->tiers = ProductTier::where('variation_id', $item->variation_id)
                            ->orderBy('min_quantity')
                            ->get();
                    }
                }
            } else {
                if ($product->current_stock < $item->product_quantity) {
                    $item->is_corrupt = true;
                    $item->corrupt_reason = 'Insufficient product stock';
                }else if ($product) {
                    // ✅ Fetch tiers for product
                    $item->tiers = ProductTier::where('product_id', $item->product_id)
                        ->whereNull('variation_id')
                        ->orderBy('min_quantity')
                        ->get();
                }
            }
            $newItems[] = $item;
        }
        $allData = array();
        $productData = $newItems;
        $results = $this->structureDataV3($productData, $allData);
        $getCartArray = [];
        foreach ($results as $key => $value) {
            $getCartData = (array) $value;
            $getCartData['feature_image'] = $this->getAttachmentUrlById((int)$value['feature_image'], 'small');
            $store_shipping = getShippingOptionById(!is_null($value['shipping_option_id']) ? $value['shipping_option_id'] : 221);
            $store_info = getStoreInfoByIdCartApi($value['store_id']);
            $getCartData['shipping_option_id'] = $store_shipping;

            $getCartData['store_info'] = $store_info;
            $getCartData['store_info']['store_logo'] = $this->getAttachmentUrlById((int)$getCartData['store_info']['store_logo'], 'small');
            $getCartData['cart_id'] = $value['cart_id'];
            $getCartData['image_id'] = $value['feature_image'];
            $getCartData['whole_sale'] = false;
            if (!$value['is_corrupt'] && $value['tiers']->isNotEmpty()) {

                $tierDetails = CartHelpers::calculateTierDetails($value['product_id'], $value['variation_id'], $value['product_quantity']);
                if($tierDetails['total_price'] > 0){
                    $getCartData['total_sale_price'] = 0;
                    $getCartData['unit_sale_price'] = 0;
                    $getCartData['unit_original_price'] = $tierDetails['unit_price'];
                    $getCartData['total_actual_price'] = $tierDetails['total_price'] ?? 0;
                    $getCartData['total_shipping_price'] = $tierDetails['total_shipping'];
                    $getCartData['whole_sale'] = true;
                    $getCartData['coupon'] = false;
                }
            }
            // ============================================================
            //  APPLY COUPON DISCOUNT (ONLY if not wholesale and not corrupt)
            // ============================================================
            if (!$value['is_corrupt'] && $getCartData['whole_sale'] != true && $getCartData['only_wholesale'] != true) {
                // Find discount matching product + variation
                $getCartData = CartHelpers::checkCouponExists($getCartData, $userId);
            }
            $getCartArray[] = $getCartData;
        }
        return $getCartArray;
    }
    public function getCart(array $data)
    {
        $cartItems = $this->cartRepository->getCart($data);
        $cartProductIds = $cartItems->pluck('product_id')->toArray();
        $discounts = $this->cartRepository->getDiscountForUser($data['id'], $cartProductIds);

        if ($cartItems->isEmpty()) {
            throw new ItemNotFoundException('No items found in the cart.');
        }
        $newItems = [];
        foreach ($cartItems as $item) {
            $item->tiers = [];
            $item->applied_tier = null;
            $product = $item->product;
            $item->is_corrupt = false;
            $item->corrupt_reason = null;

            if (!$product || $product->status != ProductConstants::STATUS_ACTIVE) {
                $item->is_corrupt = true;
                $item->corrupt_reason = 'Product is inactive or missing';

            }

            if (!empty($item->variation_id)) {
                $variation = CoreHelpers::getvariableByProduct_id($item->variation_id);
                if (!$variation) {
                    $item->is_corrupt = true;
                    $item->corrupt_reason = 'Variation not found';
                } elseif (isset($variation->current_stock) && $variation->current_stock < $item->product_quantity) {
                    $item->is_corrupt = true;
                    $item->corrupt_reason = 'Insufficient variation stock';
                }else{
                    // ✅ Fetch tiers for variation
                    $item->tiers = ProductTier::where('variation_id', $item->variation_id)
                        ->orderBy('min_quantity')
                        ->get();
                }
            } else {
                if ($product->current_stock < $item->product_quantity) {
                    $item->is_corrupt = true;
                    $item->corrupt_reason = 'Insufficient product stock';
                }else{
                    // ✅ Fetch tiers for product
                    $item->tiers = ProductTier::where('product_id', $item->product_id)
                        ->whereNull('variation_id')
                        ->orderBy('min_quantity')
                        ->get();
                }
            }
           $newItems[] = $item;
        }
        $allData = array();

        $productData = $newItems;
        $results = $this->structureData($productData, $allData);
        $getCartArray = [];
        foreach ($results as $key => $value) {
            $getCartData = (array) $value;
            $getCartData['feature_image'] = $this->getAttachmentUrlById((int)$value['feature_image'], 'small');
            $store_shipping = getShippingOptionById(!is_null($value['shipping_option_id']) ? $value['shipping_option_id'] : 221);
            $store_info = getStoreInfoByIdCartApi($value['store_id']);
            $getCartData['shipping_option_id'] = $store_shipping;

            $getCartData['store_info'] = $store_info;
            $getCartData['store_info']['store_logo'] = $this->getAttachmentUrlById((int)$getCartData['store_info']['store_logo'], 'small');
            $getCartData['cart_id'] = $value['cart_id'];
            $getCartData['image_id'] = $value['feature_image'];
            $getCartData['whole_sale'] = false;
            if (!$value['is_corrupt'] && $value['tiers']->isNotEmpty()) {

                $tierDetails = CartHelpers::calculateTierDetails($value['product_id'], $value['variation_id'], $value['product_quantity']);
                if($tierDetails['total_price'] > 0){
                    $getCartData['total_sale_price'] = 0;
                    $getCartData['unit_price'] = $tierDetails['unit_price'];
                    $getCartData['total_actual_price'] = $tierDetails['total_price'] ?? 0;
                    $getCartData['total_shipping_price'] = $tierDetails['total_shipping'];
                    $getCartData['whole_sale'] = true;
                    $getCartData['coupon'] = false;
                }
            }
            $getCartArray[] = $getCartData;
        }
        return $getCartArray;
    }
    public function structureDataV3($productData, $allData)
    {
        try {
        
        $array = array();
        foreach ($productData as $key => $value) {
            $isVariation = false;
            $totalSalePrice = 0;
            $totalActualPrice = 0;
            $product = $this->getProductById($value['product_id']);
            if ($product) {
                $array = [];

                $array['product_id'] = $value['product_id'];
                $array['store_id'] = $value['store_id'];
                $array['slug'] = $value['product']['slug'];
                $array['status'] =$value['product']['status'];
                $array['title'] = $value['product']['title'];
                $array['product_type'] = $value['product']['product_type'];
                $array['only_wholesale'] = $value['product']['only_wholesale'];
                // Only treat as variation if product type is variant AND variation data exists
                $hasVariation = !empty($value['variation']);
                if($value['product']['product_type'] == 'variant' && $hasVariation)
                {
                    $isVariation = true;
                }
                $array['unit_original_price'] = $isVariation ? $value['variation']['price'] : $value['product']['price'];
                $array['unit_sale_price'] = $isVariation ? $value['variation']['sale_price'] : $value['product']['sale_price'];
                $array['coupon'] = false;
                $array['feature_image'] = $isVariation ? $value['variation']['product_variation_image'] : $value['product']['feature_image'];
                $array['cart_id'] = $value['cart_id'];
                $array['is_check'] = $value['is_check'];
                $array['product_quantity'] = $value['product_quantity'];
                $array['current_stock'] = $isVariation ? $value['variation']['current_stock'] : $value['product']['current_stock'];
                $array['store_name'] = $value['store']['store_name'];
                $array['variation_id'] = $value['variation_id'];
                $array['attribute1'] = isset($value['variation']['attribute1']) ? $value['variation']['attribute1'] : "";
                $array['attribute2'] = isset($value['variation']['attribute2']) ? $value['variation']['attribute2'] : "";
                $array['attribute3'] = isset($value['variation']['attribute3']) ? $value['variation']['attribute3'] : "";
                $array['option1'] = isset($value['variation']['option1']) ? $value['variation']['option1'] : "";
                $array['option2'] = isset($value['variation']['option2']) ? $value['variation']['option2'] : "";
                $array['option3'] = isset($value['variation']['option3']) ? $value['variation']['option3'] : "";
                $array['unit1'] = isset($value['variation']['unit1']) ?  $value['variation']['unit1'] : "";
                $array['unit2'] = isset($value['variation']['unit2']) ? $value['variation']['unit2'] : "";
                $array['unit3'] = isset($value['variation']['unit3']) ? $value['variation']['unit3'] : "";
                $array['weight'] = $value['product']['weight'];
                $array['weight_type'] = $value['product']['weight_type'];
                $array['productSize_length'] = $value['product']['productSize_length'];
                $array['productSize_width'] = $value['product']['productSize_width'];
                $array['productSize_height'] = $value['product']['productSize_height'];
                $array['is_corrupt']  =$value['is_corrupt'];
                $array['product_tax'] = 0;
                $array['is_custom'] = isset($value['is_custom']) ? (bool)$value['is_custom'] : false;
                 // Organize related customizations and images
                $array['customizations'] = collect()
                    ->merge(
                        $value['cartItemCustomizations']->map(function ($customization) {
                            return [
                                'id' => $customization['id'],
                                'type' => 'text',
                                'price' => $customization['price'],
                                'product_quantity' => $customization['product_quantity'],
                                'instructions' => $customization['instructions'],
                                'styles' => $customization['styles'],
                                'custom_text' => $customization['custom_text'],
                                'is_check' => $customization['is_check'],
                                'file_name' => null,
                                'image_url' => null,
                            ];
                        })
                    )
                    ->merge(
                        $value['customImages']->map(function ($image) {
                            return [
                                'id' => $image['id'],
                                'type' => 'image',
                                'price' => $image['price'],
                                'product_quantity' => $image['product_quantity'],
                                'instructions' => $image['instructions'],
                                'styles' => null,
                                'custom_text' => null,
                                'file_name' => $image['file_name'],
                                'is_check' => $image['is_check'],
                                'image_url' => url('uploads/temp/' . $image['file_name']),
                            ];
                        })
                    )
                    ->values();
                $array['tiers'] = $value['tiers'];
                $array['shipping_option_id'] = $value['product']['shipping_option_id'];
                if ($value['product']['product_type'] == 'variant') {
                    $salePrice = isset($value['variation']['sale_price']) ? $value['variation']['sale_price'] : 0;
                    $actualPrice = isset($value['variation']['price']) ? $value['variation']['price'] : 0;
                    if($salePrice != 0 && $salePrice > 0){
                        $totalSalePrice = $salePrice * $value['product_quantity'];
                        $totalActualPrice = $actualPrice * $value['product_quantity'];
                    }else{
                        $totalActualPrice = $actualPrice * $value['product_quantity'];
                    }
                }else{
                    $salePrice = isset($value['product']['sale_price']) ? $value['product']['sale_price'] : 0;
                    $actualPrice = isset($value['product']['price']) ? $value['product']['price'] : 0;
                    $onSale = $value['product']['on_sale'];
                    if($onSale && $salePrice != 0 && $salePrice > 0){
                        $totalSalePrice = $salePrice * $value['product_quantity'];
                        $totalActualPrice = $actualPrice * $value['product_quantity'];
                    }else{
                        $totalActualPrice = $actualPrice * $value['product_quantity'];
                    }
                } 
                $array['total_sale_price'] = $totalSalePrice ?? 0;
                $array['total_actual_price'] = $totalActualPrice ?? 0;
                // 🔥 SPLIT CART ITEM BY EACH CUSTOMIZATION (RESPONSE ONLY)

                if (!empty($array['customizations']) && count($array['customizations']) > 0) {

                    foreach ($array['customizations'] as $customization) {

                        $newRow = $array; // clone full product data

                        // Only keep ONE customization
                        $newRow['customizations'] = $customization;

                        // Override quantity from customization
                        $newRow['product_quantity'] = $customization['product_quantity'];

                        // Optional: attach customization price
                        $newRow['customization_price'] = $customization['price'];

                        // Optional: unique frontend key
                        $newRow['row_key'] = $array['cart_id'] . '_' . $customization['id'];

                        $allData[] = $newRow;
                    }

                } else {
                    // No customization → keep as-is
                    $allData[] = $array;
                }
            }
        }
        return $allData;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Add custom product to cart with configuration matching and max quantity limit
     */
    public function addCustomProduct(array $data)
    {
        try {
            foreach ($data['products'] as $product) {
                // 1. Validate Store
                $store = $this->getStoreInfoById($product['store_id']);
                if (empty($store) || !isset($store[0])) {
                    throw new ItemNotFoundException('Store not found. Store ID: ' . $product['store_id']);
                }

                if ($store[0]->stripe != 1) {
                    throw new Exception('Store does not support purchases. Stripe not connected. Store ID: ' . $product['store_id']);
                }

                // 2. Validate Product
                $productData = $this->getProductById($product['product_id']);
                if (!$productData) {
                    throw new ItemNotFoundException('Product not found. Product ID: ' . $product['product_id']);
                }

                if ($product['store_id'] != $productData->store_id) {
                    throw new Exception('Product does not belong to the given store. Product ID: ' . $product['product_id']);
                }

                // 3. Check if product has variation
                $isVariation = !empty($product['variation_options']);

                $variationId = null;
                $currentStock = $productData->current_stock;

                if ($isVariation) {
                    $variation = $this->cartRepository->getVariationsInfo($product['variation_options'], $product['product_id']);
                    if (!$variation) {
                        throw new ItemNotFoundException('Variation not found. Product ID: ' . $product['product_id']);
                    }
                    $variationId = $variation->variation_id;
                    $currentStock = $variation->current_stock;
                }

                $originalTextStyles = $product['text_styles'] ?? $product['styles'] ?? [];
                $textStyles = $originalTextStyles;
                $imageStyles = $product['image_styles'] ?? [];
                $isImageOnly = empty($originalTextStyles) && !empty($imageStyles);
                if ($isImageOnly) {
                    $textStyles = []; 
                }

                // Check if custom product already exists in products_cart
                $productCart = $this->cartRepository->getCustomCartProduct(
                    $data['id'],
                    $data['guest'],
                    $product['product_id'],
                    $variationId,
                    $product['custom_text'] ?? null,
                    !empty($textStyles) ? $textStyles : null,
                    !empty($imageStyles) ? $imageStyles : null,
                );

                if ($productCart && $productCart->is_custom) {
                    // If text styles are present, try to update existing customization
                    if (!empty($textStyles) && is_array($textStyles)) {
                        $matchedCustomization = CartItemCustomization::where('cart_item_id', $productCart->cart_id)
                            ->when(!empty($product['composite_key']), function($q) use ($product) {
                                return $q->where('composite_key', $product['composite_key']);
                            })
                            ->when(isset($product['custom_text']), function($q) use ($product) {
                                return $q->where('custom_text', $product['custom_text']);
                            })
                            ->first();
                        $increment = (int)$product['product_quantity'] ?: 1;
                        if ($matchedCustomization) {
                            $current = ((int)$matchedCustomization->product_quantity) ?: 1;
                            $desired = $current + $increment;
                            if ($desired > 5) {
                                throw new Exception('Maximum quantity limit reached. Max per style: 5');
                            }
                            $matchedCustomization->product_quantity = $desired;
                            $matchedCustomization->save();
                        } else {
                            // Insert new customization row for this cart item
                            $stylesToSave = [];
                            if (!empty($product['styles']) && is_array($product['styles'])) {
                                foreach ($product['styles'] as $index => $item) {

                                    $style = trim($item['style'] ?? '');
                                    $value = trim($item['option_value'] ?? '');
                                    if ($style === '' || $value === '') {
                                        throw new Exception(
                                            "Invalid style data at index {$index}. Style and option_value are required."
                                        );
                                    }
                                    // STRICT validation
                                    if ($style !== '' && $value !== '') {
                                        $stylesToSave[$style] = $value;
                                    }
                                }
                            }
                            CartItemCustomization::create([
                                'cart_item_id' => $productCart->cart_id,
                                'styles' => $stylesToSave,
                                'price' => $product['price'] ?? 0,
                                'custom_text' => $product['custom_text'] ?? null,
                                'instructions' => $product['instructions'] ?? ($textStyles[0]['instructions'] ?? null),
                                'composite_key' => $product['composite_key'] ?? null,
                                'product_quantity' => $increment,
                            ]);
                        }
                        // Also handle images if present
                        if (!empty($imageStyles) && is_array($imageStyles)) {
                            $increment = (int)($product['product_quantity'] ?? 1);
                            $this->storeImageStyles($productCart->cart_id, $imageStyles, $increment);
                        }
                    } else if (!empty($imageStyles) && is_array($imageStyles)) {
                        // If only images, update image quantities
                        $increment = (int)$product['product_quantity'] ?: 1;
                        $this->updateImageQuantity($productCart->cart_id, $imageStyles, $increment);
                    }
                    // Do not add a new cart item
                } else if (!$productCart) {
                    // If no cart item exists, create new cart item and customization
                    $newCartItem = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $variationId,
                        'store_id' => $product['store_id'],
                        'product_quantity' => null,
                        'user_id' => $data['guest'] ? null : $data['id'],
                        'random_key' => $data['guest'] ? $data['id'] : null,
                        'guest' => $data['guest'],
                        'is_custom' => 1,
                    ];
                    $textStylesToSave = [];
                    $stylesRows = [];
                    if (!empty($textStyles) && is_array($textStyles)) {
                        foreach ($textStyles as $index => $item) {

                            $style = trim($item['style'] ?? '');
                            $value = trim($item['option_value'] ?? '');

                            // STRICT validation
                            if ($style === '' || $value === '') {
                                throw new Exception(
                                    "Invalid style data at index {$index}. Style and option_value are required."
                                );
                            }
                            $stylesRows[$style] = $value;
                        }
                    }
                    $group = [
                        'styles' => $stylesRows,
                        'price' => isset($product['price']) ? (float)$product['price'] : 0,
                        'custom_text' => $product['custom_text'] ?? null,
                        'instructions' => $product['instructions'] ?? ($textStyles[0]['instructions'] ?? null),
                        'composite_key' => $product['composite_key'] ?? null,
                        'product_quantity' => isset($product['product_quantity']) && (int)$product['product_quantity'] > 0 ? (int)$product['product_quantity'] : 1,
                    ];
                    if (!empty($stylesRows) || !empty($group['custom_text']) || !empty($group['instructions'])) {
                        $textStylesToSave[] = $group;
                    }
                    $cartItem = $this->cartRepository->saveCustomToCart($newCartItem, $textStylesToSave);
                    if (!empty($imageStyles) && is_array($imageStyles) && $cartItem) {
                        $increment = isset($product['product_quantity']) && (int)$product['product_quantity'] > 0 ? (int)$product['product_quantity'] : 1;
                        $this->storeImageStyles($cartItem->cart_id, $imageStyles, $increment);
                    }
                }

                if (!$data['guest']) {
                    $user = User::find($data['id']);
                    $productName = $productData->title ?? 'Unknown';
                    $productCategory = $productData->category->name ?? 'Unknown';
                    $productsId = $productData->product_id;

                    if ($user) {
                        app(KlavioService::class)->trackEvent([
                            'email' => $user->email,
                            'name' => $user->name ?? null,
                            'last_name' => $user->name ?? null,
                            'product_name' => $productName,
                            'product_id' => $productsId,
                            'product_quantity' => $product['product_quantity'],
                            'category' => $productCategory,
                        ]);
                    }
                }
            }

            return true;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function structureData($productData, $allData)
    {
        $array = array();
        foreach ($productData as $key => $value) {
            $totalSalePrice = 0;
            $totalActualPrice = 0;
            $product = $this->getProductById($value['product_id']);

            if ($product) {
                $array = [];

                $array['product_id'] = $value['product_id'];
                $array['store_id'] = $value['store_id'];
                $array['slug'] = $value['product']['slug'];
                $array['user_id'] = $value['product']['user_id'];
                $array['random_key'] = $value['random_key'];
                $array['title'] = $value['product']['title'];
                $array['product_type'] = $value['product']['product_type'];
                $array['price'] = $value['product']['price'];
                $array['on_sale'] = $value['product']['on_sale'];
                $array['sale_price'] = $value['product']['sale_price'];
                $array['coupon'] = false;
                $array['discount_id'] = null;
                $array['feature_image'] = $value['product']['feature_image'];
                $array['cart_id'] = $value['cart_id'];
                $array['is_check'] = $value['is_check'];
                $array['product_quantity'] = $value['product_quantity'];
                $array['current_stock'] = $value['product']['current_stock'];
                $array['discount_percentage'] = $value['product']['discount_percentage'];
                $discount = Discount::where('id', $value['product']['discount_id'])->first();
                if ($value['product']['product_type'] == 'variant') {
                    $discountId = $value['product']['discount_id'];
                    
                    $variationId = $value['variation_id'];
                    if ($discountId !== null && $discountId != 0) {
                        $discountApplicable = DiscountApplicable::where([
                            'applicable_type' => 'variation',
                            'discount_id' => $discountId,
                            'applicable_id' => $variationId
                        ])->first();

                        if ($discountApplicable) {
                            $array['discount_id'] = $discountId;
                            $array['on_sale'] = 1;
                            $array['coupon'] = false;
                        } else {
                            $array['on_sale'] = 0;
                            $array['discount_id'] = null;
                            $array['coupon'] = false;
                        }
                    } else {
                        $sale = Discount::where([
                            'store_id' => $value['store_id'],
                            'status' => 'active',
                            'state' => 'live',
                            'type' => 'coupon',
                            'archive' => 0,
                        ])->first();
                        if ($sale) {
                            $discountApplicable = DiscountApplicable::where([
                                'applicable_type' => 'variation',
                                'discount_id' => $sale->id,
                                'applicable_id' => $variationId
                            ])->first();

                            if ($discountApplicable) {
                                $array['discount_id'] = null;
                                $array['on_sale'] = 0;
                                $array['coupon'] = true;
                            } else {
                                $array['on_sale'] = 0;
                                $array['discount_id'] = null;
                                $array['coupon'] = false;
                            }
                        }
                    }
                } else {
                    $discountId = $value['product']['discount_id'];
                    $productId = $value['product']['product_id'];
                    if ($discountId !== null && $discountId != 0) {
                        $discountApplicable = DiscountApplicable::where([
                            'applicable_type' => 'product',
                            'discount_id' => $discountId,
                            'applicable_id' => $productId
                        ])->first();

                        if ($discountApplicable) {
                            $array['discount_id'] = $discountId;
                            $array['on_sale'] = 1;
                            $array['coupon'] = false;
                        } else {
                            $array['on_sale'] = 0;
                            $array['discount_id'] = null;
                            $array['coupon'] = false;
                        }
                    } else {
                        $sale = Discount::where([
                            'store_id' => $product['store_id'],
                            'status' => 'active',
                            'state' => 'live',
                            'type' => 'coupon',
                            'archive' => 0,
                        ])->first();

                        if ($sale) {
                            $discountApplicable = DiscountApplicable::where([
                                'applicable_type' => 'product',
                                'discount_id' => $sale->id,
                                'applicable_id' => $productId
                            ])->first();

                            if ($discountApplicable) {
                                $array['discount_id'] = null;
                                $array['on_sale'] = 0;
                                $array['coupon'] = true;
                            } else {
                                $array['on_sale'] = 0;
                                $array['discount_id'] = null;
                                $array['coupon'] = false;
                            }
                        }
                    }
                }
                $array['maxquantitylimit'] = isset($value['product']['maxquantitylimit']) ? $value['product']['maxquantitylimit'] : "";
                $array['store_name'] = $value['store']['store_name'];
                $array['stripe'] = $value['store']['stripe'];
                $array['wallet_address'] = $value['store']['wallet_address'];
                $array['store_zip_code'] = $value['store']['zip_code'];
                $array['variation_id'] = $value['variation_id'];
                $array['variable_currentstock'] = isset($value['variation']['current_stock']) ? $value['variation']['current_stock'] : "";
                $array['product_variation_image'] = isset($value['variation']['product_variation_image']) ? $value['variation']['product_variation_image'] : "";
                $array['product_variation_price'] = isset($value['variation']['price']) ? $value['variation']['price'] : 0;
                $array['product_variation_sale_price'] = isset($value['variation']['sale_price']) ? $value['variation']['sale_price'] : 0;
                $array['variation_discount_percentage'] = isset($value['variation']['discount_percentage']) ? $value['variation']['discount_percentage'] : 0;
                $array['attribute1'] = isset($value['variation']['attribute1']) ? $value['variation']['attribute1'] : "";
                $array['attribute2'] = isset($value['variation']['attribute2']) ? $value['variation']['attribute2'] : "";
                $array['attribute3'] = isset($value['variation']['attribute3']) ? $value['variation']['attribute3'] : "";
                $array['variable_maxquantitylimit'] = isset($value['variation']['maxquantitylimit']) ? $value['variation']['maxquantitylimit'] : "";
                $array['option1'] = isset($value['variation']['option1']) ? $value['variation']['option1'] : "";
                $array['option2'] = isset($value['variation']['option2']) ? $value['variation']['option2'] : "";
                $array['option3'] = isset($value['variation']['option3']) ? $value['variation']['option3'] : "";
                $array['unit1'] = isset($value['variation']['unit1']) ?  $value['variation']['unit1'] : "";
                $array['unit2'] = isset($value['variation']['unit2']) ? $value['variation']['unit2'] : "";
                $array['unit3'] = isset($value['variation']['unit3']) ? $value['variation']['unit3'] : "";
                $array['weight'] = $value['product']['weight'];
                $array['weight_type'] = $value['product']['weight_type'];
                $array['productSize_length'] = $value['product']['productSize_length'];
                $array['productSize_width'] = $value['product']['productSize_width'];
                $array['productSize_height'] = $value['product']['productSize_height'];
                $array['store_country'] = $value['store']['store_country'];
                $array['flashsale_status'] = $value['product']['flashsale_status'];
                $array['shipping_option_id'] = $value['product']['shipping_option_id'];
                $array['store_state'] = $value['store']['store_state'];
                $array['is_corrupt']  =$value['is_corrupt'];
                $array['corrupt_reason']  =$value['corrupt_reason'];
                $array['product_tax'] = 0;
                $array['shipping_price'] = 0;
                $array['tiers'] = $value['tiers'];
                $array['expected-delivery-date'] = 0;
                if ($value['product']['product_type'] == 'variant') {
                    $salePrice = isset($value['variation']['sale_price']) ? $value['variation']['sale_price'] : 0;
                    $actualPrice = isset($value['variation']['price']) ? $value['variation']['price'] : 0;
                    if($salePrice != 0 && $salePrice > 0){
                        $totalSalePrice = $salePrice * $value['product_quantity'];
                        $totalActualPrice = $actualPrice * $value['product_quantity'];
                    }else{
                        $totalActualPrice = $actualPrice * $value['product_quantity'];
                    }
                }else{
                    $salePrice = isset($value['product']['sale_price']) ? $value['product']['sale_price'] : 0;
                    $actualPrice = isset($value['product']['price']) ? $value['product']['price'] : 0;
                    $onSale = $value['product']['on_sale'];
                    if($onSale && $salePrice != 0 && $salePrice > 0){
                        $totalSalePrice = $salePrice * $value['product_quantity'];
                        $totalActualPrice = $actualPrice * $value['product_quantity'];
                    }else{
                        $totalActualPrice = $actualPrice * $value['product_quantity'];
                    }
                } 
                $array['total_sale_price'] = $totalSalePrice ?? 0;
                $array['total_actual_price'] = $totalActualPrice ?? 0;
                array_push($allData, $array);
            }
        }
        return $allData;
    }
}