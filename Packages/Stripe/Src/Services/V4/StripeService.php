<?php

namespace Packages\Stripe\Services\V4;

use Exception;
use Illuminate\Support\ItemNotFoundException;
use Packages\Stripe\Contracts\V4\BaseApiResponse;
use Packages\Stripe\Contracts\V4\StripeHelpers;
use Packages\Core\Contracts\V4\CoreHelpers;
use Illuminate\Validation\UnauthorizedException;
use Packages\Stripe\Repositories\Eloquent\V4\StripeRepository;
use Stripe\Payout;
use Stripe\Stripe;
class StripeService

{
    use BaseApiResponse;
    use StripeHelpers;
    use CoreHelpers;
    protected $stripeRepository;

    public function __construct(StripeRepository $stripeRepository)
    {
        $this->stripeRepository = $stripeRepository;
    }

    public function getAllPayouts(array $data)
    {
        try {
            // Convert date strings to timestamps, with fallback to last 3 months
            $startDate = isset($data['start_date']) ? strtotime($data['start_date']) : strtotime('-3 months');
            $endDate = isset($data['end_date']) ? strtotime($data['end_date']) : time(); // Default to now
    
            // Validate timestamps
            if (!$startDate || !$endDate) {
                throw new Exception('Invalid date format. Please use YYYY-MM-DD.');
            }
    
            $params = [
                'created' => [
                    'gte' => $startDate,
                    'lte' => $endDate,
                ],
                'expand' => ['data.destination'], // Expand additional details if needed
            ];
    
            // Add status filter directly in the API request if provided
            if (!empty($data['status'])) {
                $params['status'] = $data['status'];
            }
    
            $payouts = $this->stripeRepository->getPayouts($params);
    
            if (empty($payouts)) {
                throw new ItemNotFoundException('Payout record not found.');
            }
    
            // Convert to collection for easier filtering
            $payoutCollection = collect($payouts);
    
            // Apply ID filter after retrieving data
            if (!empty($data['id'])) {
                $payoutCollection = $payoutCollection->where('id', $data['id']);
            }
    
            if ($payoutCollection->isEmpty()) {
                throw new ItemNotFoundException('Payout record not found for the given filters.');
            }
    
            return $payoutCollection->sortByDesc('created')->values()->toArray();
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Error fetching payouts: ' . $e->getMessage());
        }
    }
    
    public function getPayoutById($id)
    {
        try {
            
            $payout = $this->stripeRepository->getPayoutById($id);
    
            if (empty($payout)) {
                throw new ItemNotFoundException('Payout record not found for this ID: ' . $id );
            }
    
            return $payout;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException( $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getPayoutsByDateRange(int $startTimestamp, int $endTimestamp)
    {
        try {
    
            $params = [
                'created' => [
                    'gte' => $startTimestamp,
                    'lte' => $endTimestamp,
                ],
                'expand' => ['data.destination'], 
            ];
    
            $payouts = $this->stripeRepository->getPayouts($params);
    
            if (empty($payouts)) {
                throw new ItemNotFoundException('No payouts found for the specified date range.');
            }
    
            return collect($payouts)->sortByDesc('created')->values()->toArray();
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException( $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Error fetching payouts by date range: ' . $e->getMessage());
        }
    }
    public function activateStipe()
    {
        try {


            $stripe = $this->stripeRepository->genrateStripeOnBoardLinkApi();

            if (empty($stripe)) {
                throw new \Exception("Error in Stripe ");
               
            }
           return  $stripe;
         
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function editStripeAccount()
    {
        try {

            $userId = auth()->user()->user_id;
            if (empty($userId)) {
                throw new UnauthorizedException('No User Found');
            }
            $store = getStoreDataByUserId($userId);
            if (empty($store)) {
                throw new UnauthorizedException('No Store Found');
            }
            $posedData = $store->account_id;
            $stripe = $this->stripeRepository->editStripeUserApi($posedData);

            if (empty($stripe)) {
                throw new \Exception("Error in Stripe ");
            }
            return  $stripe;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getStripeLoginLink($data)
    {
        try {
            $stripe = $this->stripeRepository->getStripeLoginLink($data);

            if (empty($stripe)) {
                throw new ItemNotFoundException('Stripe login link not found.');
            }

            return $stripe;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function onLoadConnectStripeAccount($data)
    {
        return $this->stripeRepository->onLoadConnectStripeAccount($data);
    }
}
