<?php

namespace Packages\Klavio\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Packages\Klavio\Contracts\KlavioHelpers;
use Illuminate\Support\ItemNotFoundException;
use Packages\Klavio\Repositories\Eloquent\KlavioRepository;

class KlavioService
{
    // use KlavioHelpers;
    protected $klavioRepository;

    public function __construct(KlavioRepository $klavioRepository)
    {
        $this->klavioRepository = $klavioRepository;
    }

    public function subscribe($data)
    {

        $email = $data['email'];

        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $listId = env('KLAVIYO_SUBSCRIBE_LIST_ID');
        $revision = "2025-01-15"; // Specify the required API version
        $baseUrl = 'https://a.klaviyo.com/api/profile-subscription-bulk-create-jobs';

        $client = new Client();
        $payload = [
            "data" => [
                "type" => "profile-subscription-bulk-create-job",
                "attributes" => [
                    "profiles" => [
                        "data" => [
                            [
                                "type" => "profile",
                                "attributes" => [
                                    "subscriptions" => [
                                        "email" => [
                                            "marketing" => [
                                                "consent" => "SUBSCRIBED",
                                            ]
                                        ]
                                    ],
                                    "email" => $email,
                                ]
                            ]
                        ]
                    ],
                    "custom_source" => "After Checkout",
                    "historical_import" => false
                ],
                "relationships" => [
                    "list" => [
                        "data" => [
                            "type" => "list",
                            "id" => $listId
                        ]
                    ]
                ]
            ]
        ];

        try {
            // Create the profile in Klaviyo
            $response = $this->klavioRepository->subscribe($client, $baseUrl, $payload, $apiKey, $revision);

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function addToList()
    {
        try {

            return $this->klavioRepository->addToList();
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function addToMetrice()
    {
        try {

            return $this->klavioRepository->addToMetrice();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
