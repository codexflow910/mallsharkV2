<?php

namespace App\Http\Controllers\API;

use File;
use Session;
use Redirect;
use Carbon\Carbon;
use App\Models\{Attachments, Review, StoreOrder, ProductRefund,User};
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\User\Useraccess;
use App\Repositories\Buyer\Buyeraccess;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Product\Productaccess;
use App\Repositories\Location\LocationAccess;
use App\Repositories\Orders\StoreOrdersAccess;
use App\Http\Controllers\MailgunController;
use Exception;
use App\Services\FCMService;
use App\Services\SellerFcmService;
USE App\Constants;
use App\Models\DeviceToken;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Http;


class BuyerController extends Controller
{
    private $_buyerobj;
    private $_locationobj;
    private $_request;
    private $productObj;
    private $access_obj;
    private $_userobj;
    private $review;
    private $mailgun;

    public function __construct(Request $request)
    {

        $this->_request = $request;
        $this->_buyerobj = new Buyeraccess();
        $this->_locationobj = new LocationAccess();
        $this->productObj = new Productaccess();
        $this->access_obj = new StoreOrdersAccess();
        $this->_userobj = new Useraccess();
        $this->review = new Review();
        $this->mailgun = new MailgunController();
    }

    public function index()
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'return_source' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $postedData = $this->_request->input();
        if (isset($postedData['return_source']) && $postedData['return_source'] == 'true') {
            $userData = $this->_buyerobj->getProfileData($postedData);
            $shippingInfo = $this->_buyerobj->getcurrentShippingAddress($postedData);
            $favorites = $this->_buyerobj->getCountFavorites($postedData);
            $categories = DB::table('categories')->select('categoryTitle', 'id')->where('parentCategoryID', '=', 0)->get();
        } else {
            return response()->json([
                'status' => false,
                'message' => "The parameter is not declared correctly. Please correct the declaration of parameter."
            ], 500);
        }

        try {

            return response()->json([
                'status' => true,
                'message' => 'Buyer Information',
                'userData' => $userData,
                'shippingInfo' => $shippingInfo,
                'favorites' => $favorites,
                'categories' => $categories,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function userAccount()
    {
        $postedData = $this->_request->input();
        if (!isset($postedData['return_source']) || !$postedData['return_source'] ||  $postedData['return_source'] != 'true') {
            return response()->json([
                'status' => false,
                'message' => "return_source must be true as it is a required parameter"
            ], 500);
        }

        try {
            $userData = $this->_buyerobj->getProfileData($postedData);
            if ($userData) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Information ',
                    'data' => $userData,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "User Not Created"
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function updateUserProfile()
    {
        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        if ($this->_request->isMethod('post')) {
           
            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'full_name' => 'required|regex:/^[\pL\s]+$/u',
                    'email' => 'required|email:rfc,dns'
                ]
            );
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }
            $uploaded_image = null;

        //    dd($this->_request->file('image'));
        // return response()->json($this->_request->hasFile('image'));
            if ($this->_request->hasFile('image')) {
                $image = $this->_request->file('image');
              
                $uploaded_image = $this->uploadUserImg($image);
               
            }
            $postedData = $this->_request->input();
            // if (!isset($postedData['return_source']) || !$postedData['return_source'] ||  $postedData['return_source'] != 'true') {
            //     return response()->json([
            //         'status' => false,
            //         'message' => "return_source must be true as it is a required parameter"
            //     ], 500);
            // } else {
            $result = $this->_buyerobj->updateUserProfileApi($postedData, $uploaded_image);
            try {
                if ($result == true) {
                    
                    return response()->json([
                        'status' => true,
                        'message' => 'User Updated Successfully',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "User Not Update"
                    ], 500);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }
    }

    public function buyerPasswordChange()
    {
        if ($this->_request->isMethod('post')) {


            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'current_password' => 'required',
                    'new_password' => 'required',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }
            $postedData = $this->_request->input();
            // echo '$postedData: <pre>' .print_r($postedData,true). '</pre>'; die();
            $result = $this->_buyerobj->UpdatePasswordApi($postedData);
            if ($result == 1) {
                return response()->json([
                    'Status' => 200,
                    'Success' => true,
                    'Message' => 'User password updated successfully'
                ]);
            } else if ($result == 0) {

                return response()->json([
                    'Status' => 400,
                    'Success' => false,
                    'Message' => 'Bad Request'
                ]);
            } else if ($result == 2) {

                return response()->json([
                    'Status' => 500,
                    'Success' => false,
                    'Message' => 'Current password incorrect'
                ]);
            }
        }
        $postedData = $this->_request->input();
        if (!isset($postedData['return_source']) || !$postedData['return_source'] ||  $postedData['return_source'] != 'true') {
            return response()->json([
                'status' => false,
                'message' => "return_source must be true as it is a required parameter"
            ], 500);
        }

        $userData = $this->_buyerobj->getProfileData($postedData);
        try {
            if ($userData) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Information ',
                    'data' => $userData,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "User Not Created"
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getStatesofCountry()
    {
        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validateUser = Validator::make(
            $this->_request->all(),
            [

                'country_id' => 'required|numeric|exists:countries,id',

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
            $countryId = $this->_request->input('country_id');
            $states = getStatesOfCountry($countryId);
            // print_rr($states); exit;
            $stateHtml = $this->builtHtmlforStates($states);
            if ($stateHtml) {
                return response()->json([
                    'status' => true,
                    'message' => 'States found:',
                    'data' => $stateHtml,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "This country id not exist"
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function builtHtmlforStates($data)
    {
        $html = "";
        if (count($data) > 0) {
            foreach ($data as $d) {
                $html .= '<option value="' . $d['state_id'] . '">' . strtolower($d['state_name']) . '</option>';
            }
        }
        return $html;
    }

    public function validateEmail()
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'email' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 422);
        }

        $email = $this->_request->input('email');
        $result = $this->_buyerobj->checkEmailApi($email);
        try {
            if ($result > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Verified Email',

                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "This email is not verified"
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getShippingAddress()
    {
        $buyerShippingData = $this->_buyerobj->getShippingAddressApi();
        $states = '';
        if (!empty($buyerShippingData)) {

            $states = getStatesOfCountry($buyerShippingData['country']);
        }
        try {
            return response()->json([
                'status' => true,
                'message' => 'Shiping Address',
                'buyerShippingData' => $buyerShippingData,
                'states' => $states,

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateShippingAddress()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'city' => 'required',
                'user_country' => 'required',
                'user_state' => 'required|numeric|exists:states,state_id',
                'zip_code' => 'required',
                'street' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 422);
        }

        $postedData = $this->_request->input();
        
        try {
            $shipping_address = $this->_buyerobj->updateShippingAddressApi($postedData);
            if ($shipping_address) {
                return response()->json([
                    'status' => 200,
                    'Success' => true,
                    'message' => 'Shipping address information has been updated successfully',


                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'Success' => false,
                    'message' => 'Shipping address information not updated',


                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function reviews()
    {
        try {
            $all = $this->_buyerobj->getallOrdersApi();
            return response()->json([
                'status' => true,
                'message' => 'Reviews',
                'data'    => $all
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function userKingdom()
    {
        $userId = Auth::user()->user_id;
        $userType = 'current';

        $userData = getUserByIdApi($userId);
        try {
            return response()->json([
                'status' => true,
                'message' => 'User Data',
                'userType' => $userType,
                'User Data'    => $userData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function changeAlertsStatus()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'alert_status' => ['required',  'in:true,false'],
                'type' => ['required',  'in:sms_alerts,email_alerts'],
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 422);
        }

        $postedData = $this->_request->input();
        if (isset($postedData['alert_status']) && $postedData['alert_status'] == 'true' || $postedData['alert_status'] == 'false' && $postedData['type'] == 'sms_alerts' || $postedData['type'] == 'email_alerts') {
            $result = $this->_buyerobj->changeAlertsApi($postedData);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The parameter is not declared correctly. Please correct the declaration of parameter."
            ], 500);
        }
        try {
            return response()->json([
                'status' => true,
                'message' => $result,

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateUserIntrest()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'categories' => ['required', 'array'],
                'categories.*' => ['exists:categories,id'],
                'return_source' => ['required',  'in:true,false'],
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 422);
        }
        $postedData = $this->_request->input();
        try {
            $result = $this->_buyerobj->updateIntrest($postedData);
            if ($result) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Add Successfully Interested Categories',

                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Not Interested ',
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function userProfileById()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'userid' => 'required|numeric|exists:users,user_id',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $posteddata = $this->_request->input();

        $userId = Auth::user()->user_id;

        if ($posteddata['userid'] && $posteddata['userid'] != $userId) {
            $userId = $posteddata['userid'];
            $userType = 'other';
        } else {
            $userId = $userId;
            $userType = 'current';
        }
        try {
            $userData = $this->_userobj->getUserDataByIdJson($userId);

            if (!empty($userData)) {
                return response()->json([
                    'status' => true,
                    'message' => 'User found',
                    'userType'  => $userType,
                    'userData'  => $userData
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function buyerOrders(Request $request)
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'status' => 'in:pending,completed,shipped,refunded,dispute,cancelled',
                'order_id' => 'exists:store_orders,order_id',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $order_status = $request->input('status');
        $order_id =  $request->input('order_id');
        $userId = Auth::user()->user_id;
        $currentDate = Carbon::today();
        // $getTodayNewStoreOrders = StoreOrder::where('buyer_id', $userId)->whereDate('created_at', $currentDate)->count();
        // $getTodayPendingStoreOrders = StoreOrder::where('buyer_id', $userId)->where('order_status', 'pending')->whereDate('created_at', $currentDate)->count();
        // $getTodayRefundReqStoreOrders = StoreOrder::where('buyer_id', $userId)->where('order_status', 'refunded')->whereDate('created_at', $currentDate)->count();
        // $getTodayDisputeStoreOrders = StoreOrder::where('buyer_id', $userId)->where('order_status', 'dispute')->whereDate('created_at', $currentDate)->count();
        if ($userId) {
            $orders_all = $this->access_obj->get_buyer_orders_with_items_all_new_Api($userId, $order_id, $order_status);
            // echo 'sss: <pre>' .print_r($orders_all,true). '</pre>'; die();
        } else {

            return response()->json([
                'status' => false,
                'message' => "Store not exist",
            ], 401);
        }
        if (Auth::user()->user_id) {
            try {
                if (!empty($orders_all)) {
                    // echo 'Data: <pre>' .print_r($orders_all,true). '</pre>'; die();
                    $selectedOrders = [];
                    foreach ($orders_all as $orders) {
                        $orders['order_status'] = ucfirst($orders['order_status']);
                        $userImageId = $orders['user']['user_avatar'];
                        if(isset($userImageId) && !is_null($userImageId)){
                        $userImage = getAttachmentUrlByIdAvatarApi($userImageId);
                        $orders['user']['user_avatar'] = $userImage;
                        }
                        
                        if ($orders['store_tax'] == null) {
                            $orders['store_tax'] = 0;
                        }
                        $order_items_list = [];
                       
                        foreach ($orders['order_items_list'] as $products) {
                            $modifiedProducts = [];
                            if($products['variation_id'] == 0){
                                foreach ($products['products'] as $product) {
                                    $product['feature_image'] = getAttachmentUrlById(intval($product['feature_image']), 'media');
                                    $modifiedProducts[] = $product;
                                }
                                
                            }else if($products['variation_id'] != 0){
                                $variation_image = DB::table('product_variation')->where('variation_id',$products['variation_id'])->first();
                                if(isset($variation_image) && !is_null($variation_image)){
                                    foreach ($products['products'] as $product) {
                                        $product['feature_image'] = getAttachmentUrlById(intval($variation_image->product_variation_image), 'media');
                                        $modifiedProducts[] = $product;
                                    }
                                }else{
                                    foreach ($products['products'] as $product) {
                                        $product['feature_image'] = getAttachmentUrlById(intval($product['feature_image']), 'media');
                                        $modifiedProducts[] = $product;
                                    }
                                }
                                    
                            }
                            if($products['review']){
                                foreach($products['review'] as $review){
                                    if($review['user_id'] == $userId && $review['product_id'] == $products['item_id']){
                                        $products['isReview'] = true;
                                    }else{
                                        $products['isReview'] = false;
                                    }
                                }
                                
                            }
                            $products['products'] = $modifiedProducts;
                            $order_items_list[] = $products;
                        }
                        $orders['order_items_list'] = $order_items_list;
                        $selectedOrders[] = $orders;
                       
                    }
                 
                    if (!empty($selectedOrders)) {
                        return response()->json([
                            'status' => true,
                            'message' => "You get all orders",
                            'orderProducts' => $selectedOrders,
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => "Status is not changed please contact the super admin",
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "No Order Found",
                    ], 500);
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
        }
    }
    public function uploadUserImg($image)
    {
        try {
          
            $myimage = $image;
            $destinationPath = public_path('uploads/avatar/');
            $originalFilename = $this->productObj->generateRandomString(15) . '_' . $myimage->getClientOriginalName();
         
            $pathInfo = pathinfo($originalFilename);
           
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];
            
            // dd($filename);
            if ($myimage) {
                $image = new Attachments();
                $image->fileName = $filename;
                $image->fileExtension = $extension;
                $image->save();
                $attachmentId = $image->attachment_id;
                $originalFilename = $filename . '_' . $attachmentId . '.' . $extension;
                $myimage->move($destinationPath, $originalFilename);
            }
            $arr['id'] = $attachmentId;
            $arr['image'] = getAttachmentUrlByIdAvatarApi($attachmentId, 'avatar', '');

            return $arr;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function userOrdersNew()
    {
        try {
            $allOrders = $this->_buyerobj->getallOrdersNewAPI();
            if ($allOrders) {
                return response()->json([
                    'status' => true,
                    'message' => 'You successfully get buyer orders',
                    'orders'   => $allOrders,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You have not any Order"
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'messagess' => $th->getMessage()
            ], 500);
        }
    }
    public function add_review()
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'rating_count' => 'required',
                'comment' => 'required',
                'product_id' => 'required'
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $response = array();
        $product_id = $this->_request->input('product_id');
        $author_id = auth()->user()->user_id;
        $rating_count = $this->_request->input('rating_count');
        $comment = $this->_request->input('comment');
        try {
            $this->review->product_id = $product_id;
            $this->review->rating = $rating_count;
            $this->review->comment = $comment;
            $this->review->user_id = $author_id;
            $this->review->status = '1';
            $this->review->save();
            if ($this->review) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'messagess' => 'Review added'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'messagess' => $e->getMessage()
            ]);
        }
    }
    public function cancel_order_api(Request $request)
    {
        
        try {
            $validator = Validator::make($request->all(), [
                'refund_date' => 'required|date',
                'message' => 'required|string',
                'order_id' => 'required|numeric',
                'order_status' => 'required|string',
                'is_refund' => 'required|string',
                'cancel_product_id.*.product_id' => 'required|numeric',
                'cancel_product_id.*.price' => 'required|numeric',
                'cancel_product_id.*.shipment' => 'required|numeric',
                'cancel_product_id.*.quantity' => 'required|numeric',
                'cancel_product_id.*.store_id' => 'required|numeric',
                'cancel_product_id.*.buyer_id' => 'required|numeric',
                'cancel_product_id.*.order_id' => 'required|numeric',
                'cancel_product_id.*.cancelled' => 'required|string',
                'cancel_product_id.*.payment_method' => 'required|string',
                'cancel_product_id.*.product_title' => 'required|string',
                'cancel_product_id.*.product_slug' => 'required|string',
                'cancel_product_id.*.refund_date' => 'required|date',
                'cancel_product_id.*.product_image' => 'required',
                'refund_reason_type' => 'required|string',
                'buyer_id' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                
                // Handle validation failure, e.g., return response with errors
                return response()->json(['errors' => $validator->errors()], 422);
            }
            if ($request) {
                
                $StoreOrder = StoreOrder::find($request['order_id']);
                // echo 'Data: <pre>' .print_r($StoreOrder,true). '</pre>'; die();
                // dd($StoreOrder);
                $storeData = $StoreOrder->store;
                // dd($storeData);
                // echo 'Data: <pre>' .print_r($storeData,true). '</pre>'; die();
                $StoreOrder->order_status = $request['order_status'] ? $request['order_status'] : null;
                $StoreOrder->refund_date = $request['refund_date'] ? $request['refund_date'] : null;
                $StoreOrder->is_refund = $request['is_refund'] ? $request['is_refund'] : null;
                $StoreOrder->save();
                
            }
            if (is_array($request->cancel_product_id)) {
                
                foreach ($request->cancel_product_id as $cancel_product) {
                    try {
                        $productCancel = new ProductRefund();
                        $productCancel->product_id = $cancel_product['product_id'] ? $cancel_product['product_id'] : "";
                        $productCancel->order_id = $cancel_product['order_id'] ? $cancel_product['order_id'] : "";
                        $productCancel->buyer_id = $cancel_product['buyer_id'] ? $cancel_product['buyer_id'] : "";
                        $productCancel->store_id = $cancel_product['store_id'] ? $cancel_product['store_id'] : "";
                        $productCancel->quantity = $cancel_product['quantity'] ? $cancel_product['quantity'] : "";
                        $productCancel->price = $cancel_product['price'] ? $cancel_product['price'] : "";
                        $productCancel->shipping_amount = $cancel_product['shipment'] ? $cancel_product['shipment'] : "";
                        $productCancel->reason_type = $request['refund_reason_type'] ? $request['refund_reason_type'] : "";
                        $productCancel->reason = $request['message'] ? $request['message'] : "";
                        $productCancel->is_refund = $request['cancelled'] ? $request['cancelled'] : "";
                        $productCancel->refund_date = $request['refund_date'] ? $request['refund_date'] : "";
                        $productCancel->payment_method = $request['payment_method'] ? $request['payment_method'] : "";
                        $productCancel->save();
                        
                    } catch (Exception $e) {
                       
                        dd($e->getMessage());
                        // echo 'Caught exception: ', $e->getMessage(), "\n";
                    }
                }
            } else {
               
                try {
                    $productCancel = new ProductRefund();
                    $productCancel->product_id = $request->product_id[0] ? $request->product_id[0] : null;
                    $productCancel->order_id = $request['order_id'] ? $request['order_id'] : "";
                    $productCancel->buyer_id = $request['buyer_id'] ? $request['buyer_id'] : "";
                    $productCancel->store_id = $request['store_id'] ? $request['store_id'] : "";
                    $productCancel->quantity = $request['quantity'] ? $request['quantity'] : "";
                    $productCancel->price = $request['price'] ? $request['price'] : "";
                    $productCancel->reason_type = $request['refund_reason_type'] ? $request['refund_reason_type'] : "";
                    $productCancel->reason = $request['reason'] ? $request['reason'] : "";
                    $productCancel->is_refund = $request['is_refund'] ? $request['is_refund'] : "";
                    $productCancel->refund_date = $request['refund_date'] ? $request['refund_date'] : "";
                    $productCancel->payment_method = $request['payment_method'] ? $request['payment_method'] : "";
                    $productCancel->save();
                } catch (Exception $e) {
                    dd($e->getMessage());
                    // echo 'Caught exception: ', $e->getMessage(), "\n";
                }
            }
            $usershipping = getShippingAddressbyUserId($request['buyer_id']);
            $userData = getUserById($request['buyer_id']);
            //  echo 'Data: <pre>' .print_r($userData,true). '</pre>'; die();
            if (!empty($userData)) {
                $email = $userData->email;
            } else {
                $email = "noman@mindblazetech.com";
            }
            // $user_description = Constants::REQUEST_CANCEL_ORDER_BUYER_DESCRIPTION;
            $subjectuser = Constants::REQUEST_CANCEL_ORDER_BUYER_SUBJECT.$request['order_id'];
            $subjectStore = $userData->name.Constants::REQUEST_CANCEL_ORDER_SELLER_SUBJECT.$request['order_id'];
            // $store_description = $userData->name.Constants::REQUEST_CANCEL_ORDER_SELLER_DESCRIPTION;
            // email send to vendor
            $html = view('mail.canceled-order-from-client', ['userData' => $userData, 'orderData' => $request->input(), "shippingAddress" => $usershipping, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);
            $subject = 'Order NO ' . $request['order_id'] . ' Cancellation';
            $toEmail = $storeData->user->email;
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to' => $toEmail,
                'subject' => $subject,
                'html' => "$html",
            ];
            $this->mailgun->SendMailgunMail($params);
            //
            
            
            if(isset($storeData->user->user_id) && !is_null($storeData->user->user_id)){
                $userData = User::with('device_token')->findOrFail($storeData->user->user_id);
                $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','seller')->get();
                if ($device_tokens) {
                    $tokens = [];
                    if(isset($device_tokens)&& count($device_tokens) > 0){
                    foreach ($device_tokens as $device_token_obj) {
                        // dd($device_token_obj->fcm_token);
                    
                    
                    $subject = $subjectStore;
                    // foreach($tokens as $token){
                        $notification = SellerFcmService::send(
                            $device_token_obj->fcm_token,
                            [
                                'title' => $subject,
                            ]
                        );
                    }
                }
                // return response()->json($userData);

                    addNotifications($storeData->user->user_id, $request['buyer_id'], $subjectStore, '', URL::to('/brand-order-details/'.$request['order_id']),'brand_order_details',$request['order_id'],'seller');

                }
            }
           
            // email send to buyer
            $html = view('mail.order-update-client-cancelled', ['order_id' => $request['order_id'], 'userData' => $userData, 'orderData' => $request->input(), "shippingAddress" => $usershipping, 'storeData' => $storeData, 'customerDetail' => $StoreOrder->user]);
            $subject = 'Order #' . $request['order_id'] . ' Cancellation Request';
            $toEmail_buyer = $email;
            $params = [
                'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
                'to' => $toEmail_buyer,
                'subject' => $subject,
                'html' => "$html",
            ];
            $this->mailgun->SendMailgunMail($params);
            // if(isset($userData->user_id) && !is_null($userData->user_id)){
            //     $userData = User::with('device_token')->findOrFail($request['buyer_id']);
            //     $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
            //     if ($device_tokens) {
            //         $tokens = [];
            //         // return response()->json($userData->user_id);
            //         if(isset($device_tokens)&& count($device_tokens) > 0){
            //         foreach ($device_tokens as $device_token_obj) {
            //             // dd($device_token_obj->fcm_token);
            //         $subject = $subjectuser;
            //         // foreach($tokens as $token){
            //             $notification = FCMService::send(
            //                 $device_token_obj->fcm_token,
            //                 [
            //                     'title' => $subject,
            //                 ]
            //             );
            //         }
            //     }
            //     // return response()->json( URL::to('new-buyer-orders-detail/'.$request['order_id']));
            //         addNotifications($userData->user_id,$storeData->user->user_id, $subjectuser, '', URL::to('new-buyer-orders-detail/'.$request['order_id']),'buyer_order_details',$request['order_id'],'buyer');
                    
            //     }
            // }

            return response()->json([
                'status' => true,
                'message' => 'You order has been canceled',
                'date' => $StoreOrder
            ]);
        } catch (\Throwable $th) {
            echo $th;
        }
    }
   
}
