<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Cart;

use App;
use URL;
use Hash;
use Mail;
use Cookie;
use Request;
use Session;
use App\BaseAccess;
use App\Models\Cart;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductCart;
use Illuminate\Support\Str;
use App\Http\Controllers\UspsT;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;

/**
 * Description of StoreAccess
 *
 * @author Asif
 */
class Cartaccess extends BaseAccess
{

    private $_cart;
    private $_variation;
    private $shiprate;
    private $_store;
    private $_product;
    private $_productCart;

    public function __construct()
    {
        $this->_cart = new Cart();
        $this->_product = new Product();
        $this->_variation = new ProductVariation();
        $this->shiprate = new UspsT();
        $this->_store = new Store();
        $this->_productCart = new ProductCart();
    }

    public function checkProductExists($postedData) {
        // Assuming $cartId, $productId, $storeId are provided in $postedData
        $productId = $postedData['product_id'];
        $storeId = $postedData['store_id'];
    
        // Implement your logic to check if the product exists in the cart based on cart_id, product_id, and store_id
        $existingProduct = DB::table('products_cart')
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();
    
        return $existingProduct !== null; // Return true if product exists, false otherwise
    }

    public function saveToCart($data) {
        // dd($data);
        $storeInfo =  getStoreInfoById($data['store_id']);
        $rand_number = Str::random(25);
        
        if ($storeInfo[0]->stripe == 1) {
           
            if (!empty($_COOKIE['cartCookie'])) {
                $variation_id = '';
                if(isset($data['vartion_options']) || isset($data['variation_id'])){
                    if(isset($data['vartion_options'])){
                        $variation_id = $this->getVariationsInfo($data['vartion_options'], $data['product_id']);
                    }else{
                        $variation_id = isset($data['variation_id']) && !empty($data['variation_id']) ? $data['variation_id'] : '';
                    }
                    $productId = $data['product_id'];
                    $product = DB::table('product_variation')->where(['product_id'=>$productId,'variation_id' => $variation_id])->first();
                    
                }else{
                    $productId = $data['product_id'];
                    $product = DB::table('products')->where(['product_id'=>$productId])->first();
                }
                
                $randomKey = $_COOKIE['cartCookie'];
                $prdoductInfo = $this->_cart
                    ->where('random_key', '=', $randomKey)
                    ->where('product_id', '=', $data['product_id'])
                    ->when(!empty($variation_id), function ($query) use ($variation_id) {
                        return $query->where('variation_id', '=', $variation_id);
                    })
                    ->first();
                if (!empty($prdoductInfo)) {
                   
                    if(!isset($data['product_quantity_minus'])){
                        $updatQuantity = $prdoductInfo['product_quantity'];
                        if($updatQuantity+1 >  $product->current_stock){
                            
                            return ['key' => $rand_number,'max_quantity' => true];
                        }
                    }
                    // $updatQuantity = (int) $prdoductInfo['product_quantity'] + (int) $data['product_quantity'];
                    $updatQuantity = $prdoductInfo['product_quantity'];
                    if (isset($data['product_quantity_plus'])) {
                        $updatQuantity++;
                    } else if (isset($data['product_quantity_minus'])) {
                        $updatQuantity--;
                    } else if (isset($data['product_quantity'])) {
                        //$updatQuantity = $prdoductInfo['product_quantity'] + $data['product_quantity'];
                        $updatQuantity = $data['product_quantity'];
                    }

                    if (isset($data['vartion_options']) && $data['vartion_options'] != "") {
                        $variationId = $this->getVariationsInfo($data['vartion_options'], $data['product_id']);
                        $productVariationId = $this->_cart->where('random_key', '=', $randomKey)->where('variation_id', '=', $variationId)->count();
                        if(!isset($data['product_quantity_minus'])){
                            $product_count = $this->_cart->where('random_key',$randomKey)->where('variation_id',$variationId)->first();
                            if(!is_null($product_count) && $product_count->cart_count > 1){
                                return ['key' => $rand_number,'max_quantity' => true];
                            }
                        }
                       
                        if ($productVariationId > 0) {
                            if(isset($data['product_quantity'])){
                                $counter = $product_count->cart_count;
                                $counter = $counter+$data['product_quantity'];
                                $updatQuantity = $product_count->product_quantity + $data['product_quantity'];
                            }elseif(isset($data['product_quantity_plus'])){
                                $counter = $product_count->cart_count;
                                $counter = $counter+1;
                                $updatQuantity = $product_count->product_quantity + 1;
                            }else{
                                $counter = $product_count->cart_count;
                                $counter = $counter-1;
                                $updatQuantity = $product_count->product_quantity-1;
                            }
                          
                            if($counter > 1){
                                return ['key' => $rand_number,'max_quantity' => true];
                            }
                            
                            $this->_cart->where('variation_id', '=', $variationId)->update(['cart_count'=>$counter,'product_quantity' => $updatQuantity]);
                        } else {
                            if(isset($data['product_quantity']) && $data['product_quantity'] > 1){
                                return ['key' => $rand_number,'max_quantity' => true];
                            }
                            $this->_cart->variation_id = $variationId;
                            $this->_cart->product_id = $data['product_id'];
                            $this->_cart->store_id = $data['store_id'];
                            $this->_cart->product_quantity = $data['product_quantity'];
                            $this->_cart->random_key = $randomKey;
                            $this->_cart->cart_count = $data['product_quantity'];
                            if (!empty($storeInfo[0]->wallet_address)) {
                                if (!empty($_COOKIE['cartCookieCrypto'])) {
                                    $randomKey_crypto = $_COOKIE['cartCookieCrypto'];
                                    $this->_cart->crypto_random = $randomKey_crypto;
                                }
                            }
                            $this->_cart->save();
                        }
                    } else {
                        
                        $product_count = $this->_cart->where('cart_id', '=', $prdoductInfo['cart_id'])->first();
                        if(!isset($data['product_quantity_minus'])){
                            if($product_count->cart_count > 1){
                                return ['key' => $rand_number,'max_quantity' => true];
                            }
                        }
                        if(isset($data['product_quantity'])){
                            $counter = $product_count->cart_count;
                            $counter = $counter+$data['product_quantity'];
                            $updatQuantity = $product_count->product_quantity + $data['product_quantity'];
                        }elseif(isset($data['product_quantity_plus'])){
                            $counter = $product_count->cart_count;
                            $counter = $counter+1;
                            $updatQuantity = $product_count->product_quantity + 1;
                        }elseif(isset($data['product_quantity_minus'])){
                            $counter = $product_count->cart_count;
                            $counter = $counter-1;
                            $updatQuantity = $product_count->product_quantity-1;
                        }
                        if($counter > 1){
                            return ['key' => $rand_number,'max_quantity' => true];
                        }
                        $this->_cart->where('cart_id', '=', $prdoductInfo['cart_id'])->update(['cart_count'=>$counter,'product_quantity' => $updatQuantity]);
                       
                    }
                } else {
                    
                    $randomKey = $_COOKIE['cartCookie'];
                    if(isset($data['product_quantity']) && $data['product_quantity'] > 1){
                        return ['key' => $rand_number,'max_quantity' => true];
                    }
                    if (isset($data['vartion_options']) && $data['vartion_options'] != "") {
                        $variationId = $this->getVariationsInfo($data['vartion_options'], $data['product_id']);
                        // $product_count = $this->_cart->where('random_key',$randomKey)->where('variation_id',$variationId)->count();
                        $this->_cart->variation_id = $variationId;
                    }
                    $this->_cart->product_id = $data['product_id'];
                    if (isset($data['store_id'])) {
                        $this->_cart->store_id = $data['store_id'];
                    }
                    $this->_cart->product_quantity = $data['product_quantity'];
                    $this->_cart->random_key = $randomKey;
                    if (!empty($storeInfo[0]->wallet_address)) {
                        if (!empty($_COOKIE['cartCookieCrypto'])) {
                            $randomKey_crypto = $_COOKIE['cartCookieCrypto'];
                            $this->_cart->crypto_random = $randomKey_crypto;
                        }
                    }
                    $this->_cart->cart_count = $data['product_quantity'];
                    $this->_cart->save();
                }
                // return true;
                //                } else {
                //                    return false;
                //                }
            } else {
                if(isset($data['product_quantity']) && $data['product_quantity'] > 1){
                    return ['key' => $rand_number,'max_quantity' => true];
                }
                if (isset($data['vartion_options']) && $data['vartion_options'] != "") {
                    $variationId = $this->getVariationsInfo($data['vartion_options'], $data['product_id']);
                    $this->_cart->variation_id = $variationId;
                }
                setcookie("cartCookie", $rand_number, time() + (2 * 24 * 60 * 60), "/");

                $this->_cart->product_id = $data['product_id'];
                if (isset($data['store_id'])) {
                    $this->_cart->store_id = $data['store_id'];
                }
                $this->_cart->product_quantity = $data['product_quantity'];
                $this->_cart->random_key = $rand_number;
                $this->_cart->cart_count = $data['product_quantity'];
                $this->_cart->save();
            }
            // return  ['random_number' =>$rand_number , 'payment_method' => 'stripe' ];
        }

        // Crypto
        if (!empty($storeInfo[0]->wallet_address)) {
            if (!empty($_COOKIE['cartCookieCrypto'])) {
                $randomKey_crypto = $_COOKIE['cartCookieCrypto'];
                // $query = $this->_cart->select('product_quantity')->where('product_id', '=', $data['product_id'])->where('crypto_random', '=', $randomKey_crypto)->first();
                // if (!empty($query)) {
                //     $cartQuantity = $query->toArray();
                //     $quantity = $cartQuantity['product_quantity'];
                // }
                // $prdoductInfo = $this->_cart->where('crypto_random', '=', $randomKey_crypto)->where('product_id', '=', $data['product_id'])->first();

                // if (!empty($prdoductInfo)) {
                //     $updatQuantity = (int) $prdoductInfo['product_quantity'] + (int) $data['product_quantity'];
                //     if (isset($data['product_quantity_plus'])) {
                //         $updatQuantity = (int) $prdoductInfo['product_quantity'] + (int) 1;
                //     } else if (isset($data['product_quantity_minus'])) {
                //         $updatQuantity = (int) $prdoductInfo['product_quantity'] - (int) 1;
                //     } else if (isset($data['product_quantity'])) {
                //         $updatQuantity = $data['product_quantity'];
                //     }
                //     if (isset($data['vartion_options']) && $data['vartion_options'] != "") {
                //         $variationId = $this->getVariationsInfo($data['vartion_options'], $data['product_id']);
                //         $productVariationId = $this->_cart->where('crypto_random', '=', $randomKey_crypto)->where('variation_id', '=', $variationId)->count();
                //         if ($productVariationId > 0) {
                //             $this->_cart->where('variation_id', '=', $variationId)->update(['product_quantity' => $updatQuantity]);
                //         } else {
                //             $this->_cart->variation_id = $variationId;
                //             $this->_cart->product_id = $data['product_id'];
                //             $this->_cart->store_id = $data['store_id'];
                //             $this->_cart->product_quantity = $data['product_quantity'];
                //             $this->_cart->crypto_random = $randomKey_crypto;
                //             $this->_cart->save();
                //         }
                //     } else {
                //         $this->_cart->where('cart_id', '=', $prdoductInfo['cart_id'])->update(['product_quantity' => $updatQuantity]);
                //     }
                // } else {

                // $randomKey_crypto = $_COOKIE['cartCookieCrypto'];
                // // echo 'Data: <pre>' .print_r($result,true). '</pre>'; die();
                // if (isset($data['vartion_options']) && $data['vartion_options'] != "") {
                //     $variationId = $this->getVariationsInfo($data['vartion_options'], $data['product_id']);
                //     $this->_cart->variation_id = $variationId;
                // }
                // $this->_cart->product_id = $data['product_id'];
                // if (isset($data['store_id'])) {
                //     $this->_cart->store_id = $data['store_id'];
                // }
                // $this->_cart->product_quantity = $data['product_quantity'];
                // $this->_cart->crypto_random = $randomKey_crypto;
                // $this->_cart->save();



            } else {
            //    return 12;
                if (isset($data['vartion_options']) && $data['vartion_options'] != "") {
                    $variationId = $this->getVariationsInfo($data['vartion_options'], $data['product_id']);
                    $this->_cart->variation_id = $variationId;
                }
                setcookie("cartCookieCrypto", $rand_number, time() + (2 * 24 * 60 * 60), "/");
                $this->_cart->product_id = $data['product_id'];
                if (isset($data['store_id'])) {
                    $this->_cart->store_id = $data['store_id'];
                }
                $this->_cart->product_quantity = $data['product_quantity'];
                $this->_cart->crypto_random = $rand_number;
                $this->_cart->save();
            }
            // return  ['random_number' =>$rand_number , 'payment_method' => 'crypto' ];
        }
        return $rand_number;
        // }
    }
    public function saveToCartApi($data)
    {
        $productAddedStatus = false;
        $maxBuyLimit = env('MAX_BUY_LIMIT');
        $maxQuantityLimit = "You’ve reached the maximum quantity allowed per purchase";
        $maxStockLimit = "Maximum stock available has been reached";
        
        foreach ($data['products'] as $singleProduct) {
            $storeInfo = getStoreInfoById($singleProduct['store_id']);
            $rand_number = $data['id'];
            $quantity = 0;
            $product = Product::where('product_id', $singleProduct['product_id'])->first();
            if (!isset($singleProduct['product_quantity'])) {
                $singleProduct['product_quantity'] = 1;
            }

            if ($storeInfo[0]->stripe == 1) {
                if (isset($singleProduct['variation_options']) && !empty($singleProduct['variation_options'])) {
                    $variationId = $this->getVariationsInfo($singleProduct['variation_options'], $singleProduct['product_id']);
                    $variableProduct = ProductVariation::where('variation_id', $variationId)->first();
                    if($data['guest']){
                        $productCart = $this->_productCart->where('random_key', $data['id'])->where('variation_id', $variationId)->first();
                        if ($productCart) {
                            $updatedQuantity = $productCart->product_quantity + $singleProduct['product_quantity'];

                            if ($updatedQuantity > $variableProduct->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            } elseif ($updatedQuantity > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            } else {
                                $this->_productCart->where('random_key', $data['id'])->where('variation_id', $variationId)->update(['product_quantity' => $updatedQuantity]);
                                $productAddedStatus = true;                                
                            }
                        } else {
                            $productCart = new ProductCart();
                            $productCart->variation_id = $variationId;
                            $productCart->product_id = $singleProduct['product_id'];
                            $productCart->store_id = $singleProduct['store_id'] ?? null;
                            $productCart->product_quantity = $singleProduct['product_quantity'];
                            $productCart->random_key = $data['id'];

                            if ($variableProduct && $variableProduct->current_stock >= $singleProduct['product_quantity'] && $singleProduct['product_quantity'] <= $maxBuyLimit) {
                                $productCart->save();
                                $productAddedStatus = true;
                            } if ($singleProduct['product_quantity'] > $variableProduct->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            } elseif ($singleProduct['product_quantity'] > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            }
                        }
                    }else{
                        $productCart = $this->_productCart->where('user_id', $data['id'])->where('variation_id', $variationId)->first();
                        if ($productCart) {
                            $updatedQuantity = $productCart->product_quantity + $singleProduct['product_quantity'];
                            if ($updatedQuantity > $variableProduct->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            } elseif ($updatedQuantity > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            } else {
                                $this->_productCart->where('user_id', $data['id'])->where('variation_id', $variationId)->update(['product_quantity' => $updatedQuantity]);
                                $productAddedStatus = true;                                
                            }
                        } else {
                            $productCart = new ProductCart();
                            $productCart->variation_id = $variationId;
                            $productCart->product_id = $singleProduct['product_id'];
                            $productCart->store_id = $singleProduct['store_id'] ?? null;
                            $productCart->product_quantity = $singleProduct['product_quantity'];
                            $productCart->user_id = $data['id'];
                            if ($variableProduct && $variableProduct->current_stock >= $singleProduct['product_quantity'] && $singleProduct['product_quantity'] <= $maxBuyLimit) {
                                $productCart->save();
                                $productAddedStatus = true;
                            } elseif ($singleProduct['product_quantity'] > $variableProduct->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            } elseif ($singleProduct['product_quantity'] > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            }
                        }
                    }
                    
                } else {
                    if($data['guest']){
                        $productCart = $this->_productCart->where('random_key', $data['id'])->where('product_id', $singleProduct['product_id'])->first();

                        if ($productCart) {
                            $updatedQuantity = $productCart->product_quantity + $singleProduct['product_quantity'];
                            if ($updatedQuantity > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            } elseif ($updatedQuantity > $product->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            } else {
                                $this->_productCart->where('random_key', $data['id'])->where('product_id', $singleProduct['product_id'])->update(['product_quantity' => $updatedQuantity]);
                                $productAddedStatus = true;
                            }
                        } else {
                            $productCart = new ProductCart();
                            $productCart->product_id = $singleProduct['product_id'];
                            $productCart->store_id = $singleProduct['store_id'] ?? null;
                            $productCart->product_quantity = $singleProduct['product_quantity'];
                            $productCart->random_key = $data['id'];
                            $product = Product::where('product_id', $singleProduct['product_id'])->first();
                            if ($product && $product->current_stock >= $singleProduct['product_quantity'] && $singleProduct['product_quantity'] <= $maxBuyLimit) {
                                $productCart->save();
                                $productAddedStatus = true;
                            } elseif ($singleProduct['product_quantity'] > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            } elseif ($singleProduct['product_quantity'] > $product->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            }
                        }
                    }else{
                        $productCart = $this->_productCart->where('user_id', $data['id'])->where('product_id', $singleProduct['product_id'])->first();
                        if ($productCart) {
                            $updatedQuantity = $productCart->product_quantity + $singleProduct['product_quantity'];
                            if ($updatedQuantity > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            } elseif ($updatedQuantity > $product->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            } else {
                                $this->_productCart->where('user_id', $data['id'])->where('product_id', $singleProduct['product_id'])->update(['product_quantity' => $updatedQuantity]);
                                $productAddedStatus = true;                                
                            }
                        } else {
                            $productCart = new ProductCart();
                            $productCart->product_id = $singleProduct['product_id'];
                            $productCart->store_id = $singleProduct['store_id'] ?? null;
                            $productCart->product_quantity = $singleProduct['product_quantity'];
                            $productCart->user_id = $data['id'];
                            $product = Product::where('product_id', $singleProduct['product_id'])->first();
                            
                            if ($product && $product->current_stock >= $singleProduct['product_quantity'] && $singleProduct['product_quantity'] <= $maxBuyLimit) {
                                $productCart->save();
                                $productAddedStatus = true;
                            } elseif($singleProduct['product_quantity'] > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            } elseif ($singleProduct['product_quantity'] > $product->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            }
                        }
                    }
                }
            }
        }

        return [
            'cart_id' => $productCart->cart_id,
            'user_id' => $rand_number,
            'status' => $productAddedStatus
        ];
    }

    public function updateCartQuantity($data)
    {
            $storeInfo =  getStoreInfoById($data['store_id']);
            $maxBuyLimit = env('MAX_BUY_LIMIT');
            $maxQuantityLimit = "You’ve reached the maximum quantity allowed per purchase";
            $maxStockLimit = "Maximum stock available has been reached";
            $rand_number = $data['id'];
            $quantity = 0;
            $product = Product::where('product_id', $data['product_id'])->first();
            if($data['guest']){
                $productCart = $this->_productCart->where('random_key', $data['id'])->where('product_id', $data['product_id'])->first();
                if ($productCart) {
                    if(isset($data['product_quantity_plus'])){
                        $updatQuantity = $productCart->product_quantity + 1;
                        if (isset($data['variation_id']) && $data['variation_id'] != "") {
                            $variationId = $data['variation_id'];
                            $productCart = $this->_productCart->where('random_key', $data['id'])->where('product_id', $data['product_id'])->where('variation_id',$variationId)->first();
                            if ($productCart) {
                                $productVariation = ProductVariation::where('variation_id', $variationId)->first();
                                $updatQuantity = $productCart->product_quantity + 1;
                                $productVariationId = $this->_productCart->where('random_key', '=', $data['id'])->where('variation_id', '=', $variationId)->count();
                                if ($productVariationId > 0 && $productVariation->current_stock >= $updatQuantity && $updatQuantity <= $maxBuyLimit) {
                                    $this->_productCart->where('random_key', $data['id'])->where('variation_id', '=', $variationId)->update(['product_quantity' => $updatQuantity]);
                                } elseif ($updatQuantity > $productVariation->current_stock) {
                                    $response['status'] = false;
                                    $response['message'] = $maxStockLimit;
                                    return $response;
                                } elseif ($updatQuantity > $maxBuyLimit) {
                                    $response['status'] = false;
                                    $response['message'] = $maxQuantityLimit;
                                    return $response;
                                } else {
                                    $response['status'] = true;
                                    $response['message'] = 'Cart Updated Successfully';
                                    return $response;
                                }
                            }
                        } else {
                            if ($product->current_stock >= $updatQuantity && $updatQuantity <= $maxBuyLimit) {
                                $this->_productCart->where('cart_id', '=', $productCart->cart_id)->update(['product_quantity' => $updatQuantity]);
                            } elseif ($updatQuantity > $product->current_stock) {
                                $response['status'] = false;
                                $response['message'] = $maxStockLimit;
                                return $response;
                            } elseif ($updatQuantity > $maxBuyLimit) {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            } else {
                                $response['status'] = true;
                                    $response['message'] = 'Cart Updated Successfully';
                                    return $response;
                            }
                        }
                    }else if(isset($data['product_quantity_minus'])){
                        $updatQuantity = $productCart->product_quantity - 1;
                        if (isset($data['variation_id']) && $data['variation_id'] != "") {
                            $variationId = $data['variation_id'];
                            $productCart = $this->_productCart->where('random_key', $data['id'])->where('product_id', $data['product_id'])->where('variation_id',$variationId)->first();
                            if ($productCart) {
                                $updatQuantity = $productCart->product_quantity - 1;
                                $productVariationId = $this->_productCart->where('random_key', $data['id'])->where('variation_id', '=', $variationId)->count();
                                if ($productVariationId > 0) {
                                    $this->_productCart->where('random_key', $data['id'])->where('variation_id', '=', $variationId)->update(['product_quantity' => $updatQuantity]);
                                }
                            }
                        } else {
    
                            $this->_productCart->where('cart_id', '=', $productCart->cart_id)->update(['product_quantity' => $updatQuantity]);
                        }
                    }
                    $response['status'] = true;
                                    $response['message'] = 'Cart Updated Successfully';
                                    return $response;
                }
                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
            }else{

            
                $productCart = $this->_productCart->where('user_id', $data['id'])->where('product_id', $data['product_id'])->first();
                if ($productCart) {
                    if(isset($data['product_quantity_plus'])){
                        $updatQuantity = $productCart->product_quantity + 1;
                        if (isset($data['variation_id']) && $data['variation_id'] != "") {
                            $variationId = $data['variation_id'];
                            $productCart = $this->_productCart->where('user_id', $data['id'])->where('product_id', $data['product_id'])->where('variation_id',$variationId)->first();
                            if ($productCart) {
                                $productVariation = ProductVariation::where('variation_id', $variationId)->first();
                                $updatQuantity = $productCart->product_quantity + 1;
                                $productVariationId = $this->_productCart->where('user_id', '=', $data['id'])->where('variation_id', '=', $variationId)->count();
                                if ($productVariationId > 0 && $productVariation->current_stock >= $updatQuantity && $updatQuantity <= $maxBuyLimit) {
                                    $this->_productCart->where('user_id', $data['id'])->where('variation_id', '=', $variationId)->update(['product_quantity' => $updatQuantity]);
                                } elseif ($updatQuantity > $productVariation->current_stock) {
                                    $response['status'] = false;
                                    $response['message'] = $maxStockLimit;
                                    return $response;
                                } elseif ($updatQuantity > $maxBuyLimit) {
                                    $response['status'] = false;
                                    $response['message'] = $maxQuantityLimit;
                                    return $response;
                                } else {
                                    $response['status'] = true;
                                    $response['message'] = 'Cart Updated Successfully';
                                    return $response;
                                }
                            }
                        } else {
                            if ($product->current_stock >= $updatQuantity && $updatQuantity <= $maxBuyLimit) {
                                $this->_productCart->where('cart_id', '=', $productCart->cart_id)->update(['product_quantity' => $updatQuantity]);
                            } else {
                                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
                            }
                        }
                    }else if(isset($data['product_quantity_minus'])){
                        $updatQuantity = $productCart->product_quantity - 1;
                        if (isset($data['variation_id']) && $data['variation_id'] != "") {
                            $variationId = $data['variation_id'];
                            $productCart = $this->_productCart->where('user_id', $data['id'])->where('product_id', $data['product_id'])->where('variation_id',$variationId)->first();
                            if ($productCart) {
                                $updatQuantity = $productCart->product_quantity - 1;
                                $productVariationId = $this->_productCart->where('user_id', '=', $data['id'])->where('variation_id', '=', $variationId)->count();
                                if ($productVariationId > 0) {
                                    $this->_productCart->where('user_id', $data['id'])->where('variation_id', '=', $variationId)->update(['product_quantity' => $updatQuantity]);
                                }
                            }
                        } else {
    
                            $this->_productCart->where('cart_id', '=', $productCart->cart_id)->update(['product_quantity' => $updatQuantity]);
                        }
                    }
                    $response['status'] = true;
                                    $response['message'] = 'Cart Updated Successfully';
                                    return $response;
                }
                $response['status'] = false;
                                $response['message'] = $maxQuantityLimit;
                                return $response;
            }
    }
    public function usercartDetail($randomKey = null)
    {
         
        if (!empty($_COOKIE['cartCookie'])) {
            $randomKey = $_COOKIE['cartCookie'];
        }

        $data = array();
        // $userId = Session::get('userData')['user_id'];
        $query = DB::table('products_cart')
            ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
            ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
            ->select(
                'products.product_id',
                'products.store_id',
                'products.user_id',
                'products.title',
                'products.price',
                'products.on_sale',
                'products.sale_price',
                'products.slug',
                'products.product_type',
                'products.feature_image',
                'products.maxquantitylimit',
                'products_cart.cart_id',
                'products_cart.product_quantity',
                'product_variation.variation_id',
                'product_variation.product_variation_image',
                'product_variation.option1',
                'product_variation.unit1',
                'product_variation.attribute1',
                'product_variation.attribute2',
                'product_variation.attribute3',
                'product_variation.option2',
                'product_variation.unit2',
                'product_variation.option3',
                'product_variation.unit3',
                'product_variation.price as variation_sale_price',
                'product_variation.sale_price as product_variation_sale_price'
            );

        $query->where('products_cart.random_key',$randomKey );

        $cartDetail = $query->get();


        if (!empty($cartDetail)) {
            $data['cart_data'] = objToArray($cartDetail);
            $data['total_quantity'] = DB::table('products_cart')->where('random_key', $randomKey)->sum('product_quantity');
            $data['random_key'] = $randomKey;
            // $data['total_quantity'] = DB::table('products_cart')->where('user_id', $userId)->sum('product_quantity');
            return $data;
        } else {
            $cartDetail = DB::table('products_cart')
                ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
                ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
                ->where('products_cart.random_key', $randomKey)
                //->where('products.productType', 'product')
                //                    ->groupby('products_cart.product_id')
                ->select(
                    'products.product_id',
                    'products.store_id',
                    'products.user_id',
                    'products.title',
                    'products.price',
                    'products.on_sale',
                    'products.sale_price',
                    'products.slug',
                    'products.product_type',
                    'products.feature_Image',
                    'products_cart.cart_id',
                    'products_cart.product_quantity',
                    'products.maxquantitylimit',
                    'product_variation.variation_id',
                    'product_variation.product_variation_image',
                    'product_variation.option1',
                    'product_variation.attribute1',
                    'product_variation.attribute2',
                    'product_variation.attribute3',
                    'product_variation.unit1',
                    'product_variation.option2',
                    'product_variation.unit2',
                    'product_variation.price as variation_sale_price',
                    'product_variation.sale_price as product_variation_sale_price'
                )
                ->get();
            $data['cart_data'] = objToArray($cartDetail);
            $data['total_quantity'] = DB::table('products_cart')->sum('product_quantity');
            $data['random_key'] = $randomKey;
            // $data['total_quantity'] = DB::table('products_cart')->where('user_id', $userId)->sum('product_quantity');
            return $data;
        }
    }
    public function usercartDetailApi($user)
    {

        $data = array();
        // $userId = Session::get('userData')['user_id'];
        $query = DB::table('products_cart')
            ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
            ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
            ->select(
                'products.product_id',
                'products.store_id',
                'products.user_id',
                'products.title',
                'products.price',
                'products.on_sale',
                'products.sale_price',
                'products.slug',
                'products.product_type',
                'products.feature_image',
                'products.maxquantitylimit',
                'products_cart.cart_id',
                'products_cart.product_quantity',
                'product_variation.variation_id',
                'product_variation.product_variation_image',
                'product_variation.option1',
                'product_variation.unit1',
                'product_variation.attribute1',
                'product_variation.attribute2',
                'product_variation.attribute3',
                'product_variation.option2',
                'product_variation.unit2',
                'product_variation.option3',
                'product_variation.unit3',
                'product_variation.price as variation_sale_price',
                'product_variation.sale_price as product_variation_sale_price'
            );
            if($user['guest']){
                $query->where('products_cart.random_key', $user['id']);
            }else{
                $query->where('products_cart.user_id', $user['id']);
            }

            $cartDetail = $query->get();
            if(isset($cartDetail)){
                $data['cart_data'] = objToArray($cartDetail);
            }
            if($user['guest']){
                $data['total_quantity'] = DB::table('products_cart')->where('random_key', $user['id'])->count();
            }else{
                $data['total_quantity'] = DB::table('products_cart')->where('user_id', $user['id'])->count();
            }
            $data['user_id'] = $user['id'];
            return $data;
        
    }
    public function usercartDetailCrypto($randomKey = null)
    {
        //  dd($randomKey);
        if (!empty($_COOKIE['cartCookieCrypto'])) {
            $randomKey = $_COOKIE['cartCookieCrypto'];
        }

        $data = array();
        // $userId = Session::get('userData')['user_id'];
        $query = DB::table('products_cart')
            ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
            ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
            ->select(
                'products.product_id',
                'products.store_id',
                'products.user_id',
                'products.title',
                'products.price',
                'products.on_sale',
                'products.sale_price',
                'products.slug',
                'products.product_type',
                'products.feature_image',
                'products_cart.cart_id',
                'products_cart.product_quantity',
                'product_variation.variation_id',
                'product_variation.product_variation_image',
                'product_variation.option1',
                'product_variation.unit1',
                'product_variation.attribute1',
                'product_variation.attribute2',
                'product_variation.attribute3',
                'product_variation.option2',
                'product_variation.unit2',
                'product_variation.option3',
                'product_variation.unit3',
                'product_variation.price as variation_sale_price',
                'product_variation.sale_price as product_variation_sale_price'
            );

        $query->where('products_cart.crypto_random', $randomKey);

        $cartDetail = $query->get();


        if (!empty($cartDetail)) {
            $data['cart_data'] = objToArray($cartDetail);
            $data['total_quantity'] = DB::table('products_cart')->where('crypto_random', $randomKey)->sum('product_quantity');
            // $data['total_quantity'] = DB::table('products_cart')->where('user_id', $userId)->sum('product_quantity');
            return $data;
        } else {
            $cartDetail = DB::table('products_cart')
                ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
                ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
                ->where('products_cart.crypto_random', $randomKey)
                //->where('products.productType', 'product')
                //                    ->groupby('products_cart.product_id')
                ->select(
                    'products.product_id',
                    'products.store_id',
                    'products.user_id',
                    'products.title',
                    'products.price',
                    'products.on_sale',
                    'products.sale_price',
                    'products.slug',
                    'products.product_type',
                    'products.feature_Image',
                    'products_cart.cart_id',
                    'products_cart.product_quantity',
                    'product_variation.variation_id',
                    'product_variation.product_variation_image',
                    'product_variation.option1',
                    'product_variation.attribute1',
                    'product_variation.attribute2',
                    'product_variation.attribute3',
                    'product_variation.unit1',
                    'product_variation.option2',
                    'product_variation.unit2',
                    'product_variation.price as variation_sale_price',
                    'product_variation.sale_price as product_variation_sale_price'
                )
                ->get();
            $data['cart_data'] = objToArray($cartDetail);
            $data['total_quantity'] = DB::table('products_cart')->sum('product_quantity');
            // $data['total_quantity'] = DB::table('products_cart')->where('user_id', $userId)->sum('product_quantity');
            return $data;
        }
    }

    public function getVariationsInfo($data, $productId)
    {
        if (isset($data[1]) && $data[1] != '' && isset($data[0]) && $data[0] != '' && isset($data[2]) && $data[2] != '') {
            DB::enableQueryLog();
            $results = ProductVariation::where('product_id', '=', $productId)
                ->where('option1', '=', $data[0])
                ->where('option2', '=', $data[1])
                ->where('option3', '=', $data[2])
                ->select('variation_id')
                ->first()
                ->toArray();
            return $results['variation_id'];
        }
        if (isset($data[1]) && $data[1] != '' && isset($data[0]) && $data[0] != '') {
            $results = ProductVariation::where('product_id', '=', $productId)
                ->where('option1', '=', $data[0])
                ->where('option2', '=', $data[1])
                ->select('variation_id')
                ->first()
                ->toArray();
            return $results['variation_id'];
        }
        if (isset($data[1]) && $data[1] != '' && isset($data[2]) && $data[2] != '') {
            $results = ProductVariation::where('product_id', '=', $productId)
                ->where('option2', '=', $data[1])
                ->where('option3', '=', $data[2])
                ->select('variation_id')
                ->first()
                ->toArray();
            return $results['variation_id'];
        }
        if (isset($data[0]) && $data[0] != '' && isset($data[2]) && $data[2] != '') {
            $results = ProductVariation::where('product_id', '=', $productId)
                ->where('option1', '=', $data[0])
                ->where('option3', '=', $data[2])
                ->select('variation_id')
                ->first()
                ->toArray();
            return $results['variation_id'];
        }

        if (isset($data[0]) && $data[0] != '') {
            $results = ProductVariation::where('product_id', '=', $productId)
                ->where('option1', '=', $data[0])
                ->select('variation_id')
                ->first()
                ->toArray();
            return $results['variation_id'];
        }
        if (isset($data[1]) && $data[1] != '') {
            $results = ProductVariation::where('product_id', '=', $productId)
                ->where('option2', '=', $data['variation2'])
                ->select('variation_id')
                ->first()
                ->toArray();
            return $results['variation_id'];
        }
        if (isset($data[2]) && $data[2] != '') {
            $results = ProductVariation::where('product_id', '=', $productId)
                ->where('option2', '=', $data['variation3'])
                ->select('variation_id')
                ->first()
                ->toArray();
            return $results['variation_id'];
        }
    }

    public function cartData()
    {
        $arr = array();
        if (Session::has('userData')) {
            $userId = Session::get('userData')['user_id'];
            $cartDetail = DB::table('products_cart')
                ->select('products.product_id', 'products.store_id', 'products.user_id', 'products.title', 'products.price', 'products.feature_Image', 'shipping_options.handling_fee', 'products_cart.cart_id', 'products_cart.product_quantity', 'stores.shop_name', 'shipping_options.processing_time', 'product_variation.variation_id', 'product_variation.option1', 'product_variation.unit1', 'product_variation.option2', 'product_variation.unit2', 'product_variation.quantity as product_variation_quantity', 'product_variation.price as variation_sale_price')
                ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
                ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
                ->Join('stores', 'products_cart.store_id', '=', 'stores.store_id')
                ->where('products_cart.user_id', $userId)
                ->where('products.productType', 'product')
                ->get();
            $prodcutData = $this->objToArray($cartDetail);
            foreach ($prodcutData as $key => $val) {
                $arr[$val['store_id']][] = $val;
            }

            if (!empty($arr)) {
                return $arr;
            } else {
                return false;
            }
        } else if (!empty($_COOKIE['cartCookie'])) {
            $key = $_COOKIE['cartCookie'];
            $cartDetail = DB::table('products_cart')
                ->select('products.product_id', 'products.store_id', 'products.user_id', 'products.title', 'products.price', 'products.feature_Image', 'shipping_options.handling_fee', 'products_cart.cart_id', 'products_cart.product_quantity', 'stores.shop_name', 'shipping_options.processing_time', 'product_variation.variation_id', 'product_variation.option1', 'product_variation.unit1', 'product_variation.option2', 'product_variation.quantity as product_variation_quantity', 'product_variation.unit2', 'product_variation.price as variation_sale_price')
                ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
                ->Join('stores', 'products_cart.store_id', '=', 'stores.store_id')
                ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
                ->where('products_cart.random_key', $key)
                ->where('products.productType', 'product')
                ->get();
            $prodcutData = $this->objToArray($cartDetail);
            foreach ($prodcutData as $key => $val) {
                $arr[$val['store_id']][] = $val;
            }

            if (!empty($arr)) {
                return $arr;
            } else {
                return false;
            }
        }
    }

    public function cartDataNew()
    {
        $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
        // $zipCode = $location['postal'];
        $zipCode = '36117';
        $originZipCode = "91601";
        $productWeight = "";
        // $userId = Session::get('userData')['user_id'];
        // if (empty($userId)) {
        //     $userId = Request::segment(2);
        // }
        $price = "";
        $alldata = array();
        $arr = array();

        if (isset($_COOKIE['cartCookie']) && !empty($_COOKIE['cartCookie'])) {

            $key = $_COOKIE['cartCookie'];
            $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store', 'categories'])->where('random_key', $key)->get();
            // echo 'Data: <pre>' .print_r($cartDetail,true). '</pre>'; die();
           
            if (!empty($cartDetail)) {

                $prodcutData = $this->objToArray($cartDetail);

                foreach ($prodcutData as $key => $pd) {
                    $arr['product_id'] = $pd['product_id'];
                    $arr['store_id'] = $pd['store_id'];
                    $arr['slug'] = $pd['product']['slug'];
                    $arr['user_id'] = $pd['product']['user_id'];
                    $arr['title'] = $pd['product']['title'];
                    $arr['product_type'] = $pd['product']['product_type'];
                    // $arr['price'] = 0.40;
                    $arr['price'] = $pd['product']['price'];
                    $arr['on_sale'] = $pd['product']['on_sale'];
                    $arr['sale_price'] = $pd['product']['sale_price'];
                    $arr['feature_image'] = $pd['product']['feature_image'];
                    $arr['cart_id'] = $pd['cart_id'];
                    $arr['product_quantity'] = $pd['product_quantity'];
                    $arr['current_stock'] = $pd['product']['current_stock'];
                    $arr['maxquantitylimit'] = isset($pd['product']['maxquantitylimit']) ? $pd['product']['maxquantitylimit'] : "";
                    $arr['store_name'] = $pd['store']['store_name'];
                    $arr['stripe'] = $pd['store']['stripe'];
                    $arr['wallet_address'] = $pd['store']['wallet_address'];
                    $arr['store_zip_code'] = $pd['store']['zip_code'];
                    $arr['variation_id'] = isset($pd['variation']['variation_id']) ? $pd['variation']['variation_id'] : "" ;
                    $arr['variable_currentstock'] = isset($pd['variation']['current_stock']) ? $pd['variation']['current_stock'] : "";
                    $arr['product_variation_image'] = isset($pd['variation']['product_variation_image']) ? $pd['variation']['product_variation_image'] : "";
                    $arr['attribute1'] = isset($pd['variation']['attribute1']) ? $pd['variation']['attribute1'] : "";
                    $arr['attribute2'] = isset($pd['variation']['attribute2']) ? $pd['variation']['attribute2'] : "";
                    $arr['attribute3'] = isset($pd['variation']['attribute3']) ? $pd['variation']['attribute3'] : "";
                    $arr['variable_maxquantitylimit'] = isset($pd['variation']['maxquantitylimit']) ? $pd['variation']['maxquantitylimit'] : "";
                    $arr['option1'] = isset($pd['variation']['option1']) ? $pd['variation']['option1'] : "";
                    $arr['option2'] = isset($pd['variation']['option2']) ? $pd['variation']['option2'] : "";
                    $arr['option3'] = isset($pd['variation']['option3']) ? $pd['variation']['option3'] : "";
                    $arr['unit1'] = isset($pd['variation']['unit1']) ?  $pd['variation']['unit1'] : "";
                    $arr['unit2'] = isset($pd['variation']['unit2']) ? $pd['variation']['unit2'] : "";
                    $arr['unit3'] = isset($pd['variation']['unit3']) ? $pd['variation']['unit3'] : "";
                    $arr['weight'] = $pd['product']['weight'];
                    $arr['status'] = $pd['product']['status'];
                    $arr['productSize_length'] = $pd['product']['productSize_length'];
                    $arr['productSize_width'] = $pd['product']['productSize_width'];
                    $arr['productSize_height'] = $pd['product']['productSize_height'];
                    $arr['store_country'] = $pd['store']['store_country'];
                    $arr['flashsale_status'] = $pd['product']['flashsale_status'];
                    $arr['shipping_option_id'] = $pd['product']['shipping_option_id'];
                    $arr['store_state'] = $pd['store']['store_state'];
                    $arr['cart_count'] = $pd['cart_count'];
                    if (isset($pd['variation'])){
                        $arr['product_variation_price'] = $pd['variation']['price'];
                        $arr['product_variation_sale_price'] =$pd['variation']['sale_price'];
                    }else {
                        $arr['product_variation_price'] = null;
                        $arr['product_variation_sale_price'] = null;
                    }
                    if ($arr['product_type'] == 'variant'){
                            $price = $arr['product_variation_price'];
                    }else {
                        $price = $arr['on_sale'] == 1 ? $arr['sale_price'] : $arr['price'];
                    }

                    // $productTax = $this->calculateTax($arr['store_state'], $arr['store_country'], $price, $pd['product_quantity']);

                    $arr['product_tax'] = 0;
                    // }
                    $arr['shipping_price'] = 0;
                    
                    $arr['expected-delivery-date'] = 0;
                    array_push($alldata, $arr);
                    
                }
                return $alldata;
            } else {
                return false;
            }
        }
    }

    public function cartDataNewApi($postedData)
    {

        $alldata = array();
        $arr = array();
        // if (isset($_COOKIE['cartCookie']) && !empty($_COOKIE['cartCookie'])) {

            // $key = $_COOKIE['cartCookie'];
            $user_id = $postedData['id'];
            if($postedData['guest']){
                $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store'])->where('random_key', $user_id)->get();

            }else{
                $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store'])->where('user_id', $user_id)->get();
            }
            // echo 'Data: <pre>' .print_r($cartDetail,true). '</pre>'; die();
            if (!empty($cartDetail)) {

                $prodcutData = $this->objToArray($cartDetail);

                foreach ($prodcutData as $key => $pd) {
                    $product = Product::where('product_id', $pd['product_id'])->first();
                    if ($product) {
                        $arr['product_id'] = $pd['product_id'];
                        $arr['store_id'] = $pd['store_id'];
                        $arr['slug'] = $pd['product']['slug'];
                        $arr['user_id'] = $pd['product']['user_id'];
                        $arr['random_key'] = $pd['random_key'];
                        $arr['title'] = $pd['product']['title'];
                        $arr['product_type'] = $pd['product']['product_type'];
                        $arr['price'] = $pd['product']['price'];
                        $arr['on_sale'] = $pd['product']['on_sale'];
                        $arr['sale_price'] = $pd['product']['sale_price'];
                        $arr['feature_image'] = $pd['product']['feature_image'];
                        $arr['cart_id'] = $pd['cart_id'];
                        $arr['is_check'] = $pd['is_check'];
                        $arr['product_quantity'] = $pd['product_quantity'];
                        $arr['current_stock'] = $pd['product']['current_stock'];
                        $arr['discount_percentage'] = $pd['product']['discount_percentage'];
                        $arr['maxquantitylimit'] = isset($pd['product']['maxquantitylimit']) ? $pd['product']['maxquantitylimit'] : "";
                        $arr['store_name'] = $pd['store']['store_name'];
                        $arr['stripe'] = $pd['store']['stripe'];
                        $arr['wallet_address'] = $pd['store']['wallet_address'];
                        $arr['store_zip_code'] = $pd['store']['zip_code'];
                        $arr['variation_id'] = $pd['variation_id'];
                        $arr['variable_currentstock'] = isset($pd['variation']['current_stock']) ? $pd['variation']['current_stock'] : "";
                        $arr['product_variation_image'] = isset($pd['variation']['product_variation_image']) ? $pd['variation']['product_variation_image'] : "";
                        $arr['product_variation_price'] = isset($pd['variation']['price']) ? $pd['variation']['price'] : "";
                        $arr['product_variation_sale_price'] = isset($pd['variation']['sale_price']) ? $pd['variation']['sale_price'] : 0;
                        $arr['variation_discount_percentage'] = isset($pd['variation']['discount_percentage']) ? $pd['variation']['discount_percentage'] : 0;
                        $arr['attribute1'] = isset($pd['variation']['attribute1']) ? $pd['variation']['attribute1'] : "";
                        $arr['attribute2'] = isset($pd['variation']['attribute2']) ? $pd['variation']['attribute2'] : "";
                        $arr['attribute3'] = isset($pd['variation']['attribute3']) ? $pd['variation']['attribute3'] : "";
                        $arr['variable_maxquantitylimit'] = isset($pd['variation']['maxquantitylimit']) ? $pd['variation']['maxquantitylimit'] : "";
                        $arr['option1'] = isset($pd['variation']['option1']) ? $pd['variation']['option1'] : "";
                        $arr['option2'] = isset($pd['variation']['option2']) ? $pd['variation']['option2'] : "";
                        $arr['option3'] = isset($pd['variation']['option3']) ? $pd['variation']['option3'] : "";
                        $arr['unit1'] = isset($pd['variation']['unit1']) ?  $pd['variation']['unit1'] : "";
                        $arr['unit2'] = isset($pd['variation']['unit2']) ? $pd['variation']['unit2'] : "";
                        $arr['unit3'] = isset($pd['variation']['unit3']) ? $pd['variation']['unit3'] : "";
                        $arr['weight'] = $pd['product']['weight'];
                        $arr['productSize_length'] = $pd['product']['productSize_length'];
                        $arr['productSize_width'] = $pd['product']['productSize_width'];
                        $arr['productSize_height'] = $pd['product']['productSize_height'];
                        $arr['store_country'] = $pd['store']['store_country'];
                        $arr['flashsale_status'] = $pd['product']['flashsale_status'];
                        $arr['shipping_option_id'] = $pd['product']['shipping_option_id'];
                        $arr['store_state'] = $pd['store']['store_state'];

                        if (isset($arr['product_variation_price'])) {
                            $price = $arr['product_variation_price'];
                        } else {
                            $price = $arr['on_sale'] == 1 ? $arr['sale_price'] : $arr['price'];
//                            $price = $arr['price'];
                        }

                        $arr['product_tax'] = 0;
                        $arr['shipping_price'] = 0;

                        $arr['expected-delivery-date'] = 0;

                        array_push($alldata, $arr);
                    }
                }
                return $alldata;
            } else {
                return false;
            }
        
       
    }
    public function cartDataNewCrypto()
    {

        $price = "";
        $alldata = array();
        $arr = array();

        if (isset($_COOKIE['cartCookieCrypto']) && !empty($_COOKIE['cartCookieCrypto'])) {

            $key = $_COOKIE['cartCookieCrypto'];

            $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store'])->where('crypto_random', $key)->get();
            //  echo 'Data: <pre>' .print_r($cartDetail,true). '</pre>'; die();

            if (!empty($cartDetail)) {

                $prodcutData = $this->objToArray($cartDetail);

                foreach ($prodcutData as $key => $pd) {
                    $arr['product_id'] = $pd['product_id'];
                    $arr['store_id'] = $pd['store_id'];
                    $arr['slug'] = $pd['product']['slug'];
                    $arr['user_id'] = $pd['product']['user_id'];
                    $arr['title'] = $pd['product']['title'];
                    $arr['product_type'] = $pd['product']['product_type'];
                    $arr['price'] = $pd['product']['price'];
                    $arr['on_sale'] = $pd['product']['on_sale'];
                    $arr['sale_price'] = $pd['product']['sale_price'];
                    $arr['feature_image'] = $pd['product']['feature_image'];
                    $arr['cart_id'] = $pd['cart_id'];
                    $arr['product_quantity'] = $pd['product_quantity'];
                    $arr['current_stock'] = $pd['product']['current_stock'];
                    $arr['store_name'] = $pd['store']['store_name'];
                    $arr['stripe'] = $pd['store']['stripe'];
                    $arr['wallet_address'] = $pd['store']['wallet_address'];
                    $arr['store_zip_code'] = $pd['store']['zip_code'];
                    $arr['variation_id'] = $pd['variation_id'];
                    $arr['variable_currentstock'] = isset($pd['variation']['current_stock']) ? $pd['variation']['current_stock'] : "";
                    $arr['product_variation_image'] = isset($pd['variation']['product_variation_image']) ? $pd['variation']['product_variation_image'] : "";
                    $arr['product_variation_price'] = isset($pd['variation']['variation_sale_price']) ? $pd['variation']['variation_sale_price'] : "";
                    $arr['attribute1'] = isset($pd['variation']['attribute1']) ? $pd['variation']['attribute1'] : "";
                    $arr['attribute2'] = isset($pd['variation']['attribute2']) ? $pd['variation']['attribute2'] : "";
                    $arr['attribute3'] = isset($pd['variation']['attribute3']) ? $pd['variation']['attribute3'] : "";
                    $arr['option1'] = isset($pd['variation']['option1']) ? $pd['variation']['option1'] : "";
                    $arr['option2'] = isset($pd['variation']['option2']) ? $pd['variation']['option2'] : "";
                    $arr['option3'] = isset($pd['variation']['option3']) ? $pd['variation']['option3'] : "";
                    $arr['unit1'] = isset($pd['variation']['unit1']) ?  $pd['variation']['unit1'] : "";
                    $arr['unit2'] = isset($pd['variation']['unit2']) ? $pd['variation']['unit2'] : "";
                    $arr['unit3'] = isset($pd['variation']['unit3']) ? $pd['variation']['unit3'] : "";
                    $arr['weight'] = $pd['product']['weight'];
                    $arr['productSize_length'] = $pd['product']['productSize_length'];
                    $arr['productSize_width'] = $pd['product']['productSize_width'];
                    $arr['productSize_height'] = $pd['product']['productSize_height'];
                    $arr['store_country'] = $pd['store']['store_country'];
                    $arr['flashsale_status'] = $pd['product']['flashsale_status'];
                    $arr['shipping_option_id'] = $pd['product']['shipping_option_id'];
                    $arr['store_state'] = $pd['store']['store_state'];

                    if (isset($arr['product_variation_price'])) {
                        $price = $arr['product_variation_price'];
                    } else {
                        $price = $arr['on_sale'] == 1 ? $arr['sale_price'] : $arr['price'];
//                        $price = $arr['price'];
                    }

                    $arr['product_tax'] = 0;

                    $arr['shipping_price'] = 0;

                    $arr['expected-delivery-date'] = 0;

                    array_push($alldata, $arr);
                    // echo '$alldata: <pre>' .print_r($alldata,true). '</pre>'; die();
                }
                return $alldata;
            } else {
                return false;
            }
            // }else{
            //     header('HTTP/1.0 400 Bad Request');
            //     echo 'Your cart has empty. Please add the product to your cart again.';
            //     exit;
        }
    }

    public function updateCart($data)
    {
        // echo '$data: <pre>' .print_r($data,true). '</pre>'; die();
        $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
        // $zipCode = $location['postal'];
        $productWeight = "";
        $zipCode = '90001';
        $originZipCode = "91601";

        $arr = array();
        $this->_cart->where('cart_id', '=', $data['cart_id'])->update(['product_quantity' => $data['product_quantity']]);
        $producquantity = $this->_cart->where('cart_id', '=', $data['cart_id'])->select('product_quantity', 'product_id')->first()->toArray();
        $prodcutData = $this->_product->select('price', 'weight', 'shipping_option_id', 'store_id')->where('product_id', '=', $producquantity['product_id'])->first()->toArray();
        if (isset($data['variation_id'])) {
            $variationPrice = $this->_variation->where('variation_id', '=', $data['variation_id'])->first();
            // echo '$variationPrice: <pre>' .print_r($variationPrice,true). '</pre>'; 
            if (!empty($variationPrice)) {
                $variationPrice = $variationPrice->toArray();
                $price = $variationPrice['price'];
            }
        } else {
            $price = $prodcutData['price'];
        }
        //  echo '$price: <pre>' .print_r($price,true). '</pre>'; 

        //        if ($prodcutData['product_currency'] == 'usd') {
        //            //$price = currencyConverter($price);
        //            $price = number_format((float) $price, 2, '.', '');
        //        }
        $totalPrice = 0;
        $productWeight = $producquantity['product_quantity'] * $prodcutData['weight'];
        $arr['ship_price_preproduct'] = 0;
        $arr['ship_price_preproduct'] = number_format($arr['ship_price_preproduct'], 2);
        $totalProductPrice = (float) $price * (float) $producquantity['product_quantity'];
        // echo '$totalProductPrice: <pre>' .print_r($totalProductPrice,true). '</pre>'; die();
        // if (isset($prodcutData['shipping_option_id']) && $prodcutData['shipping_option_id'] != "") {
        //     $query = DB::table('shipping_options')->where('id', $prodcutData['shipping_option_id'])->first();
        //     $shipWeight = $producquantity['product_quantity'] * $query->shipping_weight;
        //     if(!empty($query->origin_zipcode)) {
        //         $originZipCode = $query->origin_zipcode;
        //     }


        //     if (!empty($query) && $query->is_domesticfree == 0) {
        //         $shipService = $query->shipping_service;
        //         $handlingFee = !empty($query->domestic_handling_fee) ? $query->domestic_handling_fee : 0;
        //         $handlingFee = $handlingFee * 10;
        //         if (!empty($query) && $query->shipping_country_id == 1) {
        //             $arr['ship_price_preproduct'] = number_format((float) $query->flat_shipping_rate + (float) $handlingFee, 2);
        //         } else {
        //             $totalReq = "";
        //             $onePrice = 0;
        //             $productShippingPrice = 0;
        //             if ($productWeight > 70) {

        //                 $totalReq = ceil($productWeight / 70);
        //                 for ($i = 0; $i <= $totalReq; $i++) {
        //                    // $onePrice = $this->shiprate->getDpmesticRates('91730', '91601', 70);
        //                     $productShippingPrice = $productShippingPrice + $onePrice;
        //                 }
        //                 $arr['ship_price_preproduct'] = $productShippingPrice;
        //             }
        //            // $arr['ship_price_preproduct'] = $this->shiprate->getDpmesticRates($originZipCode, $zipCode , $productWeight) + $handlingFee;

        //         }
        //     }
        // }

        // $totalPrice = $totalProductPrice + $arr['ship_price_preproduct'];
        $totalPrice = $totalProductPrice;
        $arr['total_price'] = number_format($totalPrice, 2);
        return $arr;
    }

    public function delProduct($data)
    {

        if (!empty($_COOKIE['cartCookie'])) {
            $this->_cart->where('cart_id', '=', $data['cart_id'])->where('random_key', '=', $_COOKIE['cartCookie'])->delete();
            $count = $this->_cart->where('random_key', '=', $_COOKIE['cartCookie'])->count();
            if ($count == 0) {
                unset($_COOKIE['cartCookie']);
                setcookie('cartCookie', null, -1, '/');
                unset($_COOKIE['cartCookieCrypto']);
                setcookie('cartCookieCrypto', null, -1, '/');
            }
            return $count;
        } else {
            return false;
        }
    }

    public function delProductApi($data)
    {
        if(is_array($data['cart_id'])){
            $response = $this->_cart->whereIn('cart_id', $data['cart_id'])->delete();

        }else{
            $response = $this->_cart->where('cart_id', '=', $data['cart_id'])->delete();

        }

        if ($response == 0) {
            unset($_COOKIE['cartCookie']);
            setcookie('cartCookie', null, -1, '/');
            unset($_COOKIE['cartCookieCrypto']);
            setcookie('cartCookieCrypto', null, -1, '/');
        }
        return $response;
    }

    public function checkedToogleProductCart($data)
    {
        $response =   $this->_cart->where('cart_id', '=', $data['cart_id'])->update(['is_check' => $data['is_check']]);
        return $response;
    }
    public function delCheckoutProduct($data)
    {

        if (!empty($_COOKIE['cartCookie'])) {
            // dd($data);
            //    if 
            $productId = isset($data['productId']) && !empty($data['productId']) ? $data['productId'] : null;
            $variationId = isset($data['variationId']) && !empty($data['variationId']) ? $data['variationId'] : null;

            if ($productId && $variationId) {

                $item = $this->_cart->where('product_id', $productId)->where('variation_id', $variationId)->where('random_key', '=', $_COOKIE['cartCookie']);



                if ($item->delete()) {

                    $count = $this->_cart->where('random_key', '=', $_COOKIE['cartCookie'])->count();
                    if ($count == 0) {
                        unset($_COOKIE['cartCookie']);
                        setcookie('cartCookie', null, -1, '/');
                        unset($_COOKIE['cartCookieCrypto']);
                        setcookie('cartCookieCrypto', null, -1, '/');
                    }


                    return response()->json(['status' => true, 'message' => 'Product removed successfully']);
                } else {

                    return response()->json(['status' => false, 'message' => 'Failed to remove product']);
                }
            } else {
                $item = $this->_cart->where('product_id', $productId)->where('random_key', '=', $_COOKIE['cartCookie']);
                if ($item->delete()) {
                    $count = $this->_cart->where('random_key', '=', $_COOKIE['cartCookie'])->count();
                    if ($count == 0) {
                        unset($_COOKIE['cartCookie']);
                        setcookie('cartCookie', null, -1, '/');
                        unset($_COOKIE['cartCookieCrypto']);
                        setcookie('cartCookieCrypto', null, -1, '/');
                    }

                    return response()->json(['status' => true, 'message' => 'Product removed successfully']);
                } else {

                    return response()->json(['status' => false, 'message' => 'Failed to remove product']);
                }
            }

            // $this->_cart->where('cart_id', '=', $data['cart_id'])->where('random_key', '=', $_COOKIE['cartCookie'])->delete();

        } else {
            return false;
        }
    }
    public function delCheckoutProductApi($data)
    {

        if (!empty($_COOKIE['cartCookie'])) {
            // dd($data);
            //    if 
            $productId = isset($data['productId']) && !empty($data['productId']) ? $data['productId'] : null;
            $variationId = isset($data['variationId']) && !empty($data['variationId']) ? $data['variationId'] : null;

            if ($productId && $variationId) {

                $item = $this->_cart->where('product_id', $productId)->where('variation_id', $variationId)->where('random_key', '=', $_COOKIE['cartCookie']);



                if ($item->delete()) {

                    $count = $this->_cart->where('random_key', '=', $_COOKIE['cartCookie'])->count();
                    if ($count == 0) {
                        unset($_COOKIE['cartCookie']);
                        setcookie('cartCookie', null, -1, '/');
                        unset($_COOKIE['cartCookieCrypto']);
                        setcookie('cartCookieCrypto', null, -1, '/');
                    }


                    return response()->json(['status' => true, 'message' => 'Product removed successfully']);
                } else {

                    return response()->json(['status' => false, 'message' => 'Failed to remove product']);
                }
            } else {
                $item = $this->_cart->where('product_id', $productId)->where('random_key', '=', $_COOKIE['cartCookie']);
                if ($item->delete()) {
                    $count = $this->_cart->where('random_key', '=', $_COOKIE['cartCookie'])->count();
                    if ($count == 0) {
                        unset($_COOKIE['cartCookie']);
                        setcookie('cartCookie', null, -1, '/');
                        unset($_COOKIE['cartCookieCrypto']);
                        setcookie('cartCookieCrypto', null, -1, '/');
                    }

                    return response()->json(['status' => true, 'message' => 'Product removed successfully']);
                } else {

                    return response()->json(['status' => false, 'message' => 'Failed to remove product']);
                }
            }

            // $this->_cart->where('cart_id', '=', $data['cart_id'])->where('random_key', '=', $_COOKIE['cartCookie'])->delete();

        } else {
            return false;
        }
    }

    public function delProductAjax($data)
    {;
        $this->_cart->where('product_id', '=', $data['product_id'])->delete();
        $count = $this->_cart->where('random_key', '=', $_COOKIE['cartCookie'])->count();
        if ($count == 0) {
            unset($_COOKIE['cartCookie']);
            setcookie('cartCookie', null, -1, '/');
            unset($_COOKIE['cartCookieCrypto']);
            setcookie('cartCookieCrypto', null, -1, '/');
        }
    }
    public function delProductCrypto($data)
    {
        if (!empty($_COOKIE['cartCookieCrypto'])) {
            $this->_cart->where('cart_id', '=', $data['cart_id'])->where('crypto_random', '=', $_COOKIE['cartCookieCrypto'])->delete();
            $count = $this->_cart->where('random_key', '=', $_COOKIE['cartCookieCrypto'])->count();
            if ($count == 0) {

                unset($_COOKIE['cartCookieCrypto']);
                setcookie('cartCookieCrypto', null, -1, '/');
            }
        } else {
            return false;
        }
    }

    public function calculateInfo($data)
    {
        $productWeight = '';
        $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
        // $zipCode = $location['postal'];
        $arr = array();
        $arr2 = array();
        $alldata = array();
        $zipCode = '90001';
        $originZipCode = "91601";
        // $userId = Session::get('userData')['user_id'];
        $query = DB::table('products_cart')
            ->select('products.product_id', 'products.store_id', 'products.user_id','products.on_sale','products.sale_price', 'products.title', 'products.price', 'products.feature_image', 'products.weight', 'products.productSize_length', 'products.productSize_width', 'products.productSize_height', 'products_cart.cart_id', 'products_cart.product_quantity', 'stores.store_name', 'product_variation.variation_id', 'product_variation.option1', 'product_variation.unit1', 'product_variation.option2', 'product_variation.unit2', 'product_variation.quantity as product_variation_quantity', 'product_variation.price as product_variation_price', 'product_variation.sale_price as product_variation_sale_price','products.productSize_length', 'products.productSize_width', 'products.productSize_height', 'products.shipping_option_id', 'stores.store_state', 'stores.store_country', 'stores.zip_code')
            ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
            ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
            ->LeftJoin('stores', 'products_cart.store_id', '=', 'stores.store_id');
        // if (Session::has('userData')) {
        //     $query->where('products_cart.user_id', $userId);
        // } else 
        if (!empty($_COOKIE['cartCookie'])) {
            $key = $_COOKIE['cartCookie'];
            $query->where('products_cart.random_key', $key);
            $cartDetail = $query->get();
        } else {
            $cartDetail = [];
        }
       
        if (!empty($cartDetail)) {
            $storeData = $this->objToArray($cartDetail);

            foreach ($storeData as $key => $pd) {
                $arr['product_id'] = $pd['product_id'];
                $arr['store_id'] = $pd['store_id'];
                $arr['title'] = $pd['title'];
                $arr['price'] = $pd['price'];
                $arr['on_sale'] = $pd['on_sale'];
                $arr['sale_price'] = $pd['sale_price'];
                $arr['feature_image'] = $pd['feature_image'];
                $arr['cart_id'] = $pd['cart_id'];
                $arr['product_quantity'] = $pd['product_quantity'];
                $arr['store_name'] = $pd['store_name'];
                $arr['store_zipcode'] = $pd['zip_code'];
                $arr['variation_id'] = $pd['variation_id'];
                $arr['option1'] = $pd['option1'];
                $arr['unit1'] = $pd['unit1'];
                $arr['option2'] = $pd['option2'];
                $arr['weight'] = $pd['weight'];
                $arr['unit2'] = $pd['unit2'];
                $arr['product_variation_price'] = $pd['product_variation_price'];
                $arr['product_variation_sale_price'] = $pd['product_variation_sale_price'];
                $arr['productSize_length'] = $pd['productSize_length'];
                $arr['productSize_width'] = $pd['productSize_width'];
                $arr['productSize_height'] = $pd['productSize_height'];
                $arr['store_country'] = $pd['store_country'];
                $arr['store_state'] = $pd['store_state'];
                $arr['shipping_option_id'] = $pd['shipping_option_id'];
                if (!empty($userId)) {
                    if (isset($arr['product_variation_price'])) {
                        $price = $arr['product_variation_price'];
                    } else {
                        $price = $arr['on_sale'] == 1 ? $arr['sale_price'] : $arr['price'];
//                        $price = $arr['price'];
                    }

                    $arr['product_tax'] = 0;
                }
                $arr['shipping_price'] = 0;


                $arr['expected-delivery-date'] = 0;

                array_push($alldata, $arr);
            }
            $totalPrice = 0;
            $tax = 0;
            $shipPrice = 0;
            $discountPrice = 0;
            $totalProducts = count($storeData);
            foreach ($alldata as $sd) {
                if (isset($sd['product_variation_price'])) {
                    $price = $sd['product_variation_price'];
                    if($sd['on_sale'] == 1){
                        $salePrice = $sd['product_variation_sale_price'];
                        $discountPrice += (float) $sd['product_quantity'] * (float) $salePrice;
                    }

                    $totalPrice += (float) $sd['product_quantity'] * (float) $price;
                    
                } else {
                    if($sd['on_sale'] == 1){
                        $salePrice = $sd['sale_price'];
                        $discountPrice += (float) $sd['product_quantity'] * (float) $salePrice;
                    }
                   $price = $sd['price'];
                   $totalPrice += (float) $sd['product_quantity'] * (float) $price;
                }
                
                if (!empty($sd['shipping_price'])) {
                    $shipPrice += $sd['shipping_price'];
                }
                if (!empty($sd['product_tax'])) {
                    $tax += $sd['product_tax'];
                }
            }

            $arr2['totalProduct_amount'] = number_format($totalPrice, 2);
            $arr2['totalshiphing_amount'] = $shipPrice;
            $arr2['totalstoreproducts'] = $totalProducts;
            $arr2['discount_amount'] = number_format($discountPrice, 2);
            $arr2['product_tax'] = $tax;
            return $arr2;
            // return $alldata;
        } else {
            $totalPrice = 0;
            $tax = 0;
            $shipPrice = 0;
            $salePrice = 0;
            $totalProducts = count($cartDetail);
            $arr2['totalProduct_amount'] = number_format($totalPrice, 2);
            $arr2['totalshiphing_amount'] = $shipPrice;
            $arr2['totalstoreproducts'] = $totalProducts;
            $arr2['discount_amount'] = number_format($salePrice, 2);
            $arr2['product_tax'] = $tax;
            $arr2['redirect_url'] =  route('cart');
            return $arr2;
        }
    }

    public function cartUserDetail()
    {
        if (Session::has('userData')) {
            $userId = Session::get('userData')['user_id'];
            $cartDetail = DB::table('products_cart')
                ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
                ->where('products_cart.user_id', $userId)
                ->where('products.productType', 'product')
                ->groupby('products_cart.product_id')
                ->get();
            return objToArray($cartDetail);
        } else if (!empty($_COOKIE['cartCookie'])) {
            $randomKey = $_COOKIE['cartCookie'];
            $cartDetail = DB::table('products_cart')
                ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
                ->where('products_cart.random_key', $randomKey)
                ->where('products.productType', 'product')
                ->groupby('products_cart.product_id')
                ->get();
            return objToArray($cartDetail);
        }
    }

    public function objToArray($arr)
    {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    public function getUserCartProduct($productId)
    {
        $userId = Session::get('userData')['user_id'];
        if (isset($userId)) {
            $cartProductData = $this->_cart->where('product_id', '=', $productId)->select('variation_id')->where('user_id', '=', $userId)->first();
            if (!empty($cartProductData)) {
                $a = $cartProductData->toArray();
                $productVariation = ProductVariation::where('variation_id', '=', $cartProductData['variation_id'])->first()->toArray();
                return $productVariation;
            } else {
                return false;
            }
        } else if (!empty($_COOKIE['cartCookie'])) {
            $cartProductData = $this->_cart->where('product_id', '=', $productId)->select('variation_id')->where('random_key', '=', $_COOKIE['cartCookie'])->first();
            if (!empty($cartProductData)) {
                $cartProductData->toArray();
                return $productVariation = ProductVariation::where('variation_id', '=', $cartProductData['variation_id'])->first()->toArray();
            } else {
                return false;
            }
        }
    }

    public function checkProductIncart($productId)
    {
        $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
        if (!empty($userId)) {
            $query = $this->_cart->where('product_id', $productId)->where('user_id', $userId)->count();
            return $query;
        }
    }
}
