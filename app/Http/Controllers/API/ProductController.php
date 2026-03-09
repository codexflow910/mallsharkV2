<?php

namespace App\Http\Controllers\API;

use File;
use Image;
use Shippo;
use Session;
use Redirect;
use Carbon\Carbon;
use stdClass;
use Illuminate\Http\Request;
use App\Http\Controllers\UspsT;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Cart\Cartaccess;
use App\Repositories\Herd\Herdaccess;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ReviewsController;
use App\Repositories\Filters\Filtersaccess;
use App\Repositories\Product\Productaccess;
use App\Repositories\Campaign\Campaignaccess;
use App\Repositories\Location\LocationAccess;
use App\Http\Controllers\ElasticSearchController;
use Packages\Product\Models\V2\{Product, ProductCategory};
use Packages\Review\Models\V2\Review;
use Packages\Core\Models\V2\Category;
use Packages\Store\Models\V2\Store;
use  Packages\Order\Models\V2\StoreOrder;
use Packages\Auth\Models\V2\User;
use App\Repositories\Favourites\Favouritesaccess;
use App\Repositories\Store\Storeaccess;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public $_request;
    private $_storeobj;
    private $productObj;
    private $reviews;
    private $filter_obj;
    private $shiprate;
    private $campaignObj;
    private $herdObj;
    private $_esearch;
    private $_locationobj;
    private $_favobj;

    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->_storeobj = new Storeaccess();
        $this->productObj = new Productaccess();
        $this->reviews = new ReviewsController();
        $this->_locationobj = new LocationAccess();
        $this->shiprate = new UspsT();
        $this->campaignObj = new Campaignaccess();
        $this->herdObj = new Herdaccess();
        $this->_esearch = new ElasticSearchController();
        $this->_favobj = new Favouritesaccess();
    }

    public function productVarient()
    {
        // if ($this->_request->ajax()) {
        $data = array();
        $arr = array();
        $edit = false;
        $variationData = array();
        $varPostedArray = array();
        $postedData = $this->_request->all();
        // dd($postedData);
        // echo 'Data: <pre>' .print_r($postedData,true). '</pre>'; die();

        // exit;
        if (isset($postedData['product_id']) && $postedData['product_id']) {
            $query = DB::table('product_variation')->select('*')->where('product_id', $postedData['product_id'])
                ->get();
            $variationData = objToArray($query);
        }
        if (isset($postedData['variation_options']) && $postedData['variation_options'] != "") {
            // return $postedData;
            $data['variation_options'] = $this->combinations($postedData['variation_options']);
            $keys = ['attribute1', 'attribute2', 'attribute3'];
            // dd($data['variation_options']);
            $objects = $this->convertToObjects($data['variation_options'], $keys);
        }
        if (isset($postedData['product_id'])) {
            $edit = true;
            return response()->json(['status' => true, 'data' => $data, 'postedData' => $postedData, 'variationData' => $variationData, 'edit' => $edit, 'product_id' => $postedData['product_id']]);
        } else {

            return response()->json(['status' => true, 'data' => $objects, 'postedData' => $postedData, 'variationData' => $variationData, 'edit' => $edit]);
        }
        // }
    }

    public function convertToObjects($data, $keys)
    {
        // Check if $data is a multidimensional array

        if (count($data) > 0 && is_array($data[0])) {
            $variations = [];
            foreach ($data as $item) {
                $variation = new stdClass();
                // If the sub-array has only one value, use it as 'id'
                if (count($item) == 1) {
                    $variation->attribute1 = $item[0];
                } else {
                    foreach ($keys as $index => $key) {
                        // Use specified keys as property names
                        if (isset($item[$index])) {
                            $variation->$key = $item[$index];
                        }
                    }
                }
                $variations[] = $variation;
            }
            return $variations;
        } else {
            // If $data is a single array
            //    dd(12);
            $variations = [];
            // If the array has only one value, use it as specified keys
            // dd($data);
            if (count($data) == 1 && count($keys) > 0) {
                foreach ($data as $index => $value) {
                    $key = $keys[$index];
                    $variation = new stdClass();
                    $variation->$key = $data[0];
                    $variations[] = $variation;
                }
            } elseif (count($data) > 1) {
                // dd($data);
                foreach ($data as $index => $value) {
                    $key = $keys[0];
                    $variation = new stdClass();
                    $variation->$key = $value;
                    $variations[] = $variation;
                }
            }
            // dd($variation);
            return $variations;
        }
    }

    // Test with single array

    public function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = $this->combinations($arrays, $i + 1);
        $result = array();
        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }
    public function uploadProductImageRequest()
    {
        $result = array();
        if ($this->_request->hasFile('productImage')) {
            $imageObj = $this->_request->file('productImage');
        } else {
            $imageObj = '';
        }
        return response()->json([
            'status' => true,
            'data' => $this->productObj->uploadProcessProductImage($imageObj)
        ]);
    }
    public function deleteProductImage()
    {
        $result = null;
        if ($this->_request->isMethod('post')) {
            $imageName = $this->_request->input();
            $product_id = $this->_request->input('edit_product_id');
            // return $imageName;
            // Create data array
            $data = [
                'imageId' => $imageName['imageName'],
                'productId' => $product_id
            ];
    
            // Check conditions and perform actions
            if ((isset($imageName['imageName']) && is_numeric($imageName['imageName'])) && isset($product_id)) {
                // return 12;
                // This block will execute if $imageName is numeric and $product_id is set
                // return $data['imageId'];
                $deleteImage = $this->productObj->deleteProductImageRequest($data);
                // return $deleteImage;
                // Check if the operation was successful before returning
                if ($deleteImage) {
                    return response()->json([
                        'status' => true,
                        'message' => 'image deleted successfully'
                    ]);
                } else {
                    // Handle the case where the operation failed
                    return "Error deleting product image";
                }
            } else {
                // This block will execute if the conditions are not met
                $result = $this->productObj->deleteProductTemRequest($imageName);
                return $result;
            }
            
        }
    
       
    }
    
    public function loadVideoThumb()
    {
        if ($this->_request->isMethod('post')) {
            $url = $this->_request->input('url');
            $this->url = $url;
            $this->autoplay = 0;
            return $this->renderEmbed();
        } else {
            return $this->renderEmbed();
        }
    }
    public function shippingOptionsPopup()
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'shippingProfileId' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $shippingProfileId = $this->_request->input('shippingProfileId');
        try {
            $param = getShippingOptionMethodById($shippingProfileId);
            if (!empty($param)) {
                return response()->json([
                    'status' => true,
                    'message' => "Shipping Option Fetch Successfully",
                    'param ' => $param
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "There are no shipping available",
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function saveShippingProfile()
    {

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'origin_zipcode' => 'required',
                'shipping_address1' => 'required',
                'shipping_country_id' => 'required|numeric|exists:countries,id',
                'store_id' => 'required|numeric|exists:stores,store_id',
                'shipping_state_id' => 'required|numeric|exists:states,state_id',
                'shipping_city' => 'required|regex:/^[\pL\s]+$/u',
                'shipping_type' => 'required|in:service,fixed,free',
                'ship_service' => [
                    'required_if:shipping_type,service',
                    Rule::in(['Priority', 'Express', 'First', 'ParcelSelect']),
                ],
                'flat_shipping_rate' => 'required_if:shipping_type,fixed',
                'profile_name' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $postedData = $this->_request->all();
        try {
            $result =  $this->productObj->saveShippingProfileApi($postedData);
            if ($result['status']) {
                return response()->json([
                    'status' => true,
                    'message' => "Shipping Save Successfully",

                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        // return View('product/shipping/previousShippingOption')->render();


    }

    public function updateShippingProfile()
    {
        // if ($this->_request->ajax()) {
        $postedData = $this->_request->all();
        if (isset($postedData['shipping_option_id']) && $postedData['shipping_option_id'] != '') {
            // return response()->json($postedData['flat_shipping_rate']);
            $updateProfile = $this->productObj->updateShippingProfileApi($postedData);
            // return response()->json($updateProfile);
            if ($updateProfile['status'] == true) {
                return response()->json([
                    'status' => true,
                    'message' => 'shipping profile updated',
                    'data' => $updateProfile['data']
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'something went wrong'
                ]);
            }
        } else {
            $saveProfile = $this->productObj->saveShippingProfileApi($postedData);
            if ($saveProfile['status'] == true) {
                return response()->json([
                    'status' => true,
                    'message' => 'shipping profile created',
                    'data' => $saveProfile['data']
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'profile name already exists'
                ]);
            }
        }
        // }
    }
    public function deleteShippingProfile()
    {
        try {
            $user_id = auth()->user()->user_id;
            $shipping_id = $this->_request->input('shipping_id');
            if ($shipping_id != '') {

                DB::table('shipping_options')->where('id', '=', $shipping_id)->where('user_id',$user_id)->update(['delete_status' => '0']);
                $freeShippingId = DB::table('shipping_options')->where('profile_name','Free Shipping')->first();
                if(isset($freeShippingId) && !is_null($freeShippingId)){
                    DB::table('products')->where('user_id',$user_id)->where('shipping_option_id',$shipping_id)->update(['shipping_option_id' => $freeShippingId->id]);
                }
                return [
                    'status' => true,
                    'message' => "Shipping Profile deleted",
                ];
            } else {
                return [
                    'status' => false,
                    'message' => "There are no Shipping Profile available"
                ];
            }
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' => $th->getMessage()
            ];
        }
    }
    public function addProduct()
    {
       
        // return 12;
        // dd($this->_request->all());
            
        if ($this->_request->isMethod('post')) {
            $productData = $this->_request->all();
            // dd($productData);
            // return response()->json($productData);
            if (isset($productData['product_type']) && $productData['product_type'] == 'virtual') {
                $productData['product_file'] = $this->_request->file('product_files');
            }
            // $files = $this->_request->file('product_files');
            // echo '$productData: <pre>' .print_r($productData,true). '</pre>'; die;
            // print_rr($productData); exit;
            // return response()->json([
            //     $productData['another_product_add']
            // ]);
                if($productData['another_product_add'] != 5){

                
                if($productData['previousShippingOptionsType'] == 'USPS'){
                    $rates = $this->getRates($productData);
                    
                    if($rates['status'] == false){
                        return response()->json([
                            'status' => false,
                            'message' => 'No matching dimensions were found. Please set the correct dimensions'
                        ]);
                    }else{
                        if(array_key_exists('quantity',$rates)){
                            $productData['maxquantitylimit'] = $rates['quantity'];

                        }
                    }
                }
            }
            $validate = $this->productObj->addStoreProductApi($productData);
            if ($validate['status'] == 'save') {
                if ($this->_request->another_product_add == 1) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Product added and sent for approval',
                        'data' => $validate['data']
                    ]);
                } elseif ($this->_request->another_product_add == 5) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Product added as draft',
                        'data' => $validate['data']
                    ]);
                } else {
                    return response()->json([
                        'status' => true,
                        'message' => 'Product added and sent for approval',
                        'data' => $validate['data']
                    ]);
                }
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Your product is saved in draft'
                ]);
            }
        } else {
            // $res = checkStoreIconSelected();
            // if ($res == false) {
            //     return redirect('select-store-icon');
            // }
            $variationAttributes = $this->productObj->getVariationAttributes();

            $parentCategory = $this->productObj->getAllCategory('default', 0);
            $viewParameter = array();
            $viewParameter['parentCategory'] = $parentCategory;
            $shippingProfile = $this->productObj->getShippingProfile();
            $userId = Session::get('userData')['user_id'];
            $store_data = getStoreDataByUserId($userId);
            // for condition filter pass id 1
            // $viewParameter['ProductCondition'] = $this->filter_obj->getFiltervaluesBYId('1');
            $viewParameter['variationAttributes'] = $variationAttributes;
            //// return View('product/addProduct', $viewParameter);
            ///   echo '--';die;
            // if ($this->isMobileDevice()) {
            //     return View('product/mobile/addEdit', $viewParameter, ['shippingProfile' => $shippingProfile, 'store_data' => $store_data]);
            // } else {
            return View('product/addEdit', $viewParameter, ['shippingProfile' => $shippingProfile, 'store_data' => $store_data]);
            //}
        }
    }
    // public function addProduct()
    // {


    //     $validateUser = Validator::make(
    //         $this->_request->all(),
    //         [
    //             'product_type' => 'required|in:simple,variant,virtual',
    //             'productCategory' => 'required|exists:categories,id',
    //             'productTitle' => 'required',
    //             'productImages' => 'required',
    //             'productPrice' => 'required',
    //             'productcurrentstock' => 'required',
    //             'productWeight' => 'required',
    //             'previousShippingOptions' => 'required',
    //         ]
    //     );
    //     if ($validateUser->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'validation error',
    //             'errors' => $validateUser->errors()
    //         ], 401);
    //     }

    //     if ($this->_request->isMethod('post')) {

    //         $productData = $this->_request->all();
    //         $result = array();
    //         if ($this->_request->has('productImages')) {
    //             $imageObj = $productData['productImages'];
    //             $result = $this->productObj->uploadProcessProductImage($imageObj);
    //             foreach ($result['fileName'] as $key => $img) {
    //                 $productData['productImages'][$key] = $img;
    //             }
    //         }

    //         if (isset($productData['product_type']) && $productData['product_type'] == 'virtual') {

    //             $result = array();
    //             if ($this->_request->has('product_files')) {
    //                 $product_files = $productData['product_files'];
    //                 $result = $this->productObj->uploadProcessProductImage($product_files);
    //                 foreach ($result['fileName'] as $key => $img) {
    //                     $productData['product_files'][$key] = $img;
    //                 }
    //             }
    //         }

    //         if (isset($productData['feature_image'])) {
    //             $result = array();
    //             $featureImage[] = $productData['feature_image'];
    //             $result = $this->productObj->uploadProcessProductImage($featureImage);
    //             foreach ($result['fileName'] as $key => $img) {
    //                 $productData['feature_image'] = $img;
    //             }
    //         }
    //         $validate = $this->productObj->addStoreProductApi($productData);
    //         if ($validate == 'save') {
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Success',
    //             ], 200);
    //         } else {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'validation error',
    //                 'errors' => 'exception'
    //             ], 500);
    //         }
    //     } else {

    //         $variationAttributes = $this->productObj->getVariationAttributes();
    //         $parentCategory = $this->productObj->getAllCategory('default', 0);
    //         $viewParameter = array();
    //         $viewParameter['parentCategory'] = $parentCategory;
    //         $shippingProfile = $this->productObj->getShippingProfile();
    //         $userId = Session::get('userData')['user_id'];
    //         $store_data = getStoreDataByUserId($userId);
    //         // for condition filter pass id 1
    //         // $viewParameter['ProductCondition'] = $this->filter_obj->getFiltervaluesBYId('1');
    //         $viewParameter['variationAttributes'] = $variationAttributes;
    //         //// return View('product/addProduct', $viewParameter);
    //         ///   echo '--';die;
    //         if ($this->isMobileDevice()) {
    //             return View('product/mobile/addEdit', $viewParameter, ['shippingProfile' => $shippingProfile, 'store_data' => $store_data]);
    //         } else {
    //             return View('product/addEdit', $viewParameter, ['shippingProfile' => $shippingProfile, 'store_data' => $store_data]);
    //         }
    //     }
    // }

    public function getProductFaqsById($productId)
    {
        return $this->productObj->getProductFaqsById($productId);
    }

    public function ProductDetail($slug , Request $request)
    {
        // dd(12);
        try {
            $user_id = $request->input('user_id') ?? null;
            // echo 'Data: <pre>' .print_r($slug,true). '</pre>'; die();
            $productDetail = $this->productObj->getProductDetailBySlugApi($slug,$user_id);
            // echo 'Data: <pre>' .print_r($productDetail,true). '</pre>'; die();
            if ($productDetail) {
               
                /*
                * Product Details page  
                */

                // $productDetail = $this->productObj->getProductDetailBySlugApi($slug);
                $productDetailArray = (array) $productDetail;
                $productdescription  = $productDetailArray['description'];
                // dd($productdescription);
                $productShortDescription  = $productDetailArray['short_description'];
                if ($productdescription && !empty($productdescription)) {
                    $prodesTemp = html_entity_decode(strip_tags($productdescription));
                    $productdescription = str_replace(array("\r", "\n"), '', $prodesTemp);
                    // dd($productdescription);
                    $productDetailArray['product_description'] = $productdescription;
                    unset($productDetailArray['description']);
                }

                if ($productShortDescription && !empty($productShortDescription)) {
                    $productShortDescriptionTemp = html_entity_decode(strip_tags($productShortDescription));
                    $productShortDescription = str_replace(array("\r", "\n"), '', $productShortDescriptionTemp);
                    $productDetailArray['product_short_description'] = $productShortDescription;
                    unset($productDetailArray['short_description']);
                   
                }

                $serializedData = $productDetailArray['gallery'];
                $galleryArray = [];
                if ($serializedData && !empty($serializedData)) {
                    $unserializedArray = unserialize($serializedData);

                    foreach ($unserializedArray as $value) {
                        $galleryImageInfo = [];

                        $path = getAttachmentUrlById((int) $value, 'product');
                        $galleryImageInfo = webPImages($path,'product');
                        if ($galleryImageInfo) {
                            $galleryArray[] = $galleryImageInfo;
                        }
                    }
                }
                // dd($productDetailArray);
                $path = getAttachmentUrlById($productDetailArray['feature_image'], 'product');
                $webpUrl = webPImages($path,'product');
                if ($webpUrl) {
                    $productDetailArray['feature_image_path'] = $webpUrl;
                }else{
                    $productDetailArray['feature_image_path'] = $path;
                }
                $productDetailArray['gallery_images'] = $galleryArray;
                
                /*
                * Variation Product Detail  
                */

                $variations = $this->productObj->getAllProductVariation($productDetailArray['product_id']);
                $variationsArray = [];
              
                if (count($variations) > 0) {
                    $stock = 0;
                    foreach ($variations as $value) {
                        // dd($value->current_stock);
                        $stock += $value->current_stock;
                        // dd($stock);
                        $variation = (array) $value; // Convert the object to an array
                        $path = getAttachmentUrlSmallById((int) $value->product_variation_image, 'small');
                        $webpUrl = webPImages($path,'small');
                        if ($webpUrl) {
                            $variation['product_variation_image'] = $webpUrl;
                        }else{
                            $variation['product_variation_image'] = $path;
                        }

                        if (!isset($variation['product_variation_image']) && is_null($variation['product_variation_image'])) {
                            $variation['product_variation_image'] = $productDetailArray['feature_image_path'];
                        }
                        $variationsArray[] = $variation;
                    }
                    $productDetailArray['current_stock'] = $stock;
                }
                /*
                * Product Category  
                */
                $productCategories = $this->productObj->getCateoriesOfProductsApi($productDetail['product_id']);
                $productCategoriesArray = [];
                if (count($productCategories) > 0) {

                    foreach ($productCategories as $category) {
                        $productCategory = (array) $category; // Convert the object to an array
                        $path = getAttachmentUrlById((int) $category['image_id'], 'media');
                         $webpUrl = webPImages($path,'media');
                        if ($webpUrl) {
                            $productCategory['category_image_path'] = $webpUrl;
                        }else{
                            $productCategory['category_image_path'] = $path;
                        }
                        $productCategoriesArray[] = $productCategory;
                    }
                }

                /*
                * Related Products   
                */

                $relatedProduct = $this->productObj->getRelatedProducts($productDetailArray['product_id'], $productDetailArray['store_id']);
                $relatedProductArray = [];
                if (count($relatedProduct) > 0) {

                    foreach ($relatedProduct as $prod) {
                        $relatedProducts = (array) $prod;
                        $path = getAttachmentUrlById((int) $prod->feature_image, 'media');
                        $webpUrl = webPImages($path,'media');
                        if ($webpUrl) {
                            $relatedProducts['feature_image_path'] = $webpUrl;
                        }else{
                            $relatedProducts['feature_image_path'] = $path;
                        }
                        $relatedProductArray[] = $relatedProducts;
                    }
                }


                $productTags = getTagsByProductId($productDetailArray['product_id']);
                if (isset($productTags)) {
                    $counterTags = count($productTags);
                    $tags = implode(',', $productTags);
                }

                /*
                * Store Information  
                */

                $storeInfo = getStoreInfoByIdApi($productDetail['store_id']);
                if (isset($storeInfo)) {

                    $storeInformation = (array) $storeInfo;
                    $storeInformation['store_thumbnail'] = getAttachmentUrlById((int) $storeInfo->store_logo, 'media');
                }
                // echo 'Data: <pre>' .print_r($storeInformation,true). '</pre>'; die();
                $productTags = getTagsByProductId($productDetailArray['product_id']);
                if (isset($productTags)) {
                    $counterTags = count($productTags);
                    $tags = implode(',', $productTags);
                }

                $reviewsProduct = $this->reviews->get_reviews($productDetailArray['product_id']);
                $reviewArray = [];
                foreach ($reviewsProduct as $review) {
                    $reviews = (array) $review;
                    $user_id = $review['user_id'];
                    $userInfo = getUserById($user_id);
                    $userName = $userInfo->name;
                    $reviews['user_name'] = $userName;
                    $reviewArray[] = $reviews;
                }
                $average = 0;
                if (isset($reviewsProduct) && count($reviewsProduct) > 0) {

                    $numer = 0;
                    $star1_count = 0;
                    $star2_count = 0;
                    $star3_count = 0;
                    $star4_count = 0;
                    $star5_count = 0;
                    foreach ($reviewsProduct as $rev) {
                        $numer += $rev['rating'];



                        if ($rev['rating'] == 1) {
                            $star1_count = $star1_count + 1;
                        }
                        if ($rev['rating'] == 2) {
                            $star2_count = $star2_count + 1;
                        }
                        if ($rev['rating'] == 3) {
                            $star3_count = $star3_count + 1;
                        }
                        if ($rev['rating'] == 4) {
                            $star4_count = $star4_count + 1;
                        }
                        if ($rev['rating'] == 5) {

                            $star5_count = $star5_count + 1;
                        }

                        $total = count($reviews);
                        $star1_count_percentage = round(($star1_count / $total) * 100);
                        $star2_count_percentage = round(($star2_count / $total) * 100);
                        $star3_count_percentage = round(($star3_count / $total) * 100);
                        $star4_count_percentage = round(($star4_count / $total) * 100);
                        $star5_count_percentage = round(($star5_count / $total) * 100);
                    }
                    $singleReview = [
                        'star1_count' => $star1_count,
                        'star2_count' => $star2_count,
                        'star3_count' => $star3_count,
                        'star4_count' => $star4_count,
                        'star5_count' => $star5_count,
                    ];
                    $avergeSingleReview = [
                        'star1_count_avg' => $star1_count_percentage,
                        'star2_count_avg' => $star2_count_percentage,
                        'star3_count_avg' => $star3_count_percentage,
                        'star4_count_avg' => $star4_count_percentage,
                        'star5_count_avg' => $star5_count_percentage,
                    ];


                    $average = round($numer / count($reviewsProduct));
                    $average = isset($average) ? $average : "";
                } else {
                    $singleReview = [];
                    $avergeSingleReview = [];
                }
                $reviewAverage = $average;
                $totalReviews = count($reviewsProduct);
                $shippingDetails = $this->productObj->getShippingDetail($productDetailArray['product_id']);
                $faqs = $this->getProductFaqsById($productDetail['product_id']);
                $productShippingPrice = 0;

                $this->campaignObj->avaiableForFlashSale($productDetailArray['product_id']);

                $productInfo['videoUrlSource'] = '';
                
                if ($productDetailArray['video_url']) {
                    
                    $productDetailArray['videoUrlSource'] = $this->getYoutubeEmbedUrl($productDetailArray['video_url']);
                }
                // convert method into UC first
                $productDetailArray['shippings']['method'] = ucfirst($productDetailArray['shippings']['method']);
                return response()->json([
                    'status' => true,
                    'message' => "Product Listing Screen ",
                    'data' => [
                        'productDetail' => $productDetailArray,
                        'productCategories' => $productCategoriesArray,
                        'variations' => $variationsArray,
                        'storeInfo' => $storeInformation,
                        'reviews' => $reviewArray,
                        'reviewAverage' => $reviewAverage,
                        'reviewByStars' => $singleReview,
                        'averageReviewByStars' => $avergeSingleReview,
                        'totalReviews' => $totalReviews,
                        'relatedProduct' => $relatedProductArray,
                        'shippingDetails' => $shippingDetails,
                        'tags' => $tags,
                        'faqs' => $faqs,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Result not found"
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function SellerProductDetail($slug , Request $request)
    {
        // dd(12);
        try {
            $user_id = $request->input('user_id') ?? null;
            // echo 'Data: <pre>' .print_r($slug,true). '</pre>'; die();
            $productDetail = $this->productObj->getSellerProductDetailBySlugApi($slug,$user_id);
            // echo 'Data: <pre>' .print_r($productDetail,true). '</pre>'; die();
            if ($productDetail) {
               
                /*
                * Product Details page  
                */

                // $productDetail = $this->productObj->getProductDetailBySlugApi($slug);
                $productDetailArray = (array) $productDetail;
                $productdescription  = $productDetailArray['description'];
                // dd($productdescription);
                $productShortDescription  = $productDetailArray['short_description'];
                if ($productdescription && !empty($productdescription)) {
                    $prodesTemp = html_entity_decode(strip_tags($productdescription));
                    $productdescription = str_replace(array("\r", "\n"), '', $prodesTemp);
                    // dd($productdescription);
                    $productDetailArray['product_description'] = $productdescription;
                    unset($productDetailArray['description']);
                }

                if ($productShortDescription && !empty($productShortDescription)) {
                    $productShortDescriptionTemp = html_entity_decode(strip_tags($productShortDescription));
                    $productShortDescription = str_replace(array("\r", "\n"), '', $productShortDescriptionTemp);
                    $productDetailArray['product_short_description'] = $productShortDescription;
                    unset($productDetailArray['short_description']);
                   
                }

                $serializedData = $productDetailArray['gallery'];
                $galleryArray = [];
                if ($serializedData && !empty($serializedData)) {
                    $unserializedArray = unserialize($serializedData);

                    foreach ($unserializedArray as $value) {
                        $galleryImageInfo = [];

                        $galleryImageInfo = getAttachmentUrlById((int) $value, 'product');
                        // $gallerySmall = getAttachmentUrlById((int) $value, 'small');
                        $galleryArray[] = $galleryImageInfo;
                        // $galleryImageSmall[] = $gallerySmall;
                    }
                }
                // dd($productDetailArray);
                $productDetailArray['feature_image_path'] = getAttachmentUrlById($productDetailArray['feature_image'], 'product');
                $productDetailArray['gallery_images'] = $galleryArray;
                // $productDetailArray['galleryImageSmall'] = $galleryImageSmall;
                // dd($productDetailArray);
                /*
                * Variation Product Detail  
                */

                $variations = $this->productObj->getAllProductVariation($productDetailArray['product_id']);
                $variationsArray = [];
                
                if (count($variations) > 0) {
                    $stock = 0;
                    foreach ($variations as $value) {
                        // dd($value->current_stock);
                        $stock += $value->current_stock;
                        // dd($stock);
                        $variation = (array) $value; // Convert the object to an array
                        $variation['product_variation_image'] = getAttachmentUrlSmallById((int) $value->product_variation_image, 'small');
                        if (!isset($variation['product_variation_image']) && is_null($variation['product_variation_image'])) {
                            $variation['product_variation_image'] = $productDetailArray['feature_image_path'];
                        }
                        $variationsArray[] = $variation;
                    }
                    $productDetailArray['current_stock'] = $stock;
                }
                /*
                * Product Category  
                */
                $productCategories = $this->productObj->getCateoriesOfProductsApi($productDetail['product_id']);
                $productCategoriesArray = [];
                if (count($productCategories) > 0) {

                    foreach ($productCategories as $category) {
                        $productCategory = (array) $category; // Convert the object to an array
                        $productCategory['category_image_path'] = getAttachmentUrlById((int) $category['image_id'], 'media');
                        $productCategoriesArray[] = $productCategory;
                    }
                }

                /*
                * Related Products   
                */

                $relatedProduct = $this->productObj->getRelatedProducts($productDetailArray['product_id'], $productDetailArray['store_id']);
                $relatedProductArray = [];
                if (count($relatedProduct) > 0) {

                    foreach ($relatedProduct as $prod) {
                        $relatedProducts = (array) $prod;
                        $relatedProducts['feature_image_path'] = getAttachmentUrlById((int) $prod->feature_image, 'media');
                        $relatedProductArray[] = $relatedProducts;
                    }
                }


                $productTags = getTagsByProductId($productDetailArray['product_id']);
                if (isset($productTags)) {
                    $counterTags = count($productTags);
                    $tags = implode(',', $productTags);
                }

                /*
                * Store Information  
                */

                $storeInfo = getStoreInfoByIdApi($productDetail['store_id']);
                if (isset($storeInfo)) {

                    $storeInformation = (array) $storeInfo;
                    $storeInformation['store_thumbnail'] = getAttachmentUrlById((int) $storeInfo->store_logo, 'media');
                }
                // echo 'Data: <pre>' .print_r($storeInformation,true). '</pre>'; die();
                $productTags = getTagsByProductId($productDetailArray['product_id']);
                if (isset($productTags)) {
                    $counterTags = count($productTags);
                    $tags = implode(',', $productTags);
                }

                $reviewsProduct = $this->reviews->get_reviews($productDetailArray['product_id']);
                $reviewArray = [];
                foreach ($reviewsProduct as $review) {
                    $reviews = (array) $review;
                    $user_id = $review['user_id'];
                    $userInfo = getUserById($user_id);
                    $userName = $userInfo->name;
                    $reviews['user_name'] = $userName;
                    $reviewArray[] = $reviews;
                }
                $average = 0;
                if (isset($reviewsProduct) && count($reviewsProduct) > 0) {

                    $numer = 0;
                    $star1_count = 0;
                    $star2_count = 0;
                    $star3_count = 0;
                    $star4_count = 0;
                    $star5_count = 0;
                    foreach ($reviewsProduct as $rev) {
                        $numer += $rev['rating'];



                        if ($rev['rating'] == 1) {
                            $star1_count = $star1_count + 1;
                        }
                        if ($rev['rating'] == 2) {
                            $star2_count = $star2_count + 1;
                        }
                        if ($rev['rating'] == 3) {
                            $star3_count = $star3_count + 1;
                        }
                        if ($rev['rating'] == 4) {
                            $star4_count = $star4_count + 1;
                        }
                        if ($rev['rating'] == 5) {

                            $star5_count = $star5_count + 1;
                        }

                        $total = count($reviews);
                        $star1_count_percentage = round(($star1_count / $total) * 100);
                        $star2_count_percentage = round(($star2_count / $total) * 100);
                        $star3_count_percentage = round(($star3_count / $total) * 100);
                        $star4_count_percentage = round(($star4_count / $total) * 100);
                        $star5_count_percentage = round(($star5_count / $total) * 100);
                    }
                    $singleReview = [
                        'star1_count' => $star1_count,
                        'star2_count' => $star2_count,
                        'star3_count' => $star3_count,
                        'star4_count' => $star4_count,
                        'star5_count' => $star5_count,
                    ];
                    $avergeSingleReview = [
                        'star1_count_avg' => $star1_count_percentage,
                        'star2_count_avg' => $star2_count_percentage,
                        'star3_count_avg' => $star3_count_percentage,
                        'star4_count_avg' => $star4_count_percentage,
                        'star5_count_avg' => $star5_count_percentage,
                    ];


                    $average = round($numer / count($reviewsProduct));
                    $average = isset($average) ? $average : "";
                } else {
                    $singleReview = [];
                    $avergeSingleReview = [];
                }
                $reviewAverage = $average;
                $totalReviews = count($reviewsProduct);
                $shippingDetails = $this->productObj->getShippingDetail($productDetailArray['product_id']);
                $faqs = $this->getProductFaqsById($productDetail['product_id']);
                $productShippingPrice = 0;

                $this->campaignObj->avaiableForFlashSale($productDetailArray['product_id']);
                
                $productInfo['videoUrlSource'] = '';
                if ($productDetailArray['video_url']) {
                    $productDetailArray['videoUrlSource'] = $this->getYoutubeEmbedUrl($productDetailArray['video_url']);
                }
                // convert method into UC first
                $productDetailArray['shippings']['method'] = ucfirst($productDetailArray['shippings']['method']);
                return response()->json([
                    'status' => true,
                    'message' => "Product Listing Screen ",
                    'data' => [
                        'productDetail' => $productDetailArray,
                        'productCategories' => $productCategoriesArray,
                        'variations' => $variationsArray,
                        'storeInfo' => $storeInformation,
                        'reviews' => $reviewArray,
                        'reviewAverage' => $reviewAverage,
                        'reviewByStars' => $singleReview,
                        'averageReviewByStars' => $avergeSingleReview,
                        'totalReviews' => $totalReviews,
                        'relatedProduct' => $relatedProductArray,
                        'shippingDetails' => $shippingDetails,
                        'tags' => $tags,
                        'faqs' => $faqs,
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Result not found"
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function askQuestion(Request $request)
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'question' => 'required',
                'product_id' => 'required|numeric|exists:products,product_id',
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
            $input = $request->all();
            if (isset($input['question']) && $input['question'] != '' && isset($input['product_id']) && isset($input['product_id']) != '') {
                $response = $this->productObj->askQuestionApi($input);
                if ($response['status']) {
                    return response()->json([
                        'status' => true,
                        'message' => "We've sent your question to the store owner",
                        'data' => $response['data'],
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Your question is not sent please contact to site admin"
                    ], 422);
                }
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
    public function getPaginateProducts()
    {
        $user = auth()->user();
        $store = getStoreInfoByUserid($user->user_id);
        $all_products = Product::with('variations')
            ->select('product_id', 'slug', 'title', 'store_id', 'price', 'current_stock', 'feature_image', 'product_type', 'status')
            ->with(['orderItems' => function ($orderItems) {
                $orderItems->select('order_item_id', 'order_id', 'store_id', 'item_id');
                $orderItems->with('storeProductOrder', function ($storeProductOrder) {
                    $storeProductOrder->select('order_id', 'order_status', 'total_items')->where('order_status', 'pending');
                });
            }])
            ->where('store_id', $store->store_id)
            ->where('status', '!=', '2')
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $formattedProducts = [];
      
        foreach ($all_products->items() as $key => $product) {
            // Adjust status based on conditions
            if ($product->product_type != 'variant') {
                if ($product->status == 1 && $product->current_stock > 0) {
                    $product->status = 'active';
                } elseif ($product->status == 0) {
                    $product->status = 'deactive';
                } elseif ($product->status == 3) {
                    $product->status = 'pending';
                } elseif ($product->status == 5) {
                    $product->status = 'draft';
                } elseif ($product->status == 4) {
                    $product->status = 'rejected';
                }

            // Check for out-of-stock for non-variant products
                if ($product->status == 1 && $product->current_stock <= 0) {
                    $product->status = 'out-of-stock';
                }
            }elseif ($product->product_type == 'variant'){
                // foreach($product->variations as $variation){

                // }
                if ($product->status == 1) {
                    $product->status = 'active';
                } elseif ($product->status == 0) {
                    $product->status = 'deactive';
                } elseif ($product->status == 3) {
                    $product->status = 'pending';
                } elseif ($product->status == 5) {
                    $product->status = 'draft';
                } elseif ($product->status == 4) {
                    $product->status = 'rejected';
                }
    
            }

            // Set feature image using the 'getAttachmentUrlSmallById' function
            if (isset($product->feature_image) && !is_null($product->feature_image)) {
                $product->feature_image = getAttachmentUrlSmallById(intval($product->feature_image), 'small');
            }

            // Process variations for variant products
            if ($product->product_type == 'variant' && isset($product->variations) && !empty($product->variations)) {
                $variableproductStock = 0;
                foreach ($product->variations as $key => $variation) {
                    if($key==0){
                        $product->price = $variation->price;
                    }
                    // Set variation image using the 'getAttachmentUrlSmallById' function
                    if(isset($variation->current_stock) && !is_null($variation->current_stock)){
                        $variableproductStock += intval($variation->current_stock);
                    }
                    if (isset($variation->product_variation_image) && !is_null($variation->product_variation_image)) {
                        $imageId = intval($variation->product_variation_image);
                        $variation->product_variation_image = getAttachmentUrlSmallById($imageId, 'small');
                    } else {
                        // Set variation image to the feature image of the main product if not provided
                        $variation->product_variation_image = $product->feature_image;
                    }
                }
                // dd($variableproductStock);
                $product->current_stock = $variableproductStock;
                if ($product->status == 'active' && $variableproductStock == 0) {
                    $product->status = 'out-of-stock';
                    // dd($product->status);
                }
            }
            
            // Add only necessary data to the formattedProducts array
            $formattedProducts[] = [
                'product_id' => $product->product_id,
                'slug' => $product->slug,
                'title' => $product->title,
                'store_id' => $product->store_id,
                'price' => $product->price,
                'current_stock' => $product->current_stock,
                'feature_image' => $product->feature_image,
                'product_type' => $product->product_type,
                'status' => $product->status,
                'variations' => $product->variations,
                'order_items' => $product->orderItems,
            ];
        }

        // Build pagination information
        $paginationData = [
            'current_page' => $all_products->currentPage(),
            'data' => $formattedProducts,
            'first_page_url' => $all_products->url(1),
            'from' => $all_products->firstItem(),
            'last_page' => $all_products->lastPage(),
            'last_page_url' => $all_products->url($all_products->lastPage()),
            'links' => $all_products->links(),
            'next_page_url' => $all_products->nextPageUrl(),
            'path' => $all_products->path(),
            'per_page' => $all_products->perPage(),
            'prev_page_url' => $all_products->previousPageUrl(),
            'to' => $all_products->lastItem(),
            'total' => $all_products->total(),
        ];

        // Check if there are products and return the response
        if (!empty($formattedProducts)) {
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $paginationData,
                'count' => count($formattedProducts),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "You have no product"
            ]);
        }
    }
    public function getProducts()
    {
        $user = auth()->user();
        $store = getStoreInfoByUserid($user->user_id);
        $all_products = Product::with('variations')
            ->select('product_id', 'slug', 'title', 'store_id', 'price', 'current_stock', 'feature_image', 'product_type', 'status')
            ->with(['orderItems' => function ($orderItems) {
                $orderItems->select('order_item_id', 'order_id', 'store_id', 'item_id');
                $orderItems->with('storeProductOrder', function ($storeProductOrder) {
                    $storeProductOrder->select('order_id', 'order_status', 'total_items')->where('order_status', 'pending');
                });
            }])
            ->where('store_id', $store->store_id)
            ->where('status', '!=', '2')
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')->get();

        $formattedProducts = [];
      
        foreach ($all_products as $key => $product) {
            // Adjust status based on conditions
            if ($product->product_type != 'variant') {
                if ($product->status == 1 && $product->current_stock > 0) {
                    $product->status = 'active';
                } elseif ($product->status == 0) {
                    $product->status = 'deactive';
                } elseif ($product->status == 3) {
                    $product->status = 'pending';
                } elseif ($product->status == 5) {
                    $product->status = 'draft';
                } elseif ($product->status == 4) {
                    $product->status = 'rejected';
                }

            // Check for out-of-stock for non-variant products
                if ($product->status == 1 && $product->current_stock <= 0) {
                    $product->status = 'out-of-stock';
                }
            }elseif ($product->product_type == 'variant'){
                // foreach($product->variations as $variation){

                // }
                if ($product->status == 1) {
                    $product->status = 'active';
                } elseif ($product->status == 0) {
                    $product->status = 'deactive';
                } elseif ($product->status == 3) {
                    $product->status = 'pending';
                } elseif ($product->status == 5) {
                    $product->status = 'draft';
                } elseif ($product->status == 4) {
                    $product->status = 'rejected';
                }
    
            }

            // Set feature image using the 'getAttachmentUrlSmallById' function
            if (isset($product->feature_image) && !is_null($product->feature_image)) {
                $product->feature_image = getAttachmentUrlSmallById(intval($product->feature_image), 'small');
            }

            // Process variations for variant products
            if ($product->product_type == 'variant' && isset($product->variations) && !empty($product->variations)) {
                $variableproductStock = 0;
                foreach ($product->variations as $key => $variation) {
                    if($key==0){
                        $product->price = $variation->price;
                    }
                    // Set variation image using the 'getAttachmentUrlSmallById' function
                    if(isset($variation->current_stock) && !is_null($variation->current_stock)){
                        $variableproductStock += intval($variation->current_stock);
                    }
                    if (isset($variation->product_variation_image) && !is_null($variation->product_variation_image)) {
                        $imageId = intval($variation->product_variation_image);
                        $variation->product_variation_image = getAttachmentUrlSmallById($imageId, 'small');
                    } else {
                        // Set variation image to the feature image of the main product if not provided
                        $variation->product_variation_image = $product->feature_image;
                    }
                }
                // dd($variableproductStock);
                $product->current_stock = $variableproductStock;
                if ($product->status == 'active' && $variableproductStock == 0) {
                    $product->status = 'out-of-stock';
                    // dd($product->status);
                }
            }
            
            // Add only necessary data to the formattedProducts array
            $formattedProducts[] = [
                'product_id' => $product->product_id,
                'slug' => $product->slug,
                'title' => $product->title,
                'store_id' => $product->store_id,
                'price' => $product->price,
                'current_stock' => $product->current_stock,
                'feature_image' => $product->feature_image,
                'product_type' => $product->product_type,
                'status' => $product->status,
                'variations' => $product->variations,
                'order_items' => $product->orderItems,
            ];
        }

        // Build pagination information

        // Check if there are products and return the response
        if (!empty($formattedProducts)) {
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $formattedProducts,
                'count' => count($formattedProducts),
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => "You have no product"
            ]);
        }
    }
    
    public function searchProductTitle()
    {
        $user = auth()->user();
    
        $query = Product::select('product_id', 'title', 'store_id', 'price', 'current_stock', 'feature_image', 'product_type', 'sales', 'status', 'slug', 'is_shopify_import', 'shipping_option_id')
            ->with([
                'store:store_id,store_name',
                'orderItems' => function ($orderItems) {
                    $orderItems->select('order_item_id', 'order_id', 'store_id', 'item_id')
                        ->with('storeProductOrder', function ($storeProductOrder) {
                            $storeProductOrder->select('order_id', 'order_status', 'total_items')
                                ->where('order_status', 'pending');
                        });
                }
            ])
            ->whereHas('store', function ($query) use ($user) {
                $query->where('user_id', $user->user_id);
            });
            $query->where('status', '!=', '2');       // Remove Deleted Products

        // Apply title filter if provided
        if ($this->_request->filled('title')) {
            $title = $this->_request->input('title');
            $query->where('title', 'like', "%$title%");
        }
    
        // Apply status filter if provided
        if ($this->_request->filled('status')) {
            $status = $this->_request->input('status');
            
            if (isset($status)) {
                if ($status == 'deactive') {
                    $query->where('status', '0');
                }
                if ($status == 'active') {
                    $query->where('status', '1');
                }
                if ($status == 'delete') {
                    $query->where('status', '2');
                }
                if ($status == 'pending') {
                    $query->where('status', '3');
                }
                if ($status == 'reject') {
                    $query->where('status', '4');
                }
                if ($status == 'draft') {
                    $query->where('status', '5');
                }
                if ($status == 'discounted') {
                    $query->where('on_sale', '1');
                }
                if ($status == 'Featured') {
                    $query->where('status', '7');
                }
            }
        }
    
        $all_products = $query->paginate(10);
    
        if ($all_products->count() > 0) {
            foreach ($all_products->items() as $product) {
                $product->feature_image = getAttachmentUrlById($product->feature_image, 'media');
    
                if ($product->status == 0) {
                    $product->status = 'deActive';
                } else if ($product->status == 1) {
                    $product->status = 'active';
                } else if ($product->status == 3) {
                    $product->status = 'pending';
                } else if ($product->status == 5) {
                    $product->status = 'draft';
                } else if ($product->status == 2) {
                    $product->status = 'delete';
                } else if ($product->status == 4) {
                    $product->status = 'rejected';
                } else if ($product->status == 0) {
                    $product->status = 'deActive';
                }
            }
    
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $all_products,
                'count' => $all_products->total(),
            ], 200);
        }
    
        return response()->json([
            'status' => 404,
            'success' => false,
            'message' => "No products found"
        ]);
    }
       
    public function productStatusChange()
    {
        if ($this->_request->has('slug')) {
          
            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'slug' => 'required',
                    'status' => 'required'
                ]
            );
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = auth()->user();
            $store = getStoreInfoByUserid($user->user_id);
            $slug = $this->_request['slug'];
            $status = $this->_request['status'];
            // $product = Product::where(['slug' => $slug, 'store_id' => $store->store_id])->select('product_id', 'title', 'slug', 'status')->first();
            $product = Product::select('product_id', 'slug', 'title', 'store_id', 'price', 'current_stock', 'feature_image', 'product_type', 'status')->with(['orderItems' => function ($orderItems) {
                $orderItems->select('order_item_id', 'order_id', 'store_id', 'item_id');
                $orderItems->with('storeProductOrder', function ($storeProductOrder) {
                    $storeProductOrder->select('order_id', 'order_status', 'total_items')->where('order_status', 'pending');
                });
            }])->where(['slug' => $slug, 'store_id' => $store->store_id])->where('status', '!=', '2')->first();
            // dd($product);
            $super_store_change = $store->super_status;
            $store_status = $store->store_status;
            if($super_store_change != '1' || $store_status != '2'){
                return response()->json([
                    'status' => 200,
                    'success' => false,
                    'message' => "Please activate your store",
                    'data' => $product
                ]);
            }
            if (isset($product)) {
                if ($status == '0') {
                    $product->status = '1';
                } elseif ($status == '1') {
                    $product->status = '0';
                }
                // dd($product);
                $product->save();
                $product->feature_image = getAttachmentUrlSmallById($product->feature_image, 'small');
                if ($product->status == '0') {
                    $product->status = 'deactive';
                } elseif ($product->status == '1' && $product->current_stock != 0) {
                    $product->status = 'active';
                }elseif($product->status == '1' && $product->current_stock == 0){
                    $product->status = 'out-of-stock';
                }
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => "status changed",
                    'data' => $product
                ]);
            } else {
                return response()->json([
                    'status' => 204,
                    'success' => true,
                    'message' => "product not found",
                ]);
            }
        } else {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => "slug is required"
            ]);
        }
    }
    public function getVariationsInfo(Request $request)
    {
        $data = $request->all();
        $results = '';
        $productImage = "";
        if (count($data) > 0) {
            $results = $this->productObj->getVariationsInfoApi($data);
            
            if (!empty($results)) {
               $product = DB::table('products')->where('product_id',$data['data']['productId'])->first();
                $imagePathId = isset($results[0]['product_variation_image']) ? $results[0]['product_variation_image'] : $product->feature_image;
                $path =  !empty($imagePathId)  ? getAttachmentUrlById((int) $imagePathId, 'small') : '';
                $webpUrl = webPImages($path,'small');
                if ($webpUrl) {
                    $productImage = $webpUrl;
                }else{
                    $productImage = $path;
                }
                // $results
               
            }
            $results = array(
                'results' => $results,
                'productImage' => $productImage,
                
            );
            //    echo '$productImage: <pre>' .print_r($productImage,true). '</pre>'; die;
        }
       return response()->json([
        'status' => true,
        'data' => $results
       ]);
    }
    public function deleteProduct()
    {
        if ($this->_request->has('slug')) {
            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'slug' => 'required',
                ]
            );
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = auth()->user();
            $store = getStoreInfoByUserid($user->user_id);
            $slug = $this->_request['slug'];
            $product = Product::where(['slug' => $slug, 'store_id' => $store->store_id])->first();
            if (isset($product)) {
                $product->status = '2';
                $product->save();
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => "product deleted"
                ]);
            } else {
                return response()->json([
                    'status' => 204,
                    'success' => true,
                    'message' => "product not found"
                ]);
            }
        } else {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => "slug is required"
            ]);
        }
    }
    public function mainCategory()
    {
        $categories = getCategoriesApi();
        if ($categories) {
            return response()->json([
                'status' => true,
                'data' => $categories
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'no category found'
            ]);
        }
    }
    public function getShippingOptions()
    {
        $user = auth()->user();
        $shipping_options = getShippingOptionByUserId($user->user_id);
        $shippingOption = [];
        foreach ($shipping_options as $shipping_option) {
            if ($shipping_option->delete_status == 1) {
                array_push($shippingOption, $shipping_option);
            }
        }
        if (isset($shippingOption) && !empty($shippingOption)) {
            return response()->json([
                'status' => true,
                'data' => $shippingOption
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'no shipping profile found'
            ]);
        }
    }
    // add product to favourites
    public function addtoFavourite()
    {
        $postedData = $this->_request->input();
        if (isset($postedData['product_id']) && !empty($postedData['product_id'])) {
            $response = $this->_favobj->saveFavouriteApi($postedData);
            if ($response == true) {
                return response()->json([
                    'status' => true,
                    'message' => 'Favorite product added successfully',
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'You have unfavorited your product',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'product_id required',
            ]);
        }
    }
    public function getBuyerFavouriteProducts()
    {
        $postedData = $this->_request->input();
        $result = $this->_favobj->getBuyerFavouritesAPI($postedData);
        $products = [];
        foreach ($result as $r_product) {
            $rating = getProductRatingById($r_product['product_id']);
            $featureImage = getAttachmentUrlById($r_product['feature_Image']);
            unset($r_product['feature_Image']); // Remove the original key
            $r_product['feature_image'] = $featureImage;
            $r_product['rating'] = $rating;
            array_push($products, $r_product);
        }
        if (isset($products) && !empty($products)) {
            return response()->json([
                'status' => true,
                'favData' => $products
            ]);
        } else {
            return response()->json([
                'status' => false,
                'favData' => 'you have no product in favourites'
            ]);
        }
    }
    public function deleteFavouriteProduct(){
        $postedData = $this->_request->input();
        $result = $this->_favobj->removeFavouriteApi($postedData);
        return response()->json([
            'status' =>$result['status'],
            'message' => 'product removed from favourite'
        ]);
    }
    public function getLoveProducts()
    {
        // Retrieve 10 products
        $result = Product::where('status', '=', '1')->take(10)->get();
        $products = [];
        foreach ($result as $r_product) {
            $rating = getProductRatingById($r_product->product_id);
            $featureImage = getAttachmentUrlById($r_product->feature_image);
            $r_product['feature_image'] = $featureImage;
            $r_product['rating'] = $rating;
            array_push($products, $r_product);
        }
        // You can now use the $products variable as needed
        if (isset($products)) {
            return response()->json([
                'status' => true,
                'products' => $products
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'you have no product'
            ]);
        }
    }
    public function getSortedReviews(Request $request)
    {

        $productId = $request->input('product_id');
        $sortBy = $request->input('sort_by');
        $filteredReviews = $this->filterReviewsByProductId($productId);
        $reviews = $this->sortReviews($filteredReviews, $sortBy);
        if(isset($reviews) && !empty($reviews)){
            foreach($reviews as $review){
                $customer = getUserById($review['user_id']);
                $review['customer_name'] = $customer->name;
                $sortedReviews[] = $review;
            }
            return response()->json([
                'status' => true,
                'data' => $sortedReviews
            ]);
        }else{
            return response()->json([
                'status' => true,
                'data' => null
            ]);
        }
        
    }

    private function sortReviews($reviews, $sortBy)
    {
        if ($sortBy == 'high-to-low') {
            $sortBy = 'desc';
            $reviews = collect($reviews)->sortByDesc('rating')->values()->all();
        } elseif ($sortBy == 'low-to-high') {
            $sortBy = 'asc';
            $reviews = collect($reviews)->sortBy('rating')->values()->all();
        } elseif ($sortBy == 'old-to-new') {
            $sortBy = 'asc';
            $reviews = collect($reviews)->sortBy('created_at')->values()->all();
        } elseif ($sortBy == 'new-to-old') {
            $sortBy = 'desc';
            $reviews = collect($reviews)->sortByDesc('created_at')->values()->all();
        }
        // Add more sorting criteria as needed

        return $reviews;
    }

    private function filterReviewsByProductId($productId)
    {
        $reviews = Review::where('product_id', $productId)->get()->ToArray();
        return $reviews;
        // return array_filter($reviews, function ($review) use ($productId) {
        //     return $review['product_id'] == $productId;
        // });
    }
    function getYoutubeEmbedUrl($videoUrl)
    {
        
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';

        if (preg_match($longUrlRegex, $videoUrl, $matches) || preg_match($shortUrlRegex, $videoUrl, $matches)) {
            $youtube_id = $matches[1];
            // return 'https://www.youtube.com/embed/' . $youtube_id;
            return $videoUrl;
        } else {
            // Handle case when video URL doesn't match any pattern
            $youtube_id = null;
            return $youtube_id;
        }

        // if (preg_match($shortUrlRegex, $videoUrl, $matches)) {
        //     $youtube_id = $matches[count($matches) - 1];
        // }
       
    }

    public function editProduct($productId)
    {
        // return 12;
        $userId = auth()->user()->user_id;
        $role = getCurrentUserRoleIdApi();
    //    return response()->json([$role]);
        $variations = null;
        $viewParameter = array();
        $parentCategory = $this->productObj->getAllCategory('default', 0);

        $productInfo = $this->productObj->getProductByID($productId);
        if(isset($productInfo['shortDescription']) && !empty($productInfo['shortDescription']) && !is_null($productInfo['shortDescription'])){
            $prodesTemp =html_entity_decode(strip_tags($productInfo['shortDescription']));
            $shortDescription = str_replace(array("\r", "\n"), '', $prodesTemp);
            $productInfo['shortDescription'] = $shortDescription;
        }
        if(isset($productInfo['description']) && !empty($productInfo['description']) && !is_null($productInfo['description'])){
            $prodesTemp =html_entity_decode(strip_tags($productInfo['description']));
            $description = str_replace(array("\r", "\n"), '', $prodesTemp);
            $productInfo['description'] = $description;
        }
        $status = getProductStatus($productInfo['product_id']);
        $productInfo['status'] = $status;
        if ($productInfo['status'] == '0') {
            $productInfo['status'] = 'deactive';
        } elseif ($productInfo['status'] == '1') {
            $productInfo['status'] = 'active';
        }
        if (isset($productInfo['featureImage']) && !is_null($productInfo['featureImage'])) {
            $featureImage['id'] = $productInfo['featureImage'];
            $featureImage['path'] = getAttachmentUrlSmallById($productInfo['featureImage']);
            if (!is_null($featureImage) && $featureImage != 'Please add a valid id') {
                
                $productInfo['featureImage'] = $featureImage;
            } else {
                $productInfo['featureImage'] = null;
            }
        }
        if (isset($productInfo['gallery']) && !is_null($productInfo['gallery'])) {
            $galleryArray = array();
            foreach ($productInfo['gallery'] as $index => $g_image_id) {
                $g_image_url = getAttachmentUrlSmallById(intval($g_image_id));
                if (!is_null($g_image_id) && $g_image_id != 'Please add a valid id') {
                    $galleryArray[$index]['id'] = $g_image_id;
                    $galleryArray[$index]['path'] = $g_image_url;
                }
            }
            $productInfo['gallery'] = array_values($galleryArray); // Reindex the array
        }
        
        $productUserId = $productInfo['user_id'];
        if ($productInfo['product_type'] == 'variant') {
            $variations = $this->productObj->getAllProductVariation($productId);
            if (isset($variations) && !empty($variations)) {
                // $postedData = null;
                foreach ($variations as $index => $variation) {
                    // dd($variation);
                    if(isset($variation->product_variation_image) && !is_null($variation->product_variation_image)){
                        $variation->logo['id'] = intval($variation->product_variation_image);
                        $variation->logo['path'] = getAttachmentUrlSmallById(intval($variation->product_variation_image),'small');
                    }else{
                        // $variation->product_variation_image = $productInfo['featureImage'];
                        $variation->logo['id'] = $productInfo['featureImage'];
                        $variation->logo['path'] = getAttachmentUrlSmallById($productInfo['featureImage'],'small');
                    }
                    foreach ($variation as $key => $value) {
                        if ($key == 'price') {
                            $variation->{'price' . ($index + 1)} = $value;
                            unset($variation->$key);
                        }
                        if ($key == 'sku') {
                            $variation->{'sku' . ($index + 1)} = $value;
                            unset($variation->$key);
                        }
                        if ($key == 'current_stock') {
                            $variation->{'current_stock' . ($index + 1)} = $value;
                            unset($variation->$key);
                        }
                        if ($key == 'product_variation_image') {
                            unset($variation->$key);
                        }
                    }
                    // $variation_option = [];
                    
                    $variation_option[0][] = $variation->option1;
                    $variation_option[1][] = $variation->option2;
                    $variation_option[2][] = $variation->option3;
                    $postedData = [
                        'variationAttributeOne' => $variation->attribute1,
                        'variationAttributeTwo' => $variation->attribute2,
                        'variationAttributeThree' => $variation->attribute3,
                        'variationAttributeUnitOne' => $variation->unit1,
                        'variationAttributeUnitTwo' => $variation->unit2,
                        'variationAttributeUnitThree' => $variation->unit3,
                        'variation_options' => $variation_option
                    ];
                    $variation->attribute1 = $variation->option1;
                    $variation->attribute2 = $variation->option2;
                    $variation->attribute3 = $variation->option3;
                  
                }
                // return $variations;
                $viewParameter['variations']['data'] = $variations;
                $viewParameter['variations']['postedData'] = $postedData;
                $variationAttributes = $this->productObj->getVariationAttributes();
                $viewParameter['variationAttributes'] = $variationAttributes;
                // return $viewParameter;
            }
        }
        $productInfo['productVideoURL'] = isset($productInfo['video_url']) ? $productInfo['video_url'] : null;
        // if (isset($productInfo['video_url']) && $productInfo['video_url'] != "") {
        //     // $productInfo['videoUrlSource'] = $this->getEcho($productInfo['video_url']);
        // }
        // return response()->json($productInfo);
        
        // dd($productInfo['category']);
        $productCategories = ['parent' => '', 'child' => ''];

        foreach ($productInfo['category'] as $index => $p_category) {
            $category = category::where('id', $p_category->category_id)->first();
        // dd($category);
        // print_r($category);
        if(isset($category) && !is_null($category)){
            if ($category->parentCategoryID > 0) {
                $childCategory = [
                    'id' => $category->id,
                    'value' => $category->category_slug,
                    'label' => $category->categoryTitle,
                ];
                // if(isset($childCategory))
                $productCategories['child'] = $childCategory;
            } else {
                $parentCategory = [
                    'id' => $category->id,
                    'value' => $category->category_slug,
                    'label' => $category->categoryTitle,
                ];
                $productCategories['parent'] = $parentCategory;
            }
        
            unset($productInfo['category'][$index]);
        }
            
        }
        // die;
        // dd($productCategories);
        $productInfo['category'] =  $productCategories;
        // $category = category::where('id',$productInfo['category'][0]->category_id)->first();
        // $productInfo['category']['id'] = $category->id;
        // $productInfo['category']['value'] = $category->category_slug;
        // $productInfo['category']['label'] = $category->categoryTitle;
        // unset($productInfo['category'][0]);
        $viewParameter['parentCategory'] = $parentCategory;
        $viewParameter['prodcutDetails'] = $productInfo;
        $viewParameter['mode'] = 'edit';
        $store_data = getStoreDataByUserId($userId);
        if (isset($store_data) && !is_null($store_data)) {
            // dd($store_data);
            $store_data->store_state = getStatebyId(isset($store_data->store_state) ?? '');
            $store_data->country = 'US';
            $store_data->store_logo = getAttachmentUrlById(intval(isset($store_data->store_logo)) ?? '');
            $store_data->store_status = StoreStatusById($store_data->store_id);
        }
        // for condition filter pass id 1
        // super admin can access all product
        // dd($productUserId);
        // return response()->json($viewParameter);
        if ($userId == $productUserId && $role->role_status == '1') {
            // if ($this->isMobileDevice()) {
            //     return View('product/mobile/edit', $viewParameter);
            // } else {
            // echo 'prodcutDetails: <pre>' .print_r($productInfo,true). '</pre>'; die;
            // return View('product/edit', $viewParameter, ['variations' => $variations, 'store_data' => $store_data]);
            $data =
                [
                    'product' => $viewParameter,
                    'store_data' => $store_data
                ];
            return response()->json([
                'status' => true,
                'data' => $data

            ]);
            //}
        } else {
            return response()->json([
                'status' => false,
                'data' => 'error'

            ]);
        }
    }

   
    public function productUpdate()
    {
        $allData = $this->_request->input();
        // return response()->json(['success'=>$allData]);
        if (isset($allData['edit_product_type']) && $allData['edit_product_type'] == 'virtual') {
            $allData['product_file'] = $this->_request->file('product_files');
        }
        if($allData['previousShippingOptionsType'] == 'USPS'){
            $rates = $this->getRates($allData);
            // return response()->json([
            //     $rates['status']
            // ]);
            if($rates['status'] == false){
                return response()->json([
                    'status' => false,
                    'message' => 'No matching dimensions were found. Please set the correct dimensions'
                ]);
            }else{
                if(array_key_exists('quantity',$rates)){
                    $allData['maxquantitylimit'] = $rates['quantity'];

                }
            }
        }
        $result = $this->productObj->edit_product_by_id_api($allData);
        return response()->json([
            'status'=>true,
            'data' => $result,
            'message' => 'product updated successfully'
        ]);
    }

    public function getRates($data)
    {
        // ...
        
        $productWeight = $data['productWeight'];
        $productSize_length = $data['productSize_length'];
        $productSize_width = $data['productSize_width'];
        $productSize_height = $data['productSize_height'];
        if ($productWeight > 0 && $productSize_length == 0 && $productSize_width == 0 && $productSize_height == 0) {
            // Usps request for check rate get or not
            $r_data = [];
            $weightType = $data["weight_type"];
            $cwight = 0;

            // if ($weightType == 'oz') {
            //     $cwight = $productWeight / 16;
            //     $data['productWeight'] = number_format($cwight, 3, '.', '');
            // } else
             if ($weightType == 'pound') {
                $r_data['productWeight'] = number_format($productWeight * 16, 3, '.', '');
            }else{
                $r_data['productWeight'] = $productWeight;
            }
            // return $r_data;
            // else if ($weightType == 'kg') {
            //     $data['productWeight'] = number_format($productWeight * 35.274, 3, '.', ''); // 1 kg = 35.274 ounces
            // } else if ($weightType == 'gram') {
            //     $data['productWeight'] = number_format($productWeight / 28.35, 3, '.', ''); // 1 gram = 0.035274 ounces
            // }
            // dd($data);
            $response = $this->makeCurlRequest('https://dev.mallshark.net/api/getrate_usps_service', 'POST', $r_data);
            // return $response;
            if ($response['status'] == false) {
               return ['status' => false];
            }else{
                return ['status' => true];
            }
           

        } else if ($productWeight > 0 || $productSize_length == 0 || $productSize_width == 0 || $productSize_height == 0) {
            // dd(12);
            $r_data = [];
            $weightType = $data['weight_type'];
            $dimensionType = $data['dimention_type'];
            $clength = 0;
            $cheight = 0;
            $cwidth = 0;
            $cweight = 0;

            $r_data['productSize_length'] = round($productSize_length * 10) / 10;
            $r_data['productSize_width'] = round($productSize_width * 10) / 10;
            $r_data['productSize_height'] = round($productSize_height * 10) / 10;

            if ($weightType == 'pound') {
                $r_data['productWeight'] = number_format($productWeight * 16, 3, '.', '');
            } else{
                $r_data['productWeight'] = $productWeight;
            }
            // else if ($weightType == 'oz') {
            //     $cwight = $productWeight / 16;
            //     $data['productWeight'] = number_format($cwight, 3, '.', '');
            // }
            // else if ($weightType == 'kg') {
            //     $data['productWeight'] = number_format($productWeight * 35.274, 3, '.', ''); // 1 kg = 35.274 ounces
            // } else if ($weightType == 'gram') {
            //     $data['productWeight'] = number_format($productWeight / 28.35, 3, '.', ''); // 1 gram = 0.035274 ounces
            // }

            if ($dimensionType == 'cm') {
                $clength = $productSize_length / 2.54;
                $cheight = $productSize_height / 2.54;
                $cwidth = $productSize_width / 2.54;
                $r_data['productSize_length'] = number_format($clength, 3, '.', '');
                $r_data['productSize_width'] = number_format($cwidth, 3, '.', '');
                $r_data['productSize_height'] = number_format($cheight, 3, '.', '');
            } else if ($dimensionType == 'mm') {
                $clength = $productSize_length / 25.4;
                $cheight = $productSize_height / 25.4;
                $cwidth = $productSize_width / 25.4;
                $r_data['productSize_length'] = number_format($clength, 3, '.', '');
                $r_data['productSize_height'] = number_format($cheight, 3, '.', '');
                $r_data['productSize_width'] = number_format($cwidth, 3, '.', '');
            }else{
                $r_data['productSize_length'] = $productSize_length;
                $r_data['productSize_width'] = $productSize_width;
                $r_data['productSize_height'] = $productSize_height;
            }
            $response = $this->makeCurlRequest('https://dev.mallshark.net/api/getrates', 'POST', $r_data);
            // return $response;
            if ($response['status'] == false) {
                return ['status' => false];
            }else{
                return ['status' => true,'quantity' => $response['maxLimitQuantity']];
            }
                // Additional code here if needed
            

            // if (empty($response->json())) {
            //     $field_error = true;
            //     echo "Error: No matching dimensions found. Please set the correct dimensions";
            // }
        }
    }
    function makeCurlRequest($url, $method, $data)
    {
        // dd($url);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Handle cURL error
            echo 'Curl error: ' . curl_error($ch);
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    public function toggleMallshark(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'mallshark_selected' => 'required|boolean',
        ]);

        $product->mallshark_selected = $validated['mallshark_selected'];
        $product->save();
        $product->searchable();
        return response()->json([
            'message' => 'Mallshark selection updated successfully.',
            'mallshark_selected' => $product->mallshark_selected,
        ]);
    }

}
