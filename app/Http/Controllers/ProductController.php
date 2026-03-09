<?php

namespace App\Http\Controllers;

use File;
use Image;
use Shippo;
use Redirect;
use Carbon\Carbon;
use Shippo_Address;
use App\Models\Store;
use App\Http\Requests;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\category;
use App\Models\ProductFaq;
use Illuminate\Http\Request;
use App\Http\Controllers\UspsT;
use App\Models\CategoryProduct;
use Illuminate\Support\Facades\DB;
use App\Models\VariationAttributes;
use Illuminate\Support\Facades\URL;
use App\Repositories\Cart\Cartaccess;
use App\Repositories\Herd\Herdaccess;
use App\Repositories\User\Useraccess;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ReviewsController;
use App\Repositories\Filters\Filtersaccess;
use App\Repositories\Product\Productaccess;
use App\Repositories\Campaign\Campaignaccess;
use App\Repositories\Location\LocationAccess;
use App\Http\Controllers\TransportersController;
use App\Http\Controllers\ElasticSearchController;
use App\Repositories\VendorReviews\VendorReviewsaccess;

class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $_request;
    private $productObj;
    private $reviews;
    private $filter_obj;
    private $shiprate;
    private $campaignObj;
    private $herdObj;
    private $_esearch;
    private $_productCategory;
    private $_product;
    private $_category;

    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->productObj = new Productaccess();
        $this->reviews = new ReviewsController();
        $this->_locationobj = new LocationAccess();
        $this->shiprate = new UspsT();
        $this->campaignObj = new Campaignaccess();
        $this->herdObj = new Herdaccess();
        $this->_esearch = new ElasticSearchController();
        $this->_productCategory = new CategoryProduct();
        $this->_product = new Product();
        $this->_category = new category();
        // $this->_cartobj = new Cartaccess();
        //$this->filter_obj = new Filtersaccess();
        //$this->_vendorreivewobj = new VendorReviewsaccess();
    }

    /*
     * Function loadVideoThumb
     *
     * Use  for Check Which TYpe of Video Link 
     *
     * @param 
     * @return 
     */


    /*
     * Function productDetail
     *
     * Use for Display Product Detail Page Content
     *
     * @param 
     * @return 
     */

    /*
     * product detail page information
     */

    public function productDetail()
    {
        $slug = collect(request()->segments())->last();
        $reviewsProduct = array();
        $productResults = product::where('slug', '=', $slug)
            ->leftjoin('brands', 'brands.id', '=', 'products.product_id')
            ->leftjoin('shipping_options', 'shipping_options.id', '=', 'products.shipping_optionID')
            ->get()->toArray();
        if (isset($productResults[0])) {

            $faqs = $this->getProductFaqsById($productResults[0]['product_id']);
            $causes = $this->getProductCausesById($productResults[0]['product_id']);
            $variations = $this->productObj->getAllProductVariation($productResults[0]['product_id']);
            $values = $this->getProductValueById($productResults[0]['product_id']);
            $loc = array();
            $locations = $this->getProductsCountByProductId($productResults[0]['product_id']);
            $i = 1;
            foreach ($locations as $lo) {

                if (array_key_exists($lo->user_country_id . '_' . $lo->user_state_id, $loc)) {
                    $loc[$lo->user_country_id . '_' . $lo->user_state_id] = $loc[$lo->user_country_id . '_' . $lo->user_state_id] + 1;
                }
                if (!array_key_exists($lo->user_country_id . '_' . $lo->user_state_id, $loc)) {
                    $loc[$lo->user_country_id . '_' . $lo->user_state_id] = 1;
                }
            }
            $locationsArray = array();
            foreach ($loc as $key => $val) {
                $explodedKey = explode('_', $key);
                if (isset($explodedKey[0]) && isset($explodedKey[1])) {
                    $country = $this->_locationobj->getCountryByCountryCode($explodedKey[0]);
                    $state = $this->_locationobj->getStateByStateCode($explodedKey[1], $explodedKey[0]);
                    $locationsArray[] = array("countryname" => isset($country[0]['country_name']) ? $country[0]['country_name'] : '', "statename" => isset($state[0]['state_name']) ? $state[0]['state_name'] : '', 'count' => $val);
                }
            }

            $reviewsProduct = $this->reviews->get_reviews($productResults[0]['product_id']);
            $supporters = $this->getSupporters($productResults[0]['product_id']);
            $supportersArr = array();
            foreach ($supporters as $supporter) {

                $country = $this->_locationobj->getCountryByCountryCode($supporter->user_country_id);
                $state = $this->_locationobj->getStateByStateCode($supporter->user_state_id, $supporter->user_country_id);

                $supportersArr[] = array("countryname" => isset($country[0]['country_name']) ? $country[0]['country_name'] : '', "statename" => isset($state[0]['state_name']) ? $state[0]['state_name'] : '', "user_id" => $supporter->user_id, "name" => $supporter->name, "user_avatar" => $supporter->user_avatar, "count" => $this->getProductsCountBySupporterId($supporter->user_id));
            }
            //            decode = no need to base 64 decode id
            $totalProducts = $this->_vendorreivewobj->getStoreProductCount($productResults[0]['store_id'], "decode");
            $totalRating = $this->_vendorreivewobj->getStoreRating($productResults[0]['store_id'], "decode");
            $storeSales = $this->_vendorreivewobj->countStoreOrders($productResults[0]['store_id'], "decode");
            $storeInfo = $this->_vendorreivewobj->getStoreInfoById($productResults[0]['store_id'], "decode");
            // if ($this->isMobileDevice()) {
            //     return View('product/mobile/productDetail', array('product_id' => $productResults[0]['product_id'], 'reviews' => $reviewsProduct, 'supporters' => $supportersArr, 'locations' => $locationsArray, 'faqs' => $faqs, 'variations' => $variations, 'values' => $values, 'totalProducts' => $totalProducts, 'totalRating' => $totalRating, 'storeInfo' => $storeInfo, 'storeSales' => $storeSales, 'causes' => $causes))->with('data', $productResults);
            // } else {
                return View('product/mobile/productDetail', array('product_id' => $productResults[0]['product_id'], 'reviews' => $reviewsProduct, 'supporters' => $supportersArr, 'locations' => $locationsArray, 'faqs' => $faqs, 'variations' => $variations, 'values' => $values, 'totalProducts' => $totalProducts, 'totalRating' => $totalRating, 'storeInfo' => $storeInfo, 'storeSales' => $storeSales, 'causes' => $causes))->with('data', $productResults);
            // }
        } else {
            // if ($this->isMobileDevice()) {
            //     return View('product/mobile/productDetail');
            // } else {
                return View('product/mobile/productDetail');
            // }
        }


        //        return View('product/productDetail', array('reviews' => $reviewsProduct, 'supporters' => $supportersArr, 'locations' => $locationsArray, 'faqs' => $faqs))->with('data', $productResults);
    }

    /*
     * Function doAddProduct
     *
     * Use  for Add Product in Store 
     *
     * @param 
     * @return
     */

    public function doAddProductNew()
    {
        $variationAttributes = $this->productObj->getVariationAttributes();
        $brands = $this->productObj->getAllBrands();
        $parentCategory = $this->productObj->getAllCategory('default', 0);
        $viewParameter = array();
        $viewParameter['parentCategory'] = $parentCategory;
        $viewParameter['brands'] = $brands;
        $viewParameter['variationAttributes'] = $variationAttributes;
        return View('product/addProduct-new', $viewParameter);
    }

    public function addProduct()
    {
        
       
        
        if ($this->_request->isMethod('post')) {
            $productData = $this->_request->input();
            
            if (isset($productData['product_type']) && $productData['product_type'] == 'virtual') {
                $productData['product_file'] = $this->_request->file('product_files');
            }
            // $files = $this->_request->file('product_files');
            // echo '$productData: <pre>' .print_r($productData,true). '</pre>'; die;
            // print_rr($productData); exit;
            $validate = $this->productObj->addStoreProduct($productData);
            if ($validate == 'save') {
                if($this->_request->another_product_add == 1 ){
                    $this->_request->session()->flash('SuccessMessage', 'Product added and sent for approval');
                    return Redirect::back()->with('SuccessMessage','Product added and sent for approval');
                }elseif($this->_request->another_product_add == 5){
                    $this->_request->session()->flash('SuccessMessage', 'Product added as draft');
                    return Redirect::to('/store-products');
                
                }else{
                    $this->_request->session()->flash('SuccessMessage', 'Product added and sent for approval');
                    return Redirect::to('/store-products');
                }
            } else {
                $this->_request->session()->flash('SuccessMessage', 'Your product is saved in draft.');
                return Redirect::to('/product/' . $validate);
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

    public function editproductAction()
    {
       
        $allData = $this->_request->input();
        if (isset($allData['edit_product_type']) && $allData['edit_product_type'] == 'virtual') {
            $allData['product_file'] = $this->_request->file('product_files');
        }
        //echo 'Data: <pre>' .print_r($allData,true). '</pre>'; exit;
        $flasmessage = "Product updated successfully.";
        if (isset($allData['edit_product_id']) && $allData['edit_product_id'] != "") {
            $productInfo = $this->productObj->getProductByID($allData['edit_product_id']);
            if ($productInfo['status'] == '4') {

                $flasmessage = "Your product has been sent to our team for approval. One of our moderators will review and notify shortly.";
            }
        }

        $result = $this->productObj->edit_product_by_id($allData);
        
        $this->_request->session()->flash('SuccessMessage', $flasmessage);
        if (isset($allData['redirect']) && $allData['redirect'] != "") {
            return Redirect::to('/store/oncampaign');
        } else if (isset($allData['edit-by']) && $allData['edit-by'] != "") {
            return Redirect::to('admin/store-products');
        } else {
            return Redirect::to('/store-products');
        }
        ///return Redirect::to('/edit-product/' . $result);
    }

    public function addProductVariation()
    {
        $allData = $this->_request->input();
        return View('product/pricing/productVariation', $allData)->render();
    }

    public function getProductIdByVariations(Request $request){
        // dd($request->all());
        $productId = $request->input('productId');
        $variationImageId = $request->input('variationImageId');
        $variationKey = $request->input('variationKey');
        $productInfo = $this->productObj->getProductByID($productId);
        $variations = $this->productObj->getAllProductVariation($productId);
        // dd($productInfo);
        return view('product/getVariations-popup-ajax' , compact('variations',  'productInfo', 'variationImageId', 'variationKey'));
    }

    public function editProductIdByVariations(Request $request){
        // dd($request->all());
        $productId = $request->input('productId');
        $variationImageId = $request->input('variationImageId');
        $variationKey = $request->input('variationKey');
        $productInfo = $this->productObj->getProductByID($productId);
        $variations = $this->productObj->getAllProductVariation($productId);
        return view('product/editVariations-popup-ajax' , compact('variations',  'productInfo', 'variationImageId', 'variationKey'));
    }

    public function editProductIdByFeatureImage(Request $request){
        // dd($request->all());
        $productId = $request->input('productId');
        $featureImage = $request->input('productFeature');
        $productInfo = $this->productObj->getProductByID($productId);
        return view('product/editFeatureImage-popup-ajax' , compact('productInfo', 'featureImage'));
    }

    public function editProductIdBySetSelectedFeatureImage(Request $request){
        try {
            $data =  $request->all();
            $productInfo = $this->productObj->updateSelectedFeatureImage($data);
    
            // Assuming $productInfo is an array or object you want to return as JSON.
            return $productInfo;
        } catch (\Exception $e) {
            // Handle exceptions here
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function editProduct($productId)
    {
       
        $role = getCurrentUserRoleId();
        // dump($role);
        $parentCategory = $this->productObj->getAllCategory('default', 0);
        $productInfo = $this->productObj->getProductByID($productId);
        $productUserId = $productInfo['user_id'];
        $userId = Session::get('userData')['user_id'];
        $variations = $this->productObj->getAllProductVariation($productId);
        $variationAttributes = $this->productObj->getVariationAttributes();
        $viewParameter = array();
        $productInfo['videoUrlSource'] = '';
        if (isset($productInfo['video_url']) && $productInfo['video_url'] != "") {
            $productInfo['videoUrlSource'] = $this->getEcho($productInfo['video_url']);
        }
        $viewParameter['parentCategory'] = $parentCategory;
        $viewParameter['prodcutDetails'] = $productInfo;
        $viewParameter['variations'] = $variations;
        $viewParameter['variationAttributes'] = $variationAttributes;
        $viewParameter['mode'] = 'edit';
        $userId = Session::get('userData')['user_id'];
        $store_data = getStoreDataByUserId($userId);
        // for condition filter pass id 1
        // super admin can access all product
        if ($userId == $productUserId && $role->role_status == '1') {
            // if ($this->isMobileDevice()) {
            //     return View('product/mobile/edit', $viewParameter);
            // } else {
                // echo 'prodcutDetails: <pre>' .print_r($viewParameter,true). '</pre>'; die;
                // dd($viewParameter);
                return View('product/edit', $viewParameter, ['variations' => $variations, 'store_data' => $store_data]);
            //}
        } else {
            return view('error.404page');
        }
    }

    public function adminEditProduct($productId)
    {
        $role = getCurrentUserRoleId();
        // dump($role);

        $parentCategory = $this->productObj->getAllCategory('default', 0);

        $productInfo = $this->productObj->getProductByID($productId);
        $productUserId = $productInfo['user_id'];
        // dd($productUserId);
        $userId = Session::get('userData')['user_id'];
        $variations = $this->productObj->getAllProductVariation($productId);
        $variationAttributes = $this->productObj->getVariationAttributes();
        $viewParameter = array();
        $productInfo['videoUrlSource'] = '';
        if (isset($productInfo['video_url']) && $productInfo['video_url'] != "") {
            $productInfo['videoUrlSource'] = $this->getEcho($productInfo['video_url']);
        }
        $viewParameter['parentCategory'] = $parentCategory;
        $viewParameter['prodcutDetails'] = $productInfo;
        $viewParameter['variations'] = $variations;
        $viewParameter['variationAttributes'] = $variationAttributes;
        $viewParameter['mode'] = 'edit';
        $userId = Session::get('userData')['user_id'];
        // $store_data = getStoreDataByUserId($userId);
        $store_data = getStoreDataByProductId($productId);
        // for condition filter pass id 1
        // super admin can access all product
        if ($userId == $productUserId || $role->role_status == '1') {
            // if ($this->isMobileDevice()) {
            //     return View('product/mobile/edit', $viewParameter);
            // } else {
                // echo 'prodcutDetails: <pre>' .print_r($productInfo,true). '</pre>'; die;
                return View('product/edit', $viewParameter, ['variations' => $variations, 'store_data' => $store_data]);
            //}
        } else {
            exit("<h4>No Record found</h4>");
        }
    }

    public function editProductFaqs($productId)
    {
        $productInfo = $this->productObj->getProductByID($productId);
        // if ($this->isMobileDevice()) {
        //     return view('product/mobile/productfaqs', ['productDetails' => $productInfo]);
        // } else {
            return view('product/productfaqs', ['productDetails' => $productInfo]);
        // }
    }
    public function vendoreditFaqs($id)
    {
        $productFaq = ProductFaq::findOrFail($id);
        //    echo '$productFaq: <pre>' .print_r($productFaq,true). '</pre>'; die();
        // $faqs = ProductFaq::findOrFail($id);
        // if ($this->isMobileDevice()) {
        //     return view('product/mobile/editfaqs', compact('productFaq'));
        // } else {
            return view('product/editfaqs', compact('productFaq'));
        // }
    }
    // Delete Product Faqs
    public function deleteFaqs(Request $request)
    {
        // dd($request);
        DB::table('product_faqs')->where('faq_id', $request->id)->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Poof! Your Product Faq has been deleted!'
            ]
        );
    }

    // Update Product Faqs
    public function updateProductFaqs(Request $request, $id)
    {
        $faqs = ProductFaq::find($id);
        $faqs->question = $request->input('question');
        $faqs->answer = $request->input('answer');
        $faqs->update();
        return redirect()->route('store-products')->with('success', 'Faqs Answer Updated Successfully');
    }

    public function getChildCategory()
    {
        if ($this->_request->isMethod('post')) {
            $parentId = $this->_request->input('parentID');
            $record = $this->productObj->getAllCategory('default', $parentId);
            $html = '';
            if (!empty($record)) {

                $html .= '<h6>Sub Category</h6>';
                $html .= '<select class = "form-control" name = "productCategory[]">';
                $html .= '<option value = "">None</option>';
                foreach ($record as $value) {
                    $html .= '<option value = "' . $value['id'] . '">' . $value['categoryTitle'] . '</option>';
                }
                $html .= '</select>';
            }
            return $html;
        }
    }

    /*
     * Function deleteProductImage
     *
     * Use  for Delete Image 
     *
     * @param 
     * @return 
     */

    public function deleteProductImage()
    {
        $result = '';
        if ($this->_request->isMethod('post')) {
            $imageName = $this->_request->input();
            $result = $this->productObj->deleteProductTemRequest($imageName);
        }
        return $result;
    }

    public function deleteEditProductImage()
    {
        $result = '';
        if ($this->_request->isMethod('post')) {
            $imageName = $this->_request->input();
            $result = $this->productObj->deleteProductImageRequest($imageName);
        }
        return $result;
    }

    public function deleteEditProductFeatureImage()
    {
        $result = '';
        if ($this->_request->isMethod('post')) {
            $imageName = $this->_request->input();
            $result = $this->productObj->deleteEditProductFeatureImage($imageName);

            // dd($result);
        }
        return $result;
    }

    public function deleteEditProductthumbnailImage()
    {
        $result = '';
        if ($this->_request->isMethod('post')) {
            $imageName = $this->_request->input();
            $result = $this->productObj->deleteEditProductFeatureImage($imageName);
        }
        return $result;
    }


    public function deleteProductTempImage()
    {
        $result = '';
        if ($this->_request->isMethod('post')) {
            $imageName = $this->_request->input();
            $result = $this->productObj->deleteProductTemRequest($imageName);
        }
        return $result;
    }

    public function deleteProductItem()
    {
        $result = '';
        if ($this->_request->isMethod('post')) {
            $itemName = $this->_request->input();
            $result = $this->productObj->deleteProductFileRequest($itemName);
        }
        return $result;
    }
    // Delete Banner image 

    public function deleteBannerImage() {
        
        $result = '';
        if ($this->_request->isMethod('post')) {
            $itemName = $this->_request->input();
            $result = $this->productObj->deleteBannerImageFile($itemName);
        }
        return $result;
    }
    // create store image delete 
    public function deleteBannerImageCreate() {
        
        $result = '';
        if ($this->_request->isMethod('post')) {
            $itemName = $this->_request->input();
            $result = $this->productObj->deleteBannerImageFileCreate($itemName);
        }
        return $result;
    }

    // Update Banner image 
    public function updateBannerImage() {
        
        $result = '';
        if ($this->_request->isMethod('post')) {
            $posteddata = $this->_request->input();
            $result = $this->productObj->updateBannerImageFile($posteddata);
        }
        die();
        return $result;
    }

    public function getProductFaqsById($productId)
    {
        return $faqs = $this->productObj->getProductFaqsById($productId);
    }

    public function uploadProductImageRequest()
    {
        $result = array();
        if ($this->_request->hasFile('productImage')) {
            $imageObj = $this->_request->file('productImage');
        } else {
            $imageObj = '';
        }
        return $result = $this->productObj->uploadProcessProductImage($imageObj);
    }

    public function uploadEditProductImage(Request $request)
    {
      
        $productImages = $request->file('productimages');
        $attachmentIds = [];

        foreach ($productImages as $image) {
            // Process each image and save it as an attachment
            $data = file_get_contents($image->getRealPath());
            $fileName = 'cropImage--_--' . $this->productObj->generateRandomString(15) . '.png';
            $destinationPath = public_path() . '/uploads/temp';

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }

            $file = $destinationPath . '/' . $fileName;
            file_put_contents($file, $data);
            $attachmentId = $this->productObj->getAttachmentId($fileName, 'image', true, false);

            $attachmentIds[] = $attachmentId;
        }
        $productId = $request->input('product_id'); // Make sure you have a way to get the product ID
        $existingSerializedIds = Product::find($productId)->gallery; // Retrieve existing serialized IDs
       
        if (!empty($existingSerializedIds)) {

            $newSerializedIds = serialize(array_merge(unserialize($existingSerializedIds), $attachmentIds));
            Product::where('product_id', $productId)->update(['gallery' => $newSerializedIds]);

        } else {

            $newSerializedIds = serialize($attachmentIds);
            Product::where('product_id', $productId)->update(['gallery' => $newSerializedIds]);
        }
        

        return response()->json($attachmentIds);
    }

    public function getImagePathbyId($id){

        $response = getAttachmentUrlById((int) $id);
        
        return response()->json($response);
    }
    public function getImagePathbyIdBanner($id){

        $response = getAttachmentUrlforEmail((int) $id);
        return response()->json($response);
    }

    public function uploadProductCropImage()
    {

        if ($this->_request->isMethod('post')) {


            if (!empty($this->_request->input('croppedImage'))) {
                //                $img = str_replace('data:image / png;
                //            base64, ', '', $this->_request->input('croppedImage'));
                //                $img = str_replace(' ', '+', $img);
                //                $data = base64_decode($img);


                $img = str_replace('data:image/png;base64,', '', $this->_request->input('croppedImage'));
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);

                $fileName = $this->productObj->generateRandomString(15) . '.png';
                $fileName = 'cropImage--_--' . $this->productObj->generateRandomString(15) . '.png';
                $destinationPath = public_path() . '/uploads/temp';
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }
                $file = $destinationPath . '/' . $fileName;
                file_put_contents($file, $data);
                return $fileName;
            }
        }
    }

    function getProductsCountByProductId($productId)
    {
        return $buyers = $this->productObj->getProductsCountByProductId($productId);
    }

    public function getVariationsInfo(Request $request)
    {
        $data = $request->all();
        $results = '';
        $productImage = "";
        if (count($data) > 1) {
            $results = $this->productObj->getVariationsInfo($data);
            if (!empty($results)) {

                $product = DB::table('products')->where('product_id',$data['data']['productId'])->first();
                $imagePathId = isset($results[0]->product_variation_image) ? $results[0]->product_variation_image : $product->feature_image;
              
                $productImage =  !empty($imagePathId)  ? getAttachmentUrlById((int) $imagePathId, 'product') : '';
               
            }
            $results = array(
                'results' => $results,
                'productImage' => $productImage,
                
            );
            //    echo '$productImage: <pre>' .print_r($productImage,true). '</pre>'; die;
        }

        return json_encode($results);
    }

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

    public function productVarient()
    {
        // dd(12);
        if ($this->_request->ajax()) {
            $data = array();
            $arr = array();
            $edit =false;
            $variationData = array();
            $varPostedArray = array();
            $postedData = $this->_request->all();
            // echo 'Data: <pre>' .print_r($postedData,true). '</pre>'; die();

            // exit;
            if (isset($postedData['product_id']) && $postedData['product_id']) {
                $query = DB::table('product_variation')->select('*')->where('product_id', $postedData['product_id'])
                    ->get();
                $variationData = objToArray($query);
            }
            if (isset($postedData['variation_options']) && $postedData['variation_options'] != "") {
                $data['variation_options'] = $this->combinations($postedData['variation_options']);
            }
            if(isset($postedData['product_id'])){
                $edit =true;
                return View('product.product-variation-options', ['data' => $data], ['postedData' => $postedData, 'variationData' => $variationData, 'edit' =>$edit,'product_id'=>$postedData['product_id']])->render();
            
            }else{
                
                return View('product.product-variation-options', ['data' => $data], ['postedData' => $postedData, 'variationData' => $variationData ,'edit' =>$edit])->render();
            }
        }
    }

    public function listingDetail()
    {
    
        $productShippingPrice = "";
        $availablbeTransporter = "";
        $slug = collect(request()->segments())->last();
        $productDetail = $this->productObj->getProductDetailBySlug($slug);
        // $productWeight = $productDetail['weight'];
        $productCategories = $this->productObj->getCateoriesOfProducts($productDetail['product_id']);
        $productTags = getTagsByProductId($productDetail['product_id']);
        if (isset($productTags)) {
            $counterTags = count($productTags);
            $tags = implode(',', $productTags);
        }
        $reviewsProduct = $this->reviews->get_reviews($productDetail['product_id']);
        $variations = $this->productObj->getAllProductVariation($productDetail['product_id']);
        $postCategories = $this->productObj->getCateoriesOfProducts($productDetail['product_id']);
        $relatedProduct = $this->productObj->getRelatedProducts($productDetail['product_id'], $productDetail['store_id']);
        $shippingDetails = $this->productObj->getShippingDetail($productDetail['product_id']);
        // echo 'Data: <pre>' .print_r($shippingDetails->shipping_weight,true). '</pre>'; die;
        // $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
        $storeData = getStoreDataByUserId($productDetail['user_id']);
        $storeInfo = getStoreInfoById($productDetail['store_id']);
        $totalProducts = $this->productObj->getStoreProductCount($productDetail['store_id']);
        $storeSales = $this->productObj->countStoreOrders($productDetail['store_id']);
        // $destinationzipcode=$location['postal'];
        // $destinationzipcode = '90001';
        // $orignZipcode = '91601';
        // $orignZipcode=$storeData->zip_code;
        $faqs = $this->getProductFaqsById($productDetail['product_id']);
        // $campaign = $this->campaignObj->getSupportingCampaign($productDetail['store_id']);
        // $superSupporters = $this->herdObj->getSupporters($productDetail['store_id']);
        // $superSupportersnew = $this->herdObj->getSupporters($productDetail['store_id']);
        $productShippingPrice = 0;

        //Hide data for calculate shipping data 

        // if (!empty($shippingDetails) && $shippingDetails->is_domesticfree == 0) {


        //     // echo $shippingDetails->shipping_country_id;
        //     if ($shippingDetails->shipping_country_id == 1) {
        //         $productShippingPrice = (float) $shippingDetails->flat_shipping_rate + (float) $shippingDetails->domestic_handling_fee * 10;
        //     } else {
        //         $productShippingPrice = $this->shiprate->getDpmesticRates($orignZipcode, $destinationzipcode, $productWeight, $shippingDetails->shipping_service);
        //         $shippingDetails->domestic_handling_fee;
        //         $productShippingPrice = (float) $productShippingPrice + (float) $shippingDetails->domestic_handling_fee * 10;
        //     }
        // }
        // $remainPledges = $this->productObj->getRemainPledgeAmount($productDetail['store_id']);
        $this->campaignObj->avaiableForFlashSale($productDetail['product_id']);
        $productDetail = $this->productObj->getProductDetailBySlug($slug);
        $cartAccess = new Cartaccess();
        $incartProduct = $cartAccess->checkProductIncart($productDetail['product_id']);
        $productInfo['videoUrlSource'] = '';
        if ($productDetail['video_url']) {
            $productDetail['videoUrlSource'] = $this->getYoutubeEmbedUrl($productDetail['video_url']);
        }
            return View('product/productdetail', ['productDetail' => $productDetail, 'productCategories' => $productCategories, 
            'productShippingPrice' => $productShippingPrice, 'variations' => $variations, 'availablbeTransporter' => $availablbeTransporter,
            'postCategories' => $postCategories, 'storeSales' => $storeSales, 'storeInfo' => $storeInfo, 'totalProducts' => $totalProducts,
            'faqs' => $faqs,  'reviews' => $reviewsProduct,
            'incartProduct' => $incartProduct, 'relatedProduct' => $relatedProduct, 'shippingDetails' => $shippingDetails,
             'tags' => $tags]);


    }

    public function categoryProducts(Request $request)
    {

        $category_id = $request->input('category_id');
        $result = array();
        if ($category_id != '') {
            $related_products = DB::table('product_category')
                ->where('id', '=', $category_id)
                ->select('product_id')
                ->get()
                ->toArray();

            $selectedCategory = DB::table('categories')
                ->where('categoryTitle', '=', 'Uncategorized')
                ->get()
                ->toArray();
            //   echo 'Data: <pre>' .print_r($related_products,true). '</pre>'; die();
            if (count($related_products) > 0) {

                foreach ($related_products as $pr) {
                    $this->_product->where('product_id', '=', $pr->product_id)->update(['status' => '2']);
                    $this->_productCategory->where('product_id', '=', $pr->product_id)->update(['category_id' => $selectedCategory[0]['id']]);
                }



                return 'yes';
            } else {
                return 'no';
            }
        } else {
            return 'no';
        }
    }

    public function deleteCategory(Request $request)
    {
        try {
            $category_id = $request->input('category_id');
            if ($category_id != '') {
                $related_products = DB::table('product_category')
                ->where('category_id', '=', $category_id)
                ->select('product_id')
                ->get()
                ->toArray();
                if (count($related_products) > 0) {
                    $selectedCategory = getCategoryUncategorized();
                    foreach ($related_products as $pr) {                      
                        // dd($pr->product_id);
                        $this->_product->where('product_id', '=', $pr->product_id)->update(['status' => '2']);
                        $this->_productCategory->where('product_id', '=', $pr->product_id)->update(['category_id' => $selectedCategory->id]);
                        $this->_esearch->updateProductDocument($pr->product_id);
                    }
                    $delcategory = DB::table('categories')->where('id', $category_id)->delete();
                    return [
                        'status' => true,
                        'message' => "Category deleted and Associated products Uncategorized",
                    ];
                } else {
                    
                    DB::table('categories')->where('id', $category_id)->delete();
                    return [
                        'status' => true,
                        'message' => "Category deleted there are no any associated products",
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'message' => "There are no category available"
                ];
            }
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' =>$th->getMessage()
            ];
        
        }
    }

    public function calculateShippingByprodcutId()
    {
        if ($this->_request->ajax()) {
            $productId = $this->_request->input('product_id');
            $quantity = $this->_request->input('quantity');
            $productDetail = $this->productObj->getProductByID($productId);
            $productWeight = $productDetail['weight'] * $quantity;
            $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
            $storeData = getStoreDataByUserId($productDetail['user_id']);
            //$destinationzipcode=$location['postal'];
            $destinationzipcode = '91730';
            $orignZipcode = '91601';
            //$orignZipcode=$storeData['zip_code']
            $second_weight = '';
            $totalReq = "";
            $onePrice = 0;
            $productShippingPrice = 0;
            if ($productWeight > 70) {
                $totalReq = ceil($productWeight / 70);
                for ($i = 0; $i <= $totalReq; $i++) {
                    $onePrice = $this->shiprate->getDpmesticRates($orignZipcode, $destinationzipcode, 70);
                    $productShippingPrice = $productShippingPrice + $onePrice;
                }
            } else {
                $productShippingPrice = $this->shiprate->getDpmesticRates($orignZipcode, $destinationzipcode, $productWeight);
            }
            echo $productShippingPrice;
        }
    }

    public function getCategoryProducts(Request $request)
    {
        $category_id = $request->input('catid');
        $result = array();
        if ($category_id != '') {
            $related_products = DB::table('product_category')
                ->where('category_id', '=', $category_id)
                ->where('status', '=', '1')
                ->LeftJoin('products', 'product_category.product_id', '=', 'products.product_id')
                ->LeftJoin('stores', 'stores.store_id', '=', 'products.store_id')
                ->orderby('products.created_at', 'DESC')
                ->select('products.*')
                ->get()
                ->toArray();
            //  echo 'Data: <pre>' .print_r($related_products,true). '</pre>'; die;
            return View('home.recent-products-ajax-new', ['productsdata' => $related_products])->render();
        }
    }
    public function getCategoryActiveProducts(Request $request)
    {
        $category_id = $request->input('catid');
        $result = array();
        if ($category_id != '') {
            $related_products = DB::table('product_category')
                ->where('category_id', '=', $category_id)
                ->where('status', '=', '1')
                ->LeftJoin('products', 'product_category.product_id', '=', 'products.product_id')
                ->LeftJoin('stores', 'stores.store_id', '=', 'products.store_id')
                ->orderby('products.created_at', 'DESC')
                ->select('products.*')
                ->get()
                ->toArray();
            return View('home.recent-products-ajax-new', ['productsdata' => $related_products])->render();
        }
    }


    public function askQuestion(Request $request)
    {
        // dd($request->input());
        $input = "";
        $input = $request->all();

        if (strpos($input['question'], '?') !== false) {
        } else {
            $input['question'] = $input['question'] . " ?";
        }
        $arr = array();
        if (isset($input['question']) && $input['question'] != '' && isset($input['product_id']) && isset($input['product_id']) != '') {
            $causes = $this->productObj->askQuestion($input);
            if ($causes) {
                $res = true;
            } else {
                $res = false;
            }
        } else {
            $res = false;
        }

        if ($res) {
            $arr['status'] = 'success';
            $arr['message'] = 'We have emailed your question to the store owner. One of their support staff members will get back to you shortly. Thankyou !';
        } else {
            $arr['status'] = 'error';
            $arr['message'] = 'Your question is not sent please contact to site admin';
        }
        return $arr;
    }

    public function pledgeSupport(Request $request)
    {
        $input = $request->all();
        $arr = array();

        if (isset($input['campaign_id']) && isset($input['pledge_amount'])) {
            $userId = getCurrentUserId();
            if ($userId) {
                $store = $this->campaignObj->getCampaignStoreIconId($input['campaign_id']);
                if ($store) {
                    if ($store->store_user_id == $userId) {
                        $arr['status'] = 'error';
                        $arr['message'] = 'Sorry, you cannot pledge the store that you own.';
                    } else {
                        $arr = $this->herdObj->updateHerd($userId, $store->store_icon, $input['pledge_amount'], $store->store_name);
                        if ($arr['status'] == 'success') {
                            $this->campaignObj->updatePledge($input['campaign_id'], $input['pledge_amount']);

                            $userobj = new Useraccess();
                            $userData = $userobj->getUserDataById($userId);
                            $arr['user_wallet'] = !empty($userData->user_wallet) ? number_format_short($userData->user_wallet) : 0;

                            if (isset($input['herd_id'])) {
                                $herdDetails = $this->herdObj->getHerd($input['herd_id'], $input['user_type']);
                                $arr['herd_details'] = $herdDetails;
                            }
                        }
                    }
                } else {
                    $arr['status'] = 'error';
                    $arr['message'] = 'Store not found';
                }
            } else {
                $arr['status'] = 'error';
                $arr['message'] = 'Please login to pledge support';
            }
        } else {
            $arr['status'] = 'error';
            $arr['message'] = 'Campaign or pledge amount is missing';
        }
        return json_encode($arr);
    }

    public function shippingOptionsPopup()
    {
        $userId = Session::get('userData')['user_id'];
        $shippingProfileId = $this->_request->input('shippingProfileId');
        $param = array();
        $param['result'] = getShippingOptionMethodById(base64_decode($shippingProfileId));
        $processTime = DB::table('processing_time')->WhereNull('user_id')->orWhere('user_id', $userId)->get();
        $param['processTime'] = $processTime;
        $param['action'] = $this->_request->input('action');
        //echo '$param: <pre>' .print_r($param,true). '</pre>'; die;
        return View('product/shipping/popupShippingOptions', $param);
    }

    public function updateShippingProfile()
    {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->all();
            $userId = isset($postedData['store_id']) ? Store::where('store_id', $postedData['store_id'])->pluck('user_id')->first() : null;
            if (isset($postedData['shipping_option_id']) && $postedData['shipping_option_id'] != '') {
                $updateShippingProfile = $this->productObj->updateShippingProfile($postedData);
                
                if(!$updateShippingProfile){
                //     return response()->json(['error' => 'Shipping option name already exists for this user']);
                // return View('product/shipping/previousShippingOption', ['shippingId' => $postedData['shipping_option_id']])->render();
                return response()->json([
                    'error' => 'Shipping option name already exists for this user',
                    'view' => view('product.shipping.previousShippingOption', ['shippingId' => $postedData['shipping_option_id']])->render()
                ]);

                }
                return response()->json([
                    'view' => view('product.shipping.previousShippingOption', ['shippingId' => $postedData['shipping_option_id']])->render()
                ]);
               
            } else {
                $saveShippingProfile = $this->productObj->saveShippingProfile($postedData);
                if(!$saveShippingProfile){
                    //     return response()->json(['error' => 'Shipping option name already exists for this user']);
                    // return View('product/shipping/previousShippingOption', ['shippingId' => $postedData['shipping_option_id']])->render();
                    return response()->json([
                        'error' => 'Shipping option name already exists for this user',
                        'view' => view('product/shipping/previousShippingOption')->render()
                    ]);
                 }
                 return response()->json([
                    'view' => view('product/shipping/previousShippingOption', ['user_Id' => $userId])->render()
                ]);
            }
        }
    }

    public function updateManageShippingProfile()
    {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->all();

            if (isset($postedData['shipping_option_id']) && $postedData['shipping_option_id'] != '') {
                $shipping = $this->productObj->updateShippingProfile($postedData);
                
                if($shipping){
                    return['status' => true,'view'=>View('product/shipping/managedShippingOption', ['shippingId' => $postedData['shipping_option_id']])->render()];

                }else{
                return['status' => false,'view'=>View('product/shipping/managedShippingOption', ['shippingId' => $postedData['shipping_option_id']])->render()];

                }
            } else {
               $shipping = $this->productObj->saveShippingProfile($postedData);
               if($shipping){
                return['status' => true, 'view' =>View('product/shipping/managedShippingOption')->render()];
               }else{
                return['status' => false, 'view' =>View('product/shipping/managedShippingOption')->render()];

               }

            }
        }
    }
    public function deleteShippingProfile(){
        try {
            $user_id = Session::get('userData')['user_id'];
            $shipping_id = $this->_request->input('ship_id');
            if ($shipping_id != '') {
             
                    DB::table('shipping_options')->where('id', '=', $shipping_id)->update(['delete_status' => '0']);
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
                'message' =>$th->getMessage()
            ];
        
        }
    }
     public function mangeShippingProfile(){
            $shippingProfile = $this->productObj->getShippingProfile();
            // echo 'Data: <pre>' .print_r($shippingProfile,true). '</pre>'; die();
            $userId = Session::get('userData')['user_id'];
            $store_data = getStoreDataByUserId($userId);
            return View('product/shipping/manageShippingPage', ['shippingProfile' => $shippingProfile, 'store_data' => $store_data]);
      
     }

    public function checkService()
    {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->all();
            $query = DB::table('shipping_options')->where('shipping_country_id', 0)->where('id', $postedData['ship_service_id'])->where('is_domesticfree', 0)->select('shipping_service')->first();
            if (!empty($query)) {
                return $query->shipping_service;
            } else {
                return false;
            }
        }
    }

    //video url

    public function renderEmbed()
    {
        
        if ($this->isYoutube()) {
            $this->youtubeImg();
        } elseif ($this->isVimeo()) {
            
            $this->vimeoImg();
        }elseif($this->isYoutu()){
            $this->youtuImg();
        }
    }

    private function isYoutube()
    {
       
        return strpos($this->url, 'youtube.com') !== false;
    }
    private function isYoutu()
    {
       
        return strpos($this->url, 'youtu.be') !== false;
    }
    private function isVimeo()
    {
   
        return strpos($this->url, 'vimeo') !== false;
    }

    public function vimeoImg()
    {
        $hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/' . $this->getVimeoRefFromUrl() . '.php'));

        $html = "<div class='video_thumb'>";
        $html .= "<div class = 'image-thumbnail-panel'>";
        $html .= "<img class='img-fluid' src='" . $hash[0]['thumbnail_medium'] . "'>";
        $html .= "</div>";
        $html .= "<div class='dele-icn'>";
        $html .= " <a href = 'http://vimeo.com/" . $this->getVimeoRefFromUrl() . "'  rel='prettyPhoto' >";
        $html .= "<i class='fa fa-eye' aria-hidden='true'></i>";
        $html .= "</a>";
        $html .= "<a href='javascript:;'   class='trash-lnk add-media-cancel'>";
        $html .= " <i class = 'fa fa-trash-o' aria-hidden = 'true'></i>";
        $html .= "</a>";
        $html .= "</div> ";
        echo $html .= "</div>";
    }

    private function getVimeoRefFromUrl()
    {
        preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $this->url, $output_array);
        return $output_array[5];
    }

    public function youtubeImg()
    {
        $html = "<div class='video_thumb'>";
        $html .= "<div class='image-thumbnail-panel'>";
        $html .= "<img class='img-fluid' style='width:100%;' src=\"https://img.youtube.com/vi/" . $this->getYoutubeRefFromUrl() . "/0.jpg\">";
        $html .= "</div>";
        $html .= "<div class='dele-icn' >";
        $html .= "<a style='margin-right:10px;'  href='" . $this->url . "' rel='prettyPhoto' data-featherlight='iframe' >";
        $html .= "<i class='fa fa-eye' aria-hidden='true'></i>";
        $html .= "</a>";
        $html .= "<a href='javascript:;'   class='trash-lnk add-media-cancel'>";
        $html .= "<i class='fa fa-trash-o' aria-hidden='true'></i>";
        $html .= "</a>";
        $html .= "</div>";
        echo $html .= "</div>";
    }
    public function youtuImg()
    {
        $html = "<div class='video_thumb'>";
        $html .= "<div class='image-thumbnail-panel'>";
        $html .= "<img class='img-fluid' style='width:100%;' src=\"https://img.youtube.com/vi/" . $this->getYoutuRefFromUrl() . "/0.jpg\">";
        $html .= "</div>";
        $html .= "<div class='dele-icn' >";
        $html .= "<a style='margin-right:10px;'  href='" . $this->url . "' rel='prettyPhoto' data-featherlight='iframe' >";
        $html .= "<i class='fa fa-eye' aria-hidden='true'></i>";
        $html .= "</a>";
        $html .= "<a href='javascript:;'   class='trash-lnk add-media-cancel'>";
        $html .= "<i class='fa fa-trash-o' aria-hidden='true'></i>";
        $html .= "</a>";
        $html .= "</div>";
        echo $html .= "</div>";
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

    public function getYoutubeRefFromUrl($url = "")
    {
        $lastBit = explode('v=', $this->url);
        $lastBit = explode('&', $lastBit[1]);
        return $lastBit[0];
    }
    public function getYoutuRefFromUrl($url = "")
    {
        $lastBit = explode('?si=', $this->url);
        $lastBit = explode('.be/', $lastBit[0]);
        return $lastBit[1];
    }
    function getYoutubeEmbedUrl($videoUrl)
    {
        $shortUrlRegex = '/youtu\.be\/([a-zA-Z0-9_-]+)/i';
        $longUrlRegex = '/youtube\.com\/(?:embed|watch)\?(?:.*v=|\/)([a-zA-Z0-9_-]+)/i';

        if (preg_match($longUrlRegex, $videoUrl, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }

        if (preg_match($shortUrlRegex, $videoUrl, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }
        return 'https://www.youtube.com/embed/' . $youtube_id;
    }

    //    public function getvideoDetail($videoUrl) {
    //        $lastBit = explode('v=', $videoUrl);
    //        $lastBit = explode('&', $lastBit[1]);
    //        $imageUrl="http://img.youtube.com/vi/" . $lastBit[0] . "/0.jpg";
    //        return $imageUrl;
    //    }

    public function getEcho($videoUrl)
    {
        ob_start();
        $this->url = $videoUrl;
        $this->autoplay = 0;
        $this->loadVideoThumb();
        return ob_get_clean();
    }
    // Is feature when buyer want of each product
    public function metamaskFeatureProduct(Request $request)
    {
        $ProdcutId = $request->all();
        $current = Carbon::now();
        $currenttime = Carbon::now();
        $trialExpires = $currenttime->addMonth();
        DB::table('products')->where('product_id', $ProdcutId)->update(['is_feature' => '1', 'feature_startdate' => $current, 'feature_enddate' => $trialExpires]);
        return Redirect::back()->with('message_success', 'Product Featured Successfully.');
    }

    public function importShopifyProducts()
    {
        // if ($this->isMobileDevice()) {
        //     return view('product.mobile.shopifyImportProduct');
        // } else {
            return view('product.shopifyImportProduct');
        // }
    }
    public function csvExport(){
        $store = new Store();
        $userID = Session::get('userData')['user_id'];
        // dd($userID);
        $store = $store->select('store_id','store_name')->where('user_id',$userID)->first();
        $storeID = $store->store_id;
        $fileName = 'products.csv';
        $products = Product::with('variations','parentCategories','shippingOption')->where('store_id' , $storeID)->where('status' , '1')->get();
        $csv = "Handle,Title,Body,Vendor,Product Category,Type,Tags,Published,Option 1 Name,Option 1 Value,Option 2 Name,Option 2 Value,Option 3 Name,Option 3 Value,Variant SKU,Variant Grams,Variant Price,Image Src,Variant Image,Variant Weight Unit,Status\n";
        foreach ($products as $product) {
            $title = isset($product->title) ? '"' . str_replace('"', '""', $product->title) . '"' : '';
            $handle = isset($product->slug) ? $product->slug : '' ;
            $product_category = isset($product->parentCategories) ? $product->parentCategories[0]->categoryTitle : '';
            $short_description = isset($product->short_description) ? $product->short_description : '' ;
            $description = isset($product->description) ? '"' . str_replace('"', '""', $product->description) . '"' : '';
            $price = isset($product->price) ? $product->price : '' ;
            $sku = isset($product->sku) ? $product->sku : '' ;
            $weight = isset($product->weight) ? $product->weight : '' ;
            $image = getAttachmentUrlById((int) $product->feature_image);
            $product_type = isset($product->product_type) ? $product->product_type : '';
            $product_shipping_option_id = isset($product->shipping_option_id) ? $product->shipping_option_id : ''; 
            $product_weight_type = isset($product->weight_type) ? $product->weight_type : ''; 
            $product_dimension_type = isset($product->dimension_type) ? $product->dimension_type : '';
            $csv .= "{$product->slug},{$title},{$product->price}\n";
            if(isset($product->variations) && !is_null($product->variations)){
                $title = isset($product->title) ? '"' . str_replace('"', '""', $product->title) . '"' : '';
            $handle = isset($product->slug) ? $product->slug : '' ;
            $short_description = isset($product->short_description) ? $product->short_description : '' ;
            $description = isset($product->description) ? '"' . str_replace('"', '""', $product->description) . '"' : '';
            $price = isset($product->price) ? $product->price : '' ;
            $sku = isset($product->sku) ? $product->sku : '' ;
            $weight = isset($product->weight) ? $product->weight : '' ;
            $image = getAttachmentUrlById((int) $product->feature_image);
            $product_type = isset($product->product_type) ? $product->product_type : '';
            $product_type = isset($product->product_type) ? $product->product_type : '';
            $product_shipping_option_id = isset($product->shipping_option_id) ? $product->shipping_option_id : ''; 
            $product_weight_type = isset($product->weight_type) ? $product->weight_type : ''; 
            $product_dimension_type = isset($product->dimension_type) ? $product->dimension_type : '';
            $csv .= "{$product->slug},{$title},{$description},{$store->store_name},{$product_category},{$product_type},{$price},{$product->price}\n";
            }
        }

        // Prepare CSV response
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=products.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::make($csv, 200, $headers);
    }
    public function allActiveProducts(){
        // $store = new Store();
        // $userID = Session::get('userData')['user_id'];
        // // dd($userID);
        // $store = $store->select('store_id','store_name')->where('user_id',$userID)->first();
        // $storeID = $store->store_id;
        $fileName = 'products.csv';
        $products = Product::with('store:store_id,store_name') // Eager load the related store
        ->where('status', '1')->orderBy('store_id') // Filter products where status is 1
        ->get(); // Specify columns to select
    
        
        $csv = "Title,Slug,Store,Type,Price\n";
        foreach ($products as $product) {
            $title = isset($product->title) ? '"' . str_replace('"', '""', $product->title) . '"' : '';
            $handle = isset($product->slug) ? 'https://mallshark.com/product/'.$product->slug : '' ;
            $price = isset($product->price) ? $product->price : '' ;
            $product_type = isset($product->product_type) ? $product->product_type : '';
            
            $csv .= "{$title},{$handle},{$product->store->store_name},{$product_type},{$product->price}\n";
        }

        // Prepare CSV response
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=products.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::make($csv, 200, $headers);
    }

    public function almostSoldOut(Request $request){
        $productId = $request->id;
        $productType = $request->type;
        $v1 = $request->v1;
        $v2 = $request->v2;
        $v3 = $request->v3;
        if($productType == "variant"){
           if(isset($v1) && $v1 != '' && isset($v2) && $v2 != '' && isset($v3) && $v3 != ''){
                $isAlmostSoldOut = ProductVariation::where('product_id', '=', $productId)
                    ->where('option1', '=', $v1)
                    ->where('option2', '=', $v2)
                    ->where('option3', '=', $v3)
                    ->where('current_stock', '<=', 5)
                    ->where('current_stock', '>', 0)
                    ->exists();
                    return response()->json([
                        'success' => true,
                        'isAlmostSoldOut' => $isAlmostSoldOut,
                    ]);
            }
            else if(isset($v1) && $v1 != '' && isset($v2) && $v2 != ''){
                $isAlmostSoldOut = ProductVariation::where('product_id', '=', $productId)
                ->where('option1', '=', $v1)
                ->where('option2', '=', $v2)
                ->where('current_stock', '<=', 5)
                ->where('current_stock', '>', 0)
                ->exists();
                return response()->json([
                    'success' => true,
                    'isAlmostSoldOut' => $isAlmostSoldOut,
                ]);
            }
            else if(isset($v2) && $v2 != '' && isset($v3) && $v3 != ''){
                $isAlmostSoldOut = ProductVariation::where('product_id', '=', $productId)
                ->where('option2', '=', $v2)
                ->where('option3', '=', $v3)
                ->where('current_stock', '<=', 5)
                ->where('current_stock', '>', 0)
                ->exists();
                return response()->json([
                    'success' => true,
                    'isAlmostSoldOut' => $isAlmostSoldOut,
                ]);
            }
            else if(isset($v1) && $v1 != '' && isset($v3) && $v3 != ''){
                $isAlmostSoldOut = ProductVariation::where('product_id', '=', $productId)
                ->where('option1', '=', $v1)
                ->where('option3', '=', $v3)
                ->where('current_stock', '<=', 5)
                ->where('current_stock', '>', 0)
                ->exists();
                return response()->json([
                    'success' => true,
                    'isAlmostSoldOut' => $isAlmostSoldOut,
                ]);
            }
            else if(isset($v1) && $v1 != ''){
                $isAlmostSoldOut = ProductVariation::where('product_id', '=', $productId)
                ->where('option1', '=', $v1)
                ->where('current_stock', '<=', 5)
                ->where('current_stock', '>', 0)
                ->exists();
                return response()->json([
                    'success' => true,
                    'isAlmostSoldOut' => $isAlmostSoldOut,
                ]);
            }
            else if(isset($v2) && $v2 != ''){
                $isAlmostSoldOut = ProductVariation::where('product_id', '=', $productId)
                ->where('option2', '=', $v2)
                ->where('current_stock', '<=', 5)
                ->where('current_stock', '>', 0)
                ->exists();
                return response()->json([
                    'success' => true,
                    'isAlmostSoldOut' => $isAlmostSoldOut,
                ]);
            }
            else if(isset($v3) && $v3 != ''){
                $isAlmostSoldOut = ProductVariation::where('product_id', '=', $productId)
                ->where('option3', '=', $v3)
                ->where('current_stock', '<=', 5)
                ->where('current_stock', '>', 0)
                ->exists();
                return response()->json([
                    'success' => true,
                    'isAlmostSoldOut' => $isAlmostSoldOut,
                ]);
            }
        }else{
            $isAlmostSoldOut = Product::where('product_id', $productId)
            ->where('current_stock', '<=', 5)
            ->where('current_stock', '>', 0)
            ->exists();
            return response()->json([
                'success' => true,
                'isAlmostSoldOut' => $isAlmostSoldOut,
            ]);
        }
    }
}
