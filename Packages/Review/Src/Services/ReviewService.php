<?php

namespace Packages\Review\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ItemNotFoundException;
use Packages\Core\Contracts\CoreHelpers;
use Packages\Review\Contracts\ReviewHelpers;
use Packages\Review\Repositories\Eloquent\ReviewRepository;

class ReviewService

{
    use ReviewHelpers;
    use CoreHelpers;
    protected $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function addReview($data)
    {
        $userId = $this->getUser();
        if (!empty($data['order_id'])) {
        
        $order = $this->reviewRepository->getOrderByIdAndUser($data['order_id'], $userId);
        
        if (!$order) {
            throw new \Exception('Invalid order or order does not belong to the user.');
        }
            $existingReview = $this->reviewRepository->getReviewByOrderAndProduct($data['order_id'],$data['product_id'], $userId);

            if ($existingReview) {
                throw new \Exception('You have already reviewed this order product');
            }
        }

        $existingReview = $this->reviewRepository->getReviewProduct( $data['product_id'], $userId);
      
        if ($existingReview) {
            throw new \Exception('You have already reviewed this product');
        }

        $review = [
            'product_id' => $data['product_id'],
         
            'rating' => $data['rating_count'],
            'comment' => $data['comment'],
            'user_id' => $userId,
            'status' => 1,
        ];
        if (!empty($data['order_id'])) {
            $review['order_id'] = $data['order_id'];
        }
            DB::beginTransaction();

        try {
            $savedReview = $this->reviewRepository->saveReview($review);

            $product = $this->getProductById($data['product_id']);
            $productTitle = $product->title;
            $sellerId = $product->user_id;
            $subject = "Your product <b>{$productTitle}</b> received a {$data['rating_count']} star review.";
            $notification = $this->pushNotification($sellerId, $subject, $appFlag = 'seller');
            
            DB::commit();

            return $savedReview;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;   
        }
    }

    public function getSortedReviews($productId, $sortBy)
    {
        try {
            $filteredReviews = $this->reviewRepository->filterReviewsByProductId($productId);
            if(empty($filteredReviews)){
                throw new ItemNotFoundException('No reviews found');
            }
            $reviews = $this->sortReviews($filteredReviews, $sortBy);

            if(isset($reviews) && !empty($reviews)){
                foreach($reviews as $review){
                    $customer = $this->getUserById($review['user_id']);
                    $review['customer_name'] = $customer->name;
                    $sortedReviews[] = $review;
                }
            }

            return $sortedReviews;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function addSuperReview($data)
    {
        $userId = $this->getUser();

        $review = [
            'product_id' => $data['product_id'],
            'name' => $data['name'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'created_at' => $data['review_datetime'],
            'updated_at' => $data['review_datetime'],
            'user_id' => $userId,
            'status' => 1,
        ];

            DB::beginTransaction();
        try {
            $savedReview = $this->reviewRepository->saveSuperReview($review);

            $product = $this->getProductById($data['product_id']);
            $productTitle = $product->title;
            $sellerId = $product->user_id;
            $subject = "Your product <b>{$productTitle}</b> received a {$data['rating']} star review.";
            $notification = $this->pushNotification($sellerId, $subject, $appFlag = 'seller');

            DB::commit();
            return $savedReview;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSuperReview($id, $data)
    {
        $userId = $this->getUser();

        $review = $this->reviewRepository->findSuperReviewById($id);

        if (!$review) {
            throw new ItemNotFoundException("Review not found.");
        }

        $updatedReviewData = [
            'product_id' => $data['product_id'],
            'name'       => $data['name'],
            'rating'     => $data['rating'],
            'comment'    => $data['comment'] ?? null,
            'created_at' => $data['review_datetime'],
            'updated_at' => $data['review_datetime'],
            'user_id'    => $userId,
        ];

        DB::beginTransaction();
        try {
            $updatedReview = $this->reviewRepository->updateSuperReview($review, $updatedReviewData);
            DB::commit();
            return $updatedReview;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteSuperReview($id)
    {
        $review = $this->reviewRepository->findSuperReviewById($id);

        if (!$review) {
            throw new ItemNotFoundException("Review not found.");
        }

        $deletedReview = $this->reviewRepository->deleteSuperReview($review);

        return $deletedReview;
    }

    public function getUser()
    {
        $user = auth()->id();
        if (!$user) {
            throw new ItemNotFoundException('User not found');
        }
        return $user;
    }
    

}
