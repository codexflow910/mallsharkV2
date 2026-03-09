<?php

namespace Packages\Store\Repositories\Eloquent\V3;

use Exception;
use Zendesk;
use Carbon\Carbon;
use Packages\Auth\Models\V3\{User,Userrole};
use Illuminate\Support\Arr;
use Packages\Product\Models\V3\{Product,AttachmentSizes,Attachments};
use Packages\Store\Models\V3\{Store,States,Countries,FavouriteStore};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Packages\Shipping\Models\V3\ShippingOption;
use Packages\Order\Models\V3\StoreOrder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Packages\Core\Contracts\V3\CoreHelpers;
use Illuminate\Support\Facades\Validator;
use Packages\Store\Contracts\V3\StoreHelpers;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use App\Http\Controllers\ElasticSearchController;
use Packages\Store\Contracts\V3\StoreRepositoryInterface;
use App\Http\Controllers\MailgunController;
use Packages\Shipping\Models\V3\{ShippingProfile,FreeShipping};
use Packages\Store\Config\V3\StoreConstants;
use Packages\Product\Config\V3\ProductConstants;

class StoreRepository implements StoreRepositoryInterface
{
	use StoreHelpers;
	use CoreHelpers;
	protected $model;
	protected $store;
	protected $userRole;
	private $_esearch;
	private $mailgun;


	public function __construct(Product $model, Store $store)
	{
		$this->model = $model;
		$this->store = $store;
		$this->userRole = new Userrole();
		$this->_esearch = new ElasticSearchController();
		$this->mailgun = new MailgunController();
	}

	public function store($data)
	{

		$product =  $this->model->fill($data);
		dd('base');
	}

	public function getAttachmentId($file, $type = 'digital', $remove = false, $crop = true)
	{

		if ($file) {

			$arrayItems = explode("--_--", $file);

			if (is_array($arrayItems)) {
				$getItem = $arrayItems[1];
				$fileAttr = pathinfo($getItem);

				$nameItemOnly = $fileAttr['filename'];
				$fileExtension = $fileAttr['extension'];
				$attachments = new Attachments();
				$attachments->fileName = $nameItemOnly;
				$attachments->fileExtension = $fileExtension;
				$attachments->save();
				$attachmentId = $attachments->attachment_id;
				if ($attachmentId) {
					$getRealPath = public_path() . '/uploads/temp/' . $file;
					$fileName = $nameItemOnly . '_' . $attachmentId . '.' . $fileExtension;

					switch ($type) {
						case 'digital':
							$destinationPath = public_path() . '/uploads/digital';
							if (!File::exists($destinationPath)) {
								File::makeDirectory($destinationPath, 0775, true);
							}
							$readFile = file_get_contents($getRealPath);
							file_put_contents($destinationPath . '/' . $fileName, $readFile);

							break;
						case 'image':

							$getDirectorywithSizes = $this->getAllAttachmentSizes();

							foreach ($getDirectorywithSizes as $key => $value) {
								$directoryName = $value['name'];
								$width = $value['width'];
								$height = $value['height'];
								$destinationPath = public_path() . '/uploads/' . $directoryName;
								if ($width == 0 && $height == 0) {
									$readImage = file_get_contents($getRealPath);
									file_put_contents($destinationPath . '/' . $fileName, $readImage);
								} else {
									if ($crop) {

										if ($value['name'] == 'product') {
											$readImage = file_get_contents($getRealPath);

											file_put_contents($destinationPath . '/' . $fileName, $readImage);
										} else {
											$img = Image::make($getRealPath);
											$img->fit($width, $height, function ($constraint) {
												$constraint->upsize();
											});
											$img->save($destinationPath . '/' . $fileName);
										}
									} else {

										$img = Image::make($getRealPath);
										$img->resize($width, null, function ($constraint) {
											$constraint->aspectRatio();
										})->save($destinationPath . '/' . $fileName);
									}
								}
							}
							break;

						default:
							break;
					}
					if ($remove) {
						// unlink($getRealPath);
					}

					return $attachmentId;
				}
			}
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

	public function getStoreProducts($storeId)
	{
		try {
			$products = Product::where('store_id', $storeId)
				->select('title', 'product_id')
				->get();

			if ($products->isEmpty()) {
				throw new ItemNotFoundException('No product found.');
			}
			return $products;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function getTopSellingProducts($storeId, $filter, $startDate, $endDate)
	{
		try {
			$query = StoreOrder::where('store_orders.store_id', $storeId)
				->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
				->join('products', 'order_items.item_id', '=', 'products.product_id')
				->select(
					'products.title',
					'products.product_id',
					DB::raw('SUM(order_items.quantity) as total_quantity'),
					DB::raw('SUM(order_items.price * order_items.quantity) as total_sales')
				)
				->groupBy('order_items.item_id', 'products.title', 'products.product_id')
				->having('total_quantity', '>', 0)
				->orderBy('total_quantity', 'desc')
				->limit(20);

			// Apply the date filter
			switch ($filter) {
				case 'last_month':
					$query->whereBetween('store_orders.created_at', [
						Carbon::now()->subMonth()->startOfMonth(),
						Carbon::now()->subMonth()->endOfMonth()
					]);
					break;
				case 'this_month':
					$query->whereBetween('store_orders.created_at', [
						Carbon::now()->startOfMonth(),
						Carbon::now()
					]);
					break;
				case 'last_7_days':
					$query->whereBetween('store_orders.created_at', [
						Carbon::now()->subDays(6),
						Carbon::now()
					]);
					break;
				case 'custom':
					if (!empty($startDate) && !empty($endDate)) {
						$query->whereBetween('store_orders.created_at', [
							Carbon::parse($startDate),
							Carbon::parse($endDate)
						]);
					} else {
						throw new \Exception("Custom filter requires start and end dates.");
					}
					break;
				default:
					// No additional filter applied
					break;
			}

			$products = $query->get();

			if ($products->isEmpty()) {
				throw new ItemNotFoundException('No product found for your search.');
			}

			return $products;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	public function uploadImage($imagePath)
	{

		$fileName = $this->generateRandomString(15) . '.png';

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
	public function saveStore($data, $id = null, $bannerImage = [])
	{
		
		$userId = Auth::user()->user_id;
		if (empty($userId)) {
			throw new UnauthorizedException('No User Found');
		}
		$this->store->user_id = $userId;
		$this->store->store_name = $data['store_name'];
		$store_slug = str_slug($data['store_name'], '-');
		$this->store->store_slug = $store_slug;
		$this->store->store_logo = $id;
		$storebanners = isset($bannerImage['bannerserialize']) ? $bannerImage['bannerserialize'] : '';
		$this->store->banner_image_id = $storebanners;
		$this->store->save();
		$Userrolecount = $this->userRole->where('user_id', '=', $userId)->where('role_id', '=', 2)->count();
		if ($Userrolecount == 0) {
			$this->userRole->user_id = $userId;
			$this->userRole->role_id = 2;
			$this->userRole->save();
		}
		$this->createGeneralShippingProfile($this->store->store_id, $this->store->street_address, $this->store->store_city, $this->store->store_state, $this->store->zip_code);
		return true;
	}
	public function updateStore($data, $store)
	{
		$userId = Auth::user()->user_id;
		if (empty($userId)) {
			throw new UnauthorizedException('No User Found');
		}

		$store_name  = $data['store_name'];
		$image_id = null;
		$image = null;
		$updatedBannerSerialize = '';
		$totalProductsActivecheck = false;
		$stripeChek = false;
		$profileCheck = false;
	
		if (!$data) {
			try {
				
				if (isset($data['file_upload_banner']) && !empty($data['file_upload_banner'])) {
				$filesname = $data['file_upload_banner'];
				if ($data['file_upload_banner']) {
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
				}}
				if (isset($data['store_logo']) && !empty($data['store_logo'])) {
				$imagePath = $data['store_logo'];
				if ($data['store_logo']) {
					$image = $this->uploadImage($imagePath);
					if (!empty($image)) {
						$image_id = $image['image_id'];
					}
				}}
				$store_slug = str_slug($store_name, '-');
				$store->store_name = isset($store_name) ? $store_name : null;
				$store->store_slug = isset($store_slug) ? $store_slug : null;
				$store->store_logo = isset($image_id) ? $image_id : null;
				$store->user_id = isset($userId) ? $userId : null;
				$storeBanners = isset($updatedBannerSerialize) ? $updatedBannerSerialize : '';
				$store->banner_image_id = $storeBanners;
				$store->save();
				$this->userRole->user_id = $userId;
				$this->userRole->role_id = '2';
				$this->userRole->save();
				$totalProducts = $this->getStoreProductCountstore($store->store_id, "decode");
				$store->store_logo = $this->getAttachmentUrlById((int) $store->store_logo, 'media'); // This function In Core Package
				$store->store_url = url('brand/' . $store->store_slug);
				$storeGallery = [];
				$store_banner_img = $store->banner_image_id;
				if ($store_banner_img && !empty($store_banner_img)) {
					$unserializedLogo = unserialize($store_banner_img);
					foreach ($unserializedLogo as $banner_img_id) {
						$storeGallery[] = $this->getAttachmentUrlById((int) $banner_img_id, 'media'); // This function In Core Package
					}
					$store->banner_image_id = $storeGallery;
				}
				$store->banner_image_id = $storeGallery;
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
				$status = $this->checkStoreActive(); // This function In Core Package
				$store->store_status = $status;
				if (!empty($store)) {
					return $store;
				} else {
					throw new ItemNotFoundException('STORE CREATED FAILED');
				}
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		} else {
			if ($store->user_id !== $userId) {
				throw new UnauthorizedException('You can not Perform that Action');
			}
			try {
				$bannerSerialize = $store->banner_image_id;
				$bannerIds = unserialize($bannerSerialize);
				if (isset($data['file_upload_banner']) && !empty($data['file_upload_banner'])) {
				$filesname = $data['file_upload_banner'];
				if ($data['file_upload_banner']) {

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
							DB::table('stores')->where('store_id', $store->store_id)->update(['banner_image_id' => $updatedBannerSerialize]);
						}
					}
				}}
				if (isset($bannerIds) && !empty($bannerIds)) {
					foreach ($bannerIds as $bannerId) {
						DB::table('attachment')->where('attachment_id', $bannerId)->delete();
						$index = array_search($bannerId, $bannerIds);
						if ($index !== false) {
							unset($bannerIds[$index]);
						}
					}
				}
				if (isset($data['store_logo']) && !empty($data['store_logo'])) {
				$imagePath = $data['store_logo'];
				if ($data['store_logo']) {
					$image = $this->uploadImage($imagePath);
					if (!empty($image)) {
						$image_id = $image['image_id'];
					}
				}}
				$updated = $this->updateStoreName($store_name, $image_id);
				$storeGallery = [];
				$updated->store_logo = $this->getAttachmentUrlById((int) $updated->store_logo, 'media'); // This function In Core Package
				$updated->store_url = url('brand/' . $updated->store_slug);
				if (isset($updated->store_state)) {
					$stateObj = getStatebyId($updated->store_state); // This function In Core Package
					$updated->store_state = $stateObj['state_name'];
				}
				$store_banner_img = $updated->banner_image_id;
				if ($store_banner_img && !empty($store_banner_img)) {
					$unserializedLogo = unserialize($store_banner_img);
					foreach ($unserializedLogo as $banner_img_id) {
						$storeGallery[] = $this->getAttachmentUrlById((int) $banner_img_id, 'media'); // This function In Core Package
					}
				}
				$totalProducts = $this->getStoreProductCountstore($updated->store_id, "decode");
				$updated->banner_image_id = $storeGallery;
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
				$status = $this->checkStoreActive(); // This function In Core Package
				$updated->store_status = $status;
				if (!empty($updated)) {
					return  $updated;
				} else {
					throw new ItemNotFoundException('STORE UPDATED FAILED');
				}
			} catch (\Throwable $th) {
				throw new Exception($th->getMessage());
			}
		}
	}
	
	public function addStoreAddress($data)
	{
		try {
			$user = auth()->user();
			if (empty($user)) {
				throw new UnauthorizedException('No User Found');
			}
			$results =  $this->saveStoreAddress($data);
			if (!empty($results)) {
				$store = Store::where('user_id', $user->user_id)->with('storeShippingOrigins')->first();
				// Attach the origins as a simple array on the returned object for API clients
				$origins = $store->storeShippingOrigins ? $store->storeShippingOrigins->toArray() : [];
				$store->store_logo = $this->getAttachmentUrlById((int) $store->store_logo, 'media');
				$store->store_url = url('brand/' . $store->store_slug);
				$stateObj = $this->getStatebyId($store->store_state);
				$store->store_state = $stateObj['state_name'];

				// Map store state names for shipping origins
				if(!empty($origins)){	
					foreach ($origins as &$origin) {
						$originStateObj = $this->getStatebyId($origin['store_state']);
						$origin['store_state'] = $originStateObj['state_name'] ?? null;
					}
				}
				// Replace the original relationship with the modified array so we only expose `store_shipping_origins`
				$store->setRelation('storeShippingOrigins', collect($origins));
				$store_banner_img = $store->banner_image_id;
				$storeGallery = [];
				if ($store_banner_img && !empty($store_banner_img)) {
					$unserializedLogo = unserialize($store_banner_img);
					foreach ($unserializedLogo as $banner_img_id) {
						$storeGallery[] = $this->getAttachmentUrlById((int) $banner_img_id, 'media');
					}
				}
				$store->banner_image_id = $storeGallery;
				$totalProducts = $this->getStoreProductCountstore($store->store_id, "decode");
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
				$status = $this->checkStoreActive();
				$store->store_status = $status;
				return $store;

			} else {
				throw new ItemNotFoundException('STORE UPDATED FAILED');
			}
		} catch (\Throwable $th) {
			throw new Exception($th->getMessage());
		}
	}
	public function StoreStatusChange($data)
	{
		try {
			$store_status = $data['store_status'];
			$userId = Auth::user()->user_id;
			if (empty($userId)) {
				throw new UnauthorizedException('No User Found');
			}
			$storeData = Store::where('user_id', $userId)->first();
			
			if (isset($storeData)) {
				if ($storeData->is_completed == 1 && $storeData->stripe == 1) {
					$store_id = $storeData->store_id;
					if ($store_status == 2 && $storeData->super_status == 1) {
					$products = Product::where('user_id', $userId)
							->get();
						
						$response = Store::where('store_id', $store_id)
							->update([
								'store_status' => '2'
							]);
						if ($response > 0) {
							$storeData->searchable(); // Index store in Algolia
							if (count($products) > 0) {
								foreach ($products as $pr) {
									$pr->searchable();
								}
							}
							return [
								'status' => true,
								'message' => 'Your store is activated',
							];
						} else {
							return [
								'status' => false,
								'message' => 'You have already activated your store.',
							];
						}
					} elseif ($store_status == 0) {
						$products = Product::where('user_id', $userId)
							->get();

						
						$response = Store::where('store_id', $store_id)
							->update([
								'store_status' => '0',
							]);
						if ($response > 0) {
							$storeData->unsearchable(); // Remove store from Algolia
							if (count($products) > 0) {
								foreach ($products as $pr) {
									$pr->unsearchable();
								}
							}
							return [
								'status' => true,
								'message' => 'your store is deactivated',
							];
						} else {
							return [
								'status' => false,
								'message' => 'You have already deactivated your store.',
							];
						}
					} elseif ($store_status == 0 && $storeData->super_status == 0) {
						return [
							'status' => false,
							'message' => 'Your store is restricted',
						];
					} elseif ($store_status == 2 && $storeData->super_status == 0) {
						return [
							'status' => false,
							'message' => 'Your store is restricted',
						];
					}
				} else {
					return [
						'status' => false,
						'message' => 'Complete you store setup to activate',
					];
				}
			}
			return $results;
		} catch (\Throwable $th) {
			throw new Exception($th->getMessage());
		}
	}
	public function updateAccountSetting($data)
	{
		try {
			$user = auth()->user();
			if (empty($user)) {
				throw new UnauthorizedException('No User Found');
			}
			$user->contact_no = isset($data['contact_no']) && !empty($data['contact_no']) ? $data['contact_no'] : $user->contact_no;
			// Update password if provided
			if (isset($data['new_password']) && !is_null($data['new_password'])) {
				// Validate the old password
				if ($user->password !== md5($data['old_password'])) {
					throw new ItemNotFoundException("Invalid old password");
				}
				// Update password
				$user->password = md5($data['new_password']);
			}
			// Save the changes
			$user->save();
			return $user;
		} catch (\Throwable $th) {
			throw new Exception($th->getMessage());
		}
	}
	public function updateStoreAbout($about,$userId)
	{
		try {
		$store = Store::where('user_id', $userId)->first();
		$store->about_store =$about;
		$store->save();
		
		return $store;
		} catch (\Exception $e) {
         throw $e;
        }
	}
	
	public function getAllCountries()
	{
		try {
			$results = Countries::all()->toArray();
			return $results;
		} catch (Exception $e) {
			throw new ItemNotFoundException('Countries not found');
		}
	}

	public function getStatesByCountryId($countryId)
	{
		try {
			$results = States::where('country_id', $countryId)->get()->toArray();
			return $results;
		} catch (Exception $e) {
			throw new ItemNotFoundException('States not found');
		}
	}

	public function getStoreById($storeId)
	{
		return DB::table('stores')->where('store_id', $storeId)->first();
	}
	public function getStoreBySlug($storeSlug)
	{
		$storeId = DB::table('stores')->where('store_slug', $storeSlug)->select('store_id')->first()->store_id;
		return $storeId;
	}
	public function getStoreUserBySlug($storeSlug)
	{
		$userId = DB::table('stores')->where('store_slug', $storeSlug)->select('user_id')->first()->user_id;
		return $userId;
	}

	public function deleteAttachment($attachmentId)
	{
		return DB::table('attachment')->where('attachment_id', $attachmentId)->delete();
	}

	public function updateBannerImages($storeId, $bannerIds)
	{
		$updatedBannerSerialize = serialize($bannerIds);
		return DB::table('stores')->where('store_id', $storeId)->update(['banner_image_id' => $updatedBannerSerialize]);
	}
	public function contactStore($toEmail, $subject, $message, $toCompletename, $lastname, $toFullname, $support, $reason_text, $orderNumber)
	{
		try {
			$html = view('mail.contactUser', ['message' => $message, 'orderNumber' => $orderNumber, 'toCompletename' => $toCompletename, 'reason_text' => $reason_text, 'support' => $support, 'fullname' => $toFullname]);

			$params = [
				'from'    => 'MallShark  ' . $toEmail,
				'to'      => env('SUPPORT_EMAIL'),
				'subject' => $subject,
				'text'    => $toCompletename,
				'html'    => "$html",
			];
			$results = $this->mailgun->SendMailgunMail($params);
			// user reply
			$subject = 'Customer Support';
			$toEmail = $toEmail;

			$html = view('mail.contact-us', ['message' => $message, 'phone' => $orderNumber, 'toCompletename' => $toCompletename, 'reason_text' => $reason_text, 'support' => $support]);

			$params = [
				'from'    => 'MallShark  ' . env('SUPPORT_EMAIL'),
				'to'      => $toEmail,
				'subject' => $subject,
				'text'    => $toCompletename,
				'html'    => "$html",
			];
			$customFieldOrderNumber = '30884140137613';
			$customFieldSupportType = '30884075534861';

			// Create Zendesk user if not exists
			try {
				$zendeskUser = Zendesk::users()->createOrUpdate([
					'name' => $toCompletename,
					'email' => $toEmail,
					'role' => 'end-user',
				]);

				$zendeskId = $zendeskUser->user->id;

				// Create the Zendesk ticket
				$newTicket = Zendesk::tickets()->create([
					'subject' => 'Support Request from ' . $toFullname,
					'comment' => [
						'body' => $message,
					],
					'priority' => 'high',
					'requester_id' => $zendeskId,
					'type' => 'problem',
					'custom_fields' => [
						['id' => $customFieldOrderNumber, 'value' => $orderNumber ?? ''],
						['id' => $customFieldSupportType, 'value' => $support ?? ''],
					],
				]);
			} catch (\Exception $e) {
				throw new Exception($e->getMessage());
			}
			$results = $this->mailgun->SendMailgunMail($params);
			return true;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function getStoreByUserId($userId)
	{
		return $this->store->where('user_id', $userId)->first();
	}

	public function getAllProducts($storeId)
	{
		return $this->model->with('variations', 'labels')
			->select('product_id', 'slug', 'title', 'store_id', 'price', 'current_stock', 'sales', 'feature_image', 'product_type', 'status', 'is_shopify_import', 'shipping_option_id','unique_product','is_custom')
			->with([
				'orderItems' => function ($query) {
					$query->select('order_item_id', 'order_id', 'store_id', 'item_id')
						->with('storeProductOrder', function ($orderQuery) {
							$orderQuery->select('order_id', 'order_status', 'total_items')
								->where('order_status', 'pending');
						});
				}
			])
			->where('store_id', $storeId)
			->where('status', '!=', '2')
			->orderBy('created_at', 'desc')
			->orderBy('updated_at', 'desc')
			->paginate(10);
	}

	public function getAllBrands($brand = null)
	{
        return Store::where('store_name', 'LIKE', '%' . $brand . '%')->where('store_status', '2')->select('store_name', 'store_slug', 'store_logo', 'store_id', 'store_city', 'street_address', 'store_country', 'store_state', 'contact_email', 'contact_info', 'banner_image_id', 'wallet_address', 'stripe', 'user_id')->paginate(10);
    }
	public function storeInfo($storeId)
	{
		$indexName = env('PRODUCT_INDEX');
		$query =null; 
		$store = $storeId;




		$searchQuery = Product::search($query)->within($indexName);
		$filters = [];
		if (isset($store) && $store !== '') { // Checks if $onSale is set and not an empty string
			$filters[] = "storeId = $store";
		}
		$filtersString = !empty($filters) ? implode(' AND ', $filters) : '';
		$options = [
			// 'page' => $page - 1, // Algolia pages are 0-based, Laravel uses 1-based
			'hitsPerPage' => 1000,
			'clickAnalytics' => true
		];
		if (!empty($filtersString)) {
			$options['filters'] = $filtersString;
		}
		$searchQuery->options($options);
		$response = $searchQuery->raw();

		return $response;
	}
	public function storeHome($storeId, $data, $indexName)
	{
		try {
			$query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
			$category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
			$minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
			$maxPrice =  isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
			$perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
			$onSale =  isset($data['on_sale']) && $data['on_sale'] !== null ? $data['on_sale'] : null;
			$mallsharkselected =  isset($data['mallshark_selected']) && $data['mallshark_selected'] !== null ? $data['mallshark_selected'] : null;
			$shipOptionId =  isset($data['shipping_option_id']) && $data['shipping_option_id'] !== null ? $data['shipping_option_id'] : null;
			$page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1;
			$sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name';
			$sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc';
			$searchQuery = Product::search($query)->within($indexName);
			$store = $storeId;
			// Apply category filter if provided
			$filters = [];

			if (!empty($category)) {
				$filters[] = "(categories:\"$category\" OR categories.0:\"$category\" OR categories.2:\"$category\")";
			}
			// Apply price range filter if provided
			if (!empty($minPrice) && !empty($maxPrice)) {
				$filters[] = "effective_price >= $minPrice AND effective_price <= $maxPrice";
			}
			if (isset($onSale) && $onSale !== '') { // Checks if $onSale is set and not an empty string
				$filters[] = "on_sale = $onSale";
			}
			if (isset($mallsharkselected) && $mallsharkselected !== '') { // Checks if $onSale is set and not an empty string
				$filters[] = "mallshark_selected = $mallsharkselected";
			}
			if (isset($shipOptionId) && $shipOptionId !== '') { // Checks if $onSale is set and not an empty string
				if ($shipOptionId == 1) {
					$filters[] = "shipping_option_id = 221";    // Free Shipping
				} else {
					$filters[] = "shipping_option_id !=221 ";
				}
			}
			if (isset($store) && $store !== '') { // Checks if $onSale is set and not an empty string
				$filters[] = "storeId = $store";
			}
			// Combine all filters using AND
			$filtersString = !empty($filters) ? implode(' AND ', $filters) : '';
			if (!is_null($sortBy)) {
				$sorting = $sortBy . ':' . $sortOrder;
				$searchQuery = $searchQuery->orderBy($sorting);
			}
			$options = [
                'page' => $page - 1, // Algolia pages are 0-based, Laravel uses 1-based
                'hitsPerPage' => 50000,
                'clickAnalytics' => true
            ];
            if (!empty($filtersString)) {
                $options['filters'] = $filtersString;
            }
            $searchQuery->options($options);
			$response = $searchQuery->raw();

			return $response;
		} catch (\Exception $e) {
			throw $e;
		}
	}
public function storeHomeV3($storeId, $data, $indexName)
	{
		try {
			$query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
			$category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
			$minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
			$maxPrice =  isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
			$perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
			$onSale =  isset($data['on_sale']) && $data['on_sale'] !== null ? $data['on_sale'] : null;
			$mallsharkselected =  isset($data['mallshark_selected']) && $data['mallshark_selected'] !== null ? $data['mallshark_selected'] : null;
			$shipOptionId =  isset($data['shipping_option_id']) && $data['shipping_option_id'] !== null ? $data['shipping_option_id'] : null;
			$page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1;
			$sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name';
			$sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc';
			$searchQuery = Product::search($query)->within($indexName);
			$store = $storeId;
            $brandId = isset($data['brand_id']) && $data['brand_id'] !== null ? $data['brand_id'] : null;
			// Apply category filter if provided
			$filters = [];

			if (!empty($category)) {
				$filters[] = "(categories:\"$category\" OR categories.0:\"$category\" OR categories.2:\"$category\")";
			}
			// Apply price range filter if provided
			if (!empty($minPrice) && !empty($maxPrice)) {
				$filters[] = "effective_price >= $minPrice AND effective_price <= $maxPrice";
			}
			if (isset($onSale) && $onSale !== '') { // Checks if $onSale is set and not an empty string
				$filters[] = "on_sale = $onSale";
			}
			if (isset($mallsharkselected) && $mallsharkselected !== '') { // Checks if $onSale is set and not an empty string
				$filters[] = "mallshark_selected = $mallsharkselected";
			}
			if (isset($shipOptionId) && $shipOptionId !== '') { // Checks if $onSale is set and not an empty string
				if ($shipOptionId == 1) {
					$filters[] = "shipping_option_id = 221";    // Free Shipping
				} else {
					$filters[] = "shipping_option_id !=221 ";
				}
			}
			if (isset($store) && $store !== '') { // Checks if $onSale is set and not an empty string
				$filters[] = "storeId = $store";
			}
			if (!empty($brandId)) {
                $filters[] = "brand_id = $brandId";
            }
			// Combine all filters using AND
			$filtersString = !empty($filters) ? implode(' AND ', $filters) : '';
			if (!is_null($sortBy)) {
				$sorting = $sortBy . ':' . $sortOrder;
				$searchQuery = $searchQuery->orderBy($sorting);
			}
			$options = [
                'page' => $page - 1, // Algolia pages are 0-based, Laravel uses 1-based
                'hitsPerPage' => 50000,
                'clickAnalytics' => true
            ];
            if (!empty($filtersString)) {
                $options['filters'] = $filtersString;
            }
            $searchQuery->options($options);
			$response = $searchQuery->raw();

			return $response;
		} catch (\Exception $e) {
			throw $e;
		}
	}
	public function followStore($userId, $storeId)
	{
		try {
			$existing = FavouriteStore::where('user_id', $userId)
						->where('store_id', $storeId)
						->first();

			if ($existing) {
				$existing->delete();
				return false; // unfollowed
			}

			FavouriteStore::create([
				'user_id'  => $userId,
				'store_id' => $storeId
			]);

			return true; // followed
		} catch (\Exception $e) {
			throw $e;
		}
	}
	public function createGeneralShippingProfile($storeId, $streetAddress, $city, $state, $zipCode)
	{
		try {
 				$profile = ShippingProfile::create([
                    'store_id' => $storeId,
                    'name' => 'General Shipping',
                    'address' => $streetAddress,
                    'city' => $city,
                    'state_id' => $state,
                    'zip_code' => $zipCode,
                    'is_general' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                FreeShipping::create([
                    'profile_type' => 'general',
                    'type_id' => $profile->id,
                    'free_shipping_time' => '1-2 Days', // Default free shipping time
                ]);
			return true;
		} catch (\Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	public function unlockNextDay($sellerId, $currentDay)
    {
        try {
            $store = Store::where('user_id', $sellerId)->first();

            if (!$store) {
                throw new ItemNotFoundException('Store not found for seller');
            }

            if ($currentDay > StoreConstants::MISSION_DAY) {
                return [
                    'message' => 'All 14 days already unlocked',
                    'mission_day' => StoreConstants::MISSION_DAY,
					'sale_mission' => true,
					'mission_complete_date' => $store->mission_complete_date,
                ];
            }

            $newDay = $currentDay;
			$saleMission = (bool) ($store->sale_mission) || $newDay === StoreConstants::MISSION_DAY;

			if ($newDay === StoreConstants::MISSION_DAY && empty($store->mission_complete_date)) {
				$store->mission_complete_date = Carbon::now();
			}

            $store->mission_day = $newDay;
            $store->sale_mission = $saleMission;
            $store->save();

            return [
                'message' => 'Day unlocked successfully',
                'mission_day' => $newDay,
                'sale_mission' => $saleMission,
				'mission_complete_date' => $store->mission_complete_date,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteStore($store)
    {
        try {
            DB::beginTransaction();

            $storeId = $store->store_id;

            // Safety check: ensure no incomplete orders exist before proceeding
            $incompleteOrders = StoreOrder::where('store_id', $storeId)
                ->where('charge_status', 'paid')
                ->whereIn('order_status', ['pending', 'shipped', 'partial_shipped', 'disputed'])
                ->count();

            if ($incompleteOrders > 0) {
                throw new Exception('Cannot delete store with incomplete orders.');
            }

            // Get product IDs that have orders (must be preserved)
            $productIdsWithOrders = DB::table('order_items')
                ->where('store_id', $storeId)
                ->pluck('item_id')
                ->unique()
                ->toArray();

            // Get product IDs that have NO orders (safe to delete)
            $productIdsToDelete = DB::table('products')
                ->where('store_id', $storeId)
                ->whereNotIn('product_id', $productIdsWithOrders)
                ->pluck('product_id')
                ->toArray();

            // Delete product-related data in chunks for products with no orders
            if (!empty($productIdsToDelete)) {
                foreach (array_chunk($productIdsToDelete, 500) as $chunk) {
                    DB::table('product_variation')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_category')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_faqs')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_tags')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_ai_tags')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_specifications')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_shipping')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_video')->whereIn('product_id', $chunk)->delete();
                    DB::table('label_products')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_tiers')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_customizations')->whereIn('product_id', $chunk)->delete();
                    DB::table('custom_text_styles')->whereIn('product_id', $chunk)->delete();
                    DB::table('custom_image_settings')->whereIn('product_id', $chunk)->delete();
                    DB::table('custom_text_characters')->whereIn('product_id', $chunk)->delete();
                    DB::table('prduct_digital_files')->whereIn('product_id', $chunk)->delete();
                    DB::table('product_causes')->whereIn('product_id', $chunk)->delete();
                    DB::table('products_cart')->whereIn('product_id', $chunk)->delete();
                    DB::table('favourites')->whereIn('product_id', $chunk)->delete();
                    DB::table('reviews')->whereIn('product_id', $chunk)->delete();
                    DB::table('superadmin_reviews')->whereIn('product_id', $chunk)->delete();

                    // Delete the products themselves
                    DB::table('products')->whereIn('product_id', $chunk)->delete();
                }
            }

            // Soft delete products that have orders (preserve data but mark as deleted)
            if (!empty($productIdsWithOrders)) {
                DB::table('products')
                    ->whereIn('product_id', $productIdsWithOrders)
                    ->whereNull('deleted_at')
                    ->update([
                        'status' => ProductConstants::STATUS_DELETED,
                        'deleted_at' => now(),
                    ]);
            }

            // Delete AI usage tokens for this store
            DB::table('ai_usage_tokens')->where('store_id', $storeId)->delete();

            // Anonymize store name and soft delete
            $store->store_name = 'deleted_store_' . $storeId . '_' . time();
            $store->save();
            $store->delete(); // SoftDeletes trait sets deleted_at

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
