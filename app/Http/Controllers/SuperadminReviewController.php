<?php

namespace App\Http\Controllers;

use App\Models\Attachments;
use Illuminate\Http\Request;
use App\Models\SuperadminReview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SuperadminReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'name' => 'required|string|max:100',
            'review_datetime' => 'required',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
            'name' => 'required|string|max:100',
            'gallery' => 'nullable|array|max:5',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Check for validation failures
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagesGallery = $this->storeReviewImagesFromUploadedFiles($request->file('gallery') ?? []);
        $gallery = serialize($imagesGallery);

        // Create a new review instance
        $review = new SuperadminReview();
        $review->product_id = $request->input('product_id');
        $review->user_id = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']:'';
        $review->rating = $request->input('rating');
        $review->comment = $request->input('comment');
        $review->gallery = $gallery;
        $review->created_at = $request->input('review_datetime');
        $review->name = $request->input('name'); // Assuming you want to save the user's name
        $review->status = 1; // Assuming '1' means active or published
        $review->save();

        // Return a response
        return response()->json(['message' => 'Review submitted successfully!' , $review], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'review_datetime' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'gallery' => 'nullable|array|max:5',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'existing_gallery' => 'nullable|array',
            'existing_gallery.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review = SuperadminReview::findOrFail($id);

        // Deserialize existing gallery from DB (if exists)
        $existingGalleryFromDB = $review->gallery ? unserialize($review->gallery) : [];

        // Get remaining existing images from request (after user removed some)
        $existingGalleryFromRequest = $request->input('existing_gallery', []);

        // Keep only images that are both in DB and still present in the form
        $preservedGallery = array_values(array_intersect($existingGalleryFromDB, $existingGalleryFromRequest));

        // Handle newly uploaded files
        $newUploadedGallery = $this->storeReviewImagesFromUploadedFiles($request->file('gallery') ?? []);

        // Merge preserved old + new uploaded image attachment IDs
        $finalGallery = array_merge($preservedGallery, $newUploadedGallery);

        // Save updated review
        $review->name = $request->name;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->gallery = serialize($finalGallery);
        $review->created_at = $request->review_datetime;
        $review->save();

        return response()->json(['success' => 'Review updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review = SuperadminReview::findOrFail($id);

        if (!$review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['success' => 'Review deleted successfully!']);
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
}
