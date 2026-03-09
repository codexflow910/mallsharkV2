<?php

namespace Packages\Stripe\Repositories\Eloquent;


use DB;
use Session;
use App\BaseAccess;
use Packages\Store\Models\Store;
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

            $this->store->where('user_id', $userId)->update(['stripe' => "1"]);
        }
        return $link->url;
    }catch (\Exception $e) {
            throw $e;
        }
    }
}
