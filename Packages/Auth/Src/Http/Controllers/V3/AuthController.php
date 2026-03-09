<?php

namespace Packages\Auth\Http\Controllers\V3;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Packages\Auth\Services\V3\AuthService;
use Illuminate\Support\Facades\Validator;
use Packages\Auth\Contracts\V3\BaseApiResponse;
use Illuminate\Support\ItemNotFoundException;
use Packages\Auth\Http\Requests\V3\LoginRequest;
use Illuminate\Validation\ValidationException;
use Packages\Auth\Http\Requests\V3\LogoutRequest;
use Illuminate\Validation\UnauthorizedException;
use Packages\Auth\Http\Requests\V3\RegisterRequest;
use Packages\Auth\Http\Requests\V3\VerifyOtpRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Packages\Auth\Config\AuthConstants;
use Packages\Auth\Contracts\V3\AuthHelpers;
use Packages\Auth\Http\Requests\V3\UpdateProfileRequest;
use Packages\Auth\Http\Requests\V3\UpdateAddressRequest;
use Packages\Auth\Http\Requests\V3\ChangePasswordRequest;

class AuthController extends Controller
{
    use BaseApiResponse;
    use AuthHelpers;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /** User Login */
    public function login(LoginRequest $request)
    {
        try {
            $userData = $this->authService->loginUser($request->validated());
            if (isset($userData['user']) && $userData['user']->status != 1) {
                return $this->notVerifiedResponse($userData, 'Verify Your account first');
            }
            return $this->successResponse($userData, 'User Logged In Successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**  User Registration */
    public function register(RegisterRequest $request)
    {
        try {
            $userData = $this->authService->registerUser($request->validated());

            return $this->successResponse($userData, 'User Registered Successfully, Check your Email to verify.');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function logout(LogoutRequest $request)
    {
        try {
            $userData = $this->authService->logoutUser($request->validated());

            if($userData) {
                return $this->successResponse(null, 'User Logged Out Successfully');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $this->authService->changePassword($request->validated());

            return $this->successResponse($user, 'User password updated successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /** Verify Account */
    public function verifyEmail(VerifyOtpRequest $request)
    {
        try {
    
            $user = $this->authService->verifyEmail($request->validated());
    
            return $this->successResponse($user, 'User verified successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /** Send forgot password otp also resend otp */
    public function forgotPassword(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                ]
            );
    
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $email = $request->input('email');

            $user = $this->authService->forgotPassword($email);
    
            return $this->successResponse($user, 'Check your email to reset password.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }      
    }

    /** Verify Password Otp */
    public function matchPasswordOtp(VerifyOtpRequest $request)
    {
        try {
    
            $user = $this->authService->matchPasswordOtp($request->validated());
    
            return $this->successResponse($user, 'OTP verified successfully. Create a new password.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**Check if email already exists */
    public function checkEmailExist(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                ]
            );
    
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $email = $request->input('email');
            $user = $this->authService->checkEmailExist($email);
    
            return $this->successResponse($user, 'Email already exists.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**Create New Password. */
    public function createNewPassword(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'password' => 'required|min:6',
                ]
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $user = $this->authService->createNewPassword($request->all());

            return $this->successResponse($user, 'User new password created successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }    

    public function sellerLogin(LoginRequest $request)
    {
        try {
            $user = $this->authService->sellerLogin($request->validated());

            if(!is_null($user)){
                return $this->successResponse($user, 'Seller Logged In Successfully');
            }else{
                return $this->notFoundResponse('User not found');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function requestDeletion(Request $request)
    {
        try {
              $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required',
             
            ]
        );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()->first()
            ]);
        }
      
         $data = $request->input();
     
        if(!isset($data['reason'])){
            $data['reason'] = 'User not provided any reason for this request.';
        }
         
         $user = $this->authService->requestDeletion($data);

            return $this->successResponse($user, 'Your account has been deleted');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
             if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            if ($e instanceof UnauthorizedException) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            if ($e instanceof AuthorizationException) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function userProfile(Request $request)
    {
        try {
            $id = $request->input('id');
            $userId = auth()->user()->user_id;
            $userType = $id && $id != $userId ? 'other' : 'current';
            $userId = $id ?: $userId;

            $userProfileData = $this->authService->getUserProfileData($userId, $userType);

            return $this->successResponse($userProfileData, 'User Profile Data Fetched Successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateShippingAddress(UpdateAddressRequest $request)
    {
        try {
            $address = $this->authService->updateShippingAddress($request->validated());

            return $this->successResponse($address, 'Shipping address updated successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    } 

    public function updateUserProfile(UpdateProfileRequest $request)
    {
        try {

            $image = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');              
            }
            $postedData = $request->input();
            $user = $this->authService->updateUserProfile($postedData, $image);

            return $this->successResponse($user, 'User Profile Updated Successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
             if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function analytics()
    {
        try {
            $user = auth()->user();
            $analytics = $this->authService->analytics($user);

            return $this->successResponse($analytics, 'User Analytics Fetched Successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function assignUserToStore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'  => 'required|string|max:255',
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $validated = $validator->validated();

            $response = $this->authService->assignUserToStore($validated['name'], $validated['email']);

            return $this->createdResponse($response, 'User assigned to store successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('Failed to assign store to user: ' . $e->getMessage());
        }
    }

    public function assignPermissionsToUser(Request $request)
    {
        try {
            
            $validation = Validator::make($request->all(), [
                    'user_id' => 'required|exists:users,user_id',
                    'permissions' => 'nullable|array',
                    'permissions.*' => 'exists:permissions,permission_id',
                ]
            );

            if ($validation->fails()) {
                return $this->validationErrorResponse($validation->errors());
            }

            $data = $request->all();
            $userId = $data['user_id'];
            $permissions = $data['permissions'];
            $response = $this->authService->assignPermissionsToUser($userId, $permissions);
            return $this->successResponse($response, 'User permissions assigned successfully');
        } catch (Exception $e) {
            throw new Exception("Failed: " . $e->getMessage());
        }
    }

    public function getSellerSidePermissions()
    {
        try {
            $permissions = $this->authService->getSellerSidePermissions();

            return $this->successResponse($permissions, 'Seller side permissions fetched successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getStoreAccessors()
    {
        try {
            $user = auth()->user();
            $store = $user->store;
            if (!$store) {
                return $this->notFoundResponse('No store found for the user.');
            }
            $storeId = $store->store_id;

            $accessors = $this->authService->getStoreAccessors($storeId);

            return $this->successResponse($accessors, 'Store accessors fetched successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function removeStoreAccessor($userId)
    {
        try {
            $userId = (int) $userId;
            $user = auth()->user();
            $store = $user->store;
            if (!$store) {
                return $this->notFoundResponse('No store found for the user.');
            }
            $storeId = $store->store_id;

            $response = $this->authService->removeStoreAccessor($storeId, $userId);
            if ($response) {
                return $this->successResponse(null, 'Store accessor removed successfully');
            } else {
                return $this->errorResponse('Failed to remove store accessor');
            }
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getModeratorPermissions()
    {
        try {
            $userId = auth()->user()->user_id;
            $response = AuthHelpers::getModelPermissions($userId);

            if ($response->isEmpty()) {
                return $this->notFoundResponse('Permissions not found for user: ' . $userId);
            }

            return $this->successResponse($response, 'Permissions retrieved successfully');
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
        }
    }
    public function customerWalletAddress(Request $request)
    {
        try {
            $response = $this->authService->customerWalletAddress($request->all());
            if($response){
                return $this->successResponse($response, 'Wallet address saved successfully');
            } else {
                return $this->errorResponse('Failed to save wallet address');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getCustomerWalletAddress()
    {
        try {
            $response = $this->authService->getCustomerWalletAddress();
            if($response){
                return $this->successResponse($response, 'Wallet address fetched successfully');
            } else {
                return $this->errorResponse('Failed to fetch wallet address');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function deleteAccount()
    {
        try {
            $this->authService->deleteAccount();

            return $this->successResponse(null, 'Your account has been deleted successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function resendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);
            $email = $request->input('email');
            $this->authService->resendOtp($email);

            return $this->successResponse(null, 'OTP has been resent to your email.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
