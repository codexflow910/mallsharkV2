<?php

namespace Packages\Klavio\Repositories\Eloquent;

use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\ItemNotFoundException;
use Packages\Klavio\Contracts\KlavioRepositoryInterface;


class KlavioRepository implements KlavioRepositoryInterface
{

    protected $model;

    public function __construct() {}

    public function subscribe($client, $baseUrl, $payload, $apiKey, $revision)
    {
        try {
            $response = $client->post($baseUrl, [
                'json' => $payload,
                'headers' => [
                    'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
                    'Revision' => $revision,
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() != 200) {
                \Log::error('Error creating profile subscription.', ['response' => $responseData ?? 'No response data']);
            }

            if ($responseData == null) {
                return true;
            }
            return $responseData;
        } catch (\Exception $e) {
            \Log::error('Error sending request to Klaviyo API: ' . $e->getMessage(), ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    public function createKlaviyoProfile($email, $first_name = null, $last_name = null)
    {
        // Klaviyo API Key (Private Key)
        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $revision = "2025-01-15";

        // Klaviyo API endpoint to create or update profile
        $url = 'https://a.klaviyo.com/api/profiles/';

        $client = new Client();
        try {
            $response = $client->post($url, [
                'json' => [
                    'data' => [
                        'type' => 'profile',
                        'attributes' => [
                            'email' => $email,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                        ]
                    ]
                ],
                'headers' => [
                    'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
                    'Revision' => $revision, // Just use $revision without the 'Revision' string prefix
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            // Optionally log the response or handle errors
            if ($response->getStatusCode() != 200) {
                // Handle error
                \Log::error('Error creating Klaviyo profile.', ['response' => $responseData ?? 'No response data']);
            }

            return $responseData;
        } catch (\Exception $e) {
            \Log::error('Error creating Klaviyo profile: ' . $e->getMessage(), ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    public function addToKlaviyoList($profileId, $listId)
    {
        // Klaviyo API Key (Private Key)
        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $revision = "2025-01-15"; // Specify the required API version

        // Klaviyo API endpoint to add profile to list
        $url = 'https://a.klaviyo.com/api/lists/' . $listId . '/relationships/profiles';

        // Initialize Guzzle HTTP client
        $client = new Client();
        $data = [
            'data' => [
                [
                    'type' => 'profile',
                    'id' => $profileId
                ]
            ]
        ];

        try {
            $response = $client->post($url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
                    'Revision' => $revision,
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() != 200) {

                \Log::error('Error adding profile to list.', ['response' => $responseData ?? 'No response data']);
            }

            return $responseData;
        } catch (\Exception $e) {
            // Handle exceptions and errors
            \Log::error('Error sending request to Klaviyo List API: ' . $e->getMessage(), ['exception' => $e->getMessage()]);
            throw $e;
        }
    }



    //     public function newsletter()
    //     {
    //           $apiKey = env('KLAVIYO_PRIVATE_KEY');
    // $revision = "2025-01-15"; // Specify the required API version


    // // To add Profile
    // $data = [
    //     "data" => [
    //         "type" => "profile",
    //         "attributes" => [
    //             "email" => "hapil27669@jomspar.com",
    //             "first_name" => "Arslan",
    //             "last_name" => "Shafiq",
    //             // "phone_number" => "+12348678901",
    //             // Add other profile attributes here
    //         ]
    //     ]
    // ];

    // $ch = curl_init();

    // curl_setopt($ch, CURLOPT_URL, "https://a.klaviyo.com/api/profiles");
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    // curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //     "Authorization: Klaviyo-API-Key {$apiKey}",
    //     "Revision: {$revision}",
    //     "Content-Type: application/json"
    // ]);
    // $response = curl_exec($ch);
    // $error = curl_error($ch);
    // curl_close($ch);

    // if ($error) {
    //     return "cURL Error: $error";
    // }
    // dd($response);


    //      dd(3);

    //     }
    public function addToList()
    {

        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $revision = "2025-01-15"; // Specify the required API version


        // To add Profile
        $data = [
            "data" => [
                [
                    "type" => "profile",
                    "id" => "01JNGB47P4745XGQFYXRF4BFTE"
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://a.klaviyo.com/api/lists/RvqQgG/relationships/profiles");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Klaviyo-API-Key {$apiKey}",
            "Revision: {$revision}",
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "cURL Error: $error";
        }
        dd($response);


        dd(3);
    }

    public function addToMetrice()
    {

        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $revision = "2025-01-15"; // Specify the required API version   
        // To add Profile
        $data = [
            "token" => "W9RFKq", // Your Klaviyo API token
            "event" =>   "Started Checkout",  //$request->event_name, // Event name like "Started Checkout"
            "customer_properties" => [
                "email" =>   "l217630@lhr.nu.edu.pk", //  $request->email,
                "first_name" => "Arslan", //$request->first_name,
                "last_name" => "Kashif"  // $request->last_name,
            ],
            "properties" =>   [
                "ProductName" => "Black Hoodie",
                "ProductID" => "45678",
                "Price" => 39.99,
                "Quantity" => 1
            ]

            //$request->properties // Cart ID, TotalPrice, etc.
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://a.klaviyo.com/api/track");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Klaviyo-API-Key {$apiKey}",
            "Revision: {$revision}",
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "cURL Error: $error";
        }
        dd($response);


        dd(3);
    }
}
