<?php

namespace Packages\Review\Repositories\Eloquent\V3;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Packages\Review\Models\V3\Review;
use Packages\Order\Models\V3\StoreOrder;
use Packages\Product\Models\V3\Attachments;
use Packages\Review\Models\V3\SuperadminReview;
use Packages\Review\Contracts\V3\BaseApiResponse;
use Packages\Review\Contracts\V3\ReviewRepositoryInterface;

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
        $userReviews = Review::with('user')->where('product_id', $productId)->get();

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

    public function storeReviewImagesFromUploadedFiles(array $files): array
    {
        $imagesGallery = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $fileNameOnly = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileExtension = $file->getClientOriginalExtension();

            // Save attachment
            $attachment = new Attachments();
            $attachment->fileName = $fileNameOnly;
            $attachment->fileExtension = $fileExtension;
            $attachment->save();

            $attachmentId = $attachment->attachment_id;

            if (!$attachmentId) {
                continue;
            }

            $finalFileName = $fileNameOnly . '_' . $attachmentId . '.' . $fileExtension;

            // Save original image
            $destinationPath = public_path('uploads/review');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }

            $file->move($destinationPath, $finalFileName);

            // Optional thumbnail
            $thumbnailPath = public_path('uploads/review/thumbnail');
            if (!File::exists($thumbnailPath)) {
                File::makeDirectory($thumbnailPath, 0775, true);
            }

            $img = Image::make($destinationPath . '/' . $finalFileName);
            $img->fit(200, 200, function ($constraint) {
                $constraint->upsize();
            })->save($thumbnailPath . '/' . $finalFileName);

            $imagesGallery[] = $attachmentId;
        }

        return $imagesGallery;
    }

    function getReviewGalleryUrls($gallerySerialized)
    {
        $attachmentIds = unserialize($gallerySerialized);

        $attachments = Attachments::whereIn('attachment_id', $attachmentIds)->get();

        $urls = [];

        foreach ($attachments as $attachment) {
            $fileName = $attachment->fileName . '_' . $attachment->attachment_id . '.' . $attachment->fileExtension;
            $urls[] = asset('uploads/review/' . $fileName);
        }

        return $urls;
    }

}
