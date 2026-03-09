<?php

namespace Packages\Product\Contracts;

use App\Models\Product;
use Packages\Store\Models\Store;
use Packages\Product\Models\User;
use Packages\Store\Models\States;
use Illuminate\Support\Facades\DB;
use Packages\Review\Models\Review;
use Packages\Product\Models\Attachments;
use Packages\Product\Models\ProductTags;
use Packages\Review\Models\SuperadminReview;

trait ProductHelpers
{

    protected function convert_to_slug($str)
    {


        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        $slug = rtrim($str, '-');
        $lastProductId = Product::max('product_id');
        $currentProductId = $lastProductId ? $lastProductId + 1 : 1;
        $result_slug = Product::where('slug', $slug)->get()->toArray();
        $getRecord = count($result_slug);
        if ($getRecord > 0) {
            // $slug = $slug . '-' . rand(45, 568522);
            $slug = $slug . '-' . $currentProductId;
        }
        return $slug;
    }
    protected function convert_to_slug_for_update($str,$pid)
    {
        // this is only for update case beacuse we have to attach product id with slug if it is not unique to make it unique
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        $slug = rtrim($str, '-'); 
        
        $result_slug = Product::where('slug', $slug)->where('product_id','!=',$pid)->get()->toArray();
        $getRecord = count($result_slug);
        if ($getRecord > 0) {
            $slug = $slug . '-' . $pid;
        }
        return $slug;
    }
    function getAttachmentUrlSmallById($id, $size = 'small')
    {
        $int = (int)$id;
        $data = Attachments::find($id);
        if ($data != '') {
            return url('/uploads') . '/' . $size . '/' . $data->fileName . '_' . $id . '.' . $data->fileExtension;
        }
    }
    function generateRandomString($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getStatebyId($stateId = '') {
        $query = States::where('state_id', $stateId)->first();
        if (!empty($query)) {
            return $this->objToArray($query);
        } else {
            return false;
        }
    }

    function sanitizeDescription($description) 
    {
        if (!$description) return '';
        return str_replace(["\r", "\n"], '', html_entity_decode(strip_tags($description)));
    }

    function getAttachmentUrlById($id, $size = 'media') {

        if(isset($id)) $id = json_decode($id);
    
        if ($id != '' && is_int($id)) {

            $data = Attachments::find($id);
            if ($data) {
                return url('/uploads') . '/' . $size . '/' . $data->fileName . '_' . $id . '.' . $data->fileExtension;
            } 
        }
    }
    function getStoreInfoByUserid($userID) {
            $res = DB::table('stores')->select('store_id','store_name', 'banner_image_id', 'campaign_id','store_status','super_status')->where('user_id', $userID)->first();

            if ($res) {
                return $res;
            } else {
                return null;
            }
        }
    function getTagsByProductId($productId = "") {
        $data = array();
        $query = ProductTags::where('product_id', $productId)->get();
        if (!empty($query)) {
            $resutlt = $this->objToArray($query);
            foreach ($resutlt as $res) {
                $data[] = $res['tag_title'];
            }
        }
        return $data;
    }
    function getStoreNameInfoById($storeID)
    {
        $res = Store::select('store_id','store_name','store_slug')->where('store_id', $storeID)->first();

        if ($res) {
            return $res;
        } else {
            return null;
        }
    }
    function getStoreInfoById($storeID) {
        $res = Store::find($storeID);

        $storeData = $res->only(['store_id', 'store_name', 'store_slug', 'store_logo', 'banner_image_id', 'store_state', 'store_city', 'country', 'about_store']);
        
        if ($storeData) {
            return $storeData;
        } else {
            return null;
        }
    }

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
            ->values() // Re-index the collection
            ->toArray();

        return $allReviews;
    }

    function getYoutubeEmbedUrl($videoUrl)
    {
        
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';

        if (preg_match($longUrlRegex, $videoUrl, $matches) || preg_match($shortUrlRegex, $videoUrl, $matches)) {
            $youtube_id = $matches[1];
            // return 'https://www.youtube.com/embed/' . $youtube_id;
            return $videoUrl;
        } else {
            // Handle case when video URL doesn't match any pattern
            $youtube_id = null;
            return $youtube_id;
        }

        // if (preg_match($shortUrlRegex, $videoUrl, $matches)) {
        //     $youtube_id = $matches[count($matches) - 1];
        // }
       
    }

    public function avaiableForFlashSale($productId = '') 
    {
        $flashsalequery = Product::where('product_id', $productId)
                        ->where('flashsale_status', 'choosen')->first();
        if (!empty($flashsalequery)) {
            $currentTimeDate = date('Y-m-d H:i:s');
            $constants = config('constants.shopzit_gamification');
            $flashSaleDuration = $constants['CHOOSEN_CASHBACK_LIMIT'];
            $query = DB::table('store_campaigns')
                            ->select('flash_sale_start')->where('product_id', $productId)->where('status', 'active')->first();


            $flashEndate = date('Y-m-d H:i:s', strtotime("+$flashSaleDuration hours", strtotime($query->flash_sale_start)));


            if ($flashEndate < $currentTimeDate) {
                Product::where('product_id', $productId)
                        ->update(['flashsale_status' => 'standard','home_flash_sale'=>0]);
            }
        }
    }

    function getUserById($user_id = '') {
        if ($user_id != '') {
            return $result = User::where('user_id', '=', $user_id)->first();
        }
    }

    function objToArray($arr) {

        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    function getProductRatingById($product_id = null) {
    
        $reviews = Review::where('product_id', $product_id)->pluck('rating');  
        $average = round($reviews->avg());
    
        return [
            'average' => $average,
            'total' => $reviews->count()
        ];
    }
    public function getUsersByIds(array $userIds)
    {
        return User::whereIn('user_id', $userIds)->get()->keyBy('user_id');
    }
}
