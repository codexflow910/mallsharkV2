<?php

namespace Packages\Store\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Store\Contracts\V3\BaseApiResponse;
use Packages\Store\Services\V3\PayPalOnboardingService;

class PayPalOnboardingController extends Controller
{
    use BaseApiResponse;

    protected PayPalOnboardingService $onboardingService;

    public function __construct(PayPalOnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Initiate PayPal seller onboarding
     * Creates a partner referral and returns the onboarding URL
     *
     * POST /api/v3/store/paypal/onboard
     */
    public function initiateOnboarding(Request $request)
    {
        try {
            $result = $this->onboardingService->createPartnerReferral();

            return $this->successResponse($result, 'PayPal onboarding link generated', Response::HTTP_CREATED);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            Log::error('PayPal Onboarding Initiate Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Handle return callback from PayPal onboarding
     * This is called when seller completes or exits PayPal onboarding
     *
     * GET /api/v3/store/paypal/return
     */
    public function onboardingReturn(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'merchantIdInPayPal' => 'nullable|string',
                'merchantId' => 'nullable|string',
                'accountStatus' => 'nullable|string',
                'permissionsGranted' => 'nullable|string',
                'isEmailConfirmed' => 'nullable|string',
                'paypalEmail' => 'nullable|email',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

             // Use authenticated user to get store
            $result = $this->onboardingService->handleSellerReturnWithAuth($request->all());

            return $this->successResponse($result, 'PayPal onboarding completed successfully', Response::HTTP_OK);
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            Log::error('PayPal Onboarding Return Error', [
                'error' => $e->getMessage(),
                'params' => $request->all(),
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }
    public function checkWalletStatus(Request $request)
    {
        try {
            $result = $this->onboardingService->checkWalletStatus();

            return $this->successResponse($result, 'Wallet status retrieved');
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            Log::error('Wallet Status Check Error', [
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }
    /**
     * Check seller's PayPal connection status
     *
     * GET /api/v3/store/paypal/status
     */
    public function checkStatus(Request $request)
    {
        try {
            $result = $this->onboardingService->getSellerStatus();

            return $this->successResponse($result, 'PayPal status retrieved');
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            Log::error('PayPal Status Check Error', [
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Refresh PayPal connection status from PayPal API
     * Force-fetches latest status from PayPal
     *
     * POST /api/v3/store/paypal/refresh-status
     */
    public function refreshStatus(Request $request)
    {
        try {
            $userId = auth()->user()->user_id;
            $store = \Packages\Store\Models\V3\Store::where('user_id', $userId)->first();

            if (!$store) {
                throw new ItemNotFoundException('Store not found');
            }

            $result = $this->onboardingService->getMerchantIntegrationStatus($store->store_id);

            return $this->successResponse($result, 'PayPal status refreshed');
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            Log::error('PayPal Status Refresh Error', [
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Disconnect PayPal account from store
     *
     * POST /api/v3/store/paypal/disconnect
     */
    public function disconnect(Request $request)
    {
        try {
            $result = $this->onboardingService->disconnectPayPal();

            return $this->successResponse($result, 'PayPal account disconnected');
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            Log::error('PayPal Disconnect Error', [
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Regenerate onboarding link for incomplete onboarding
     *
     * POST /api/v3/store/paypal/regenerate-link
     */
    public function regenerateLink(Request $request)
    {
        try {
            $result = $this->onboardingService->regenerateOnboardingLink();

            return $this->successResponse($result, 'New onboarding link generated');
        } catch (UnauthorizedException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            Log::error('PayPal Regenerate Link Error', [
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }
}
