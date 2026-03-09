<?php

namespace App\Services;

use Exception;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Constants;

class FCMService
{
    public static function send($token, $notification)
    {
        try {
            Log::info('FCMService send method called.');

            $accessToken = self::getAccessToken();
            $data = [
                'message' => [
                    'token' => $token,
                    'notification' =>  $notification,
                    // 'notification' => [
                        
                    //     'title' => Constants::ORDER_CREATE_SUBJECT_BUYER . '1798',
                    //     'body'=>'FCM Message'
                    // ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/v1/projects/mallshark-notifications/messages:send', $data);

            if ($response->failed()) {
                throw new Exception('FCM request failed: ' . $response->body());
            }

            return response()->json(['status'=>true,'message'=>'sent']);
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            // Token is unregistered
            Log::error("FCM token not found. Token should be deleted.");
            // Optionally remove it from DB
        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            Log::error("Invalid FCM message: " . $e->getMessage());
        }
    }

    private static function getAccessToken()
    {   try{
        
        
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/mallshark-notifications-0d34da2cbe5b.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithAssertion();
        }
        
        return $client->getAccessToken()['access_token'];
    }catch(Exception $e){
        return response()->json(['status' => false, 'message client' => $e->getMessage()]);
    }
    }
}
