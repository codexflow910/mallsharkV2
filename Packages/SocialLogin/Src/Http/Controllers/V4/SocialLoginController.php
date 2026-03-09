<?php

namespace Packages\SocialLogin\Http\Controllers\V4;


use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\ItemNotFoundException;
use Packages\SocialLogin\Contracts\V4\BaseApiResponse;
use Packages\SocialLogin\Http\Requests\V4\AppleLoginRequest;
use Packages\SocialLogin\Services\V4\SocialLoginService;
use Packages\SocialLogin\Http\Requests\V4\GoogleLoginRequest;
use Packages\SocialLogin\Http\Requests\V4\FacebookLoginRequest;

class SocialLoginController extends Controller
{
    use BaseApiResponse;


    protected $socialLoginService;

    public function __construct(SocialLoginService $socialLoginService)
    {
        $this->socialLoginService = $socialLoginService;

    }

    public function facebookLogin(FacebookLoginRequest $request)
    {
        try {
            $data = $this->socialLoginService->loginFacebookUser($request->validated());

            return $this->successResponse($data, "Login successful", Response::HTTP_OK);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('Authentication failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function googleLogin(GoogleLoginRequest  $request)
    {
        try {
            $data = $this->socialLoginService->loginGoogleUser($request->validated());

            return $this->successResponse($data, "Login successful", Response::HTTP_OK);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('Authentication failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function appleLogin(AppleLoginRequest  $request)
    {
        try {
            $data = $this->socialLoginService->loginAppleUser($request->validated());

            return $this->successResponse($data, "Login successful", Response::HTTP_OK);
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('Authentication failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
