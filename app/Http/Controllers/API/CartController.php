<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\{Product, ProductVariation, User,Userrole};
use App\Repositories\Cart\Cartaccess;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MailgunController;

class CartController extends Controller
{
    private $_cartobj;
    private $_request;
    // private $_productobj;
    private $_user;
    private $_userRole;
    private $mailgun;

    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->_cartobj = new Cartaccess();
        // $this->_productobj = new Product();
        $this->_user = new User();
        $this->_userRole = new Userrole();
        $this->mailgun = new MailgunController();
    }

    public function index()
    {
        try {
         
            $postedData = $this->_request->input();
            $results = $this->_cartobj->cartDataNewApi($postedData);
           
          
            if ($results) {
                $getCartArray = [];
                foreach ($results as $key => $value) {
                   
                    $getcartdata = (array) $value;

                    
                    $getcartdata['feature_image'] = getAttachmentUrlById((int)$value['feature_image'], 'small');
                    $getcartdata['product_variation_image'] = getAttachmentUrlById((int)$value['product_variation_image'], 'small');
                    $state = getStatebyId($value['store_state']);
                    $store_shipping = getShippingOptionById($value['shipping_option_id'] ? $value['shipping_option_id'] : 221);
                    $shipping_info = [
                        'shipping_id' => $store_shipping['id'],
                        'shipping_cost' => $store_shipping['shipping_cost'] ?? 0,
                        'shipping_service' => !empty($store_shipping['shipping_service']) ? $store_shipping['shipping_service'] : 'no service selected',
                        'profile_name' => $store_shipping['profile_name'],
                        'shipping_method' => ($store_shipping['id'] == 221) ? 'free' : 'flat'
                    ];
                    $store_country = getCountryById($value['store_country']);
                    $store_info = getStoreInfoByIdCartApi($value['store_id']);
                    // echo 'Data: <pre>' .print_r($store_info,true). '</pre>'; die();
                    $getcartdata['store_state'] = $state;
                    $getcartdata['shipping_option_id'] = $store_shipping;
                    $getcartdata['shiping_data'] = $shipping_info;
                    $getcartdata['store_country'] = $store_country;

                    $getcartdata['store_info'] = $store_info;
                    $getcartdata['store_info']['store_logo'] = getAttachmentUrlById((int)$getcartdata['store_info']['store_logo'], 'small');
                    $getcartdata['cart_id'] = $value['cart_id'];
                    

                    $getCartArray[] = $getcartdata;
                }
                // echo 'Data: <pre>' .print_r($getCartArray,true). '</pre>'; 
                

                if ($getCartArray !== null) {
                   

                    if (count($getCartArray) > 0) {
                        return response()->json([
                            'status' => true,
                            'message' => "Cart data",
                            'cartdata' => $getCartArray,
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => "You don't have any product in the cart",
                            'cartdata' => [],
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "You don't have any product in the cart",
                        'cartdata' => [],
                        ], 200);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You don't have any product in the cart",
                    'cartdata' => [],
                    ], 200);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
    public function checkQuantity(){
        $productData = $this->_request->input();
        $response = [];
        foreach($productData['products'] as $singleProduct){
            // dd($singleProduct);
            $productResp=[];
            $product = Product::where('product_id', $singleProduct['product_id'])->first();
            if($product){
                $productResp = [ 
                    'product_id' => $product->product_id,
                    'name' => $product->title,
                    'stock' => $product->current_stock,
                    'max_quantity' => $product->maxquantitylimit
                ];
            }
            if (isset($singleProduct['variation_id']) && $singleProduct['variation_id'] != "") {
                $variation = ProductVariation::where('variation_id',$singleProduct['variation_id'])->first();
                if($variation){
                    $productResp['variation'] = [
                        'variation_id'=>$variation->variation_id,
                        'stock'=>$variation->current_stock,
                        'max_quantity' => $variation->maxquantitylimit
                    ];
                $response[] = $productResp;
                return response()->json([
                    'status' => true,
                    'data' => $response
                ]);
                }else{
                    return response()->json([
                        'status' => true,
                        'data' => [],
                        'message' => 'variation not found'
                    ]);
                }
                
            }else{
                if(!empty($productResp)){
                    $response[] = $productResp;
                    return response()->json([
                        'status' => true,
                        'data' => $response
                    ]);
                }else{
                    return response()->json([
                        'status' => true,
                        'data' => [],
                        'message' => 'product not found'
                    ]);
                }
            }
            
        }
        
    }
    public function addToCart()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            $res = $this->_cartobj->saveToCartApi($postedData);
            if($res['status']){
                $userDetail = ['guest' => $postedData['guest'],'id'=>$postedData['id']];
                $orderData = $this->_cartobj->usercartDetailApi($userDetail);
                $cartCounter = isset($orderData['total_quantity']) ? $orderData['total_quantity'] : 0;
                if ($cartCounter > 0) {
                    return response()->json([
                        'status' => true,
                        'message' => ' Add to cart Successfully',
                        'cartCounter' => $cartCounter,
                        'cart_id' => $res['cart_id'],
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Product not added',
                        'cartCounter' => $cartCounter,
                    ]);
                }
            }else {
                return response()->json([
                    'status' => $res['status'],
                    'message' => $res['message'],
                ], 409);
            }
        }
    }

    public function cartProductIncreaseQuantity()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'product_id' => 'required',
                'store_id' => 'required',
                'product_quantity_plus' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            $orderData = '';
            $res = $this->_cartobj->updateCartQuantity($postedData);
            if ($res['status']) {
                return response()->json($res);
            }else{
                return response()->json($res);
            }
            return response()->json([
                'status' => $res['status'],
                'message' => $res['message'],
            ], 409);
        }
    }

    public function cartProductDecreaseQuantity()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'product_id' => 'required',
                'store_id' => 'required',
                'product_quantity_minus' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            $orderData = '';
            $res = $this->_cartobj->updateCartQuantity($postedData);
            if ($res['status']) {
                return response()->json($res);
            }else{
                return response()->json($res);
            }
        }
    }

    public function delCartProducts()
    {
        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'cart_id' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        try {
            $PostedData = $this->_request->input();
            $result = $this->_cartobj->delProductApi($PostedData);
            if ($result) {
                return response()->json([
                    'status' => true,
                    'message' => 'Product removed from the cart',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Product does not exist in the cart',
                ]);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public function checkedToogleProductsToCart()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'cart_id' => 'required',
                'is_check' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        try {
            $PostedData = $this->_request->input();
            $result = $this->_cartobj->checkedToogleProductCart($PostedData);
            if ($result) {
                return response()->json([
                    'status' => true,
                    'message' => 'Product selected from the cart',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Product does not exist in the cart',
                ]);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
}
