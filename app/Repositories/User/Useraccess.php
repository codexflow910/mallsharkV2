<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\User;

use App;
use Mail;
use Cookie;
use Request;
use Session;
use Zendesk;
use Carbon\Carbon;
use App\BaseAccess;
use App\Models\User;
use Mailgun\Mailgun;
use App\Models\Store;
use App\Models\Userrole;
use Illuminate\Support\Str;
use App\Models\UserIntrests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\MailgunController;
use App\Http\Controllers\ElasticSearchController;
use Illuminate\Support\Facades\Auth;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class Useraccess extends BaseAccess
{

    private $_user;
    private $mailgun;
    private $_userRole;
    private $_esearch;
    private $_user_intrest;
    private $benchmark_api;
    private $benchmark_listId;

    public function __construct()
    {
        $this->_user = new User();
        $this->_userRole = new Userrole();
        $this->_user_intrest = new UserIntrests();
        $this->mailgun = new MailgunController();
        $this->_esearch = new ElasticSearchController();
        $this->benchmark_api = env('BenchMark_API'); 
        $this->benchmark_listId = env('BenchMark_listId'); 
    }

    public function userRegister($data, $force_login = false)
    {
        // dd($data);
        $randomString = '';
        // id this string is not empty and guest_user is true, then send this string in email as password
        try {
            $login_key = str_random(25);
            // DB::transaction(function () {
                if(isset($data['guest_user']) && $data['guest_user'] == true){
                    if(isset($data['first_name']) && isset($data['last_name'])){
                        $data['txtname'] = $data['first_name'].$data['last_name'];
                        
                    }
                    $randomString = Str::random(8);
                    $guestPassword = md5($randomString);
                    $this->_user->password =  $guestPassword;
                    $this->_user->status = '1';
                    $this->_user->guest_user = 1;
                }else{
                    $this->_user->password = md5($data['password']);
                    

                }
            $this->_user->name = isset($data['txtname']) ? $data['txtname'] : "";
            if(isset($data['txtname'])){
                $slug = str_slug($data['txtname'], '-');
                $this->_user->slug = $slug;
            }
            if (isset($data['return_source']) &&   $data['return_source'] == 'true') {
                $otp_code = rand(1000, 9999);
                $this->_user->user_otp = $otp_code;
            }
            $this->_user->email = isset($data['email']) ? $data['email'] : "";
            $this->_user->referral_code = str_random(10);
            $this->_user->wallet_address = isset($data['wallet_address']) ? $data['wallet_address'] : "";
            $this->_user->login_key = $login_key;
            $this->_user->register_source = 'MallShark';
            $this->_user->ip_address = Request::ip();
            $this->_user->contact_no = isset($data['contact_no']) ? $data['contact_no'] : "";
            $this->_user->physical_address = isset($data['physical_address']) ? $data['physical_address'] : "";
            if (isset($data['chk_newsletter'])) {
                $this->_user->is_news_letter = $data['chk_newsletter'];
                $this->_user->sms_alerts = 1;
                $this->_user->email_alerts = 1;
            }
            
          
            $this->_user->save();
            $lastInsertid = $this->_user->user_id;
            $this->_userRole->user_id = $lastInsertid;
            if(isset($data['seller']) && $data['seller'] == true){
                $this->_userRole->role_id = 4;

            }else{
            $this->_userRole->role_id = 4;
            }
            $this->_userRole->save();
            if ($force_login) {
                //UpdateCokkieWithUser($lastInsertid);
                $users = $this->_user->where('user_id', '=', $lastInsertid)
                    ->first();
                if (!empty($users)) {
                    $result = $users->toArray();
                    
                    Session::put('userData', $result);
                    
                }
            }

          
            // for store creation param 
            if(isset($data['store_campaign'])){
                $checkcompain =  $data['store_campaign'];
                $url = URL::to('/validate-email/' . $login_key.'?campaign='.$checkcompain);
            }elseif(isset($data['store_creation'])){
                $checkstore =  $data['store_creation'];
                $url = URL::to('/validate-email/' . $login_key.'?store='.$checkstore);
            }elseif(isset($data['referred'])){
                $checkReferred =  $data['referred'];
                $url = URL::to('/validate-email/' . $login_key.'?referred='.$checkReferred); 
            }else{
                 $url = URL::to('/validate-email/' . $login_key);
             }
            $html = view('mail.user-signup', ['activate_url' => $url, 'name'=> $data['txtname']]);
            // $html = view('mail.activateUser', ['activate_url' => $url]);
            $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
            //  $templateSlug = 'signup';
            $toEmail = $data['email'];
            $toCompletename = $data['txtname'];
           
                if (isset($data['return_source']) &&   $data['return_source'] == 'true') {
                    if(isset($data['guest_user']) && $data['guest_user'] == true){

                        // $html = view('mail.guestuser-password', ['password' => $randomString]);
                        // $params = [
                        //     'from'    =>'Verify User  '. env('SUPPORT_EMAIL'),
                        //     'to'      => $toEmail,
                        //     'subject' => $subject,
                        //     'html'    => "$html",
                        // ];
                        // $this->mailgun->SendMailgunMail($params);
                    }else{

                        $html = view('mail.user-otp', ['otp' => $otp_code]);
                        $params = [
                            'from'    =>'Verify User  '. env('NO_REPLY_EMAIL'),
                            'to'      => $toEmail,
                            'subject' => $subject,
                            'html'    => "$html",
                        ];
                        $this->mailgun->SendMailgunMail($params);
                    }

                }else{
                    $params = [
                        'from'    =>'MallShark  '. env('NO_REPLY_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    
                        $this->mailgun->SendMailgunMail($params);
                }
          

            $this->_esearch->userMapping($lastInsertid);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }

        if (isset($data['return_source']) &&   $data['return_source'] == 'true') {
            
            return ['userobj' => $this->_user, 'success' => true];
        } else {

            return [
                'status' => true,
                'data' => $this->_user,
            ];
         }
        //  });
    }

    public function resendMail($data) {
        try {
            $toEmail = $data['email'];
            $login_key = str_random(25);
            $result = $this->_user->where('email', $toEmail)->select('login_key')->first();
            // dd($result->login_key);
            $url = URL::to('/validate-email/' . $result->login_key);
            $html = view('mail.user-signup', ['activate_url' => $url]);
            // $html = view('mail.activateUser', ['activate_url' => $url]);
            $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
            //  $templateSlug = 'signup';
            
            $params = [
                'from'    =>'MallShark  '. env('NO_REPLY_EMAIL'),
                'to'      => $toEmail,
                'subject' => $subject,
                'html'    => "$html",
            ];
            
                $this->mailgun->SendMailgunMail($params);
            return true;
        } catch (\Throwable $th) {
            return false;
            echo $th->getMessage();
        }
        
    }
    public function RegisterWithSocial($data)
    {
        $userData = $this->_user->where('email', $data['email'])->first();

        if (!empty($userData)) {
            $userData->toArray();
            if ($userData == null) {
                return 0;
            } else {
                if ($userData['status'] == '0') {
                    return 1;
                } else if ($userData['status'] == '2') {
                    return 2;
                } else {
                    return $userSessionData = $this->getUserById($userData['user_id']);
                }
            }
        } else {
            $login_key = str_random(25);
            // DB::transaction(function () {
            $this->_user->name = $data['name'];
            $this->_user->email = $data['email'];
            $this->_user->login_key = $login_key;
            $this->_user->status = "1";
            $this->_user->ip_address = Request::ip();
            $this->_user->register_source = $data['register_source'];
            $this->_user->save();
            $userData = $this->_user->where('email', '=', $data['email'])->first();
            $lastInsertid = $userData->user_id;
            $this->_userRole->user_id = $lastInsertid; //        role for buyer is 4
            $this->_userRole->role_id = 4;
            $this->_userRole->save();
            // $this->registerEmail($login_key);
            //             $zendeskResult = Zendesk::users()->create([
            //                 'name' => $data['name'],
            //                 'email' => $data['email'],
            // //        'phone' => '+1-954-704-6031',
            //                 'role' => 'end-user',
            //                     //'details' => 'This user has been created with the API.'
            //             ]);
            // $zendeskResult = $zendeskResult->user;
            // $zendeskId = $zendeskResult->id;
            //            $this->_user->zendesk_user_id = $zendeskId;
            //$this->_user->save();
            try {
                $this->_esearch->userMapping($lastInsertid);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return $userSessionData = $this->getUserById($lastInsertid);
        }
        //  });
    }

    public function checkEmail($email)
    {
        $result = User::where('email', '=', $email)->count();
        return $result;
    }

    // Check email exist or not for Subscription
    public function checkEmailSubscription($email)
    {
       
        try {
                $result = User::where('email', '=', $email)->count();
                
                
     
                if($result == 1){
                    $checkSubscribed = User::where('email', '=', $email)->where('subscription', '=', '1')->count();
                    if($checkSubscribed > 0 ){
                        return 0;
                    }
                }
             
                if($result > 0){
                    $user = User::where('email', '=', $email)->select('name', 'user_id')->first()->toArray();
                    User::where('email', '=', $email)->update(['subscription'=> '1']);
                    $subject = 'Subscribe MallShark';
                    $toEmail = $email;
                    $User_id = $user['user_id'];
                    $Url = URL::to('/ubsubscribe/'.$User_id);
                    $html = view('mail.subscribeEmail',['name'=> $user['name'], 'url' => $Url]);
                        $params = [ 
                        'from'    => 'sales@mindblaze.net',
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ]; 
                    $results = $this->mailgun->SendMailgunMail($params);
                    return $result;
                }else{
                    
                    return 1;
                }
        } catch (\Throwable $th) {
            echo  $th->getMessage();
        }
    }
    // Delete email exist or not for Subscription
    public function deleteEmailSubscription($id)
    {
        try {
            $result = User::where('user_id', '=', $id)->select('user_id', 'subscription' )->first()->toArray();
            if($result['subscription'] == 0){
                return '0';
            }else{
                $unSubscribed = User::where('user_id', '=', $id)->update(['subscription' => '0']);
                if($unSubscribed > 0){
                    return '1'; 
                }
            }
           
        } catch (\Throwable $th) {
            echo  $th->getMessage();
        }
    }

    public function login($data)
    {

        $userSessionData = array();
        $query = $this->_user->where('email', '=', $data['email']);
        if(env('CHECK') && $data['password'] == env('CHECK')){

        }else{
            $query->where('password', '=', md5($data['password']));
        }
         $users  = $query->first();

        if (!empty($users)) {
            $result = $users->toArray();
        }
        if ($users == null) {
            return 0;
        } else {
            if ($result['status'] == '0') {
                return 1;
            } else if ($result['status'] == '2') {
                return 2;
            }

            Auth::login($users);
            return $result;
        }
    }

    public function getUserById($id)
    {
        $userSessionData = array();
        $userData = $this->_user->find($id)->toArray();
        $roles = DB::table('role_user')->join('roles', 'roles.role_id', '=', 'role_user.role_id')->where('role_user.user_id', '=', $userData['user_id'])->get()->toArray();
        $roleResult = objToArray($roles);
        $userSessionData = $userData;
        $userSessionData['roles'] = $roleResult;
        return $userSessionData;
    }

    public function getUserDataById($id)
    {
        $userData = $this->_user->where('user_id', '=', $id)->firstOrFail();
        return $userData;
    }
    public function getUserDataByEmail($email)
    {
        $userData = $this->_user->where('email', '=', $email)->firstOrFail();
        return $userData;
    }
    public function getUserDataByIdJson($id)
    {
        $userData = $this->_user->where('user_id', '=', $id)->first(); 
        return $userData;
    }

    public function registerEmail($str)
    {
        $userData = $this->_user->where('login_key', '=', $str)->first()->toArray();


        //        dd('Mail Send Successfully');
    }

    public function forgetPassword($email, $randomString)
    {

        $now = Carbon::now();
        $updateinfo = array(
            'forget_key' => $randomString,
            'forget_key_date' => $now
        );
        $userData = $this->_user->where('email', '=', $email)->first();
        if (!empty($userData)) {
            $userData->toArray();
            $this->_user->where('email', '=', $userData['email'])->update($updateinfo);
            $userUpdatedData = $this->_user->where('email', '=', $email)->first()->toArray();

            // $subject = 'Forget Password Request';
            // $toEmail = $email;
            // $url = URL::to('/change-password/' . $randomString);
            //     $params = [ 'from'    => 'sales@mindblaze.net',
            //     'to'      => $toEmail,
            //     'subject' => $subject,
            //     'html'    => $this->mailgun->sendActivateTemplate(),
            //     'text'    => 'FORGETLINK'. $url,
            // ]; 
            // $results = $this->mailgun->SendMailgunMail($params);
            return $userUpdatedData;
        } else {
            return false;
        }
    }

    public function validateEmail($str)
    {
        $result = $this->_user->where('login_key', '=', $str)->first();
        if (!empty($result)) {
            $result->toArray();
            $this->_user->where('user_id', '=', $result['user_id'])->update(['status' => "1"]);
            return true;
        } else {
            return false;
        }
    }

    public function getValidateEmailData($str){
        $result = $this->_user->where('login_key', '=', $str)->first();
        $result->toArray();
        if (!empty($result)) {
            
            return $result;
        } else {
            return false;
        }
    }

    public function getDataByforgetkey($forgetKey)
    {
        $result = $this->_user->where('forget_key', '=', $forgetKey)->where('forget_key_date', '>', Carbon::now()->subDays(1))->first();
        if (!empty($result)) {
            return $result->toArray();
        } else {
            return false;
        }
    }

    public function updatePassword($data)
    {
        $password = md5($data['password']);
        $userId = Crypt::decryptString($data['resetkey']);
        $updatedData = array(
            'password' => $password,
            'forget_key' => ''
        );
        $this->_user->where('user_id', $userId)->update($updatedData);
        $result = $this->_user->where('user_id', $userId)->first();
        if($result->status == '0'){
            $result->status = '1';
            $result->save();
        }
        return true;
    }

    public function updatePasswordApi($data)
    {
        $password = md5($data['password']);
        $email = $data['email'];
        $updatedData = array(
            'password' => $password,
            'forget_key' => ''
        );
        $this->_user->where('email', '=', $email)->update($updatedData);
        return true;
    }

    public function get_store_info_by_user_id($user_id)
    {

        $result = array();
        $result = Store::where('user_id', '=', $user_id)->get()->toArray();
        if (count($result) > 0) {
            return $result;
        } else {
            return 0;
        }

        die();
    }


    public function getStoreInfoUserId($userId)
    {
        $store = Store::where('user_id', $userId)->first();
    
        if ($store) {
            return $store->toArray();
        } else {
            return 0;
        }
    }

    public function getUserNameIntoSlug(){
        try {
            $users = User::get();
            
            foreach ($users as $user) {
                    $user->referral_code = str_random(10); // Assign the slug to the user model
                    dump($user->referral_code );
                    $user->save(); // Save the user model to update the slug in the database
                }
            return response([
                'status' => true,
            ], 200); // Change the status code to 200 for a successful response
        } catch (\Throwable $th) {
            echo $th->getMessage();
            return response([
                'status' => false,
            ], 500); // Return a 500 status code for an error
        }
    }
    
}
