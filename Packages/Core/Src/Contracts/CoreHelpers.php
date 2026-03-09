<?php
namespace Packages\Core\Contracts;
use App\Events\NewNotificationEvent;
use Packages\Auth\Models\DeviceToken;
use Packages\Product\Models\Product;
use Packages\Product\Models\Attachments;
use Packages\Store\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Services\FCMService;
use App\Services\SellerFcmService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MailgunController;
use Carbon\Carbon;
use Exception;
use Huddle\Zendesk\Facades\Zendesk;
use Illuminate\Support\ItemNotFoundException;
use Packages\Home\Models\ShippingAddress;
use Packages\Auth\Models\User;
use Packages\Order\Models\StoreOrder;
use Packages\Product\Models\Favourites;

trait CoreHelpers
{
        function getStoreDataByUserId($userId, $status = "") {
            $query = Store::where('user_id', '=', $userId);
            if (!empty($status)) {
                $query->whereIn('store_status', ['1', '2']);
            }
            $storeData = $query->first();
            if ($status == "" && !empty($storeData)) {
                $storeData->policies_status = $storeData->policies;
                if ($storeData->policies == 'Yes' || $storeData->policies == '') {

                    $policies = DB::table('content_pages')
                            ->where('page_slug', '=', 'shop-policies')
                            ->first();
                    if (!empty($policies)) {
                        $page_content = $policies->page_content;
                        $page_content = preg_replace('~>\s+<~', '><', $page_content);
                        if (!empty($page_content)) {
                            $storeData->policies = $policies->page_content;
                        }
                    }
                }
            }

            if (!empty($storeData)) {
                return $storeData;
            } else {
                return false;
            }
        }

        function getAttachmentUrlByIdMedia($id, $size = 'media') {
            $global_obj = new Attachments();
            if ($id != '' && is_int($id)) {
                $global_obj->id = $id;
                $global_obj->size = $size;

                return $global_obj->getAttachmentUrlByIdEmailTempletes();
            } else {
                return 'Please add a valid id';
            }
        }
        function getAttachmentUrlById($id, $size = 'media') {

            $global_obj = new Attachments();
            if(isset($id))
            $id=json_decode($id);

            if ($id != ''){
                if(is_int($id)) {
                    $global_obj->id = $id;
                    $global_obj->size = $size;
                    
                    return $global_obj->getAttachmentUrlById();
                }else {
                    return false;
                }
            } else {
                return 'Please add a valid id';
            }
        }
        function getUserByIdchat($user_id = '') {
            if ($user_id != '') {
                return $result = DB::table('users')->select('user_id as id', 'name', 'contact_no', 'email', 'physical_address', 'zendesk_user_id', 'created_at', 'updated_at')->where('user_id', '=', $user_id)->first();
            }
        }
        function getStorDataUserId($userId){
            $query = Store::where('user_id', '=', $userId)->first();
            if (!empty($query)) {
                return $query;
            } else {
                return false;
            }
        }
     
        function getStatebyId($stateId = '') {
            $query = DB::table('states')->where('state_id', $stateId)->first();
            if (!empty($query)) {
                return objToArray($query);
            } else {
                return false;
            }
        }
        function checkStoreActive() {
            $userId = auth()->user()->user_id;
            if (empty($userId)) {
                return '';
            }
            $storeData = getStoreDataByUserId($userId);
        if ($storeData->store_status == "0") {
            return $storeData->store_status;
        } else if ($storeData->store_status == "3") {
            return $storeData->store_status;
        } else if ($storeData->store_status == "1") {
            return $storeData->store_status;
        } else if ($storeData->store_status == "2") {
            return $storeData->store_status;
        } else {
            return '';
        }
        }
        function getOrderItems($order_id){
            
        $result = DB::table('order_items')->where("order_id", $order_id)->get();
       
        if(count($result) > 0) {
            return $result;
        }
        }
        function getShippingOptionByUserId($userId) {
                
        $result =  DB::table('shipping_options')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('user_id','asc')->select('*')
            ->get()
            ->toArray();
        
            if(count($result) > 0) {
            return $result;
        }
            return False;
        }
        function getvariableDataByProduct_id($product_id) {
            $query = DB::table('product_variation')->select('variation_id', 'product_variation_image','price','sale_price')->where('product_id', $product_id)->first();
        //   echo '$query: <pre>' .print_r($query,true). '</pre>'; die;
            if (!empty($query)) {
                
                
                return $query;
            } else {
                return false;
            }
        }
        function getStoreInfoById($storeID) {
        
            $res = DB::table('stores')->select('*')->where('store_id', $storeID)->get()->toArray();
           
            if (isset($res[0]->policies) && $res[0]->policies == 'Yes') {
                $policies = DB::table('content_pages')
                        ->where('page_slug', '=', 'shop-policies')
                        ->first();
                if (!empty($policies)) {
                    $page_content = $policies->page_content;
                    $page_content = preg_replace('~>\s+<~', '><', $page_content);
                    if (!empty($page_content)) {
                        $res[0]->shop_policies = $policies->page_content;
                    }
                }
            }
            return $res;
        }
        function getStoreInfoByUserid($userID) {
            $res = DB::table('stores')->select('store_id','store_name', 'banner_image_id', 'campaign_id','store_status','super_status')->where('user_id', $userID)->first();

            if ($res) {
                return $res;
            } else {
                return null;
            }
        }
        function getCurrentUserId() {
            if (Session::has('userData')) {
                $userId = Session::get('userData')['user_id'];
                return $userId;
            } else {
                return false;
            }
        }
        function getUserById($user_id = '') {
            if ($user_id != '') {
                return $result = User::where('user_id', '=', $user_id)->first();
            }
        }
        function getShippingAddressbyUserId($user_id = '') {
            $query = DB::table('shipping_addresses')->where('user_id', $user_id)->latest('sh_id')->first();
            if (!empty($query)) {
                return objToArray($query);
            } else {
                return false;
            }
        }
        function getOrderItemsByOrderId($order_id){
            $result = DB::table('order_items')->where("order_id", $order_id)->get();
            if(count($result) > 0) {
                return $result;
            }
            }
        function getProductIdSpecificColums($product_id = '') {
            if ($product_id != '') {
                return $results = Product::where('product_id', '=', $product_id)->select('product_id', 'title', 'slug', 'feature_image', 'product_type')->first();
            }
        }
        function getvariableByProduct_id($variation_id) {
                $query = DB::table('product_variation')->select('variation_id', 'product_variation_image','price','sale_price','option1','option2','option3','unit1','unit2','unit3','attribute1','attribute2','attribute3')->where('variation_id', $variation_id)->first();
                if (!empty($query)) {
                    
                    
                    return $query;
                } else {
                    return false;
                }
            }

        function addNotifications($receptId, $senderId, $subject, $description, $urlLink,$return_tab=null,$notification_action_id=null,$app_flag=null) {
            $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']:'';
            $data = array();
            $data['recipient_id'] = !empty($receptId) ? $receptId : "";
            $data['sender_id'] = !empty($senderId) ? $senderId : "";
            $data['subject'] = !empty($subject) ? $subject : "";
            $data['description'] = !empty($description) ? $description : "";
            $data['url_link'] = !empty($urlLink) ? $urlLink : "";
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['app_flag'] = $app_flag;
            $data['return_tab'] = $return_tab;
            $data['notification_action_id'] = $notification_action_id;
            $query = DB::table('notifications')->insertGetId($data);
            $userQuery = DB::table('users')->where('user_id', $receptId)->update(['has_notification' => 1]);
            $userQuery = DB::table('users')->where('user_id', $senderId)->update(['has_notification' => 1]);

            $notification = DB::table('notifications')->where('notification_id', $query)->first();

            if ($query && $notification) {
                
                event(new NewNotificationEvent($receptId, $notification));
                return true;
            } else {
                return false;
            }
        }

    public function getOrderById($orderId)
    {
        return StoreOrder::where('order_id', $orderId)->first();
    }

    public function changeOrderStatus($orderId, $status)
    {
        $updateData = ['order_status' => $status];

        if ($status === 'shipped') {
            $updateData['shipped_date'] = now();
        }

        return StoreOrder::where('order_id', $orderId)->update($updateData);
    }
    /**
     * Push notifications
     */
    function pushNotification($userId, $message, $appFlag = null, $url = null)
    {
        $deviceTokens = DeviceToken::where('user_id', $userId)
            ->pluck('fcm_token');
    
        if ($deviceTokens->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No device tokens found.'], 404);
        }
    
        $service = ($appFlag == 'seller') ? SellerFcmService::class : FCMService::class;
        $failedTokens = [];
        $successTokens = [];
    
        $deviceTokens->each(function ($token) use ($service, $message, $url, &$failedTokens, &$successTokens) {
            try {
                $notificationData = [
                    'title' => $message,
                ];

                if ($url) {
                    $notificationData['url'] = $url;
                }

                $service::send($token, $notificationData);
                $successTokens[] = $token; // Track successful tokens
            } catch (\Exception $e) {
                Log::error("FCM Notification Failed for Token: $token | Error: " . $e->getMessage());
                $failedTokens[] = $token; // Track failed tokens
            }
        });
    
        return response()->json([
            'status' => true,
            'message' => 'Push notifications processed.',
            'success_tokens' => $successTokens,
            'failed_tokens' => $failedTokens,
        ]);
    }
    
    /***
     * Get product by id
     */
    function getProductById($productId)
    {
        return Product::where('product_id', $productId)->first();
    }
    
    function sendGenericEmail($from, $to, $subject, $message, $url = null)
    {
        $url = $url ? url($url) : null; 
        $user = User::where('email', $to)->first();

        $htmlContent = view('mail.generic', compact('subject', 'message', 'user', 'url'))->render();
    
        $params = [
            'from'    => $from,
            'to'      => $to,
            'subject' => $subject,
            'html'    => $htmlContent, 
        ];
    
        $mailgun = new MailgunController();
        return $mailgun->SendMailgunMail($params);
    }
    
    public function getFavoriteUsersByProductId ($productId) 
    {
        return Favourites::where('product_id', $productId)->pluck('user_id');
    }

    function getUserByEmail($email = '') 
    {
        return User::where('email', $email)->first();
    }

    public function getShippingAddressById($shippingAddressId) 
    {
        return ShippingAddress::where('sh_id', $shippingAddressId)->first();
    }
    function getShippingOptionById($shippingId) {
        $query = DB::table('shipping_options')->where('id', $shippingId)->first();
        if (!empty($query)) {
            return objToArray($query);
        } else {
            return false;
        }
    }
    function getVariationNameById($variationId) {
        $variation = DB::table('product_variation')
                ->where('variation_id', '=', $variationId)
                ->select('attribute1', 'attribute2', 'attribute3', 'unit1', 'unit2', 'unit3', 'option1', 'option2', 'option3')
                ->get()
                ->toArray();
        $name = '';
        if (count($variation) > 0) {
            // Start with attribute1, option1, and unit1
            $name = ucfirst($variation[0]->attribute1) . ': ' . $variation[0]->option1 . ' ' . $variation[0]->unit1;
        }
        // Check if attribute2, option2, and unit2 exist and add them
        if (isset($variation[0]->option2) && $variation[0]->option2 != '') {
            $name .= ' ' . ucfirst($variation[0]->attribute2) . ': ' . $variation[0]->option2 . ' ' . $variation[0]->unit2;
        }
        // Check if attribute3, option3, and unit3 exist and add them
        if (isset($variation[0]->option3) && $variation[0]->option3 != '') {
            $name .= ' ' . ucfirst($variation[0]->attribute3) . ': ' . $variation[0]->option3 . ' ' . $variation[0]->unit3;
        }
        return $name;
    }
    function copyImageFromUrlToNewFolder($imageUrl)
    {
        if(!empty($imageUrl)){
            $imageName = basename($imageUrl); // Get the filename from the URL (e.g., "th (5)_15607.jpg")
            $imageName =uniqid() . '_' . $imageName;

            $newFolder = 'uploads/orders'; // New folder to store the image
            
            $newImagePath = $newFolder . '/' . $imageName;

            // Ensure the folder exists
            $absoluteNewFolder = public_path($newFolder);
            if (!File::exists($absoluteNewFolder)) {
                File::makeDirectory($absoluteNewFolder, 0755, true);
            }

            // Convert the URL to a local path
            $relativePath = parse_url($imageUrl, PHP_URL_PATH); // Extract the relative path from the URL
            $sourcePath = public_path(ltrim($relativePath, '/'));

            // Check if the source file exists
            if (!File::exists($sourcePath)) {
                return response()->json(['message' => 'Source file not found!'], 404);
            }

            // Copy the file to the new folder
            if (File::copy($sourcePath, public_path($newImagePath))) {
                
                $newImagePath = basename($newImagePath);       
            }

            return $newImagePath;
        } else {
            return null;
        }
    }
    function getCountryByCode($code = '') {
        $query = DB::table('countries')->where('code', $code)->first();
        if (!empty($query)) {
            return objToArray($query);
        } else {
            return false;
        }
    }
    function getStatebyCode($code = '') {
        $query = DB::table('states')->where('code', $code)->first();
        if (!empty($query)) {
            return objToArray($query);
        } else {
            return false;
        }
    }
    function variableUpdateCurrentStock($varId, $productId, $sale) {
        $currentStock =  DB::table('product_variation')->where('variation_id', $varId)->where('product_id', $productId)->value('current_stock');

        // Check if the current stock is greater than or equal to the sale quantity
        if ($currentStock >= $sale) {
            // If there is enough stock, decrement the current stock by the sale quantity
            DB::table('product_variation')->where('variation_id', $varId)->where('product_id', $productId)->decrement('current_stock', $sale);
            DB::table('products')->where('product_id', $productId)->decrement('current_stock', $sale);
        DB::table('product_variation')->where('variation_id', $varId)->where('product_id', $productId)->increment('sales', $sale);
        }else{
            return false;
        }
    
    }
    function getvariableProduct($productId) {
        $query = DB::table('product_variation')->select('product_id', 'price', 'current_stock', 'sales')->where('product_id', $productId)->get();
        // echo '$query: <pre>' .print_r($query,true). '</pre>'; die;
        if (!empty($query)) {
            $data = array();
            $arr = array();
            $minPrice = 0;
            // $maxprice = 0;
            $currentStock = 0;
            $totalSales = 0;
            foreach ($query as $rs) {
                $arr[] = $rs->price;

                $currentStock += $rs->current_stock;
                $totalSales += $rs->sales;
            }
            if (!empty($arr)) {
                $data['min_price'] = min($arr); 
            }
            $data['current_stock'] = $currentStock;
            $data['total_sales'] = $totalSales;
            return $data;
        } else {
            return false;
        }
    }
    function simpleUpdateCurrentStock($productId, $sale) {
        $currentStock = DB::table('products')->where('product_id', $productId)->value('current_stock');

        // Check if the current stock is greater than or equal to the sale quantity
        if ($currentStock >= $sale) {
            // If there is enough stock, decrement the current stock by the sale quantity
            DB::table('products')->where('product_id', $productId)->decrement('current_stock', $sale);
            DB::table('products')->where('product_id', $productId)->increment('sales', $sale);
        }else{
            return false;
        }
    
    }
    function getCountryById($countryId = '') {
        $query = DB::table('countries')->where('id', $countryId)->first();
        if (!empty($query)) {
            return objToArray($query);
        } else {
            return false;
        }
    }

    function convertToPounds(float $weight, string $unit): float
	{
		return match (strtolower($unit)) {
			'kg', 'kilogram' => $weight * 2.20462,
			'g', 'gram'      => $weight * 0.00220462,
			'oz', 'ounce'    => $weight * 0.0625,
			default          => $weight,
		};
	}

    function convertToInches($value, $unit)
    {
        $value = (float) $value;
        switch (strtolower($unit)) {
            case 'cm':
                return $value * 0.393701;
            case 'm':
                return $value * 39.3701;
            case 'mm':
                return $value * 0.0393701;
            case 'ft':
            case 'feet':
                return $value * 12;
            case 'in':
            case 'inch':
            default:
                return $value;
        }
    }
}