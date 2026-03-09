<?php

namespace Packages\Review\Repositories\Eloquent;

use Exception;
use Packages\Review\Models\Review;
use Packages\Order\Models\StoreOrder;
use Packages\Review\Models\SuperadminReview;
use Packages\Review\Contracts\BaseApiResponse;
use Packages\Review\Contracts\ReviewRepositoryInterface;

class ReviewRepository implements ReviewRepositoryInterface
{
    use BaseApiResponse;
    protected $model;
    protected $superadminReview;

    public function __construct(Review $model, SuperadminReview $superadminReview)
    {
        $this->model = $model;
        $this->superadminReview = $superadminReview;
    }

    public function saveReview($data)
    {
        try {
            $review = $this->model->fill($data);
            $review->save();
            return $review;

        } catch (Exception $e) {
            throw new Exception("Error saving review: " . $e->getMessage());
        }
    }

    public function getOrderByIdAndUser($orderId, $userId)
    {
        return StoreOrder::where('order_id', $orderId)->where('buyer_id', $userId)->first();
    }
    
    public function getReviewByOrderAndProduct($orderId, $productId, $userId)
    {
        return Review::where('order_id', $orderId)
                    ->where('product_id', $productId)
                    ->where('user_id', $userId)
                    ->first();
    }
    public function getReviewProduct($productId, $userId)
    {
      
        return Review::where('product_id', $productId)
            ->where('user_id', $userId)
            ->first();
    }

    public function filterReviewsByProductId($productId)
    {
        $userReviews = Review::where('product_id', $productId)->get();

        $superAdminReviews = SuperadminReview::where('product_id', $productId)->get();

        // Combine and sort all reviews by creation date
        $allReviews = $userReviews->concat($superAdminReviews)
            ->sortByDesc('created_at')
            ->values()
            ->toArray();

        return $allReviews;
    }

    public function saveSuperReview($data)
    {
        try {
            $review = $this->superadminReview->fill($data);
            $review->save();
            return $review;
        } catch (Exception $e) {
            throw new Exception("Error saving super admin review: " . $e->getMessage());
        }
    }

    public function findSuperReviewById($id)
    {
        return SuperadminReview::find($id);
    }

    public function updateSuperReview($review, $data)
    {
        try {
            $review->update($data);
            return $review;
        } catch (Exception $e) {
            throw new Exception("Error updating super admin review: " . $e->getMessage());
        }
    }

    public function deleteSuperReview($review)
    {
        try {
            $review->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception("Error deleting super admin review: " . $e->getMessage());
        }
    }
}
