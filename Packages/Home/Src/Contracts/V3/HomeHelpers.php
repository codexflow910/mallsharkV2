<?php
namespace Packages\Home\Contracts\V3;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Packages\Product\Models\V3\Attachments;
use Packages\Core\Models\V3\category;
trait HomeHelpers
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
					$data['store_country'] = 1;
				}
				$data['store_state'] = isset($postedData['store_state']) ? $postedData['store_state'] : "";
				$data['store_city'] = isset($postedData['store_city']) ? $postedData['store_city'] : '';
				$data['street_address'] = isset($postedData['street_address']) ? $postedData['street_address'] : '';
				$data['zip_code'] = isset($postedData['zip_code']) ? $postedData['zip_code'] : '';
				$data['contact_info'] = isset($postedData['store_phone']) ? $postedData['store_phone'] : '';
				$data['about_store'] = isset($postedData['about_store']) ? $postedData['about_store'] : '';
				$data['policies'] = isset($postedData['shop_policy_radio']) ? $postedData['shop_policy_radio'] : '';
				$data['is_completed'] = '1';
				$data['store_status'] = '1';
				Store::where('user_id', '=', $userId)->update($data);
				return true;
		}
		function getAttachmentUrlById($id, $size = 'media') {
			$global_obj = new Attachments();
			if(isset($id))$id=json_decode($id);
			if ($id != '' && is_int($id)) {
				$global_obj->id = $id;
				$global_obj->size = $size;
				return $global_obj->getAttachmentUrlById();
			} else {
				return 'Please add a valid id';
			}
		}
		function getCategoryBySlug($slug){
			if ($slug != '') {
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
}