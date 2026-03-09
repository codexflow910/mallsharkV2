<?php

namespace App\Http\Controllers;

use App\Models\Attachments;
use Session;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Models\SuperadminReview;
use Illuminate\Database\Eloquent\Model;

class ReviewsController extends Controller {

    private $review;

    public function __construct() {
        $this->review = new Review();
    }

    public function add_review(Request $request) {
        $response = array();
        $product_id = $request->input('product_id');
        $orderId = $request->input('orderId');
        $author_id = $request->input('author_id');
        $rating_count = $request->input('rating_count');
        $comment = $request->input('comment');
        
        if ($product_id != '' && $author_id != '' && $rating_count != '' && $comment != '' && $orderId != '') {
            $this->review->product_id = $product_id;
            $this->review->order_id = $orderId;
            $this->review->rating = $rating_count;
            $this->review->comment = $comment;
            $this->review->user_id = $author_id;
            $this->review->status = '1';
            $this->review->save();
            $response['status']='success';
            $response['message']='<div class="alert alert-success alert-success-mbt search_message" role="alert" style="display: block;">
                    Review added successfully
                </div>';
           
        }
        else{
              $response['status']='error';
              $response['message']='<div class="alert alert-danger alert-success-mbt search_message" role="alert" style="display: block;">
                    Review Not added please try again!
                </div>';
        }
        return json_encode($response);
      
    }

    // public function get_reviews($p_id='') {
      
    //     if ($p_id == '') {
    //         return 'No record found';
    //     }
    //     return $all_reviews = Review::where('product_id', '=', $p_id)->orderBy('created_at','DESC')->get()->toArray();
    // }

    public function get_reviews($p_id = ''){
        if ($p_id == '') {
            return 'No record found';
        }

        // Fetch user reviews
        $userReviews = Review::where('product_id', $p_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        // Fetch super admin reviews
        $superAdminReviews = SuperadminReview::where('product_id', $p_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        // Combine and sort all reviews by creation date
        $allReviews = $userReviews->concat($superAdminReviews)
            ->sortByDesc('created_at')
            ->values();

        // Loop through and attach gallery URLs
        $allReviews = $allReviews->map(function ($review) {
            $gallery = $review->gallery ?? '';
            if (!empty($gallery)) {
                $review->gallery_urls = $this->getReviewGalleryUrls($gallery);
            } else {
                $review->gallery_urls = [];
            }
            return $review;
        });

        return $allReviews->toArray();
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

    public function update_review_status(Request $request) {
        $p_id = $request->input('product_id');
        $p_status = $request->input('status');
        if ($p_id == '') {
            return 'No record found';
        }
        if ($p_status == '') {
            return 'Cannot update status';
        }
        $update = Review::find($p_id);
        $update->status = $p_status;
        $update->save();
    }

    public function delete_review($id) {

        if (empty($id)) {
            return response()->json(['error' => 'No record found'], 404);
        }
    
        $delete = Review::where('review_id', $id)->first();
    
        if (!$delete) {
            return response()->json(['error' => 'Review not found'], 404);
        }
    
        $delete->delete();
    
        return response()->json(['success' => 'Review deleted successfully!']);
    }

}
