<?php

namespace Packages\SocialLogin\Http\Controllers\V2;


use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\ItemNotFoundException;
use Packages\SocialLogin\Contracts\V2\BaseApiResponse;
use Packages\SocialLogin\Http\Requests\V2\AppleLoginRequest;
use Packages\SocialLogin\Services\V2\SocialLoginService;
use Packages\SocialLogin\Http\Requests\V2\GoogleLoginRequest;
use Packages\SocialLogin\Http\Requests\V2\FacebookLoginRequest;

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
