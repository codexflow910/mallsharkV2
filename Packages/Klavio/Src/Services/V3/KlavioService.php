<?php

namespace Packages\Klavio\Services\V3;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use  Packages\Klavio\Repositories\Eloquent\V3\KlavioRepository;

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
            // 1. Create the profile in Klaviyo
            $this->klavioRepository->createKlaviyoProfile($email, $first_name, $last_name);
            $this->trackSubscribedToNewsletterEvent($email, $first_name, $last_name);
        } catch (Exception $e) {
            throw $e;
        }
    }


    public function createKlaviyoProfile(array $data)
    {
        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $url = 'https://a.klaviyo.com/api/profiles/';
        $client = new Client();

        $payload = [
            'data' => [
                'type' => 'profile',
                'attributes' => [
                    'email' => $data['email'],
                    'first_name' => $data['name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                ]
            ]
        ];


    

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Klaviyo-API-Key {$apiKey}",
                    'revision' => '2023-10-15',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $payload
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            \Log::error('Klaviyo Error', [
                'message' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
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

   
    // Add to Cart
    public function trackEvent(array $eventData)
    {

        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $url = 'https://a.klaviyo.com/api/events/';
        $payload = [
            'data' => [
                'type' => 'event',
                'attributes' => [
                    'metric' => [
                        'data' => [
                            'type' => 'metric',
                            'attributes' => [
                                'name' => 'Added to Cart'
                            ]
                        ]
                    ],
                    'profile' => [
                        'data' => [
                            'type' => 'profile',
                            'attributes' => [
                                'email' => $eventData['email'],
                              
                            ]
                        ]
                    ],
                    'properties' => [
                        'product_id' => (string) $eventData['product_id'],
                        'product_name' => $eventData['product_name'],
                        'quantity' => (string) $eventData['product_quantity'],
                      
                    ],
                    
                ]
            ]
        ];
        

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Klaviyo-API-Key {$apiKey}",
                    'revision' => '2023-10-15',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload
            ]);


        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : 'No body';

            \Log::error('Klaviyo API Client Error', [
                'message' => $e->getMessage(),
                'body' => $body,
                'payload' => $payload,
            ]);

        }
    }
    // Started Checkout Event
    public function trackStartedCheckout(array $eventData)
    {
        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $url = 'https://a.klaviyo.com/api/events/';
        $payload = [
            'data' => [
                'type' => 'event',
                'attributes' => [
                    'metric' => [
                        'data' => [
                            'type' => 'metric',
                            'attributes' => [
                                'name' => 'Started Checkout'   
                            ]
                        ]
                    ],
                    'profile' => [
                        'data' => [
                            'type' => 'profile',
                            'attributes' => [
                        'email' => $eventData['email'] ?? null,
                            ]
                        ]
                    ],
                    'properties' => [
                        'checkout_id' => $eventData['checkout_id'] ?? null,
                        'cart_items' => $eventData['cart_items'] ?? [],   // you can pass an array of products or items being checked out
                        'total_price' => (string) ($eventData['total_price'] ?? '0'),
                        'currency' => $eventData['currency'] ?? 'USD',
                    ],
                ]
            ]
        ];

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Klaviyo-API-Key {$apiKey}",
                    'revision' => '2023-10-15',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload
            ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : 'No body';

            \Log::error('Klaviyo API Client Error', [
                'message' => $e->getMessage(),
                'body' => $body,
                'payload' => $payload,
            ]);
        }
    }
 
    
    // Added to Favorites Event
    public function trackFavoriteEvent($user, $product)
    {
        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $url = 'https://a.klaviyo.com/api/events/';
        
        $data = [
            'data' => [
                'type' => 'event',
                'attributes' => [
                    'metric' => [
                        'data' => [
                            'type' => 'metric',
                            'attributes' => [
                                'name' => 'Added to Favorites'
                            ]
                        ]
                    ],
                    'profile' => [
                        'data' => [
                            'type' => 'profile',
                            'attributes' => [
                                'email' => $user->email,

                            ]
                        ]
                    ],
                    'properties' => [
                        'product_id' => $product->product_id,
                        'product_name' => $product->title,
                        'product_price' => $product->price,
                        'discount_price' => $product->sale_price,
                    ],
                ]
            ]
        ];

    
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Klaviyo-API-Key {$apiKey}",
                    'revision' => '2023-10-15',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $data
            ]);


        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : 'No body';

            \Log::error('Klaviyo API Client Error', [
                'message' => $e->getMessage(),
                'body' => $body,
                'payload' => $data,
            ]);

        }
    }
    // Track Search Event
    public function trackSearchEvent( $email,$keyword)
    {
        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $url = 'https://a.klaviyo.com/api/events/';

        

         $data = [
            'data' => [
                'type' => 'event',
                'attributes' => [
                    'metric' => [
                        'data' => [
                            'type' => 'metric',
                            'attributes' => [
                                'name' => 'Searched Product'
                            ]
                        ]
                    ],
                    'profile' => [
                        'data' => [
                            'type' => 'profile',
                            'attributes' => [
                                'email' => $email,
                            ]
                        ]
                    ],
                        
                    'properties' => [
                        'keyword' => $keyword
                    ],
                ]
            ]
        ];

        try {
            $client = new \GuzzleHttp\Client();

            $client->post($url, [
                'headers' => [
                    'Authorization' => "Klaviyo-API-Key {$apiKey}",
                    'revision' => '2023-10-15',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $data
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = $response ? $response->getBody()->getContents() : 'No body';

            \Log::error('Klaviyo Search Event Error', [
                'message' => $e->getMessage(),
                'body' => $body,
                'payload' => $data,
            ]);
        }
    }
    // Track Active on Site Event
    public function trackActiveOnSiteEvent($email)
    {
        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $url = 'https://a.klaviyo.com/api/events/';

        $data = [
            'data' => [
                'type' => 'event',
                'attributes' => [
                    'metric' => [
                        'data' => [
                            'type' => 'metric',
                            'attributes' => [
                                'name' => 'Active on Site'
                            ]
                        ]
                    ],
                    'profile' => [
                        'data' => [
                            'type' => 'profile',
                            'attributes' => [
                                'email' => $email,
                            ]
                        ]
                    ],
                    'properties' => [
                        'source' => 'signup' 
                    ],
                ]
            ]
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $client->post($url, [
                'headers' => [
                    'Authorization' => "Klaviyo-API-Key {$apiKey}",
                    'revision' => '2023-10-15',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $data
            ]);
        } catch (ClientException $e) {
           
        }
    }
    // Track Subscribed to Newsletter Event
    public function trackSubscribedToNewsletterEvent($email, $first_name = '', $last_name = '')
    {
        $apiKey = env('KLAVIYO_PRIVATE_KEY');
        $url = 'https://a.klaviyo.com/api/events/';

        $payload = [
            'data' => [
                'type' => 'event',
                'attributes' => [
                    'metric' => [
                        'data' => [
                            'type' => 'metric',
                            'attributes' => [
                                'name' => 'Subscribed to Newsletter'
                            ]
                        ]
                    ],
                    'profile' => [
                        'data' => [
                            'type' => 'profile',
                            'attributes' => [
                                'email' => $email,
                                'first_name' => $first_name,
                                'last_name' => $last_name
                            ]
                        ]
                    ],
                    'properties' => [
                        'source' => 'Newsletter Signup'
                    ],
                ]
            ]
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $client->post($url, [
                'headers' => [
                    'Authorization' => "Klaviyo-API-Key {$apiKey}",
                    'revision' => '2023-10-15',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload
            ]);
        } catch (\Exception $e) {
        }
    }
}
