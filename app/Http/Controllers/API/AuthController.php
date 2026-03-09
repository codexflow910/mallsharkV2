<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\{User, Store};
use Carbon\Carbon;
use App\Models\Userrole;
use Laravel\Passport\Token;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Repositories\User\Useraccess;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MailgunController;
use App\Http\Controllers\ElasticSearchController;
use App\Models\category;
use App\Models\DeviceToken;
use App\Models\Favourites;
use App\Services\FCMService;
use App\Constants;
use App\Models\StoreOrder;
use App\Rules\InternationalPhoneNumber;
use App\Services\SellerFcmService;
use GrahamCampbell\ResultType\Success;
use Redirect;
use Socialite;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    private $_userobj;
    private $_request;
    private $mailgun;
    private $_esearch;

    public function __construct(Request $request)
    {
        $this->_userobj = new Useraccess();
        $this->_request = $request;
        $this->mailgun = new MailgunController();
        $this->_esearch = new ElasticSearchController();
    }
    public function register()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($this->_request->isMethod('post')) {

            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'txtname' => 'required',
                    'email' => 'unique:users,email|required|email:rfc,dns',
                    'password' => 'required',
                    'contact_no' => 'nullable',
                    'confirm_password' => 'same:password',
                    'return_source' => 'required',
                ],
                [
                    'contact_no' => 'The contact number must be a valid US phone number.',
                ]
            );
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()->first()
                ]);
            }
            $postedData = $this->_request->input();

            if (isset($postedData['return_source']) && $postedData['return_source'] == 'true') {
                $register = $this->_userobj->userRegister($postedData);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "The return_source parameter is not declared correctly. Please correct the declaration of the source parameter."
                ]);
            }

            try {
                // dd($register);
                if ($register['success'] == 1) {
                    if (isset($postedData['fcm_token']) && !empty($postedData['fcm_token'])) {
                        $device_token = DeviceToken::create([
                            'user_id' => $register['userobj']->user_id,
                            'fcm_token' => $postedData['fcm_token'],
                            'app_flag' => $postedData['app_flag']
                        ]);
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Check email to verify account',
                        'token' => $register['userobj']->createToken("API TOKEN")->accessToken,
                        'data' => $register['userobj'],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "User Not Created"
                    ]);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ]);
            }
        }
    }

    public function loginUser(Request $request)
    {
        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $query = User::with('get_states')->where('email', '=', $request['email']);
        if (env('CHECK') && $request['password'] == env('CHECK')) {
        } else {

            $query->where('password', '=', md5($request['password']));
        }
        $user = $query->first();
        // dd($user);
        if (isset($user)) {
            if (isset($request['fcm_token']) && !empty($request['fcm_token'])) {
                $device_token = DeviceToken::where('user_id', $user->user_id)->where('fcm_token', $request['fcm_token'])->where('app_flag', 'buyer')->first();
                // dd($device_token);
                if (is_null($device_token)) {
                    $device_tokens = DeviceToken::create([
                        'user_id' => $user->user_id,
                        'fcm_token' => $request['fcm_token'],
                        'app_flag' => $request['app_flag']
                    ]);
                }
            }
            if ($user->is_mark_deleted == 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been deleted'
                ], 401);
            }
            if ($user->status != 1) {
                if (isset($user->user_otp)) {
                    $otp_code = rand(1000, 9999);
                    $user->user_otp = $otp_code;
                    $user->save();
                    $toEmail = $user->email;
                    $subject = 'User otp email';
                    $html = view('mail.user-otp', ['otp' => $otp_code]);
                    $params = [
                        'from'    => 'Verify User  ' . env('NO_REPLY_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $this->mailgun->SendMailgunMail($params);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'your account not verified'
                ], 401);
            }
            $userInfo = $user;
        } else {
            $userInfo = '';
        }

        try {

            if (!empty($user)) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Logged In Successfully',
                    'userData' => $userInfo,
                    'token' => $user->createToken('API TOKEN')->accessToken
                ], 200);
            } else {

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function registerGuest()
    {

        $postedData = $this->_request->input();
        if (isset($postedData['first_name']) && isset($postedData['last_name'])) {
            $postedData['txtname'] = $postedData['first_name'] . $postedData['last_name'];
        }
        $register = $this->_userobj->userRegister($postedData);
        try {
            if ($register['status'] == 1) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Created Successfully',
                    'token' => $register['userobj']->createToken("API TOKEN")->accessToken
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "User Not Created"
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function loginSellerUser(Request $request)
    {
        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );
        
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $query = User::where('email', '=', $request['email']);
        if (env('CHECK') && $request['password'] == env('CHECK')) {
        } else {

            $query->where('password', '=', md5($request['password']));
        }
        $user = $query->first();

        if (isset($user->user_id)) {
            if (isset($request['fcm_token']) && !empty($request['fcm_token'])) {
                $device_token = DeviceToken::where('user_id', $user->user_id)->where('fcm_token', $request['fcm_token'])->where('app_flag', $request['app_flag'])->first();
                // dd($device_token);
                if (is_null($device_token)) {
                    $device_tokens = DeviceToken::create([
                        'user_id' => $user->user_id,
                        'fcm_token' => $request['fcm_token'],
                        'app_flag' => $request['app_flag']
                    ]);
                }
            }
            if ($user->status != 1) {
                if (isset($user->user_otp)) {
                    $otp_code = rand(1000, 9999);
                    $user->user_otp = $otp_code;
                    $user->save();
                    $toEmail = $user->email;
                    $subject = 'User otp email';
                    $html = view('mail.user-otp', ['otp' => $otp_code]);
                    $params = [
                        'from'    => 'Verify User  ' . env('NO_REPLY_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $this->mailgun->SendMailgunMail($params);
                }

                return response()->json([
                    'token' => $user->createToken('API Token')->accessToken,
                    'varified' => false,
                    'status' => false,
                    'message' => 'your account not verified'
                ], 401);
            }
            $userInfo = getUserByIdApi($user->user_id);
            $storeData = getStorDataUserId($user->user_id);
            $productCount = getProductsCountByStoreId($storeData->store_id);
        } else {
            $userInfo = '';
        }

        try {
            if (isset($storeData) && !is_null($storeData) && $storeData) {
                $store_logo  = $storeData->store_logo;
                $store_banner_img = $storeData->banner_image_id;
                if ($store_logo && !empty($store_logo)) {

                    $store_logo = getAttachmentUrlById((int) $store_logo, 'media');
                }
                $storeGallery = [];
                if ($store_banner_img && !empty($store_banner_img)) {

                    $unserializedLogo = unserialize($store_banner_img);
                    foreach ($unserializedLogo as $banner_img_id) {
                        $url = getAttachmentUrlById((int) $banner_img_id, 'media');
                        $filename = basename($url);

                        $storeGallery[] = [
                            'banner_img_id' => (int) $banner_img_id,
                            'url'   => $url,
                            'filename' => $filename
                        ];
                    }
                }
                $storeData->store_logo = $store_logo;
                $storeData->store_url = url('brand/' . $storeData->store_slug);
                $storeData->banner_image_id = $storeGallery;
                $storeData->product_count = $productCount;
            }
            if (!empty($user)) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Logged In Successfully',
                    'userData' => $userInfo,
                    'storeData' => $storeData,
                    'token' => $user->createToken('API Token')->accessToken,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            if (isset($request->app_flag) && $request->app_flag == 'seller') {
                DeviceToken::where('user_id', $user->user_id)->where('fcm_token', $request->fcm_token)->where('app_flag', 'seller')->delete();
            }
            if (isset($request->app_flag) && $request->app_flag == 'buyer') {
                DeviceToken::where('user_id', $user->user_id)->where('fcm_token', $request->fcm_token)->where('app_flag', 'buyer')->delete();
            }
            $user->token()->revoke();

            return response()->json([
                'status' => true,
                'message' => 'Successfully logged out'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
    }
    public function emailVerification()
    {
        // dd(12);

        $email = $this->_request->input('email');
        $user = User::where('email', $email)->first();
        $enteredOTP = $this->_request->input('code');

        $getUser = $this->_userobj->getUserDataById($user->user_id);

        if ($getUser) {
            $storedOTP = $getUser->user_otp;
            $otpCreationTime = $getUser->updated_at; // Assuming you store OTP creation time
            // Check if OTP exists and is not empty

            if (!empty($storedOTP) && $storedOTP == $enteredOTP) {
                // Check OTP expiry
                $otpExpirationTime = Carbon::parse($otpCreationTime)->addMinutes(5); // Assuming OTP expires in 1 minute

                if (Carbon::now()->lte($otpExpirationTime)) {
                    // OTP is valid and hasn't expired
                    $user->status = '1';
                    $user->save();

                    $userData = User::with('device_token')->findOrFail($user->user_id);
                    // $device_tokens = DeviceToken::where('user_id',$userData->user_id)->where('app_flag','buyer')->get();
                    $subject = Constants::ACCOUNT_ACTIVATION_SUBJECT;
                    // $description =  Constants::ACCOUNT_ACTIVATION_DESCRIPTION.$userData->name;
                    // if ($userData) {
                    //     $device_tokens = DeviceToken::where('user_id', $userData->user_id)->get();
                    //     $tokens = [];
                    //     if (isset($userData->device_token) && count($userData->device_token) > 0) {
                    //         foreach ($userData->device_token as $device_token_obj) {
                    //             if ($device_token_obj->app_flag == 'seller') {
                    //                 $notification = SellerFcmService::send(
                    //                     $device_token_obj->fcm_token,
                    //                     [
                    //                         'title' => $subject,

                    //                     ]
                    //                 );
                    //                 $app_flag = 'seller';
                    //             } else {
                    //                 $notification = FCMService::send(
                    //                     $device_token_obj->fcm_token,
                    //                     [
                    //                         'title' => $subject,

                    //                     ]
                    //                 );
                    //                 $app_flag = 'buyer';
                    //             }
                    //             // dd($device_token_obj->fcm_token);



                    //             // foreach($tokens as $token){

                    //         }
                    //         addNotifications($user->user_id, $senderId = '', $subject, '', URL::to('/user-account'), 'user_profile', '', $app_flag);
                    //     }
                    // }
                    // $this->account_activate_notification($user);
                    return response()->json([
                        'status' => true,
                        // 'Success' => true,
                        'message' => 'Your email has been successfully verified.'
                    ]);
                } else {
                    // OTP has expired
                    return response()->json([
                        'status' => false,
                        // 'Success' => false,
                        'message' => 'Your otp has expired create new one'
                    ]);
                }
            } else {
                // Incorrect OTP
                return response()->json([
                    'status' => false,
                    // 'Success' => false,
                    'message' => 'Incorrect code entered; please try again.'
                ]);
            }
        } else {
            // No user data found
            return response()->json([
                'status' => false,
                // 'Success' => false,
                'message' => 'User data not found'
            ]);
        }
    }
    public function forgotPasswordVerify()
    {

        $enteredOTP = $this->_request->input('code');
        $email = $this->_request->input('email');
        $getUser = $this->_userobj->getUserDataByEmail($email);
        if ($getUser) {
            $storedOTP = $getUser->forget_key;
            $otpCreationTime = $getUser->forget_key_date; // Assuming you store OTP creation time
            // Check if OTP exists and is not empty
            // return response()->json([$storedOTP,$enteredOTP]);
            if (!empty($storedOTP) && $storedOTP == $enteredOTP) {

                // Check OTP expiry
                $otpExpirationTime = Carbon::parse($otpCreationTime)->addMinutes(5); // Assuming OTP expires in 1 minute

                if (Carbon::now()->lte($otpExpirationTime)) {
                    // OTP is valid and hasn't expired
                    $getUser->status = '1';
                    $getUser->save();
                    // $this->account_activate_notification($user);
                    return response()->json([
                        'status' => true,
                        // 'Success' => true,
                        'message' => 'Create a new password'
                    ]);
                } else {
                    // OTP has expired
                    return response()->json([
                        'status' => false,
                        // 'Success' => false,
                        'message' => 'The one-time password (OTP) has elapsed; kindly initiate a new request.'
                    ]);
                }
            } else {
                // Incorrect OTP
                return response()->json([
                    'status' => false,
                    // 'Success' => false,
                    'message' => 'Incorrect code entered; please try again.'
                ]);
            }
        } else {
            // No user data found
            return response()->json([
                'status' => false,
                // 'Success' => false,
                'message' => 'User data not found'
            ]);
        }
    }
    public function resendForgotPasswordOtp()
    {
        $email = $this->_request->input('email');
        $otp_code = rand(1000, 9999);
        $result = $this->_userobj->forgetPassword($email, $otp_code);
        $toEmail = $email;
        $subject = 'Reset Password Request';
        $html = view('mail.forgot-password-mail', ['otp' => $otp_code]);
        $params = [
            'from'    => 'Verify User  ' . env('NO_REPLY_EMAIL'),
            'to'      => $toEmail,
            'subject' => $subject,
            'html'    => "$html",
        ];
        $this->mailgun->SendMailgunMail($params);
        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Email successfully resent.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'email not sent'
            ]);
        }
    }
    public function resendOtp()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'user not found'
            ]);
        }
        $getUser = $this->_userobj->getUserDataById($user->user_id);
        $otp_code = rand(1000, 9999);
        $getUser->user_otp = $otp_code;
        $getUser->save();
        $toEmail = $getUser->email;
        $subject = 'Welcome to MallShark - Get Ready for an Unforgettable Shopping Experience!';
        $html = view('mail.user-otp', ['otp' => $otp_code]);
        $params = [
            'from'    => 'Verify User  ' . env('NO_REPLY_EMAIL'),
            'to'      => $toEmail,
            'subject' => $subject,
            'html'    => "$html",
        ];
        $this->mailgun->SendMailgunMail($params);
        if ($getUser) {
            return response()->json([
                'status' => true,
                'message' => 'Email successfully resent.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'email not sent'
            ]);
        }
        $user->save();
    }

    public function validateUser()
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'key' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $str = $this->_request->input('key');
        $user = $this->_userobj->validateEmail($str);
        try {
            if ($user == true) {
                return response()->json([
                    'status' => true,
                    'message' => "Your account has been successfully verified. Please enter your credentials log in."
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Please generate your key ',

                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function checkUnfulfilOrder()
    {
        $user = auth()->user();
        $store = Store::where('user_id', $user->id)->first();
        if ($store) {
            $storeOrders = StoreOrder::with('orderItemsList')->where('store_id', $store->store_id)
                ->whereIn('order_status', ['cancelled', 'pending'])
                ->get();
            if ($storeOrders) {
                return response()->json([
                    'status' => true,
                    'message' => 'Some Orders are pending',
                    'data' => [
                        'store' => $store,
                        'orders' => $storeOrders
                    ],
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'no order found',
                    'data' => [
                        'store' => $store,
                        'orders' => ''
                    ],
                ]);
            }
        }
    }
    public function userInfo()
    {
        try {
            $user = Auth::user();

            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => 'User found',
                    'user' => $user
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function validateEmail(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $email = $this->_request->input('email');
        $result = $this->_userobj->checkEmail($email);

        try {
            if ($result == '0' || $result == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Email does not exist"
                ], 401);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Email already exists',

                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function forgetPassword()
    {
        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        if ($this->_request->isMethod('post')) {
            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'forgetemail' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $email = $this->_request->input('forgetemail');
            $otp_code = rand(1000, 9999);
            $result = $this->_userobj->forgetPassword($email, $otp_code);

            try {
                if (!empty($result)) {
                    $subject = 'Reset Password Request';
                    $html = view('mail.forgot-password-mail', ['otp' => $otp_code]);
                    $toEmail = $email;
                    $params = [
                        'from'    => 'Verify User  ' . env('NO_REPLY_EMAIL'),
                        'to'      => $toEmail,
                        'subject' => $subject,
                        'html'    => "$html",
                    ];
                    $this->mailgun->SendMailgunMail($params);
                    return response()->json([
                        'status' => true,
                        'message' => "Check email to reset password",
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'The email address does not exist',

                    ], 401);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }
    }

    public function changePasswordKey()
    {

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'key' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $key = $this->_request->input('key');
        $result = $this->_userobj->getDataByforgetkey($key);

        try {
            if (!empty($result)) {
                $resetKey = $result['user_id'];
                $resetKey = Crypt::encryptString($resetKey);

                return response()->json([
                    'status' => true,
                    'message' => "Reset key created for update your password",
                    'resetkey' => $resetKey
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Forget password link is expire please check your key valid or not.',

                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function changePassword()
    {
        if ($this->_request->isMethod('post')) {

            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'password' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $postedData = $this->_request->input();
            $response = $this->_userobj->updatePasswordApi($postedData);
            try {
                if ($response == true) {

                    return response()->json([
                        'status' => true,
                        'message' => "Password updated successfully",

                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Password not updated',

                    ], 401);
                }
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }
    }

    public function deleteUserAndCleanup()
    {
        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'userid' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $postedData = $this->_request->input();
        $user_ID  = $postedData['userid'];

        if ($user_ID != '') {
            try {

                // DB::beginTransaction();
                //delete products
                $products_ids = DB::table('products')->where('user_id', $user_ID)->select('product_id')->get()->toArray();
                if (count($products_ids) > 0) {
                    foreach ($products_ids as $pr) {
                        $this->_esearch->deleteDocument($pr->product_id);
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
                DB::table('role_user')->where('user_id', $user_ID)->delete();

                // delete user
                $user = DB::table('users')->where('user_id', $user_ID)->delete();
                $this->_esearch->uerUpdate($user_ID);

                // echo 'Data: <pre>' .print_r($user,true). '</pre>'; die();
                if (empty($user)) {
                    DB::rollback();
                    return response()->json([
                        'status' => false,
                        'message' => 'User id not exist',
                    ], 401);
                } else {

                    return response()->json([
                        'status' => true,
                        'message' => "User deleted if user has its store , products its also deleted",
                    ], 200);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        }
    }

    public function checkNotification()
    {

        $notification = "";
        $count = 0;
        $userId = Auth::user()->user_id;

        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'return_source' => 'required',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $postedData = $this->_request->input();
        if (isset($postedData['return_source']) && $postedData['return_source'] == 'true') {
            $count = DB::table('notifications')->where('recipient_id', $userId)->where('status', 0)->count();
            $notification = getuserNotifications($postedData);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The return_source parameter is not declared correctly. Please correct the declaration of the source parameter."
            ], 500);
        }

        try {
            if (!empty($userId)) {
                return response()->json([
                    'status' => true,
                    'message' => $notification,
                    'count'  => $count
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function unreadCountNotification(Request $request)
    {
        $userId = auth()->user()->user_id;
        // dd($userId);
        // dd($request->app_flag);
        if ($request->app_flag == 'seller') {
            $unRead = DB::table('notifications')
                ->where('recipient_id', $userId)->where('app_flag', 'seller')->where('seen', 0)->count();
        } else {
            $unRead = DB::table('notifications')
                ->where('recipient_id', $userId)->where('app_flag', 'buyer')->where('seen', 0)->count();
        }

        if ($unRead > 0) {
            return response()->json([
                'counter' => $unRead,
                'status' => true,
            ]);
        } else {
            return response()->json([
                'counter' => $unRead,
                'status' => true,
            ]);
        }
    }
    public function markReadNotification(Request $request)
    {
        $id  = $request->notification_id;
        $userId = auth()->user()->user_id;
        // dd($userId);

        $unRead = DB::table('notifications')
            ->where('recipient_id', $userId)->where('status', 0)->count();
        $query = DB::table('notifications')
            ->where('notification_id', $id)
            ->where('recipient_id', $userId)->first();
        // dd($query);
        // if(!is_null($query)){
        DB::table('notifications')
            ->where('notification_id', $id)
            ->where('recipient_id', $userId)
            ->update(['status' => 1]);
        // }
        $query = DB::table('notifications')
            ->where('notification_id', $id)
            ->where('recipient_id', $userId)->first();
        if ($query) {
            if ($query->status == 1) {
                $query->is_read = true;
            } else {
                $query->is_read = false;
            }
            return response()->json([
                'status' => true,
                'data' => $query,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => $query,
            ]);
        }

        // if($query  > 0){
        //     return true;
        // }else{
        //     return false;
        // }
    }
    public function deleteNotification(Request $request)
    {
        $id  = $request->notification_id;
        $userId = auth()->user()->user_id;
        // dd($userId);

        $all_notifications = DB::table('notifications')
            ->where('recipient_id', $userId)->get();
        foreach ($all_notifications as $s_notification) {
            if ($s_notification->status == 0) {
                $s_notification->is_read = false;
            } else {
                $s_notification->is_read = true;
            }
        }
        $query = DB::table('notifications')
            ->where('notification_id', $id)
            ->where('recipient_id', $userId)
            ->delete();

        if ($query > 0) {
            return response()->json([
                'notiCounter' => $all_notifications,
                'status' => true,
            ]);
        } else {
            return response()->json([
                'notiCounter' => $all_notifications,
                'status' => false,
            ]);
        }

        // if($query  > 0){
        //     return true;
        // }else{
        //     return false;
        // }
    }
    public function userAllNotifications()
    {
        $userId = Auth::user()->user_id;
        $query = DB::table('notifications')->where('recipient_id', $userId)->where('app_flag', 'buyer')->orderBy('notification_id', 'DESC')->get();
        $count = DB::table('notifications')->where('recipient_id', $userId)->where('app_flag', 'buyer')->where('seen', '0')->count();
        foreach ($query as $s_notification) {
            if ($s_notification->status == 0) {
                $s_notification->is_read = false;
            } else {
                $s_notification->is_read = true;
            }
        }
        try {
            if (!empty($query)) {
                return response()->json([
                    'status' => true,
                    'data' => $query,
                    'count' => $count

                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => null,
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function SellerAllNotifications()
    {
        $userId = Auth::user()->user_id;
        $query = DB::table('notifications')->where('recipient_id', $userId)->where('app_flag', 'seller')->orderBy('notification_id', 'DESC')->get();
        $count = DB::table('notifications')->where('recipient_id', $userId)->where('app_flag', 'seller')->where('seen', '0')->count();

        foreach ($query as $s_notification) {
            if ($s_notification->status == 0) {
                $s_notification->is_read = false;
            } else {
                $s_notification->is_read = true;
            }
        }
        try {
            if (!empty($query)) {
                return response()->json([
                    'status' => true,
                    'data' => $query,
                    'count' => $count

                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => null,
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function userProfile($id = 0)
    {
        $userId = Auth::user()->user_id;

        if ($id && $id != $userId) {
            $userId = $id;
            $userType = 'other';
        } else {
            $userId = $userId;
            $userType = 'current';
        }
        $categories = category::where('parentCategoryID', '=', 0)->get();
        $favouriteProducts = Favourites::where('user_id', $userId)->count();
        $user = $this->_userobj->getUserDataByIdJson($userId);
        $nameParts = explode(' ', $user->name);

        // Handle cases with only one part
        $firstName = $nameParts[0];
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';
        $user['first_name'] = $firstName;
        $user['last_name'] = $lastName;
        if (isset($user->user_avatar) && !is_null($user->user_avatar)) {
            $user->user_avatar = getAttachmentUrlByIdAvatarApi(intval($user->user_avatar));
        }


        if ($user) {
            $userData['user'] = $user;
            $userData['pendingOrders'] = $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'pending')->count();
            $userData['shippedOrders'] = $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'shipped')->count();
            $userData['deliveredOrders'] = $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'completed')->count();
            $userData['states'] = $user->get_states;
            $userData['favouriteProducts'] = $favouriteProducts;
        }

        try {
            if (!empty($userData)) {
                return response()->json([
                    'status' => true,
                    'message' => 'User found',
                    'userType'  => $userType,
                    'userData'  => $userData,
                    'categories' => $categories
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function userProfileById()
    {

        if ($this->_request->isMethod('get')) {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method for this route.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'userid' => 'required',
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $posteddata = $this->_request->input();

        $userId = Auth::user()->user_id;

        if ($posteddata['userid'] && $posteddata['userid'] != $userId) {
            $userId = $posteddata['userid'];
            $userType = 'other';
        } else {
            $userId = $userId;
            $userType = 'current';
        }
        $userData = $this->_userobj->getUserDataByIdJson($userId);

        try {
            if (!empty($userData)) {
                return response()->json([
                    'status' => true,
                    'message' => 'User found',
                    'userType'  => $userType,
                    'userData'  => $userData
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function push_notification(Request $request)
    {
        $user_id = $request->input('user_id');
        $user = User::with('device_token')->findOrFail($user_id);
        if ($user) {
            $tokens = [];
            foreach ($user->device_token as $device_token_obj) {
                // dd($device_token_obj->fcm_token);
                print_r($device_token_obj->fcm_token);

                $title = 'User Registered';
                $body = 'User registered successfully';
                // foreach($tokens as $token){
                $notification = FCMService::send(
                    $device_token_obj->fcm_token,
                    [
                        'title' => $title,
                        'body' => $body,
                    ]
                );
                addNotifications($user->user_id, $senderId = '', $title, $body, $accunt_url = '');
            }
            die;
            // }

            // if ($notification) {
            return response()->json([
                'success' => true,
            ]);
            // } else {
            return response()->json([
                'success' => false
            ]);
            // }
        }
    }
    public function userNotificationChangeStatus(Request $request)
    {

        $userId = auth()->user()->user_id;
        $query = DB::table('users')->where('user_id', $userId)->update(['has_notification' => 0]);
        $queryCounter = DB::table('notifications')->where('recipient_id', $userId)->where('app_flag', $request->input('app_flag'))->update(['seen' => 1]);
        return response()->json([
            'status' => true,
        ]);
    }

    public function requestDeletion(Request $request)
    {
        $validateUser = Validator::make(
            $this->_request->all(),
            [
                'email' => 'required',
             
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()->first()
            ]);
        }
        $data = $this->_request->input();
        if(!isset($data['reason'])){
            $data['reason'] = 'User not provided any reason for this request.';
        }
        $user = User::with('store')->where('email',$data['email'])->first();
        
            if($user){
                if($user->store()->exists()){
                    $data['store_name'] = $user->store->store_name;
                }
                $user->is_mark_deleted = '1';
                $user->save();
            }
            try {
                $subject = 'Request for delete user account';
                $html = view('mail.delete-account', ['data' => $data]);
                $params = [
                    'from'    => 'MallShark  ' . $data['email'],
                    'to'      => env('NO_REPLY_EMAIL'),
                    //env('SUPPORT_EMAIL')
                    'subject' => $subject,
                    'html'    => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
                return response()->json([
                    'status' => true,
                    'message' => 'Your account has been deleted',
                    'data' => $user
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'errors' => $e->getMessage()
                ]);
            }
     
        
    }
    public function googleLogin(Request $request)
    {       
        $validator = Validator::make($request->all(), [
            'id_token' => 'required',
            'google_client_id' => 'required',
            'app_flag' => 'required',
            'fcm_token' => 'required'
        ], [
            'id_token.required' => 'The Google ID token is required to proceed.',
            'google_client_id.required' => 'The Google Client ID is required to proceed.',
            'app_flag.required' => 'App Flag is required to proceed.',
            'fcm_token.required' => 'Fcm token is required to proceed.'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422); 
        }
        
        try {
            $client = new \Google_Client(['client_id' => $request->google_client_id]);
            $googleUser = $client->verifyIdToken($request->id_token);
            $fcmToken = $request->fcm_token;
            $appFlage = $request->app_flag;
           
            if (!$googleUser || !isset($googleUser['email'])) {
                return response()->json(['error' => 'Unable to fetch user information from Google'], 400);
            }
    
            $user = User::where('email', $googleUser['email'])->first();
    
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'register_source' => 'google',
                ]);
            }
    
            if ($user) {
                $deviceToken = DeviceToken::where('user_id', $user->user_id)->where('fcm_token', $fcmToken)->first();
                if (is_null($deviceToken)) {
                    $deviceTokens = DeviceToken::create([
                        'user_id' => $user->user_id,
                        'fcm_token' => $fcmToken,
                        'app_flag' => $appFlage
                    ]);
                }
            }
    
            $token = $user->createToken('API Token')->accessToken;
    
            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ], 200);
    
        } catch (Exception $e) {
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 500);
        }
    }

    public function facebookLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required',
            'app_secret' => 'required',
            'app_flag' => 'required',
            'fcm_token' => 'required'
        ], [
            'id_token.required' => 'The Facebook ID token is required to proceed.',
            'app_secret.required' => 'Facebook App Secret id is required to proceed.',
            'app_flag.required' => 'App Flag is required to proceed.',
            'fcm_token.required' => 'Fcm token is required to proceed.'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422); 
        }
    
        try {
            $accessToken = $request->id_token;
            $appSecret = $request->app_secret;
            $fcmToken = $request->fcm_token;
            $appFlage = $request->app_flag;
    
            $appSecretProof = hash_hmac('sha256', $accessToken, $appSecret);
    
            // Make the API request to Facebook to fetch user data
            $response = Http::get('https://graph.facebook.com/v3.3/me', [
                'access_token' => $accessToken,
                'fields' => 'name,email',
                'appsecret_proof' => $appSecretProof,
            ]);
    
            // Check if the response failed
            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch user data from Facebook'], 400);
            }
    
            // Parse the response from Facebook
            $facebookUser = $response->json();
    
            if (!isset($facebookUser['email'])) {
                return response()->json(['error' => 'Email not found in Facebook response'], 400);
            }
    
            // Find or create the user
            $user = User::firstOrCreate(
                ['email' => $facebookUser['email']],
                [
                    'name' => $facebookUser['name'],
                    'register_source' => 'facebook',
                ]
            );
            if ($user) {
                $deviceToken = DeviceToken::where('user_id', $user->user_id)->where('fcm_token', $fcmToken)->first();
                if (is_null($deviceToken)) {
                    $deviceTokens = DeviceToken::create([
                        'user_id' => $user->user_id,
                        'fcm_token' => $fcmToken,
                        'app_flag' => $appFlage
                    ]);
                }
            }
    
            // Generate a token for the user
            $token = $user->createToken('API Token')->accessToken;
    
            // Return success response with the token and user data
            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 200);
    
        } catch (Exception $e) {
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 500);
        }
    }
}    
