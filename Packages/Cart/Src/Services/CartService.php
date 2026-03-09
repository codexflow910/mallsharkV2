<?php

namespace Packages\Cart\Services;

use Exception;
use Packages\Cart\Contracts\CartHelpers;
use Packages\Core\Contracts\CoreHelpers;
use Illuminate\Support\ItemNotFoundException;
use Packages\Cart\Repositories\Eloquent\CartRepository;

class CartService
{
    use CartHelpers;
    use CoreHelpers;

    protected $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function addToCart(array $data)
    {
        $maxBuyLimit = env('MAX_BUY_LIMIT');

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

                // 4. Fetch Cart Item
                $productCart = $this->cartRepository->getCartProduct(
                    $data['id'],
                    $data['guest'],
                    $product['product_id'],
                    $variationId
                );

                $updatedQuantity = $productCart
                    ? $productCart->product_quantity + $product['product_quantity']
                    : $product['product_quantity'];

                // 5. Validate Quantity and Stock
                if ($updatedQuantity > $maxBuyLimit) {
                    throw new Exception('Maximum quantity allowed exceeded. Product ID: ' . $product['product_id']);
                }

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
                    ];

                    $this->cartRepository->saveToCart($newCartItem);
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
            $maxBuyLimit = env('MAX_BUY_LIMIT');
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

            if (isset($data['product_quantity_plus'])) {
                $newQty = $productCart->product_quantity + $data['product_quantity_plus'];

                if (!empty($data['variation_id'])) {
                    $variation = $this->cartRepository->getVariation($data['variation_id']);
                    if (!$variation) {
                        throw new ItemNotFoundException('Variation not found. Variation ID: ' . $data['variation_id']);
                    }

                    if (
                        $variation->current_stock >= $newQty &&
                        $newQty <= $maxBuyLimit
                    ) {
                        return $this->cartRepository->updateQuantity($conditions, $newQty);
                    } elseif ($variation->current_stock < $newQty) {
                        throw new Exception('Insufficient stock for variation. Variation ID: ' . $data['variation_id']);
                    } elseif ($newQty > $maxBuyLimit) {
                        throw new Exception('Maximum quantity allowed exceeded for variation. Variation ID: ' . $data['variation_id']);
                    }
                    
                }

                if ($product->current_stock >= $newQty && $newQty <= $maxBuyLimit) {
                    return $this->cartRepository->updateQuantity($conditions, $newQty);
                } elseif ($product->current_stock < $newQty) {
                    throw new Exception('Insufficient stock for product. Product ID: ' . $data['product_id']);
                } elseif ($newQty > $maxBuyLimit) {
                    throw new Exception('Maximum quantity allowed exceeded for product. Product ID: ' . $data['product_id']);
                }
            }

            if (isset($data['product_quantity_minus'])) {
                $newQty = $productCart->product_quantity - $data['product_quantity_minus'];
                if ($newQty < 1) {
                    throw new Exception('Quantity cannot be less than 1. Product ID: ' . $data['product_id']);
                }
                return $this->cartRepository->updateQuantity($conditions, $newQty);
            }

            return false;

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

    public function toggleCart($cartId, $isCheck)
    {
        try {
            return $this->cartRepository->toggleCart($cartId, $isCheck);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getCart(array $data)
    {
        $cartItems = $this->cartRepository->getCart($data);

        if ($cartItems->isEmpty()) {
            throw new ItemNotFoundException('No items found in the cart.');
        }

        $allData = array();

        $productData = $cartItems->toArray();

        // dd($cartItems, $productData);
        $results = $this->structureData($productData, $allData);

        foreach ($results as $key => $value) {

            $getCartData = (array) $value;

            $getCartData['feature_image'] = $this->getAttachmentUrlById((int)$value['feature_image'], 'small');
            $getCartData['product_variation_image'] = $this->getAttachmentUrlById((int)$value['product_variation_image'], 'small');
            $state = getStatebyId($value['store_state']);
            $store_shipping = getShippingOptionById(!is_null($value['shipping_option_id']) ? $value['shipping_option_id'] : 221);
            $shipping_info = [
                'shipping_id' => $store_shipping['id'],
                'shipping_cost' => $store_shipping['flat_shipping_rate'] ?? 0,
                'shipping_service' => !empty($store_shipping['shipping_service']) ? $store_shipping['shipping_service'] : 'no service selected',
                'profile_name' => $store_shipping['profile_name'],
                'shipping_method' => ($store_shipping['id'] == 221) ? 'free' : 'flat'
            ];
            $store_country = getCountryById($value['store_country']);
            $store_info = getStoreInfoByIdCartApi($value['store_id']);
            // echo 'Data: <pre>' .print_r($store_info,true). '</pre>'; die();
            $getCartData['store_state'] = $state;
            $getCartData['shipping_option_id'] = $store_shipping;
            $getCartData['shiping_data'] = $shipping_info;
            $getCartData['store_country'] = $store_country;

            $getCartData['store_info'] = $store_info;
            $getCartData['store_info']['store_logo'] = $this->getAttachmentUrlById((int)$getCartData['store_info']['store_logo'], 'small');
            $getCartData['cart_id'] = $value['cart_id'];

            $getCartArray[] = $getCartData;
        }
        return $getCartArray;
    }

    public function structureData($productData, $allData)
    {
        $array = array();
        foreach ($productData as $key => $value) {
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
                $array['feature_image'] = $value['product']['feature_image'];
                $array['cart_id'] = $value['cart_id'];
                $array['is_check'] = $value['is_check'];
                $array['product_quantity'] = $value['product_quantity'];
                $array['current_stock'] = $value['product']['current_stock'];
                $array['discount_percentage'] = $value['product']['discount_percentage'];
                $array['maxquantitylimit'] = isset($value['product']['maxquantitylimit']) ? $value['product']['maxquantitylimit'] : "";
                $array['store_name'] = $value['store']['store_name'];
                $array['stripe'] = $value['store']['stripe'];
                $array['wallet_address'] = $value['store']['wallet_address'];
                $array['store_zip_code'] = $value['store']['zip_code'];
                $array['variation_id'] = $value['variation_id'];
                $array['variable_currentstock'] = isset($value['variation']['current_stock']) ? $value['variation']['current_stock'] : "";
                $array['product_variation_image'] = isset($value['variation']['product_variation_image']) ? $value['variation']['product_variation_image'] : "";
                $array['product_variation_price'] = isset($value['variation']['price']) ? $value['variation']['price'] : "";
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
                $array['productSize_length'] = $value['product']['productSize_length'];
                $array['productSize_width'] = $value['product']['productSize_width'];
                $array['productSize_height'] = $value['product']['productSize_height'];
                $array['store_country'] = $value['store']['store_country'];
                $array['flashsale_status'] = $value['product']['flashsale_status'];
                $array['shipping_option_id'] = $value['product']['shipping_option_id'];
                $array['store_state'] = $value['store']['store_state'];

                $array['product_tax'] = 0;
                $array['shipping_price'] = 0;

                $array['expected-delivery-date'] = 0;

                array_push($allData, $array);
            }
        }
        return $allData;
    }
}