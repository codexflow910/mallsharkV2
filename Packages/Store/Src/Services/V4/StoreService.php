<?php

namespace Packages\Store\Services\V4;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Packages\Store\Contracts\V4\StoreHelpers;
use Packages\Core\Contracts\V4\CoreHelpers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ItemNotFoundException;
use App\Http\Controllers\API\ElasticSearchController;
use App\Repositories\search\Searchaccess;
use Illuminate\Validation\UnauthorizedException;
use Packages\Store\Contracts\V4\BaseApiResponse;
use Packages\Store\Repositories\Eloquent\V4\StoreRepository;

class StoreService
{
	use StoreHelpers;
	use CoreHelpers;
	use BaseApiResponse;

	protected $storeRepository;
	private $_esearch;
	private $_esObjAccess;

	public function __construct(StoreRepository $storeRepository)
	{
		$this->storeRepository = $storeRepository;
		$this->_esearch = new ElasticSearchController();
		$this->_esObjAccess = new Searchaccess();
	}

	public function getStore()
	{
		$store = auth()->user()->store;
		if (!$store) {
			throw new ItemNotFoundException('Store not found');
		}
		return $store;
	}

	public function getStoreProducts(Request $request)
	{
		try {

			$type = $request->input('type', 'top');
			$filter = $request->input('filter');
			$startDate = $request->input('start_date');
			$endDate = $request->input('end_date');

			$products = $this->getFilteredProducts($type, $filter, $startDate, $endDate);

			return  $products;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function getFilteredProducts($type = 'top', $filter, $startDate, $endDate)
	{
		try {

			$storeId = $this->getStore()->store_id;

			if ($type === 'all') {
				return $this->storeRepository->getStoreProducts($storeId);
			}

			return $this->storeRepository->getTopSellingProducts($storeId, $filter, $startDate, $endDate);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	public function addStore($data)
	{
		try {
			$userId = Auth::user()->user_id;

			$existingStore = $this->getStoreByname($userId);
			// dd($data);
			$image_id = null;
			$image = null;
			$imagePath = null;
			$imagePathBanner = null;
			if (isset($data['store_logo']) && !empty($data['store_logo'])) {
				$imagePath = $data['store_logo'];
			}

			if (isset($data['file_upload_banner']) && !empty($data['file_upload_banner'])) {
				$imagePathBanner = $data['file_upload_banner'];
			}


			if (!empty($imagePath) && isset($imagePath)) {

				$image = $this->storeRepository->uploadImage($imagePath);

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
				throw new Exception("Store Already Created");
			} else {
				$results = $this->storeRepository->saveStore($data, $image_id, $banner_image);
				if (!empty($results)) {
					return [
						'store_image' => !empty($image) ? ($image['filename'] ?? null) : null,
						'store_image_id' => $image_id,
						'store_banner_image' => !empty($banner_image) && isset($banner_image['fileName']) ? $banner_image['fileName'] : null,
						'store_banner_id' => !empty($banner_image) && isset($banner_image['id']) ? $banner_image['id'] : null,
					];
				} else {
					throw new ItemNotFoundException('Company profile has not been created');
				}
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
	public function editStore()
	{
		try {
			$user = auth()->user()->toArray();
			$userId = $user['user_id'];

			$storeData = $this->getStoreDataByUserId($userId);
			$storeData = $this->objToArray($storeData);
		
			$totalProducts = $this->getStoreProductsCount($storeData['store_id'], "decode");

			$store_logo_url = $this->getAttachmentUrlById($storeData['store_logo']); // This function In Core Package
			$storeData['store_logo_url'] = $store_logo_url;
			$storeData['store_url'] = url('brand/' . $storeData['store_slug']);
			$storeGallery = []; // Ensure it's initialized
			$bannerImageData = $storeData['banner_image_id'];
			$bannerImageIds = @unserialize($bannerImageData);

			if ($bannerImageIds && is_array($bannerImageIds)) {
				foreach ($bannerImageIds as $bannerId) {
					
					$url = $this->getAttachmentUrlById($bannerId); // Core package function
					$filename = basename($url);

					$storeGallery[] = [
						'banner_img_id' => (int) $bannerId,
						'url' => $url,
						'filename' => $filename
					];
				}
			}

			$storeData['banner_image_urls'] = $storeGallery;

			$stripeChek = false;
			$profileCheck = false;

			if (isset($storeData) && $storeData['stripe'] == '1') {
				$stripeChek = true;
			}
			if (isset($storeData['is_completed']) && ($storeData['is_completed'] != 0)) {
				$profileCheck = true;
			}

			$storeData['stripeCheck'] = $stripeChek;
			$storeData['profileCheck'] = $profileCheck;
            $profileCheck = false;
 			$totalProductsActivecheck = false;
			if ($totalProducts >= 1) {
			$totalProductsActivecheck = true;
			}
            if (isset($storeData) && $storeData['stripe'] == '1') {
                $stripeChek = true;
            }
            if (isset($storeData['is_completed']) && ($storeData['is_completed'] != 0)) {
                $profileCheck = true;
            }
			$storeData['totalProductsActivecheck'] = $totalProductsActivecheck;
            $storeData['stripeCheck'] = $stripeChek;
            $storeData['profileCheck'] = $profileCheck;

			$storeData['user'] = $user;

			if (!empty($storeData)) {
				return $storeData;
			} else {
				throw new ItemNotFoundException('Store Not found');
			}
			if ($storeData['user_id'] !== $userId) {
				throw new AuthorizationException('Unauthorized to edit this store');
			}
		} catch (\Throwable $th) {
			throw new ItemNotFoundException('Company profile has not been created');
		}
	}
	public function updateStore($data)
	{
		$userId = Auth::user()->user_id;
		$store = $this->getStorDataUserId($userId);
		$store_name  = $data['store_name'];
		$image_id = null;
		$image = null;
		$updatedBannerSerialize = '';
		$totalProductsActivecheck = false;
		$stripeChek = false;
		$profileCheck = false;
		$results = $this->storeRepository->updateStore($data, $store);
		return $results;
	}
	public function addStoreAddress($data)
	{
		$results = $this->storeRepository->addStoreAddress($data);
		return $results;
	}
	public function StoreStatusChange($data)
	{
		$results = $this->storeRepository->StoreStatusChange($data);
		return $results;
	}
	public function updateAccountSetting($data)
	{
		try {
			$results = $this->storeRepository->updateAccountSetting($data);
			if ($results) {
				return  $results;
			} else {
				throw new ItemNotFoundException("Failed to Update");
			}
		} catch (ItemNotFoundException $e) {
			return $this->notFoundResponse($e->getMessage());
		}
	}
	public function getAllCountries()
	{
		$results = $this->storeRepository->getAllCountries();
		return $results;
	}

	public function getStatesByCountryId($countryId)
	{
		$results = $this->storeRepository->getStatesByCountryId($countryId);
		return $results;
	}

	/**
	 * Delete Store Banner
	 */
	public function deleteBanner(array $data)
	{
		$storeData = $this->storeRepository->getStoreById($data['store_id']);

		if (!$storeData) {
			throw new ItemNotFoundException('Store not found.');
		}

		$bannerIds = unserialize($storeData->banner_image_id);
		$fileName = $this->extractMediaSubPath($data['filename']);

		if (!$bannerIds || empty($bannerIds)) {
			throw new ItemNotFoundException('No banners found for this store.');
		}

		$destinationPath = public_path('uploads/media/' . $fileName);
		$destinationPath = realpath($destinationPath); // Normalize path

		if ($destinationPath && File::exists($destinationPath)) {
			unlink($destinationPath);
			$this->storeRepository->deleteAttachment($data['image_id']);
		}

		// Ensure proper ID type comparison
		if (($index = array_search((int) $data['image_id'], $bannerIds, true)) !== false) {
			unset($bannerIds[$index]);
		}

		$this->storeRepository->updateBannerImages($data['store_id'], $bannerIds);

		return count($bannerIds) < count(unserialize($storeData->banner_image_id));
	}

	/**
	 * Extracts the media subpath from the given URL.
	 */
	private function extractMediaSubPath($fileName)
	{
		$parts = parse_url($fileName);
		$path = $parts['path'];
		$mediaPosition = strpos($path, '/media/');

		return $mediaPosition !== false ? substr($path, $mediaPosition + strlen('/media/')) : $fileName;
	}
	public function contactStore($data)
	{
		try {
			$toEmail = $data['email'];
			$subject = 'Customer Support Query';
			$message =  $data['message'];
			$toCompletename =  $data['first_name'];
			$lastname = isset($data['last_name']) ? $data['last_name'] : '';
			$toFullname = trim($toCompletename . ' ' . $lastname);
			$support = isset($data['support']) && !is_null($data['support']) ? ucfirst($data['support']) : '';
			$reason_text = isset($data['reason']) && !is_null($data['reason']) ? ucfirst($data['reason']) : '';
			$orderNumber = isset($data['order_number']) ? $data['order_number'] : '';
			$results = $this->storeRepository->contactStore($toEmail, $subject, $message, $toCompletename, $lastname, $toFullname, $support, $reason_text, $orderNumber);
			if ($results) {
				return  $results;
			} else {
				throw new ItemNotFoundException("Failed to Update");
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * Get User's Store products.
	 */
	public function getAllProducts()
	{
		try {
			$userId = auth()->user()->user_id;
			
			$store = StoreHelpers::getAccessibleStore($userId);

			if (!$store) {
				throw new ItemNotFoundException('User does not belong or have access to any store.');
			}

			$products = $this->storeRepository->getAllProducts($store->store_id);
			$formattedProducts = $this->formatProducts($products);

			if (empty($formattedProducts)) {
				throw new ItemNotFoundException("You have no product.");
			}

			return [
				'products' => $formattedProducts,
				'pagination' => $this->paginateData($products),
			];
		} catch (ItemNotFoundException $e) {
			throw new ItemNotFoundException($e->getMessage());
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * Format the products data
	 */
	public function formatProducts($products)
	{
		$formattedProducts = [];

		foreach ($products->items() as $product) {
			$product->status = $this->getProductStatus($product);
			$product->feature_image = $this->getAttachmentUrlSmallById(intval($product->feature_image), 'small');
			$product->current_stock = $this->calculateStock($product);

			foreach ($product->variations as $variation) {
				$variation->product_variation_image = $this->getVariationImage($variation, $product->feature_image);
			}

			$formattedProducts[] = [
				'product_id' => $product->product_id,
				'slug' => $product->slug,
				'title' => $product->title,
				'store_id' => $product->store_id,
				'price' => $product->price,
				'current_stock' => $product->current_stock,
				'sales' => $product->sales,
				'feature_image' => $product->feature_image,
				'product_type' => $product->product_type,
				'status' => $product->status,
				'is_shopify_import' => $product->is_shopify_import,
				'is_custom' => $product->is_custom,
				'shipping_option_id' => $product->shipping_option_id,
				'variations' => $product->variations,
				'order_items' => $product->orderItems,
				'unique_product' => $product->unique_product,
			];
		}

		return $formattedProducts;
	}

	/**
	 * Determine product status
	 */
	public function getProductStatus($product)
	{
		// if ($product->product_type != 'variant') {
		if ($product->status == 1 && $product->current_stock > 0) {
			return 'active';
		} elseif ($product->status == 0) {
			return 'deactive';
		} elseif ($product->status == 3) {
			return 'pending';
		} elseif ($product->status == 5) {
			return 'draft';
		} elseif ($product->status == 4) {
			return 'rejected';
		}
		// }

		return ($product->status == 1) ? 'active' : (($product->status == 3) ? 'pending' : (($product->status == 4) ? 'rejected' : ''));
	}

	/**
	 * Calculate stock for variant products
	 */
	public function calculateStock($product)
	{
		if($product->unique_product == true){
			return $product->current_stock;
		}
		if ($product->product_type == 'variant' && !empty($product->variations)) {
			$totalStock = array_sum(array_column($product->variations->toArray(), 'current_stock'));
			return $totalStock > 0 ? $totalStock : 0;
		}

		return $product->current_stock;
	}

	/**
	 * Paginate response data
	 */
	public function paginateData($products)
	{
		return [
			'current_page' => $products->currentPage(),
			'first_page_url' => $products->url(1),
			'from' => $products->firstItem(),
			'last_page' => $products->lastPage(),
			'last_page_url' => $products->url($products->lastPage()),
			'next_page_url' => $products->nextPageUrl(),
			'path' => $products->path(),
			'per_page' => $products->perPage(),
			'prev_page_url' => $products->previousPageUrl(),
			'to' => $products->lastItem(),
			'total' => $products->total(),
		];
	}

	public function getVariationImage($variation, $featureImage)
	{
		if (!empty($variation->product_variation_image)) {
			$imageId = intval($variation->product_variation_image);
			return getAttachmentUrlSmallById($imageId, 'small');
		}

		// Set variation image to the feature image of the main product if not provided
		return $featureImage;
	}

	public function getAllBrands($brand = null)
	{
		try {
			$data = $this->storeRepository->getAllBrands($brand);
			if (empty($data)) {
				throw new ItemNotFoundException("Brands not found");
			}
			$data->getCollection()->transform(function ($store) {
				$storeArray = $store->toArray();
				$storeArray['store_thumbnail'] = $this->getAttachmentUrlById(
					(int) $storeArray['store_logo'], 
					'media'
				);

				// New banner image processing
				$bannerImageData = $storeArray['banner_image_id'] ?? '';
				if (!empty($bannerImageData)) {
					$bannerImageIds = unserialize($bannerImageData);
					$imageUrls = [];
					foreach ($bannerImageIds as $bannerId) {
						$imageUrl = getAttachmentUrlByIdMedia((int) $bannerId, 'media');
						$mediaUrl = asset('uploads/media/' . basename($imageUrl));
						$imageUrls[] = $mediaUrl;
					}
					$storeArray['banner_image_urls'] = $imageUrls;
				}
				return $storeArray;
			});
	
			return $data;
			
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function getStoreAbout()
	{
		try {

			$userId = auth()->user()->user_id;
			if (!$userId) {
				throw new UnauthorizedException('No User found');
			}
			$store = $this->getStoreDataByUserId($userId);

			if (isset($store) && !empty($store)) {
				return $store;
			} else {
				throw new ItemNotFoundException("Store Data Not Avaliable");
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}
	public function updateStoreAbout($about)
	{
		try {
			$userId = auth()->user()->user_id;
			if (!$userId) {
				throw new UnauthorizedException('No User found');
			}
			$store = $this->storeRepository->updateStoreAbout($about, $userId);
			if (isset($store) && !empty($store)) {
				return $store;
			} else {
				throw new ItemNotFoundException("Store Data Not Avaliable");
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}
	public function storeHome($slug, $data)
	{
		try {
			$perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
			$page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
			$sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name'; // Default sort by name
			$sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc'; // Default order ascending

			$indexName = env('PRODUCT_INDEX'); // Default primary index
			if (!is_null($sortBy)) {
				if ($sortBy === 'price' && $sortOrder === 'asc') {
					$indexName = env('PRODUCTS_PRICE_ASC');
				} elseif ($sortBy === 'price' && $sortOrder === 'desc') {
					$indexName = env('PRODUCTS_PRICE_DESC');
				} elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
					$indexName = env('PRODUCT_CREATED_AT_ASC');
				} elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
					$indexName = env('PRODUCT_CREATED_AT_DESC');
				}
			}
			$storeId = $this->storeRepository->getStoreBySlug($slug);
			$userId = $this->storeRepository->getStoreUserBySlug($slug);
			$storeData = $this->getStoreDataByUserId($userId);
			$allCategories = [];
			$storeData = $this->objToArray($storeData);
			$store_logo_url = $this->getAttachmentUrlById($storeData['store_logo']); // This function In Core Package
			$storeData['store_logo_url'] = $store_logo_url;
			$storeGallery = []; // Ensure it's initialized
			$bannerImageData = $storeData['banner_image_id'];
			$bannerImageIds = @unserialize($bannerImageData);

			if ($bannerImageIds && is_array($bannerImageIds)) {
				foreach ($bannerImageIds as $bannerId) {
					$url = $this->getAttachmentUrlById($bannerId);

					$filename = basename($url);

					$storeGallery[] = [
						'banner_img_id' => (int) $bannerId,
						'url' => $url,
						'filename' => $filename
					];
				}
			}

			$storeData['banner_image_urls'] = $storeGallery;
			$results = $this->storeRepository->storeHome($storeId, $data, $indexName);

			$products = collect($results['hits']);
			$productIds = $products->pluck('id')->toArray();
			// Fetch categories and tags from the database for these products
			$categories = DB::table('categories')
				->join('product_category', 'categories.id', '=', 'product_category.category_id')
				->whereIn('product_category.product_id', $productIds)
				->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID','categories.image_id', 'product_category.product_id')
				->get()
				->groupBy('product_id');



			$modifiedProducts = $products->map(function ($product, $index) use ($categories, $results) {
				// Get categories from the grouped data
				$categoryList = $categories[$product['id']] ?? collect();
				$categoryArray = $categoryList->toArray();
				$productId = $product['id'];

				$categoriesData = [];
				$parentCategories = [];
				$childCategories = [];

				// Step 1: Separate parent and child categories
				foreach ($categoryList as $category) {
					if ($category->parentCategoryID == 0) {
						
						$imageUrl = CoreHelpers::getAttachmentUrlById((int)$category->image_id, 'media');
						$parentCategories[$category->id] = [
							'parent' => [
								'id' => $category->id,
								'categoryTitle' => $category->categoryTitle,
								'category_slug' => $category->category_slug,
								'parentCategoryID' => $category->parentCategoryID,
								'category_image' => $imageUrl ? $imageUrl : null,
							],
							'childCategories' => []
						];
					} else {
						$childCategories[] = $category;
					}
				}

				// Step 2: Attach children to the correct parents
				foreach ($childCategories as $category) {
					$parentId = $category->parentCategoryID;

					if (isset($parentCategories[$parentId])) {
						$parentCategories[$parentId]['childCategories'][] = [
							'id' => $category->id,
							'categoryTitle' => $category->categoryTitle,
							'category_slug' => $category->category_slug,
							'parentCategoryID' => $category->parentCategoryID,
						];
					} else {
						// Optional: handle orphan child categories with missing parent
						// For example, add them as top-level if desired
					}
				}

				$categoriesData = array_values($parentCategories); // Reset keys for clean array
				$allCategories[] = $categoriesData;
				$reviewsProduct = $this->get_reviews($product['id']);

				$reviewArray = [];
				foreach ($reviewsProduct as $review) {
					$reviews = (array) $review;
					$user_id = $review['user_id'];
					$userInfo = $this->getUserById($user_id);
					$userName = $userInfo->name;
					$reviews['user_name'] = $userName;
					$reviewArray[] = $reviews;
				}
				$average = 0;
				$points = 0;
				if (isset($reviewsProduct) && count($reviewsProduct) > 0) {

					$numer = 0;
					$star1_count = 0;
					$star2_count = 0;
					$star3_count = 0;
					$star4_count = 0;
					$star5_count = 0;
					foreach ($reviewsProduct as $rev) {
						$numer += $rev['rating'];



						if ($rev['rating'] == 1) {
							$star1_count = $star1_count + 1;
						}
						if ($rev['rating'] == 2) {
							$star2_count = $star2_count + 1;
						}
						if ($rev['rating'] == 3) {
							$star3_count = $star3_count + 1;
						}
						if ($rev['rating'] == 4) {
							$star4_count = $star4_count + 1;
						}
						if ($rev['rating'] == 5) {

							$star5_count = $star5_count + 1;
						}

						$total = count($reviews);
						$star1_count_percentage = round(($star1_count / $total) * 100);
						$star2_count_percentage = round(($star2_count / $total) * 100);
						$star3_count_percentage = round(($star3_count / $total) * 100);
						$star4_count_percentage = round(($star4_count / $total) * 100);
						$star5_count_percentage = round(($star5_count / $total) * 100);
					}
					$singleReview = [
						'star1_count' => $star1_count,
						'star2_count' => $star2_count,
						'star3_count' => $star3_count,
						'star4_count' => $star4_count,
						'star5_count' => $star5_count,
					];
					$avergeSingleReview = [
						'star1_count_avg' => $star1_count_percentage,
						'star2_count_avg' => $star2_count_percentage,
						'star3_count_avg' => $star3_count_percentage,
						'star4_count_avg' => $star4_count_percentage,
						'star5_count_avg' => $star5_count_percentage,
					];


					$average = round($numer / count($reviewsProduct));
					$points = number_format($numer / count($reviewsProduct), 1);

					$average = isset($average) ? $average : "";
				} else {
					$singleReview = [];
					$avergeSingleReview = [];
				}
				$reviewAverage = $average;
				$totalReviews = count($reviewsProduct);

				return [
					'product_id' => $product['id'],
					'objectID' => $product['objectID'] ?? null,
					'title' => $product['name'],
					'store_id' => $product['storeId'],
					'slug' => $product['slug'],
					'price' => $product['price'],
					'description' => $product['description'],
					'current_stock' => $product['current_stock'],
					'status' => $product['status'],
					'is_custom' => $product['is_custom'] ?? 0,
					'reviewAverageStar' => $reviewAverage,
					'reviewAveragepoints' => $points,
					'totalReviews' => $totalReviews,
					'product_type' => $product['product_type'],
					'on_sale' => $product['on_sale'],
					'sale_price' => $product['sale_price'],
					'discount_percentage' => $product['discount_percentage'],
					'feature_image' => $product['feature_image'] ?? null,
					'feature_image_alt' => $product['feature_image_alt'] ?? null,
					'shipping_option_id' => $product['shipping_option_id'],
					'categories' => array_values($categoriesData), // Attach categories
					'store_id' => $product['storeId'],
					'created_at' => $product['created_at'],
					'query_id' => $results['queryID'],
					'mallshark_selected' => $product['mallshark_selected'] ?? 0,
					'labels' => $product['labels'] ?? null


				];
			});



			// Replace the original product collection with the modified one
			$products = $modifiedProducts;


			if (isset($products) && !empty($products)) {
				return [
					'Product' => $products,
				];
			} else {
				throw new ItemNotFoundException("Store Data Not Avaliable");
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}
	//version 3 function for website
	public function storeHomeV3($slug,$data)
	{
		try {
			$perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
			$page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
			$sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name'; // Default sort by name
			$sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc'; // Default order ascending

			$indexName = env('PRODUCT_INDEX'); // Default primary index
			if (!is_null($sortBy)) {
				if ($sortBy === 'price' && $sortOrder === 'asc') {
					$indexName = env('PRODUCTS_PRICE_ASC');
				} elseif ($sortBy === 'price' && $sortOrder === 'desc') {
					$indexName = env('PRODUCTS_PRICE_DESC');
				} elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
					$indexName = env('PRODUCT_CREATED_AT_ASC');
				} elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
					$indexName = env('PRODUCT_CREATED_AT_DESC');
				}
			}
			$storeId = $this->storeRepository->getStoreBySlug($slug);
			$userId = $this->storeRepository->getStoreUserBySlug($slug);
			$storeData = $this->getStoreDataByUserId($userId);
			$allCategories = [];
			$storeData = $this->objToArray($storeData);
			$store_logo_url = $this->getAttachmentUrlById($storeData['store_logo']); // This function In Core Package
			$storeData['store_logo_url'] = $store_logo_url;
			$storeGallery = []; // Ensure it's initialized
			$bannerImageData = $storeData['banner_image_id'];
			$bannerImageIds = @unserialize($bannerImageData);

			if ($bannerImageIds && is_array($bannerImageIds)) {
				foreach ($bannerImageIds as $bannerId) {
					$url = $this->getAttachmentUrlById($bannerId);

					$filename = basename($url);

					$storeGallery[] = [
						'banner_img_id' => (int) $bannerId,
						'url' => $url,
						'filename' => $filename
					];
				}
			}

			$storeData['banner_image_urls'] = $storeGallery;
			$results = $this->storeRepository->storeHomeV3($storeId,$data, $indexName);

			$products = collect($results['hits']);
			$productIds = $products->pluck('id')->toArray();
			// Fetch categories and tags from the database for these products
			$categories = DB::table('categories')
				->join('product_category', 'categories.id', '=', 'product_category.category_id')
				->whereIn('product_category.product_id', $productIds)
				->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
				->get()
				->groupBy('product_id');

			
			$brands = DB::table('brands')
                    ->join('products', 'brands.id', '=', 'products.brand_id')
                    ->whereIn('products.product_id', $productIds)
                    ->select('brands.id', 'brands.name', 'brands.logo', 'products.product_id')
                    ->get()
                    ->groupBy('product_id')
                    ->map(function ($items) {
                        return $items->map(function ($item) {
                            $item->logo = SearchHelpers::getDistributorLogo($item->logo, 'distributors-documents');
                            return $item;
                        });
                    });
			$modifiedProducts = $products->map(function ($product, $index) use ($categories, $results) {
				// Get categories from the grouped data
				$categoryList = $categories[$product['id']] ?? collect();
				$categoryArray = $categoryList->toArray();
				$productId = $product['id'];
				// Store in another array
				// $allCategories[$productId] = $categoryList->toArray();

				// dd($allCategories);
				$categoriesData = [];

				foreach ($categoryList as $category) {
					if ($category->parentCategoryID == 0) {
						$categoriesData[] = [
							'parent' => [
								'id' => $category->id,
								'categoryTitle' => $category->categoryTitle,
								'category_slug' => $category->category_slug,
								'parentCategoryID' => $category->parentCategoryID,
							],
							'childCategories' => []
						];
					} else {
						foreach ($categoriesData as &$parentCategory) {
							if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
								$parentCategory['childCategories'][] = [
									'id' => $category->id,
									'categoryTitle' => $category->categoryTitle,
									'category_slug' => $category->category_slug,
									'parentCategoryID' => $category->parentCategoryID
								];
								break;
							}
						}
					}
				}
				$allCategories[] = $categoriesData;
				return [
					'product_id' => $product['id'],
					'objectID' => $product['objectID'] ?? null,
					'title' => $product['name'],
					'store_id' => $product['storeId'],
					'slug' => $product['slug'],
					'price' => $product['price'],
					'description' => $product['description'],
					'current_stock' => $product['current_stock'],
					'status' => $product['status'],
					'is_custom' => $product['is_custom'] ?? 0,
					'product_type' => $product['product_type'],
					'on_sale' => $product['on_sale'],
					'sale_price' => $product['sale_price'],
					'discount_percentage' => $product['discount_percentage'],
					'feature_image' => $product['feature_image'] ?? null,
					'feature_image_alt' => $product['feature_image_alt'] ?? null,
					'shipping_option_id' => $product['shipping_option_id'],
					'categories' => array_values($categoriesData), // Attach categories
					'created_at' => $product['created_at'],
					'query_id' => $results['queryID'],
					'mallshark_selected' => $product['mallshark_selected'] ?? 0,
					'labels' => $product['labels'] ?? null


				];
			});
			// Now you can use $allCategories outside the map()
		

			// Optional: Reindex values (if needed)
			

// dd($uniqueCategories);

			// Replace the original product collection with the modified one
			$products = $modifiedProducts;
		

			if (isset($products) && !empty($products)) {
				return [
					'Product' => $products,
					'brands'=> $brands->flatten()->unique('id')->values(),
				];
			} else {
				throw new ItemNotFoundException("Store Data Not Avaliable");
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}
	public function storeinfo($slug)
	{
		try {

			$storeId = $this->storeRepository->getStoreBySlug($slug);
			$userId = $this->storeRepository->getStoreUserBySlug($slug);
			$storeData = $this->getStoreDataByUserId($userId);
			$allCategories = [];
			$storeData = $this->objToArray($storeData);
			$store_logo_url = $this->getAttachmentUrlById($storeData['store_logo']); // This function In Core Package
			$storeData['store_logo_url'] = $store_logo_url;
			$storeGallery = []; // Ensure it's initialized
			$bannerImageData = $storeData['banner_image_id'];
			$bannerImageIds = @unserialize($bannerImageData);
			$storeTotalRating = 0;
			$storeTotalReviews = 0;
			if ($bannerImageIds && is_array($bannerImageIds)) {
				foreach ($bannerImageIds as $bannerId) {
					$url = $this->getAttachmentUrlById($bannerId);

					$filename = basename($url);

					$storeGallery[] = [
						'banner_img_id' => (int) $bannerId,
						'url' => $url,
						'filename' => $filename
					];
				}
			}

			$storeData['banner_image_urls'] = $storeGallery;
			$results = $this->storeRepository->storeInfo($storeId);

			$products = collect($results['hits']);
			$productIds = $products->pluck('id')->toArray();
			// Fetch categories and tags from the database for these products
			$categories = DB::table('categories')
				->join('product_category', 'categories.id', '=', 'product_category.category_id')
				->whereIn('product_category.product_id', $productIds)
				->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
				->get()
				->groupBy('product_id');

			$allCategories = []; // Define this before the map()

			$modifiedProducts = $products->map(function ($product, $index) use ($categories, $results, &$allCategories, &$storeTotalRating, &$storeTotalReviews) {
				$categoryList = $categories[$product['id']] ?? collect();
				$categoryArray = $categoryList->toArray();
				$productId = $product['id'];

				$categoriesData = [];
				$parentCategories = [];
				$childCategories = [];
					// Step 1: Separate parent and child categories
				foreach ($categoryList as $category) {
					if ($category->parentCategoryID == 0) {
						$parentCategories[$category->id] = [
							'parent' => [
								'id' => $category->id,
								'categoryTitle' => $category->categoryTitle,
								'category_slug' => $category->category_slug,
								'parentCategoryID' => $category->parentCategoryID,
							],
							'childCategories' => []
						];
					} else {
						$childCategories[] = $category;
					}
				}

				// Step 2: Attach children to the correct parents
				foreach ($childCategories as $category) {
					$parentId = $category->parentCategoryID;

					if (isset($parentCategories[$parentId])) {
						$parentCategories[$parentId]['childCategories'][] = [
							'id' => $category->id,
							'categoryTitle' => $category->categoryTitle,
							'category_slug' => $category->category_slug,
							'parentCategoryID' => $category->parentCategoryID,
						];
					} else {
						// Optional: handle orphan child categories with missing parent
						// For example, add them as top-level if desired
					}
				}

				$categoriesData = array_values($parentCategories); // Reset keys for clean array
				$allCategories[] = $categoriesData;

				// reviews
				$reviewsProduct = $this->get_reviews($product['id']);
				if (!empty($reviewsProduct)) {

					foreach ($reviewsProduct as $review) {
						$storeTotalRating += $review['rating'];
						$storeTotalReviews++;
					}
				}

				return [
					'product_id' => $product['id'],
					'objectID' => $product['objectID'] ?? null,
					'title' => $product['name'],
					'store_id' => $product['storeId'],
					'slug' => $product['slug'],
					'price' => $product['price'],
					'description' => $product['description'],
					'current_stock' => $product['current_stock'],
					'status' => $product['status'],
					'product_type' => $product['product_type'],
					'on_sale' => $product['on_sale'],
					'sale_price' => $product['sale_price'],
					'discount_percentage' => $product['discount_percentage'],
					'feature_image' => $product['feature_image'] ?? null,
					'categories' => array_values($categoriesData),
					'created_at' => $product['created_at'],
					'query_id' => $results['queryID'],
					'mallshark_selected' => $product['mallshark_selected'] ?? 0
				];
			});

			// Now you can use $allCategories outside the map()
			$uniqueCategories = [];

			foreach ($allCategories as $items) {
				foreach ($items as $item) {
					$parent = $item['parent'];
					$childCategories = $item['childCategories'];

					$parentId = $parent['id'];

					// If parent already exists
					if (isset($uniqueCategories[$parentId])) {
						// Merge and ensure child categories are unique by ID
						$existingChildren = $uniqueCategories[$parentId]['childCategories'];
						$combinedChildren = array_merge($existingChildren, $childCategories);

						// Remove duplicates based on child ID
						$uniqueChildren = collect($combinedChildren)->unique('id')->values()->toArray();

						$uniqueCategories[$parentId]['childCategories'] = $uniqueChildren;
					} else {
						// Add new parent with its children
						$uniqueCategories[$parentId] = [
							'parent' => $parent,
							'childCategories' => $childCategories,
						];
					}
				}
			}

			// Optional: Reindex values (if needed)
			$uniqueCategories = array_values($uniqueCategories);

			// dd($uniqueCategories);

			// Replace the original product collection with the modified one
			$products = $modifiedProducts;


			// Calculate average store rating
			$storeAverageRating = $storeTotalReviews > 0 ? number_format($storeTotalRating / $storeTotalReviews, 1) : 0;


			$storeReviewSummary = [
				'average_rating' => $storeAverageRating,
				'total_reviews' => $storeTotalReviews,

			];
			if (isset($products) && !empty($products)) {
				return [
					'storeInformation' => $storeData,
					'category' => $uniqueCategories,
					'storeReviewSummary' => $storeReviewSummary

				];
			} else {
				throw new ItemNotFoundException("Store Data Not Avaliable");
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function followStore($storeId)
	{
		try {
			$userId = auth()->user()->user_id;

			return $this->storeRepository->followStore($userId, $storeId);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function unlockNextDay($sellerId, $currentDay)
    {
        try {
            return $this->storeRepository->unlockNextDay($sellerId, $currentDay);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteStore()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                throw new ItemNotFoundException('User not found.');
            }

            $store = $this->getStoreDataByUserId($user->user_id);
            if (!$store) {
                throw new ItemNotFoundException('No store found for this user.');
            }

            $storeId = $store->store_id;

            // Check for pending, shipped, partial_shipped, or disputed orders
            $incompleteOrders = DB::table('store_orders')
                ->where('store_id', $storeId)
                ->where('charge_status', 'paid')
                ->whereIn('order_status', ['pending', 'shipped', 'partial_shipped', 'disputed'])
                ->count();

            if ($incompleteOrders > 0) {
                throw new AuthorizationException('Your store has pending, shipped or disputed orders. All orders must be completed before deleting your store.');
            }

            return $this->storeRepository->deleteStore($store);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (ItemNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
