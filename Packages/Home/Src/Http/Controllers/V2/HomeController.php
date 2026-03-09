<?php

namespace Packages\Home\Http\Controllers\V2;


use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Illuminate\Validation\UnauthorizedException;
use Packages\Home\Contracts\V2\BaseApiResponse;
use Algolia\AlgoliaSearch\SearchClient;
use Packages\Home\Http\Requests\V2\CookiesRequest;
use Packages\Home\Models\V2\Cookies;
use Packages\Home\Services\V2\HomeService;

class HomeController extends Controller
{
    use BaseApiResponse;

    
    protected $homeService;

    public function __construct(HomeService $homeService)

    {
        $this->homeService = $homeService;
        // $this->middleware('guest')->except('logout');
    }

    public function getHomeBanners(Request $request)
    {
        try {
            /*
        * Home Page Banners array 
        */
            $category = $request->input('category');
            $slides_main = $this->homeService->getHomeBanners($category);
           if(!empty($slides_main)){
            return $this->successResponse($slides_main,'Result Found',Response::HTTP_CREATED);
           }else{
            return $this->notFoundResponse('item not found');
           }
        } catch (\Throwable $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getHomeCategories()
    {
        try {
            $categories = $this->homeService->getHomeCategories();
            return $this->successResponse($categories, 'Categories Found', Response::HTTP_OK);

        } catch (\Throwable $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getSideBarCategories()
    {
        try {
            $categories = $this->homeService->getSideBarCategories();
            return $this->successResponse($categories, 'Categories Found', Response::HTTP_OK);

        } catch (\Throwable $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getCategories()
    {
        try {
            $data = $this->homeService->getCategories();
            
            if (empty($data)) {
                throw new ItemNotFoundException('Categories not found.');
            }

            return $this->successResponse($data, 'Categories retrieved successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function categoryHeadingSeo($slug){
        try {
            if(!empty($slug)){
                $category = $this->homeService->categoryHeadingSeo($slug);
                if($category){
                    return $this->successResponse($category, 'Categories Found', Response::HTTP_OK);
                }else{
                    throw new ItemNotFoundException('Category not found.');
                }
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function storeCookies(CookiesRequest $request){
        try {

            $cookies = $this->homeService->cookies($request->all());

            if($cookies){
                return $this->successResponse($cookies, Response::HTTP_OK);
            }else{
                throw new ItemNotFoundException('cookies not found.');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getCookies(GetCookiesRequest $request )
    { 
        try {
            $userId = $request->input('user_id');

            $getcookies = $this->homeService->getCookies($userId);

            if($getcookies){
                return $this->successResponse($getcookies, Response::HTTP_OK);
            }else{
                throw new ItemNotFoundException('cookies not found.');
                }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function gettiktokVideos(){
        try {
            $videos = $this->homeService->gettiktokVideos();
            if($videos){
                return $this->successResponse($videos, 'Videos Found', Response::HTTP_OK);
            }else{
                throw new ItemNotFoundException('Videos not found.');
            }
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
