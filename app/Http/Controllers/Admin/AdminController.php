<?php

namespace App\Http\Controllers\Admin;

use Twilio;
use Session;
use Zendesk;
use App\Models\{category,Store, StoreOrder, User, OrderItems};
use Illuminate\Http\Request;
use App\Http\Controllers\UspsT;
use App\Http\Controllers\Controller;
use App\Repositories\Cart\Cartaccess;
//use App\Http\Controllers\ElasticSearchController;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Herd\Herdaccess;
use App\Repositories\Admin\Adminaccess;
use App\Repositories\Buyer\Buyeraccess;
use Illuminate\Support\Facades\Redirect;
use App\Repositories\search\Searchaccess;
use App\Http\Controllers\ReviewsController;
use App\Repositories\Filters\Filtersaccess;
use App\Repositories\Product\Productaccess;
use App\Repositories\Campaign\Campaignaccess;
use App\Repositories\Location\LocationAccess;
use App\Http\Controllers\ElasticSearchController;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;
use Packages\Core\Models\V2\MallsharkPurchasedPackage;
use Illuminate\Support\Facades\Http;
use App\Models\TikTokVideo;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Models\V2\Product;
use App\Services\FacebookXmlService;

class AdminController extends Controller
{

    private $_request;
    private $_adminobj;
    private $_buyerobj;
    private $_locationobj;
    private $_category;
    private $_esObj;
    private $_esObjAccess;
    private $productObj;
    private $filter_obj;
    private $herdObj;
    private $campaignObj;
    private $reviews;
    private $shiprate;
    private $tiktok;
    //private $productobj;

    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->_adminobj = new Adminaccess();
        $this->_buyerobj = new Buyeraccess();
        $this->_locationobj = new LocationAccess();
        $this->_esObj = new ElasticSearchController();
        $this->_esObjAccess = new Searchaccess();
        $this->_category = new category();
        $this->filter_obj = new Filtersaccess();
        $this->productObj = new Productaccess();
        $this->herdObj = new Herdaccess();
        $this->campaignObj = new Campaignaccess();
        $this->reviews = new ReviewsController();
        $this->shiprate = new UspsT();
        $this->tiktok = new TikTokVideo();
        // $this->productobj = new ReviewsController();
    }

    public function index()
    {
        $unactivatedUser = $this->_adminobj->countActivateAndDeactiveUsers(0);
        $activatedUser = $this->_adminobj->countActivateAndDeactiveUsers(2);
        $unactiveListing = $this->_adminobj->getActiveListing(0);
        $activeListing = $this->_adminobj->getActiveListing(1);
        $allproducts = $this->_adminobj->getAllProducts();
        $allstores = $this->_adminobj->getAllStorescount();
        $alltiktokvideos = $this->_adminobj->getAllTikTokVideosCount();
        return View('admin/admindashboard', ['unactivatedUser' => $unactivatedUser, 'activatedUser' => $activatedUser, 'unactiveListing' => $unactiveListing, 'activeListing' => $activeListing, 'allproducts' => $allproducts, 'allstores' => $allstores, 'alltiktokvideos' => $alltiktokvideos]);
    }

    public function listManagement()
    {
        $data = $this->_request->all();
        $results = $this->_esObj->listSearch($data);
        // $listingresults = $this->_adminobj->getListingData($data);
        return view('admin/listmanagement', ['listingresults' => $results]);
    }

    public function movers()
    {

        return view('admin/movers');
    }

    public function ChangeStatus($productId, $status)
    {
        $result = $this->_adminobj->ChangeStatusByid($productId, $status);
        if ($result) {
            $this->_request->session()->flash('SuccessMessage', 'Add status changed successfully.');
        } else {
            $this->_request->session()->flash('ErrorMessage', 'Add Status is not changed please contact to technical dept.');
        }
        $redirect = url()->previous();
        return redirect($redirect);
    }

    public function productDetail()
    {
        $productShippingPrice = "";
        $availablbeTransporter = "";
        $slug = collect(request()->segments())->last();
        $productDetail = $this->_adminobj->getProductDataBySlug($slug);
        $productTags = getTagsByProductId($productDetail['product_id']);
        if (isset($productTags)) {
            $counterTags = count($productTags);
            $tags = implode(',', $productTags);
        }
        $productCategories = $this->productObj->getCateoriesOfProducts($productDetail['product_id']);
        $reviewsProduct = $this->reviews->get_reviews($productDetail['product_id']);
        $postCategories = $this->productObj->getCateoriesOfProducts($productDetail['product_id']);
        $relatedProduct = $this->productObj->getRelatedProducts($productDetail['product_id'], $productDetail['store_id']);
        $shippingDetails = $this->productObj->getShippingDetail($productDetail['product_id']);
        $location = json_decode(file_get_contents('https://ipinfo.io/json'), TRUE);
        $storeData = getStoreDataByUserId($productDetail['user_id']);
        $storeInfo = getStoreInfoById($productDetail['store_id']);
        $totalProducts = $this->productObj->getStoreProductCount($productDetail['store_id']);
        $storeSales = $this->productObj->countStoreOrders($productDetail['store_id']);
        //$destinationzipcode=$location['postal'];
        $destinationzipcode = '91730';
        $orignZipcode = '91601';
        //$orignZipcode=$storeData['zip_code'];

        $faqs = $this->getProductFaqsById($productDetail['product_id']);
        $superSupporters = $this->herdObj->getSupporters($productDetail['store_id']);
        $productShippingPrice = 0;

        if (!empty($shippingDetails) && $shippingDetails->is_domesticfree == 0) {

            // echo $shippingDetails->shipping_country_id;
            if ($shippingDetails->shipping_country_id == 1) {
                $productShippingPrice = (float) $shippingDetails->flat_shipping_rate + (float) $shippingDetails->domestic_handling_fee * 10;
            } else {
                $productShippingPrice = $this->shiprate->getDpmesticRates($orignZipcode, $destinationzipcode, $productDetail['weight'], $shippingDetails->shipping_service);
                $shippingDetails->domestic_handling_fee;
                $productShippingPrice = (float) $productShippingPrice + (float) $shippingDetails->domestic_handling_fee * 10;
            }
        }
        $cartAccess = new Cartaccess();
        $productInfo['videoUrlSource'] = '';
        if ($productDetail['video_url']) {
            $productDetail['videoUrlSource'] = $this->getYoutubeEmbedUrl($productDetail['video_url']);
        }
        // if ($this->isMobileDevice()) {
        //     return View('admin/mobile/productdetail', ['productDetail' => $productDetail, 'productCategories' => $productCategories, 'productShippingPrice' => $productShippingPrice, 'availablbeTransporter' => $availablbeTransporter, 'postCategories' => $postCategories, 'storeSales' => $storeSales, 'storeInfo' => $storeInfo, 'totalProducts' => $totalProducts, 'faqs' => $faqs, 'superSupporters' => $superSupporters, 'reviews' => $reviewsProduct, 'relatedProduct' => $relatedProduct, 'shippingDetails' => $shippingDetails]);
        // } else {
        return View('admin/productdetail', ['tags' => $tags, 'productDetail' => $productDetail, 'productCategories' => $productCategories, 'productShippingPrice' => $productShippingPrice, 'availablbeTransporter' => $availablbeTransporter, 'postCategories' => $postCategories, 'storeSales' => $storeSales, 'storeInfo' => $storeInfo, 'totalProducts' => $totalProducts, 'faqs' => $faqs, 'superSupporters' => $superSupporters, 'reviews' => $reviewsProduct, 'relatedProduct' => $relatedProduct, 'shippingDetails' => $shippingDetails]);
        // }
    }

    public function listingFilter()
    {
        return view('admin/listing-filters');
    }

    public function deactivate()
    {
        return view('admin/admindeactivate');
    }

    public function listingsOperations()
    {
        return view('admin/listing-operations');
    }

    public function allUsers()
    {
        $getData = $this->_request->all();
        // $allUsers = $this->_esObj->userSearch($getData);
        $allUsers = $this->_adminobj->getAllUsers($getData);
        return view('admin/users', ['allUsers' => $allUsers]);
    }

    public function allOrders(Request $request)
    {
        $active = '#home';
        $orderStatus = $request->input('order_status', 'All');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $orderState = $request->input('order_state');

        $query = StoreOrder::with('shippingAddress')->where('charge_status', 'paid');

        // Most selective: Date range
        if (!empty($fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Then by state
        if (!empty($orderState)) {
            $query->whereHas('shippingAddress', function ($q) use ($orderState) {
                $q->where('state', $orderState);
            });
        }

        // Finally, status
        if ($orderStatus && $orderStatus !== 'All') {
            $query->where('order_status', strtolower($orderStatus));
        }

        // Clone the query to get total before pagination
        $totalAmount = (clone $query)->sum('total_amount');

        // Paginate the filtered result
        $orders = $query->orderBy('created_at', 'DESC')->paginate(15)->withQueryString();
      
        $states = getAllStates();
        return View('admin/orderStatus', array(
            'orders_all' => $orders,
            'active' => $active,
            'states' => $states,
            'ordersTotalAmount' => $totalAmount,
        ));
       
    }

    public function getCryptoRefunds(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $orderState = $request->input('order_state');
        $order_id = $request->input('order_id');
        // Base query with filtered refund items
        $query = StoreOrder::with([
            'refundItems' => function ($q) {
                $q->where('is_refund', 0)
                ->where(function ($q) {
                    $q->whereNull('is_reject')->orWhere('is_reject', 0);
                })
                ->where('refund_status', 0);
            },
            'customerWallet',
            'shippingAddress'
        ])
        ->where('payment_method', 'crypto')
        ->where('charge_status','paid');
        // Filters
        if (!empty($fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if (!empty($orderState)) {
            $query->whereHas('shippingAddress', function ($q) use ($orderState) {
                $q->where('state', $orderState);
            });
        }
        if (!empty($order_id)) {
            $query->where('order_id', $order_id);
        }
        // Paginate
        $orders = $query->orderBy('created_at', 'DESC')->paginate(15)->withQueryString();

        // Add total_refund property to each order for display
        foreach ($orders as $order) {
            $order->total_refund = $order->refundItems->sum(function ($item) {
                return ($item->price ?? 0) + ($item->shipping_amount ?? 0);
            });
        }

        $states = getAllStates();

        return view('admin.crypto-orders', [
            'orders_all' => $orders,
            'states' => $states
        ]);
    }


    public function OrderDetailsAdmin($order_id) {
        $order = DB::table('store_orders')->where('charge_status','paid')->where("order_id", $order_id)->first();

        $order = getStoreOrder($order_id);
        if($order){

            $orderStatus = $order->order_status;
 
            $all = $this->_buyerobj->getOrderDetailsNew($order_id);

            if ($all) {
                    return view('admin.orderDetails', array(
                        'orders_all' => $all, 'status' => $orderStatus
                    ));  
            }else{
                return view('error.404page');
            } 
        }else{
            return view('error.404page');
        }
    }

    public function manageCategories(Request $request)
    {
        // $alldata = $request->all();
        $alldata = $_GET;
        if (isset($alldata['remove_cat']) && $alldata['remove_cat'] != '') {
            $category = $this->_category->find($alldata['remove_cat']);
            // echo 'Data: <pre>' .print_r($category,true). '</pre>'; die();

            $category->delete();
            return Redirect::back()->with('message_success', 'Category is deleted successully');
        }
        $update_id = $request->input('cat_id');
        $records_count = array();
        if ($update_id != '') {
            $records_count = $this->_category->where('id', '=', $update_id)->get()->toArray();
        }

        return view('admin/categories', ['alldata' => $alldata, 'updated_category' => $records_count]);
    }

    public function userChangeStatus($userId, $status)
    {
        $result = $this->_adminobj->udpdateStatus($userId, $status);
        if ($result) {
            $this->_request->session()->flash('SuccessMessage', 'User status changed successfully.');
        } else {
            $this->_request->session()->flash('ErrorMessage', 'Something went wrong.');
        }
        $redirect = url()->previous();
        return redirect($redirect);
    }

    public function updateUserRole()
    {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $res = $this->_adminobj->assignRoles($postedData);
            if ($res == "error") {
                echo 'error';
            } else {
                echo 'success';
            }
        }
    }

    public function userInfo($userId)
    {
        if ($this->_request->isMethod('post')) {
            
            $postedData = $this->_request->input();
            $result = $this->_adminobj->updateuserProfile($postedData, $userId);
            if ($result == true) {
                $this->_request->session()->flash('SuccessMessage', 'User information has been updated successfully');
                return redirect('/admin/allusers');
            } else {

                $this->_request->session()->flash('ErrorMessage', 'Some thing went wrong');
                return redirect('admin/allusers');
            }
        }
        $userData = $this->_adminobj->getuserProfile($userId);
        if (!empty($userData['user_country_id'])) {
            $countryCode = $userData['user_country_id'];
            $states = $this->_locationobj->getStateByCountryCode($countryCode);
        } else {
            $states = '';
        }
        return view('admin/userdetail', ['userData' => $userData, 'states' => $states]);
    }
    public function checkSlugUnique(Request $request)
    {
        $slug = $request->input('slug');
        $categoryId = $request->input('category_id');

        if ($categoryId) {
            // If category_id exists, check if any other category has the same slug
            $exists = Category::where('category_slug', $slug)
                ->where('id', '!=', $categoryId) // Exclude current category
                ->exists();
        } else {
            // If category_id doesn't exist, just check if the slug is unique
            $exists = Category::where('category_slug', $slug)->exists();
        }
        return response()->json(['exists' => $exists]);
    }
    public function addCategories(Request $request)
    {
        $alldata = $request->all();
        if (isset($alldata['category_name']) && $alldata['category_name'] != '' && isset($alldata['category_id']) && $alldata['category_id'] != '') {
            if(isset($alldata['category_slug']) && $alldata['category_slug'] != ''){
                $slug = $alldata['category_slug'];
            }else{
                $slug = str_slug($alldata['category_name'], '-');
                $records_count = $this->_category->where('Category_slug', 'LIKE', '%' . str_slug($alldata['category_name'], '-') . '%')->count();
                if ($records_count > 0) {
                    $increamented = $records_count + 1;
                    $slug = $slug . '-' . $increamented;
                }
            }
            if ($alldata['last_parent'] != $alldata['parent_cat']) {
                $related_products = DB::table('product_category')
                    //->where('category_id','=',$alldata['last_parent'])
                    ->where('category_id', '=', $alldata['category_id'])
                    ->select('product_id')
                    ->get()
                    ->toArray();
                $affected_products = array();
                foreach ($related_products as $pro) {
                    $product_id = $pro->product_id;
                    $affected_products[] = $product_id;
                }
                DB::table('product_category')->whereIn('product_id', $affected_products)->delete();
                foreach ($affected_products as $pr) {
                    DB::table('product_category')->insert(
                        ['category_id' => $alldata['category_id'], 'product_id' => $pr]
                    );
                    DB::table('product_category')->insert(
                        ['category_id' => $alldata['parent_cat'], 'product_id' => $pr]
                    );
                }
            }
            $attachmentId = '';
            if (isset($alldata['category_img'])) {
                $attachmentId = $alldata['category_img'];
            }
            if ($request->hasfile('file_name')) {
                $imageObj = array($request->file('file_name'));
                if ($imageObj) {
                    $fileName = $this->productObj->uploadTempFiles($imageObj, 'file', '');
                    $attachmentId = $this->productObj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

                    //                }
                }
            }
            $iconAttachmentId = '';
            if ($request->hasfile('icon_file_name')) {
                $iconImageObj = array($request->file('icon_file_name'));
                if ($iconImageObj) {
                    $iconFileName = $this->productObj->uploadTempFiles($iconImageObj, 'file', '');
                    $iconAttachmentId = $this->productObj->getAttachmentId($iconFileName['fileName'][0], 'image', true, false);

                    //                }
                }
            }
            if ($request->hasfile('category_banner_image')) {
                $imageObj = array($request->file('category_banner_image'));
                if ($imageObj) {
                    $fileName = $this->productObj->uploadTempFiles($imageObj, 'file', '');
                    $banner_image_id = $this->productObj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

                    //                }
                }
            }
            if ($request->hasfile('category_landing_image')) {
                $imageObj = array($request->file('category_landing_image'));
                if ($imageObj) {
                    $fileName = $this->productObj->uploadTempFiles($imageObj, 'file', '');
                    $landing_image_id = $this->productObj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

                    //                }
                }
            }
            // dd($iconAttachmentId);
            if ($attachmentId == '') {
                return Redirect::back()->with('message_error', 'Please Add Category Image');
            }
            $category = $this->_category->find($alldata['category_id']);

            $category->CategoryTitle = $alldata['category_name'];
            $category->description = $alldata['description'];
            $category->meta_title = $alldata['meta_title'];
            $category->meta_description = $alldata['meta_description'];
            $category->category_heading = $alldata['category_heading'];
            $category->Category_slug = $slug;
            $category->parentCategoryID = $alldata['parent_cat'];
            $category->image_id = $attachmentId;
            if (isset($iconAttachmentId) &&  !empty($iconAttachmentId)) {

                $category->icon_id = $iconAttachmentId;
            }
            if (isset($landing_image_id) &&  !empty($landing_image_id)) {
                $category->category_landing_image = $landing_image_id;
            }
            if (isset($banner_image_id) &&  !empty($banner_image_id)) {
                $category->category_banner_image = $banner_image_id;
            }
            $category->save();
            // $this->_esObj->esMApping();
            return Redirect::back()->with('message_success', 'Category is updated successully');
        }

        if (isset($alldata['category_name']) && $alldata['category_name'] != '') {

            if(isset($alldata['category_slug']) && $alldata['category_slug'] != ''){
                $slug = $alldata['category_slug'];
            }else{
                $slug = str_slug($alldata['category_name'], '-');
                $records_count = $this->_category->where('Category_slug', 'LIKE', '%' . str_slug($alldata['category_name'], '-') . '%')->count();
            if ($records_count > 0) {
                $increamented = $records_count + 1;
                $slug = $slug . '-' . $increamented;
            }
            }
            
            $data = array();
            $attachmentId = '';
            if ($request->hasfile('file_name')) {

                $imageObj = array($request->file('file_name'));

                if ($imageObj) {
                    $fileName = $this->productObj->uploadTempFiles($imageObj, 'file', '');
                    $attachmentId = $this->productObj->getAttachmentId($fileName['fileName'][0], 'image', true, false);
                    //                }
                }
            } else {
                return Redirect::back()->with('message_error', 'Please Add Category Image');
            }
            $iconAttachmentId = '';
            if ($request->hasfile('icon_file_name')) {
                $iconImageObj = array($request->file('icon_file_name'));
                if ($iconImageObj) {
                    $iconFileName = $this->productObj->uploadTempFiles($iconImageObj, 'file', '');
                    $iconAttachmentId = $this->productObj->getAttachmentId($iconFileName['fileName'][0], 'image', true, false);

                    //                }
                }
            }
            if ($request->hasfile('category_banner_image')) {
                $imageObj = array($request->file('category_banner_image'));
                if ($imageObj) {
                    $fileName = $this->productObj->uploadTempFiles($imageObj, 'file', '');
                    $banner_image_id = $this->productObj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

                    //                }
                }
            }
            if ($request->hasfile('category_landing_image')) {
                $imageObj = array($request->file('category_landing_image'));
                if ($imageObj) {
                    $fileName = $this->productObj->uploadTempFiles($imageObj, 'file', '');
                    $landing_image_id = $this->productObj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

                    //                }
                }
            }
            $this->_category->CategoryTitle = $alldata['category_name'];
            $this->_category->description = $alldata['description'];
           
            $this->_category->meta_title = $alldata['meta_title'];
            $this->_category->meta_description = $alldata['meta_description'];
            $this->_category->category_heading = $alldata['category_heading'];
            $this->_category->Category_slug = $slug;
            $this->_category->parentCategoryID = $alldata['parent_cat'];
            $this->_category->image_id = $attachmentId;
            if (isset($iconAttachmentId) &&  !empty($iconAttachmentId)) {

                $this->_category->icon_id = $iconAttachmentId;
            }
            $this->_category->save();
            if (isset($landing_image_id) &&  !empty($landing_image_id)) {
                $this->_category->category_landing_image = $landing_image_id;
            }
            if (isset($banner_image_id) &&  !empty($banner_image_id)) {
                $this->_category->category_banner_image = $banner_image_id;
            }
            return Redirect::back()->with('message_success', 'Category is added successully');
        }
        return Redirect::back()->with('message_error', 'Please Add Category Name');
    }
    public function changeCategoryStatus(Request $request)
    {
        $categoryId = $request->input('category_id');

        // Fetch the category
        $childHasProducts = false;
        $hasProducts = false;
        $category = Category::find($categoryId);
        // dd($category);
        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Category not found.']);
        }
        if ($request->status == 0) {
            if ($category->parentCategoryID == 0) {
                $childs = Category::where('parentCategoryID', $category->id)->get();
                if ($childs->count() > 0) {
                    foreach ($childs as $child) {
                        if ($child->products()->exists()) {
                            $childHasProducts = true;
                            break;
                        }
                    }
                }
            } else {
                $hasProducts = $category->products()->exists();
            }
            if ($hasProducts || $childHasProducts) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This category or one of its child categories has products. Deactivation is not allowed.'
                ]);
            } else {
                if ($category->parentCategoryID == 0) {
                    $childs = Category::where('parentCategoryID', $category->id)->get();
                    if ($childs->count() > 0) {
                        foreach ($childs as $child) {
                            $child->update(['status' => '0']);
                        }
                    }
                }
                // dd($category);
                $category->update(['status' => '0']);
                return response()->json(['status' => 'success', 'message' => 'Category deactivated successfully.']);
            }
        } else {
            if ($category->parentCategoryID == 0) {
                $childs = Category::where('parentCategoryID', $category->id)->get();
                if ($childs->count() > 0) {
                    foreach ($childs as $child) {
                        $child->update(['status' => '1']);
                    }
                }
            }
            $category->update(['status' => '1']); // Assuming 'status' column
            // dd($category);
            return response()->json(['status' => 'success', 'message' => 'Category Activated successfully.']);
        }
    }
    public function ticketList()
    {
        return view('admin/tickets');
    }

    public function ticketDetail()
    {
        return view('admin/ticket-detail');
    }

    public function emailTemplate()
    {
        return view('admin/email-template');
    }
public function shopifyCsv()
  {
    $foundError = false;
    $store = new Store();
    $userID = Session::get('userData')['user_id'];
    $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
    $store_id = $storeID->store_id;
    $storeData = getStoreDataById($store_id);

    $stripe = 0;
    if (count($storeData) > 0) {

      $stripe = $storeData[0]->stripe;
    }
    return view('admin.csv', compact('stripe', 'foundError'));
  }

  public function importCsv(Request $request)
  {
    // dd($request->all());
      // Validate and upload the CSV file
      $request->validate([
          'csv_file' => 'required|file|mimes:csv,txt',
      ]);
  
      // Path to store the uploaded file temporarily
      $filePath = $request->file('csv_file')->getRealPath();
  
      // Open and read the CSV file
      if (($handle = fopen($filePath, 'r')) !== false) {
          // Skip the header row
          fgetcsv($handle, 1000, ',');
  
          // Loop through each row in the CSV file
          while (($data = fgetcsv($handle, 1000, ',')) !== false) {
       
              // Retrieve product_id and slug from CSV row
              $product_id = $data[0];
              $title = $data[1]; // This can be ignored if you only update the slug
              $slug = $data[2];
  
              // Update only the 'slug' of the matching product in the database
              DB::table('products')
                  ->where('product_id', $product_id)
                  ->update(['slug' => $slug]);
          }
  
          // Close the file after reading
          fclose($handle);
      }
  
      return redirect()->back()->with('success', 'CSV data has been imported and updated successfully.');
  }
    public function storeHome($storeSlug, Request $request)
    {
        
        if (!empty($storeSlug)) {
            $res = DB::table('stores')->where('store_slug', '=', $storeSlug)->get()->toArray();
            
            //print_rr($res);
            if (count($res) > 0) {

                $store_id = $res[0]->store_id;

                $user_id = $res[0]->user_id;
                if ($store_id != '' && $user_id != '') {
                    $all_filters = $request->all();
                    if (empty($all_filters)) {
                        $all_filters = array();
                    } 
                    
                     $productResults = $this->_esObj->searchDocumentStore($all_filters, $store_id);  
                    
                     $products = $this->_esObj->searchFilterDocumentStore($all_filters,$store_id); 
                        
                                $filter = array();
                
                                $filter100 = false; 
                                $filter_25_to_50 = false; 
                                $filter_50_to_100 = false; 
                                $filter_1_to_25 = false; 
                                $category_array = [];
                                $parent_array = [];  // Array to store all parent categories
                                if(isset($products['records']) && count($products['records'] > 0)){
                                    foreach($products['records'] as $product){
                                
                                        if (isset($product['_source']['categories']) && is_array($product['_source']['categories'])) {
                    
                    
                                            if (!$filter100) {
                                                if($product['_source']['on_sale'] == 1){
                                                    if($product['_source']['sale_price'] >= 100){
                                                        $filter100 = true;
                                                    }
                                                }else{
                                                    if($product['_source']['price'] >= 100){
                                                        $filter100 = true;
                                                    }
                                                }
                                               
                                            }
                                            if (!$filter_1_to_25) {
                                                if($product['_source']['on_sale'] == 1){
                                                    if($product['_source']['sale_price'] >= 1 && $product['_source']['sale_price'] <= 25){
                                                        $filter_1_to_25 = true;
                                                    }
                                                }else{
                                                    if($product['_source']['price'] >= 1 && $product['_source']['price'] <= 25){
                                                        $filter_1_to_25 = true;
                                                    }
                                                }
                                               
                                            }
                                           
                                            if (!$filter_25_to_50) {
                                                if($product['_source']['on_sale'] == 1){
                                                    if($product['_source']['sale_price'] > 25 && $product['_source']['sale_price'] <= 50){
                                                        $filter_25_to_50 = true;
                                                    }
                                                }else{
                                                    if($product['_source']['price'] > 25 && $product['_source']['price'] <= 50){
                                                        $filter_25_to_50 = true;
                                                    }
                                                }
                                               
                                            }
                                            
                                           
                                            if (!$filter_50_to_100) {
                                                if($product['_source']['on_sale'] == 1){
                                                    if($product['_source']['sale_price'] > 50 && $product['_source']['sale_price'] < 100){
                                                        $filter_50_to_100 = true;
                                                    }
                                                }else{
                                                    if($product['_source']['price'] > 50 && $product['_source']['price'] < 100){
                                                        $filter_50_to_100 = true;
                                                    }
                                                }
                                               
                                            } 
                                          
                                            $filter['filter100'] = $filter100;
                                            $filter['filter_1_to_25'] = $filter_1_to_25;
                                            $filter['filter_25_to_50'] = $filter_25_to_50;
                                            $filter['filter_50_to_100'] = $filter_50_to_100;
                            
                                            
                                            // Loop through the categories of the product
                                            //  dd($product['_source']['categories']);
                                            foreach ($product['_source']['categories'] as $category) {
                                            
                                                $parentCategory = getChildCategoryParent($category); // Retrieve parent category if exists
                                                
                                                // Case: No parent category -> This category is a parent itself
                                                if(!$parentCategory){
                                                    // Check if this parent category already exists in the array
                                                    $found = false;
                                                    foreach ($category_array as &$parent) {
                                                        if (isset($parent[$category])) {
                                                            $found = true;
                                                            break;
                                                        }
                                                    }
                                    
                                                    // If the parent category is not already in the array, add it
                                                    if (!$found) {
                                                        $category_array[] = [
                                                            $category => ['child' => []] // Initialize parent with an empty 'child' array
                                                        ];
                                                    }
                                                } 
                                                // Case: Parent category exists -> Add this category as a child of the parent
                                                else {
                                                    $parentSlug = $parentCategory->category_slug; // Assuming the slug is the unique identifier
                                    
                                                    // Check if the parent category already exists in the array
                                                    $found = false;
                                                    foreach ($category_array as &$parent) {
                                                        if (isset($parent[$parentSlug])) {
                                                            // If the child category is not already in the parent's child array, add it
                                                            if (!in_array($category, $parent[$parentSlug]['child'])) {
                                                                $parent[$parentSlug]['child'][] = $category;
                                                            }
                                                            $found = true;
                                                            break;
                                                        }
                                                    }
                                    
                                                    // If the parent category does not exist, add it with the current category as a child
                                                    if (!$found) {
                                                        $category_array[] = [
                                                            $parentSlug => ['child' => [$category]] // Initialize parent with this category as the first child
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                
                                    
                                }
                    $total_sales = DB::table('order_items')->where('store_id', $store_id)->sum('quantity');

                    $total_items = DB::table('products')->where('store_id', $store_id)->where('status', '1')->count();
                    // $campaign = $this->campaignObj->getSupportingCampaign($store_id);
                    $storeInfo = getStoreInfoById($store_id);
                    $icon_name = '';
                    if (isset($storeInfo[0]->store_icon) && $storeInfo[0]->store_icon != '') {
                        $iconid = $storeInfo[0]->store_icon;
                        $icon_detail = DB::table('store_icons')->where('icon_id', $iconid)->select('name')->first();
                        if ($icon_detail) {

                            $icon_name = $icon_detail->name ? $icon_detail->name : "";
                        } else {
                            $icon_name = "";
                        }
                    }
                    $shop_policies = '';
                    if (isset($storeInfo[0]->shop_policies)) {
                        $shop_policies = $storeInfo[0]->shop_policies;
                    }

                    // $superSupporters = $this->herdObj->getSupporters($store_id);
                    $cartProducts = $this->_esObjAccess->getCartAddedProduct();
                    $default_view = $res[0]->default_view;
                    
                    if ($request->ajax()) {
                        if (isset($_GET['loadmore'])) {
                            return view('search.search-ajax-loadmore', ['productResults' => $productResults, 'cartProducts' => $cartProducts, 'storeInfo' => $storeInfo,  'icon_name' => $icon_name]);
                        }
                        $ajax = true;
                        return view('Store.search-ajax', ['productResults' => $productResults, 'cartProducts' => $cartProducts, 'storeInfo' => $storeInfo,  'storeinfo' => $res,'icon_name' => $icon_name ,'categories' => $category_array,'filters' => $filter,'shop_policies' => $shop_policies,'ajax' => $ajax])->render();
                    } else {
                        // dd($productResults);
                        $ajax = false;
                        return view('Store/store-home', [
                            'productResults' => $productResults,
                            'cartProducts' => $cartProducts,
                            'storeinfo' => $res,
                            'total_sales' => $total_sales,
                            'total_items' => $total_items,
                            'storeInfo' => $storeInfo,
                            'shop_policies' => $shop_policies,
                            'icon_name' => $icon_name,
                            'categories' => $category_array,'filters' => $filter,'ajax' => $ajax
                        ]);
                    }
                }
            }
        }
        
        return view('error.404page');
    }



    public function storeHome2()
    {
        return view('admin/store-home2');
    }

    public function storeHome3()
    {
        return view('admin/store-home3');
    }

    public function storeProducts(Request $request)
    {
        $data = $request->all();
        $results = $this->_adminobj->getAllStoreProduct($data);
        $stores = $this->_adminobj->getActiveStores();
        return view('admin/storeproducts', ['results' => $results,'stores' => $stores]);
    }
    public function tiktokVideos(Request $request)
    {
        $data = $request->all();
        $results = $this->_adminobj->getAllTikTokVideos($data);
        return view('admin/tiktokvideos', ['results' => $results]);
    }
    public function facebookFeedMulti(Request $request)
    {
        try {
            $productIds = $request->input('product_ids', []);
            if (!is_array($productIds)) {
                if (is_string($productIds)) {
                    $productIds = array_filter(explode(',', $productIds));
                } else {
                    $productIds = [];
                }
            }

            if (empty($productIds)) {
                return response()->json(['error' => 'No products selected'], 422);
            }

            // Store product IDs in cache (replace existing ones)
            \Cache::forget('facebook_feed_product_ids');
            \Cache::forever('facebook_feed_product_ids', $productIds);

            $status = ProductConstants::STATUS_ACTIVE;
            $products = Product::where('status',$status)->whereIn('product_id', $productIds)->get();

            if ($products->isEmpty()) {
                return response()->json(['error' => 'No products found for provided IDs'], 404);
            }

            $service = new FacebookXmlService();
            $xmlContent = $service->generateXml($products);

            $filename = 'facebook_feed.xml';
            $dir = public_path('analytics');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $filePath = $dir.DIRECTORY_SEPARATOR.$filename;
            file_put_contents($filePath, $xmlContent);

            $url = url('analytics/'.$filename);

            return response()->json(['url' => $url, 'filename' => $filename]);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to generate feed', 'message' => $e->getMessage()], 500);
        }
    }
    public function productBulkUpdate(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'ids' => 'required|string',
            'status' => 'required|in:approve,disapprove'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product_ids = $request->input('ids');
            $status = $request->input('status');
            $product_ids = explode(',', $product_ids);

            // Filter out empty values
            $product_ids = array_filter($product_ids);

            if (empty($product_ids)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No valid product IDs provided'
                ], 400);
            }
            $configStatus = $status == 'approve'
                ? ProductConstants::STATUS_ACTIVE
                : ProductConstants::STATUS_REJECTED;

            $updateQuery = Product::whereIn('product_id', $product_ids);

            if ($status == 'approve') {
                $updateQuery->where('status', '!=', ProductConstants::STATUS_DELETED);
            }

            $updated = $updateQuery->update([
                'status' => $configStatus
            ]);
            if ($status == 'approve' && $updated < count($product_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some products were deleted and are not approved.',
                ], 422);
            }
            // Sync products to Algolia
            if ($status == 'approve') {
                // For approved products, sync them to Algolia
                $products = Product::whereIn('product_id', $product_ids)->get();
                $products->searchable();
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated product(s)",
                ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating products',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function updateFeatureStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'feature_status' => 'required|boolean',
            ]);
            $product = Product::where('product_id', $request->id)
            ->update(['feature_status' => $request->feature_status]);
            return response()->json(['message' => 'Feature status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    public function getStoresAdmin(Request $request)
    {
        $data = $request->all();
        $results = $this->_adminobj->getAllStoresAdmin($data);
        return view('admin/admin-stores', ['results' => $results]);
    }

    public function missionCompleteSellers(Request $request)
    {
        $stores = Store::with('user')
            ->where('sale_mission', 1)
            ->orderByDesc('mission_complete_date')
            ->paginate(50)
            ->withQueryString();

        return view('admin/mission-complete-sellers', [
            'stores' => $stores,
        ]);
    }

    public function adminEnableStore(Request $request)
    {
        try {
            $data = $request->all();
            $results = $this->_adminobj->enableStoreAdmin($data);

            if ($results !== false) {
                return response()->json([
                    'status' => true,
                    'message' => 'Store activated successfully',
                    'data' => $results,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something Went Wrong ',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error activating store',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function adminActivateStore(Request $request)
    {
        try {
            $data = $request->all();
            $results = $this->_adminobj->activateStoreAdmin($data);

            if ($results !== false) {
                return response()->json([
                    'status' => true,
                    'message' => 'Store activated successfully',
                    'data' => $results,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something Went Wrong ',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error activating store',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    public function adminDisableStore(Request $request)
    {
        try {
            $data = $request->all();
            $results = $this->_adminobj->disableStoreAdmin($data);

            if ($results !== false) {
                return response()->json([
                    'status' => true,
                    'message' => 'Store deactivated successfully',
                    'data' => $results,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something Went Wrong ',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error deactivating store',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function adminDeactivateStore(Request $request)
    {
        try {
            $data = $request->all();
            $results = $this->_adminobj->deactiavteStoreAdmin($data);

            if ($results !== false) {
                return response()->json([
                    'status' => true,
                    'message' => 'Store deactivated successfully',
                    'data' => $results,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something Went Wrong ',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error deactivating store',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function adminApprovedStore(Request $request)
    {
        try {
            $data = $request->all();
            $results = $this->_adminobj->approvedStoreAdmin($data);

            if ($results !== false) {
                return response()->json([
                    'status' => true,
                    'message' => 'Store Approved Successfully',
                    'data' => $results,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something Went Wrong ',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error Approved store',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function adminRejectStore(Request $request)
    {
        try {
            $data = $request->all();

            $results = $this->_adminobj->rejectStoreAdmin($data);

            if ($results !== false) {
                return response()->json([
                    'status' => true,
                    'message' => 'Store Rejected Successfully',
                    'data' => $results,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something Went Wrong ',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Error Rejected Store',
                'error' => $th->getMessage(),
            ], 500);
        }
    }



    public function changeProductStatus($productId, $status, $storeStatus)
    {
       
        $parms = array();
        $parms['product_id'] = $productId;
        $parms['status'] = $status;
        if ($storeStatus == 2) {
            $this->_adminobj->ChangeStatusByid($parms);
            $flashMessage = "Product " . $status . "d Successfully";
            $msgStatus = 'SuccessMessage';
        } else {
            $flashMessage = "This Product is Can't Approved Because your this store not active.";
            $msgStatus = 'errorMessage';
        }
        return Redirect::back()->with($msgStatus, $flashMessage);
    }

    public function changeProductStatusWise($productId, $status)
    {

        $parms = array();
        $flashMessage = '';
        // $flashsaleproduct = $this->campaignObj->checkproductInFlashSale($productId);
        // if ($flashsaleproduct == true) {
        $parms['product_id'] = $productId;
        $parms['status'] = $status;

        $flashsaleproduct = $this->_adminobj->ChangeStatusByid($parms);
        if (!$flashsaleproduct) {
            $flashMessage = "This store is disabled please enable this to approve product";
        } else {
            $flashMessage = "Product " . $status . "d Successfully";
        }

        $msgStatus = 'SuccessMessage';


        //     $msgStatus = 'SuccessMessage';
        // } else if ($flashsaleproduct == false) {
        //     $flashMessage = "This product is currently enabled for flash sale and cannot be " . $status . "ed.";
        //     $msgStatus = 'errorMessage';
        // } else {
        //     $flashMessage = "Some thing went wrong please try again.";
        //     $msgStatus = 'errorMessage';
        // }
        $this->_request->session()->flash($msgStatus, $flashMessage);
        return redirect()->back();
    }

    public function getProductFaqsById($productId)
    {
        return $faqs = $this->productObj->getProductFaqsById($productId);
    }

    public function ProductStatusUpdate()
    {
        $message = "";
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            // $flashsaleproduct = $this->campaignObj->checkproductInFlashSale($postedData['product_id']);
            // if ($flashsaleproduct == true) {
            $this->_adminobj->ChangeStatusByid($postedData);
            $message = "success";
            // } else if ($flashsaleproduct == false) {
            //     $message = "flashsale";
            // } else {
            //     $message = "error";
            // }
            return $message;
        }
    }

    public function storeStatusUpdate()
    {
        $message = "";
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $response =  $this->_adminobj->storeChangeStatusByid($postedData);
            dd($response);
            // if ($flashsaleproduct == true) {

            //     $message = "success";
            // } else if ($flashsaleproduct == false) {
            //     $message = "flashsale";
            // } else {
            //     $message = "error";
            // }
            return $response;
        }
    }

    public function addbanner()
    {
        $imageObj = $this->_request->file('file');

        if ($imageObj) {
            $postedData = $this->_request->file('file');
            $result = $this->_adminobj->bannerUpload($postedData, 'image', true, false);
            if ($result) {
                print_rr($result);
            } else {
                return false;
            }
        }
    }

    public function getFlashSaleProducts(Request $req)
    {
        $search = $req->input('search_products');
        $flashSaleProduct = $this->productObj->getFlashProducts($search);
        return view('admin/flashsaleproducts', array('flashSaleProduct' => $flashSaleProduct));
    }

    public function setFeatureProduct($ProdcutId)
    {
        DB::table('products')->update(['home_flash_sale' => 0]);
        DB::table('products')->where('product_id', $ProdcutId)->update(['home_flash_sale' => 1]);
        return Redirect::back()->with('message_success', 'Product Status Changed Successfully.');
    }

    public function salesReports()
    {
        $postData = $this->_request->input();
        $data = $this->_adminobj->getSaleData($postData);
    }

    public function saleReport()
    {
         $postData = $this->_request->input();
        $totalSales = "";
        $postData['type'] = $postData['type'] ?? 'yearly';
        $postData['date_to'] = $postData['date_to'] ?? '';
        $postData['date_from'] = $postData['date_from'] ?? '';
        $postData['store_ids'] = $postData['store_ids'] ?? [];
        $storeSales = $this->_adminobj->getSaleData($postData);
        $totalSales = array_sum(array_column($storeSales, 'total_amount'));
        $storeData = $this->_adminobj->getAllStoresSales($postData);
        // $saleProducts = $this->_adminobj->getSaleProducts($postData);
        return view('admin/reports', ['totalSales' => $totalSales, 'storeSales' => $storeSales, 'storeData' => $storeData]);
    }
    public function productReport()
    {
         $postData = $this->_request->all();
        $totalSales = "";
        $postData['type'] = $postData['type'] ?? 'yearly';
        $postData['date_to'] = $postData['date_to'] ?? '';
        $postData['date_from'] = $postData['date_from'] ?? '';
        $postData['store_id'] = $postData['store_id'] ?? '';
        $postData['product_ids'] = $postData['product_ids'] ?? [];
        $productSales = $this->_adminobj->getProductSaleData($postData);
        // dd($productSales);
        $totalSales = array_sum(array_column($productSales, 'total_amount'));
        $saleProducts = $this->_adminobj->getAllSaleProducts($postData);
        return view('admin/product-reports', ['totalSales' => $totalSales, 'storeSales' => $productSales,'productData' => $saleProducts]);
    }
    public function getStoresAuto()
    {
        $postData = $this->_request->input();
        $storeData = $this->_adminobj->getAllStores($postData);
        foreach ($storeData as $si) {
            $arr[] = $si->store_name;
        }
        if (!empty($arr)) {
            echo json_encode($arr);
        } else {
            $arr[] = "no result found";
            echo json_encode($arr);
        }
    }

    public function getStoresName()
    {
        $postData = $this->_request->input();
        $storeData = $this->_adminobj->getAllStoresSales($postData);
        return view('admin/store-ajax', ['storeData' => $storeData]);
    }
    //search product name
    public function getProductName()
    {
        
        $postData = $this->_request->input();
        $productData = $this->_adminobj->getAllSaleProducts($postData);
        return view('admin/product-ajax', ['productData' => $productData]);
    }
    public function getAllStripeVendorAccounts()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));

        $accounts = $stripe->accounts->all(['limit' => 50]);
        //  echo 'Data: <pre>' .print_r($accounts,true). '</pre>'; die(); 
        return view('admin.stripevendoraccounts', compact('accounts'));
    }

    public function getYoutubeVideos()
    {
        return view('admin.youtube-videos');
    }

    public function getProductVideos()
    {
        return view('admin.product-assign-videos');
    }
    public function mallsharkPurchasedPackages(Request $request)
    {
        $query = MallsharkPurchasedPackage::with(['store.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            //add search for status (active, inactive, expired) if status is == all then do not apply status filter
            $query->where(function ($q) use ($search) {
                $q->where('package_name', 'like', "%{$search}%")
                ->orWhereHas('store', function ($q2) use ($search) {
                    $q2->where('store_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q3) use ($search) {
                            $q3->where('name', 'like', "%{$search}%");
                        });
                });
            });
        }

        if ($request->filled('status') && strtolower($request->status) !== 'all') {
            $query->where('status', $request->status);
        }

        $sortOrder = $request->get('sort', 'desc'); // 'desc' by default
        $packages = $query->orderBy('created_at', $sortOrder)->paginate(10);

        return view('admin.mallshark-purchased-packages', compact('packages'));
    }

    public function createUser(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:50',
            'email'       => 'required|email|unique:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create user (default password example: '123456')
        $password = 123456;
        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => md5($password)
        ]);

        $user->user_country_id = 1;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'User created successfully',
            'data'    => $user
        ], 201);
    }
    public function saveVideos(Request $request)
    {
        $validated = $request->validate([
            'selected_videos' => 'required|array',
            'selected_videos.*' => 'exists:tik_tok_videos,id',
            'product_links' => 'array',
            'product_ids' => 'array',
        ]);

        foreach ($validated['selected_videos'] as $videoId) {
            $video = TikTokVideo::find($videoId);
            
            if ($video) {
                $video->is_selected = true;
                $productLink = $request->product_links[$videoId] ?? null;
                $video->product_link = $productLink;
                $video->product_id = $request->product_ids[$videoId] ?? null;
                
                // If product link is provided, fetch product data from database
                if (!empty($productLink)) {
                    // Extract slug from URL
                    if (preg_match('/\/product\/([^+\?]+)/', $productLink, $matches)) {
                        $slug = $matches[1];
                        
                        // Query products table
                        $product = DB::table('products')
                            ->where('slug', $slug)
                            ->where('status', 1)
                            ->first();
                        
                        if ($product) {
                            // Get product image
                            $productImage = null;
                            if (!empty($product->feature_image)) {
                                $productImage = getAttachmentUrlById($product->feature_image, 'product');
                            }
                            
                            // Get price (sale price if on sale, otherwise regular price)
                            $price = $product->on_sale == 1 && !empty($product->sale_price) 
                                ? $product->sale_price 
                                : $product->price;
                            
                            // Save product data to video
                            $video->product_title = $product->title;
                            $video->product_price = $price;
                            $video->product_image = $productImage;
                        }
                    }
                }
                
                $video->save();
            }
        }

        return redirect()->back()->with('success', 'Videos saved successfully!');
    }

    /**
     * Store new TikTok video
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'embed_code' => 'required|string',
                'product_link' => 'nullable|url|max:500',
            ]);

            $embedCode = $request->embed_code;
            $videoId = null;
            $url = null;

            if (preg_match('/data-video-id=["\'](\d+)["\']/', $embedCode, $matches)) {
                $videoId = $matches[1];
            }
            
            if (preg_match('/cite=["\']https?:\/\/(?:www\.)?tiktok\.com\/@[^\/]+\/video\/(\d+)["\']/', $embedCode, $matches)) {
                preg_match('/cite=["\'](https?:\/\/[^"\']+)["\']/', $embedCode, $urlMatch);
                $url = $urlMatch[1] ?? null;
            }
            $existingVideo = TikTokVideo::where('video_id', $videoId)->first();
            if ($existingVideo) {
                return response()->json([
                    'success' => false,
                    'message' => 'This video already exists!'
                ], 422);
            }

            $videoData = [
                'video_id' => $videoId,
                'url' => $url,
                'embed_code' => $embedCode,
                'product_link' => $request->product_link,
            ];

            // Fetch product metadata if product link is provided
            if ($request->product_link) {
                try {
                    $productData = $this->fetchProductMetadata($request->product_link);
                    
                    // Or add individual fields
                    $videoData['product_title'] = $productData['title'] ?? null;
                    $videoData['product_price'] = $productData['price'] ?? null;
                    $videoData['product_image'] = $productData['image'] ?? null;
                    
                } catch (\Exception $e) {
                Log::error('Failed to fetch product metadata: ' . $e->getMessage());
                }
            }

            $video = TikTokVideo::create($videoData);

            return response()->json([
                'success' => true,
                'message' => 'Video added successfully!',
                'video' => $video,
                'product_data' => isset($productData) ? $productData : null
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Delete a buyer's account
     * Soft deletes user, anonymizes email, and clears buyer-related data.
     */
    public function deleteUserAccount($userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found.'], 404);
            }

            // Check if user has Super Admin or Admin role
            $roles = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.role_id')
                ->where('role_user.user_id', $userId)
                ->pluck('roles.role_name')
                ->toArray();

            if (in_array('Super Admin', $roles) || in_array('Admin', $roles)) {
                return response()->json(['status' => false, 'message' => 'Cannot delete Super Admin or Admin accounts.'], 403);
            }

            // Check for incomplete buyer orders
            $incompleteOrders = StoreOrder::where('buyer_id', $userId)
                ->where('charge_status', 'paid')
                ->whereIn('order_status', ['pending', 'shipped'])
                ->count();

            if ($incompleteOrders > 0) {
                return response()->json(['status' => false, 'message' => 'User has pending or shipped orders. All orders must be completed first.'], 403);
            }

            // Check if user has an active store (not soft deleted)
            $store = \Packages\Store\Models\V3\Store::withTrashed()
                ->where('user_id', $userId)
                ->first();

            if ($store && is_null($store->deleted_at)) {
                return response()->json(['status' => false, 'message' => 'User has an active store. Delete the store first.'], 403);
            }

            DB::beginTransaction();

            // Delete buyer-related records
            DB::table('products_cart')->where('user_id', $userId)->delete();
            DB::table('checkout_logs')->where('user_id', $userId)->delete();
            DB::table('cookies')->where('user_id', $userId)->delete();
            DB::table('customer_coupons')->where('user_id', $userId)->delete();
            DB::table('customer_wallets')->where('user_id', $userId)->delete();
            DB::table('device_tokens')->where('user_id', $userId)->delete();
            DB::table('favourites')->where('user_id', $userId)->delete();
            DB::table('favourite_stores')->where('user_id', $userId)->delete();
            DB::table('oauth_access_tokens')->where('user_id', $userId)->delete();
            DB::table('recommended_products')->where('user_id', $userId)->delete();
            DB::table('search_tags')->where('user_id', $userId)->delete();

            // Anonymize email and soft delete
            $user->email = 'deleted_' . $userId . '_' . time() . '@anonymized.com';
            $user->is_mark_deleted = '1';
            $user->save();

            DB::commit();

            return response()->json(['status' => true, 'message' => 'User account deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch product metadata from Mallshark URL
     */
    private function fetchProductMetadata($url)
    {
        try {
            Log::info('Fetching product metadata', ['url' => $url]);
            
            // Extract slug from URL
            if (preg_match('/\/product\/([^+\?]+)/', $url, $matches)) {
                $slug = $matches[1];
                Log::info('Extracted slug from URL', ['slug' => $slug]);
                
                // Query products table
                $product = DB::table('products')
                    ->where('slug', $slug)
                    ->where('status', '1')
                    ->first();
                
                if ($product) {
                    Log::info('Product found in database', [
                        'product_id' => $product->product_id,
                        'title' => $product->title
                    ]);
                    
                    // Get product image
                    $productImage = null;
                    if (!empty($product->feature_image)) {
                        $productImage = getAttachmentUrlById($product->feature_image, 'product');
                    }
                    
                    // Get price (sale price if on sale, otherwise regular price)
                    $price = $product->on_sale == 1 && !empty($product->sale_price) 
                        ? $product->sale_price 
                        : $product->price;
                    
                    return [
                        'title' => $product->title ?? null,
                        'price' => $price ?? null,
                        'image' => $productImage,
                        'url' => $url,
                        'product_id' => $product->product_id,
                    ];
                } else {
                    Log::warning('Product not found in database', ['slug' => $slug]);
                }
            } else {
                Log::warning('Could not extract slug from URL', ['url' => $url]);
            }
            
            return [
                'title' => null,
                'price' => null,
                'image' => null,
                'url' => $url,
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching product metadata', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return [
                'title' => null,
                'price' => null,
                'image' => null,
                'url' => $url,
            ];
        }
    }

    /**
     * API endpoint to get product metadata (for AJAX calls)
     */
    public function getProductMetadata(Request $request)
    {
        try {
            $url = $request->input('url');
            
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valid URL is required'
                ], 400);
            }

            $productData = $this->fetchProductMetadata($url);
            
            return response()->json([
                'success' => true,
                'data' => $productData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Product metadata error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Admin: Delete a seller's store
     * Soft deletes store, anonymizes store name, deletes products without orders and AI tokens.
     */
    public function deleteStore($storeId)
    {
        try {
            $store = Store::find($storeId);

            if (!$store) {
                return response()->json(['status' => false, 'message' => 'Store not found.'], 404);
            }

            // Check for incomplete orders
            $incompleteOrders = StoreOrder::where('store_id', $storeId)
                ->where('charge_status', 'paid')
                ->whereIn('order_status', ['pending', 'shipped', 'partial_shipped', 'disputed'])
                ->count();

            if ($incompleteOrders > 0) {
                return response()->json(['status' => false, 'message' => 'Store has pending, shipped or disputed orders. All orders must be completed first.'], 403);
            }

            // Check for pending payouts
            // $pendingPayouts = DB::table('store_escrows')
            //     ->where('store_id', $storeId)
            //     ->where('profit_status', 'pending')
            //     ->count();

            // if ($pendingPayouts > 0) {
            //     return response()->json(['status' => false, 'message' => 'Store has pending payouts. Wait until all payouts are processed.'], 403);
            // }

            DB::beginTransaction();

            // Get product IDs that have orders (must be preserved)
            $productIdsWithOrders = DB::table('order_items')
                ->where('store_id', $storeId)
                ->pluck('item_id')
                ->unique()
                ->toArray();

            // Get product IDs that have NO orders (safe to delete)
            $productIdsToDelete = DB::table('products')
                ->where('store_id', $storeId)
                ->whereNotIn('product_id', $productIdsWithOrders)
                ->pluck('product_id')
                ->toArray();

            if (!empty($productIdsToDelete)) {
                foreach (array_chunk($productIdsToDelete, 500) as $chunk) {
                    DB::table('product_variation')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_category')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_faqs')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_tags')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_ai_tags')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_specifications')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_shipping')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_video')->whereIn('product_id', $chunk)->delete();
                    DB::table('label_products')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_tiers')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_customizations')->whereIn('product_id', $chunk)->delete();
                    DB::table('custom_text_styles')->whereIn('product_id', $chunk)->delete();
                    DB::table('custom_image_settings')->whereIn('product_id', $chunk)->delete();
                    DB::table('custom_text_characters')->whereIn('product_id', $chunk)->delete();
                    DB::table('prduct_digital_files')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_causes')->whereIn('product_id', $chunk)->delete();
                    DB::table('products_cart')->whereIn('product_id', $chunk)->delete();
                    DB::table('favourites')->whereIn('product_id', $chunk)->delete();
                    DB::table('reviews')->whereIn('product_id', $chunk)->delete();
                    DB::table('superadmin_reviews')->whereIn('product_id', $chunk)->delete();

                    DB::table('products')->whereIn('product_id', $chunk)->delete();
                }
            }

            // Soft delete products that have orders (preserve data but mark as deleted)
            if (!empty($productIdsWithOrders)) {
                DB::table('products')
                    ->whereIn('product_id', $productIdsWithOrders)
                    ->whereNull('deleted_at')
                    ->update([
                        'status' => ProductConstants::STATUS_DELETED,
                        'deleted_at' => now(),
                    ]);
            }

            // Delete AI usage tokens
            DB::table('ai_usage_tokens')->where('store_id', $storeId)->delete();

            // Anonymize store name and soft delete
            $store->store_name = 'deleted_store_' . $storeId . '_' . time();
            $store->save();
            $store->delete(); // SoftDeletes sets deleted_at

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Store deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
