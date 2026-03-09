<?php

namespace App\Http\Controllers;

use DB;
use Mail;

use Talk;
use Crypt;
use Cookie;
use Twilio;
use Session;
use Redirect;
use App\Constants;
use App\Models\User;
use Mailgun\Mailgun;
use App\Models\DeviceToken;
use App\Rules\USPhoneNumber;
use App\Services\FCMService;
use Illuminate\Http\Request;
use App\Services\SellerFcmService;
use Illuminate\Support\Facades\URL;
use App\Repositories\Herd\Herdaccess;
use App\Repositories\User\Useraccess;
use function PHPUnit\Framework\isNull;
use App\Rules\InternationalPhoneNumber;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Paginator;
use App\Http\Controllers\MailgunController;


use romanzipp\Turnstile\Rules\TurnstileCaptcha;
use App\Http\Controllers\ElasticSearchController;

//use Weblee\Mandrill\Mail;

// asif git

class UserController extends Controller {

    private $_userobj;
    private $_request;
    //private $mandrill;
    private $redirectTo;
    private $elasticSearchObj;
    private $mailgun;
// 
//    
    public function __construct(Request $request) {
        //  $this->redirectTo = url()->previous();
        $this->_userobj = new Useraccess();
        $this->_request = $request;
       // $this->mandrill = new MandrillT();
       $this->mailgun = new MailgunController();
        $this->elasticSearchObj = new ElasticSearchController();
    }

    public function register() {
        if ($this->_request->isMethod('post')) {
            $payload = $this->_request->validate([
                'cf-turnstile-response' => ['required', 'string', new TurnstileCaptcha()],
            ],
            [
                'cf-turnstile-response.required' => 'Human verification is required.',
                'cf-turnstile-response.string' => 'Invalid Turnstile response format.',
            ]);

            $this->_request->validate([
                'txtname' => 'required',
                'email' => 'unique:users,email',
                'password' => 'required|confirmed',
                'contact_no' => ['nullable', new InternationalPhoneNumber],
            ]);
            $postedData = $this->_request->input();
            // dd($postedData);
            $register = $this->_userobj->userRegister($postedData);
             // Push the dataLayer event
                $dataLayerUserEvent = [
                    'event' => 'sign_up',
                        'userData' => [
                            'user_id' => $register['data']['user_id'],
                            'user_name' => $register['data']['name'],
                            'email' => trim($register['data']['email']),
                            'register_source' => $register['data']['register_source'],
                            'contact_no' => $register['data']['contact_no'],
                            'user_list_id' => 'signup_user',
                            'user_list_name' => 'User Register', 
                        ],
                    ];
                    session(['dataLayerUserEvent' => $dataLayerUserEvent]);

            // $html=view('mail.activateUser',['activate_url'=>$url]);
            // $url = URL::to('/validate-email/' . $login_key);
            //    $subject = 'Welcome To MallShark';
            //  //  $templateSlug = 'signup';
            //    $toEmail = $data['email'];
            //    $toCompletename = $data['txtname'];
            //    $params = [ 'from'    => 'sales@mindblaze.net',
            //    'to'      => $toEmail,
            //    'subject' => $subject,
            //    'html'    => "$html",
            // ]; 
            //    $results = $this->mailgun->SendMailgunMail($params);
            if($register['status']){
                $subject = Constants::WELCOME_MESSAGE;
                $user_id = $register['data']['user_id'];

                addNotifications($user_id,$user_id,$subject,'',  URL::to('/search?keyword='));
                $this->_request->session()->flash('RegistersuccessMessage', env('register_text'));
                return view('user.signup-confirmation');
            }
        }
       
        $categories = DB::table('categories')->select('categoryTitle', 'id')->where('parentCategoryID', '=', 0)->get();
        // if ($this->isMobileDevice()) {
        //     return view('user/mobile/register', ['categories' => $categories]);
        // } else {
        
            return view('user/register', ['categories' => $categories]);
        // }
    }

    public function register_ajax() {

        if ($this->_request->isMethod('post')) {
            $this->_request->validate([
                'txtname' => 'required',
                'email' => 'unique:users,email',
                'password' => 'required|confirmed',
            ]);
            $postedData = $this->_request->input();
            $inserted = $this->_userobj->userRegister($postedData, true);
            if ($inserted) {
                return 'success';
            } else {
                return 'error';
            }
        } else {
            return 'error';
        }
    }

    public function validateEmail() {
        if ($this->_request->ajax()) {
            $email = $this->_request->input('email');
            $result = $this->_userobj->checkEmail($email);
            if ($result > 0) {
                echo "false";
            } else {
                echo "true";
            }
        }
    }

    // Validate Subscription Email
    public function validateEmailSubscription() {

        if ($this->_request->ajax()) {
            $email = $this->_request->input('s_email');
            $result = $this->_userobj->checkEmailSubscription($email);
            // echo 'Data: <pre>' .print_r($result,true). '</pre>'; die();
           
            if ($result == 1) {
                return response()->json([
					'status' => '1',
				], 200);
                echo "true";
            } else{
                return response()->json([
					'status' => '0',
				], 200);
            }
        }
    }

    public function UnsubscribeEmail($id){
        return view('home.unsubsribe-email', ['user_id' => $id]);
    }
    public function UnsubscribeEmailUser(){

        $user_id = $this->_request->input('user_id');
        try {
            $result = $this->_userobj->deleteEmailSubscription($user_id);
            
            if ($result == '1') {
                return response()->json([
                    'status' => '1',
                ], 200);
                echo "true";
            } else{
                return response()->json([
                    'status' => '0',
                ], 200);
            }
            
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
   
    public function login() {
        if($this->_request->session()->has('userData')){
            return Redirect::to('/');
        }
        if ($this->_request->isMethod('post')) {
            $this->_request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            $postedData = $this->_request->input();
            // dd($postedData);
            $result = $this->_userobj->login($postedData);
            if(isset($postedData['store_creation']) && $postedData['store_creation'] == 'creation'){
            $redirectUrl = '/login?store=creation';
            }
            else if(isset($postedData['created_store']) &&  $postedData['created_store']== 'store'){
                $redirectUrl = '/login?created=store';
            }else{
                $redirectUrl = '/login';
            }
            // dd($result);
            if ($result == 0) {
                $this->_request->session()->flash('errorMessage', 'Invalid email or password');
                return Redirect::to($redirectUrl)->withInput($this->_request->only('email'));
            } else if ($result == 1) {
                $this->_request->session()->flash('validationMessage', 'Please validate your email address');
                return Redirect::to($redirectUrl)->withInput($this->_request->only('email'));
            } else if ($result == 2) {
                $this->_request->session()->flash('errorMessage', 'Your account has been deactivated. Please contact customer support');
                return Redirect::to($redirectUrl)->withInput($this->_request->only('email'));
            } else {
                $this->_request->session()->put('userData', $result);
                $userId = Session::get('userData')['user_id'];
                $count = DB::table('notifications')->where('recipient_id', $userId)->where('status', 0)->count();

                $userData = Session::get('userData');
                $userData['previous_notification'] = $count;
                Session::put('userData', $userData);
                
                // $this->_request->session()->put('userData' , $count);
            }
             $userID = getCurrentUserId();
             $userData = getStorDataUserId($userID);
             
            if(isset($postedData['store_creation']) && $postedData['store_creation'] == 'creation'){
              
                return Redirect::to('/store-name');

            } else if(isset($postedData['created_store']) &&  $postedData['created_store']== 'store'){
                if(!empty($userData)){ 
              
                    return Redirect::to('/store-dashboard');
                }else{
                    return Redirect::to('/store-name');

                }
                return Redirect::to('/store-dashboard');

            }else if($this->_request->filled(['campaign'])){
                

             return Redirect::to('/store-name?campaign='.$postedData['campaign']);

            }elseif($this->_request->filled(['referred'])){

                return Redirect::to('/store-name?referred='.$postedData['referred']);

            }else{
                
                $pageUrl = Session::get('redirect_url');
                return Redirect::to($pageUrl);
            }

        }
        // if ($this->isMobileDevice()) {
        //     return view('user/mobile/login');
        // } else {
            return view('user/login');
        // }
    }

    public function ResendEmail(){

    $postedData = $this->_request->input();
    try {   
            $result = $this->_userobj->resendMail($postedData);
            if($result){
                return response()->json([
                    'status' => true,
                    'message' => "Please check your email inbox."
                ]);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }
    public function login_ajax() {

        $this->_request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $postedData = $this->_request->input();
        $result = $this->_userobj->login($postedData);
        $error = array();
        if ($result == 0) {

            $error['error'] = 'Invalid email or password';
        } else if ($result == 1) {

            $error['error'] = 'Please validate your email address';
        } else {
            $this->_request->session()->put('userData', $result);
            $error['success'] = 'Login Successfull';
        }




        return json_encode($error);
    }

    public function logout() {
        $this->_request->session()->flush();
        return Redirect::to('/login');
    }

    public function mailCheck() {
        $secret =env('MAILGUN_SECRET');     
        $mailgun = Mailgun::create($secret); // For EU servers
        $domain = env('MAILGUN_DOMAIN');
        $params = [ 'from'    => 'kamran@ecommedge.com',
        'to'      => 'noman@mindblazetech.com',
        'subject' => 'The Mail for testing purpose!',
        'text'    => 'It is so simple to send a message.'];
        $mailgun->messages()->send($domain, $params);
        echo 'successfully';
    }

    public function validateUser($str) {
    try {

        $user = $this->_userobj->validateEmail($str);
        $userData = $this->_userobj->getValidateEmailData($str);
        $subject = Constants::ACCOUNT_ACTIVATION_SUBJECT;
        // $description =  Constants::ACCOUNT_ACTIVATION_DESCRIPTION.$userData->name;
        $senderId = $userData->user_id;
        if ($user == true) {
            $this->_request->session()->flash('SuccessMessage', 'Your account has been successfully verified. Please enter your credentials below to log in.');
            if ($userData) {
                $device_tokens = DeviceToken::where('user_id',$userData->user_id)->get();
                $tokens = [];
                if(isset($userData->device_token)&& count($userData->device_token) > 0){
                    foreach ($userData->device_token as $device_token_obj) {
                        if($device_token_obj->app_flag == 'seller'){
                            $notification = SellerFcmService::send(
                                $device_token_obj->fcm_token,
                                [
                                    'title' => $subject,
                                    
                                ]
                            );
                            $app_flag = 'seller';
                        }else{
                            $notification = FCMService::send(
                                $device_token_obj->fcm_token,
                                [
                                    'title' => $subject,
                                    
                                ]
                            );
                            $app_flag = 'buyer';
                        }
                        // dd($device_token_obj->fcm_token);
                    
                    
                    
                    // foreach($tokens as $token){
                        
                    }
                addNotifications($userData->user_id, $senderId = '', $subject, '', URL::to('/user-account'),'user_profile','',$app_flag);
            }
                    
                
            }
            

            if ($this->_request->filled(['referred'])) {
                return Redirect::to('/login?referred='.$_GET['referred']);
            }
            if ($this->_request->filled(['store'])) {

                return Redirect::to('/login?store='.$_GET['store']);

            }elseif($this->_request->filled(['campaign'])){

                return Redirect::to('/login?campaign='.$_GET['campaign']);

            }else{
                
                return Redirect::to('/login');
            }
        }
    } catch (\Throwable $th) {
        $th->getMessage();
    }
    }

    public function forgetPassword() {
        if ($this->_request->isMethod('post')) {
           
        
            $this->_request->validate([
                'forgetemail' => 'required',
            ]);
            $email = $this->_request->input('forgetemail');
            $randomString = str_random(25);
            $result = $this->_userobj->forgetPassword($email,$randomString);
            if (!empty($result)) {
                 $url = URL::to('/change-password/' . $randomString); 
                 $html=view('mail.forgot-password',['forget_url'=>$url,'name'=>$result['name']]);
                //  $html=view('mail.forgotEmail',['forget_url'=>$url,'name'=>$result['name']]);
                $subject = 'Reset Password Request';
                $toEmail = $email;
              
                    $params = [ 'from'    =>'MallShark  '. env('NO_REPLY_EMAIL'),
                    'to'      => $toEmail,
                    'subject' => $subject,
                    'html'    =>  "$html",
                    'text'    => 'FORGETLINK'. $url,
                ]; 
                $results = $this->mailgun->SendMailgunMail($params);




                $this->_request->session()->flash('SuccessMessage', 'Great, we shot you an email to make sure its really you. Please check your inbox and click the confirmation link.');
                return Redirect::to('/forget-password');
            } else {
                $this->_request->session()->flash('errorMessage', 'The email address does not exist.');

                return Redirect::to('/forget-password');
            }
        }
        // if ($this->isMobileDevice()) {
        //     return view('user/mobile/forget');
        // } else {
            return view('user/forget');
        // }
    }

    public function changePassword($key) {
        $result = $this->_userobj->getDataByforgetkey($key);
        if (!empty($result)) {
            $resetKey = $result['user_id'];
            $resetKey = Crypt::encryptString($resetKey);
            if ($this->isMobileDevice()) {
                return view('user/mobile/reset', ['result' => $result, 'resetKey' => $resetKey]);
            } else {
                return view('user/reset', ['result' => $result, 'resetKey' => $resetKey]);
            }
        } else {
            $this->_request->session()->flash('SuccessMessage', 'Forget password link is expire please enter your email again.');
            return Redirect::to('/forget-password');
        }
    }

    public function doChangepassword() {
        if ($this->_request->isMethod('post')) {
            $postedData = $this->_request->input();
            $response = $this->_userobj->updatePassword($postedData);
            if ($response == true) {
                $this->_request->session()->flash('SuccessMessage', 'Password updated successfully');
                return Redirect::to('/login');
            }
        }
    }

    public function facebookRedirect() {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookCallback() {
        $error = $this->_request->input('error');
        if(isset($error)) {
            return Redirect::to('/login');
        }
        $data = array();
        $user = Socialite::driver('facebook')->user();
       if(isset($user['email']) && !empty($user['email'])) {
        $data['name'] = $user['name']; 
        $data['email'] = $user['email'];
        $data['register_source'] = "facebook";
        $userData = $this->_userobj->RegisterWithSocial($data);
        if ($userData == 0) {
            $this->_request->session()->flash('errorMessage', 'Invalid email or password');
            return Redirect::to('/login');
        } else if ($userData == 1) {
            $this->_request->session()->flash('errorMessage', 'Please validate your email address');
            return Redirect::to('/login');
        } else if ($userData == 2) {
            $this->_request->session()->flash('errorMessage', 'Your account has been deactivated please contact customer support');
            return Redirect::to('/login');
        } else {
            $this->_request->session()->put('userData', $userData);
        }
        return Redirect::to('/');
       } else {
        $this->_request->session()->flash('errorMessage', 'Something went wrong please try again later');
        return Redirect::to('/login');
       
       }
    }

    public function redirectLinkedIn() {
        //  return Socialite::driver('linkedin')->redirect();
        return Socialite::driver('linkedin')->redirect();
    }

    public function callbackLinkedIn() {
        $user = Socialite::driver('linkedin')->user();
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        exit;
    }

    public function userProfile($id = 0) {

        if ($id && $id != Session::get('userData')['user_id']) {
            $userId = $id;
            $userType = 'other';
        } else {
            $userId = Session::get('userData')['user_id'];
            $userType = 'current';
        }
        $userData = $this->_userobj->getUserDataById($userId);
        $cas = "";
        $a = array();
        $b = array();

            return view('user/userprofile', ['userData' => $userData, 'userType' => $userType]);
        // }
    }

    public function getInbox() {
        $inboxes = Talk::user(1)->threads();
        //$inboxes = Talk::getInbox();
        print_rr($inboxes);
        exit;
        return view('message.threads', $inboxes);
    }

    public function deleteUserAndCleanup($user_ID) {
        if ($user_ID != '') {
            //delete products
            $products_ids = DB::table('products')->where('user_id', $user_ID)->select('product_id')->get()->toArray();
            if (count($products_ids) > 0) {


                foreach ($products_ids as $pr) {
                    $this->elasticSearchObj->deleteDocument($pr->product_id);
                }
            }

            DB::table('products')->where('user_id', $user_ID)->delete();
            $stores = DB::table('stores')->where('user_id', $user_ID)->select('store_id')->get()->toArray();
            if (count($stores) > 0) {


                foreach ($stores as $st) {
                    $store_id = $st->store_id;
                    DB::table('stores')->where('store_id', $store_id)->delete();
                    DB::table('store_orders')->where('store_id', $store_id)->delete();
                    DB::table('order_items')->where('store_id', $store_id)->delete();
                }
            }

            //delete orders buyer side
            $orders_buyer = DB::table('store_orders')->where('buyer_id', $user_ID)->select('order_id')->get()->toArray();
            if (count($orders_buyer) > 0) {


                foreach ($orders_buyer as $st) {
                    $order_id = $st->order_id;

                    DB::table('order_items')->where('order_id', $order_id)->delete();
                }
            }
            DB::table('store_orders')->where('store_orders.buyer_id', $user_ID)->delete();

            // delete user
            DB::table('role_user')->where('user_id', $user_ID)->delete();
            DB::table('users')->where('user_id', $user_ID)->delete();
            return '<h3>Process was successfull</h3>';
        }
    }

    public function iconPurchase(Request $request) {
        $input = $request->all();
        $arr = array();

        if (isset($input['seller_herd_id'])) {
            $herdaccess = new Herdaccess();
            $arr = $herdaccess->iconPurchase($input['seller_herd_id']);

            if (!empty($arr['herd_info'])) {
                $receptId = $arr['herd_info']['user_id'];
                $subject = 'Order Received';
                $toCompletename = '';
                $zit_amount = $arr['herd_info']['icon_price'] + $arr['herd_info']['icon_debt'];
                //$subject = 'Your Herd Added';
                $buyer_id = getCurrentUserId();
                $buyer = getUserById($buyer_id);
                $z_animal = getZAnimalById($arr['herd_info']['icon_id']);
                $description = $subject = "Congratulations! . You have received $zit_amount from " . $buyer->name . " on purchase of " . $z_animal->name . ".";

                addNotifications($receptId, $buyer_id, $subject, $description, URL::to('/user-profile'));
            }
        } else {
            $arr['status'] = 'error';
            $arr['message'] = 'Icon Seller is missing';
        }
        return $arr;
    }

    public function iconDetails(Request $request) {
        $input = $request->all();
        $arr = array();

        if (isset($input['user_herd_id'])) {
            $herdaccess = new Herdaccess();
            $arr = $herdaccess->iconDetails($input['user_herd_id']);
        } else {
            $arr['status'] = 'error';
            $arr['message'] = 'Icon id is missing';
        }
        return $arr;
    }

    public function iconDetailsAjax(Request $request) {
        $input = $request->all();
        $arr = array();

        if (isset($input['user_herd_id'])) {
            $herdaccess = new Herdaccess();
            if (isset($input['listing_type']) && $input['listing_type'] == 'transactions')
                $arr = $herdaccess->iconDetailsTransactions($input['user_herd_id']);
            else if (isset($input['listing_type']) && $input['listing_type'] == 'aip_history')
                $arr = $herdaccess->iconDetailsAipHistory($input['user_herd_id']);
        }
        else {
            $arr['status'] = 'error';
            $arr['message'] = 'Icon id is missing';
        }
        return $arr;
    }

    public function markRead() {
        $userId = Session::get('userData')['user_id'];
        $query = DB::table('notifications')->where('recipient_id', $userId)->update(['status' => 1]);
    }

    public function markReadNotification(Request $request) {
        $id  = $request->notification_id;
        $userId = Session::get('userData')['user_id'];
        // dd($userId);

        $counter = DB::table('notifications')
        ->where('recipient_id', $userId)->where('status', 0)->count();

        $query = DB::table('notifications')
        ->where('notification_id', $id)
        ->where('recipient_id', $userId)
        ->update(['status' => 1]);

        if($query > 0){
            return response()->json([
                'notiCounter' => $counter-1,
                'status' => true,
                'message' => "0 Unread Notifications"
            ]);
        }else{
            return response()->json([
                'notiCounter' => $counter,
                'status' => false,
                'message' => "0 Unread Notifications"
            ]);
        }
        
        // if($query  > 0){
        //     return true;
        // }else{
        //     return false;
        // }
    }
    public function deleteNotification(Request $request) {
        $id  = $request->notification_id;
        $userId = Session::get('userData')['user_id'];
        // dd($userId);

        $counter = DB::table('notifications')
        ->where('recipient_id', $userId)->count();

        $query = DB::table('notifications')
        ->where('notification_id', $id)
        ->where('recipient_id', $userId)
       ->delete();

        if($query > 0){
            return response()->json([
                'notiCounter' => $counter-1,
                'status' => true,
            ]);
        }else{
            return response()->json([
                'notiCounter' => $counter,
                'status' => false,
            ]);
        }
        
        // if($query  > 0){
        //     return true;
        // }else{
        //     return false;
        // }
    }
    // new
    public function checkNewNotification() {
        $userId = Session::get('userData')['user_id'];
        
        if (!empty($userId)) {
            $prevCount = Session::get('userData')['previous_notification'];
           
            $newCount = DB::table('notifications')->where('recipient_id', $userId)->where('status', 0)->count();
    
            if ($newCount > $prevCount) {
                // The user has a new notification
                // Perform the necessary actions or send a response indicating the new notification
                return response()->json([
                    'status' => true,
                    'message' => 'You have a new notification!',
                    'new_count' => $newCount,
                ]);
            }
        }
    
        // If there are no new notifications, you can return an appropriate response
        return response()->json([
            'status' => false,
            'message' => 'No new notifications.',
        ]);
    }

    public function checkNotification() {
        $notification = "";
        $count = 0;
        $userId = Session::get('userData')['user_id'];
        if (!empty($userId)) {
            $count = DB::table('notifications')->where('recipient_id', $userId)->where('status', 0)->count();

            $notification = getuserNotifications();
            return view('user.notificationnew', ['notification' => $notification, 'count' => $count])->render();
            // return view('user.notificationhtml', ['notification' => $notification, 'count' => $count])->render();
        } else {
            return false;
        }
    }

    public function userNotificationChangeStatus(Request $request){
        $userId = Session::get('userData')['user_id'];
        $query = DB::table('users')->where('user_id', $userId)->update(['has_notification' => 0]);
        $countMessage = DB::table('notifications')->where('recipient_id', $userId)->where('status', 0)->count();
        $queryCounter = DB::table('notifications')->where('recipient_id', $userId)->update(['seen' => 1]);
        if($queryCounter > 0){
            return response()->json([
                'status' => true,
                'message' => "Notifications Mark 0"
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => "Notifications Mark Not 0"
            ]);
        }
    }

    public function userAllNotifications() {
        $userId = Session::get('userData')['user_id'];
        if($userId){
            $query = DB::table('notifications')->where('recipient_id', $userId)->orderBy('notification_id', 'DESC')->paginate(50);
            if (!empty($query)) {
                return view('user.allnotification', ['notification' => $query]);
            } else {
                return false;
            }
        }else{
            return redirect('login');
        }
    }

    public function sms() {
        $new_url = get_tiny_url('http://shopzit.mindblaze.net/search?cashback=75');
        Twilio::message('+923146860077', "hi asif flash sale is turned on please visit here $new_url");
    }

    public function googleRedirect()
    {
     return Socialite::driver('google')->redirect();
    }
    
    public function googleCallback(Request $request)
    {
        $user = Socialite::driver('google')->user();
        $data['name'] = $user['name'];
        $data['email'] = $user['email'];
        $data['register_source'] = "google";
        $userData = $this->_userobj->RegisterWithSocial($data);
        if (!empty($userData)) {
            if ($userData == 0) {
                $this->_request->session()->flash('errorMessage', 'Invalid email or password');
                return Redirect::to('/login');
            } else if ($userData == 1) {
                $this->_request->session()->flash('errorMessage', 'Please validate your email address');
                return Redirect::to('/login');
            } else if ($userData == 2) {
                $this->_request->session()->flash('errorMessage', 'Your account has been deactivated please contact customer support');
                return Redirect::to('/login');
            } else {
                $this->_request->session()->put('userData', $userData);
            }
            $request->session()->put('userData', $userData);
            return Redirect::to('/');
        } else {
            $userData = $this->_userobj->RegisterWithSocial($data);
            $request->session()->put('userData', $userData);
            return Redirect::to('/');
        }
    }
    public function SignupConfirmation (){
        return view('user.signup-confirmation');
    }
    public function userNameConvertSlug(){

        $userData = $this->_userobj->getUserNameIntoSlug();
        if($userData){
            return true;
        }
    }
}
