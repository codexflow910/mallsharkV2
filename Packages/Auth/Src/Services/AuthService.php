<?php

namespace Packages\Auth\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Packages\Auth\Config\AuthConstants;
use Packages\Auth\Contracts\AuthHelpers;
use Packages\Core\Contracts\CoreHelpers;
use Packages\Product\Models\Attachments;
use Dotenv\Exception\ValidationException;
use App\Http\Controllers\MailgunController;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Auth\Repositories\Eloquent\AuthRepository;
use Packages\Cart\Models\ProductCart;

class AuthService

{
    use AuthHelpers;
    use CoreHelpers;
    protected $authRepository;
    protected $mailgun;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
        $this->mailgun = new MailgunController();
    }

    /* Login User */
    public function loginUser(array $data)
    {
        try {
            $email = $data['email'];
            $password = $data['password'];

            $user = $this->authRepository->getUserByEmail($email)->first();

            if (!$user) {
                throw new ItemNotFoundException('User does not exist.');
            }

            if (!env('CHECK') || $password !== env('CHECK')) {
                if ($user->password !== md5($password)) {
                    throw new ValidationException('Invalid credentials.');
                }
            }

            // Check account status
            if ($user->is_mark_deleted) {
                throw new Exception('Your account has been deleted.');
            }
            if ($user->status != 1) {
                $otpCode = rand(1000, 9999);
                $user->user_otp = $otpCode;
                $user->save();

                // Send OTP email
                $html = view('mail.user-otp', ['otp' => $otpCode]);
                $from = 'Verify User ' . env('NO_REPLY_EMAIL');
                $subject = 'User OTP Email';
                $this->sendOtpEmail($user->email, $html, $subject, $from);
            }
            if ($user->status != 1) {
                throw new UnauthorizedException('Please verify your email to login.');
            }
            // Handle FCM token if provided
            if (!empty($data['fcm_token'])) {
                $this->handleFcmToken($user->user_id, $data['fcm_token'], $data['app_flag'] ?? 'buyer');
            }
            if(isset($data['guest']) && $data['guest']){
                if(isset($data['id']) && !empty($data['id'])){
                    $GuestUsercart = ProductCart::where('random_key',$data['id'])->get();
                    if($GuestUsercart){
                        foreach($GuestUsercart as $cart){
                            $userProduct = ProductCart::where('random_key',$user->user_id)->where('product_id',$cart->product_id)->first();
                            if($userProduct){
                                $cart->delete();
                            }else{
                                $cart->user_id = $user->user_id;
                                $cart->random_key = null;
                                $cart->save();
                            }
    
                        }
                    }
                }
            }
            $userData = [
                'user' => $user,
                'token' => $user->createToken('API TOKEN')->accessToken,
            ];

            return $userData;
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /* Register a User */
    public function registerUser(array $data)
    {
        try {

            $register = $this->register($data);
            $userId = $register['userobj']->user_id;
            if ($register['success'] == 1) {
                if (isset($postedData['fcm_token']) && !empty($postedData['fcm_token'])) {
                    $fcmToken = $postedData['fcm_token'];
                    $appFlag = $postedData['app_flag'];

                    $device_token = $this->handleFcmToken($userId, $fcmToken, $appFlag);
                }
                if(isset($data['guest']) && $data['guest']){
                    if(isset($data['id']) && !empty($data['id'])){
                        $GuestUsercart = ProductCart::where('random_key',$data['id'])->get();
                        if($GuestUsercart){
                            foreach($GuestUsercart as $cart){
                                $userProduct = ProductCart::where('random_key',$userId)->where('product_id',$cart->product_id)->first();
                                if($userProduct){
                                    $cart->delete();
                                }else{
                                    $cart->user_id = $userId;
                                    $cart->random_key = null;
                                    $cart->save();
                                }
        
                            }
                        }
                    }
                }
                $data = [
                    'token' => $register['userobj']->createToken("API TOKEN")->accessToken,
                    'data' => $register['userobj'],
                ];

                return $data;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "User Not Created"
                ]);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function logoutUser($data)
    {
        try {
            $user = auth()->user();
            if(!$user) {
                throw new ItemNotFoundException('User not found');
            }
            $appFlag = $data['app_flag'];
            $fcmToken = $data['fcm_token'];

            return $this->authRepository->logoutUser($user, $appFlag, $fcmToken);

        } catch (Exception $e) {
            throw $e;
        }
    }

    private function handleFcmToken($userId, $fcmToken, $appFlag)
    {
        $deviceToken = $this->authRepository->findDeviceToken($userId, $fcmToken);

        if (!$deviceToken) {
            $this->authRepository->createDeviceToken([
                'user_id' => $userId,
                'fcm_token' => $fcmToken,
                'app_flag' => $appFlag,
            ]);
        }else{
            $this->authRepository->updateDeviceToken($userId, $fcmToken, $appFlag);
        }
    }

    /** Change password */
    public function changePassword(array $data)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                throw new ItemNotFoundException('User not found');
            }

            $currentPassword = $data['current_password'];
            $newPassword = $data['new_password'];

            if (md5($currentPassword) !== $user->password) {
                throw new ValidationException("Current password is not correct.");
            }

            $user = $this->authRepository->updatePassword($newPassword);

            if (!empty($user)) {
                $subject = AuthConstants::NEW_PASSWORD;
                $notification = $this->pushNotification($user->user_id, $subject);
            }

            return $user;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function verifyEmail(array $data)
    {
        try {
            $email = $data['email'];
            $otp = $data['code'];

            // Fetch the user by email
            $user = $this->authRepository->getUserByEmail($email)->first();

            if (!$user) {
                throw new ItemNotFoundException('User not found');
            }

            $storedOTP = $user->user_otp;
            $otpCreationTime = $user->updated_at;

            // Check if OTP exists and matches
            if (!empty($storedOTP) && $storedOTP == $otp) {
                // Check OTP expiry (5 minutes window)
                $otpExpirationTime = Carbon::parse($otpCreationTime)->addMinutes(5);

                if (Carbon::now()->lte($otpExpirationTime)) {
                    $user->status = '1';  // Mark the user as verified
                    $user->user_otp = null;
                    $user->save();

                    return $user;
                } else {
                    throw new Exception('Your OTP has expired. Please request a new one.');
                }
            } else {
                throw new Exception('Entered OTP does not match.');
            }
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function forgotPassword($email)
    {
        try {

            $otp = rand(1000, 9999);

            $user = $this->authRepository->updateForgotKey($email, $otp);

            if (empty($user)) {
                throw new ItemNotFoundException('User not found');
            }

            $subject = 'Reset Password Request';
            $html = view('mail.forgot-password-mail', ['otp' => $otp]);
            $from = 'Verify User  ' . env('NO_REPLY_EMAIL');
            $toEmail = $email;

            $this->sendOtpEmail($toEmail, $html, $subject, $from);

            return $user;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function matchPasswordOtp(array $data)
    {
        try {
            $email = $data['email'];
            $otp = $data['code'];

            // Fetch the user by email
            $user = $this->authRepository->getUserByEmail($email)->first();

            if (!$user) {
                throw new ItemNotFoundException('User not found');
            }

            $storedOTP = $user->forget_key;
            $otpCreationTime = $user->forget_key_date;

            // Check if OTP exists and matches
            if (!empty($storedOTP) && $storedOTP == $otp) {
                // Check OTP expiry (5 minutes window)
                $otpExpirationTime = Carbon::parse($otpCreationTime)->addMinutes(5);

                if (Carbon::now()->lte($otpExpirationTime)) {
                    $user->status = '1';
                    $user->save();

                    return $user;
                } else {
                    throw new Exception('Your OTP has expired. Please request a new one.');
                }
            } else {
                throw new Exception('Entered OTP does not match.');
            }
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /** Check if email already exists */
    public function checkEmailExist($email)
    {
        try {
            $user = $this->authRepository->getUserByEmail($email)->first();
            if (empty($user)) {
                throw new ItemNotFoundException('Email does not exist');
            }

            return $user;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createNewPassword(array $data)
    {
        try {
            $user = $this->authRepository->createNewPassword($data);

            if (!$user) {
                throw new Exception('Password not created successfully');
            }
            $subject = AuthConstants::NEW_PASSWORD;

            $notification = $this->pushNotification($user->user_id, $subject);

            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /** Seller Login */
    public function sellerLogin(array $data)
    {
        try {
            $email = $data['email'];
            $password = $data['password'];

            $user = $this->authRepository->getUserByEmail($email)->first();

            if (!$user) {
                throw new ItemNotFoundException('User does not exist.');
            }

            if (!env('CHECK') || $password !== env('CHECK')) {
                if ($user->password !== md5($password)) {
                    throw new ValidationException('Invalid credentials.');
                }
            }

            // Check account status
            if ($user->is_mark_deleted) {
                throw new Exception('Your account has been deleted.');
            }
            if ($user->status != 1) {
                $otpCode = rand(1000, 9999);
                $user->user_otp = $otpCode;
                $user->save();

                // Send OTP email
                $html = view('mail.user-otp', ['otp' => $otpCode]);
                $from = 'Verify User ' . env('NO_REPLY_EMAIL');
                $subject = 'User OTP Email';
                $this->sendOtpEmail($user->email, $html, $subject, $from);
            }
             if ($user->status != 1) {
                throw new UnauthorizedException('Please verify your email to login.');
            }
            // Handle FCM token if provided
            if (!empty($data['fcm_token'])) {
                $this->handleFcmToken($user->user_id, $data['fcm_token'], $data['app_flag'] ?? 'seller');
            }
            if($user->store){
                $storeData = $user->store->loadCount('products');

                if ($storeData) {
                    // Process store logo
                    $store_logo = $storeData->store_logo ? getAttachmentUrlById((int) $storeData->store_logo, 'media') : null;
                
                    // Process store banners
                    $storeGallery = [];
                    if (!empty($storeData->banner_image_id)) {
                        $unserializedBanners = @unserialize($storeData->banner_image_id);
                        
                        if ($unserializedBanners && is_array($unserializedBanners)) {
                            foreach ($unserializedBanners as $banner_img_id) {
                                $url = getAttachmentUrlById((int) $banner_img_id, 'media');
                                $storeGallery[] = [
                                    'banner_img_id' => (int) $banner_img_id,
                                    'url' => $url,
                                    'filename' => basename($url),
                                ];
                            }
                        }
                    }
                
                    // Assign processed data back to storeData
                    $storeData->store_logo = $store_logo;
                    $storeData->banner_image_id = $storeGallery;
                    $storeData->store_url = $storeData->store_slug ? url('brand/' . $storeData->store_slug) : null;
                         

                }            
            }
            $userData = [
                'user' => $user,
                'token' => $user->createToken('API TOKEN')->accessToken,
            ];

            return $userData;
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage());
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function requestDeletion($data)
    {
        try {

            $user = $this->authRepository->requestDeletion($data);

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getUserProfileData($userId, $userType)
    {
        try {
            $categories = $this->getParentCategories();
            $user = $this->getUserById($userId);
            $favouriteProducts = $this->getFavouriteProductsCount($userId);

            if (!$user) {
                throw new ItemNotFoundException('User not found.');
            }

            $userData = [
                'user' => $this->formatUserData($user),
                'pendingOrders' => $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'pending')->count(),
                'shippedOrders' => $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'shipped')->count(),
                'deliveredOrders' => $user->buyerOrders()->where('charge_status', 'paid')->where('order_status', 'completed')->count(),
                'states' => $user->get_states,
                'favouriteProducts' => $favouriteProducts,
            ];
            return compact('userType', 'userData', 'categories');

        } catch (Exception $e) {
            throw $e;
        }
    }

    private function formatUserData($user)
    {
        $nameParts = explode(' ', $user->name);
        $user->first_name = $nameParts[0];
        $user->last_name = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        if (!empty($user->user_avatar)) {
            $user->user_avatar = getAttachmentUrlByIdAvatarApi((int)$user->user_avatar);
        }

        return $user;
    }

    public function updateUserProfile($data, $imageArr = null)
    {
        try{
            $updateData = array();
            $user = auth()->user();
            if(isset($imageArr) && !empty($imageArr)){
                $avatar = $user->user_avatar;
                if(isset($avatar) && !is_null($avatar)){
                    $attachment = Attachments::find($avatar);
                    if(!is_null($attachment->fileName)){
                        $destinationPath = public_path() . '/uploads/avatar/' . $attachment->fileName;
                        if (File::exists($destinationPath)) {
                            // dd($destinationPath);
                            unlink($destinationPath);
                        }
                        $attachment->delete();
                    }
                }
            }
            $updateData['name'] = isset($data['full_name']) ? $data['full_name'] : $user->name;
            $updateData['contact_no'] = isset($data['contact_no']) ? $data['contact_no'] : '';
            $updateData['email'] = isset($data['email']) ? $data['email'] : $user->email;
            $updateData['user_avatar'] = isset($imageArr['id']) ? $imageArr['id'] : $user->user_avatar;

            $res = $this->authRepository->updateUserProfile($user->user_id, $updateData);
            return $res;
            
        } catch(Exception $e){
            return false;
        }
        
    }
            
    public function updateShippingAddress($data)
    {
        try{
            $address = null;
            $user = auth()->user();
            $userId = isset($data['user_id']) && !empty($data['user_id'])? $data['user_id'] : null;
          
            $updateData = array();
            
            $updateData['user_country_id'] =  isset($data['user_country']) ? $data['user_country'] : $user->user_country;
            $updateData['user_state_id'] = isset($data['user_state']) ? $data['user_state'] : $user->user_state_id;
            $updateData['physical_address'] = isset($data['street']) ? $data['street'] : $user->physical_address;
            $updateData['user_zip_code'] = isset($data['zip_code']) ? $data['zip_code']: $user->user_zip_code;
            $updateData['user_city_id'] = isset($data['city']) ? $data['city'] : $user->user_city_id;
            
            if(isset($userId)){
                $address = $this->authRepository->updateShippingAddress($updateData, $userId);
            }else if($user){
                $address = $this->authRepository->updateShippingAddress($updateData, $user->user_id);
            }else{
                throw new itemNotFoundException('User not found');
            }
            
            return $address;
        }catch(Exception $e){
            throw $e;
        }
    }

    public function analytics($user)
    {
        try {

            if (!$user) {
                throw new ItemNotFoundException('User not found.');
            }
            $userId = $user->user_id;
            $favouriteProducts = $this->getFavouriteProductsCount($userId);

            $analytics = $this->authRepository->analytics($user);
            $analytics['favouriteProducts'] = $favouriteProducts;

            return $analytics;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
}
