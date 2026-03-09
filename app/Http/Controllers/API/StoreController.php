<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Attachments;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\{AttachmentSizes, Store, Product, Userrole};
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use App\Repositories\Buyer\Buyeraccess;
use App\Repositories\Store\Storeaccess;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StripeController;
use App\Repositories\Product\Productaccess;
use App\Http\Controllers\API\ElasticSearchController;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Repositories\search\Searchaccess;
use App\Http\Controllers\MailgunController;
use App\Repositories\Favourites\FavouritesStoreAccess;
use App\Models\ProductVariation;
use App\Models\Discount;
// use Intervention\Image\ImageManagerStatic as Image;

class StoreController extends Controller
{

	private $_storeobj;
	private $_storereview;
	private $_request;
	private $_productobj;
	private $_stripeObj;
	private $_campObj;
	private $_esearch;
	private $_buyerobj;
	private $_esObjAccess;
	private $_userRole;
	private $mailgun;
	private $_favobj;

	//
	public function __construct(Request $request)
	{
		$this->_storeobj = new Storeaccess();
		$this->_esearch = new ElasticSearchController();
		$this->_productobj = new Productaccess();
		$this->_stripeObj = new StripeController();
		$this->_buyerobj = new Buyeraccess();
		$this->_request = $request;
		$this->_esObjAccess = new Searchaccess();
		$this->_userRole = new Userrole();
		$this->mailgun = new MailgunController();
		$this->_favobj = new FavouritesStoreAccess();
	}

	public function index()
	{
		$userId = Auth::user()->user_id;
		try {
			$storeData = getStoreDataByUserId($userId);
			$productCount = getProductsCountByStoreId($storeData->store_id);
			$prodesTemp = html_entity_decode(strip_tags($storeData->about_store));
			$storeAbout = str_replace(array("\r", "\n"), '', $prodesTemp);
			$storeData->about_store = $storeAbout;
			if (!empty($storeData)) {
				$userData = auth()->user();

				$totalOrders = $this->_storeobj->countAllStoreOrders($storeData->store_id, $status = '');
				$totalcanceledOrders = $this->_storeobj->countAllStoreOrders($storeData->store_id, $status = "cancelled");
				$completeOrders = $this->_storeobj->countAllStoreOrders($storeData->store_id, $status = "shipped");
				$pendingOrders = $this->_storeobj->countAllStoreOrders($storeData->store_id, $status = "pending");
				$totalProducts = $this->_storeobj->getStoreProductCountstore($storeData->store_id, "decode");
				$activeProducts = $this->_storeobj->getStoreProductCountstoreApi($storeData->store_id, "decode", '1');
				$pendingProducts = $this->_storeobj->getStoreProductCountstoreApi($storeData->store_id, "decode", '3');
				$outOfStockProducts = $this->_storeobj->getStoreProductOutOfStockCountApi($storeData->store_id, "decode", '1');
				if (isset($storeData->store_state)) {
					$stateObj = getStatebyId($storeData->store_state);
					$storeData->store_state = $stateObj['state_name'];
				}

				$storeData->store_url = url('brand/' . $storeData->store_slug);
				$store_logo  = $storeData->store_logo;
				$store_banner_img = $storeData->banner_image_id;
				// return response()->json($store_banner_img);
				if ($store_logo && !empty($store_logo)) {

					$store_logo = getAttachmentUrlById((int) $store_logo, 'media');
				}
				$storeGallery = [];
				// echo 'Data: <pre>' .print_r(,true). '</pre>'; die();
				if ($store_banner_img && !empty($store_banner_img)) {
					$unserializedLogo = unserialize($store_banner_img);
					if (isset($unserializedLogo) && !empty($unserializedLogo)) {
						foreach ($unserializedLogo as $banner_img_id) {
							$storeGallery[] = getAttachmentUrlById((int) $banner_img_id, 'media');
						}
					}
				}
				$storeData->store_logo = $store_logo;
				$storeData->store_url = url('brand/' . $storeData->store_slug);
				$storeData->banner_image_urls = $storeGallery;
				$totalProductsActivecheck = false;
				$stripeChek = false;
				$profileCheck = false;
				if ($totalProducts >= 10) {
					$totalProductsActivecheck = true;
				}
				if (isset($storeData) && $storeData->stripe == '1') {
					$stripeChek = true;
				}
				if (isset($storeData->is_completed) && ($storeData->is_completed != 0)) {
					$profileCheck = true;
				}
				$storeData->totalProductsActivecheck = $totalProductsActivecheck;
				$storeData->stripeChek = $stripeChek;
				$storeData->profileCheck = $profileCheck;
				// echo 'Data: <pre>' .print_r($storeData,true). '</pre>'; die();
				$status = checkStoreActiveApi();
				$storeData->store_status = $status;
				$storeData->productCount = $productCount;
				return response()->json([
					'status' => true,
					'message' => 'Store Information',
					'storeData' => $storeData,
					'userData' => $userData,
					'shippedOrders' => $completeOrders,
					'pendingOrders' => $pendingOrders,
					'totalcanceledOrders' => $totalcanceledOrders,
					'totalOrders' => $totalOrders,
					'totalProducts' => $totalProducts,
					'activeProducts' => $activeProducts,
					'pendingProducts' => $pendingProducts,
					'outOfStockProducts' => $outOfStockProducts
				], 200);
			} else {
				return response()->json([
					'status' => false,
					'message' => "You dont have any store"
				], 401);
			}
		} catch (\Throwable $th) {
			return response()->json([
				'status' => false,
				'message' => $th->getMessage()
			], 500);
		}
	}


	public function showStores(Request $req)
	{
		$keyword = $req->input('keyword');
		try {
			$results =  $this->_storeobj->SearchStores($keyword);
			if (isset($results)) {
				$storeInformationArray = [];
				foreach ($results as $store) {
					$storeInformation = (array) $store;
					$storeInformation['store_thumbnail'] = getAttachmentUrlById((int) $store['store_logo'], 'media');
					$storeInformationArray[] = $storeInformation;
				}
			}
			if (!empty($results)) {
				return response()->json([
					'status' => true,
					'message' => 'Brand Information Successfully',
					'result' => $storeInformationArray,
				], 200);
			} else {
				return response()->json([
					'status' => false,
					'message' => "Result Not Found"
				], 401);
			}
		} catch (\Throwable $th) {
			return response()->json([
				'status' => false,
				'message' => $th->getMessage()
			], 500);
		}
	}


	public function addStore()
	{
		$userId = Auth::user()->user_id;
		if ($this->_request->isMethod('get')) {
			return response()->json([
				'error' => 'Method not allowed. Please use POST method for this route.',
			], Response::HTTP_METHOD_NOT_ALLOWED);
		}

		$validateUser = Validator::make(
			$this->_request->all(),
			[
				'store_name' => ['required', new \App\Rules\UniqueStoreName],
			]
		);

		if ($validateUser->fails()) {
			return response()->json([
				'status' => false,
				'message' => 'Validation error',
				'errors' => $validateUser->errors()
			], 401);
		}

		if ($this->_request->isMethod('post')) {
			$postedData = $this->_request->all();
			$existingStore = $this->_storeobj->getStoreBynameApi($userId);

			try {
				$image_id = null;
				$image = null;
				$imagePath = $this->_request->file('store_logo');
				$imagePathBanner = $this->_request->file('file_upload_banner');

				if (!empty($imagePath) && isset($imagePath)) {
					$image = $this->uploadImage($imagePath);
					if (!empty($image)) {
						$image_id = $image['image_id'];
					}
				}

				if (!empty($imagePathBanner) && isset($imagePathBanner)) {
					$banner_image = $this->uploadStoreImageRequest($imagePathBanner);
				} else {
					$banner_image = null;
				}

				if ($existingStore) {

					return response()->json([
						'status' => false,
						'message' => "You already created Store"
					], 401);
				} else {
					// Create new store
					$results = $this->_storeobj->saveStoreApi($postedData, $image_id, $banner_image);

					if (!empty($results)) {
						return response()->json([
							'status' => true,
							'message' => 'Company profile has been created successfully',
							'store_image' => !empty($image) ? $image['filename'] : null,
							'store_image_id' => $image_id,
							'store_banner_image' => !empty($banner_image['fileName']) ? $banner_image['fileName'] : null,
							'store_banner_id' => isset($banner_image) ? $banner_image['id'] : null,
						], 200);
					} else {
						return response()->json([
							'status' => false,
							'message' => "Company profile has not been created"
						], 401);
					}
				}
			} catch (\Throwable $th) {
				return response()->json([
					'status' => false,
					'message' => $th->getMessage()
				], 500);
			}
		}
	}

	public function editStore()
	{
		try {
			$userId = Auth::user()->user_id;
			$storeData = getStoreDataByUserId($userId);
			$storeData = $this->objToArray($storeData);
			
			$store_logo_url = getAttachmentUrlByIdApi($storeData['store_logo']);
			$storeData['store_logo_url'] = $store_logo_url;
			$bannerImageData = $storeData['banner_image_id'];
			$bannerImageIds = unserialize($bannerImageData);

			$imageUrls = [];
			foreach ($bannerImageIds as $bannerId) {
				$imageUrl = getAttachmentUrlByIdMedia((int) $bannerId, 'media');
				$mediaUrl = asset('uploads/media/' . basename($imageUrl));
				$imageUrls[] = $mediaUrl;
			}
			$storeData['banner_image_urls'] = $imageUrls;

			if (!empty($storeData)) {
				return response()->json([
					'status' => true,
					'message' => 'Store Information ',
					'storeData' => $storeData
				], 200);
			} else {
				return response()->json([
					'status' => false,
					'message' => 'Store not found'
				], 401);
			}
			// Check if the logged-in user has permission to edit this store
			if ($storeData['user_id'] !== $userId) {
				return response()->json([
					'status' => false,
					'message' => 'Unauthorized to edit this store'
				], 403);
			}
		} catch (\Throwable $th) {
			return response()->json([
				'status' => false,
				'message' => $th->getMessage()
			], 500);
		}
	}

	public function updateStore(Request $request)
	{

		$userId = Auth::user()->user_id;
		$store = getStorDataUserId($userId); // Fetch store by ID
		$store_name  =  $request->input('store_name');
		$image_id = null;
		$image = null;
		$updatedBannerSerialize = '';
		$totalProductsActivecheck = false;
		$stripeChek = false;
		$profileCheck = false;
		if (!$store) {
			try {


				$validateUser = Validator::make(
					$this->_request->all(),
					[
						'store_name' => 'unique:stores,store_name|required',
					]
				);

				if ($validateUser->fails()) {
					return response()->json([
						'status' => false,
						'message' => 'Validation error',
						'errors' => $validateUser->errors()
					], 401);
				}
				$store = new Store();
				$filesname = $request->file('file_upload_banner');
				// return response()->json($request->hasFile('file_upload_banner'));
				if ($request->hasFile('file_upload_banner')) {
					foreach ($filesname as $filename) {
						$destinationPath = public_path() . '/uploads/media/' . $filename;
						if (File::exists($destinationPath)) {
							unlink($destinationPath);
						} else {
							$image = $this->uploadImage($filename);
							$bannerIds[] = $image['image_id'];
						}
						$updatedBannerSerialize = serialize($bannerIds);
					}
				}
				$imagePath = $request->file('store_logo');
				if ($request->hasFile('store_logo')) {
					$image = $this->uploadImage($imagePath);
					if (!empty($image)) {
						$image_id = $image['image_id'];
					}
				}
				//  $updatedBannerSerialize;
				$store_slug = str_slug($store_name, '-');
				$store->store_name = isset($store_name) ? $store_name : null;
				$store->store_slug = isset($store_slug) ? $store_slug : null;
				$store->store_logo = isset($image_id) ? $image_id : null;
				$store->user_id = isset($userId) ? $userId : null;
				$storeBanners = isset($updatedBannerSerialize) ? $updatedBannerSerialize : '';
				$store->banner_image_id = $storeBanners;
				$store->save();
				// get store Logo
				$this->_userRole->user_id = $userId;
				$this->_userRole->role_id = '2';
				$this->_userRole->save();
				$totalProducts = $this->_storeobj->getStoreProductCountstore($store->store_id, "decode");
				$store->store_logo = getAttachmentUrlById((int) $store->store_logo, 'media');
				$store->store_url = url('brand/' . $store->store_slug);
				// get store banners 
				$storeGallery = [];
				$store_banner_img = $store->banner_image_id;
				if ($store_banner_img && !empty($store_banner_img)) {
					$unserializedLogo = unserialize($store_banner_img);
					foreach ($unserializedLogo as $banner_img_id) {
						$storeGallery[] = getAttachmentUrlById((int) $banner_img_id, 'media');
					}
					$store->banner_image_id = $storeGallery;
				}
				$store->banner_image_id = $storeGallery;
				// $store->status = StoreStatusById($store->status);
				if ($totalProducts >= 10) {
					$totalProductsActivecheck = true;
				}
				if (isset($storeData) && $storeData->stripe == '1') {
					$stripeChek = true;
				}
				if (isset($store->is_completed) && ($store->is_completed != 0)) {
					$profileCheck = true;
				}
				$store->totalProductsActivecheck = $totalProductsActivecheck;
				$store->stripeChek = $stripeChek;
				$store->profileCheck = $profileCheck;
				// echo 'Data: <pre>' .print_r($storeData,true). '</pre>'; die();
				$status = checkStoreActiveApi();
				$store->store_status = $status;
				if (!empty($store)) {
					return response()->json([
						'status' => true,
						'message' => 'Store created successfully',
						'data' => $store,
						'store_new' => true

					], 200);
				} else {
					return response()->json([
						'status' => false,
						'message' => "Store created failed"
					], 401);
				}
			} catch (Exception $e) {
				return response()->json($e->getMessage());
			}
		} else {
			if ($store->user_id !== $userId) {
				return response()->json([
					'status' => false,
					'message' => 'Unauthorized to update this store'
				], 403);
			}
			try {
				// Store Banners updated
				if ($store->store_name != $store_name) {
					$validateUser = Validator::make(
						$this->_request->all(),
						[
							'store_name' => 'unique:stores,store_name|required',
						]
					);

					if ($validateUser->fails()) {
						return response()->json([
							'status' => false,
							'message' => 'Validation error',
							'errors' => $validateUser->errors()
						], 401);
					}
				}
				$bannerSerialize = $store->banner_image_id;
				$bannerIds = unserialize($bannerSerialize);
				$filesname = $request->file('file_upload_banner');
				if ($request->hasFile('file_upload_banner')) {
					foreach ($filesname as $filename) {
						$destinationPath = public_path() . '/uploads/media/' . $filename;
						if (File::exists($destinationPath)) {
							unlink($destinationPath);
						}
						if (isset($filename)) {
							$image = $this->uploadImage($filename);
							$banners[] = $image['image_id'];
						}
						if (isset($banners) && is_array($banners)) {
							$updatedBannerSerialize = serialize($banners);
							// dd($updatedBannerSerialize);
							DB::table('stores')->where('store_id', $store->store_id)->update(['banner_image_id' => $updatedBannerSerialize]);
						}
					}
				}
				if (isset($bannerIds) && !empty($bannerIds)) {
					foreach ($bannerIds as $bannerId) {
						DB::table('attachment')->where('attachment_id', $bannerId)->delete();
						// Find and remove the image ID from the $bannerIds array
						$index = array_search($bannerId, $bannerIds);
						if ($index !== false) {
							unset($bannerIds[$index]);
						}
					}
				}

				$imagePath = $request->file('store_logo');

				if ($request->hasFile('store_logo')) {
					$image = $this->uploadImage($imagePath);
					if (!empty($image)) {
						$image_id = $image['image_id'];
					}
				}
				$updated = $this->_storeobj->updateStoreNameApi($store_name, $image_id);
				$storeGallery = [];
				$updated->store_logo = getAttachmentUrlById((int) $updated->store_logo, 'media');
				$updated->store_url = url('brand/' . $updated->store_slug);
				if (isset($updated->store_state)) {
					$stateObj = getStatebyId($updated->store_state);
					$updated->store_state = $stateObj['state_name'];
				}

				$store_banner_img = $updated->banner_image_id;
				if ($store_banner_img && !empty($store_banner_img)) {

					$unserializedLogo = unserialize($store_banner_img);
					foreach ($unserializedLogo as $banner_img_id) {
						$storeGallery[] = getAttachmentUrlById((int) $banner_img_id, 'media');
					}
				}
				$totalProducts = $this->_storeobj->getStoreProductCountstore($updated->store_id, "decode");
				$updated->banner_image_id = $storeGallery;
				// $store->status = StoreStatusById($store->status);
				if ($totalProducts >= 10) {
					$totalProductsActivecheck = true;
				}
				if (isset($updated) && $updated->stripe == '1') {
					$stripeChek = true;
				}
				if (isset($updated->is_completed) && ($updated->is_completed != 0)) {
					$profileCheck = true;
				}
				$updated->totalProductsActivecheck = $totalProductsActivecheck;
				$updated->stripeChek = $stripeChek;
				$updated->profileCheck = $profileCheck;
				// echo 'Data: <pre>' .print_r($storeData,true). '</pre>'; die();
				$status = checkStoreActiveApi();
				$updated->store_status = $status;
				if (!empty($updated)) {
					return response()->json([
						'status' => true,
						'message' => 'Store updated successfully',
						'data' => $updated
					], 200);
				} else {
					return response()->json([
						'status' => false,
						'message' => "Store update failed"
					], 401);
				}
			} catch (\Throwable $th) {
				return response()->json([
					'status' => false,
					'message' => $th->getMessage()
				], 500);
			}
		}
		// Check if the logged-in user has permission to update this store


	}

	// image remaing
	public function addStorebk()
	{
		if ($this->_request->isMethod('get')) {
			return response()->json([
				'error' => 'Method not allowed. Please use POST method for this route.',
			], Response::HTTP_METHOD_NOT_ALLOWED);
		}

		$validateUser = Validator::make(
			$this->_request->all(),
			[
				'store_logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
				// 'store_name' => ['required', new \App\Rules\UniqueStoreName],
			]
		);
		if ($validateUser->fails()) {
			return response()->json([
				'status' => false,
				'message' => 'validation error',
				'errors' => $validateUser->errors()
			], 401);
		}
		// dd($this->_request->all());
		if ($this->_request->isMethod('post')) {
			$postedData = $this->_request->all();
			// dd($postedData['store_logo']);
			$file = $this->_request->file('store_logo');
			if (!empty($file) && isset($file)) {

				$imageArray = $this->uploadBrandLogo($file);
			}
			try {
				$results =  $this->_storeobj->saveStoreApi($postedData);

				if (!empty($results)) {
					return response()->json([
						'status' => true,
						'message' => 'Company profile has been created successfully',

					], 200);
				} else {
					return response()->json([
						'status' => false,
						'message' => "Company profile has been Not created "
					], 401);
				}
			} catch (\Throwable $th) {
				return response()->json([
					'status' => false,
					'message' => $th->getMessage()
				], 500);
			}
		}
	}


	public function addStoreAddress()
	{
		if ($this->_request->isMethod('get')) {
			return response()->json([
				'error' => 'Method not allowed. Please use POST method for this route.',
			], Response::HTTP_METHOD_NOT_ALLOWED);
		}



		if ($this->_request->isMethod('post')) {
			$validateUser = Validator::make(
				$this->_request->all(),
				[
					'street_address' => 'required',
					'store_state' => 'required|numeric|exists:states,state_id',
					'store_city' => 'required|regex:/^[\pL\s]+$/u',
					'zip_code' => 'required',
				]
			);
			if ($validateUser->fails()) {
				return response()->json([
					'status' => false,
					'message' => 'validation error',
					'errors' => $validateUser->errors()
				], 401);
			}
			$postedData = $this->_request->input();
			// echo 'Data: <pre>' .print_r($postedData,true). '</pre>'; die();
			try {
				$user = auth()->user();
				$results =  $this->_storeobj->saveStoreAddressApi($postedData);

				if (!empty($results)) {
					$store = Store::where('user_id', $user->user_id)->first();
					$store->store_logo = getAttachmentUrlById((int) $store->store_logo, 'media');
					$store->store_url = url('brand/' . $store->store_slug);
					$stateObj = getStatebyId($store->store_state);
					$store->store_state = $stateObj['state_name'];
					$store_banner_img = $store->banner_image_id;
					$storeGallery = [];
					if ($store_banner_img && !empty($store_banner_img)) {

						$unserializedLogo = unserialize($store_banner_img);
						foreach ($unserializedLogo as $banner_img_id) {
							$storeGallery[] = getAttachmentUrlById((int) $banner_img_id, 'media');
						}
					}
					$store->banner_image_id = $storeGallery;
					$totalProducts = $this->_storeobj->getStoreProductCountstore($store->store_id, "decode");
					$totalProductsActivecheck = false;
					$stripeChek = false;
					$profileCheck = false;
					if ($totalProducts >= 10) {
						$totalProductsActivecheck = true;
					}
					if (isset($store) && $store->stripe == '1') {
						$stripeChek = true;
					}
					if (isset($store->is_completed) && ($store->is_completed != 0)) {
						$profileCheck = true;
					}
					$store->totalProductsActivecheck = $totalProductsActivecheck;
					$store->stripeChek = $stripeChek;
					$store->profileCheck = $profileCheck;
					// echo 'Data: <pre>' .print_r($storeData,true). '</pre>'; die();
					$status = checkStoreActiveApi();
					$store->store_status = $status;
					// $status = StoreStatusById($store->store_id);
					// $store->store_status = $status;
					return response()->json([
						'status' => true,
						'message' => 'Store address has been updated successfully',
						'data' => $store
					], 200);
				} else {
					return response()->json([
						'status' => false,
						'message' => "Store address has been Not created "
					], 404);
				}
			} catch (\Throwable $th) {
				return response()->json([
					'status' => false,
					'message' => $th->getMessage()
				], 500);
			}
		}
	}

	/*
     * * Create Directories if Not Exist and Retrun Images Sizes
     */

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

	public function updateEditStoreAddress()
	{
		$validateUser = Validator::make(
			$this->_request->all(),
			[
				'store_name' => ['required', new \App\Rules\UniqueStoreName],
				'street_address' => 'required',
				'store_phone' => 'required|numeric',
				'store_country' => 'required|numeric|exists:countries,id',
				'store_state' => 'required|numeric|exists:states,state_id',
				'store_city' => 'required|regex:/^[\pL\s]+$/u',
				'zip_code' => 'required',
				'store_website' => 'required',
				'about_store' => 'required',
			]
		);
		if ($validateUser->fails()) {
			return response()->json([
				'status' => false,
				'message' => 'validation error',
				'errors' => $validateUser->errors()
			], 401);
		}
		$postedData = $this->_request->input();

		try {
			$results = $this->_storeobj->updateStoreApi($postedData);

			if (!empty($results)) {
				return response()->json([
					'status' => true,
					'message' => 'Company profile data has been updated successfully',

				], 200);
			} else {
				return response()->json([
					'status' => false,
					'message' => "Company profile not updated "
				], 401);
			}
		} catch (\Throwable $th) {
			return response()->json([
				'status' => false,
				'message' => $th->getMessage()
			], 500);
		}
	}

	public function checkShopAvailablity()
	{
		$validateUser = Validator::make(
			$this->_request->all(),
			[
				'store_name' => 'required',
			]
		);
		if ($validateUser->fails()) {
			return response()->json([
				'status' => false,
				'message' => 'validation error',
				'errors' => $validateUser->errors()
			], 401);
		}

		$postedData = $this->_request->input();

		try {
			$result = $this->_storeobj->getStoreBynameApi($postedData);
			if ($result) {
				return response()->json([
					'status' => false,
					'message' => "There are no brand name exist"
				], 401);
			} else {
				return response()->json([
					'status' => true,
					'message' => 'Brand Name Available',

				], 200);
			}
		} catch (\Throwable $th) {
			return response()->json([
				'status' => false,
				'message' => $th->getMessage()
			], 500);
		}
	}

	public function chat()
	{
		try {
			$users = User::all(['user_id', 'name', 'email']);
			if ($users) {
				return response()->json([
					'status' => true,
					'message' => "User Data",
					'User data' => $users,
				], 200);
			} else {
				return response()->json([
					'status' => true,
					'message' => 'User not found',

				], 401);
			}
		} catch (\Throwable $th) {
			return response()->json([
				'status' => false,
				'message' => $th->getMessage()
			], 500);
		}
	}

	public function storeReports()
	{
		$storeSales = "";
		$products = $this->_productobj->getProductsReportsApi();

		try {
			if ($this->_request->isMethod('get') && $this->_request->input('type') != "") {

				$postData = $this->_request->input();
				// type not empty product id empty custom empty
				if (isset($postData['type']) && $postData['product_id'] == "" && $postData['date_to'] == "" && $postData['date_from'] == "") {
					$storeSales = getYearlyOrdersOfStoreApi($postData['type']);
				}
				// type not empty product not empty custom empty
				else if ((isset($postData['type']) && $postData['type'] != "") && (isset($postData['product_id']) && $postData['product_id'] != "") && $postData['date_to'] == "" && $postData['date_from'] == "") {
					$storeSales = getProductSaleApi($postData['type'], $postData['product_id'], "", "");
				}
				// type not empty product not empty custom not empty
				else if ($postData['type'] != "" && $postData['product_id'] != "" && $postData['date_to'] != "" && $postData['date_from'] != "") {
					$storeSales = getProductSaleApi("date", $postData['product_id'], $postData['date_to'], $postData['date_from']);
				}
				// type not empty product empty and custom not empty
				else if ($postData['date_to'] != "" && $postData['date_from'] != "" && $postData['product_id'] == "") {
					$storeSales = getYearlyOrdersOfStoreApi("date", $postData['date_to'], $postData['date_from']);
				}
			} else {
				$storeSales = getYearlyOrdersOfStoreApi("yearly");
			}
			return response()->json([
				'status' => true,
				'message' => "Store Sale Report",
				'storeSales' => $storeSales,
				'products' => $products,
			], 200);
		} catch (\Throwable $th) {
			return response()->json([
				'status' => false,
				'message' => $th->getMessage()
			], 500);
		}
	}


	public function StoreStatusChange()
	{
		$validateUser = Validator::make(
			$this->_request->all(),
			[
				'store_status' => 'required|in:0,2',
			]
		);
		if ($validateUser->fails()) {
			return response()->json([
				'status' => false,
				'message' => 'validation error',
				'errors' => $validateUser->errors()
			], 401);
		}

		$posedData = $this->_request->input();
		try {
			$results = $this->_storeobj->storeStatusChangeApi($posedData);
			// dd($results);
			if ($results['status']) {
				return response()->json([
					'status' => true,
					'message' => $results['message'],
				]);
			} else {
				return response()->json([
					'status' => false,
					'message' => $results['message'],
				]);
			}
		} catch (\Throwable $th) {
			echo $th->getMessage();
		}
	}

	public function objToArray($arr)
	{
		$arr = json_encode($arr);
		$res = json_decode($arr, true);
		return $res;
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

	/**
	 *  Function for upload Image
	 *
	 */
	public function uploadProcessStoreImage($bannerimages)
	{
		$productGallery = array();
		if (is_array($bannerimages)) {
			// if (count($productsimages) > 0) {
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
				$fileName = $this->_productobj->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
				$bimage->move($destinationPath, $fileName);
				$productGallery['fileName'][] = $fileName;
				$productGallery['validate'][] = $status;

				if ($bimage) {
					$image = new Attachments();
					$image->fileName = $fileName;
					$image->fileExtension = $fileExtension;
					$image->save();
					$attachmentId = $image->attachment_id;
					// Add the new image ID to the $bannerIds array
					$bannerIds[] = $attachmentId;
				}

				// Update the serialized array in the stores table
				$updatedBannerSerialize = serialize($bannerIds);
				$productGallery['bannerserialize'] = $updatedBannerSerialize;
				$productGallery['id'] = $bannerIds;
			}
			// }
			return $productGallery;
		} else {
			return false;
		}
	}

	private function uploadImage($imagePath)
	{
		$fileName = $this->_productobj->generateRandomString(15) . '.png';
		$tempFolderPath = 'uploads/temp/';

		if ($fileName) {
			$fileAttr = pathinfo($fileName);
			$nameItemOnly = $fileAttr['filename'];
			$fileExtension = $fileAttr['extension'];

			$attachments = new Attachments();
			$attachments->fileName = $nameItemOnly;
			$attachments->fileExtension = $fileExtension;
			$attachments->save();
			$attachmentId = $attachments->attachment_id;

			if ($attachmentId) {
				$getRealPath = $imagePath->getRealPath();
				$fileName = $nameItemOnly . '_' . $attachmentId . '.' . $fileExtension;

				$getDirectorywithSizes = $this->getAllAttachmentSizes();

				foreach ($getDirectorywithSizes as $value) {
					$directoryName = $value['name'];
					$width = $value['width'];
					$height = $value['height'];
					$destinationPath = public_path() . '/uploads/' . $directoryName;
					if ($width == 0 && $height == 0) {
						$readImage = file_get_contents($getRealPath);
						file_put_contents($destinationPath . '/' . $fileName, $readImage);
					} else {
						$image = Image::make($getRealPath);
						$resizedImage = $image->resize($width, $height, function ($constraint) {
							$constraint->aspectRatio();
						});
						$resizedImage->save($destinationPath . '/' . $fileName);
					}
				}
			}
			File::delete($tempFolderPath . $fileName);

			return [
				'filename'	=> $fileName,
				'image_id' => $attachmentId,
			];
		}
	}
	public function updateAccountSetting(Request $request)
	{
		try {
			// Validate the request data
			$request->validate([
				'email' => 'required',
			]);

			// Validation passed, proceed with the rest of the code
			// Get the authenticated user
			$user = auth()->user();
			
			// Update the fields
			$user->email = $request->input('email');
			$user->contact_no = $request->has('contact_no') && $request->filled('contact_no') ? $request->input('contact_no') : $user->contact_no;

			// Update password if provided
			if ($request->has('new_password') && !is_null($request->input('new_password'))) {
				// Validate the old password
				if ($user->password !== md5($request->input('old_password'))) {
					return response()->json(['error' => 'Invalid old password'], 401);
				}

				// Update password
				$user->password = md5($request->input('new_password'));
			}

			// Save the changes
			$user->save();

			return response()->json(['statue'=>true,'data'=>$user, 'message' => 'User updated successfully']);
		} catch (ValidationException $e) {
			// Validation failed, return the validation errors
			return response()->json(['errors' => $e->errors()], 422);
		}
	}
	public function updateStoreAbout(Request $request)
	{
	
		$validateUser = Validator::make(
			$request->all(),
			[
				'about_store' => 'required',
			]
		);

		if ($validateUser->fails()) {
			return response()->json([
				'status' => false,
				'message' => 'validation error',
				'errors' => $validateUser->errors()
			], 401);
		}
		$user = auth()->user();
		$store = Store::where('user_id', $user->user_id)->first();
		$store->about_store = $request->input('about_store');
		$store->save();
		return response()->json([
			'status' => true,
			'message' => 'store about updated successfully',
			'data' => $store
		]);
	}
	public function getStoreAbout()
	{
		$user = auth()->user();
		$store = getStoreDataByUserId($user->user_id);
		if (isset($store) && !empty($store)) {
			return response()->json([
				'status' => true,
				'data' => $store
			]);
		} else {
			return response()->json([
				'status' => false,
				'message' => 'store data not available'
			]);
		}
	}
	public function storeHome(Request $request)
	{
		$product_id = $request->input('product_id');
			$product = Product::with('store')->find($product_id);
			if(isset($product)){
				if (isset($product->store) && !is_null($product->store)) {
					$store_id = $product->store->store_id;
					
						
							if ($store_id != '') {
								$all_filters = $request->all();
								$productResults = $this->_esearch->searchDocumentStoreApi($all_filters, $store_id);

								$category_array = [];
								$parent_array = []; // Array to store all parent categories
								
									if (isset($productResults['records']) && count($productResults['records']) > 0) {
										foreach ($productResults['records'] as $product) {
											if (isset($product['_source']['categories']) && is_array($product['_source']['categories'])) {
												foreach ($product['_source']['categories'] as $category) {
													
													$parentCategory = getChildCategoryParent($category); // Retrieve parent category if exists
													$childCategory = getChildCategory($category);
													// Case: No parent category -> This category is a parent itself
													if (!$parentCategory) {
														// Retrieve category details and image
														$getcategory = getCategoryBySlug($category);
														$image = getAttachmentUrlByIdApi(intval($getcategory->image_id));
														$getcategory->image_url = $image;
														
														// Check if this parent category already exists
														$parentExists = false;
														foreach ($category_array as &$parentCategoryItem) {
															if ($parentCategoryItem['parent']->category_slug === $category) {
																$parentExists = true;
																break;
															}
														}
														
														// Add new unique parent category with an empty 'children' array
														if (!$parentExists) {
															$category_array[] = [
																'parent' => $getcategory,
																'children' => []
															];
														}
													} 
													
													// Case: Parent category exists -> Add this category as a child of the parent
													else {
														$parentSlug = $parentCategory->category_slug; // Assuming slug is the unique identifier
														
														// Find or create parent category entry in the main array
														$parentIndex = null;
														foreach ($category_array as $index => &$parentCategoryItem) {
															if ($parentCategoryItem['parent']->category_slug === $parentSlug) {
																$parentIndex = $index;
																break;
															}
														}
									
														// If parent category not found, add it
														if ($parentIndex === null) {
															$parentCategoryData = getCategoryBySlug($parentSlug);
															$parentCategoryData->image_url = getAttachmentUrlByIdApi(intval($parentCategoryData->image_id));
									
															$category_array[] = [
																'parent' => $parentCategoryData,
																'children' => $childCategory // Initialize children with this category
															];
														} else {
															// Add unique child category to the existing parent category
															if (!in_array($childCategory, $category_array[$parentIndex]['children'])) {
																$category_array[$parentIndex]['children'][] = $childCategory;
															}
														}
													}
												}
											}

										}
									}
								
								// Check the results
								// dd($category_array);
								
								$total_sales = DB::table('order_items')->where('store_id', $store_id)->sum('quantity');
			
								$total_items = DB::table('products')->where('store_id', $store_id)->where('status', '1')->count();
								// $campaign = $this->campaignObj->getSupportingCampaign($store_id);
								$storeInfo = getStoreInfoByIdApi($store_id);
								$store_logo = '';
								if (isset($storeInfo->store_logo) && $storeInfo->store_logo != '') {
									$logoId = $storeInfo->store_logo;
									if(!empty($logoId)){
										// if(is_int($logoId)){
										$logoUrl = getAttachmentUrlSmallById(intval($logoId));
										$store_logo = $logoUrl;
										// }
									}
									$storeInfo->store_logo = $store_logo;
								}
								$storeBanner = null;
								if (isset($storeInfo->banner_image_id) && $storeInfo->banner_image_id != '') {
									$banner_image_ids = unserialize($storeInfo->banner_image_id);
									if(is_array($banner_image_ids) && !empty($banner_image_ids)){
										foreach($banner_image_ids as $banner_img_id){
											// if(is_int($banner_img_id)){
												$bannerUrl = getAttachmentUrlByIdApi(intval($banner_img_id));
												$storeBanner = $bannerUrl;
											// }
											
										}
									}
								$storeInfo->bannerUrl = $storeBanner;
								}
								$state = getStatebyId($storeInfo->store_state);
								
								if(!is_null($state)){
									$storeInfo->store_state = $state['state_name'];
								}
								$review_rating = getStoreReviewAverageApi($store_id);
								if(auth()->user()){
									$user_id = auth()->user()->user_id;
									if(!is_null($user_id)){
										$favourite = checkFavouriteStore($store_id,$user_id);
									}
								}else{
									$favourite = false;
								}
								$storeInfo->favourite = $favourite;

								$followers = getStoreFollowers($store_id);
								$store = ['storeInfo'=>$storeInfo,'reviews' => $review_rating, 'followers' => $followers];

								$cartProducts = $this->_esObjAccess->getCartAddedProduct();
									return response()->json([
										'status' => true,
										'data' => [
											'productResults' => $productResults['records'],
										'cartProducts' => $cartProducts,
										'total_sales' => $total_sales,
										'total_items' => $total_items,
										'store_data' => $store,
										'categories' => $category_array
										]
									]);
							}else {
							return response()->json([
								'status' => false,
								'message' => 'Sorry, this product does not belongs to any store'
								]);
							}
					}else {
						return response()->json([
							'status' => false,
							'message' => 'Sorry, this product does not belongs to any store'
						]);
					}
			} else {
				return response()->json([
					'status' => false,
					'message' => 'Sorry, this product does not exist'
				]);
			}
	}
	public function storeHomeFilter($store_id,Request $request)
	{

		

		$store_id = $store_id;
		if (isset($store_id)) {
			$all_filters = $request->all();
			$productResults = $this->_esearch->searchDocumentStoreApi($all_filters, $store_id);

			if (!empty($productResults) && count($productResults['records']) > 0) {
				$totalProducts = $productResults['total']['value'];

				$productArray = [];
				foreach ($productResults['records'] as  $records) {
					$products = (array) $records;
					$products['_source']['feature_image'] = getAttachmentUrlById(intval($records['_source']['feature_image']), 'media');
					$rating = getProductRatingById($records['_source']['product_id']);
					$products['_source']['rating'] = $rating;
					$productArray['productResults'][] = $products;
				}
				return response()->json([
					'status' => true,
					'data' => $productArray
				]);
			}else {
				return response()->json([
					'status' => false,
					'message' => 'No Product Found'
				]);
			}
		}else {
			return response()->json([
				'status' => false,
				'message' => 'Sorry,store does not exist'
			]);
		}
	}
		
	public function seller_account_notification(Request $request)
	{
		$user = auth()->user(); // Fetch the authenticated user

		// Validate incoming data
		$validatedData = $request->validate([
			'email_alert' => 'required',
			'sms_alert' => 'required',
		]);

		// Update user's notification preferences
		$user->sms_alerts = $validatedData['sms_alert'];
		$user->email_alerts = $validatedData['email_alert'];
		$user->save();

		return response()->json([
			'message' => 'Notification preferences updated successfully',
			'user' => $user // Optionally, you can return the updated user data
		]);
	}
	public function contactSupportApi(Request $request)
	{

		try {
			$validateUser = Validator::make(
				$this->_request->all(),
				[
					'name' => 'required',
					'email' => 'required',
					'message' => 'required',
					'reason' => 'required'
				]
			);
			if ($validateUser->fails()) {
				return response()->json([
					'status' => false,
					'message' => 'validation error',
					'errors' => $validateUser->errors()
				], 401);
			}
// dd(2);
			$message = $request->message;
			$orderNumber = $request->phone;
			$toFullname = $request->name;
			$subject = 'Customer Support Query';
			$toEmail = $request->email;
			$reason_text = ucfirst($request->reason);
			


			$html = view('mail.contactUser', ['message' => $message, 'orderNumber' => $orderNumber, 'toCompletename' => $toFullname, 'support' => $reason_text, 'fullname' => $toFullname]);
			$params = [
				'from'    => 'MallShark  ' . $toEmail,
				'to'      => env('SUPPORT_EMAIL'),
				'subject' => $subject,
				'text'    => $toFullname,
				'html'    => "$html",
			];
			   $results = $this->mailgun->SendMailgunMail($params); 
			// user reply
			$subject = 'Customer Support';
			$toEmail = $request->email;

			$html = view('mail.contact-us', ['message' => $message, 'phone' => $orderNumber, 'toCompletename' => $toFullname,'support' => $reason_text]);
			// $html=view('mail.contactUserReply',['message'=>$message, 'phone' => $phone , 'toCompletename' => $toCompletename,'reason_text' =>$reason_text, 'support' =>$support]);
			$params = [
				'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
				'to'      => $toEmail,
				'subject' => $subject,
				'text'    => $toFullname,
				'html'    => "$html",
			];
			  $results = $this->mailgun->SendMailgunMail($params); 
			return response()->json([
				'status' => true,
				'success' => true,
				'message' => 'Your Query has been submitted successfully'
			]);
		} catch (Exception $e) {
			return response()->json([
				'status' => false,
				'success' => false,
				'message' => 'An Execption occurred'
			]);
		}
	}

	public function bulkUpdateProducts(Request $request)
	{
		ini_set('max_execution_time', 0);

		try {
			$csvFile = $request->file('csv_file');

			// Validate the uploaded file
			if (!$csvFile || $csvFile->getClientOriginalExtension() !== 'csv') {
				return response()->json(['message' => 'Invalid file type. Please upload a CSV file.', 'status' => false], 400);
			}

			if (($handle = fopen($csvFile, 'r')) !== false) {
				$headers = fgetcsv($handle);

				if (!in_array('Handle', $headers) || !in_array('Title', $headers) || !in_array('Variant Price', $headers) || !in_array('Variant Inventory', $headers)) {
					return response()->json(['message' => 'Headers must include Handle, Title, Variant Price, and Variant Inventory', 'status' => false], 400);
				}

				DB::beginTransaction();
				$userId = getCurrentUserId();
				$allUpdatesSuccessful = true;
				$errors = []; // To track failures.
				$storeData = Store::where('user_id', $userId)->first();
				$storeId = $storeData->store_id;

				while (($data = fgetcsv($handle)) !== false) {
					$row = array_combine($headers, $data);

					$row['Variant Price'] = trim($row['Variant Price']);
                    $row['Variant Inventory'] = trim($row['Variant Inventory']);
					// Validate price and inventory
					if (($row['Variant Price'] !== '' || $row['Variant Inventory'] !== '')) {
						if (($row['Variant Price'] !== '' && !isValidPrice($row['Variant Price'])) || 
							($row['Variant Inventory'] !== '' && !isValidStock($row['Variant Inventory']))) {
						$errors[] = "Invalid data for Handle: {$row['Handle']} (Price: {$row['Variant Price']}, Inventory: {$row['Variant Inventory']})";
						continue;
					}
					}

					$product = Product::where('slug', $row['Handle'])->first();

					if ($product && $product->user_id == $userId) {
						if ($product->product_type != 'variant') {
							$product->price = $row['Variant Price'] !== '' ? $row['Variant Price'] : $product->price;
							$product->current_stock = $row['Variant Inventory'] !== '' ? $row['Variant Inventory'] : $product->current_stock;
							$product->save();
						} else {
							$productVariations = ProductVariation::where('product_id', $product->product_id)->get();

							foreach ($productVariations as $variation) {
								// Normalize Option1 for comparison
								$option1Value = is_string($row['Option1 Value']) ? strtolower($row['Option1 Value']) : $row['Option1 Value'];
								$variationOption1 = is_string($variation->option1) ? strtolower($variation->option1) : $variation->option1;
							
								$match = !empty($row['Option1 Value']) && $variationOption1 == $option1Value;
							
								if (!empty($row['Option2 Value'])) {
									// Normalize Option2 for comparison
									$option2Value = is_string($row['Option2 Value']) ? strtolower($row['Option2 Value']) : $row['Option2 Value'];
									$variationOption2 = is_string($variation->option2) ? strtolower($variation->option2) : $variation->option2;
							
									$match = $match && $variationOption2 == $option2Value;
								}
							
								if (!empty($row['Option3 Value'])) {
									// Normalize Option3 for comparison
									$option3Value = is_string($row['Option3 Value']) ? strtolower($row['Option3 Value']) : $row['Option3 Value'];
									$variationOption3 = is_string($variation->option3) ? strtolower($variation->option3) : $variation->option3;
							
									$match = $match && $variationOption3 == $option3Value;
							
								}
							
								if ($match) {
									$variation->price = $row['Variant Price'];
									$variation->current_stock = $row['Variant Inventory'];
									$variation->save();
								}
							}
						}
						$discount = Discount::where(['store_id' => $storeId, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();

						if (isset($discount) && !empty($discount)) {
							if ($discount->discount_value_type == 'percent') {
								$discountValue = $product->price * ($discount->discount_value / 100);
								$salePrice = $product->price - $discountValue;

								if ($salePrice > 0) {
									$product->on_sale = true;
									$product->sale_price = $salePrice;
									$product->discount_id = $discount->id;
									$product->discount_percentage = $discount->discount_value;
								} else {
									$product->sale_price = $product->price;
									$product->on_sale = false;
								}
							} elseif ($discount->discount_value_type == 'fixed') {
								$salePrice = $product->price - $discount->discount_value;

								if ($salePrice > 0) {
									$discountPercentage = $product->price > 0 ? round(($discount->discount_value / $product->price) * 100) : 0;
									$product->on_sale = true;
									$product->sale_price = $salePrice;
									$product->discount_percentage = $discountPercentage;
									$product->discount_id = $discount->id;
								} else {
									$product->sale_price = $product->price;
									$product->on_sale = false;
								}
							}
							$product->save();
						}

						// Apply discount to variations if the product is a variant
						if ($product->product_type == 'variant') {
							$productVariations = ProductVariation::where('product_id', $product->product_id)->get();
							if (isset($discount) && !empty($discount)) {
								foreach ($productVariations as $variation) {
									if ($discount->discount_value_type == 'percent') {
										$discountValue = $variation->price * ($discount->discount_value / 100);
										$salePrice = $variation->price - $discountValue;

										if ($salePrice > 0) {
											$variation->sale_price = $salePrice;
											$variation->discount_percentage = $discount->discount_value;
										} else {
											$variation->sale_price = $variation->price;
										}
									} elseif ($discount->discount_value_type == 'fixed') {
										$salePrice = $variation->price - $discount->discount_value;

										if ($salePrice > 0) {
											$discountPercentage = $variation->price > 0 ? round(($discount->discount_value / $variation->price) * 100) : 0;
											$variation->sale_price = $salePrice;
											$variation->discount_percentage = $discountPercentage;
										} else {
											$variation->sale_price = $variation->price;
										}
									}
									$variation->save();
								}
							}
						}	
						$this->_esearch->esMapping($product->product_id);
					}
				}

				if ($allUpdatesSuccessful && empty($errors)) {
					DB::commit();
					return response()->json([
						'message' => 'Products updated successfully',
						'status' => true
					], 200);
				} else {
					DB::rollBack();
					return response()->json([
						'message' => 'Some updates failed.',
						'status' => false,
						'errors' => $errors
					], 200);
				}
			}
		} catch (\Exception $e) {
			return response()->json(['message' => $e->getMessage(), 'status' => false], 500);
		}
	}

	public function toggleAiFeature($storeId)
    {
        $store = Store::findOrFail($storeId);

        // Toggle the current value
        $store->ai_enabled = !$store->ai_enabled;
        $store->save();

        return response()->json([
            'message' => 'AI feature toggled successfully.',
            'ai_enabled' => $store->ai_enabled,
        ]);
    }
	public function toggleShopifyConnected(Request $request, $id)
	{
		try {
			// Validate input first
			$validated = $request->validate([
				'is_shopify_allowed' => 'required|boolean',
			]);

			// Fetch store
			$store = Store::findOrFail($id);

			// Update and save
			$store->is_shopify_allowed = (bool) $validated['is_shopify_allowed'];
			$store->save();

			return response()->json([
				'status' => true,
				'message' => 'Shopify connection status updated successfully.',
				'is_shopify_allowed' => $store->is_shopify_allowed,
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => false,
				'message' => $e->getMessage(),
			], 500);
		}
	}
	public function toggleWholesale(Request $request, $id)
	{
		try {
			// Validate input first
			$validated = $request->validate([
				'is_wholesale_allowed' => 'required|boolean',
			]);

			// Fetch store
			$store = Store::findOrFail($id);

			// Update and save
			$store->is_wholesale_allowed = (bool) $validated['is_wholesale_allowed'];
			$store->save();

			return response()->json([
				'status' => true,
				'message' => 'Wholesale connection status updated successfully.',
				'is_wholesale_allowed' => $store->is_wholesale_allowed,
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => false,
				'message' => $e->getMessage(),
			], 500);
		}
	}
}
