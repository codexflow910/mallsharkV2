<?php

namespace Packages\Search\Http\Controllers\V3;


use Exception;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Nette\Schema\Elements\Base;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Packages\Search\Services\V3\SearchService;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Facades\Validator;
use Algolia\AlgoliaSearch\InsightsClient;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Product\Models\V3\Product;
use Packages\Search\Contracts\BaseApiResponse;
use Packages\Search\Http\Requests\V3\SearchRequest;
use Packages\Search\Http\Requests\V3\SearchResultRequest;
use Packages\Search\Http\Requests\V3\SearchByCategoriesRequest;
use Packages\Search\Contracts\SearchRepositoryInterface;
use Packages\Search\Http\Requests\V3\SearchImageRequest;
use Packages\Search\Services\SearchTypes\SimpleProductService;
use Packages\Search\Services\SearchTypes\VariableProductService;
use Packages\Home\Http\Requests\V3\GetCookiesRequest;


class SearchController extends Controller
{
    use BaseApiResponse;

    
    protected $searchService;

    public function __construct(SearchService $searchService)

    {
        $this->searchService = $searchService;
    }

    
    public function suggestions(SearchRequest $request)
    {
        try {
            
            $data = $request->validated();
            $result = $this->searchService->suggestion($data);
            if($result){
                return $this->successResponse($result, 'Result Found', Response::HTTP_CREATED);
            }
        } catch (Exception $e) {
            
            //check exception type and return reponse based on that using errorResponse method
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
        
    }
    public function getCategoryFilters($categoryId)
    {
        try {
            $result = $this->searchService->getCategoryFilters($categoryId);
            return $this->successResponse($result, 'Filters Found', Response::HTTP_OK);
        } catch (Exception $e) {
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function searchResults(SearchResultRequest $request){
        try
        {
            $data = $request->validated();
            $result = $this->searchService->searchResults($data);
            if($result['products']->count() > 0){
                $result['current_page'] = intval($data['page']);
                return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
           
        }catch (Exception $e) {
            
            //check exception type and return reponse based on that using errorResponse method
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
       
    }
    //version 3 api for website
    public function searchResultsV3(SearchResultRequest $request){
        try
        {
            $data = $request->validated();
            $result = $this->searchService->searchResultsV3($data);
            if($result['products']->count() > 0){
                $result['current_page'] = intval($data['page']);
                return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
           
        }catch (Exception $e) {
            
            //check exception type and return reponse based on that using errorResponse method
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
       
    }
    public function bestSellers(SearchResultRequest $request)
    {
       
        try{
            $data = $request->validated();
            $data = $request->all();
            $result = $this->searchService->bestSellers($data);
            
            if($result['products']->count() > 0){
                $result['current_page'] = intval($data['page']);
                return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
        }catch (Exception $e) {
                
            //check exception type and return reponse based on that using errorResponse method
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    // version 3 api for website
    public function bestSellersV3(SearchResultRequest $request)
    {
       
        try{
            $data = $request->validated();
            $data = $request->all();
            $result = $this->searchService->bestSellersV3($data);
            
            if($result['products']->count() > 0){
                $result['current_page'] = intval($data['page']);
                return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
        }catch (Exception $e) {
                
            //check exception type and return reponse based on that using errorResponse method
            
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getRelatedProducts(Request $request)
    {
        try{
            $productId = $request->input('id');
            $products = $this->searchService->getRelatedProducts($productId);
            if($products->count() > 0){
                return $this->successResponse($products, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getTrendingProducts(Request $request)
    {
        try {
            
            $products = $this->searchService->getTrendingProducts();
            
            if($products){
                return $this->successResponse($products, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
           
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getPopularTags()
    {
        try {
            $response = $this->searchService->getPopularTags();
            
            return $this->successResponse($response, 'Popular tags retrieved successfully.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getSearchTags()
    {
        try {
            $response = $this->searchService->getUserSearchTags();

            return $this->successResponse($response, 'You successfully got the tags.');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getSeacrchProduct(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'status' => 'nullable',
                    'title'=> 'nullable',
                ],
            );

            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }
            $data = $request->all();
            // dd($data);
            $result = $this->searchService->getSeacrchProduct($data);
            return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
        } catch (Exception $e) {

            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function storeClickEvent(Request $request)
    {
        $productId = $request->id;
        $userId = $request->user_id;
        $appId = env('ALGOLIA_APP_ID');
        $apiKey = env('ALGOLIA_SECRET'); 
        $indexName = env('PRODUCT_INDEX');
            $positions = [1]; 
            $now = now()->timestamp * 1000;           

                
                    $client = InsightsClient::create($appId, $apiKey);
                    $response = $client->sendEvent([
                      'eventType' => 'click',
                    'eventName' => 'Product Clicked',
                        'index' => $indexName,
                        'userToken' => $userId,
                        'timestamp' => $now,
                        'objectIDs' => $productId,
                    ]);
            $data = ['status' => true,'message' => $response];
            return response()->json(
                $data
            );
        
    }
    public function storeConversionEvent(Request $request)
    {
        $productId = $request->id;
        $userId = $request->user_id;
        $appId = env('ALGOLIA_APP_ID');
        $apiKey = env('ALGOLIA_SECRET'); 
        $indexName = env('PRODUCT_INDEX');
            $positions = [1]; 
            $now = now()->timestamp * 1000;           

                
                    $client = InsightsClient::create($appId, $apiKey);
                    $response = $client->sendEvent([
                      'eventType' => 'conversion',
                    'eventName' => 'Product purchased',
                        'index' => $indexName,
                        'userToken' => $userId,
                        'timestamp' => $now,
                        'objectIDs' => $productId,
                    ]);
            $data = ['status' => true,'message' => $response];
            return response()->json(
                $data
            );
        
    }
    public function deals(SearchResultRequest $request)
    {

        try {
            $data = $request->validated();
            $data = $request->all();
            $result = $this->searchService->deals($data);
            if($result['products']->count() > 0){
                $result['current_page'] = intval($data['page']);
            return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
        } catch (Exception $e) {

            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    //version 3 api for website
    public function dealsV3(SearchResultRequest $request)
    {

        try {
            $data = $request->validated();
            $data = $request->all();
            $result = $this->searchService->dealsV3($data);
            if($result['products']->count() > 0){
                $result['current_page'] = intval($data['page']);
            return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
        } catch (Exception $e) {

            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function exploreMore(SearchResultRequest $request)
    {

        try {
            $data = $request->validated();
            $data = $request->all();
            $result = $this->searchService->exploreMore($data);
            return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
         
        } catch (Exception $e) {

            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getMostPopularProduct(Request $request)
    {
        try{
            $data = $request->all();
            $result = $this->searchService->mostPopular($data);
            $hasBestSellers = collect($result['products'])->count() > 0;
            if($hasBestSellers){
                $result['current_page'] = intval($data['page']);
                return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
        }catch (Exception $e) {
                
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function searchImage(SearchImageRequest $request)
    {
        try {
            $image = $request->file('image'); 
            $result = $this->searchService->searchProductsFromImage($image);
            return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }


    public function generateTags(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required'
            ]);
            $productId = $request->input('product_id');
            $result = $this->searchService->generateTagsForProduct($productId);
                return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function generateTagsForAllProducts()
    {
        try {
            $result = $this->searchService->generateTagsForAllProducts();
            return $this->successResponse($result, 'Tags generated for all products.', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function recommendedProducts(Request $request )
    { 
        try {
            $data = $request->all();

            $getcookies = $this->searchService->recommendedProducts($data);
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
    //version 3 api for website
    public function recommendedProductsV3(Request $request )
    { 
        try {
            $data = $request->all();

            $getcookies = $this->searchService->recommendedProductsV3($data);
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
    public function getMostPapularProduct(SearchResultRequest $request)
    {
       
        try{
            $data = $request->validated();
            $data = $request->all();
            $result = $this->searchService->mostPapular($data);
            $hasBestSellers = collect($result['best_sellers'])->count() > 0;
            if($hasBestSellers){
                $result['current_page'] = intval($data['page']);
                return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }
        }catch (Exception $e) {
                
            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getLookingSimilarProducts(Request $request)
    {
        try {
            $productId = $request->input('product_id') ?? null;

            if (!trim($productId)) {
                return $this->validationErrorResponse(['product_id' => 'The product_id field is required.']);
            }

            $response = $this->searchService->getLookingSimilarProducts($productId);
            return $this->successResponse($response, 'Looking similar products retrieved successfully');
        } catch (ItemNotFoundException  $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function searchByCategories(SearchByCategoriesRequest $request)
    {
        try
        {
            $data = $request->validated();
            $result = $this->searchService->searchByCategories($data);
            if($result['products']->count() > 0){
                $result['current_page'] = intval($data['page']);
                return $this->successResponse($result, 'Item Found', Response::HTTP_CREATED);
            }else{
                return $this->notFoundResponse('item not found');
            }

        }catch (Exception $e) {

            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if ($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->errors());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
}
