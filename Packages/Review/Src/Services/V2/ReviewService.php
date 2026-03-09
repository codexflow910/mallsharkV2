<?php

namespace Packages\Review\Services\V2;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ItemNotFoundException;
use App\Jobs\SendOrderEmailsJob;
use Packages\Core\Contracts\V2\CoreHelpers;
use Packages\Review\Contracts\V2\ReviewHelpers;
use Packages\Review\Repositories\Eloquent\V2\ReviewRepository;
use Packages\Order\Models\V2\StoreOrder;
use Packages\Auth\Models\V2\User;
use App\Http\Controllers\MailgunController;
use App\Models\Review;


class ReviewService

{
    use ReviewHelpers;
    use CoreHelpers;
    protected $reviewRepository;
    private $mailgun;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->mailgun = new MailgunController();
        $this->reviewRepository = $reviewRepository;
    }

    public function addReviewV3($data)
    {
        // If user not logged in, resolve by email
        if (!empty($data['email'])) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                throw new \Exception('Invalid review link');
            }
            $userId = $user->user_id;
        }
        if (!empty($data['order_id'])) {
        
        $orderid = $data['order_id'];
        $order = $this->reviewRepository->getOrderByIdAndUser($orderid, $userId);
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
            $imagesGallery = $this->reviewRepository->storeReviewImagesFromUploadedFiles($data['gallery'] ?? []);
            $review['gallery'] = serialize($imagesGallery);

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
            $imagesGallery = $this->reviewRepository->storeReviewImagesFromUploadedFiles($data['gallery'] ?? []);
            $review['gallery'] = serialize($imagesGallery);

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

            if (empty($filteredReviews)) {
                throw new ItemNotFoundException('No reviews found');
            }

            $reviews = $this->sortReviews($filteredReviews, $sortBy);
            $sortedReviews = [];
            $imagesGallery = [];

            $starCounts = [
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
            ];

            $totalRating = 0;

            if (!empty($reviews)) {
                foreach ($reviews as $review) {
                    $review = (array) $review;

                    // Set user name
                    $customer = $review['user'] ?? null;
                    $review['user_name'] = $customer['name'] ?? $review['name'] ?? 'Guest';

                    // Unset user object to prevent exposure
                    unset($review['user']);

                    $gallery = $review['gallery'] ?? [];
                    if (isset($gallery) && !empty($gallery)) {
                        $review['gallery_urls'] = $this->reviewRepository->getReviewGalleryUrls($gallery);
                        $imagesGallery = array_merge($imagesGallery, $review['gallery_urls']);
                    }

                    // Rating stats
                    $rating = (int) $review['rating'];
                    if (isset($starCounts[$rating])) {
                        $starCounts[$rating]++;
                    }
                    $totalRating += $rating;

                    $sortedReviews[] = $review;
                }
            }

            // Final stats
            $totalReviews = count($sortedReviews);
            $average = $totalReviews ? round($totalRating / $totalReviews, 2) : 0;

            // Star breakdown
            $reviewByStars = [
                'star1_count' => $starCounts[1],
                'star2_count' => $starCounts[2],
                'star3_count' => $starCounts[3],
                'star4_count' => $starCounts[4],
                'star5_count' => $starCounts[5],
            ];

            return [
                'reviews' => $sortedReviews,
                'images' => $imagesGallery,
                'reviewByStars' => $reviewByStars,
                'reviewAverage' => $average,
            ];

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

    public function reviewRequest($orderid)
{
    try {
        // 1. Validate order
        $order = StoreOrder::with(['orderItemsList.product', 'user'])
            ->where('order_id', $orderid)
            ->firstOrFail();

        $buyer = $order->user;

        if (!$buyer || empty($buyer->email)) {
            throw new \Exception('Buyer not found or email is missing');
        }

        // 2. Prepare products array for email
        $products = [];

        foreach ($order->orderItemsList as $orderItem) {
            $product = $orderItem->product;

            if (!$product) {
                continue;
            }
            $FRONT_END_URL = env('FRONT_END_URL');
            $products[] = [
                'product'   => $product,
                'image'     => $orderItem->item_image,
                'reviewUrl' => url("{$FRONT_END_URL}/product/{$product->slug}?email={$buyer->email}"),
            ];
        }
        if (empty($products)) {
            throw new \Exception('No products found for this order');
        }

        // 3. Render email HTML
        $htmlContent = view('mail.review-request', [
            'buyer'    => $buyer,
            'products' => $products,
        ])->render();

        // 4. Mailgun params
        $params = [
            'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
            'to'      => $buyer->email,
            'subject' => 'Write a review for your recent purchase',
            'html'    => "$htmlContent",
        ];
        // 5.send email job
        $this->mailgun->SendMailgunMail($params);
        return [
            'status'   => true,
            'order_id' => $orderid,
            'products' => count($products),
        ];

    } catch (\Exception $e) {
        throw $e;
    }
}
public function getReviews()
{
    try {
        $userId = $this->getUser();
        if (!$userId) {
            throw new \Exception('User ID is required');
        }

        $reviews = Review::with(['product' => function ($query) {
            $query->select('product_id', 'title', 'slug', 'feature_image');
        }])
        ->where('user_id', $userId)
        ->get();

        if ($reviews->isEmpty()) {
            throw new \Exception('No reviews found');
        }

        $data = $reviews->map(function ($review) {

            /* ---------- Product Feature Image ---------- */
            $featureImage = null;
            if (!empty($review->product?->feature_image)) {
                $featureImage = $this->getAttachmentUrlById(
                    $review->product->feature_image
                );
            }

            /* ---------- Gallery Images (ID + URL) ---------- */
            $gallery = [];

            if (!empty($review->gallery)) {
                $galleryIds = is_array($review->gallery)
                    ? $review->gallery
                    : @unserialize($review->gallery);

                if (is_array($galleryIds)) {
                    foreach ($galleryIds as $imageId) {
                        $gallery[] = [
                            'id'  => $imageId,
                            'url' => $this->getAttachmentUrlById($imageId, 'review'),
                        ];
                    }
                }
            }

            return [
                'review_id'  => $review->review_id,
                'product_id' => $review->product_id,
                'order_id'   => $review->order_id,
                'rating'     => $review->rating,
                'comment'    => $review->comment,
                'status'     => $review->status,
                'is_review_updated' => $review->is_review_updated,
                'created_at' => $review->created_at,

                'gallery' => $gallery,

                'product' => [
                    'title'         => $review->product->title ?? null,
                    'slug'          => $review->product->slug ?? null,
                    'feature_image' => $featureImage,
                ],
            ];
        });

        return $data;
    } catch (\Exception $e) {
        throw $e;
    }
}

public function updateReview($data, $reviewId)
{
    try {
       $userId = $this->getUser();
        if (!$userId) {
            throw new \Exception('User ID is required');
        }

        // Find review
        $review = Review::where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->first();

        if (!$review) {
            throw new \Exception('Review not found or does not belong to the user');
        }

        //  One-time update check
        if ($review->is_review_updated) {
            throw new \Exception('You can update the review only once');
        }

        /* ---------------- Review Fields ---------------- */
        if (isset($data['rating_count'])) {
            $review->rating = $data['rating_count'];
        }

        if (isset($data['comment'])) {
            $review->comment = $data['comment'];
        }

        /* ---------------- Existing Gallery ---------------- */
        $existingGallery = [];

        if (!empty($review->gallery)) {
            $existingGallery = is_array($review->gallery)
                ? $review->gallery
                : unserialize($review->gallery);
        }

        /* ---------------- Delete Gallery ---------------- */
        if (!empty($data['deleted_gallery']) && is_array($data['deleted_gallery'])) {
            foreach ($data['deleted_gallery'] as $deleteId) {

                // remove from array
                $existingGallery = array_values(
                    array_diff($existingGallery, [$deleteId])
                );

                // OPTIONAL: delete attachment record / file
                // $this->reviewRepository->deleteReviewImageById($deleteId);
            }
        }

        /* ---------------- New Gallery Upload ---------------- */
        if (!empty($data['new_gallery_image']) && is_array($data['new_gallery_image'])) {

            $newImageIds = $this->reviewRepository
                ->storeReviewImagesFromUploadedFiles($data['new_gallery_image']);

            // merge new + existing
            $existingGallery = array_merge($existingGallery, $newImageIds);
        }

        /* ---------------- Save Gallery ---------------- */
        $review->gallery = serialize(array_unique($existingGallery));
        $review->is_review_updated = true;

        $review->save();

        return $review->toArray();
        

    } catch (\Exception $e) {
        throw $e;
    }
}
    public function getTopReviews()
    {
        $data = $this->reviewRepository->getTopReviews();

        $reviews = $data['reviews']->map(function ($review) {
            return [
                'name' => $review->user->name ?? 'Guest',
                'rating' => $review->rating,
                'comment' => $review->comment,
                'date' => $review->created_at,
            ];
        });

        $superadminReviews = $data['superadmin_reviews']->map(function ($superadminreview) {
            return [
                'name' => $superadminreview->name ?? 'Guest',
                'rating' => $superadminreview->rating,
                'comment' => $superadminreview->comment,
                'date' => $superadminreview->created_at,
            ];
        });

        $merged = $reviews->merge($superadminReviews)
            ->sortByDesc('date')
            ->values();

        return $merged;
    }

public function deleteReview($reviewId)
    {
        try {
            $userId = $this->getUser();
            if (!$userId) {
                throw new \Exception('User ID is required');
            }

            // Find review
            $review = Review::where('review_id', $reviewId)
                ->where('user_id', $userId)
                ->first();

            if (!$review) {
                throw new \Exception('Review not found or does not belong to the user');
            }

            $review->delete();

            return [
                'message' => 'Review deleted successfully',
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}