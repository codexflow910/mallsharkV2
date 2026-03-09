<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Cart\Cartaccess;
use Redirect;
use Exception;

class CartController extends Controller {

    private $_cartobj;
    private $_request;

    public function __construct(Request $request) {
        $this->_request = $request;
        $this->_cartobj = new Cartaccess();
    }

    public function index() {
        $result = $this->_cartobj->cartDataNew();
        $resultCrypto = $this->_cartobj->cartDataNewCrypto();
        // echo '$resultCrypto: <pre>' .print_r($resultCrypto,true). '</pre>'; die();
        // if($this->isMobileDevice()){
        // return view('cart/mobile/cart-main', ['cartdata' => $result, 'cartdataCrypto' => $resultCrypto]);
        // }else{
             return view('cart/cart-main', ['cartdata' => $result, 'cartdataCrypto' => $resultCrypto]);
        // }
    }

    public function addToCart() {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $orderData = '';

            // Check if the product already exists in the cart
            // $productExists = $this->_cartobj->checkProductExists($postedData);

            // if ($productExists) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'This product is already in your cart.',
            //     ]);
            // }
            $res = $this->_cartobj->saveToCart($postedData);
           
            $orderData = $this->_cartobj->usercartDetail($res);
            $cartCounter = isset($orderData['total_quantity']) ? $orderData['total_quantity']: 0;
            $view = View('cart.carthtml', ['ajaxCartdata' => $orderData])->render();
            // dd($res);
            if(isset($res['max_quantity']) && $res['max_quantity']){
                return response()->json([
                    'cartCounter' => $cartCounter,
                    'view' => $view, 
                    'status' => false,
                    'message' => 'max quantity reach',
                ]);
            }
            if($cartCounter > 0){
                return response()->json([
                    'cartCounter' => $cartCounter,
                    'view' => $view, 
                    'status' => true,
                    'message' => ' Add to cart Successfully',
                ]);
            }else{
                return response()->json([
                    'cartCounter' => $cartCounter,
                    'view' => $view, 
                    'status' => false,
                    'message' => 'Your Product not added in your cart. Please verify your product',
                ]);
            }

        }
        
    }

    public function cartCounter() {
        $count = 0;
        $cartData = $this->_cartobj->cartUserDetail();
        if (!empty($cartData)) {
            $count = count($cartData);
        }
        echo $count;
        exit;
    }
    public function delCheckoutProducts() {
        if ($this->_request->ajax()) {
            
            $PostedData = $this->_request->input();
            // echo '$PostedData: <pre>' .print_r($PostedData,true). '</pre>'; die();
            return $this->_cartobj->delCheckoutProduct($PostedData);
        }
    }
    public function delCartProducts() {
        if ($this->_request->ajax()) {
            
            $PostedData = $this->_request->input();
            // echo '$PostedData: <pre>' .print_r($PostedData,true). '</pre>'; die();
           $result = $this->_cartobj->delProduct($PostedData);
           if($result){
            return $result;
           }else{
            return false;
           }

        }
    }

    public function delCartProductsCrypto() {
        if ($this->_request->ajax()) {
            $PostedData = $this->_request->input();
            // echo '$PostedData: <pre>' .print_r($PostedData,true). '</pre>'; die();
            $this->_cartobj->delProductCrypto($PostedData);
        }
    }
    public function delCartBasket() {

        if ($this->_request->ajax()) {
            $PostedData = $this->_request->input();
            $this->_cartobj->delProduct($PostedData);
            $cartData = cartDetail();
            $cartCounter = isset($cartData['total_quantity']) ? $cartData['total_quantity']: 0;
            $view = View('cart.carthtml', ['cartdata' => $cartData])->render();
            return response()->json([
                'view' => $view,
                'cartCounter' => $cartCounter,
                'status' => true,
                'message' => 'Product removed from Cart'
            ]);
            
        }
    }
    public function delCartBasketAjax() {

        if ($this->_request->ajax()) {
            $PostedData = $this->_request->input();
            // dd($PostedData['cart_id']);
            $this->_cartobj->delProductAjax($PostedData);
            $cartData = cartDetail();
            $cartCounter = isset($cartData['total_quantity']) ? $cartData['total_quantity']: 0;
            $view = View('cart.carthtml', ['cartdata' => $cartData])->render();
            return response()->json([
                'view' => $view,
                'cart_id' => $PostedData['cart_id'],
                'cartCounter' => $cartCounter,
                'status' => true,
                'message' => 'Product deleted Successfully'
            ]);
        }
    }

    public function delCartBasketCrypto() {

        if ($this->_request->ajax()) {
            $PostedData = $this->_request->input();
            $this->_cartobj->delProductCrypto($PostedData);
            $resultCrypto = cartDetailCrypto();
            return View('cart.carthtml', ['cartdataCrypto' => $resultCrypto])->render();
        }
    }

    public function updateCart() {
        if ($this->_request->ajax()) {
            $PostedData = $this->_request->input();
            $result = $this->_cartobj->updateCart($PostedData);
          //  print_r($result); exit;
            return $result;
        }
    }

    public function addNote() {
        if ($this->_request->ajax()) {
            $PostedData = $this->_request->input();
            $this->_cartobj->addNote($PostedData);
        }
    }

    // public function calculateCart() {
    //     if ($this->_request->ajax()) {
    //         $PostedData = $this->_request->input();
    //         $cartData = $this->_cartobj->calculateInfo($PostedData);
    //         dd($cartData);
    //         return View('cart.cartcalculate', ['cartdata' => $cartData])->render();
    //     }
    // }
    public function calculateCart() {
        if ($this->_request->ajax()) {
            $PostedData = $this->_request->input();
            $cartData = $this->_cartobj->calculateInfo($PostedData);
            return $cartData;
            // dd($cartData);
            // return View('cart.cartcalculate', ['cartdata' => $cartData])->render();
        }
    }

    public function getCartdetail() {
        if ($this->_request->ajax()) {
            $cartData = cartDetail();
          
            $cartCounter = isset($cartData['total_quantity']) ? $cartData['total_quantity']: 0;
            $view = View('cart.carthtml', ['cartdata' => $cartData])->render();
            return response()->json([
                'view' => $view,
                'cartCounter' => $cartCounter
            ]);
        }
    }
}
