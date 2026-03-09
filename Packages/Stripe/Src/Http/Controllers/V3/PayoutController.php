<?php

namespace Packages\Stripe\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Stripe\Contracts\V3\BaseApiResponse;
use Packages\Stripe\Services\V3\StripeService;

class PayoutController extends Controller
{
    use BaseApiResponse;


    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function getAllPayouts(Request $request)
    {
        try {
            $data = $this->stripeService->getAllPayouts($request->all());
            return $this->successResponse($data, 'Payouts retrieved successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPayout($id)
    {
        try {
            $data = $this->stripeService->getPayoutById($id);
            return $this->successResponse($data, 'Payout retrieved successfully.', Response::HTTP_OK);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPayoutsByDateRange(Request $request)
    {
        try {
            // Validate input dates
            $startDate = $request->input('start_date'); // Expected format: YYYY-MM-DD
            $endDate = $request->input('end_date'); // Expected format: YYYY-MM-DD

            if (!$startDate || !$endDate) {
                throw new ValidationException('Both start_date and end_date are required.');
            }

            // Convert dates to timestamps
            $startTimestamp = strtotime($startDate);
            $endTimestamp = strtotime($endDate);

            if ($startTimestamp === false || $endTimestamp === false) {
                throw new ValidationException('Invalid date format. Use YYYY-MM-DD.');
            }

            $payouts = $this->stripeService->getPayoutsByDateRange($startTimestamp, $endTimestamp);

            return $this->successResponse($payouts, 'Payouts retrieved successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function activateStipe(Request $request)
    {
        try {

            $stripe = $this->stripeService->activateStipe();

            return $this->successResponse($stripe, 'Url Link');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof UnauthorizedException) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function editStripeAccount(Request $request)
    {
        try {

            $stripe = $this->stripeService->editStripeAccount();

            return $this->successResponse($stripe, 'Url Link');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof UnauthorizedException) {
                return response()->json(['Error' => $e->getMessage()], 401);
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getStripeLoginLink(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'account_id' => 'required|string',
                'stripe_status' => 'required|boolean|in:0,1',
            ]);
            
            if ($validated->fails()) {
                return $this->validationErrorResponse($validated->errors());
            }
            $data = $request->all();
            $response = $this->stripeService->getStripeLoginLink($data);

            return $this->successResponse($response, 'Url Link');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    
    public function onLoadConnectStripeAccount(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'account_id' => 'required|string',
            ]);

            if ($validated->fails()) {
                return $this->validationErrorResponse($validated->errors());
            }

            $response = $this->stripeService->onLoadConnectStripeAccount($request->all());

            return $this->successResponse($response, 'Onload Stripe Account Connected Successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        } 
    }
}
