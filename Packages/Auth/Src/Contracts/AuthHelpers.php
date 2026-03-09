<?php

namespace Packages\Auth\Contracts;

use Exception;
use App\Models\Userrole;
use Illuminate\Support\Str;
use Packages\Auth\Models\User;
use Illuminate\Support\Facades\DB;
use Packages\Product\Models\Product;
use Packages\Core\Models\Category;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Packages\Product\Models\Favourites;
use Packages\Product\Models\Attachments;
use App\Http\Controllers\MailgunController;


trait AuthHelpers
{

    public function register($data, $forceLogin = false)
    {
        $user = new User();
        $userRole = new Userrole();
        $mailgun = new MailgunController();
     
        $randomString = '';
    
        try {
            $loginKey = str_random(25);
    
            // Handle guest user registration
            if (!empty($data['guest_user'])) {
                if (!empty($data['first_name']) && !empty($data['last_name'])) {
                    $data['name'] = $data['first_name'] . $data['last_name'];
                }
                $randomString = Str::random(8);
                $guestPassword = md5($randomString);
                $user->password = $guestPassword;
                $user->status = '1';
                $user->guest_user = 1;
            } else {
                $user->password = md5($data['password']);
            }
    
            $user->name = $data['name'] ?? "";
            $user->slug = !empty($data['name']) ? $this->strSlug($data['name'], '-') : null;
            $user->email = $data['email'] ?? "";
            $user->referral_code = str_random(10);
            $user->wallet_address = $data['wallet_address'] ?? "";
            $user->login_key = $loginKey;
            $user->register_source = 'MallShark';
            $user->ip_address = Request::ip();
            $user->contact_no = $data['contact_no'] ?? "";
            $user->physical_address = $data['physical_address'] ?? "";
            $user->is_news_letter = $data['chk_newsletter'] ?? 0;
            $user->sms_alerts = $data['chk_newsletter'] ?? 0;
            $user->email_alerts = $data['chk_newsletter'] ?? 0;
            $user->user_otp = rand(1000, 9999);
    
            $user->save();
    
            // Assign user role
            $userRole->user_id = $user->user_id;
            $userRole->role_id = !empty($data['seller']) ? 4 : 4;
            $userRole->save();
    
            // Force login if specified
            if ($forceLogin) {
                $userData = $user->where('user_id', $user->user_id)->first();
                if ($userData) {
                    Session::put('userData', $userData->toArray());
                }
            }
    
            // Build activation URL
            $url = $this->buildActivationUrl($loginKey, $data);
    
            // Prepare and send email
            $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
            $from = 'MallShark ' . env('NO_REPLY_EMAIL');
                $html = view('mail.user-otp', ['otp' => $user->user_otp]);
    
            $this->sendOtpEmail($data['email'], $html, $subject, $from);
            // ElasticSearch mapping
        
    
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
           throw new Exception($e->getMessage());
        }
    
        // Return appropriate response
            return ['userobj' => $user, 'success' => true];
    }
    
    public function buildActivationUrl($loginKey, $data)
    {
        if (!empty($data['store_campaign'])) {
            return URL::to('/validate-email/' . $loginKey . '?campaign=' . $data['store_campaign']);
        }
        if (!empty($data['store_creation'])) {
            return URL::to('/validate-email/' . $loginKey . '?store=' . $data['store_creation']);
        }
        if (!empty($data['referred'])) {
            return URL::to('/validate-email/' . $loginKey . '?referred=' . $data['referred']);
        }
        return URL::to('/validate-email/' . $loginKey);
    }
    
    public function strSlug($title, $separator = '-', $language = 'en')
    {
        return Str::slug($title, $separator, $language);
    }
    function getStoreProductsCount($storeId, $decode = null)
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $query = Product::where('store_id', '=', $storeId);

        $query->where('status', '!=', '2');

        $count = $query->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }


    public function sendOtpEmail($email, $html, $subject, $from)
    {
        if ($html instanceof \Illuminate\Contracts\View\View) {
            $html = $html->render();
        }

        $params = [
            'from'    => $from,
            'to'      => $email,
            'subject' => $subject,
            'html'    => $html,
        ];
        
        return $this->mailgun->SendMailgunMail($params); 
    }

    function getParentCategories() 
    {		
        $categories = Category::where('parentCategoryID', '=', 0)->get();
        return $categories;
    }

    // function getUserById($id)
    // {
    //     $userData = User::where('user_id', '=', $id)->first(); 
    //     return $userData;
    // }

    function getAttachmentUrlByIdAvatarApi($id, $size = 'avatar') {


        $global_obj = new Attachments();
    
        if ($id != '' && is_int($id)) {
            $global_obj->id = $id;
            $global_obj->size = $size;
    
            return $global_obj->getAttachmentUrlByIdAvatarApi();
        } else {
            return 'Please add a valid id';
        }
    }

    function getFavouriteProductsCount($userId)
    {
        
        $favouriteProducts = Favourites::where('user_id', $userId)->count();

        return $favouriteProducts;
    }
}
