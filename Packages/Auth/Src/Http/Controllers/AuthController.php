<?php

namespace Packages\Auth\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Packages\Auth\Services\AuthService;
use Illuminate\Support\Facades\Validator;
use Packages\Auth\Contracts\BaseApiResponse;
use Illuminate\Support\ItemNotFoundException;
use Packages\Auth\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;
use Packages\Auth\Http\Requests\LogoutRequest;
use Illuminate\Validation\UnauthorizedException;
use Packages\Auth\Http\Requests\RegisterRequest;
use Packages\Auth\Http\Requests\VerifyOtpRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Packages\Auth\Http\Requests\UpdateProfileRequest;
use Packages\Auth\Http\Requests\UpdateAddressRequest;
use Packages\Auth\Http\Requests\ChangePasswordRequest;

class AuthController extends Controller
{
    use BaseApiResponse;

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
}
