<?php

namespace Packages\Stripe\Repositories\Eloquent\V2;


use DB;
use Session;
use App\BaseAccess;
use Packages\Store\Models\V2\Store;
use Illuminate\Support\ItemNotFoundException;
use Packages\Stripe\Contracts\StripeRepositoryInterface;
use Packages\Stripe\Contracts\BaseApiResponse;
use Illuminate\Validation\UnauthorizedException;
use Stripe\Payout;
use Stripe\Stripe;
use Illuminate\Support\Facades\URL;
use Stripe\Transfer;
use Stripe\Stripe as StripeLib;
use Exception;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Models\V2\Product;
use Packages\Store\Services\V2\StoreSetupService;

class StripeRepository implements StripeRepositoryInterface
{
    use BaseApiResponse;
    private $store;

    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_API_KEY'));
        $this->store = new Store();
    }
    public function getPayouts(array $params)
    {
        $params['expand'] = ['data.destination'];
        return Payout::all($params)->data;
    }

    public function getPayoutById($id)
    {
        try {
            $payout = Payout::retrieve([
                'id' => $id,
                'expand' => ['destination'],
            ]);

            return $payout;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Payout record not found for this ID: ' . $id);
        }
    }
    public function genrateStripeOnBoardLinkApi()
    {
        try {

            $userId = auth()->user()->user_id;
            if (empty($userId)) {
                throw new UnauthorizedException('No User Found');
            }

            $storeStatus = $this->store->select('stripe', 'account_id')->where('user_id', $userId)->first();

            if ($storeStatus->stripe == '0' || $storeStatus->stripe == 0) {
                // if($storeStatus->account_id == ''){ 
                $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
                $account = $stripe->accounts->create([
                    'type' => 'express',
                    'country' => 'US',
                ]);
                $link = $stripe->accountLinks->create([
                    'account' => $account->id,
                    'refresh_url' => env('STRIPE_ONBOARDING_REDIRECT'),
                    'return_url' => env('STRIPE_ONBOARDING_REDIRECT'),
                    'type' => 'account_onboarding',
                ]);

                $this->store->where('user_id', $userId)->update(['account_id' => "$account->id", 'account_link' => "$link->url"]);
                return $link->url;
            } else if ($storeStatus->stripe == '1' || $storeStatus->stripe == 1) {
                throw new \Exception("Stripe Already active");
            } else {
                return;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function editStripeUserApi($account)
    {
        try {
       
        $userId = auth()->user()->user_id;
     
            if (empty($userId)) {
                throw new UnauthorizedException('No User Found');
            }
        $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
        // return response()->json($stripe);
        $response = $stripe->accounts->retrieve(
            $account,
            []
        );


        $link = $stripe->accountLinks->create([
            'account' => $response->id,
            'refresh_url' => env('STRIPE_ONBOARDING_REDIRECT'),
            'return_url' => env('STRIPE_ONBOARDING_REDIRECT'),
            'type' => 'account_onboarding',
        ]);
        // echo '$link: <pre>' .print_r($link,true). '</pre>'; die();
        $details = $response->details_submitted;

        if ($details == '1') {

            $this->store->where('user_id', $userId)->update(['stripe' => "1",'stripe_connected' => "1"]);
        }
        return $link->url;
    }catch (\Exception $e) {
            throw $e;
        }
    }

    public function getStripeLoginLink($data)
    {
        try {
            $userId = auth()->user()->user_id;
            $accountId = $data['account_id'];
            $newStripeStatus = $data['stripe_status'];

            $store = $this->store->where('user_id', $userId)->where('stripe_connected', '1')->first();

            if (!$store) {
                throw new ItemNotFoundException('Store not found for this user.');
            }

            if ($store->stripe == 0) {
                if ($store->stripe_connected == 1) {
                    $this->store->where('user_id', $userId)->update(['stripe' => $newStripeStatus]);
                }
            } elseif ($store->stripe == 1) {
                // $this->deactivateProductsForUser($userId);

                $this->store->where('user_id', $userId)->update([
                    'stripe' => '0',
                    'store_status' => '0'
                ]);
            }

            return $this->store->where('account_id', $accountId)->first();
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Error fetching Stripe login link: ' . $e->getMessage());
        }
    }

    public function deactivateProductsForUser($userId) 
    {
        $products = Product::where('user_id', $userId)
            ->whereNotIn('status', ['2', '3', '5'])
            ->get(); // Get the collection first

        foreach ($products as $product) {
            $product->update(['status' => ProductConstants::STATUS_DEACTIVATED]);
            $product->unsearchable();
        }
    }

    public function onLoadConnectStripeAccount($data)
    {
          try {
            $user = auth()->user();
            $userId = $user->user_id;
            $accountId = $data['account_id'];

            $store = $this->store
                ->with('user')
                ->select('stripe','user_id', 'store_id', 'store_name','store_logo', 'stripe_connected')
                ->where('user_id', $userId)
                ->first();

            if (!$store) {
                throw new ItemNotFoundException('Store not found.');
            }

            // Proceed only if stripe and stripe_connected are both 0
            if ($store->stripe == 0 && $store->stripe_connected == 0) {
                $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));

                $response = $stripe->accounts->retrieve($accountId);
                $storeSetupService = app(StoreSetupService::class);
                $storeSetupService->stripeAccountConnected($store);
                if ($response->details_submitted) {
                    $this->store->where('user_id', $userId)->update([
                        'stripe' => 1,
                        'stripe_connected' => 1
                    ]);

                    // Refresh the updated store
                    $store = $this->store
                        ->select('stripe', 'store_id', 'stripe_connected')
                        ->where('user_id', $userId)
                        ->first();
                }
            }

            return $store;

        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new \Exception("An unexpected error occurred: " . $e->getMessage());
        }
    }

}
