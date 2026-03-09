<?php

namespace App\Http\Controllers;

use Crypt;
use Cookie;
use Redirect;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Attachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use App\Models\FeatureProductPackage;
use App\Repositories\Buyer\Buyeraccess;
use App\Repositories\Store\Storeaccess;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StripeController;
use App\Repositories\Product\Productaccess;
use App\Models\FeatureProductPackagePurchase;
use App\Repositories\Campaign\Campaignaccess;
use App\Http\Controllers\ElasticSearchController;
use App\Repositories\Reports\Reportsaccess;
use App\Models\ProductVariation;
use App\Models\Discount;

class StoreController extends Controller
{

    private $_storeobj;
    private $_storereview;
    private $_request;
    private $_productobj;
    private $_stripeObj;
    private $_campObj;
    private $_esearch;
    private $_buyerobj;
    private $_reportsobj;

//
    public function __construct(Request $request)
    {
        $this->_storeobj = new Storeaccess();
        $this->_storeobj;
        $this->_campObj = new Campaignaccess();
        $this->_esearch = new ElasticSearchController();
        $this->_productobj = new Productaccess();
        $this->_stripeObj = new StripeController();
        $this->_buyerobj = new Buyeraccess();
        $this->_reportsobj = new Reportsaccess();
        $this->_request = $request;
    }

    public function showStores(Request $req)
    {
        $keyword = $req->input('keyword');
        $results = $this->_storeobj->SearchStores($keyword);
        // echo '$results: <pre>' .print_r($results,true). '</pre>'; die();
        // if ($this->isMobileDevice()) {
        //     return view('Store.mobile.stores', array('stores' => $results));
        // } else {
            return view('Store.stores', array('stores' => $results));

        // }
    }

    public function index($id = "")
    {
        $userId = Session::get('userData')['user_id'];
        //for test case start
        $checkroute = true;
        if ($userId == '') {
            $userId = $id;
            $checkroute = false;
        }
        // test case code end
        $storeData = getStoreDataByUserId($userId);
        if (!empty($storeData)) {
            $userData = getUserById($userId);
            //  echo '$storeData: <pre>' .print_r($storeData,true). '</pre>'; die;
            $totalcompletedOrders = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "completed");
            $totalcanceledOrders = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "cancelled");
            $currentMonthComplete = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "completed", $current = "Month");
            $currentMonthPending = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "pending", $current = "Month");
            $totalProducts = $this->_storeobj->getStoreProductCountstore($storeData->store_id, "decode");
            $activeProducts = $this->_storeobj->getStoreProductCountstore($storeData->store_id, "decode", '1');

            $storeDataLastMonth = $this->_reportsobj->getStoreDataWithOrdersUserId($userId, "last_month");
            $storeData7Days = $this->_reportsobj->getStoreDataWithOrdersUserId($userId, "last_7_days");
            $storeDataToday = $this->_reportsobj->getStoreDataWithOrdersUserId($userId, "today");
            if ($storeDataLastMonth) {
                $orders = $storeDataLastMonth['store']->storeOrders;
                $lastMonthSales = $this->_reportsobj->getGrossSales($orders);
            }
            if ($storeData7Days) {
                $orders = $storeData7Days['store']->storeOrders;
                $thisWeekSales = $this->_reportsobj->getGrossSales($orders);
            }
            if ($storeDataToday) {
                $orders = $storeDataToday['store']->storeOrders;
                $todaySales = $this->_reportsobj->getGrossSales($orders);
            }

            //$storeCountry = $this->_locationobj->getAllCountryById($storeData->store_country);
            //$stripeSubscription = $this->_stripeObj->getSubscriptionsByUserId();
            // $connectAccount = $this->_stripeObj->connectAccount();
            //            return view('vendor/store', ['stripeSub' => $stripeSubscription, 'storeData' => $storeData, 'userData' => $userData, 'totalProducts' => $totalProducts, 'currentMonthComplete' => $currentMonthComplete, 'totalcanceledOrders' => $totalcanceledOrders, 'totalcanceledOrders' => $totalcanceledOrders, 'currentMonthPending' => $currentMonthPending, "totalcompletedOrders" => $totalcompletedOrders, 'totalProducts' => $totalProducts, 'connectAccount' => $connectAccount, 'stripeConnectedAccount' => $stripeConnectedAccount]);
            // $res = checkStoreIconSelected();

            // if ($res == false && $checkroute == true) {
            //     return redirect('select-store-icon');
            // }

            // if ($this->isMobileDevice()) {
            //     return view('Store/mobile/store-dashboard', ['storeData' => $storeData, 'userData' => $userData, 'totalProducts' => $totalProducts, 'currentMonthComplete' => $currentMonthComplete, 'totalcanceledOrders' => $totalcanceledOrders, 'totalcanceledOrders' => $totalcanceledOrders, 'currentMonthPending' => $currentMonthPending, "totalcompletedOrders" => $totalcompletedOrders, 'totalProducts' => $totalProducts, 'activeProducts' => $activeProducts]);
            // } else {
           
                return view('Store/store', ['storeData' => $storeData, 'userData' => $userData, 'totalProducts' => $totalProducts, 'currentMonthComplete' => $currentMonthComplete, 'totalcanceledOrders' => $totalcanceledOrders, 'totalcanceledOrders' => $totalcanceledOrders, 'currentMonthPending' => $currentMonthPending, "totalcompletedOrders" => $totalcompletedOrders, 'totalProducts' => $totalProducts, 'activeProducts' => $activeProducts, 'lastMonthSales' => $lastMonthSales, 'thisWeekSales' => $thisWeekSales, 'todaySales' => $todaySales]);
            // }
        } else {
            return '<h2>You dont have any store</h2>';
        }
    }

    public function Payments()
    {
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId);
        if (!empty($storeData)) {
            $userData = getUserById($userId);
            $totalcompletedOrders = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "completed");
            $totalcanceledOrders = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "cancelled");
            $currentMonthComplete = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "completed", $current = "Month");
            $currentMonthPending = $this->_storeobj->countTotalStoreOrders($storeData->store_id, $status = "pending", $current = "Month");
            $totalProducts = $this->_storeobj->getStoreProductCount($storeData->store_id, "decode");
            //$storeCountry = $this->_locationobj->getAllCountryById($storeData->store_country);
            $stripeConnectedAccount = $this->_stripeObj->getConnectedAccountByUserId();
            // echo 'Data: <pre>' .print_r($stripeConnectedAccount,true). '</pre>'; die();
            $stripeSubscription = $this->_stripeObj->getSubscriptionsByUserId();

                return view('Store/payments', ['storeData' => $storeData,'stripeConnectedAccount' => $stripeConnectedAccount]);
        } else {
            return '<h2>You dont have any store</h2>';
        }
    }

    public function getPaidAddress()
    {

        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            $result = $this->_buyerobj->UpdateWalletData($postedData);
            if ($result == 1) {
                $this->_request->session()->flash('updatesuccessmessage', 'User Wallet Address has been updated successfully');
                return redirect()->back();
            } else if ($result == 0) {

                $this->_request->session()->flash('updateerrormessage', 'Please enter correct Wallet Address');
                return redirect()->back();
            } else if ($result == 2) {

                $this->_request->session()->flash('updateerrormessage', 'Please enter correct Wallet Address');
                return redirect()->back();
            }
        }
        $userData = $this->_buyerobj->getstoreData();
        //   echo '$userData: <pre>' .print_r($userData,true). '</pre>'; die;
        // if ($this->isMobileDevice()) {
        //     return view('Store/mobile/getpaid', ['userData' => $userData]);
        // } else {
            return view('Store/getpaid', ['userData' => $userData]);
        // }
    }

    public function addStore()
    {
        
        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            // echo 'Data: <pre>' .print_r($postedData,true). '</pre>'; die();
            $this->_storeobj->saveStore($postedData);
            $this->_request->session()->flash('SuccessMessage', 'Company profile  has been created successfully.');
            return redirect('/add-product');
        }
        $stripeConnectedAccount = $this->_stripeObj->getConnectedAccountByUserId();
        $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id'] : '';
        $useRole =  getUserroles($userId);

    //    echo 'Data: <pre>' .print_r($useRole,true). '</pre>'; die();
        if (Session::has('userData')) {
            $storeData = getStoreDataByUserId($userId);
            $storeData = $this->objToArray($storeData);
             //echo '$storeData: <pre>' .print_r($storeData,true). '</pre>'; die();
            // if($useRole[0]['role-id'] == '4'){
            //     return redirect('/store-name'); 
            // }else{
            //     return redirect('/'); 
            // }
             if (empty($storeData)) {
                return redirect('/store-name'); 
            //     $res = checkStoreIconSelected();
            //     if ($stripeConnectedAccount == true && $res == false) {
            //         return redirect('select-store-icon');
            //     } else {
            //         return redirect('/store/edit');
            //     }
            } else {
            // if ($this->isMobileDevice()) {
            //     return view('Store/mobile/createstore', ['actionUrl' => 'store/create']);
            // } else {
                return view('Store/createstore', ['actionUrl' => 'store/create', 'storeData' => $storeData]);
            }
            // }
        }else{
            
            return redirect('/login'); 
        }
    }

    public function editStore()
    {
    
        // if ($this->_request->post()) {
        //     dd('called');
        //     $postedData = $this->_request->input();
        //     $test= $this->_storeobj->updateStore($postedData);
        //     $this->_request->session()->flash('SuccessMessage', 'Company profile data  has been updated successfully.');
        //     return redirect()->back();
        // }
        $stripeConnectedAccount = $this->_stripeObj->getConnectedAccountByUserId();
        // $res = checkStoreIconSelected();
        // if ($stripeConnectedAccount == true && $res == false) {
        //     return redirect('select-store-icon');
        // } else {
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId);
        $storeData = $this->objToArray($storeData);
        // if ($this->isMobileDevice()) {
        //     return view('Store/mobile/createstore', ['actionUrl' => 'store/edit', 'storeData' => $storeData]);
        // } else {
            return view('Store/createstore', ['actionUrl' => 'store/edit', 'storeData' => $storeData]);
        // }
        // }
    }
    public function updateEditStore()
    {
        $postedData = $this->_request->input();
        $test = $this->_storeobj->updateStore($postedData);
        $this->_request->session()->flash('SuccessMessage', 'Company profile data  has been updated successfully.');
        // return redirect()->back();
        return redirect('/store-dashboard');
    }
    public function fetchPackages(){
        $featureProductPackage = FeatureProductPackage::get();
        return response()->json(['status'=>true,'packages'=>$featureProductPackage]);
    }
    public function storeProducts($id = "")
    {
        $data = $this->_request->all();
        $userId = getCurrentUserId();
        $role = getCurrentUserRoleId();
        $checkRoute = true;
        // for test case code start
        if ($userId == "") {
            $userId = $id;
            $checkRoute = false;
        }

        $storeData = getStoreDataByUserId($userId);
        $storeId = $storeData->store_id;
        // echo 'data: <pre>' .print_r($data,true). '</pre>';
        // echo 'storeId: <pre>' .print_r($storeId,true). '</pre>'; die;
        // $storeCampaigns = $this->_campObj->getStoreCampaign($storeId);

        $results = $this->_storeobj->getProductsByStore($storeId, $data);

        // $supportCampaigns = $this->_campObj->getSupportingCampaign($storeId);
        $userData = getUserById($userId);
        $userAvailableCredits = $userData->user_credits;
        $prodcutFeaturePrice = $this->_storeobj->getFeaturePricing();
        $stripeConnectedAccount = $this->_stripeObj->getConnectedAccountByUserId();
        $res = checkStoreIconSelected();
        $featureProductPackage = FeatureProductPackage::get();
        //echo 'Data: <pre>' .print_r($featureProductPackage,true). '</pre>';
        // if ($stripeConnectedAccount == true && $res == false && $checkRoute == true) {
        //     return redirect('select-store-icon');
        // }
        $query = DB::table('products')
            ->where('user_id', $userId)
            ->select('product_id', 'is_feature');
        $Dataquery = $query->get();
        if (!empty($Dataquery)) {
            $featureData = $this->objToArray($Dataquery);
        }
        // if ($this->isMobileDevice()) {
        //     return view('Store/mobile/storeproducts', ['results' => $results, 'prodcutFeaturePrice' => $prodcutFeaturePrice, 'userAvailableCredits' => $userAvailableCredits, 'storeCampaigns' => $storeCampaigns, 'supportCampaigns' => $supportCampaigns, 'featureProductPackage' => $featureProductPackage, 'storeData' => $storeData]);
        // }
        return view('Store/storeproducts', ['featureData' => $featureData, 'results' => $results, 'prodcutFeaturePrice' => $prodcutFeaturePrice,
         'userAvailableCredits' => $userAvailableCredits,  
          'role' => $role, 'featureProductPackage' => $featureProductPackage, 'storeData' => $storeData]);
    }

    public function getproductsWithAutoComplete()
    {
        return $this->_request;
        if ($this->_request->ajax()) {
            $arr = array();
            $postedData = $this->_request->input();
            $storeIcons = $this->_storeobj->seacrhProductsauto($postedData);
            foreach ($storeIcons as $si) {
                $arr[] = $si->title;
            }
            echo json_encode($arr);
        }
    }

    public function getCampaignProdutsByStore()
    {
        $results = $this->_storeobj->getStoreCamapingProducts();
        // $totalproducts = isset($results)?(count($results)):0;
        // if ($this->isMobileDevice()) {
        //     return view('Store.mobile.running_campaign', ['results' => $results]);
        // } else {
            return view('Store/running_campaign', ['results' => $results]);
        // }
    }

    public function getflashProduct($id = "")
    {
        $campProducts = "";
        $storeCampaigns = "";
        $userId = getCurrentUserId();
        if ($userId == "") {
            $userId = $id;
        }
        $StoreData = getStoreDataByUserId($userId, $status = '1');
        $storeId = !empty($StoreData->store_id) ? $StoreData->store_id : "";
        if (!empty($storeId)) {
            $storeCampaigns = $this->_campObj->getStoreCampaign($storeId);
            $data = $this->_request->all();
            $campProducts = $this->_storeobj->getProductsByStore($storeId, $data);
        }
        // if ($this->isMobileDevice()) {
        //     return view('Store.mobile.flashsale', ['results' => $campProducts, 'storeCampaigns' => $storeCampaigns]);
        // } else {
            return view('Store/flashsale', ['results' => $campProducts, 'storeCampaigns' => $storeCampaigns]);
        // }
    }

    public function deleteProduct($productId)
    {
        $res = checkStoreIconSelected();
        $stripeConnectedAccount = $this->_stripeObj->getConnectedAccountByUserId();
        // $res = checkStoreIconSelected();
        // if ($stripeConnectedAccount == true && $res == false) {
        //     return redirect('select-store-icon');
        // }
        // $res = $this->_campObj->checkproductInFlashSale($productId);
        // if ($res == true) {
            $this->_storeobj->delProductById($productId);
            $this->_request->session()->flash('SuccessMessage', 'Product has been deleted successfully.');
        // } else {
        //     $this->_request->session()->flash('errorMessage', 'This product is currently enabled for flash sale and cannot be deleted. Please choose another one and try again.');
        // }

        return redirect()->back();
    }

    public function ChangeStatus($productId, $status)
    {
        $stripeConnectedAccount = $this->_stripeObj->getConnectedAccountByUserId();
        $res = checkStoreIconSelected();
        // if ($stripeConnectedAccount == true && $res == false) {
        //     return redirect('select-store-icon');
        // }
        // $ress = $this->_campObj->checkproductInFlashSale($productId);
        // if ($ress == true) {
            // dd($status);

            $store_id = DB::table('products')->select('store_id')->where('product_id',$productId)->first();
            
            $storeData = getStoreDataById($store_id->store_id);
            $store_super_status = $storeData[0]->super_status;
            $store_status = $storeData[0]->store_status;
            if($store_super_status != '1' || $store_status != '2'){
               
                $this->_request->session()->flash('SuccessMessage', 'Activate your store to update product');
                return redirect()->back();
            }
            $result = $this->_storeobj->ChangeStatusByid($productId, $status);
            if ($result) {
                $this->_request->session()->flash('SuccessMessage', 'Product status changed successfully.');
            } else {
                $this->_request->session()->flash('ErrorMessage', 'Product Status is not changed please contact to technical dept.');
            }
        // } else {
        //     $this->_request->session()->flash('errorMessage', 'This product is currently enabled for flash sale and cannot be deactivated. Please choose another one and try again.');
        // }
        return redirect()->back();
    }

    public function uploadBrandLogo()
    {
        $data = array();
        $img = str_replace('data:image/png;base64,', '', $this->_request->input('ImageData'));
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        
        $fileName = $this->_productobj->generateRandomString(15) . '.png';
        // dd($fileName);
        $fileName = 'cropImage--_--' . $this->_productobj->generateRandomString(15) . '.png';
        $destinationPath = public_path() . '/uploads/temp';
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0775, true);
        }
        
        $file = $destinationPath . '/' . $fileName;
        file_put_contents($file, $data);
        $attachmentId = $this->_productobj->getAttachmentId($fileName, 'image', true, false);
       

        $arr['id'] = $attachmentId;
        $arr['image'] = getAttachmentUrlById($attachmentId, 'media', '');
        //dd($arr);
        echo json_encode($arr);

        //  dd($this->_request);
//         if ($this->_request->hasFile('file')) {
//             $imageObj = $this->_request->file('file');
//             dd($imageObj);
//             if ($imageObj) {
//                 $fileName = $this->_productobj->uploadTempFiles($imageObj, 'file', '');
//                 $attachmentId = $this->_productobj->getAttachmentId($fileName['fileName'][0], 'image', true, false);
//                 $data['id'] = $attachmentId;
//                 $data['image'] = getAttachmentUrlById($attachmentId, 'media', '');
//                 dd($data);
//                 return $data;

// //                }
//             }
//         }
    }

    public function getStatesofCountry()
    {

        if ($this->_request->ajax()) {
            $countryId = $this->_request->input('country_id');
            $states = $this->_locationobj->getStateByCountryCode($countryId);

            $stateHtml = $this->builtHtmlforStates($states);
            return $stateHtml;
        }
    }

    public function builtHtmlforStates($data)
    {
        $html = "<option value=''>Please select</option>";
        if (count($data) > 0) {
            foreach ($data as $d) {
                $html .= '<option value="' . $d['state_id'] . '">' . $d['state_name'] . '</option>';
            }
        }
        return $html;
    }

    public function activateStore()
    {
       
        try {
        if ($this->_request->ajax()) {
            $posedData = $this->_request->input();
            $results = $this->_storeobj->storeStatusChange($posedData);
           if ($results['status']) {
            return response()->json([
                'status' => true,
                'message' => $results['message'],
                'data' => $results['message'], 
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => $results['message'],
            ]);
        }
            // $this->_storeobj->changeStoreStatus($posedData);
        }
    } catch (\Throwable $th) {
       echo $th->getMessage();
    }
    }

    public function storeDeactivate()
    {
        return view('Store/store-deactivate');
    }

    public function chat()
    {
        $users = User::all();
        return view('messages/users', compact('users'));
    }
    // Store Banner 
    public function storeBannerImage(Request $request)
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'file_upload_banner.*' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validate each uploaded file individually
            ]
        );
    
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->errors()
            ], 422);
        }
    
        try {
            $attachmentIds = [];
    
            foreach ($this->_request->file('file_upload_banner') as $file) {
                $originalFilename = $this->_productobj->generateRandomString(15) . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads/media');
    
                $pathInfo = pathinfo($originalFilename);
                $filename = $pathInfo['filename'];
                $extension = $pathInfo['extension'];
    
                $file->move($destinationPath, $originalFilename);
    
                $image = new Attachments();
                $image->fileName = $filename;
                $image->fileExtension = $extension;
                $image->save();
    
                $attachmentId = $image->attachment_id;
                $attachmentIds[] = $attachmentId;
                
            }
            $arr = [];
    
            foreach ($attachmentIds as $attachmentId) {
                $imageData['id'] = $attachmentId;
                $imageData['image'] = getAttachmentUrlByIdMedia($attachmentId, 'media', '');
                $arr[] = $imageData;
            }
            return response()->json([
                'status' => true,
                'message' => 'Upload Successful',
                'data' => serialize($attachmentIds),
                'imageUrl' => $arr,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    
    // Update Store Banner when create new store
    public function updateBannerImageCreate(Request $request)
    {
        
        
        DB::beginTransaction();
        try {

            $bannerIds = unserialize($request->serilize_images);
            
            // dd($bannerIds);
            $myimage = $request->file('update_upload_banner');
            $destinationPath = public_path('uploads/media');
            $originalFilename = $this->_productobj->generateRandomString(15) . '_' . $myimage->getClientOriginalName();
            
            $pathInfo = pathinfo($originalFilename);
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];
            
            $myimage->move($destinationPath, $originalFilename);
            
            if ($myimage) {
                $image = new Attachments();
                $image->fileName = $filename;
                $image->fileExtension = $extension;
                $image->save();
                $attachmentId = $image->attachment_id;
                
                // Add the new image ID to the $bannerIds array
                $bannerIds[] = $attachmentId;
                DB::table('attachment')->where('attachment_id', $request->input('imageID'))->delete();
                 // Find and remove the image ID from the $bannerIds array
                $index = array_search($request->input('imageID'), $bannerIds);
                if ($index !== false) {
                    unset($bannerIds[$index]);
                }
                $updatedBannerSerialize = serialize($bannerIds);
                // dd($updatedBannerSerialize);
                $arr['id'] = $attachmentId;
                $arr['image'] = getAttachmentUrlByIdMedia($attachmentId, 'media', '');
                $arr['serilize_images'] = $updatedBannerSerialize;

                 DB::commit();
         
    
            return response()->json([
                'status' => true,
                'message' => 'Image Updated Successful',
                'data' => $arr,
            ], 200);
        }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    
    

    public function storeTemplates(Request $req) {
        $default_view = $req->input('chacker');
        $alldata = $req->all();
        $attachmentId1 = (null);
        $attachmentId2 = (null);
        $attachmentId3 = (null);

        if (isset($alldata['banner1_img'])) {
            $attachmentId1 = $alldata['banner1_img'];
        }
        if ($req->hasfile('banner1')) {

            $imageObj = array($req->file('banner1'));

            if ($imageObj) {
                $fileName = $this->_productobj->uploadTempFiles($imageObj, 'file', '');

                $attachmentId1 = $this->_productobj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

                //                }
            }
        }

        if (isset($alldata['banner2_img'])) {
            $attachmentId2 = $alldata['banner2_img'];
        }
        if ($req->hasfile('banner2')) {

            $imageObj = array($req->file('banner2'));

            if ($imageObj) {
                $fileName = $this->_productobj->uploadTempFiles($imageObj, 'file', '');

                $attachmentId2 = $this->_productobj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

                //                }
            }
        }

        if (isset($alldata['banner3_img'])) {
            $attachmentId3 = $alldata['banner3_img'];
        }
        if ($req->hasfile('banner3')) {

            $imageObj = array($req->file('banner3'));

            if ($imageObj) {
                $fileName = $this->_productobj->uploadTempFiles($imageObj, 'file', '');

                $attachmentId3 = $this->_productobj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

                //                }
            }
        }
        $message = '';
        $error = '';
        if($default_view == '0'){
            $error = 'You are not select image, please have a look';
        }
        if($default_view == '1'){
            
                // if ($default_view != '') {
                  
                    $res = DB::table('stores')->where('user_id', getCurrentUserId())->update(['default_view' => $default_view, 'image_id1' => $attachmentId1, 'image_id2' => $attachmentId2, 'image_id3' => $attachmentId3]);
                    $message = 'Update Successfully';
        
                    //$selected=$selected[0]->
                
        }
        // if($default_view == 1){
        //     $error = 'Please select at least one image';
        // }
        // elseif($default_view != 0){
        //     $error = 'Please select at least one image';
        // }
        $results = DB::table('stores')->where('user_id', getCurrentUserId())->select('*')->get()->toArray();
        $selected = isset($results[0]->default_view) ? $results[0]->default_view : 1;
        // if ($this->isMobileDevice()) {
        //     return view('Store/mobile/store-template')->with(array('message' => $message, 'error' => $error, 'selected' => $selected, 'results' => $results));
        // } else {
            return view('Store/store-template')->with(array('message' => $message, 'error' => $error, 'selected' => $selected, 'results' => $results));
        // }
    }

    public function chatHistory($id)
    {
        $conversations = Talk::getMessagesByUserId($id, 0, 5);
        $user = '';
        $messages = [];
        if (!$conversations) {
            $user = User::find($id);
        } else {
            $user = $conversations->withUser;
            $messages = $conversations->messages;
        }

        return view('messages.conversations', compact('messages', 'user'));
    }

    public function checkShopAvailablity()
    {
        $postedData = $this->_request->input();
        $result = $this->_storeobj->getStoreByname($postedData);
        if ($result == true) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function objToArray($arr)
    {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    public function storeReports()
    {
        $storeSales = "";
        $products = $this->_productobj->getProductsReports();
        // if ($this->isMobileDevice()) {
        //     return view('Store.mobile.reports', ['products' => $products]);
        // } else {
            if ($this->_request->isMethod('get') && $this->_request->input('type') != "") {

                $postData = $this->_request->input();
                // type not empty product id empty custom empty
                if (isset($postData['type']) && $postData['product_id'] == "" && $postData['date_to'] == "" && $postData['date_from'] == "") {
                    $storeSales = getYearlyOrdersOfStore($postData['type']);
                }
                // type not empty product not empty custom empty
                else if ((isset($postData['type']) && $postData['type'] != "") && (isset($postData['product_id']) && $postData['product_id'] != "") && $postData['date_to'] == "" && $postData['date_from'] == "") {
                    $storeSales = getProductSale($postData['type'], $postData['product_id'], "", "");
                }
                // type not empty product not empty custom not empty
                else if ($postData['type'] != "" && $postData['product_id'] != "" && $postData['date_to'] != "" && $postData['date_from'] != "") {
                    $storeSales = getProductSale("date", $postData['product_id'], $postData['date_to'], $postData['date_from']);
                }
                // type not empty product empty and custom not empty
                else if ($postData['date_to'] != "" && $postData['date_from'] != "" && $postData['product_id'] == "") {
                    $storeSales = getYearlyOrdersOfStore("date", $postData['date_to'], $postData['date_from']);
                }
            } else {
                $storeSales = getYearlyOrdersOfStore("yearly");
            }

            return view('Store.reports', ['products' => $products, 'storeSales' => $storeSales]);
        // }
    }

    public function storeMArketing()
    {
        return view('Store.store-marketing');
    }

//     public function selectStoreIcon() {
//         if ($this->_request->isMethod('post')) {

//             $postedData = $this->_request->input();
//             $result = $this->_storeobj->selectStoreIcon($postedData);
//             if ($result == true) {
//                 $this->_request->session()->flash('SuccessMessage', 'Store icon has been selected successfully.');
//                 return redirect('/store-dashboard');
//             }
// //            } else {
// ////                $this->_request->session()->flash('errorMessage', 'Some thing went wrong.');
// ////                return redirect()->back();
// //            }
//         } else {
//             $userId = Session::get('userData')['user_id'];
//             $storeData = getStoreDataByUserId($userId);
//             if (!empty($storeData->store_icon)) {
//                 $this->_request->session()->flash('errorMessage', 'You have already select store icon.');
//                 return redirect('/store-dashboard');
//             }
//             $result = $this->_storeobj->getStoreIcons();
//             if ($this->isMobileDevice()) {
//                 return view('Store/mobile/store-icon', ['storeIcons' => $result]);
//             } else {
//                 return view('Store.store-icon', ['storeIcons' => $result]);
//             }
//         }
//     }

    public function SearchIcons()
    {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input('search');
            $storeIcons = $this->_storeobj->seacrhStoreIcons($postedData);
            return View('Store.iconsearch', ['storeIcons' => $storeIcons])->render();
        }
    }

    public function SearchIconsWithAutoComplete()
    {
        if ($this->_request->ajax()) {
            $arr = array();
            $postedData = $this->_request->input('term');
            $storeIcons = $this->_storeobj->seacrhStoreIconsauto($postedData);
            foreach ($storeIcons as $si) {
                $arr[] = $si->name;
            }
            echo json_encode($arr);
        }
    }

    public function getCampaign()
    {
        if ($this->_request->isMethod('post')) {
            $resultArray = array();
            $postedData = $this->_request->input();
            $result = $this->_storeobj->addToCampagin($postedData);
            if ($result == true) {
                $this->_request->session()->flash("SuccessMessage", "Campaign has been added succesfully");
            } else {
                return false;
            }
        }
    }

    public function sellerLanding()
    {
        // if ($this->isMobileDevice()) {

        //     return view('Store.mobile.sellerlanding');
        // } else {
            // return view('Store/sellerlanding');
            return view('Store/selling');
        // }
    }

    public function getvariationsOfProduct()
    {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $resutlt = $this->_storeobj->getvariableProductsInfo($postedData);
            return view('Store.productvariationhtml', ['resutlt' => $resutlt])->render();
        }
    }

    public function campaignCollect($id = "")
    {
        $userId = getCurrentUserId();
        if ($userId == "") {
            $userId = $id;
        }
        $storeId = DB::table('stores')->where('user_id', $userId)->where('store_status', '!=', '0')->value('store_id');
        $supportCampaign = $this->_campObj->getSupprtCampaign($storeId);
        $data = $this->_request->all();
        $results = $this->_storeobj->getProductsByStore($storeId, $data);
        // if ($this->isMobileDevice()) {
        //     return view('Store.mobile.activecampaign', ['supportCampaigns' => $supportCampaign, 'results' => $results]);
        // } else {
            return view('Store.activecampaign', ['supportCampaigns' => $supportCampaign, 'results' => $results]);
        // }
    }

    public function abc()
    {
        $query = DB::table('store_campaigns')->select('product_id')->groupBy('product_id')->get();
        //$query=DB::table('store_campaigns')->select('product_id')->whereIn('status',['active','pending','support'])->groupBy('product_id')->get();
//       print_rr(objToArray($query));
        foreach ($query as $q) {
            $query2 = DB::table('products')->where('product_id', $q->product_id)->select('product_id as product_no', 'status')->first();
            print_rr($query2);
            // echo "product id".$query2->product_no." status".$query2->status."<br>";
        }
    }

    public function StoreCamp($productId)
    {
        $query = DB::table('producs')->where('product_id', $productId)->first();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }
    public function storeActivates() {
        $query = Store::where('wallet_address',null)->get();
        foreach($query as $q) {
            $userId =  $q->user_id;
            $store_id =  $q->store_id;
            $this->_storeobj->changeStoreStatus($userId,'0','true');
        }
    }
    public function makeFeatureProduct()
    {
        $postedData = $this->_request->input();
        $postedData = $postedData['data'];
        DB::beginTransaction();
        $packageArray = array(1 => 30, 2 => 60, 3 => 90);
        $startDate = date('Y-m-d');
        $selectpkg = $packageArray[$postedData['package_id']];
        $days = '+'.$selectpkg. 'days';
        $endate = date('Y-m-d',strtotime($days,strtotime($startDate)));
        try {
            $featureProduct = new FeatureProductPackagePurchase();
            $featureProduct->user_id = !empty($postedData['user_id']) ? $postedData['user_id'] : '' ;
            $featureProduct->store_id = !empty($postedData['store_id']) ? $postedData['store_id'] : '';
            $featureProduct->package_id = !empty($postedData['package_id']) ? $postedData['package_id'] : 0;
            $featureProduct->product_id = !empty($postedData['product_id']) ? $postedData['product_id'] :'';
            $featureProduct->start_date = $startDate;
            $featureProduct->end_date = $endate;
            $featureProduct->save();
            Product::where('product_id',$postedData['product_id'])->update(['feature_status'=>1]);
            DB::commit();
        } catch (Exception $e) {
             echo 'Data: <pre>' .print_r($e->error,true). '</pre>';
            DB::rollBack();
        }
    }
    
    public function createSellerName(){
        $userId = getCurrentUserId();
        $store = getStoreInfoByUserid($userId);
        if(isset($store) && $store){
            redirect('store-dashboard');
        }

        if ($this->_request->isMethod('post')) {
            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'store_name' => 'required',
                ]
            );
        
            if ($validateUser->fails()) {
                return  $this->_request->session()->flash('ErrorMessage', 'Please validate your shop name.');
            }
            $postedData = $this->_request->input();
         
            try {
               
            $this->_storeobj->saveStoreName($postedData);
            $this->_request->session()->flash('SuccessMessage', 'Shop Name has been created successfully.');
            return redirect('/store/create');
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
            
        }
        if (Session::has('userData')) {

            $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']: "";
        }else{
           return redirect('/login');
        }
        
        $storeData = getStoreDataByUserId($userId);
        $storeData = $this->objToArray($storeData);
        return view('Store.sellername', ['actionUrl' => 'store-name','storeData' => $storeData]);
    }

    public function editSellerName(){
        if ($this->_request->isMethod('post')) {
            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'store_name' => 'required',
                ]
            );
        
            if ($validateUser->fails()) {
                return  $this->_request->session()->flash('ErrorMessage', 'Please validate your shop name.');
            }
            $postedData = $this->_request->input();
         
            try {
               
            $this->_storeobj->updateStoreName($postedData);
            $this->_request->session()->flash('SuccessMessage', 'Shop Name has been Updated successfully.');
            return redirect('/store/edit');
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
            
        }
        $userId = Session::get('userData')['user_id'];
        $storeData = getStoreDataByUserId($userId);
        $storeData = $this->objToArray($storeData);
        return view('Store.sellername', ['actionUrl' => 'edit/store-name','storeData' => $storeData]);
    }

    
    public function stockYourStore(){
        return view('Store.stockyourstore');
    }

    public function uploadStoreImageRequest()
    {
        $result = [];
        
        if ($this->_request->hasFile('file_upload_banner')) {
            $userId = Session::get('userData')['user_id'];
            $storeInfo = getStoreInfoByUserId($userId);
        
            $bannerSerialize = $storeInfo->banner_image_id;
            $bannerIds = unserialize($bannerSerialize);
            
            if (count($bannerIds) > 5) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have already uploaded 5 images.',
                ]);
            }

            $imageObj = $this->_request->file('file_upload_banner');
        } else {
            $imageObj = null;
        }
        
        $result = $this->_storeobj->uploadProcessStoreImage($imageObj);
        $fileCount = count($result['fileName']); 
        $totalCounter = $fileCount + count($bannerIds);
 
       
        // dd($totalCounter);
        if ($totalCounter > 5) {
            return response()->json([
                'status' => false,
                'message' => 'You can upload a maximum of 5 images.',
            ]);
        }

        if ($result !== null) {
            return response()->json([
                'status' => true,
                'message' => 'Store banner uploaded.',
                'data' => $result,
            ]);
        }
    }
    // Banner Image cropper function
    public function uploadBannerCropImage(Request $request)
    {

        if (!empty($this->_request->input('croppedImage'))) {
          

            $img = str_replace('data:image/png;base64,', '', $this->_request->input('croppedImage'));
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            $fileName = $this->_productobj->generateRandomString(15) . '.png';
            $fileName = 'cropImage--_--' . $this->_productobj->generateRandomString(15) . '.png';
            $destinationPath = public_path() . '/uploads/temp';
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }
            $file = $destinationPath . '/' . $fileName;
            file_put_contents($file, $data);
            // dd($fileName);
            return $fileName;
        }



        // if ($this->_request->isMethod('post')) {
        //     // dd($request->all());
        //     if (!empty($this->_request->input('croppedImage'))) {
                

        //         $img = str_replace('data:image/png;base64,', '', $this->_request->input('croppedImage'));
        //         $img = str_replace(' ', '+', $img);
        //         $data = base64_decode($img);
        //         $fileName = $this->_productobj->generateRandomString(15) . '.png';
        //         $fileName = 'cropImage--_--' . $this->_productobj->generateRandomString(15) . '.png';
        //         $destinationPath = public_path() . '/uploads/temp';
        //         if (!File::exists($destinationPath)) {
        //             File::makeDirectory($destinationPath, 0775, true);
        //         }
        //         $file = $destinationPath . '/' . $fileName;
        //         file_put_contents($file, $data);
        //         $attachmentId = $this->_productobj->getAttachmentId($fileName, 'image', false);
              
        //         $userId = Session::get('userData')['user_id'];
        //         $storeInfo = getStoreInfoByUserId($userId);
        //         $store_id = $storeInfo->store_id;

        //         $imageId = $this->_request->input('image_id');
    
        //         $existingSerializedIds = $storeInfo->banner_image_id;

        //         if (!empty($existingSerializedIds)) {
        //             $bannerIds = unserialize($existingSerializedIds);
                   
                    
        //             // Remove the specific $imageId if it exists
        //             $bannerIds = array_diff($bannerIds, [$imageId]);
   
        //             // Convert $attachmentId to an array if it's not already
        //             if (!is_array($attachmentId)) {
        //                 $attachmentId = [$attachmentId];
        //             }
                   
        //             // Merge the new attachment IDs with the existing list
        //             $newIdsArray = array_merge($bannerIds, $attachmentId);
        //             $newSerializedIds = serialize($newIdsArray);
                  
        //         } else {
        //             // Convert $attachmentId to an array if it's not already
        //             if (!is_array($attachmentId)) {
        //                 $attachmentId = [$attachmentId];
        //             }

        //             $newSerializedIds = serialize($attachmentId);
        //         }
        //         Store::where('store_id', $store_id)->update(['banner_image_id' => $newSerializedIds]);
        //         return $attachmentId;
        //     }
        // }
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

    // public function updateEditStoreBannerImage(Request $request)
    // {
 
    //     $storeBanner = array();
    //     $bannerImages = $this->_request->file('file_upload_banner');
        

    //     if ($bannerImages) {
    //         $userId = Session::get('userData')['user_id'];
    //         $storeInfo = getStoreInfoByUserId($userId);

    //         $existingSerializedIds = $storeInfo->banner_image_id;
    //         $bannerIds = unserialize($existingSerializedIds);

    //         if (is_array($bannerIds)) {
    //             $existingBannerCount = count($bannerIds);
    //         } else {
    //             $existingBannerCount = 0; // Set a default count if $bannerIds is not an array
    //         }

    //         if (is_array($bannerImages)) {
    //             $newBannerCount = count($bannerImages);
    //         } else {
    //             $newBannerCount = 0; // Set a default count if $bannerImages is not an array
    //         }

    //         $totalBannerCount = $existingBannerCount + $newBannerCount;

    //             // dump($totalBannerCount);
    //         if ($totalBannerCount > 6) {

    //             return false;
    //         }

    //         if (count($bannerImages) > 0) {
    //             $destinationPath = public_path() . '/uploads/temp';
    //             if (!File::exists($destinationPath)) {
    //                 File::makeDirectory($destinationPath, 0775, true);
    //             }
    //             foreach ($bannerImages as $image) {
    //                 $OriginalfileName = $image->getClientOriginalName();
    //                 $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
    //                 $fileExtension = $image->getClientOriginalExtension();
    //                 $data = getimagesize($image);
    //                 $width = $data[0];
    //                 $height = $data[1];

    //                 $status = true;
                    
    //                 $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
    //                 $image->move($destinationPath, $fileName);
    //                 $storeBanner['fileName'][] = $fileName;
    //                 $storeBanner['validate'][] = $status;
    //             }
    //         }
    //     }
    //     // echo 'Data: <pre>' .print_r($storeBanner,true). '</pre>'; die();
    //     return $storeBanner;


        public function updateEditStoreBannerImage(Request $request)
    {
        $storeBanner = array();
        $bannerImages = $request->file('file_upload_banner');
    
        if ($bannerImages) {
            $userId = Session::get('userData')['user_id'];
            $storeInfo = getStoreInfoByUserId($userId);
    
            // Deserialize existing banner IDs
            $existingSerializedIds = $storeInfo->banner_image_id;
            $bannerIds = unserialize($existingSerializedIds);
            $bannerIds = is_array($bannerIds) ? $bannerIds : [];
    
            // Count the existing banners
            $existingBannerCount = count($bannerIds);
    
            // Count the new banners
            $newBannerCount = is_array($bannerImages) ? count($bannerImages) : 0;
    
            // Calculate total banner count
            $totalBannerCount = $existingBannerCount + $newBannerCount;
    
            if ($totalBannerCount > 6) {
              return false;
            }
    
            foreach ($bannerImages as $image) {
                $destinationPath = public_path() . '/uploads/temp';
    
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }
    
                $OriginalfileName = $image->getClientOriginalName();
                $nameonly = pathinfo($OriginalfileName, PATHINFO_FILENAME);
                $fileExtension = $image->getClientOriginalExtension();
    
                // Check if the file extension is valid, you can add more extensions as needed
                if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid file extension. Allowed extensions are jpg, jpeg, png, and gif.',
                    ]);
                }
    
                $status = true;
    
                $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                $image->move($destinationPath, $fileName);
                $storeBanner['fileName'][] = $fileName;
                $storeBanner['validate'][] = $status;

            }
    
            
        }
        return $storeBanner;

    }

        // $bannerImages = $request->file('file_upload_banner');
        // $attachmentIds = [];
        // // dd($bannerImages);
        // foreach ($bannerImages as $image) {
        //     // Process each image and save it as an attachment
        //     $data = file_get_contents($image->getRealPath());
        //     $fileName = 'cropImage--_--' . $this->_productobj->generateRandomString(15) . '.png';
        //     $destinationPath = public_path() . '/uploads/temp';

        //     if (!File::exists($destinationPath)) {
        //         File::makeDirectory($destinationPath, 0775, true);
        //     }

        //     $file = $destinationPath . '/' . $fileName;
        //     file_put_contents($file, $data);
           
        //     $attachmentId = $this->_productobj->getAttachmentIdCustom($fileName, 'image', true, false);

        //     $attachmentIds[] = $attachmentId;
        // }

        //     $userId = Session::get('userData')['user_id'];
        //     $storeInfo = getStoreInfoByUserId($userId);
        //     $store_id = $storeInfo->store_id;


        // $existingSerializedIds = $storeInfo->banner_image_id;
        // if(!empty($existingSerializedIds)){
            
        //     $bannerIds = unserialize($existingSerializedIds);
        //     $newIdsArray = array_merge($bannerIds, $attachmentIds);
        //     $newSerializedIds = serialize($newIdsArray);
        // }else{
          
        //     $newSerializedIds = serialize($attachmentIds);
        //     // dd($newSerializedIds);
        // }
        
        // if (!empty($bannerIds) && count($bannerIds) > 5) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'You have already uploaded 5 images.',
        //     ]);
        // }
        // $fileCount = count($attachmentIds);
        // // dd($fileCount); 
        // $dbImageCount = !empty($bannerIds) ? count($bannerIds) : 0;
        // $totalCounter = $fileCount + $dbImageCount;
 
       
        // // dd($totalCounter);
        // if ($totalCounter > 5) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'You can upload a maximum of 5 images.',
        //     ]);
        // }
    
        // // Update the product record with the new serialized IDs
        // Store::where('store_id', $store_id)->update(['banner_image_id' => $newSerializedIds]);

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Store banner uploaded.',
        //     'data' => $attachmentIds,
        // ]);
       
    // }

        //edit Update Store Banner 

        public function updateBannerImage(Request $request)
        {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'update_upload_banner.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                ]
            );
        
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }
            DB::beginTransaction();
            try {
                $storeId = $request->input('storeid');
                $storeData = DB::table('stores')->where('store_id', $storeId)->first();
                $bannerSerialize = $storeData->banner_image_id;
                $bannerIds = unserialize($bannerSerialize);
        
                $filename = $request->input('filename');    
                $destinationPath = public_path() . '/uploads/media/' . $filename;
        
                if (File::exists($destinationPath)) {
                    unlink($destinationPath);
                    DB::table('attachment')->where('attachment_id', $request->input('imageID'))->delete();
        
                    // Find and remove the image ID from the $bannerIds array
                    $index = array_search($request->input('imageID'), $bannerIds);
                    if ($index !== false) {
                        unset($bannerIds[$index]);
                    }
                }
        
                $myimage = $request->file('update_upload_banner');
                $destinationPath = public_path('uploads/media');
                $originalFilename = $this->_productobj->generateRandomString(15) . '_' . $myimage->getClientOriginalName();
        
                $pathInfo = pathinfo($originalFilename);
                $filename = $pathInfo['filename'];
                $extension = $pathInfo['extension'];
                $myimage->move($destinationPath, $originalFilename);
                // dd($originalFilename);
        
                if ($myimage) {
                    $image = new Attachments();
                    $image->fileName = $filename;
                    $image->fileExtension = $extension;
                    $image->save();
                    $attachmentId = $image->attachment_id;
                //  dd($attachmentId);
                    // Add the new image ID to the $bannerIds array
                    $bannerIds[] = $attachmentId;
                }
        
                // Update the serialized array in the stores table
                $updatedBannerSerialize = serialize($bannerIds);
                DB::table('stores')->where('store_id', $storeId)->update(['banner_image_id' => $updatedBannerSerialize]);
     
                $arr['id'] = $attachmentId;
                $arr['image'] = getAttachmentUrlByIdMedia($attachmentId, 'media', '');
                $arr['edit_id'] = $request->input('imageID');
                DB::commit();
        
                return response()->json([
                    'status' => true,
                    'message' => 'Image Updated Successful',
                    'data' => $arr,
                ], 200);
            } catch (\Throwable $th) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }

        public function bulkUpdate() {
            return view('Store.bulk-update');
        }
    
        public function bulkUpdateProducts(Request $request)
        {
            ini_set('max_execution_time', 0);

            try {
                $csvFile = $request->file('csv_file');

                if (!$csvFile || $csvFile->getClientOriginalExtension() !== 'csv') {
                    return response()->json(['message' => 'Invalid file type. Please upload a CSV file.', 'status' => false], 400);
                }

                if (($handle = fopen($csvFile, 'r')) !== false) {
                    $headers = fgetcsv($handle);

                    if (!in_array('Handle', $headers) || !in_array('Title', $headers) || !in_array('Variant Price', $headers) || !in_array('Variant Inventory', $headers)) {
                        return response()->json(['message' => 'Headers must include Handle, Title, Variant Price, and Variant Inventory.', 'status' => false], 400);
                    }

                    DB::beginTransaction();
                    $userId = getCurrentUserId();
                    $errors = []; // To collect detailed errors for frontend.
                    $storeData = Store::where('user_id', $userId)->first();
                    $storeId = $storeData->store_id;
                    while (($data = fgetcsv($handle)) !== false) {
                        $row = array_combine($headers, $data);

                        $row['Variant Price'] = trim($row['Variant Price']);
                        $row['Variant Inventory'] = trim($row['Variant Inventory']);

                        if (($row['Variant Price'] !== '' || $row['Variant Inventory'] !== '')) {
                            if (($row['Variant Price'] !== '' && !isValidPrice($row['Variant Price'])) || 
                                ($row['Variant Inventory'] !== '' && !isValidStock($row['Variant Inventory']))) {
                                $errors[] = "Invalid data for Handle: {$row['Handle']} (Price: {$row['Variant Price']}, Inventory: {$row['Variant Inventory']})";
                                continue;
                            }
                        }
                        

                        $product = Product::where('slug', $row['Handle'])->first();

                        if ($product && $product->user_id == $userId) {
                            if ($product->product_type != 'variant') {
                                $product->price = $row['Variant Price'] !== '' ? $row['Variant Price'] : $product->price;
                                $product->current_stock = $row['Variant Inventory'] !== '' ? $row['Variant Inventory'] : $product->current_stock;
                                $product->save();
                            } else {
                                $productVariations = ProductVariation::where('product_id', $product->product_id)->get();

                                foreach ($productVariations as $variation) {
                                    // Normalize Option1 for comparison
                                    $option1Value = is_string($row['Option1 Value']) ? strtolower($row['Option1 Value']) : $row['Option1 Value'];
                                    $variationOption1 = is_string($variation->option1) ? strtolower($variation->option1) : $variation->option1;
                                
                                    $match = !empty($row['Option1 Value']) && $variationOption1 == $option1Value;
                                
                                    if (!empty($row['Option2 Value'])) {
                                        // Normalize Option2 for comparison
                                        $option2Value = is_string($row['Option2 Value']) ? strtolower($row['Option2 Value']) : $row['Option2 Value'];
                                        $variationOption2 = is_string($variation->option2) ? strtolower($variation->option2) : $variation->option2;
                                
                                        $match = $match && $variationOption2 == $option2Value;
                                    }
                                
                                    if (!empty($row['Option3 Value'])) {
                                        // Normalize Option3 for comparison
                                        $option3Value = is_string($row['Option3 Value']) ? strtolower($row['Option3 Value']) : $row['Option3 Value'];
                                        $variationOption3 = is_string($variation->option3) ? strtolower($variation->option3) : $variation->option3;
                                
                                        $match = $match && $variationOption3 == $option3Value;

                                    }
                                
                                    if ($match) {
                                        $variation->price = $row['Variant Price'] !== '' ? $row['Variant Price'] : $variation->price;
                                        $variation->current_stock = $row['Variant Inventory'] !== '' ? $row['Variant Inventory'] : $variation->current_stock;
                                        $variation->save();
                                    }
                                }
                            }
                        
                            $discount = Discount::where(['store_id' => $storeId, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();

                            if (isset($discount) && !empty($discount)) {
                                if ($discount->discount_value_type == 'percent') {
                                    $discountValue = $product->price * ($discount->discount_value / 100);
                                    $salePrice = $product->price - $discountValue;

                                    if ($salePrice > 0) {
                                        $product->on_sale = true;
                                        $product->sale_price = $salePrice;
                                        $product->discount_id = $discount->id;
                                        $product->discount_percentage = $discount->discount_value;
                                    } else {
                                        $product->sale_price = $product->price;
                                        $product->on_sale = false;
                                    }
                                } elseif ($discount->discount_value_type == 'fixed') {
                                    $salePrice = $product->price - $discount->discount_value;

                                    if ($salePrice > 0) {
                                        $discountPercentage = $product->price > 0 ? round(($discount->discount_value / $product->price) * 100) : 0;
                                        $product->on_sale = true;
                                        $product->sale_price = $salePrice;
                                        $product->discount_percentage = $discountPercentage;
                                        $product->discount_id = $discount->id;
                                    } else {
                                        $product->sale_price = $product->price;
                                        $product->on_sale = false;
                                    }
                                }
                                $product->save();
                            }

                            // Apply discount to variations if the product is a variant
                            if ($product->product_type == 'variant') {
                                $productVariations = ProductVariation::where('product_id', $product->product_id)->get();
                                if (isset($discount) && !empty($discount)) {
                                    foreach ($productVariations as $variation) {
                                        if ($discount->discount_value_type == 'percent') {
                                            $discountValue = $variation->price * ($discount->discount_value / 100);
                                            $salePrice = $variation->price - $discountValue;

                                            if ($salePrice > 0) {
                                                $variation->sale_price = $salePrice;
                                                $variation->discount_percentage = $discount->discount_value;
                                            } else {
                                                $variation->sale_price = $variation->price;
                                            }
                                        } elseif ($discount->discount_value_type == 'fixed') {
                                            $salePrice = $variation->price - $discount->discount_value;

                                            if ($salePrice > 0) {
                                                $discountPercentage = $variation->price > 0 ? round(($discount->discount_value / $variation->price) * 100) : 0;
                                                $variation->sale_price = $salePrice;
                                                $variation->discount_percentage = $discountPercentage;
                                            } else {
                                                $variation->sale_price = $variation->price;
                                            }
                                        }
                                        $variation->save();
                                    }
                                }
                            }
                            $this->_esearch->esMapping($product->product_id);
                        }
                    }

                    if (empty($errors)) {
                        DB::commit();
                        return response()->json(['message' => 'Products updated successfully.', 'status' => true], 200);
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Some updates failed.',
                            'status' => false,
                            'errors' => $errors
                        ], 400);
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage(), 'status' => false], 500);
            }
        }

}
