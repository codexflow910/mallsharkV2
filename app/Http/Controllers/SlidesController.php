<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use App\Models\SlideSet;
use Illuminate\Http\Request;
use App\Repositories\Slide\Slideaccess;
use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SlidesController extends Controller {

    private $slide_access_obj;
    private $attachment_obj;

    public function __construct() {
        $this->slide_access_obj = new Slideaccess();
        $this->attachment_obj = new AttachmentController();
    }

    public function getAllSlides() {
        return $slides = DB::table('slides')
                ->select('slides.*')
                ->get();
    }

    public function getAllSlidesByCategory($category) {
        return $slides = DB::table('slides')
                ->where('category', $category)
                ->select('slides.*')
                ->get();
    }

    public function getDashboard() {
        $all_slides = $this->getAllSlides();
        //print_rr($all_slides);
        //die();
        return view("admin/slides", array('all_slides' => $all_slides));
    }

    public function addSlide(Request $req) {
        
        $url = $req->input('slide_url');
         $category=$req->input('slide_category');
        if ($req->hasfile('slide_image')) {
            $imageObj = array($req->file('slide_image'));
        }
        $bg_color = $req->input('bg_color');
        $res = $this->slide_access_obj->insertSlide($url, $imageObj, $category, 1,$bg_color);

        if ($res) {
            $message = 'slide is added successfuly';
        }
        return redirect('admin/slides')->with('message_success', $message);
    }

    public function updateSlide(Request $req) {
        
        $url = $req->input('slide_url');
        $slide_id = $req->input('slide_id');
        $category=$req->input('slide_category');
        $bg_color = $req->input('bg_color');
        $imageObj = '';
        if ($req->hasfile('slide_image')) {
            $imageObj = array($req->file('slide_image'));
        }
        $res = $this->slide_access_obj->updateSlide($slide_id, $imageObj, $url, $category, 1,$bg_color);

        if ($res) {
            $message = 'slide is updated successfuly';
        }
        return redirect('admin/slides')->with('message_success', $message);
    }

    public function deleteSlide($slide_id) {

        $res = $this->slide_access_obj->deleteSlide($slide_id);

        if ($res) {
            $message = 'slide is Deleted successfuly';
            return redirect('admin/slides')->with('message_success', $message);
        } else {
            $message = 'Something went wrong';
            return redirect('admin/slides')->with('message_error', $message);
        }
    }

    public function bannerManagement()
    {
        $allBannerSets = SlideSet::with('slides')
            ->orderBy('created_at', 'desc')
            ->get();

            foreach ($allBannerSets as $set) {
                foreach ($set->slides as $slide) {
                    $slide->image_url = getAttachmentUrlById($slide->attachment_id, 'media');
                }
            }

        $selectedDates = $allBannerSets->pluck('start_date')->unique()->values()->all();

        return view("admin.banner-management-sets", compact('allBannerSets', 'selectedDates'));
    }

    public function bannerListing()
    {
        $allBannerSets = SlideSet::with('slides')
            ->orderBy('created_at', 'desc')
            ->get();

            foreach ($allBannerSets as $set) {
                foreach ($set->slides as $slide) {
                    $slide->image_url = getAttachmentUrlById($slide->attachment_id, 'media');
                }
            }

        return view("admin.banner-management", compact('allBannerSets'));
    }

    public function addSlideToSet(Request $req)
    {

        $url = $req->input('slide_url');
        $category = $req->input('slide_category');
        $slide_type = $req->input('slide_type');
        if ($req->hasfile('slide_image')) {
            $imageObj = array($req->file('slide_image'));
        }
        $bg_color = $req->input('bg_color');
        $slideSetId = $req->input('slide_set_id', null);

        $res = $this->slide_access_obj->insertSlide($url, $imageObj, $category, 1, $bg_color, $slideSetId, $slide_type);

        if ($res) {
            $message = 'Slide added successfully';
        }
        return redirect('slides/banners')->with('message_success', $message);
    }

    public function editSlideToSet(Request $req)
    {
        $url = $req->input('slide_url');
        $slide_id = $req->input('slide_id');
        $category = $req->input('slide_category');
        $slide_type = $req->input('slide_type');
        $bg_color = $req->input('bg_color');
        $imageObj = '';
        if ($req->hasfile('slide_image')) {
            $imageObj = array($req->file('slide_image'));
        }
        $slideSetId = $req->input('slide_set_id', null);

        $res = $this->slide_access_obj->updateSlide($slide_id, $imageObj, $url, $category, 1, $bg_color, $slideSetId, $slide_type);

        if ($res) {
            $message = 'Slide updated successfully';
        }
        return redirect('slides/banners')->with('message_success', $message);
    }

    public function deleteSlideFromSet($slideId)
    {

        $res = $this->slide_access_obj->deleteSlide($slideId);

        if ($res) {
            $message = 'Slide deleted successfully';
            return redirect('slides/banners')->with('message_success', $message);
        } else {
            $message = 'Something went wrong';
            return redirect('slides/banners')->with('message_error', $message);
        }
    }

    public function createNewSet(Request $request)
    {
        // Validate request
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'startDate'  => [
                'required',
                'date',
                Rule::unique('slide_sets', 'start_date')
                    ->ignore($slideSetId ?? null) // for update case
            ],
            'slider.*' => 'nullable|file|mimes:jpg,jpeg,png,webp',
            'box1' => 'required|file|mimes:jpg,jpeg,png,webp',
            'box2' => 'required|file|mimes:jpg,jpeg,png,webp',
            'box3' => 'required|file|mimes:jpg,jpeg,png,webp',
            'box4' => 'required|file|mimes:jpg,jpeg,png,webp',
            'box5' => 'required|file|mimes:jpg,jpeg,png,webp',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validated->errors(),
            ], 422);
        }

        $validated = $validated->validated();

        try {
            $result = DB::transaction(function () use ($validated, $request) {
                // Create slide set
                $slideSet = new SlideSet();
                $slideSet->name = $validated['name'];
                $slideSet->start_date = $validated['startDate'];
                $slideSet->save();

                $slideSetId = $slideSet->id;
                $category = 'home';
                $bg_color = '#001dbb'; // default background color
                $uploadedFiles = [];

                // Handle slider images
                if ($request->hasFile('slider')) {
                    foreach ($request->file('slider') as $index => $file) {
                        $slide = $this->slide_access_obj->insertSlide(
                            null,
                            [$file], // wrap in array ✅
                            $category,
                            1,
                            $bg_color,
                            $slideSetId,
                            'slider',
                            $index // save index instead of null
                        );
                        $uploadedFiles["slider_$index"] = $slide->attachment_id;
                    }
                }

                // Handle box images
                foreach (['box1', 'box2', 'box3', 'box4', 'box5'] as $field) {
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);
                        $boxNumber = str_replace('box', '', $field);

                        $slide = $this->slide_access_obj->insertSlide(
                            null,
                            [$file],
                            $category,
                            1,
                            $bg_color,
                            $slideSetId,
                            'banner',
                            $boxNumber
                        );
                        $uploadedFiles[$field] = $slide->attachment_id;
                    }
                }
                //Handle mobile images
                foreach (['mblbox1', 'mblbox2', 'mblbox3', 'mblbox4', 'mblbox5'] as $field) {
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);
                        $boxNumber = str_replace('mblbox', '', $field);

                        $slide = $this->slide_access_obj->insertSlide(
                            null,
                            [$file],
                            'home-small',
                            1,
                            $bg_color,
                            $slideSetId,
                            'mbl',
                            $boxNumber
                        );
                        $uploadedFiles[$field] = $slide->attachment_id;
                    }
                }

                return $slideSet->load('slides');
            });

            return response()->json([
                'message' => 'Slide set created successfully',
                'slide_set' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create slide set',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateSlideSet(Request $request, $setId)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'startDate'  => [
                'required',
                'date',
                Rule::unique('slide_sets', 'start_date')
                    ->ignore($setId ?? null)
            ],
            'slider.*' => 'required',
            'box1' => 'required',//file or attachment id
            'box2' => 'required',
            'box3' => 'required',
            'box4' => 'required',
            'box5' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validated->errors(),
            ], 422);
        }

        $validated = $validated->validated();

        try {
            $result = DB::transaction(function () use ($validated, $request, $setId) {
                $slideSet = SlideSet::findOrFail($setId);
                $slideSet->name = $validated['name'];
                $slideSet->start_date = $validated['startDate'];

                $slideSet->save();

                $category = 'home';
                $bg_color = '#001dbb';

                /** -----------------------
                 *  Handle slider images
                 * ---------------------- */
                $sliderInputs = $request->input('slider', []); // only IDs
                $sliderFiles  = $request->file('slider', []);  // only files
                    
                // Final slide IDs to keep
                $finalSlideIds = [];

                /** -----------------------
                 * 1. Existing slides (keep by attachment id)
                 * ---------------------- */
                foreach ($sliderInputs as $slideId => $attachmentId) {
                    $slide = Slide::find($slideId);
                    if ($slide) {
                        $finalSlideIds[] = (int) $slideId;
                    }
                }

                /** -----------------------
                 * 2. Handle file uploads
                 * ---------------------- */
                foreach ($sliderFiles as $key => $file) {
                    // If numeric, assume it's meant for updating existing slide
                    if (is_numeric($key)) {
                        $slide = Slide::find($key);
                        if ($slide) {
                            $fileName = $this->attachment_obj->uploadTempFiles([$file], 'image', '');
                            $newAttachmentId = $this->attachment_obj->getAttachmentId(
                                $fileName['fileName'][0],
                                'image',
                                true,
                                false
                            );
                            $slide->attachment_id = $newAttachmentId;
                            $slide->save();
                            $finalSlideIds[] = (int) $slide->slide_id;
                        }
                    }

                    // If "new", insert brand new slide
                    if ($key === "new") {
                        $newFiles = is_array($file) ? $file : [$file];
                        foreach ($newFiles as $index => $f) {
                            $newSlide = $this->slide_access_obj->insertSlide(
                                null,
                                [$f],
                                $category,
                                1,
                                $bg_color,
                                $slideSet->id,
                                'slider',
                                $index
                            );
                            if ($newSlide) {
                                $finalSlideIds[] = (int) $newSlide->slide_id;
                            }
                        }
                    }
                }

                /** -----------------------
                 * 3. Delete removed sliders
                 * ---------------------- */
                $slideSet->slides()
                    ->where('type', 'slider')
                    ->whereNotIn('slide_id', $finalSlideIds)
                    ->delete();

                /** -----------------------
                 * Handle mobile boxes (mbl slider)
                 * ---------------------- */
                    $finalMobileSlideIds = [];
                    $mobileBoxes = ['mblbox1', 'mblbox2', 'mblbox3', 'mblbox4', 'mblbox5'];

                    foreach ($mobileBoxes as $field) {

                        $existingAttachmentId = $request->input($field);

                        $files = $request->file($field, []);

                        $boxNumber = (int) str_replace('mblbox', '', $field);

                        // Existing slide by box
                        $slide = Slide::where('slide_set_id', $slideSet->id)
                            ->where('type', 'mbl')
                            ->where('box_number', $boxNumber)
                            ->first();

                        /**
                         * ------------------------
                         * 1. UPDATE EXISTING SLIDE
                         * ------------------------
                         */
                        if ($slide && isset($files) && $files instanceof \Illuminate\Http\UploadedFile) {

                            $fileName = $this->attachment_obj->uploadTempFiles([$files], 'image', '');
                            $attachmentId = $this->attachment_obj->getAttachmentId(
                                $fileName['fileName'][0],
                                'image',
                                true,
                                false
                            );

                            $slide->attachment_id = $attachmentId;
                            $slide->save();

                            $finalMobileSlideIds[] = (int) $slide->slide_id;
                            continue;
                        }

                        /**
                         * ------------------------
                         * 2. ADD NEW SLIDE
                         * ------------------------
                         */
                        if (isset($files['new']) && $files['new'] instanceof \Illuminate\Http\UploadedFile) {

                            $newSlide = $this->slide_access_obj->insertSlide(
                                null,
                                [$files['new']], 
                                'home-small',
                                1,
                                $bg_color,
                                $slideSet->id,
                                'mbl',
                                $boxNumber
                            );

                            if ($newSlide) {
                                $finalMobileSlideIds[] = (int) $newSlide->slide_id;
                            }
                            continue;
                        }

                        /**
                         * ------------------------
                         * 3. KEEP EXISTING SLIDE
                         * ------------------------
                         */
                        if ($slide && $existingAttachmentId) {
                            $finalMobileSlideIds[] = (int) $slide->slide_id;
                        }
                    }

                    /**
                     * DELETE REMOVED MOBILE SLIDES
                     */
                    $slideSet->slides()
                        ->where('type', 'mbl')
                        ->whereNotIn('slide_id', $finalMobileSlideIds)
                        ->delete();
                /** -----------------------
                 *  Handle box images
                 * ---------------------- */
                foreach (['box1', 'box2', 'box3', 'box4', 'box5'] as $field) {
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);
                        $boxNumber = str_replace('box', '', $field);

                        $slide = Slide::where('slide_set_id', $slideSet->id)
                            ->where('type', 'banner')
                            ->where('box_number', $boxNumber)
                            ->first();

                        $fileName = $this->attachment_obj->uploadTempFiles([$file], 'image', '');
                        $attachmentId = $this->attachment_obj->getAttachmentId(
                            $fileName['fileName'][0],
                            'image',
                            true,
                            false
                        );

                        if ($slide) {
                            $slide->attachment_id = $attachmentId;
                            $slide->save();
                        } else {
                            $this->slide_access_obj->insertSlide(
                                null,
                                [$file],
                                $category,
                                1,
                                $bg_color,
                                $slideSet->id,
                                'banner',
                                $boxNumber
                            );
                        }
                    }
                }

                return $slideSet->load('slides');
            });

            return response()->json([
                'success'   => true,
                'message'   => 'Slide set updated successfully',
                'slide_set' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update slide set',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getSlidesSets()
    {
        $allBannerSets = SlideSet::with('slides')
            ->orderBy('created_at', 'desc')
            ->get();

        return $allBannerSets;
    }

    public function updateSlideLink(Request $request, $slideId)
{
    try {
        // Log incoming request for debugging
        Log::info('Update slide link request', [
            'slide_id' => $slideId,
            'request_data' => $request->all()
        ]);

        // Validate with 'sometimes' for optional boolean fields
        $validated = $request->validate([
            'link' => 'nullable|string|max:255',
            'heading' => 'nullable|string|max:255',
            'heading_color' => 'nullable|string|max:20',
            'sub_heading' => 'nullable|string|max:255',
            'sub_heading_color' => 'nullable|string|max:20',
            'cto' => 'nullable|string|max:255',
            'button_bg_color' => 'nullable|string|max:20',
            'enable_fields' => 'sometimes|boolean',
            'heading_bold' => 'sometimes|boolean',
            'heading_italic' => 'sometimes|boolean',
            'sub_heading_bold' => 'sometimes|boolean',
            'sub_heading_italic' => 'sometimes|boolean',
            'cto_bold' => 'sometimes|boolean',
            'cto_italic' => 'sometimes|boolean',
            'swipe_headings' => 'sometimes|boolean',
        ]);

        $slide = Slide::findOrFail($slideId);

        // Update all fields for all slide types (including 'mbl')
        $slide->link = $validated['link'] ?? null;
        $slide->heading = $validated['heading'] ?? null;
        $slide->heading_color = $validated['heading_color'] ?? null;
        $slide->sub_heading = $validated['sub_heading'] ?? null;
        $slide->sub_heading_color = $validated['sub_heading_color'] ?? null;
        $slide->cto = $validated['cto'] ?? null;
        $slide->button_bg_color = $validated['button_bg_color'] ?? null;

        // Update boolean fields with proper casting
        $slide->heading_bold = isset($validated['heading_bold']) ? (bool)$validated['heading_bold'] : false;
        $slide->heading_italic = isset($validated['heading_italic']) ? (bool)$validated['heading_italic'] : false;
        $slide->sub_heading_bold = isset($validated['sub_heading_bold']) ? (bool)$validated['sub_heading_bold'] : false;
        $slide->sub_heading_italic = isset($validated['sub_heading_italic']) ? (bool)$validated['sub_heading_italic'] : false;
        $slide->cto_bold = isset($validated['cto_bold']) ? (bool)$validated['cto_bold'] : false;
        $slide->cto_italic = isset($validated['cto_italic']) ? (bool)$validated['cto_italic'] : false;
        $slide->swipe_headings = isset($validated['swipe_headings']) ? (bool)$validated['swipe_headings'] : false;
        $slide->status = isset($validated['enable_fields']) ? (bool)$validated['enable_fields'] : false;

        $slide->save();

        Log::info('Slide updated successfully', ['slide_id' => $slideId]);

        // Return JSON response with explicit headers
        $responseData = [
            'success' => true,
            'message' => 'Slide updated successfully',
            'link' => $slide->link,
        ];

        // Always return all fields in response for all slide types
        $responseData = array_merge($responseData, [
            'heading' => $slide->heading,
            'heading_color' => $slide->heading_color,
            'sub_heading' => $slide->sub_heading,
            'sub_heading_color' => $slide->sub_heading_color,
            'cto' => $slide->cto,
            'button_bg_color' => $slide->button_bg_color,
            'heading_bold' => (bool)$slide->heading_bold,
            'heading_italic' => (bool)$slide->heading_italic,
            'sub_heading_bold' => (bool)$slide->sub_heading_bold,
            'sub_heading_italic' => (bool)$slide->sub_heading_italic,
            'cto_bold' => (bool)$slide->cto_bold,
            'cto_italic' => (bool)$slide->cto_italic,
            'swipe_headings' => (bool)$slide->swipe_headings,
            'status' => (bool)$slide->status,
        ]);

        return response()->json($responseData, 200, ['Content-Type' => 'application/json']);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error', [
            'slide_id' => $slideId,
            'errors' => $e->errors()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422, ['Content-Type' => 'application/json']);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Slide not found', ['slide_id' => $slideId]);

        return response()->json([
            'success' => false,
            'message' => 'Slide not found',
        ], 404, ['Content-Type' => 'application/json']);

    } catch (\Exception $e) {
        Log::error('Slide update error', [
            'slide_id' => $slideId,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating the slide',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500, ['Content-Type' => 'application/json']);
    }
}

    public function deleteSlideSet($setId)
    {
        try {
            $slideSet = SlideSet::findOrFail($setId);
            $slideSet->slides()->delete();
            $slideSet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slide set and associated slides deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Slide set not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSlideSet($setId)
    {
        $slideSet = SlideSet::with('slides')->findOrFail($setId);
        return response()->json($slideSet);
    }

    public function getActiveSlideSet()
    {
        try {
            $today = now()->toDateString();

            // 1. Try today's active set
            $slideSet = SlideSet::with('slides')
                ->where('status', 'active')
                ->whereDate('start_date', $today)
                ->first();

            // 2. If none → fallback to latest previous active set
            if (!$slideSet) {
                $slideSet = SlideSet::with('slides')
                    ->where('status', 'active')
                    ->whereDate('start_date', '<=', $today)
                    ->orderByDesc('start_date') // latest previous
                    ->first();
            }

            // 3. If still none, return not found
            if (!$slideSet) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active slide set found',
                    'data'    => null
                ], 404);
            }

            
            foreach ($slideSet->slides as $slide) {
                $slide->image_url = getAttachmentUrlById($slide->attachment_id, 'media');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Active slide set found',
                'data'    => $slideSet
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching slide set',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
}
