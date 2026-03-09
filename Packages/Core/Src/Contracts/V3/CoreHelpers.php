<?php
namespace Packages\Core\Contracts\V3;
use App\Events\NewNotificationEvent;

use Packages\Auth\Models\V3\DeviceToken;
use Packages\Product\Models\V3\Product;
use Packages\Product\Models\V3\Attachments;
use Packages\Store\Models\V3\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use App\Services\FCMService;
use App\Services\SellerFcmService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MailgunController;
use InvalidArgumentException;
use Carbon\Carbon;
use Exception;
use Huddle\Zendesk\Facades\Zendesk;
use Illuminate\Support\ItemNotFoundException;
use Packages\Home\Models\V3\ShippingAddress;
use Packages\Auth\Models\V3\User;
use Packages\Core\Config\CoreConstants;
use Packages\Order\Models\V3\OrderItems;
use Packages\Order\Models\V3\StoreOrder;
use Packages\Product\Models\V3\Favourites;
use Packages\Core\Models\CheckoutLog;
use Packages\Product\Models\V3\Label;
use Packages\Product\Models\V3\ProductVariation;
use Packages\Shipping\Models\V3\ShippingProfile;
use App\Models\CategoryProduct;
use Packages\Core\Models\V3\category;
use Packages\Discount\Models\V3\DiscountUsage;
trait CoreHelpers
{
        function getStoreDataByUserId($userId, $status = "") {
            $query = Store::with('state')->where('user_id', '=', $userId);
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
        public static function getAttachmentUrlById($id, $size = 'media') {

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
     
       public static function getStatebyId($stateId = '') {
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
        public static function getStoreInfoById($storeID) {
        
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
        public static function getUserById($user_id = '') {
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
        public static function getvariableByProduct_id($variation_id) {
                $query = DB::table('product_variation')->select('variation_id', 'product_variation_image','price','sale_price','option1','option2','option3','unit1','unit2','unit3','attribute1','attribute2','attribute3','current_stock')->where('variation_id', $variation_id)->first();
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

    public static function getOrderById($orderId)
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
    public static function pushNotification($userId, $message, $appFlag = null, $url = null)
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

   public static function getChildCategoryByProductID($ProductID) 
   {
        $ObjeCatogry = new CategoryProduct();

        $getAllProductCategory = $ObjeCatogry->where('product_id', $ProductID)
            ->pluck('category_id');
        $objParentCategory = new Category();
        $getAllChildCategories = $objParentCategory->whereIn('id', $getAllProductCategory)->where('parentCategoryID', '!=', 0)->first();

        return $getAllChildCategories;
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

    public static function getUserByEmail($email = '') 
    {
        return User::where('email', $email)->first();
    }

    public static function getShippingAddressById($shippingAddressId) 
    {
        return ShippingAddress::where('sh_id', $shippingAddressId)->first();
    }
    public static function getShippingOptionById($shippingId) {
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
    public static function getCountryByCode($code = '') {
        $query = DB::table('countries')->where('code', $code)->first();
        if (!empty($query)) {
            return objToArray($query);
        } else {
            return false;
        }
    }
    public static function getStatebyCode($code = '') {
        $query = DB::table('states')->where('code', $code)->first();
        if (!empty($query)) {
            return objToArray($query);
        } else {
            return false;
        }
    }
    public static function variableUpdateCurrentStock($varId, $productId, $sale) {
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
    public static function simpleUpdateCurrentStock($productId, $sale) 
    {
        $currentStock = DB::table('products')->where('product_id', $productId)->value('current_stock');

        // Determine how much can actually be sold
        $saleQuantity = min($currentStock, $sale); // prevent negative stock

        if ($saleQuantity > 0) {
            DB::table('products')->where('product_id', $productId)->decrement('current_stock', $saleQuantity);
            DB::table('products')->where('product_id', $productId)->increment('sales', $saleQuantity);
            return $saleQuantity; // actual quantity sold
        } else {
            return false; // nothing can be sold
        }
    }
    public static function getCountryById($countryId = '') {
        $query = DB::table('countries')->where('id', $countryId)->first();
        if (!empty($query)) {
            return objToArray($query);
        } else {
            return false;
        }
    }
    public function getCompleteOrderData($id) {
        $data = StoreOrder::with([
            'orderItems.product',
            'orderItems.variation',
            'shippingAddress',
            'store.country', 
            'store.state',  
            'store.user',
            'user.state',
            'user.country',
        ])
        ->where('order_id', $id)
        ->get()
        ->toArray();

        return $data;
    }
    public static function checkoutLog($data)
    {
        $log = CheckoutLog::create($data);
        return $log;
    }

    public static function checkoutLogV3($data)
    {
        $log = CheckoutLog::create($data);
        return $log;
    }

    public static function findAndUpdateLogWithLogId($logId, $session)
    {
        $log = CheckoutLog::where('id', $logId)->first();
        if ($log) {
            $log->session_id = $session->id;
            $log->save();
        }
    }
    public static function findAndUpdateLogWithSessionId($guestId,$sessionId)
    {
        $logs = CheckoutLog::where('session_id', $guestId)->get();
        foreach ($logs as $log) {
            $log->session_id = $sessionId;
            $log->save();
        }
    }

    public static function findAndUpdateLogWithSessionIdV3($guestId,$sessionId)
    {
        $logs = CheckoutLog::where('user_id', $guestId)->get();
        foreach ($logs as $log) {
            $log->session_id = $sessionId;
            $log->save();
        }
    }
    public function createZendeskTicketForPackagePurchased($data)
    {
        try {
            $buyer = auth()->user();
            $buyerName = $buyer->name;
            $buyerEmail = $buyer->email;

            // Create or update Zendesk user
            $zendeskUser = Zendesk::users()->createOrUpdate([
                'name'  => $buyerName,
                'email' => $buyerEmail,
                'role'  => 'end-user',
            ]);

            $zendeskId = $zendeskUser->user->id ?? null;

            if (!$zendeskId) {
                throw new ItemNotFoundException("Zendesk ID not found");
            }

            // Extract package info
            $packageName = $data['package_name'];
            $amount      = number_format($data['amount'], 2);
            $startedAt   = Carbon::parse($data['started_at'])->toFormattedDateString();
            $expiredAt   = Carbon::parse($data['expired_at'])->toFormattedDateString();

            // Build Zendesk ticket content
            $subject = "You’ve Successfully Purchased a Mallshark Package";
            $message = <<<EOD
                Dear $buyerName,

                Thank you for purchasing the **$packageName** package on Mallshark.

                **Package Details:**
                - Amount: \$$amount
                - Start Date: $startedAt
                - Expiry Date: $expiredAt

                If you have any questions or need help with your package benefits, feel free to reply to this email or contact us at [care@mallshark.com](mailto:care@mallshark.com).

                We appreciate your business and look forward to supporting your store's success.

                Best regards,  
                Team Mallshark
                EOD;

            // Create Zendesk ticket
            $newTicket = Zendesk::tickets()->create([
                'subject'      => $subject,
                'comment'      => ['body' => $message],
                'priority'     => 'normal',
                'requester_id' => $zendeskId,
                'type'         => 'question',
            ]);
            
            $packageInfo = $data;
            // Send Mailgun email confirmation
            $from = env('NO_REPLY_EMAIL');
            $to = $buyerEmail;
            $htmlContent = view('mail.mallshark-package-purchase', compact('buyerName', 'packageInfo'))->render();

            $params = [
                'from'    => $from,
                'to'      => $to,
                'subject' => $subject,
                ];

            $mailgun = new MailgunController();
            $mailgun->SendMailgunMail($params);
            return true;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function sendLowStockEmail($orderId)
    {
        $orders = $this->getCompleteOrderData($orderId);
        $order = $orders[0] ?? null;

        if (!$order) {
            return null;
        }

        // Find low stock products (under 30 or multiple of 5)
        $lowStockItems = [];
        foreach ($order['order_items'] as $item) {
            $stock = $item['variation']['current_stock'] ?? $item['product']['current_stock'] ?? null;

            if ($stock !== null) {
                if ($stock < 30 && ($stock % 5 === 0)) {
                    $lowStockItems[] = $item;
                }
            }
        }

        if (empty($lowStockItems)) {
            return null;
        }

        foreach ($lowStockItems as &$item) {
            $item['product']['feature_image'] = $this->getAttachmentUrlById(
                $item['product']['feature_image'],
                'media'
            );
            if($item['variation'] != null){
                $item['variation']['product_variation_image'] = $this->getAttachmentUrlById(
                    $item['variation']['product_variation_image'],
                    'media'
                );
            }
            
        }
        $storeOwner = $order['store']['user'] ?? null;

        if ($storeOwner && !empty($storeOwner['email'])) {

            // List of special store emails
            $specialStoreEmails = [
                'daimch89@gmail.com',
                'rehmatullah@mindblazetech.com',
                'daimch89+2@gmail.com',
                'neseror884@agiuse.com',
                'talhaarshad427@gmail.com',
                'xebifif292@bankrau.com',
                'wokiwo4241@macho3.com',
                'asifjavaid@mindblazetech.com',
                'chad@mallshark.com',
                'ejaz@mindblazetech.com',
            ];

            // Decide recipient(s)
            if (in_array(strtolower($storeOwner['email']), array_map('strtolower', $specialStoreEmails))) {
                $to = ['chadfugate85@gmail.com', 'care@mallshark.com'];
            } else {
                $to = [$storeOwner['email']];
            }
            $htmlContent = view('mail.low-stock-notification', [
                'order'         => $order,
                'lowStockItems' => $lowStockItems,
                'storeOwner'    => $storeOwner,
                'storeData'     => $order['store'] ?? null,
            ])->render();

            $params = [
                'from'    => env('CARE_EMAIL'),
                'to'      => implode(',', $to),
                'subject' => '⚠️ Low Stock Alert – ' . $order['store']['store_name'] . ' – Needs Restocking',
                'html'    => $htmlContent,
            ];

            $mailgun = new MailgunController();
            $mailgun->SendMailgunMail($params);
            return true;
        }

        return null;
    }


    public function getStoreById($storeId)
	{
		$store = Store::where('store_id', $storeId)->with('country')->first();
		return $store;
	}

    function convertPackageFormat(array $products)
	{
        // dd($products);
		$packageWeight = 0;
		$maxLength = $maxWidth = 0;
		$totalHeight = 0;

		foreach ($products as $product) {
			$quantity = (int) ($product['quantity'] ?? 1);

			// Weight conversion
			$weight = $this->convertToPounds((float) $product['weight'], $product['weight_unit'] ?? 'lb');
			$packageWeight += ($weight * $quantity);

			// Dimension conversion
			$length = $this->convertToInches($product['length'] ?? 0, $product['dimension_unit'] ?? 'in');
			$width  = $this->convertToInches($product['width'] ?? 0, $product['dimension_unit'] ?? 'in');
			$height = $this->convertToInches($product['height'] ?? 0, $product['dimension_unit'] ?? 'in');

			$maxLength = max($maxLength, $length);
			$maxWidth = max($maxWidth, $width);
			$totalHeight += ($height * $quantity);
		}

		return [
			'weight' => [
				'units' => 'lb',
				'value' => round($packageWeight, 2)
			],
			'dimensions' => [
				'length' => round($maxLength, 2),
				'width' => round($maxWidth, 2),
				'height' => round($totalHeight, 2),
				'units' => 'in'
			]
		];
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

    public function filterFedexResponse($fedexResponse)
    {
        $rateDetails = $fedexResponse['output']['rateReplyDetails'] ?? [];

        $filtered = [];

        foreach ($rateDetails as $detail) {
            $serviceType = $detail['serviceType'] ?? null;
            $serviceName = $detail['serviceName'] ?? null;

            // Safely extract price from ratedShipmentDetails
            $price = null;
            if (!empty($detail['ratedShipmentDetails'][0]['totalNetCharge'])) {
                $price = (float) $detail['ratedShipmentDetails'][0]['totalNetCharge'];
            }

            if ($serviceType && $serviceName && $price !== null) {
                // If already exists, keep the one with the lower prive
                if (!isset($filtered[$serviceType]) || $price < $filtered[$serviceType]['price']) {
                    $filtered[$serviceType] = [
                        'serviceType' => $serviceType,
                        'serviceName' => $serviceName,
                        'deliveryDate' => $detail['operationalDetail']['deliveryDate'] ?? null,
                        'deliveryDay' => $detail['operationalDetail']['deliveryDay'] ?? null,
                        'price' => $price
                    ];
                }
            }
        }

        return array_values($filtered);
    }


    public static function filterUspsResponse($carrierRates)
    {
        $filtered = [];
        $rateOptions = $carrierRates['rateOptions'] ?? [];

        foreach ($rateOptions as $option) {
            if (!isset($option['rates']) || !is_array($option['rates'])) {
                continue;
            }

            foreach ($option['rates'] as $rate) {
                $filtered[] = [
                    'description' => $rate['description'] ?? null,
                    'mailClass' => $rate['mailClass'] ?? null,
                    'productDefinition' => $rate['productDefinition'] ?? null,
                    'SKU' => $rate['SKU'] ?? null,
                    'startDate' => $rate['startDate'] ?? null,
                    'processingCategory' => $rate['processingCategory'] ?? null,
                    'rateIndicator' => $rate['rateIndicator'] ?? null,
                    'totalBasePrice' => $option['totalBasePrice'] ?? null,
                    'destinationEntryFacilityType' => $rate['destinationEntryFacilityType'] ?? null
                ];
            }
        }

        $priorityOrder = [
            'USPS_GROUND_ADVANTAGE' => 1,
            'PRIORITY_MAIL' => 2,
            'PRIORITY_MAIL_EXPRESS' => 3,
        ];

        usort($filtered, function ($a, $b) use ($priorityOrder) {
            $aPriority = $priorityOrder[$a['mailClass']] ?? 999;
            $bPriority = $priorityOrder[$b['mailClass']] ?? 999;
            return $aPriority <=> $bPriority;
        });
        
        $filteredRates = collect($filtered)->filter(function ($rate) {
            return in_array($rate['rateIndicator'], CoreConstants::USPS_RATE_INDICATORS);
        })->values()->all();
        
        $filteredRates = collect($filteredRates)->filter(function ($rate) {
            return !in_array($rate['processingCategory'], CoreConstants::USPS_RESTRICTED_PROCESSING_CATEGORIES);
        })->values()->all();

        return $filteredRates;
    }

    public static function splitName($name)
    {
        $nameParts = explode(' ', trim($name));
        $firstName = array_shift($nameParts);
        $lastName = implode(' ', $nameParts);
        return [$firstName, $lastName];
    }
    
    function addAdditionalFee(array $carrierRates, string $feeType, float $feeValue, bool $additionalFeeCheck)
    {
        if ($additionalFeeCheck) {
            foreach ($carrierRates as &$rate) {
                if (isset($rate['price']) && is_numeric($rate['price'])) {
                    $originalPrice = (float) $rate['price'];
                    $additionalFee = 0;

                    switch ($feeType) {
                        case 'percentage':
                            $additionalFee = $originalPrice * ($feeValue / 100);
                            break;
                        case 'fixed':
                        case 'flat':
                            $additionalFee = $feeValue;
                            break;
                    }

                    $rate['additional_fee'] = round($additionalFee, 2);
                    $rate['price'] = round($originalPrice + $additionalFee, 2);
                }
            }
            unset($rate); // Break the reference
        }

        return $carrierRates;
    }

    public static function convertWeightToTargetUnit(float $weight, string $fromUnit, string $toUnit): float
    {
        $from = strtolower($fromUnit);
        $to = strtolower($toUnit);

        // Convert to grams first
        $weightInGrams = match ($from) {
            'g', 'gram', 'grams' => $weight,
            'kg', 'kilogram', 'kilograms' => $weight * 1000,
            'lb', 'lbs', 'pound', 'pounds' => $weight * 453.592,
            'oz', 'ounce', 'ounces' => $weight * 28.3495,
            default => throw new InvalidArgumentException("Unknown fromUnit: $from")
        };

        // Convert grams to target unit
        return match ($to) {
            'g', 'gram', 'grams' => $weightInGrams,
            'kg', 'kilogram', 'kilograms' => $weightInGrams / 1000,
            'lb', 'lbs', 'pound', 'pounds' => $weightInGrams / 453.592,
            'oz', 'ounce', 'ounces' => $weightInGrams / 28.3495,
            default => throw new InvalidArgumentException("Unknown toUnit: $to")
        };
    }

    public static function calculateFixedShipping(array $product, object $fixedShipping)
    {
        $qty = $product['quantity'];

        // Case 1: Shipping Per Item
        if ($fixedShipping->shipping_per_item) {
            $perItemPrice = (float) $fixedShipping->per_item_price;
            $maxQty = $fixedShipping->max_items_allowed_for_price ?? null;
            $additional = (float) $fixedShipping->additional_price;

            if ($maxQty && $qty > $maxQty) {
                $baseShipping = $perItemPrice * $maxQty;
                $extraShipping = $additional * ($qty - $maxQty);
                return round($baseShipping + $extraShipping, 2);
            } else {
                return round($perItemPrice * $qty, 2);
            }
        }

        // Case 2: Shipping By Weight Per Item
        if ($fixedShipping->shipping_by_weight) {

            $basePrice = (float) $fixedShipping->base_price;
            $maxWeightAllowed = (float) $fixedShipping->max_weight_allowed_for_price;
            $additional  = (float) $fixedShipping->additional_price_weight;
            $profileUnit = $fixedShipping->weight_unit ?? 'lb';

            // Step 1: Calculate total weight in product's unit
            $totalWeight = (float) $product['weight'] * (int) $product['quantity'];

            // Step 2: Convert to shipping profile's weight unit
            $convertedWeight = CoreHelpers::convertWeightToTargetUnit(
                $totalWeight,
                $product['weight_unit'],
                $profileUnit
            );

            // Step 3: Round up to next whole number (ceil)
            $weight = ceil($convertedWeight);
            if($maxWeightAllowed){
                $maxTotalWeight = $maxWeightAllowed * $fixedShipping->base_price_per_weight;
            }else{ 
                $maxTotalWeight = $maxWeightAllowed;
            }
            if($maxTotalWeight && $maxTotalWeight > 0 && $weight > $maxTotalWeight) {
                $baseShipping = $basePrice;
                $extraShipping = $additional * ($weight - $maxTotalWeight);
                return round($baseShipping + $extraShipping, 2);
            } else {
                return round($basePrice , 2);
            }
        }
        return 0.00;
    }
     function createZendeskTicketForPackagePurshased($data)
    {
        try {
            $buyer = auth()->user();
            $buyerName = $buyer->name;
            $buyerEmail = $buyer->email;

            // Create or update Zendesk user
            $zendeskUser = Zendesk::users()->createOrUpdate([
                'name'  => $buyerName,
                'email' => $buyerEmail,
                'role'  => 'end-user',
            ]);

            $zendeskId = $zendeskUser->user->id ?? null;

            if (!$zendeskId) {
                throw new ItemNotFoundException("Zendesk ID not found");
            }

            // Extract package info
            $packageName = $data['package_name'];
            $amount      = number_format($data['amount'], 2);
            $startedAt   = Carbon::parse($data['started_at'])->toFormattedDateString();
            $expiredAt   = Carbon::parse($data['expired_at'])->toFormattedDateString();

            // Build Zendesk ticket content
            $subject = "You’ve Successfully Purchased a Mallshark Package";
            $message = <<<EOD
                Dear $buyerName,

                Thank you for purchasing the **$packageName** package on Mallshark.

                **Package Details:**
                - Amount: \$$amount
                - Start Date: $startedAt
                - Expiry Date: $expiredAt

                If you have any questions or need help with your package benefits, feel free to reply to this email or contact us at [care@mallshark.com](mailto:care@mallshark.com).

                We appreciate your business and look forward to supporting your store's success.

                Best regards,  
                Team Mallshark
                EOD;

            // Create Zendesk ticket
            $newTicket = Zendesk::tickets()->create([
                'subject'      => $subject,
                'comment'      => ['body' => $message],
                'priority'     => 'normal',
                'requester_id' => $zendeskId,
                'type'         => 'question',
            ]);
            
            $packageInfo = $data;
            // Send Mailgun email confirmation
            $from = env('NO_REPLY_EMAIL');
            $to = $buyerEmail;
            $htmlContent = view('mail.mallshark-package-purchase', compact('buyerName', 'packageInfo'))->render();

            $params = [
                'from'    => $from,
                'to'      => $to,
                'subject' => $subject,
                'html'    => $htmlContent,
            ];

            $mailgun = new MailgunController();
            $mailgun->SendMailgunMail($params);

            $adminSubject = "A seller has successfully purchased a package on <strong>Mallshark</strong>";

            $params = [
                'from' => $from,
                'to' => env('CARE_EMAIL'),
                'subject' => $adminSubject,
                'html' => $htmlContent
            ];

            $mailgun->SendMailgunMail($params);
            return true;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public static function notifyShipstationFailure($orderId, $payload)
    {
        try {
            $subject = "🚨 ShipStation Order Failed # " . $orderId;

            $html = view('mail.shipstation-failure', [
                'orderId'      => $orderId,
                'payload'      => $payload,
            ])->render();

            $params = [
                'from'    => 'MallShark ' . env('SUPPORT_EMAIL'),
                'to'      => 'care@mallshark.com',
                'subject' => $subject,
                'html'    => $html,
            ];

            $mailgun = new MailgunController();
            return $mailgun->SendMailgunMail($params);
        } catch (\Exception $e) {
            Log::error("Failed to send ShipStation failure email", [
                'orderId' => $orderId,
                'error'   => $e->getMessage(),
            ]);
            throw new \Exception('Failed to send ShipStation failure email: ' . $e->getMessage());
        }
    }
    public static function getProductOrderCount($productId)
    {
        $orderCount = OrderItems::where('item_id', $productId)
            ->sum('quantity');
        return $orderCount;
    }

    public static function assignLabelToProduct($productId, $labelSlug)
    {
        $label = Label::where('slug', $labelSlug)->first();

        if (!$label) {
            return false;
        }

        $product = Product::find($productId);

        if (!$product) {
            return false;
        }

        // Attach label only if it's not already attached
        if (!$product->labels()->where('label_id', $label->id)->exists()) {
            $product->labels()->attach($label->id);
        }

        // Re-run search indexing
        $product->searchable();
        return true;
    }

    public static function filterUspsError($response)
    {
        if (is_string($response)) {
            $response = json_decode($response, true);
        }

        $error = $response['error'] ?? [];
        $message = $error['message'] ?? $response['message'] ?? 'USPS request failed.';
        $code = $error['code'] ?? $response['code'] ?? null;
        $errors = $error['errors'] ?? $response['errors'] ?? [];

        $formattedMessage = $message;

        // 🧩 USPS-style error handling (array of errors)
        if (!empty($errors)) {
            $details = [];

            foreach ($errors as $err) {
                $param = $err['source']['parameter'] ?? null;
                $detail = $err['detail'] ?? 'Unknown issue';
                $codePart = isset($err['code']) ? "(Code: {$err['code']})" : '';

                if ($param) {
                    $details[] = ucfirst($param) . ": " . $detail . " {$codePart}";
                } else {
                    $details[] = "{$detail} {$codePart}";
                }
            }

            // Append all details to message
            $formattedMessage .= ' — ' . implode(', ', array_unique($details));
        }

        // 🧠 Fallback for OpenAPI validation errors
        if (str_contains($message, 'OpenAPI-Spec-Validation')) {
            $issues = [];

            preg_match_all("/Path '\/([^']+)'/", $message, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $path) {
                    $issues[] = "Invalid field: {$path}";
                }
            }

            preg_match_all("/'([^']+)' does not match input string ([^:\]]+)/", $message, $invalids);
            if (!empty($invalids[1])) {
                foreach ($invalids[1] as $index => $field) {
                    $value = trim($invalids[2][$index] ?? '');
                    $issues[] = "Field '{$field}' has invalid value: {$value}";
                }
            }

            if (empty($issues)) {
                $issues[] = str_contains($message, 'required')
                    ? 'Some required fields are missing or invalid.'
                    : 'Please verify the address and package details.';
            }

            $formattedMessage = implode(', ', array_unique($issues));
        }

        return [
            'status'  => false,
            'message' => trim($formattedMessage),
            'code'    => $code,
        ];
    }

    public static function getProductDimensions($productId, $variationId = null)
    {
        $default = [
            'weight' => [
                'units' => null,
                'value' => 0
            ],
            'dimensions' => [
                'length' => 0,
                'width'  => 0,
                'height' => 0,
                'units'  => null
            ]
        ];

        try {
            $product = Product::where('product_id', $productId)
                ->select('weight', 'weight_type', 'productSize_height', 'productSize_width', 'productSize_length', 'dimention_type')
                ->first();

            if (!$product) {
                return $default;
            }

            $data = [
                'weight' => [
                    'units' => $product->weight_type ?? null,
                    'value' => (float) ($product->weight ?? 0),
                ],
                'dimensions' => [
                    'length' => (float) ($product->productSize_length ?? 0),
                    'width'  => (float) ($product->productSize_width ?? 0),
                    'height' => (float) ($product->productSize_height ?? 0),
                    'units'  => $product->dimention_type ?? null,
                ],
            ];

            if (!empty($variationId)) {
                $variation = ProductVariation::where('variation_id', $variationId)
                    ->select('attribute1', 'attribute2', 'attribute3', 'unit1', 'unit2', 'unit3', 'option1', 'option2', 'option3')
                    ->first();

                if ($variation) {
                    $attributes = [
                        ['attr' => $variation->attribute1, 'option' => $variation->option1, 'unit' => $variation->unit1],
                        ['attr' => $variation->attribute2, 'option' => $variation->option2, 'unit' => $variation->unit2],
                        ['attr' => $variation->attribute3, 'option' => $variation->option3, 'unit' => $variation->unit3],
                    ];

                    $dimensionBased = false;

                    foreach ($attributes as $item) {
                        $attr = strtolower(trim($item['attr'] ?? ''));
                        $value = (float) ($item['option'] ?? 0);
                        $unit = strtolower(trim($item['unit'] ?? ''));

                        if (in_array($attr, ['weight'])) {
                            $data['weight']['value'] = $value;
                            $data['weight']['units'] = $unit;
                            $dimensionBased = true;
                        } elseif (in_array($attr, ['length'])) {
                            $data['dimensions']['length'] = $value;
                            $data['dimensions']['units'] = $unit;
                            $dimensionBased = true;
                        } elseif (in_array($attr, ['width'])) {
                            $data['dimensions']['width'] = $value;
                            $data['dimensions']['units'] = $unit;
                            $dimensionBased = true;
                        } elseif (in_array($attr, ['height'])) {
                            $data['dimensions']['height'] = $value;
                            $data['dimensions']['units'] = $unit;
                            $dimensionBased = true;
                        }
                    }

                    if ($dimensionBased) {
                        return $data;
                    }
                }
            }

            return $data;
        } catch (Exception $e) {
            return $default;
        }
    }
    public static function attachCouponResult($product, $applied, $couponInfo, $finalTotal)
    {
        $product['coupon_applied'] = $applied ? 1 : 0;

        $product['coupon_id'] = $couponInfo['coupon_id'];
        $product['coupon_discount_type'] = $couponInfo['coupon_discount_type'];
        $product['coupon_discount_value'] = $couponInfo['coupon_discount_value'];

        $product['final_total_after_coupon'] = $finalTotal !== null ? round($finalTotal, 2) : null;
        $product['coupon_unit_price'] = $finalTotal !== null ? round($finalTotal / $product['quantity'], 2) : null;

        return $product;
    }
    public static function isCouponUsageExceeded($coupon, $userId)
    {
        if ($coupon->max_usage_limit && $coupon->usage_count >= $coupon->max_usage_limit) {
            return true;
        }

        if ($coupon->one_use_per_customer) {
            $alreadyUsed = DiscountUsage::where([
                'discount_id' => $coupon->id,
                'user_id' => $userId,
                'usage_count' => 1,
            ])->exists();

            if ($alreadyUsed) {
                return true;
            }
        }

        return false;
    }
}