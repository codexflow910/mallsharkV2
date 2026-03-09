<?php
namespace Packages\Store\Contracts\V2;
use Packages\Product\Models\V2\{Product,Attachments};
use Packages\Store\Models\V2\{FavouriteStore,Store};
use Packages\Core\Models\V2\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Packages\Review\Models\SuperadminReview;
use Packages\Review\Models\Review;
use Illuminate\Support\Facades\Session;
use Packages\Shipping\Models\V2\ShippingOption;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Support\Facades\Storage;
use Packages\Store\Models\V2\StoreShippingOrigin;
use Packages\Shipping\Models\V2\ShippingProfile;
trait StoreHelpers
{
    protected function convert_to_slug($str)
 {
     $str = strtolower(trim($str));
     $str = preg_replace('/[^a-z0-9-]/', '-', $str);
     $str = preg_replace('/-+/', "-", $str);
     $slug = rtrim($str, '-');

     $result_slug = Product::where('slug', $slug)->get()->toArray();
     $getRecord = count($result_slug);
     if ($getRecord > 0) {
         $slug = $slug . '-' . rand(45, 568522);
     }
     return $slug;
 }
   public function getStoreByname($data)
    {
        $nameCount = Store::where('user_id', '=', $data)->count();
        if ($nameCount > 0) {
            return true; // Store with the same name for this user exists
        } else {
            return false; // Store with the same name for this user does not exist
        }
    }
	public function get_reviews($p_id = '')
	{
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
	public function uploadStoreImageRequest($imagebanner)
	{  
    	$result = [];
		if ($imagebanner) {
			$imageObj = $imagebanner;
		} else {
			$imageObj = null;
		}
		$result = $this->uploadProcessStoreImage($imageObj);
		$totalCounter = count($result['fileName']);

		if ($totalCounter > 5) {
			return response()->json([
				'status' => false,
				'message' => 'You can upload a maximum of 5 images.',
			]);
		}

		if ($result !== null) {
			return $result;
		} else {
			return  false;
		}
	}
	public function uploadProcessStoreImage($bannerimages)
	{
		$productGallery = array();
		if (is_array($bannerimages)) {
			$destinationPath = public_path() . '/uploads/temp';
			if (!File::exists($destinationPath)) {
				File::makeDirectory($destinationPath, 0775, true);
			}
			foreach ($bannerimages as $bimage) {

				$OriginalfileName = $bimage->getClientOriginalName();
				$nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
				$fileExtension = $bimage->getClientOriginalExtension();
				$data = getimagesize($bimage);
				$width = $data[0];
				$height = $data[1];
				if ($width < 1180 || $height < 350) {
					$status = FALSE;
				} else {
					$status = true;
				}
				$fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;     
				$bimage->move($destinationPath, $fileName);
				$productGallery['fileName'][] = $fileName;
				$productGallery['validate'][] = $status;
				if ($bimage) {               
					$image = new Attachments();               
					$image->fileName = $fileName;
					$image->fileExtension = $fileExtension;
					$image->save();
					$attachmentId = $image->attachment_id;
					$bannerIds[] = $attachmentId;
				}
				// Update the serialized array in the stores table
				$updatedBannerSerialize = serialize($bannerIds);
				$productGallery['bannerserialize'] = $updatedBannerSerialize;
				$productGallery['id'] = $bannerIds;
			}
			return $productGallery;
		} else {
			return false;
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
	public static function getStoreProductsCount($storeId, $decode = null)
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $query = Product::where('store_id', '=', $storeId);
       
            $query->where('status', '!=' , '2');
        
        $count = $query->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }
    public function getAllAttachmentSizes()
	{
		$AllSize = new AttachmentSizes();
		$getDirectorywithSizes = $AllSize->all()->toArray();
		if (!empty($getDirectorywithSizes)) {
			foreach ($getDirectorywithSizes as $key => $value) {

				$directoryName = $value['name'];
				$path = public_path() . '/uploads/' . $directoryName;
				if (!File::exists($path)) {
					File::makeDirectory($path, 0775, true);
				}
			}
		}
		return $getDirectorywithSizes;
	}
		public function objToArray($arr)
			{
				$arr = json_encode($arr);
				$res = json_decode($arr, true);
				return $res;
			}
		public function updateStoreName($store_name, $id = null, $bannerImage = [])
			{
				$userId = Auth::user()->user_id;
					if (empty($userId)) {
					return false;
					}
				$store_slug = str_slug($store_name, '-');
				$store = Store::where('user_id', $userId)->first();
				if (!$store) {
					return false;
				}
				$store->store_name = isset($store_name) ? $store_name : $store->store_name;
				$store->store_slug = isset($store_slug) ? $store_slug : $store->store_slug;
				$store->store_logo = isset($id) ? $id : $store->store_logo;
				$storeBanners = isset($bannerImage['bannerserialize']) ? $bannerImage['bannerserialize'] : $store->banner_image_id;
				$store->banner_image_id = $storeBanners;
				$store->save();
				return $store;
			}

		public function getStoreProductCountstore($storeId, $decode = null, $status = '')
			{
				if ($decode == null || $decode = "") {
					$storeId = base64_decode($storeId);
				}
				$query = Product::where('store_id', '=', $storeId);
				$query->where('status', '!=' , '2');
				$count = $query->count();
				if ($count > 0) {
					return $count;
				} else {
					return 0;
				}
			}
			  public function saveStoreAddress($postedData)
			{
				$userId = Auth::user()->user_id;
					if (empty($userId)) {
						return false;
				}
				$data = array();
				$data['user_id'] = $userId;
				$data['terms_condtions'] = isset($postedData['store_term_conditon']) ? $postedData['store_term_conditon'] : '';
				$data['store_website'] = isset($postedData['store_website']) ? $postedData['store_website'] : '';
				if(isset($postedData['store_country']) && $postedData['store_country'] == ''){
					$country = getCountryByName($postedData['store_country']);
					$countryName = $country->name;
					$data['store_country'] = $countryName;
				}else{
					$data['store_country'] = $postedData['country_code'];
				}
				$data['store_state'] = isset($postedData['store_state']) ? $postedData['store_state'] : "";
				$data['store_city'] = isset($postedData['store_city']) ? $postedData['store_city'] : '';
				$data['street_address'] = isset($postedData['street_address']) ? $postedData['street_address'] : '';
				$data['zip_code'] = isset($postedData['zip_code']) ? $postedData['zip_code'] : '';
				$data['contact_info'] = isset($postedData['store_phone']) ? $postedData['store_phone'] : '';
				$data['about_store'] = isset($postedData['about_store']) ? $postedData['about_store'] : '';
				$data['policies'] = isset($postedData['shop_policy_radio']) ? $postedData['shop_policy_radio'] : '';
				Store::where('user_id', '=', $userId)->update($data);
				
				// Shipping profile data
				$storeData = Store::where('user_id', '=', $userId)->first();
				$storeId = $storeData['store_id'];
				//update general shipping profile
				$shippingData = [
					'name'      => 'General Shipping',
					'address'   => $storeData['street_address'],
					'city'      => $storeData['store_city'],
					'state_id'  => $storeData['store_state'],
					'zip_code'  => $storeData['zip_code'],
				];

				ShippingProfile::where('store_id', $storeId)
				->where('is_general', 1)
				->update($shippingData);
				
				return true;
			}
			

	function getAttachmentUrlSmallById($id, $size = 'small')
	{
		$attachment = new Attachments();
		if ($id != '' && is_int($id)) {
			$attachment->id = $id;
			$attachment->size = $size;

			return $attachment->getAttachmentUrlById();
		} else {
			return 'Please add a valid id';
		}
	}
	function getStoreIdByStoreSlug($slug){
   
    	 $store_id = DB::table('stores')->where('store_slug', $slug)->value('store_id');

		if (!empty($store_id)) {
			return $store_id;
		} else {
			return false;
		}
	}
 function getProductDetailByStoreId($storeId){
   
    	$product = Product::with('store')->where('store_id', $storeId)->first();

		if (!empty($product)) {
			return $product;
		} else {
			return false;
		}
 }
function getChildCategoryParent($childCategorySlug) {
    $result = DB::table('categories')->where('category_slug', $childCategorySlug)->select('parentCategoryID')->first();
    if ($result) {
        $catId = $result->parentCategoryID;
        // Ensure $catId is not null or zero
        if ($catId) {
            $parentCategory = DB::table('categories')->where('id', $catId)->select('category_slug', 'categoryTitle')->first();
            if ($parentCategory) {
                return $parentCategory;
            }
        }
    }
    return false;
}
function getChildCategory($childCategorySlug) {
    $result = DB::table('categories')->where('category_slug', $childCategorySlug)->select('category_slug', 'categoryTitle')->first();
    if ($result) {
       return $result;
    }
    return false;
}
function getCategoryBySlug($slug){
    if ($slug != '') {
       
        //$cat_id = DB::table('categories')->select('id')->where('categoryTitle','=',$category_name);
        $cat_slug = category::where('category_slug', $slug)
        ->first();
        
                if (!empty($cat_slug)) {
                    return $cat_slug;
                } else {
                    return false;
                }
            } else {
                return false;
            }
}

function getStoreReviewAverageApi($store_id) {
    $reviews = DB::table('reviews')
                    ->join('products', 'products.product_id', 'reviews.product_id')
                    ->join('users', 'users.user_id', 'reviews.user_id')
                    ->where('products.store_id', '=', $store_id)
                    ->select('reviews.*', 'products.store_id', 'products.title', 'products.slug', 'products.short_description', 'products.feature_image', 'users.name', 'users.user_avatar')->get()->toArray();

   
    $reviews_array = array();
   
    if (isset($reviews) && count($reviews) > 0) {
        $numer = 0;
        foreach ($reviews as $rev) {
            $numer += $rev->rating;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['comment'] = $rev->comment;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['added_by'] = $rev->user_id;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['name'] = $rev->name;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['title'] = $rev->title;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['slug'] = $rev->slug;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['short_description'] = $rev->short_description;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['created_at'] = $rev->created_at;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['feature_image'] = $rev->feature_image;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['user_avatar'] = $rev->user_avatar;
        }

        $total = count($reviews);
        $average= round($numer / $total);
        $result = array('rating'=>$average,'reviews_count' => count($reviews_array), 'review' => $reviews_array);
        return $result;
        
    }else{
        $result = array('message' => 'no review found');
        return $result;
    }
}
function checkFavouriteStore($store_id,$user_id){
    $favourite = FavouriteStore::where(['store_id' => $store_id,'user_id'=>$user_id])->first();
    if(isset($favourite)){
        return true;
    }else{
        return false;
    }
 }
 function getStoreFollowers($storeId)
{
    $result = DB::table('favourite_stores')
        ->where('store_id', $storeId)
        ->count();

    return $result;
}
 function getTotalSale($storeId)
{
  $total_sales = DB::table('order_items')->where('store_id', $storeId)->sum('quantity');

    return $total_sales;
}
function getTotalItems($storeId)
{
  	$total_items = DB::table('products')->where('store_id', $storeId)->where('status', '1')->count();
    return $total_items;
}
	
function getStoreInfoByIdApi($storeID) {
    $res = DB::table('stores')->select('store_id','store_name','store_slug','store_logo','banner_image_id','store_state','store_city','country','about_store')->where('store_id', $storeID)->first();

    if ($res) {
        return $res;
    } else {
        return null;
    }
}

	public static function getAccessibleStore($userId)
    {
        $store = Store::where('user_id', $userId)->first();

        if (!$store) {
            $pivot = DB::table('store_user')->where('user_id', $userId)->first();

            if ($pivot) {
                $store = Store::find($pivot->store_id);
            }
        }

        return $store;
    }
}