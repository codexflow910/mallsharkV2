<?php

namespace Packages\Review\Http\Controllers\V2;


use Exception;
use Packages\Auth\Models\V2\User;
use Packages\Auth\Models\V2\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Dotenv\Exception\ValidationException;
use Packages\Review\Services\V2\ReviewService;
use Illuminate\Support\ItemNotFoundException;
use Packages\Review\Contracts\V2\BaseApiResponse;
use Packages\Review\Http\Requests\V2\StoreRequest;
use Packages\Review\Http\Requests\V3\ReviewRequest;
use Packages\Review\Http\Requests\V2\SuperAdminStoreRequest;
use Packages\Review\Http\Requests\V3\UpdateRequest;

class ReviewController extends Controller
{
    use BaseApiResponse;


    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;

    }

    public function addReviewV3(ReviewRequest $request)
    {

        try {

            $data = $request->validated();
            $response = $this->reviewService->addReviewV3($data);

            return  $this->successResponse($response, 'Review added successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {

            if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function addReview(StoreRequest $request)
    {

        try {

            $data = $request->validated();
            $response = $this->reviewService->addReview($data);

            return  $this->successResponse($response, 'Review added successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {

            if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            if($e instanceof ValidationException) {
                return $this->validationErrorResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getSortedReviews(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $sortBy = $request->input('sort_by');
            $response = $this->reviewService->getSortedReviews($productId, $sortBy);
            return  $this->successResponse($response, 'Reviews retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {

            if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function addSuperReview(SuperAdminStoreRequest $request)
    {
        try {    
            $data = $request->validated();
            $response = $this->reviewService->addSuperReview($data);
            return  $this->successResponse($response, 'Review added successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {

            if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function updateSuperReview(SuperAdminStoreRequest $request, $id)
    {
        try {   
            $data = $request->validated();
            $response = $this->reviewService->updateSuperReview($id, $data);
            return  $this->successResponse($response, 'Review updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {

            if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function deleteSuperReview($id)
    {
        try {
            $response = $this->reviewService->deleteSuperReview($id);
            return  $this->successResponse($response, 'Review deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {

            if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function reviewRequest($orderid)
    {
        try{
             $response = $this->reviewService->reviewRequest($orderid);
            return  $this->successResponse($response, 'Review request succefully', Response::HTTP_OK);
        } catch (\Exception $e)
        {
             if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function getReviews()
    {
        
        try{
            $response = $this->reviewService->getReviews();
            return  $this->successResponse($response, 'review fetched successfully', Response::HTTP_OK);
        } catch (\Exception $e)
        {
             if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

     public function updateReview(UpdateRequest $request, $reviewId)
    {
        try{
            $data = $request->validated();
            $response = $this->reviewService->updateReview($data, $reviewId);
            return  $this->successResponse($response, 'Review request succefully', Response::HTTP_OK);
        } catch (\Exception $e)
        {
             if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

    public function deleteReview($reviewId)
    {
        try{
            $response = $this->reviewService->deleteReview($reviewId);
            return  $this->successResponse($response, 'Review deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e)
        {
             if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }

     public function getTopReviews()
    {
       try{
            $response = $this->reviewService->getTopReviews();
            return  $this->successResponse($response, 'Top Reviews retrieved successfully', Response::HTTP_OK);
       } catch (\Exception $e) {

            if($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
}
