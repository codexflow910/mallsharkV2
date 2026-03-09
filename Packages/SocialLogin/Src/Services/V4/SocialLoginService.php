<?php

namespace Packages\SocialLogin\Services\V4;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Google_Client;
use Illuminate\Support\Facades\Http;
use Packages\Core\Models\V4\MallsharkPurchasedPackage;
use Packages\SocialLogin\Contracts\V4\SocialLoginHelpers;
use Packages\SocialLogin\Repositories\Eloquent\V4\SocialLoginRepository;
use Packages\Auth\Config\AuthConstants;
class SocialLoginService

{
    use SocialLoginHelpers;
    protected $socialLoginRepository;

    public function __construct(SocialLoginRepository $socialLoginRepository)
    {
        $this->socialLoginRepository = $socialLoginRepository;
    }

    public function loginFacebookUser(array $data)
    {
        try {
            $accessToken = $data['id_token'];
            $appSecret = $data['app_secret'];
    
            $fcmToken = $data['fcm_token'];
            $appFlag = $data['app_flag'];
    
            $appSecretProof = hash_hmac('sha256', $accessToken, $appSecret);
    
            // Make the API request to Facebook
            $response = Http::get('https://graph.facebook.com/v3.3/me', [
                'access_token' => $accessToken,
                'fields' => 'name,email',
                'appsecret_proof' => $appSecretProof,
            ]);
    
            // Check if the request failed
            if ($response->failed()) {
                throw new Exception('Failed to fetch user data from Facebook');
            }
    
            $facebookUser = $response->json();
    
            // Check if email is present in the Facebook response
            if (!isset($facebookUser['email'])) {
                throw new Exception('Email not found in Facebook response');
            }
    
            try {
                // Find or create the user
                $user = $this->socialLoginRepository->findOrCreateUser([
                    'email' => $facebookUser['email'],
                    'name' => $facebookUser['name'],
                    'register_source' => 'facebook',
                ]);
                
                // Load store relation
                $user->load([
                    'store' => function ($query) {
                        $query->withCount('products'); 
                    }
                ]);
                
                // Check if store exists before processing
                if ($user->store) {
                    // Process store logo
                    $user->store->store_logo = $user->store->store_logo 
                        ? getAttachmentUrlById((int) $user->store->store_logo, 'media') 
                        : null;

                    // Initialize store gallery
                    $storeGallery = [];

                    // Unserialize and process banner images
                    $bannerImageData = $user->store->banner_image_id;
                    $bannerImageIds = @unserialize($bannerImageData);

                    if ($bannerImageIds && is_array($bannerImageIds)) {
                        foreach ($bannerImageIds as $bannerId) {
                            $url = getAttachmentUrlById((int) $bannerId, 'media');
                            $filename = basename($url);

                            $storeGallery[] = [
                                'banner_img_id' => (int) $bannerId,
                                'url' => $url,
                                'filename' => $filename
                            ];
                        }
                    }

                    // Add processed values back to store object
                    $user->store->store_url = $user->store->store_slug 
                        ? url('brand/' . $user->store->store_slug) 
                        : null;
                    
                    $user->store->banner_image_urls = $storeGallery;
                }
            } catch (Exception $e) {
                throw new Exception('Error while finding or creating user: ' . $e->getMessage());
            }
    
            try {
                // Check or create device token
                $deviceToken = $this->socialLoginRepository->findDeviceToken($user->user_id, $fcmToken);
                if (is_null($deviceToken)) {
                    $this->socialLoginRepository->createDeviceToken([
                        'user_id' => $user->user_id,
                        'fcm_token' => $fcmToken,
                        'app_flag' => $appFlag,
                    ]);
                }
            } catch (Exception $e) {
                throw new Exception('Error while handling device token: ' . $e->getMessage());
            }
    
            $token = $user->createToken('API Token')->accessToken;

            return [
                'user' => $user,
                'token' => $token, 
            ];
        } catch (Exception $e) {
            throw new Exception('Facebook login failed: ' . $e->getMessage());
        }
    }
    

    public function loginGoogleUser(array $data)
    {
        try {
            $client = new Google_Client(['client_id' => $data['google_client_id']]);
            $googleUser = $client->verifyIdToken($data['id_token']);
            
            $fcmToken = $data['fcm_token'];
            $appFlag = $data['app_flag'];
    
            // Check if the Google user information is valid
            if (!$googleUser || !isset($googleUser['email'])) {
                throw new Exception('Unable to fetch user information from Google');
            }

            // Try to find or create the user
            $status = AuthConstants::USER_STATUS_ACTIVE;
            try {
                $user = $this->socialLoginRepository->findOrCreateUser([
                    'email' => $googleUser['email'],
                    'name' => $googleUser['name'],
                    'register_source' => 'google',
                    'status' => $status,
                ]);
                                
                // Load store relation
                $user->load([
                    'store' => function ($query) {
                        $query->withCount('products'); 
                    }
                ]);

                // Check if store exists before processing
                if ($user->store) {
                    // Process store logo
                    $user->store->store_logo = $user->store->store_logo 
                        ? getAttachmentUrlById((int) $user->store->store_logo, 'media') 
                        : null;

                    // Initialize store gallery
                    $storeGallery = [];

                    // Unserialize and process banner images
                    $bannerImageData = $user->store->banner_image_id;
                    $bannerImageIds = @unserialize($bannerImageData);

                    if ($bannerImageIds && is_array($bannerImageIds)) {
                        foreach ($bannerImageIds as $bannerId) {
                            $url = getAttachmentUrlById((int) $bannerId, 'media');
                            $filename = basename($url);

                            $storeGallery[] = [
                                'banner_img_id' => (int) $bannerId,
                                'url' => $url,
                                'filename' => $filename
                            ];
                        }
                    }

                    // Add processed values back to store object
                    $user->store->store_url = $user->store->store_slug 
                        ? url('brand/' . $user->store->store_slug) 
                        : null;
                    
                    $user->store->banner_image_urls = $storeGallery;
                    $storeId= $user->store->store_id;
                    if (!empty($storeId)) {
                        $package = MallsharkPurchasedPackage::where('store_id', $storeId)
                            ->where('status', 'active') 
                            ->latest()
                            ->first();
                        $user->store->package = $package;
                    }
  
                }
            } catch (Exception $e) {
                throw new Exception('Error while finding or creating user: ' . $e->getMessage());
            }
    
            // Try to check if the device token exists or create a new one
            try {
                $deviceToken = $this->socialLoginRepository->findDeviceToken($user->user_id, $fcmToken);
                if (is_null($deviceToken)) {
                    $this->socialLoginRepository->createDeviceToken([
                        'user_id' => $user->user_id,
                        'fcm_token' => $fcmToken,
                        'app_flag' => $appFlag,
                    ]);
                }
            } catch (Exception $e) {
                throw new Exception('Error while handling device token: ' . $e->getMessage());
            }
    
            // Generate the access token for the user
            $token = $user->createToken('API Token')->accessToken;
    
            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (Exception $e) {

            throw new Exception('Google login failed: ' . $e->getMessage());
        }
    }
    
    public function loginAppleUser(array $data)
    {
        
        try {
            $idToken = $data['id_token'];
            $fcmToken = $data['fcm_token'];
            $appFlag = $data['app_flag'];

            // Verify Apple ID Token
            $applePayload = $this->verifyAppleIdToken($idToken);

            if (!$applePayload || !isset($applePayload['email']) || !isset($applePayload['name'])) {
                throw new Exception('Unable to fetch user information from Apple');
            }

            // Try to find or create the user
            try {
                $user = $this->socialLoginRepository->findOrCreateUser([
                    'email' => $applePayload['email'],
                    'name' => $applePayload['name'],
                    'register_source' => 'apple',
                ]);
                                
                // Load store relation
                $user->load([
                    'store' => function ($query) {
                        $query->withCount('products');
                    }
                ]);

                // Check if store exists before processing
                if ($user->store) {
                    // Process store logo
                    $user->store->store_logo = $user->store->store_logo 
                        ? getAttachmentUrlById((int) $user->store->store_logo, 'media') 
                        : null;

                    // Initialize store gallery
                    $storeGallery = [];

                    // Unserialize and process banner images
                    $bannerImageData = $user->store->banner_image_id;
                    $bannerImageIds = @unserialize($bannerImageData);

                    if ($bannerImageIds && is_array($bannerImageIds)) {
                        foreach ($bannerImageIds as $bannerId) {
                            $url = getAttachmentUrlById((int) $bannerId, 'media');
                            $filename = basename($url);

                            $storeGallery[] = [
                                'banner_img_id' => (int) $bannerId,
                                'url' => $url,
                                'filename' => $filename
                            ];
                        }
                    }

                    // Add processed values back to store object
                    $user->store->store_url = $user->store->store_slug 
                        ? url('brand/' . $user->store->store_slug) 
                        : null;
                    
                    $user->store->banner_image_urls = $storeGallery;
                }
            } catch (Exception $e) {
                throw new Exception('Error while finding or creating user: ' . $e->getMessage());
            }
    
            // Try to check if the device token exists or create a new one
            try {
                $deviceToken = $this->socialLoginRepository->findDeviceToken($user->user_id, $fcmToken);
                if (is_null($deviceToken)) {
                    $this->socialLoginRepository->createDeviceToken([
                        'user_id' => $user->user_id,
                        'fcm_token' => $fcmToken,
                        'app_flag' => $appFlag,
                    ]);
                }
            } catch (Exception $e) {
                throw new Exception('Error while handling device token: ' . $e->getMessage());
            }

            // Generate the access token for the user
            $token = $user->createToken('API Token')->accessToken;
    
            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (Exception $e) {

            throw new Exception('Apple login failed: ' . $e->getMessage());
        }
    }

    private function verifyAppleIdToken($idToken)
    {
        // Fetch Apple's public keys
        $publicKeys = json_decode(file_get_contents('https://appleid.apple.com/auth/keys'), true);

        if (!$publicKeys || !isset($publicKeys['keys'])) {
            throw new Exception('Failed to fetch Apple public keys');
        }
        
        // Decode the token using the parsed key set
        $decoded = JWT::decode($idToken, JWK::parseKeySet($publicKeys));

        return (array) $decoded;
    }
}
