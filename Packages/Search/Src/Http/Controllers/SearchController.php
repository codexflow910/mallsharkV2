<?php

namespace Packages\Search\Http\Controllers;


use Exception;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Nette\Schema\Elements\Base;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Packages\Search\Services\SearchService;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Facades\Validator;
use Algolia\AlgoliaSearch\InsightsClient;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Packages\Search\Contracts\BaseApiResponse;
use Packages\Search\Http\Requests\SearchRequest;
use Packages\Search\Http\Requests\SearchResultRequest;
use Packages\Search\Contracts\SearchRepositoryInterface;
use Packages\Search\Services\SearchTypes\SimpleProductService;
use Packages\Search\Services\SearchTypes\VariableProductService;

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
    public function searchResults(SearchResultRequest $request){
        try
        {
            $data = $request->validated();
            // dd($data);
            $result = $this->searchService->searchResults($data);
            if($result['products']->count() > 0){
                $result['page'] = $data['page'];
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
     
            if($result->count() > 0){
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
            $productId = $request->id;
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
}
