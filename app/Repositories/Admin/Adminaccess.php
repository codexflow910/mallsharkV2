<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Admin;

use File;
use Image;
use Session;
use App\BaseAccess;
use App\Constants;
use App\Models\Cart;
use App\Models\User;
use Packages\Store\Models\V2\Store;
use Packages\Store\Models\V3\Store as StoreV3;
use Packages\Product\Models\V2\Product;
use App\Services\FCMService;
use App\Services\SellerFcmService;
use App\Models\Userrole;
use App\Models\Attachments;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\MailgunController;
use App\Http\Controllers\ElasticSearchController;
use App\Models\DeviceToken;
use App\Models\TikTokVideo;
use Illuminate\Support\Facades\DB;
use PHPUnit\TextUI\XmlConfiguration\Constant;
use Packages\Product\Config\ProductConstants;
use Packages\Store\Config\StoreConstants;
use Packages\Order\Models\V2\{StoreOrder,OrderItems};
use Packages\Admin\Contracts\V2\AdminHelpers;
/**
 * Description of Admin Access
 *
 * @author Asif
 */
class Adminaccess extends BaseAccess
{
    use AdminHelpers;

    private $_userroles;
    private $_store;
    private $_user;
    private $_product;
    private $_esearch;
    private $_tax;
    private $mandrill;
    private $mailgun;
    private $storeOrder;
    private $orderItems;
    private $_tiktok;
    //
    public function __construct()
    {
        $this->_userroles = new Userrole();
        $this->_user = new User();
        $this->_store = new Store();
        $this->_product = new Product();
        $this->_esearch = new ElasticSearchController();
        $this->mailgun = new MailgunController();
        $this->storeOrder = new StoreOrder();
        $this->orderItems = new OrderItems();
        $this->_tiktok = new TikTokVideo();
    }

    public function getListingData($data)
    {

        $query = DB::table('products')
            ->select('products.product_id', 'products.user_id', 'products.store_id', 'products.title', 'products.slug', 'products.price', 'products.status', 'categories.categoryTitle', 'products.created_at', 'users.name')
            ->join('product_category', 'product_category.product_id', '=', 'products.product_id')
            ->join('categories', 'categories.id', '=', 'product_category.category_id')
            ->join('users', 'users.user_id', '=', 'products.user_id')
            ->where('products.listing_type', '=', 'post')
            ->groupBy('products.product_id');
        if (isset($data['order']) && $data['order'] != '') {
            $order = $data['order'];
            $query->orderBy('products.product_id', $order);
        }
        if (isset($data['status']) && $data['status'] != '') {
            $status = $data['status'];
            $query->where('products.status', "$status");
        }
        $res = $query->paginate(10);
        if (!empty($res)) {
            return $res;
        } else {
            return false;
        }
    }
    public function ChangeStatusByid($data)
    {
        $userProductId = product::where('product_id', $data['product_id'])->first();
        $userProductData = product::select('title', 'slug')->where('product_id', $data['product_id'])->first();
        $p_name = $userProductData->title;
        $p_slug = $userProductData->slug;
        $currentuserId = Session::get('userData')['user_id'];
        $storeData = $userProductId->store;
        // $storeName =  !empty($storeData->store_name) ? $storeData->store_name :''; 
        $store_name =  !empty($storeData->user->name) ? $storeData->user->name : '';
        $userData = getUserByIdchat($userProductId->user_id);
        if ($data['product_id'] != '' && $data['status'] != '') {
            if ($data['status'] == 'Activate') {
                // if ($storeData->super_status == '1') {


                $update = product::where('product_id', $data['product_id'])
                    ->update(['status' => '1']);
                //email send
                $html = view('mail.activeteProduct', ['product' => $update]);
                $subject = 'Product Activated MallShark';
                $toEmail = $userData->email;
                $params = [
                    'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
                $subject = Constants::SUPER_ADMIN_ACTIVE_PRODUCT;
                //   $this->sendEmail($subject = 'Product Activated MallShark', $templateSlug = 'product-activated', $userData->email, $userData->name, $userProductId->title);
                $product = Product::find($data['product_id']);
                $product->searchable();
            }
            if ($data['status'] == 'Approve') {
                if ($storeData->super_status == '1') {

                    $product = product::where('product_id', $data['product_id'])->first();
                    if($product){
                        $product->status = '1';
                        $product->save();
                        $product->searchable();


                        //email send
                        $html = view('mail.approved-product-vendor-side', ['store_name' => $store_name, 'product' => $product]);
                        $subject = 'Yay! Your Product Has Been Approved on MallShark!';
                        $toEmail = $userData->email;
                        $params = [
                            'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                            'to'      => $toEmail,
                            'subject' => $subject,
                            'html'    => "$html",
                        ];
                        $this->mailgun->SendMailgunMail($params);
                        $subject = Constants::SUPER_ADMIN_APPROVE_PRODUCT;
                        //  $this->sendEmail($subject = 'Product Approve MallShark', $templateSlug = 'product-approved', $userData->email, $userData->name, $userProductId->title);
                    }
                }else{
                    return false;
                }
            }
            if ($data['status'] == 'Restore') {
                $update = product::where('product_id', $data['product_id'])
                    ->update(['status' => '3']);
                $product = product::where('product_id', $data['product_id'])->first();
                $html = view('mail.restored-product-vendor-side', ['store_name' => $store_name, 'product' => $product]);
                $subject = 'Yay! Your Product Has Been Restored on the MallShark Platform!';
                $toEmail = $userData->email;
                $params = [
                    'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
                $subject = Constants::SUPER_ADMIN_RESTORE_PRODUCT;
                //   $this->sendEmail($subject = 'Product Restored MallShark', $templateSlug = 'product-restored', $userData->email, $userData->name, $userProductId->title);

            }
            if ($data['status'] == 'Delete') {
                $product = product::where('product_id', $data['product_id'])->first();
                if($product){
                    $product->status = '2';
                    $product->save();
                    $product->unsearchable();
                    $html = view('mail.deleted-product-vendor-side', ['store_name' => $store_name, 'reason' => $data['reason'], 'product' => $product]);
                    $subject = 'Uh Oh - Product Removed from MallShark Platform';
                    $toEmail = $userData->email;
                    $params = [
                        'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $this->mailgun->SendMailgunMail($params);
                    // $this->sendEmail($subject = 'Product Deleted', $templateSlug = 'product-suspended', $userData->email, $userData->name, $userProductId->title, $data['reason']);
                    $subject = Constants::SUPER_ADMIN_DELETE_PRODUCT;
                    $product = Product::find($data['product_id']);
                    $product->unsearchable();
                } 
            }
            if ($data['status'] == 'Reject') {
                $update = product::where('product_id', $data['product_id'])
                    ->update(['status' => '4']);
                $product = product::where('product_id', $data['product_id'])->first();
                $html = view('mail.rejected-product-vendor-side', ['store_name' => $store_name, 'product' => $product]);
                $subject = 'Oh No! Your Product Has Been Rejected on MallShark';
                $toEmail = $userData->email;
                $params = [
                    'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
                // addNotifications($userData->id,  $currentuserId, $subject, $subject, URL::to('/store-products'));
                $subject = Constants::SUPER_ADMIN_REJECTE_PRODUCT;
                // $this->sendEmail($subject = 'Product Rejected MallShark', $templateSlug = 'product-rejected', $userData->email, $userData->name, $userProductId->title, $data['reason']);
            }
            if ($data['status'] == 'Deactivate') {
                $update = product::where('product_id', $data['product_id'])
                    ->update(['status' => '0']);
                $product = product::where('product_id', $data['product_id'])->first();
                $html = view('mail.deactivateProduct', ['store_name' => $store_name, 'product' => $product]);
                $subject = 'Product deactivated MallShark';
                $toEmail = $userData->email;
                $params = [
                    'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
                $subject = Constants::SUPER_ADMIN_DEACTIVE_PRODUCT;
                // $this->sendEmail($subject = 'Product deactivated MallShark', $templateSlug = 'product-deactivated', $userData->email, $userData->name, $userProductId->title);
                $product = Product::find($data['product_id']);
                $product->unsearchable();
            }
            if (isset($userData->id) && (!is_null($userData->id) || $userData->id != "")) {
                if ($storeData->super_status == '1') {
                    $device_tokens = DeviceToken::where('user_id', $userData->id)->where('app_flag', 'seller')->get();
                    $currentuserId = Constants::SUPER_ADMIN_ID;

                    if (isset($device_tokens) && count($device_tokens) > 0) {
                        foreach ($device_tokens as $device_token_obj) {

                            // dd($device_token_obj->fcm_token);


                            $subject = $subject;
                            // foreach($tokens as $token){
                            $notification = SellerFcmService::send(
                                $device_token_obj->fcm_token,
                                [
                                    'title' => $subject,
                                ]
                            );
                        }
                    }
                    addNotifications($userData->id,  $currentuserId, $subject, '', URL::to('/store-products'), 'store_products', '', 'seller');
                }
            }
            return true;
        }
    }

    public function getProductDataBySlug($slug)
    {
        $query = Product::where('slug', '=', $slug)->first();
        if (!empty($query)) {
            return $query->toArray();
        } else {
            return false;
        }
    }

    public function getAllUsers($getData)
    {
        $query = DB::table('users')
            ->select('users.user_id', 'users.name', 'users.email', 'users.status', 'role_user.role_id')
            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.user_id');

        if (!empty($getData['userrole'])) {
            $query->where('role_user.role_id', $getData['userrole']);
        }

        if (!empty($getData['keyword'])) {
            $query->where('users.name', 'like', '%' . $getData['keyword'] . '%');
        }

        $query->groupBy('users.user_id')
            ->orderBy('users.created_at', 'DESC');

        return $query->paginate(20);
    }

    public function getAllsupportUsers()
    {
    }

    public function objToArray($arr)
    {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    public function udpdateStatus($userId, $status)
    {
        if ($userId != '' && $status != '') {
            if ($status == 'Activate') {
                $update = User::where('user_id', $userId)
                    ->update(['status' => '1']);
                DB::table('products')->where('user_id', $userId)->where('status', '!=', '2')->update(['status' => '1']);
                $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();
            }
            if ($status == 'Deactivate') {
                $update = User::where('user_id', $userId)
                    ->update(['status' => '2']);
                $query = $this->_store->where('user_id', $userId)->update(['store_status' => '2']);
                DB::table('products')->where('user_id', $userId)->where('status', '!=', '2')->update(['status' => '0']);
                $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();
            }
            
            return $update;
        }
    }

    public function assignRoles($data)
    {
        $role = $this->_userroles->where('role_id', '=', $data['role_id'])->where('user_id', '=', $data['user_id'])->first();
        if (!empty($role) && $role->role_status == '1') {
            $this->_userroles->where('role_id', '=', $data['role_id'])->where('user_id', '=', $data['user_id'])->delete();
            $this->_esearch->uerUpdate($data['user_id']);

            if ($data['role_id'] == 4) {
                $this->_user->where('user_id', '=', $data['user_id'])->update(['status' => '2']);
            }
            if ($data['role_id'] == 2) {
                DB::table('products')->where('user_id', $data['user_id'])->update(['status' => '0']);
                $products = DB::table('products')->select('product_id')->where('user_id', $data['user_id'])->get()->toArray();
            }
        } else if (!empty($role) && $role->role_status == '0') {
            $this->_userroles->where('role_id', '=', $data['role_id'])->where('user_id', '=', $data['user_id'])->update(['role_status' => '1']);
            if ($data['role_id'] == 4) {
                $this->_user->where('user_id', '=', $data['user_id'])->update(['status' => '1']);
            }
            if ($data['role_id'] == 2) {
                DB::table('products')->where('user_id', $data['user_id'])->update(['status' => '1']);
                $products = DB::table('products')->select('product_id')->where('user_id', $data['user_id'])->get()->toArray();
            }
        } else {
            $this->_userroles->role_id = $data['role_id'];
            $this->_userroles->user_id = $data['user_id'];
            $this->_userroles->role_status = "1";
            $this->_userroles->save();
            $this->_esearch->uerUpdate($data['user_id']);
        }
    }

    public function getuserProfile($userId)
    {
        $query = $this->_user->with('roles')->where('user_id', '=', $userId)->first();
        if (!empty($query)) {
            $userData = $query->toArray();
            return $userData;
        } else {
            return false;
        }
    }

    public function updateuserProfile($data, $userId)
    {
        $updateData = array();
        $updateData['name'] = $data['user_name'];
        $updateData['user_country_id'] = $data['user_country'];
        $updateData['user_state_id'] = $data['user_state'];
        $updateData['user_city_id'] = $data['user_city_id'];
        $updateData['user_zip_code'] = $data['user_zip_code'];
        $updateData['contact_no'] = $data['contact_no'];
        $updateData['physical_address'] = $data['physical_address'];
        if (isset($data['avatar_image_hidden']) && $data['avatar_image_hidden'] != "") {
            $updateData['user_avatar'] = $data['avatar_image_hidden'];
        }
        $res = $this->_user->where('user_id', '=', $userId)->update($updateData);

        // Update user roles (sync in pivot table)
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user = $this->_user->find($userId);
            $user->roles()->sync($data['roles']);
        }

        if (is_null($res)) {
            return false;
        } else {
            return true;
        }
    }

    public function countActivateAndDeactiveUsers($status)
    {
        $count = $this->_user->where('status', '=', $status)->count();
        return $count;
    }

    public function getActiveListing($status)
    {
        $count = $this->_product->where('status', '=', "$status")->count();
        return $count;
    }

    public function getAllProducts()
    {
        $count = $this->_product->count();
        return $count;
    }
    public function getAllStorescount()
    {
        $count = $this->_store->count();
        return $count;
    }
    public function getAllTikTokVideosCount()
    {
        $count = $this->_tiktok->count();
        return $count;
    }
    public function getAllTikTokVideos(){    
        $allVideos = $this->_tiktok->orderBy('created_at', 'DESC')->paginate(10);
        return $allVideos;
    }

    public function sendEmail($subject, $templateSlug, $toEmail, $toCompletename, $productTitle, $reason = "")
    {
        // $subject = 'Ad Approved MallShark';
        //$templateSlug = 'ad-approved';
        //  $toEmail = $userData->email;
        //$toCompletename = $userData->name;
        $varsArray = array(
            array(
                'name' => 'USERNAME',
                'content' => $toCompletename
            ),
            array(
                'name' => 'PRODUCTNAME',
                'content' => $productTitle
            ),
            array(
                'name' => 'REASON',
                'content' => !empty($reason) ? $reason : ""
            ),
        );
        $this->mandrill->sendMandrillEmail($subject, $templateSlug, $toEmail, $toCompletename, $varsArray);
    }

    public function getPostadmin($postId)
    {
        $productInfo = array();
        $userId = Session::get('userData')['user_id'];
        $query = $this->_product->where('product_id', '=', $postId);
        if (!empty($query)) {
            $productDetail = $query->first();
        }
        if (!empty($productDetail)) {
            $productCategory = DB::table('product_category')->where('product_id', $postId)->get()->toArray();
            $productDetail['category'] = $productCategory;
            return $productDetail = $productDetail->toArray();
        } else {
            return false;
        }
    }

    public function getAllStoreProduct($data)
    {
        
        //DB::connection()->enableQueryLog();
        $query = DB::table('products')
        ->join('stores', 'products.store_id', '=', 'stores.store_id');
        // $query->whereIn('products.store_id', $store_ids);
        // $query->orderBy('store_id', 'DESC');
         $perPage = $data['per_page'] ?? 30;
        if (isset($data['store_id']) && !empty($data['store_id'])) {
            $storeIds = explode(',', $data['store_id']);
            if(is_array($storeIds) && count($storeIds) > 0){
                $query->whereIn('products.store_id', $storeIds);
            }
        }
        $query->orderBy('created_at', 'DESC');
        if (isset($data['search_products'])) {
            $query->where('title', 'like', '%' . $data['search_products'] . '%');
        }
        $query->select('products.product_id', 'products.slug', 'products.store_id', 'stores.store_name', 'stores.store_slug','products.status', 'products.user_id', 'products.title', 'products.price', 'products.quantity', 'products.created_at', 'products.description', 'products.short_description', 'products.is_feature','products.feature_status', 'products.feature_Image', 'products.product_type', 'products.sales', 'products.current_stock', 'products.user_revenue', 'products.mallshark_selected');
        if (isset($data['status'])) {
            if ($data['status'] == 'pending') {
                $query->where('status', '3');
            }
            if ($data['status'] == 'approve') {
                $query->where('status', '1');
            }
            if ($data['status'] == 'active') {
                $query->where('status', '1');
            }
            if ($data['status'] == 'deactive') {
                $query->where('status', '0');
            }
            if ($data['status'] == 'delete') {
                $query->where('status', '2');
            }
            if ($data['status'] == 'reject') {
                $query->where('status', '4');
            }
        }
        if (!empty($query)) {
            $query->where('status', '<>', '5');
            return $productData = $query->paginate($perPage)->withQueryString();
        } else {
            return false;
        }
    }
    public function getAllStoresAdmin($data)
    {
        $query = DB::table('stores')
            ->leftJoin('products', 'stores.store_id', '=', 'products.store_id')
            ->leftJoin('store_orders', 'stores.store_id', '=', 'store_orders.store_id')
            ->leftJoin('users', 'stores.user_id', '=', 'users.user_id')
            ->select(
                'stores.store_id',

                'users.name',
                'users.email',
                'stores.store_name',
                'stores.store_slug',
                'stores.stripe',
                'stores.is_completed',
                'stores.store_status',
                'stores.super_status',
                'stores.user_id',
                'stores.store_logo',
                'stores.banner_image_id',
                'stores.created_at',
                'stores.is_shopify_allowed',
                'stores.ai_enabled',
                'stores.is_wholesale_allowed',
                'stores.unique_product_selling',
                DB::raw('(SELECT COUNT(product_id) FROM products WHERE products.store_id = stores.store_id) AS product_count'),
                DB::raw('(SELECT COUNT(order_id) FROM store_orders WHERE store_orders.store_id = stores.store_id  AND store_orders.charge_status = "paid") AS order_count')
            )
            ->groupBy(
                'stores.store_id',
                'users.name',
                'stores.store_name',
                'stores.store_slug',
                'stores.store_status',
                'stores.super_status',
                'stores.user_id',
                'stores.store_logo',
                'stores.created_at',
                'stores.ai_enabled',

            );



        if (isset($data['search_stores'])) {
            $query->where('stores.store_name', 'like', '%' . $data['search_stores'] . '%');
        }

        if (isset($data['status'])) {
            if ($data['status'] == 'active') {
                $query->where('stores.store_status', '2');
            } elseif ($data['status'] == 'deactive') {
                $query->where('stores.store_status', '0');
            } elseif ($data['status'] == 'pending') {
                $query->where('stores.store_status', '1');
            } elseif ($data['status'] == 'reject') {
                $query->where('stores.store_status', '3');
            } elseif ($data['status'] == 'almost-active') {
                $query->where('stores.store_status', '1')
                    ->whereExists(function ($subquery) {
                        $subquery->select(DB::raw(1))
                            ->from('products')
                            ->whereColumn('products.store_id', 'stores.store_id')
                            ->whereIn('products.status', ['1', '3', '0'])
                            ->limit(1);
                    });
            }
        }
        
        if (!empty($data['status']) && $data['status'] === 'almost-active') {
            $query->orderBy('stores.updated_at', 'DESC');
        } else {
            $query->orderBy('stores.created_at', 'DESC');
        }

        return $storeData = $query->paginate(30)->withQueryString();
    }
    public function getActiveStores($data = array())
    {

        $storeStatus = StoreConstants::STATUS_ACTIVE;
        $query = Store::where('store_status','2')
        ->with(['products' => function($query){
            $query->where('status', ProductConstants::STATUS_ACTIVE);
        }])
        ->select('store_id', 'store_name', 'store_status')
        ->get();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }   
    }
    public function enableStoreAdmin($data)
    {
        $store_id = $data['store_id'];
        $store_status = $data['storeStatus'];
        $user_id = $data['user_id'];
        $userData = getUserByIdchat($user_id);
        $storeData = Store::select('store_name')->where('store_id', $store_id)->first();
        $store_name = $storeData->store_name;
        $currentuserId = Session::get('userData')['user_id'];

        // if ($store_status == 0) {

        DB::table('products')->where('user_id', $user_id)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '1']);
        $products = DB::table('products')->select('product_id')->where('user_id', $user_id)->get()->toArray();

      

        $response = DB::table('stores')
            ->where('store_id', $store_id)
            ->update([
                'super_status' => '1'
            ]);
        $html = view('mail.store.activate-store-admin', ['store_name' => $store_name]);
        $subject = Constants::STORE_ENABLE_SUBJECT;
        $toEmail = $userData->email;
        $params = [
            'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
            'to'      => $toEmail,
            'subject' => $subject,
            'html'    => "$html",
        ];
        $this->mailgun->SendMailgunMail($params);
        if (isset($userData->id) && !is_null($userData->id)) {
            $userData = User::with('device_token')->findOrFail($userData->id);
            $device_tokens = DeviceToken::where('user_id', $userData->user_id)->where('app_flag', 'seller')->get();
            if ($userData) {
                $tokens = [];
                if (isset($device_tokens) && count($device_tokens) > 0) {
                    foreach ($device_tokens as $device_token_obj) {

                        // dd($device_token_obj->fcm_token);


                        $subject = $subject;
                        $description =  $subject;
                        // foreach($tokens as $token){
                        $notification = SellerFcmService::send(
                            $device_token_obj->fcm_token,
                            [
                                'title' => $subject,
                            ]
                        );
                    }
                }
                addNotifications($userData->user_id,  $currentuserId, $subject, '', URL::to('/store-dashboard'), 'store_dashboard', '', 'seller');
            }
        }

        // dd($response);
        return $response > 0 ? true : false;
        // } else {
        //     return false; 
        // }
    }
    public function activateStoreAdmin($data)
    {
        $store_id = $data['store_id'];
        $store_status = $data['storeStatus'];
        $user_id = $data['user_id'];
        $userData = getUserByIdchat($user_id);
        $storeData = Store::select('store_name')->where('store_id', $store_id)->first();
        $store_name = $storeData->store_name;
        $storeActiveStatus = StoreConstants::STATUS_ACTIVE;
        $currentuserId = Session::get('userData')['user_id'];

        // if ($store_status == 0) {

        DB::table('products')->where('user_id', $user_id)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '1']);

        $response = DB::table('stores')
            ->where('store_id', $store_id)
            ->update([
                'store_status' => '2'
            ]);
            // Index store in Algolia if it's fully active
        $storeModel = StoreV3::find($store_id);
        if ($storeModel && $storeModel->store_status == '2') {
            $storeModel->searchable();
        }
            Product::where('store_id', $store_id)->searchable();
        $html = view('mail.store.activate-store-admin', ['store_name' => $store_name]);
        $subject = Constants::STORE_ACTIVATED_SUBJECT;
        $toEmail = $userData->email;
        $params = [
            'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
            'to'      => $toEmail,
            'subject' => $subject,
            'html'    => "$html",
        ];
        $this->mailgun->SendMailgunMail($params);
        if (isset($userData->id) && !is_null($userData->id)) {
            $userData = User::with('device_token')->findOrFail($userData->id);
            $device_tokens = DeviceToken::where('user_id', $userData->user_id)->where('app_flag', 'seller')->get();
            if ($userData) {
                $tokens = [];
                if (isset($device_tokens) && count($device_tokens) > 0) {
                    foreach ($device_tokens as $device_token_obj) {

                        // dd($device_token_obj->fcm_token);


                        $subject = $subject;
                        // foreach($tokens as $token){
                        $notification = SellerFcmService::send(
                            $device_token_obj->fcm_token,
                            [
                                'title' => $subject,
                            ]
                        );
                    }
                }
                addNotifications($userData->user_id,  $currentuserId, $subject, '', URL::to('/store-dashboard'), 'store_dashboard', '', 'seller');
            }
        }

        // dd($response);
        return $response > 0 ? true : false;
        // } else {
        //     return false; 
        // }
    }

    public function disableStoreAdmin($data)
    {
        // dd($data);
        $store_id = $data['store_id'];
        $store_status = $data['storeStatus'];
        $user_id = $data['user_id'];
        $userData = getUserByIdchat($user_id);
        $storeData = Store::select('store_name')->where('store_id', $store_id)->first();
        $store_name = $storeData->store_name;
        $currentuserId = Session::get('userData')['user_id'];

        // if ($store_status == 2) {

        DB::table('products')->where('user_id', $user_id)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '0']);
        $products = Product::where('store_id', $store_id)->unsearchable();

        $response = DB::table('stores')
            ->where('store_id', $store_id)
            ->update([
                'super_status' => '0',
                'store_status' => '0'
            ]);
        // Remove store from Algolia
        $storeModel = StoreV3::find($store_id);
        if ($storeModel) {
            $storeModel->unsearchable();
        };
        $html = view('mail.store.deactivate-store-admin', ['store_name' => $store_name]);
        $subject = Constants::STORE_DISABLE_SUBJECT;
        $toEmail = $userData->email;
        $params = [
            'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
            'to'      => $toEmail,
            'subject' => $subject,
            'html'    => "$html",
        ];
        $this->mailgun->SendMailgunMail($params);
        if (isset($userData->id) && !is_null($userData->id)) {
            $userData = User::with('device_token')->findOrFail($userData->id);
            $device_tokens = DeviceToken::where('user_id', $userData->user_id)->where('app_flag', 'seller')->get();
            if ($userData) {
                $tokens = [];
                if (isset($device_tokens) && count($device_tokens) > 0) {
                    foreach ($device_tokens as $device_token_obj) {

                        // dd($device_token_obj->fcm_token);


                        $subject = $subject;
                        // foreach($tokens as $token){
                        $notification = SellerFcmService::send(
                            $device_token_obj->fcm_token,
                            [
                                'title' => $subject,
                            ]
                        );
                    }
                }
                addNotifications($userData->user_id,  $currentuserId, $subject, '', URL::to('/store-dashboard'), 'store_dashboard', '', 'seller');
            }
        }


        return $response > 0 ? true : false;
        // } else {
        //     return false; 
        // }
    }

    public function deactiavteStoreAdmin($data)
    {
        $store_id = $data['store_id'];
        $store_status = $data['storeStatus'];
        $user_id = $data['user_id'];
        $userData = getUserByIdchat($user_id);
        $storeData = Store::select('store_name')->where('store_id', $store_id)->first();
        $store_name = $storeData->store_name;
        $currentuserId = Session::get('userData')['user_id'];

        // if ($store_status == 2) {

        DB::table('products')->where('user_id', $user_id)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '0']);
        $products = Product::where('store_id', $store_id)->unsearchable();

        $response = DB::table('stores')
            ->where('store_id', $store_id)
            ->update([
                'store_status' => '0'
            ]);
            // Remove store from Algolia
        $storeModel = StoreV3::find($store_id);
        if ($storeModel) {
            $storeModel->unsearchable();
        };
        $html = view('mail.store.deactivate-store-admin', ['store_name' => $store_name]);
        $subject = Constants::STORE_DEACTIVATED_SUBJECT;
        $toEmail = $userData->email;
        $params = [
            'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
            'to'      => $toEmail,
            'subject' => $subject,
            'html'    => "$html",
        ];
        $this->mailgun->SendMailgunMail($params);
        if (isset($userData->id) && !is_null($userData->id)) {
            $userData = User::with('device_token')->findOrFail($userData->id);
            $device_tokens = DeviceToken::where('user_id', $userData->user_id)->where('app_flag', 'seller')->get();
            if ($userData) {
                $tokens = [];
                if (isset($device_tokens) && count($device_tokens) > 0) {
                    foreach ($device_tokens as $device_token_obj) {

                        // dd($device_token_obj->fcm_token);


                        $subject = $subject;
                        // foreach($tokens as $token){
                        $notification = SellerFcmService::send(
                            $device_token_obj->fcm_token,
                            [
                                'title' => $subject,
                            ]
                        );
                    }
                }
                addNotifications($userData->user_id,  $currentuserId, $subject, '', URL::to('/store-dashboard'), 'store_dashboard', '', 'seller');
            }
        }
        // addNotifications($userData->id,  $currentuserId, $subject, $subject, URL::to('/store-dashboard'));

        return $response > 0 ? true : false;
        // } else {
        //     return false; 
        // }
    }
    public function approvedStoreAdmin($data)
    {
        $store_id = $data['store_id'];
        $store_status = $data['storeStatus'];
        $user_id = $data['user_id'];
        $userData = getUserByIdchat($user_id);
        $storeData = Store::select('store_name')->where('store_id', $store_id)->first();
        $store_name = $storeData->store_name;
        $currentuserId = Session::get('userData')['user_id'];
        if ($store_status == 3 || $store_status == 1) {
            $activeProducts = DB::table('products')->where('user_id', $user_id)->whereIn('status', ['1', '3', '0'])->count();
            if ($activeProducts > 0) {
                $response = DB::table('stores')
                    ->where('store_id', $store_id)
                    ->update([
                        'store_status' => '0',
                        'super_status' => '1'
                    ]);

                $html = view('mail.store.approved-store-admin', ['store_name' => $store_name]);
                $subject = Constants::STORE_APPROVED_SUBJECT;
                $toEmail = $userData->email;
                $params = [
                    'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
                if (isset($userData->id) && !is_null($userData->id)) {
                    $userData = User::with('device_token')->findOrFail($userData->id);
                    $device_tokens = DeviceToken::where('user_id', $userData->user_id)->where('app_flag', 'seller')->get();
                    if ($userData) {
                        $tokens = [];
                        if (isset($device_tokens) && count($device_tokens) > 0) {
                            foreach ($device_tokens as $device_token_obj) {

                                $subject = $subject;
                                $notification = SellerFcmService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
                            }
                        }
                        addNotifications($userData->user_id,  $currentuserId, $subject, '', URL::to('/store-dashboard'), 'store_dashboard', '', 'seller');
                    }
                }
                return $response > 0 ? true : false;
            }
            
            return false;
        } else {
            return false;
        }
    }

    public function rejectStoreAdmin($data)
    {

        $store_id = $data['store_id'];
        $store_status = $data['status'];
        $user_id = $data['user_id'];
        $reason = $data['reason'];
        $userData = getUserByIdchat($user_id);
        $storeData = Store::select('store_name')->where('store_id', $store_id)->first();
        $store_name = $storeData->store_name;
        $currentuserId = Session::get('userData')['user_id'];

        if ($store_status == 1) {

            DB::table('products')->where('user_id', $user_id)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '0']);
            $products = DB::table('products')->select('product_id')->where('user_id', $user_id)->get()->toArray();

            $response = DB::table('stores')
                ->where('store_id', $store_id)
                ->update([
                    'store_status' => '3',
                    'super_status' => '0',
                    'reject_reason' => $reason
                ]);
            $storeModel = StoreV3::find($store_id);
            if ($storeModel) {
                $storeModel->unsearchable();
            };
            $html = view('mail.store.rejected-store-admin', ['store_name' => $store_name, 'reason' => $reason]);
            $subject = Constants::STORE_REJECTED_SUBJECT;
            $toEmail = $userData->email;
            $params = [
                'from'    => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                'to'      => $toEmail,
                'subject' => $subject,
                'html'    => "$html",
            ];
            $this->mailgun->SendMailgunMail($params);

            if (isset($userData->id) && !is_null($userData->id)) {
                $userData = User::with('device_token')->findOrFail($userData->id);
                $device_tokens = DeviceToken::where('user_id', $userData->user_id)->where('app_flag', 'seller')->get();
                if ($userData) {
                    $tokens = [];
                    if (isset($device_tokens) && count($device_tokens) > 0) {
                        foreach ($device_tokens as $device_token_obj) {

                            // dd($device_token_obj->fcm_token);


                            $subject = $subject;
                            // foreach($tokens as $token){
                            $notification = SellerFcmService::send(
                                $device_token_obj->fcm_token,
                                [
                                    'title' => $subject,
                                ]
                            );
                        }
                    }
                    addNotifications($userData->user_id,  $currentuserId, $subject, '', URL::to('/store-dashboard'), 'store_dashboard', '', 'seller');
                }
            }
            return $response > 0 ? true : false;
        } else {
            return false;
        }
    }





    public function bannerUpload($file, $type = 'digital', $remove = false, $crop = true)
    {
        $destinationPath = public_path() . '/uploads/banner';
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0775, true);
        }
        $nameOnly = time();
        $fileName = $nameOnly . '.' . $file->getClientOriginalExtension();
        $getDirectorywithSizes = DB::table('attachment_sizes')->where('name', 'banner')->first();
        $directoryName = $getDirectorywithSizes->name;
        $width = $getDirectorywithSizes->width;
        $height = $getDirectorywithSizes->height;
        $fileExtension = $file->getClientOriginalExtension();
        $attachments = new Attachments();
        $attachments->fileName = $nameOnly;
        $attachments->fileExtension = $fileExtension;
        $attachments->save();
        $attachmentId = $attachments->attachment_id;
        if ($attachmentId) {
            $destinationPath = public_path() . '/uploads/' . $directoryName;
            if ($width == 0 && $height == 0) {
                //$readImage = file_get_contents($getRealPath);
                //file_put_contents($destinationPath . '/' . $fileName, $readImage);
            } else {
                if ($crop) {
                    $img = Image::make($file);
                    $img->fit($width, $height, function ($constraint) {
                        $constraint->upsize();
                    });
                    $img->save($destinationPath . '/' . $fileName);
                } else {
                    $img = Image::make($file);
                    $img->resize($width, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath . '/' . $fileName);
                }
            }
            return $attachmentId;
        }
    }

    public function getSaleData($data)
    {
        
        $query = DB::table('store_orders')->join('order_items', 'order_items.order_id', '=', 'store_orders.order_id')->select('store_orders.order_id', 'store_orders.created_at', 'store_orders.store_id', 'store_orders.total_amount','store_orders.shipping_amount')->where('store_orders.charge_status','paid')->orderBy('store_orders.created_at');
        if (!empty($data['store_id'])) {
            $query->where('store_orders.store_id', $data['store_id']);
        }
        $data['store_ids'] = $data['store_ids'] ? explode(',',$data['store_ids']) : [];
        if (!empty($data['store_ids']) && count($data['store_ids']) > 0) {
            $query->whereIn('store_orders.store_id', $data['store_ids']);
        }
        // type not empty and date empty
        if (!empty($data['date_to']) && !empty($data['date_from'])) {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($data['date_from'])));
            $query->whereBetween('store_orders.created_at', [$data['date_to'], $from]);
            $data = $query->get();
        } elseif (!empty($data['type']) && $data['type'] == "yearly") {
            $query->whereYear('store_orders.created_at', date('Y'));
            $data = $query->get();
        } elseif (!empty($data['type']) && $data['type'] == "monthly") {
            $query->whereMonth('store_orders.created_at', date('m'))
            ->whereYear('store_orders.created_at', date('Y'));
            $data = $query->get();
        } else if (!empty($data['type']) && $data['type'] == "lastmonth") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query->whereMonth('store_orders.created_at', $month)
            ->whereYear('store_orders.created_at', date('Y'));          
            $data = $query->get();
        } else if (!empty($data['type']) && $data['type'] == "weekly") {
            $date = date('Y-m-d');
            // dd($date);
            $from = date('Y-m-d', strtotime($date));
            $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query->whereBetween('store_orders.created_at', [$from, $to]);
            $data = $query->get();
        } else if (!empty($data['type']) && $data['type'] == "daily") {
            $date = date('Y-m-d');
            $query->whereDate('store_orders.created_at', $date);
            $data = $query->get();
        }
        $arr2 = objToArray($data);
        $new_arr = array();
        foreach ($arr2 as $key => $arr) {
            $arrcreated_at = explode(' ', $arr['created_at']);
            $arrcreated_at = $arrcreated_at[0];
            $updated = true;
            foreach ($new_arr as $key2 => $arr3) {
                if ($arr3['created_at'] == $arrcreated_at) {
                    $existing_total = $arr3['total_amount'] + $arr['total_amount'];
                    $new_arr[$key2]['total_amount'] = $existing_total;
                    $updated = false;
                }
            }
            if ($updated) {
                $new_arr[] = array('created_at' => $arrcreated_at, 'total_amount' => $arr['total_amount']);
            }
        }
        return $new_arr;
    }
    public function getProductSaleData($data)
{
    $storeId = $data['store_id'];

    $query = DB::table('order_items')
    ->join('store_orders', 'store_orders.order_id', '=', 'order_items.order_id')
    ->where('store_orders.store_id', $storeId) // ✅ specific store
    ->where('store_orders.charge_status', 'paid') // ✅ only paid orders
    ->select(
        'order_items.item_id',
        'order_items.product_name',
        'order_items.item_image',
        'store_orders.store_id',
        'store_orders.order_id',
        DB::raw('SUM(order_items.quantity) as total_quantity'),
         DB::raw('SUM(
            CASE
                WHEN (order_items.sold_price + 0) > 0 THEN (order_items.sold_price + 0) * (order_items.quantity + 0)
                ELSE (order_items.price + 0) * (order_items.quantity + 0)
            END
        ) as total_amount'),
        DB::raw('DATE(store_orders.created_at) as created_at')
    )
    ->groupBy(
        'order_items.item_id',
        'order_items.product_name',
        'order_items.item_image',
        'store_orders.store_id',
        'store_orders.order_id',
        DB::raw('DATE(store_orders.created_at)')
    )
    ->orderBy('store_orders.created_at', 'asc');

    // Filter by product_id
    if (!empty($data['product_id'])) {
        $query->where('order_items.item_id', $data['product_id']);
    }

    // Filter by multiple product_ids
    $data['product_ids'] = !empty($data['product_ids']) ? explode(',', $data['product_ids']) : [];
    if (!empty($data['product_ids'])) {
        $query->whereIn('order_items.item_id', $data['product_ids']);
    }

    // Date filters (✅ use store_orders.created_at instead of order_items.created_at)
    if (!empty($data['date_to']) && !empty($data['date_from'])) {
        $from = date('Y-m-d', strtotime("+1 days", strtotime($data['date_from'])));
        $query->whereBetween('store_orders.created_at', [$data['date_to'], $from]);
    } elseif (!empty($data['type']) && $data['type'] == "yearly") {
        $query->whereYear('store_orders.created_at', date('Y'));
    } elseif (!empty($data['type']) && $data['type'] == "monthly") {
        $query->whereMonth('store_orders.created_at', date('m'))
            ->whereYear('store_orders.created_at', date('Y'));
    } elseif (!empty($data['type']) && $data['type'] == "lastmonth") {
        $date = now();
        $month = $date->subMonth()->format('m');
        $query->whereMonth('store_orders.created_at', $month)
            ->whereYear('store_orders.created_at', date('Y'));
    } elseif (!empty($data['type']) && $data['type'] == "weekly") {
        $from = now()->startOfWeek();
        $to = now()->endOfWeek();
        $query->whereBetween('store_orders.created_at', [$from, $to]);
    } elseif (!empty($data['type']) && $data['type'] == "daily") {
        $query->whereDate('store_orders.created_at', date('Y-m-d'));
    }

    $data = $query->get();
    // Optional: aggregate totals by date in PHP (but SQL already groups by date!)
    $arr2 = objToArray($data);
    $new_arr = [];
    foreach ($arr2 as $arr) {
        $arrcreated_at = $arr['created_at'];

        $found = false;
        foreach ($new_arr as &$arr3) {
            if ($arr3['created_at'] == $arrcreated_at) {
                $arr3['total_amount'] += $arr['total_amount'];
                $arr3['total_quantity'] += $arr['total_quantity'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $new_arr[] = [
                'created_at'     => $arrcreated_at,
                'total_amount'   => $arr['total_amount'],
                'total_quantity' => $arr['total_quantity'],
            ];
        }
    }

    return $new_arr;
}


    public function getAllStoresSales($data = array())
    {
        if (isset($data['store_name']) && $data['store_name'] != "") {
            $query = $this->_store->select('store_id', 'store_name', 'store_logo')
            ->where('store_name', 'like', '%' . $data['store_name'] . '%')
            ->whereIn('store_status', ['0', '2'])
            ->get();
        } else if (isset($data['search_status']) && $data['search_status'] == "yes") {
           $query = $this->_store->select('store_id', 'store_name', 'store_logo')
            ->where('store_name', 'like', '%' . $data['term'] . '%')
            ->whereIn('store_status', ['0', '2'])
            ->get();
        } else {
            $query = $this->_store->select('store_id', 'store_name', 'store_logo')
            ->whereIn('store_status', ['0', '2'])
            ->get();
        }
        $parms['type'] = isset($data['type']) ? $data['type'] : "yearly";
        $parms['date_to'] = isset($data['date_to']) ? $data['date_to'] : "";
        $parms['date_from'] = isset($data['date_from']) ? $data['date_from'] : "";
       foreach ($query as $key => $store) {
            $storeSaleData = AdminHelpers::getStoreSale($store->store_id,$parms);
            $store->total_sale = isset($storeSaleData['store_sale']) ? $storeSaleData['store_sale'] : 0;
        }
        $query = collect($query)->sortByDesc('total_sale')->values()->all();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }
    public function getAllSaleProducts($data = array())
    {
        $activeStatus = ProductConstants::STATUS_ACTIVE;
        $deActiveStatus = ProductConstants::STATUS_DEACTIVATED;
       if (isset($data['search_status']) && $data['search_status'] == "yes") {
            $query = $this->_product->select(
                'products.product_id',
                'products.title',
                'products.feature_image',
                DB::raw('(SELECT order_items.item_image 
                        FROM order_items
                        JOIN store_orders ON store_orders.order_id = order_items.order_id
                        WHERE order_items.item_id = products.product_id
                            AND store_orders.charge_status = "paid"
                        LIMIT 1) as item_image')
            )
            ->where('products.title', 'like', '%' . $data['term'] . '%')
            ->whereIn('products.status', [$activeStatus, $deActiveStatus])
            ->get();
        } else {
            $query = $this->_product->select(
                'products.product_id',
                'products.title',
                'products.feature_image',
                DB::raw('(SELECT order_items.item_image 
                        FROM order_items
                        JOIN store_orders ON store_orders.order_id = order_items.order_id
                        WHERE order_items.item_id = products.product_id
                            AND store_orders.charge_status = "paid"
                        LIMIT 1) as item_image')
            )
            ->whereIn('products.status', [$activeStatus, $deActiveStatus])
            ->get();
        }
        $parms['type'] = isset($data['type']) ? $data['type'] : "yearly";
        $parms['date_to'] = isset($data['date_to']) ? $data['date_to'] : "";
        $parms['date_from'] = isset($data['date_from']) ? $data['date_from'] : "";
        $parms['store_id'] = isset($data['store_id']) ? $data['store_id'] : "";
        foreach ($query as $key => $product) {
            $productSaleData = AdminHelpers::getProductSales($product->product_id,$parms);
            if($productSaleData > 0){
                $product->total_sale = isset($productSaleData) ? $productSaleData : 0;
            }else{
                unset($query[$key]);
            }
            
        }
        $query = collect($query)->sortByDesc('total_sale')->values()->all();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }
    public function gettotalProducts($data = "")
    {
        $query = DB::table('products')->select('product_id', 'created_at')->where('status', '1');
        if (isset($data['type']) && $data['type'] == "yearly") {
            $query->whereYear('created_at', date('Y'));
            return $data = $query->get();
        }
    }
}
