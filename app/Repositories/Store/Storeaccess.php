<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Store;

use DB;
use App;
use Hash;
use Mail;
use Image;
use Cookie;
use Request;
use Session;
use DateTime;
use DateInterval;
use DateTimeZone;
use App\BaseAccess;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Userrole;
use App\Models\Campaigns;
use App\Models\StoreIcon;
use App\Models\StoreOrder;
use App\Models\CampaignStore;
use App\Models\FeaturePrices;
use App\Models\CategoryProduct;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Repositories\Product\Productaccess;
use App\Repositories\Campaign\Campaignaccess;
use App\Http\Controllers\ElasticSearchController;
use Twilio\Rest\Serverless\V1\Service\FunctionPage;

/**
 * Description of StoreAccess
 *
 * @author Asif
 */
class Storeaccess extends BaseAccess
{

    private $_store;
    private $_languages;
    private $_countries;
    private $productObj;
    private $_user;
    private $_product;
    private $_attachment;
    private $_storeorders;
    private $storeicons;
    private $_userRole;
    private $_esearch;

    public function __construct()
    {
        $this->_store = new Store();
        $this->_userRole = new Userrole();
        $this->_user = new User();
        $this->_product = new Product();
        $this->productObj = new Productaccess();
        $this->_storeorders = new StoreOrder();
        $this->_esearch = new ElasticSearchController();
        $this->storeicons = new StoreIcon();
    }

    public function saveVendor($data)
    {
        $userId = Session::get('userData')['user_id'];
        $this->_store->user_id = $userId;
        $storeUserData = $this->_store->where('user_id', '=', $userId)->count();

        if ($storeUserData > 0) {
            if (isset($data['has_store'])) {
                $this->_user->where('user_id', '=', $userId)->update(['has_store' => '1']);
                $Userrolecount = $this->_userRole->where('user_id', '=', $userId)->where('role_id', '=', 2)->count();
                if ($Userrolecount == 0) {
                    $this->_userRole->user_id = $userId;
                    //        role for seller is 2
                    $this->_userRole->role_id = 2;
                    $this->_userRole->save();
                }

                unset($data['has_store']);
            }
            $this->_store->where('user_id', '=', $userId)->update($data);
        } else {
            $this->_store->shop_language = $data['shop_language'];
            $this->_store->shop_country = $data['shop_country'];
            $this->_store->shop_currency = $data['shop_currency'];
            $this->_store->available_duration = $data['available_duration'];
            $this->_store->about_store = $data['about_store'];
            if (isset($data['other_available_duration'])) {
                $this->_store->other_available_duration = $data['other_available_duration'];
            } else {
                $this->_store->other_available_duration = '';
            }
            $this->_store->save();
        }
    }

    public function saveStore($postedData)
    {
        $userId = Session::get('userData')['user_id'];
        $data = array();
        $data['user_id'] = $userId;
        $data['terms_condtions'] = isset($postedData['store_term_conditon']) ? $postedData['store_term_conditon'] : '';
        $data['store_website'] = isset($postedData['store_website']) ? $postedData['store_website'] : '';
        $data['store_country'] = isset($postedData['store_country']) ? $postedData['store_country'] : "";
        $data['store_state'] = isset($postedData['store_state']) ? $postedData['store_state'] : "";
        $data['store_city'] = isset($postedData['store_city']) ? $postedData['store_city'] : '';
        $data['street_address'] = isset($postedData['street_address']) ? $postedData['street_address'] : '';
        $data['zip_code'] = isset($postedData['zip_code']) ? $postedData['zip_code'] : '';
        $data['contact_info'] = isset($postedData['store_phone']) ? $postedData['store_phone'] : '';
        $data['about_store'] = isset($postedData['about_store']) ? $postedData['about_store'] : '';
        $data['policies'] = isset($postedData['shop_policy_radio']) ? $postedData['shop_policy_radio'] : '';
        $data['is_completed'] = '1';
        $data['store_status'] = '1';
        if (isset($postedData['store_image_id']) && $postedData['store_image_id'] != "") {
            $data['store_logo'] = $postedData['store_image_id'];
        }
        $storebanners = isset($postedData['storeBannerImage']) ? $postedData['storeBannerImage'] : '';
        $storeBannerImages = [];
        if (is_array($storebanners) && count($storebanners) > 0) {

            foreach ($storebanners as $image) {
                $storeBannerImages[] = $this->productObj->getAttachmentId($image, 'image', false);
            }
        }

        $storeInfo = getStoreInfoByUserId($userId);
        if ($storeInfo) {
            $existingSerializedIds = $storeInfo->banner_image_id;
            $bannerIds = unserialize($existingSerializedIds);
            if (is_array($bannerIds)) {
                // Assuming $storeBannerImages is the array you want to merge
                if (is_array($storeBannerImages)) {
                    $newIdsArray = array_merge($bannerIds, $storeBannerImages);
                    $newSerializedIds = serialize($newIdsArray);
                } else {
                }
            } else {
                $newSerializedIds = serialize($storeBannerImages);
            }
        } else {
        }
        $data['banner_image_id'] = $newSerializedIds;
        $this->_store->where('user_id', '=', $userId)->update($data);
    }
    public function saveStoreName($data)
    {
        if (isset($data['campaign'])) {
            $campaignResults = Campaigns::where('slug', $data['campaign'])->first();
        }

        if (isset($data['referred'])) {
            $referredResults = User::where('referral_code', $data['referred'])->first();
        }
        $three_months = new DateInterval('P3M');
        $nextThreeMonth = new DateTime();
        $currentdate = new DateTime();
        $nextThreeMonth->add($three_months);

        $userId = Session::get('userData')['user_id'];
        $this->_store->user_id = $userId;
        $this->_store->store_name = isset($data['store_name']) ? $data['store_name'] : "";
        $this->_store->terms_condtions = isset($data['store_term_conditon']) ? $data['store_term_conditon'] : "";
        $this->_store->is_completed = '0';
        $this->_store->store_status = '1';
        $this->_store->referred = isset($referredResults->user_id) ? $referredResults->user_id : null;

        if (isset($data['store_name'])) {
            $store_slug = str_slug($data['store_name'], '-');
            $this->_store->store_slug = $store_slug;
        }


        $this->_store->save();
        $lastInsertedStore = $this->_store->store_id;

        $dataLayerCreateStoreEvent = [
            'event' => 'store_created',
            'userData' => [
                'user_id' => $userId,
                'store_id' => $lastInsertedStore,
                'store_name' => $this->_store->store_name,
                'store_slug' => $this->_store->store_slug,
                'user_list_id' => 'store_created',
                'user_list_name' => 'Store Created',
            ],
        ];
        session(['dataLayerCreateStoreEvent' => $dataLayerCreateStoreEvent]);


        $campaignStore = new CampaignStore();
        if (isset($data['campaign'])) {
            $campaignStore->campaign_id = $campaignResults->id ? $campaignResults->id : null;
            $campaignStore->store_id = $lastInsertedStore;
            $campaignStore->campaign_join = $currentdate;
            $campaignStore->campaign_expiry = $nextThreeMonth;
            $campaignStore->status = '1';
            $campaignStore->save();
        }


        $Userrolecount = $this->_userRole->where('user_id', '=', $userId)->where('role_id', '=', 2)->count();
        if ($Userrolecount == 0) {
            $this->_userRole->user_id = $userId;
            //  role for store is 2
            $this->_userRole->role_id = 2;
            $this->_userRole->save();
        }
    }
    //Update Store Name
    public function updateStoreName($postedData)
    {

        $userId = Session::get('userData')['user_id'];
        $data = array();
        $data['user_id'] = $userId;
        $data['store_name'] = isset($postedData['store_name']) ? $postedData['store_name'] : '';
        $data['is_completed'] = '0';
        $store_slug = str_slug($postedData['store_name'], '-');
        $data['store_slug'] = isset($store_slug) ? $store_slug : '';

        $this->_store->where('user_id', '=', $userId)->update($data);
    }

    public function saveStoreAddressApi($postedData)
    {
        $userId = Auth::user()->user_id;
        $data = array();
        $data['user_id'] = $userId;
        $data['terms_condtions'] = isset($postedData['store_term_conditon']) ? $postedData['store_term_conditon'] : '';
        $data['store_website'] = isset($postedData['store_website']) ? $postedData['store_website'] : '';
        if(isset($postedData['store_country']) && $postedData['store_country'] == ''){
            $country = getCountryByName($postedData['store_country']);
            $countryName = $country->name;
            $data['store_country'] = $countryName;
        }else{
            $data['store_country'] = 1;
        }
        $data['store_state'] = isset($postedData['store_state']) ? $postedData['store_state'] : "";
        $data['store_city'] = isset($postedData['store_city']) ? $postedData['store_city'] : '';
        $data['street_address'] = isset($postedData['street_address']) ? $postedData['street_address'] : '';
        $data['zip_code'] = isset($postedData['zip_code']) ? $postedData['zip_code'] : '';
        $data['contact_info'] = isset($postedData['store_phone']) ? $postedData['store_phone'] : '';
        $data['about_store'] = isset($postedData['about_store']) ? $postedData['about_store'] : '';
        $data['policies'] = isset($postedData['shop_policy_radio']) ? $postedData['shop_policy_radio'] : '';
        $data['is_completed'] = '1';
        $data['store_status'] = '1';
        // echo 'Data: <pre>' .print_r($data,true). '</pre>'; die();
        $this->_store->where('user_id', '=', $userId)->update($data);

        return true;
    }

    public function saveStoreApi($data, $id = null, $bannerImage = [])
    {
        // echo 'Data: <pre>' .print_r($data,true). '</pre>'; die();
        $userId = Auth::user()->user_id;
        $this->_store->user_id = $userId;
        $this->_store->store_name = $data['store_name'];
        $store_slug = str_slug($data['store_name'], '-');
        $this->_store->store_slug = $store_slug;
        $this->_store->store_logo = $id;
        $storebanners = isset($bannerImage['bannerserialize']) ? $bannerImage['bannerserialize'] : '';
        $this->_store->banner_image_id = $storebanners;
        $this->_store->save();

        $Userrolecount = $this->_userRole->where('user_id', '=', $userId)->where('role_id', '=', 2)->count();
        if ($Userrolecount == 0) {
            $this->_userRole->user_id = $userId;
            //  role for store is 2
            $this->_userRole->role_id = 2;
            $this->_userRole->save();
        }
        return true;
    }

    public function updateStoreNameApi($store_name, $id = null, $bannerImage = [])
    {
        $userId = Auth::user()->user_id;
        $store_slug = str_slug($store_name, '-');
        
        // Fetch the store record based on the user ID
        $store = $this->_store->where('user_id', $userId)->first();

        if (!$store) {
            return false; // Handle case where the store for the user is not found
        }

        // Update store details
        $store->store_name = isset($store_name) ? $store_name : $store->store_name;
        $store->store_slug = isset($store_slug) ? $store_slug : $store->store_slug;
        $store->store_logo = isset($id) ? $id : $store->store_logo;

        $storeBanners = isset($bannerImage['bannerserialize']) ? $bannerImage['bannerserialize'] : $store->banner_image_id;
        $store->banner_image_id = $storeBanners;
        $store->save();

        return $store;
    }


    public function updateStore($postedData)
    {
        //  dd($postedData);
        $userId = Session::get('userData')['user_id'];
        $data = array();
        $data['user_id'] = $userId;
        $data['terms_condtions'] = isset($postedData['store_term_conditon']) ? $postedData['store_term_conditon'] : '';
        $data['store_website'] = isset($postedData['store_website']) ? $postedData['store_website'] : '';
        $data['store_country'] = isset($postedData['store_country']) ? $postedData['store_country'] : "";
        $data['store_state'] = isset($postedData['store_state']) ? $postedData['store_state'] : "";
        $data['store_city'] = isset($postedData['store_city']) ? $postedData['store_city'] : '';
        $data['street_address'] = isset($postedData['street_address']) ? $postedData['street_address'] : '';
        $data['zip_code'] = isset($postedData['zip_code']) ? $postedData['zip_code'] : '';
        $data['contact_info'] = isset($postedData['store_phone']) ? $postedData['store_phone'] : '';
        $data['about_store'] = isset($postedData['about_store']) ? $postedData['about_store'] : '';
        $data['policies'] = isset($postedData['shop_policy_radio']) ? $postedData['shop_policy_radio'] : '';
        $previous_image_id = isset($postedData['previous_image_id']) ? $postedData['previous_image_id'] : '';
        $data['is_completed'] = '1';
        // $data['store_status'] = '2';
        $storebanners = isset($postedData['storeBannerImage']) ? $postedData['storeBannerImage'] : '';

        $storeBannerImages = [];
        if (is_array($storebanners) && count($storebanners) > 0) {
            foreach ($storebanners as $key => $image) {
                if ($image === null) {
                    $storebanners[$key] = $key;
                } else {
                    $attachmentId = $this->productObj->getAttachmentId($image, 'image', false);
                    if ($attachmentId !== null) {
                        $storebanners[$key] = $attachmentId;
                    }
                }
            }
        }

        $storeInfo = getStoreInfoByUserId($userId);
        if ($storeInfo) {
            $existingSerializedIds = $storeInfo->banner_image_id;
            $bannerIds = unserialize($existingSerializedIds);
            if (isset($bannerIds)) {
                $bannerIds = [];
            }

            // dd($storebanners);
            if (is_array($bannerIds)) {
                // dd($storebanners);
                // Assuming $storeBannerImages is the array you want to merge
                if (is_array($storebanners) && !empty($storebanners)) {

                    $newIdsArray = array_merge($bannerIds, $storebanners);
                    // dd($newIdsArray);
                    $newSerializedIds = serialize($newIdsArray);
                    $data['banner_image_id'] = $newSerializedIds;
                } else {
                }
            } else {
                $newSerializedIds = serialize($storebanners);
                $data['banner_image_id'] = $newSerializedIds;
            }
        } else {
        }


        if (isset($postedData['store_image_id']) && $postedData['store_image_id'] != "") {
            $data['store_logo'] = $postedData['store_image_id'];
        }


        // dd($data);
        $this->_store->where('user_id', '=', $userId)->update($data);
    }


    public function updateStoreApi($postedData)
    {
        $userId = Auth::user()->user_id;
        $data = array();
        $data['user_id'] = $userId;
        $data['terms_condtions'] = isset($postedData['store_term_conditon']) ? $postedData['store_term_conditon'] : '';
        $data['store_website'] = isset($postedData['store_website']) ? $postedData['store_website'] : '';
        $data['store_country'] = $postedData['store_country'];
        $data['store_state'] = $postedData['store_state'];
        $data['store_city'] = isset($postedData['store_city']) ? $postedData['store_city'] : '';
        $data['street_address'] = isset($postedData['street_address']) ? $postedData['street_address'] : '';
        $data['store_name'] = isset($postedData['store_name']) ? $postedData['store_name'] : '';
        $store_slug = str_slug($postedData['store_name'], '-');
        $data['store_slug'] = isset($store_slug) ? $store_slug : '';
        $data['zip_code'] = isset($postedData['zip_code']) ? $postedData['zip_code'] : '';
        $data['contact_info'] = isset($postedData['store_phone']) ? $postedData['store_phone'] : '';
        $data['about_store'] = isset($postedData['about_store']) ? $postedData['about_store'] : '';
        $data['policies'] = isset($postedData['shop_policy_radio']) ? $postedData['shop_policy_radio'] : '';
        if (isset($postedData['store_image_id']) && $postedData['store_image_id'] != "") {
            $data['store_logo'] = $postedData['store_image_id'];
        }
        $updated = $this->_store->where('user_id', '=', $userId)->update($data);
        if ($updated > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getStoreByname($data)
    {
        $userId = Session::get('userData')['user_id'];
        $nameCount = $this->_store->where('store_name', '=', $data['store_name'])
            ->where('user_id', '!=', $userId)
            ->count();
        if ($nameCount > 0) {

            return false;
        } else {
            return true;
        }
    }

    public function getStoreBynameApi($data)
    {
        // if (!is_array($data) || !array_key_exists('user_id', $data)) {
        //     die('called');
        //     // Handle the case where $data is not an array or 'store_name' key is missing
        //     return false;
        // }

        
        $nameCount = $this->_store->where('user_id', '=', $data)->count();
        if ($nameCount > 0) {
            return true; // Store with the same name for this user exists
        } else {
            return false; // Store with the same name for this user does not exist
        }
    }

    public function getStoreDataByUserid()
    {
        $userId = Session::get('userData')['user_id'];
        $storeData = $this->_store->where('user_id', '=', $userId)->first();
        if (!empty($storeData)) {
            return $storeData->toArray();
        } else {
            return false;
        }
    }

    public function getProductsBystoreID($data)
    {
        $userId = Session::get('userData')['user_id'];
        $storeUserData = $this->_store->select('store_id')->where('user_id', '=', $userId)->first();

        if (!empty($storeUserData)) {
            $order = 'DESC';
            if (isset($data['order']) && $data['order'] != '') {
                $order = $data['order'];
            }
            if (isset($data['category']) && $data['category'] != '' && $data['category'] != 'All') {

                $category = DB::table('categories')->where('category_slug', $data['category'])->value('id');
                $query = DB::table('product_category')
                    ->join('products', 'products.product_id', '=', 'product_category.product_id')
                    ->where('product_category.category_id', '=', $category)
                    ->select('products.product_id', 'products.user_id', 'products.title', 'products.slug', 'status', 'products.price', 'products.quantity', 'products.created_at', 'products.product_type', 'products.description', 'products.short_description', 'products.feature_Image', 'products.is_feature', 'products.on_campaign', 'products.sales', 'products.current_stock')
                    ->where('products.store_id', '=', $storeUserData->store_id)
                    ->where('products.status', '!=', '2')
                    ->orderBy('created_at', $order)
                    ->paginate(15);
                $productData = $query;
            } else {
                $query = DB::table('products')->select('product_id', 'slug', 'status', 'user_id', 'title', 'price', 'quantity', 'created_at', 'description', 'short_description', 'is_feature', 'feature_Image', 'on_campaign', 'product_type', 'sales', 'current_stock')->where('products.status', '!=', '2')->where('store_id', '=', $storeUserData->store_id)
                    ->orderBy('created_at', $order);
                if (isset($data['campaign']) && $data['campaign'] != '') {
                    $query->where('on_campaign', 1);
                }
                if (isset($data['onsale']) && $data['onsale'] != '') {
                    // exit("here");
                    $query->where('is_flash_sale', 1);
                }

                $productData = $query->paginate(15);
            }
            //$productData->get();

            if (!empty($productData)) {
                return $productData;
            }
        } else {
            return false;
        }
    }

    public function getProductsByStore($storeId, $data)
    {
        //DB::connection()->enableQueryLog();
        //print_rr($data); exit;
        $query = DB::table('products')->join('stores', 'products.store_id', '=', 'stores.store_id');
        $query->where('products.status', '!=', '2');
        $query->where('products.store_id', '=', $storeId);
        $query->orderBy('created_at', 'DESC');
        if (isset($data['search_products'])) {
            $query->where('title', 'like', '%' . $data['search_products'] . '%');
        }
        if (isset($data['status'])) {
            if ($data['status'] == 'pending') {
                $query->where('status', '3');
            }
            if ($data['status'] == 'active') {
                $query->where('status', '1');
            }
            if ($data['status'] == 'approve') {
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
            if ($data['status'] == 'draft') {
                $query->where('status', '5');
            }
        }
        $query->select(
            'products.product_id',
            'products.slug',
            'products.status',
            'products.user_id',
            'products.title',
            'products.price',
            'products.on_sale',
            'products.sale_price',
            'products.discount_percentage',
            'products.quantity',
            'products.created_at',
            'products.description',
            'products.short_description',
            'products.is_feature',
            'products.feature_Image',
            'products.product_type',
            'products.sales',
            'products.current_stock',
            'products.user_revenue',
            'products.is_feature',
            'products.feature_status',
            'stores.store_status'
        );
        if (!empty($query)) {
            return $productData = $query->paginate(15);
        } else {
            return false;
        }
    }

    public function getStoreCamapingProducts()
    {
        $userId = getCurrentUserId();
        $storeData = getStoreDataByUserId($userId, $status = "1");
        $storeId = !empty($storeData->store_id) ? $storeData->store_id : "";
        if (!empty($storeId)) {
            $campQuery = DB::table('store_campaigns')->where('store_id', $storeId)->where('status', 'active')->first();
            if (!empty($campQuery)) {
                $query = DB::table('products')->where('product_id', $campQuery->product_id)->first();
                if (!empty($query)) {

                    return $query;
                }
            }
        } else {
            return false;
        }
    }

    public function getflashProduct($storeId)
    {
        $campQuery = getCampaignIdByStoreID($storeId);
        if (!empty($campQuery)) {
            $producId = $campQuery->product_id;
            $query = DB::table('products')
                ->select('products.product_id', 'products.slug', 'products.store_id', 'products.status', 'products.user_id', 'products.title', 'products.price', 'products.quantity', 'products.created_at', 'products.description', 'products.short_description', 'products.is_feature', 'products.feature_Image', 'products.product_type', 'products.sales', 'products.current_stock', 'products.user_revenue')
                ->where('product_id', $producId)
                ->where('flashsale_status', 'choosen')->first();

            return $query;
        } else {
            return false;
        }
    }

    public function getOnflashSaleProducts()
    {
        $userId = getCurrentUserId();
        $storeData = getStoreDataByUserId($userId);
        $storeId = $storeData->store_id;
        $flashQuery = DB::table('store_campaigns')->where('status', 'active')->first();
    }

    public function getVariationByProductId($productId)
    {
        $query = ProductVariation::where('product_id', $productId)
            ->select('variation_id', 'attribute1', 'attribute2', 'attribute3', 'unit1', 'unit2', 'unit3', 'option1', 'option2', 'option3', 'price', 'current_stock', 'sku', 'visibility')
            ->where('visibilty', 1);
        if (!empty($query)) {
            return $query->toArray();
        } else {
            return false;
        }
    }

    public function delProductById($propductId)
    {
        $product = $this->_product->where('product_id', '=', $propductId)->first();
        if($product){
            $product->status = '2';
            $product->save();
            $product->unsearchable();
        }
    }

    public function duplicateProductByid($productId)
    {
        $this->productObj->duplicateProductById($productId);
    }

    public function ChangeStatusByid($product_id, $product_status)
    {
        if ($product_id != '' && $product_status != '') {
            $userid = getCurrentUserId();
            if ($product_status == 'Activate') {
                $update = product::where('product_id', $product_id)->first();
                if($update){
                    $update->status = '1';
                    $update->save();
                }
                    
            }
            if ($product_status == 'Deactivate') {
                $update = product::where('product_id', $product_id)->first();
                    if($update){
                        $update->status = '0';
                        $update->save();
                        $update->unsearchable();
                    }
            }
            if ($product_status == 'Restore') {
                $update = product::where('product_id', $product_id)
                    ->update(['status' => '1']);
            }
            if ($product_status == 'Approve') {

                $update = product::where('product_id', $product_id)->update(['status' => '1']);

                $subject = "Great news! Your Product is now active. Click here to view it.";
                $description = "Great news! Your Product is now active. Click here to view it.";
                addNotifications($userid,  $userid, $subject, $description, URL::to('/store-products'));
            }
            if ($product_status == 'Reject') {
                $update = product::where('product_id', $product_id)
                    ->update(['status' => '4']);
                $subject = "Unfortunately, [product name] has been rejected by the administrator. Click here for more info";
                $description = "Unfortunately, [product name] has been rejected by the administrator. Click here for more info";
                addNotifications($userid,  $userid, $subject, $description, URL::to('/store-products'));
            }
            return $update;
        }
    }

    public function getStoreProducts($id, $params)
    {
        $productAccessObj = new Productaccess();
        $limit = 12;
        $page = 0;
        $values = $productAccessObj->getAllValues();
        if (isset($params['page']) && $params['page'] != '') {
            $page = $params['page'] - 1;
        }
        if (empty($params) || ($page > 0 && count($params) == 2)) {
            $storeId = base64_decode($id);
            $productData = DB::table('products')
                ->Leftjoin('reviews', 'products.product_id', '=', 'reviews.product_id')
                ->select('products.product_id', 'products.slug', 'products.is_feature', 'products.status', 'products.user_id', 'products.title', 'products.store_id', 'products.price', 'products.salePrice', 'products.quantity', 'products.created_at', 'products.feature_Image')
                ->where('products.store_id', '=', $storeId)
                ->where('products.status', '=', '1')
                ->groupBy('products.product_id')
                ->offset($page * $limit)
                ->limit($limit)
                ->orderBy('products.product_id', 'DESC');
            $query = $productData->get();
            $totalProduct = DB::table('products')
                ->Leftjoin('reviews', 'products.product_id', '=', 'reviews.product_id')
                ->where('products.store_id', '=', $storeId)
                //->where('products.productType', '=', 'product')
                ->where('products.status', '=', '1')
                ->groupBy('products.product_id')
                ->orderBy('products.product_id', 'DESC')
                ->get()
                ->count();
            $totalPage = ceil($totalProduct / $limit);

            if (!empty($productData)) {
                return array('results' => $query, 'values' => $values, 'page' => $totalPage, 'totalresults' => $totalProduct);
            }
        } else {
            /*
             * search filter resutls
             */
            $child_categories = isset($params['child_categories']) ? $params['child_categories'] : array();
            $keyword = isset($params['keyword']) ? $params['keyword'] : '';
            $child_categories_sub = isset($params['child_categories_sub']) ? $params['child_categories_sub'] : array();
            $child_brands = isset($params['child_brand']) ? $params['child_brand'] : '';
            $array_ids = array();
            if (isset($params['parent_category']) && $params['parent_category'] != '') {
                $category = DB::table('categories')->where('category_slug', $params['parent_category'])->value('id');
                $values = $productAccessObj->getAllValues($category);
                $parent_cat = $category;
                if ($child_categories != '' && count($child_categories) > 0) {
                    /*
                     * array of child categories ids
                     */
                    $array1 = array();
                    $category = DB::table('categories')->select('id')->whereIn('category_slug', $child_categories)->get();
                    if (isset($category[0])) {
                        $values = $productAccessObj->getAllValues($category[0]->id);
                    }

                    $new_arr = array();
                    /*
                     * fetching child categoires sub child categories
                     */
                    foreach ($category as $row) {
                        array_push($array1, $row->id);
                    }
                    foreach ($array1 as $id) {
                        $child_ids = getChildsCategoriesIds($id);
                        foreach ($child_ids as $key => $id) {
                            array_push($new_arr, $key);
                        }
                    }
                    /*
                     * child categories and their child categories
                     */
                    $array_ids = array_merge($new_arr, $array1);
                }
                if ($child_categories_sub != '' && count($child_categories_sub) > 0) {
                    /*
                     * array of sub child categories ids
                     */
                    $array_ids = array();
                    $category = DB::table('categories')->select('id')->whereIn('category_slug', $child_categories_sub)->get();
                    foreach ($category as $row) {
                        array_push($array_ids, $row->id);
                    }
                }
                if (count($array_ids) > 0) {
                    /*
                     * query if child or sub child categories are available
                     */
                    $query = DB::table('product_category')
                        ->join('products', 'products.product_id', '=', 'product_category.product_id')
                        ->whereIn('product_category.category_id', $array_ids)
                        ->groupBy('product_category.product_id');
                } else {
                    /*
                     * query if childs or  sub childs are not selected in query string 
                     */
                    $categories_child_sub_child = array();
                    /*
                     * fetching parent category's child categories
                     */
                    $child_ids = getChildsCategoriesIds($parent_cat);

                    foreach ($child_ids as $key => $id) {
                        array_push($categories_child_sub_child, $key);
                    }
                    $arra_sub_sub = array();
                    /*
                     * fetching child category's sub child categories
                     */
                    foreach ($categories_child_sub_child as $id) {
                        $child_ids = getChildsCategoriesIds($id);
                        foreach ($child_ids as $key => $id) {
                            array_push($arra_sub_sub, $key);
                        }
                    }
                    /*
                     * geting all categories (parent,child,sub child)
                     */
                    array_push($categories_child_sub_child, $category);
                    $new_array = array_merge($categories_child_sub_child, $arra_sub_sub);

                    // die();
                    $query = DB::table('product_category')
                        ->join('products', 'products.product_id', '=', 'product_category.product_id')
                        ->whereIn('product_category.category_id', $new_array)
                        ->groupBy('product_category.product_id');
                }
            } else {
                /*
                 * query if no categoryt is selected
                 */
            }
            if (isset($keyword) && $keyword != '') {
                /// shop_name
                $query->where('products.title', 'like', $keyword);
            }
            /*
             *  Brand filert in search
             */
            if (isset($params['brand']) && $params['brand'] != '') {
                $brand = DB::table('brands')->where('brand_name', $params['brand'])->value('id');
                $query->join('brands', 'products.brand_id', '=', 'brands.id')
                    ->where('brands.id', '=', $brand);
            }
            /*
             * Values filter in search
             */
            if (isset($params['value_id']) && count($params['value_id']) > 0) {

                $query->join('product_values', 'products.product_id', '=', 'product_values.product_id')
                    ->wherein('product_values.value_id', $params['value_id']);
            }

            /*
             * min and max price filter
             */
            if (isset($params['price_min']) && isset($params['price_max']) && $params['price_min'] != '' && $params['price_max'] != '') {
                $query->whereBetween('products.price', [$params['price_min'], $params['price_max']]);
            }
            /*
             * when only min price is available
             */
            if (isset($params['price_min']) && !isset($params['price_max'])) {
                $query->where('products.price', '>=', $params['price_min']);
            }
            /*
             * when only max price is available
             */
            if (isset($params['price_max']) && !isset($params['price_min'])) {
                $query->where('products.price', '<=', $params['price_max']);
            }
            $query2 = $query;
            $productCount = $query2->orderBy('products.created_at', 'DESC')
                ->where('status', '=', '1')
                //->where('products.productType', '=', 'product')
                ->get()
                ->count();
            $totalPage = ceil($productCount / $limit);
            // echo $totalPage;
            $query->orderBy('products.created_at', 'DESC')
                ->OFFSET($page * $limit)
                ->limit($limit)
                ->select('*')
                ->where('status', '=', '1')
                ->where('status', '=', '1');
            //   echo $query->toSql();
            $peoductData = $query->get();

            $arr = array('results' => $peoductData, 'values' => $values, 'page' => $totalPage, 'totalresults' => $productCount);

            return $arr;
        }
    }

    //            decode = no need to base 64 decode id 
    public function countStoreOrders($storeId, $decode = null)
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $count = DB::table('order_items')
            ->where('order_items.store_id', $storeId)
            ->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }

    //            decode = no need to base 64 decode id 
    public function getStoreInfoById($storeId, $decode = null)
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $storeData = $this->_store->select('store_id', 'shop_name', 'store_logo', 'created_at')->where('store_id', '=', $storeId)->first();
        if (!empty($storeData)) {
            return $storeData->toArray();
        } else {
            return false;
        }
    }

    //            decode = no need to base 64 decode id 
    public function getStoreProductCount($storeId, $decode = null)
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $count = $this->_product->where('store_id', '=', $storeId)->where('status', '=', '1')->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }

    public function getStoreProductOutOfStockCount($storeId, $decode = null)
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $count = $this->_product->where('store_id', '=', $storeId)->where('current_stock', '<=', '0')->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }
    public function getStoreProductOutOfStockCountApi($storeId, $decode = null,$status='')
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $count = $this->_product->where('store_id', '=', $storeId)->where('current_stock', '<=', '0')->where('status', "$status")->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }
    public function getStoreProductCountstore($storeId, $decode = null, $status = '')
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $query = $this->_product->where('store_id', '=', $storeId);
       
            $query->where('status', '!=' , '2');
        
        $count = $query->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }
    public function getStoreProductCountstoreApi($storeId, $decode = null, $status = '')
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $query = $this->_product->where('store_id', '=', $storeId);
        if ($status != "" && $status == '1') {
            $query->where('status', "$status")->where('current_stock','>',0);
        }elseif($status != "" && $status == '3'){
            $query->where('status', "$status");
        }
        $count = $query->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }
    public function countTotalStoreOrders($storeId, $status, $current = Null)
    {
        $currentMonth = date('m');
        $ordersCount = $this->_storeorders->where('store_id', $storeId)
            ->where('order_status', '=', $status);
        if ($current != Null) {
            $ordersCount->whereRaw('MONTH(created_at) = ?', [$currentMonth]);
        }
        $totalResult = $ordersCount->count();
        return $totalResult;
    }

    public function countAllStoreOrders($storeId, $status)
    {
        $ordersCount = $this->_storeorders->where('store_id', $storeId);
           if(isset($status) && $status != ''){
            $ordersCount->where('order_status', '=', $status);
           }

        $totalResult = $ordersCount->count();
        return $totalResult;
    }

    public function getStoreReviews($storeId, $decode = null)
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $storeRating = DB::table('products')->select('products.product_id', 'products.user_id', 'reviews.rating', 'users.name', 'reviews.comment', 'users.user_avatar', 'reviews.created_at')->join('reviews', 'products.product_id', '=', 'reviews.product_id')->join('users', 'products.user_id', '=', 'users.user_id')
            ->where('products.store_id', '=', $storeId)->where('products.status', '=', '1')
            ->orderBy('review_id', 'DESC')->paginate(10);
        if (!empty($storeRating)) {
            return $this->objToArray($storeRating);
        } else {
            return false;
        }
    }

    public function storeStatusChange($status)
    {

        $store_status = $status['store_status'];
        $userId = Session::get('userData')['user_id'];
        $storeData = DB::table('stores')->where('user_id', $userId)->first();
        $store_id = $storeData->store_id;

        if ($store_status == 2 && $storeData->super_status == 1) {

            DB::table('products')->where('user_id', $userId)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '1']);
            $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();

            $response = DB::table('stores')
                ->where('store_id', $store_id)
                ->update([
                    'store_status' => '2'
                ]);
            if ($response > 0) {
                return [
                    'status' => true,
                    'message' => 'Your store is activated',

                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Your store is not activate because super admin deactivated your store',

                ];
            }
        } elseif ($store_status == 0) {
            DB::table('products')->where('user_id', $userId)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '0']);
            $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();

            $response = DB::table('stores')
                ->where('store_id', $store_id)
                ->update([
                    'store_status' => '0',
                ]);

            if ($response > 0) {
                return [
                    'status' => true,
                    'message' => 'your store is deactivated',

                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'your store is not deactivate',

                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'Your store is restricted',

            ];
        }
    }

    public function storeStatusChangeApi($status)
    {

        $store_status = $status['store_status'];
        $userId = Auth::user()->user_id;

        $storeData = DB::table('stores')->where('user_id', $userId)->first();
        if(isset($storeData)){
            $totalProducts = DB::table('products')->where('store_id', $storeData->store_id)->whereNotIn('status', ['2'])->count();
            if($storeData->is_completed == 1 && $storeData->stripe == 1 && $totalProducts >= 10){
               
            
        $store_id = $storeData->store_id;
        if ($store_status == 2 && $storeData->super_status == 1) {

            DB::table('products')->where('user_id', $userId)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '1']);
            $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();

            $response = DB::table('stores')
                ->where('store_id', $store_id)
                ->update([
                    'store_status' => '2'
                ]);
            // echo 'Data: <pre>' .print_r($response,true). '</pre>'; die();
            if ($response > 0) {
                return [
                    'status' => true,
                    'message' => 'Your store is activated',

                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'You have already activated your store.',

                ];
            }
        } elseif ($store_status == 0) {
            DB::table('products')->where('user_id', $userId)->whereNotIn('status', ['2', '3', '5'])->update(['status' => '0']);
            $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();

            $response = DB::table('stores')
                ->where('store_id', $store_id)
                ->update([
                    'store_status' => '0',
                ]);

            if ($response > 0) {
                return [
                    'status' => true,
                    'message' => 'your store is deactivated',

                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'You have already deactivated your store.',

                ];
            }
        } elseif ($store_status == 0 && $storeData->super_status == 0) {
            return [
                'status' => false,
                'message' => 'Your store is restricted',

            ];
        } elseif($store_status == 2 && $storeData->super_status == 0) {
            return [
                'status' => false,
                'message' => 'Your store is restricted',

            ];
        }
    }else{
        return [
            'status' => false,
            'message' => 'Complete you store setup to activate',

        ];
    }
    }
    }

    public function changeStoreStatus($status = '', $user_id = '', $store = 'false')
    {
        // dd($status);
        $status = $status['store_status'];
        if ($user_id != '' && $status != '' && $store == 'true') {
            DB::table('products')->where('user_id', $user_id)->where('status', '!=', '2')->update(['status' => $status]);
            $products = DB::table('products')->select('product_id')->where('user_id', $user_id)->get()->toArray();
            $this->_store->where('user_id', $user_id)->update(['store_status' => "$status"]);
            return false;
        }
        if ($user_id != '' && $status != '') {
            DB::table('products')->where('user_id', $user_id)->where('status', '!=', '2')->update(['status' => $status]);
            $products = DB::table('products')->select('product_id')->where('user_id', $user_id)->get()->toArray();
            return false;
        }
        $userId = Session::get('userData')['user_id'];
        $storeStatus = $this->_store->select('store_status', 'store_id', 'super_status')->where('user_id', $userId)->first();
        if ($storeStatus->store_status == '0' || $storeStatus->store_status == 0) {


            $status = '2';
            DB::table('products')->where('user_id', $userId)->whereNotIn('status', ['2', '3'])->update(['status' => '1']);
            $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();
        } elseif ($storeStatus->store_status == '2' || $storeStatus->store_status == 2) {
            $status = '0';
            DB::table('products')->where('user_id', $userId)->whereNotIn('status', ['2', '3'])->update(['status' => '0']);
            $products = DB::table('products')->select('product_id')->where('user_id', $userId)->get()->toArray();
        }
        if ($storeStatus) {
            $storeId = $storeStatus->store_id;
        }
        $this->_store->where('user_id', $userId)
            ->update(['store_status' => "$status"]);
        //add campaign section

        //Hide Compaign section when user store active 

        // $storeCampaign = DB::table('store_campaigns')->where('store_id', $storeId)->count();
        // if ($storeCampaign <= 0) {
        //     $campObj = new Campaignaccess();
        //     $campObj->saveCampaign();
        // }
    }

    public function getFeaturePricing()
    {
        $featureObj = new FeaturePrices();
        $query = $featureObj->where('feature_price_id', '=', 1)->first();
        if (!empty($query)) {
            return $query->toArray();
        } else {
            return false;
        }
    }

    public function makeProductFeature($productId)
    {
        $featureenddate = date('Y-m-d', strtotime('+1 month'));
        $featurestartdate = date('Y-m-d');
        $feature = $this->getFeaturePricing();
        $featurePrice = $feature['price'];
        //        $data = array();
        //        $data['is_feature'] = "1";
        //        $data['feature_startdate'] = $featurestartdate;
        //        $data['feature_enddate'] = $featureenddate;
        //        $data['feature_price'] = $featurePrice;
        //$this->_product->where('product_id', '=', $productId)->update(['is_feature' => "1"]);
        $this->_product->where('product_id', '=', $productId)->update([
            'feature_startdate' => $featureenddate,
            'description' => $featurestartdate,
            'is_feature' => "1",
            'feature_price' => $featurePrice,
        ]);
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            $userAvailableCredits = $userData[0]->user_credits;
            $remainCredits = (int) $userAvailableCredits - (int) $featurePrice;

            $this->_user->where('user_id', '=', $userid)->update(['user_credits' => $remainCredits]);
        }
    }

    public function getStoreIcons()
    {
        $query = $this->storeicons->where('is_selected', 0)->paginate(50);
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function SearchStores($string)
    {
        return $results = $this->_store->where('store_name', 'LIKE', '%' . $string . '%')->where('store_status', '2')->select('store_name', 'store_slug', 'store_logo', 'store_id', 'wallet_address', 'stripe', 'user_id')->get()->toArray();
    }

    public function seacrhStoreIcons($name)
    {
        $query = $this->storeicons->where('is_selected', 0)->where('name', 'like', $name . '%')->paginate(100);
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function seacrhStoreIconsauto($name)
    {
        $query = $this->storeicons->select('name')->where('is_selected', 0)->where('name', 'like', '%' . $name . '%')->limit(10)->get();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function selectStoreIcon($data)
    {
        $storeIcon = $data['icon_selected'];
        if (Session::has('userData')) {
            $userId = Session::get('userData')['user_id'];
            $this->_store->where('user_id', $userId)->update(['store_icon' => $storeIcon]);
            $this->storeicons->where('icon_id', $storeIcon)->update(['is_selected' => 1]);
            return true;
        } else {
            return false;
        }
    }

    public function getvariableProductsInfo($data)
    {
        $query = DB::table('product_variation')
            ->select('variation_id', 'product_id', 'attribute1', 'attribute1', 'attribute2', 'attribute3', 'option1', 'option2', 'option3', 'price', 'current_stock', 'sku', 'sales')
            ->where('product_id', $data['product_id'])->get();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function seacrhProductsauto($data) {

        $userId = Session::get('userData')['user_id'];
        $StoreData = getStoreDataByUserId($userId);
        $storeId = !empty($StoreData->store_id) ? $StoreData->store_id : "";
        $query = DB::table('products')->where('title', 'like', '%' . $data['term'] . '%');
        if (isset($data['role']) && $data['role'] == "storeowner") {
            $query->where('store_id', $storeId);
        }
        $res = $query->get();
        if (!empty($res)) {
            return $res;
        } else {
            return false;
        }
    }

    public function getBrandsOfWeek()
    {
        $query = $this->_store->select('store_logo', 'store_slug', 'wallet_address')->where('top_seller', 1)->get();
        if ($query) {
            return $query;
        } else {
            return false;
        }
    }
    public function getBrandsOfWeekApi($perPage = 10, $limit)
    {
        $query = $this->_store->select('store_logo', 'store_slug', 'wallet_address')->where('top_seller', 1)->limit($limit)->paginate($perPage)->toArray();

        return $query;
    }
    public function getBrands($orderBy = 'created_at', $orderType = 'asc', $limit = 10, $shuffle = false)
    {
        if ($shuffle) {

            $query = $this->_store->where('top_seller', 1)->orderBy($orderBy, $orderType)->limit($limit)->get()->shuffle();
        } else {

            $query = $this->_store->where('top_seller', 1)->orderBy($orderBy, $orderType)->limit($limit)->get();
        }
        if ($query) {
            return $query;
        } else {
            return false;
        }
    }

    public function objToArray($arr)
    {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    /**
     *  Function for upload Image
     *
     */
    public function uploadProcessStoreImage($productsimages)
    {
        $productGallery = array();
        if ($productsimages) {
            if (count($productsimages) > 0) {
                $destinationPath = public_path() . '/uploads/temp';
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }
                foreach ($productsimages as $image) {
                    $OriginalfileName = $image->getClientOriginalName();
                    $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                    $fileExtension = $image->getClientOriginalExtension();
                    $data = getimagesize($image);
                    $width = $data[0];
                    $height = $data[1];
                    if ($width < 1180 || $height < 350) {
                        $status = FALSE;
                    } else {
                        $status = true;
                    }
                    $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                    $image->move($destinationPath, $fileName);
                    $productGallery['fileName'][] = $fileName;
                    $productGallery['validate'][] = $status;
                }
            }
        }
        return $productGallery;
    }
    public function generateRandomString($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
