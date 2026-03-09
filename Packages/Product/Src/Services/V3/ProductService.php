<?php

namespace Packages\Product\Services\V3;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Support\ItemNotFoundException;
use Packages\Product\Contracts\V3\ProductHelpers;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Models\V3\{Attachments,AiUsageToken,ProductVisitor};
use Illuminate\Support\Facades\Http;
use Packages\Store\Models\V3\Store;
use Packages\Product\Repositories\V3\ProductRepository;
use Intervention\Image\Facades\Image;
use Packages\Klavio\Services\V3\KlavioService;


class ProductService

{
    use ProductHelpers;
    protected $productRepository;
    protected $KlavioService;

    public function __construct(ProductRepository $productRepository, KlavioService $KlavioService)
        
    {
        $this->productRepository = $productRepository;
        $this->KlavioService = $KlavioService;
    }

    public function getStore()
    {
        $store = auth()->user()->store;
        if (!$store) {
            throw new ItemNotFoundException('Store not found');
        }
        return $store;
    }
    public function storeFaqs($product_id, $questions, $answers)
    {
        try {

            if (isset($questions) && isset($answers) && !empty($questions) && !empty($answers)) {

                $faqs = $this->validateStoreFaqs(['faqQuestions' => $questions, 'faqAnswers' => $answers]);

                return $this->productRepository->storeFaqs($product_id, $faqs);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateFaqs($product_id, $questions, $answers)
    {
        try {
            if (isset($questions) && isset($answers) && !empty($questions) && !empty($answers)) {

                $faqs = $this->validateStoreFaqs(['faqQuestions' => $questions, 'faqAnswers' => $answers]);

                return $this->productRepository->updateFaqs($product_id, $faqs);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function getLoveProducts()
    {
        try {
            $result = $this->productRepository->getLoveProducts();
            $products = [];
            foreach ($result as $r_product) {
                $featureImage = $this->getAttachmentUrlById($r_product->feature_image);
                $r_product['feature_image'] = $featureImage;
                array_push($products, $r_product);
            }
            // You can now use the $products variable as needed
            if (isset($products)) {

                return $products;
            } else {
                throw new ItemNotFoundException('Product not found');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    function validateStoreFaqs($faqs)
    {

        $rules = [
            'faqQuestions' => 'required|array',
            'faqAnswers' => 'required|array',
        ];
        $validator = Validator::make($faqs, $rules);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        return $validator->validated();
    }
    public function get($productId)
    {
        try {

            $product = $this->productRepository->getProductById($productId);

            $product->feature_image = [
                'id' => $product->feature_image,
                'url' => $this->getAttachmentUrlSmallById($product->feature_image) ?? null,
                'alt' => ProductHelpers::getImageAltById($product->feature_image) ?? '',
            ];
            $product->load('variations.tiers', 'categories', 'tags', 'faqs');
            if ($product->variations->count() > 0) {
                foreach ($product->variations as $variation) {

                    $variation->product_variation_image = [
                        'id' => $variation->product_variation_image,
                        'url'=>$this->getAttachmentUrlSmallById($variation->product_variation_image) ?? null];

                    $variationDiscounts = $this->productRepository->getDiscountApplicable($product->discount_id, $variation->variation_id);
                    if(!empty($variationDiscounts)){
                        $variation->discounts = $variationDiscounts;
                    }
                }
            }
            return $product;
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Product not found');
        }
    }

    public function disassociateFromShopify($productId)
    {
        try {

            $product = $this->productRepository->getShopifyProduct($productId);
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Product not found');
        }
    }
    public function uploadProcessProductImage($image)
    {

        $productGallery = array();
        if ($image) {

            $destinationPath = public_path() . '/uploads/temp';
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }

            $OriginalfileName = $image->getClientOriginalName();
            $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
            $fileExtension = $image->getClientOriginalExtension();

            $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
            $image->move($destinationPath, $fileName);
            return ['file_name' => $fileName];
        }
    }

    public function uploadProcessDescImage($image)
    {

        $productGallery = array();
        if ($image) {

            $destinationPath = public_path() . '/uploads/desc';
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }

            $OriginalfileName = $image->getClientOriginalName();
            $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
            $fileExtension = $image->getClientOriginalExtension();

            $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
        
         
            $image->move($destinationPath, $fileName);
           
            $descUrl = $this->productRepository->getDescAttachmentUrl($fileName);
            return $descUrl;
        }
    }
    public function getProductGallery($productId)
    {
        try {
            $product = $this->productRepository->getProductById($productId);
            return $product->gallery;
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Product not found'); 
        }
    }

    public function getProductDetail($slug, $userId, $guestUuid = null)
    {
        try {
            $productDetail = $this->productRepository->getProductDetailBySlug($slug, $userId);

            if (!$productDetail) {
                throw new ItemNotFoundException('Product not found');
            }

            $product = (array) $productDetail;
            $productId = $product['product_id'];

            // Track visitor
            $visitorId = $userId ? (string) $userId : $guestUuid;
            if ($visitorId) {
                try {
                    ProductVisitor::firstOrCreate([
                        'store_id'   => $product['store_id'],
                        'product_id' => $productId,
                        'visitor_id' => $visitorId,
                        'visit_date' => now()->toDateString(),
                    ]);
                } catch (\Exception $e) {
                    // Silently fail - visitor tracking should not break product detail
                }
            }
            if($product['product_type'] === 'simple') {
                $product = ProductHelpers::checkCouponExists($product, $product['product_type'],$variation=null,$userId);
            }
            $featureImageId = $product['feature_image'] ?? null;
            $gallery = $product['gallery'] ?? [];
        
            // Sanitize descriptions once
            $product['product_description'] = $this->sanitizeDescription($product['description'] ?? '');
            $product['product_short_description'] = $this->sanitizeDescription($product['short_description'] ?? '');
        
            // Feature Image
            $featureImagePath = $this->getAttachmentUrlById($featureImageId, 'product');
            $product['feature_image_path'] = $featureImagePath;
            $product['feature_image_alt'] = ProductHelpers::getImageAltById($featureImageId);
           if (isset($product['gallery']) && !is_array($product['gallery'])) {
                $product['gallery'] = unserialize($product['gallery']);
            }
            // Gallery Images
            $product['gallery_images'] = $this->processGallery($gallery, "product");
            $product['gallery_alt'] = ProductHelpers::getGalleryAlt($gallery);
            $product['gallery_small_images'] = $this->processGallery($gallery, "small");
            $saleProductIds = ProductConstants::SALE_PRODUCTS;
            if(in_array($productId, $saleProductIds)){
                $product['unit_sold'] = $product['sales'];
            } else {
                $product['unit_sold'] = $this->productRepository->getProductUnitSold($productId);

            }

            // Handle variations
            $variationsArray = [];
            if ($product['product_type'] === 'variant') {
                $variationData = $this->processVariations($productId, $featureImagePath,$userId);
                if($product['unique_product'] != true){
                    $product['current_stock'] = $variationData['stock'];
                }
                $variationsArray = $variationData['variations'];
            }
        
            $faqs = $product['faqs'];
            unset($product['faqs']);
            if ($product['order_items']) {
                unset($product['order_items']);
            }
            $store = $product['store'];
            $isWholesaleAllowed = $store['is_wholesale_allowed'] ?? false;

            $store = [
                'store_id' => $store['store_id'],
                'store_name' => $store['store_name'],
                'store_slug' => $store['store_slug'],
                'store_city' => $store['store_city'],
                'created_at' => $store['created_at'],
                'store_logo_url' => $this->getAttachmentUrlById($store['store_logo'], 'media'),
            ];

            $product['store'] = $store;
            $review = $product['reviews'];
            $superadminReview = $product['superadmin_review'];
            $allReviews = array_merge($review, $superadminReview);
            // Total count
            $totalCount = count($allReviews);

            // Average rating
            $avgRating = $totalCount > 0 
                ? array_sum(array_column($allReviews, 'rating')) / $totalCount 
                : 0;

            $reviews = [
                'total_count' => $totalCount,
                'average_rating' => round($avgRating, 2)
            ];
            unset($product['reviews'], $product['superadmin_review']);

            // If store wholesale is disabled by admin, hide tiers and allow retail
            if (!$isWholesaleAllowed) {
                $product['only_wholesale'] = 0;
                $tiers = [];
            } else {
                $tiers = $product['tiers'];
            }
            unset($product['tiers']);

            return [
                'productDetail' => $product,
                'variations' => $variationsArray,
                'tiers' => $tiers,
                'faqs' => $faqs,
                'reviews' => $reviews
            ];
        } catch (\Exception $e) {
            throw $e;
        }
        
    }
    public function getProductPreview($slug, $userId)
    {
        try {
            $productDetail = $this->productRepository->getProductPreviewBySlug($slug, $userId);
            
            if (!$productDetail) {
                throw new ItemNotFoundException('Product not found');
            }
        
            $product = (array) $productDetail;
            $productId = $product['product_id'];
            if($product['product_type'] === 'simple') {
                $product = ProductHelpers::checkCouponExists($product, $product['product_type'],$variation=null,$userId);
            }
            $featureImageId = $product['feature_image'] ?? null;
            $gallery = $product['gallery'] ?? [];
        
            // Sanitize descriptions once
            $product['product_description'] = $this->sanitizeDescription($product['description'] ?? '');
            $product['product_short_description'] = $this->sanitizeDescription($product['short_description'] ?? '');
            
            // Feature Image
            $featureImagePath = $this->getAttachmentUrlById($featureImageId, 'product');
            $product['feature_image_path'] = $featureImagePath;
            $product['feature_image_alt'] = ProductHelpers::getImageAltById($featureImageId);
           if (isset($product['gallery']) && !is_array($product['gallery'])) {
                $product['gallery'] = unserialize($product['gallery']);
            }
            // Gallery Images
            $product['gallery_images'] = $this->processGallery($gallery, "product");
            $product['gallery_alt'] = ProductHelpers::getGalleryAlt($gallery);
            $product['gallery_small_images'] = $this->processGallery($gallery, "small");
            $saleProductIds = ProductConstants::SALE_PRODUCTS;
            if(in_array($productId, $saleProductIds)){
                $product['unit_sold'] = $product['sales'];
            } else {
                $product['unit_sold'] = $this->productRepository->getProductUnitSold($productId);

            }

            // Handle variations
            $variationsArray = [];
            if ($product['product_type'] === 'variant') {
                $variationData = $this->processVariations($productId, $featureImagePath,$userId);
                $product['current_stock'] = $variationData['stock'];
                $variationsArray = $variationData['variations'];
            }
        
            $faqs = $product['faqs'];
            unset($product['faqs']);

            $store = $product['store'];
            $isWholesaleAllowed = $store['is_wholesale_allowed'] ?? false;

            $store = [
                'store_id' => $store['store_id'],
                'store_name' => $store['store_name'],
                'store_slug' => $store['store_slug'],
                'store_city' => $store['store_city'],
                'created_at' => $store['created_at'],
                'store_logo_url' => $this->getAttachmentUrlById($store['store_logo'], 'media'),
            ];

            $product['store'] = $store;
            $review = $product['reviews'];
            $superadminReview = $product['superadmin_review'];
            $allReviews = array_merge($review, $superadminReview);
            // Total count
            $totalCount = count($allReviews);

            // Average rating
            $avgRating = $totalCount > 0 
                ? array_sum(array_column($allReviews, 'rating')) / $totalCount 
                : 0;

            $reviews = [
                'total_count' => $totalCount,
                'average_rating' => round($avgRating, 2)
            ];
            unset($product['reviews'], $product['superadmin_review']);

            // If store wholesale is disabled by admin, hide tiers and allow retail
            if (!$isWholesaleAllowed) {
                $product['only_wholesale'] = 0;
                $tiers = [];
            } else {
                $tiers = $product['tiers'];
            }
            unset($product['tiers']);

            return [
                'productDetail' => $product,
                'variations' => $variationsArray,
                'tiers' => $tiers,
                'faqs' => $faqs,
                'reviews' => $reviews
            ];
        } catch (\Exception $e) {
            throw $e;
        }
        
    }

    public function getSellerProductDetail($slug, $userId)
    {
        try {
            $productDetail = $this->productRepository->getSellerProductDetailBySlug($slug, $userId);
            if (!$productDetail) {
                throw new ItemNotFoundException('Product not found');
            }

            $productDetailArray = (array) $productDetail;
            if (isset($productDetailArray['description']) && !empty($productDetailArray['description'])) {
                $productDetailArray['product_description'] = $this->sanitizeDescription($productDetailArray['description']);
            }
            if (isset($productDetailArray['short_description']) && !empty($productDetailArray['short_description'])) {
                $productDetailArray['product_short_description'] = $this->sanitizeDescription($productDetailArray['short_description']);
            }
            unset($productDetailArray['description'], $productDetailArray['short_description']);

            // Process gallery images
            $productDetailArray['gallery_images'] = $this->processGalleryImages($productDetail['gallery']);
            $productDetailArray['feature_image_path'] = $this->getAttachmentUrlById($productDetail['feature_image'], 'product');

            // Process variations
            $processVariations = $this->processVariations($productDetailArray['product_id'], $productDetailArray['feature_image_path'],$userId);
            if (!empty($processVariations)) {
                $variationsArray = $processVariations['variations'];
                $productDetailArray['current_stock'] = $processVariations['stock'];
            }

            /*
            * Product Category  
            */
            $productCategories = $this->productRepository->getCategoriesOfProduct($productDetail['product_id']);
            $productCategoriesArray = [];
            if (count($productCategories) > 0) {

                foreach ($productCategories as $category) {
                    $productCategory = (array) $category; // Convert the object to an array
                    $productCategory['category_image_path'] = $this->getAttachmentUrlById((int) $category['image_id'], 'media');
                    $productCategoriesArray[] = $productCategory;
                }
            }

            /*
            * Related Products   
            */

            $relatedProduct = $this->productRepository->getRelatedProducts($productDetailArray['product_id'], $productDetailArray['store_id']);
            $relatedProductArray = [];
            if (count($relatedProduct) > 0) {

                foreach ($relatedProduct as $prod) {
                    $relatedProducts = (array) $prod;
                    $relatedProducts['feature_image_path'] = $this->getAttachmentUrlById((int) $prod->feature_image, 'media');
                    $relatedProductArray[] = $relatedProducts;
                }
            }

            /*
            * Product Tags  
            */

            $productTags = $this->getTagsByProductId($productDetailArray['product_id']);
            if (isset($productTags)) {
                $counterTags = count($productTags);
                $tags = implode(',', $productTags);
            }

            /*
            * Store Information  
            */

            $storeInfo = $this->getStoreNameInfoById($productDetail['store_id']);
            if (isset($storeInfo)) {

                $storeInformation = (array) $storeInfo;
                $storeInformation['store_thumbnail'] = $this->getAttachmentUrlById((int) $storeInfo['store_logo'], 'media');
            }

            $reviewsProduct = $this->get_reviews($productDetailArray['product_id']);
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
                $average = isset($average) ? $average : "";
            } else {
                $singleReview = [];
                $avergeSingleReview = [];
            }
            $reviewAverage = $average;
            $totalReviews = count($reviewsProduct);
            $shippingDetails = $this->productRepository->getShippingDetail($productDetailArray['product_id']);
            $faqs = $this->productRepository->getProductFaqsById($productDetail['product_id']);

            $this->avaiableForFlashSale($productDetailArray['product_id']);

            if ($productDetailArray['video_url']) {

                $productDetailArray['videoUrlSource'] = $this->getYoutubeEmbedUrl($productDetailArray['video_url']);
            }

            $productDetailArray['shippings']['method'] = ucfirst($productDetailArray['shippings']['method']);

            $data = [
                'productDetail' => $productDetailArray,
                'productCategories' => $productCategoriesArray,
                'variations' => $variationsArray,
                'storeInfo' => $storeInformation,
                'reviews' => $reviewArray,
                'reviewAverage' => $reviewAverage,
                'reviewByStars' => $singleReview,
                'averageReviewByStars' => $avergeSingleReview,
                'totalReviews' => $totalReviews,
                'relatedProduct' => $relatedProductArray,
                'shippingDetails' => $shippingDetails,
                'tags' => $tags,
                'faqs' => $faqs,
            ];

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    private function processGallery($serializedData,$size)
    {
        $galleryArray = [];
        if ($serializedData) {
            $unserializedArray = unserialize($serializedData);
            foreach ($unserializedArray as $value) {
                $image = $this->getAttachmentUrlById((int) $value,$size);
                $galleryArray[] = $image;
                // $galleryArray[] = webPImages($image, 'small');
            }
        }
        return $galleryArray;
    }


    private function processGalleryImages($serializedData)
    {
        $galleryArray = [];
        if ($serializedData) {
            $unserializedArray = unserialize($serializedData);
            foreach ($unserializedArray as $value) {
                $image = $this->getAttachmentUrlById((int) $value, 'product');
                $galleryArray[] = webPImages($image, 'small');
            }
        }
        return $galleryArray;
    }

    private function processVariations($productId, $fallbackImage,$userId)
    {
        $variations = $this->productRepository->getAllProductVariation($productId);
        $variationsArray = [];
        $stock = 0;

        foreach ($variations as $variation) {
            if($variation['product_variation_image'] == null || $variation['product_variation_image'] == ''){
                continue;
            }
            $variation = ProductHelpers::checkCouponExists($productId,$type = 'variant', $variation,$userId);
            $stock += $variation['current_stock'];
            $variationArray = (array) $variation;
            $variationArray['variation_image_id'] = $variationArray['product_variation_image'];
            $variationArray['product_variation_image'] = $this->getAttachmentUrlSmallById((int) $variation['product_variation_image'], 'small') ?? $fallbackImage;
            $variationArray['product_variation_image'] = webPImages($variationArray['product_variation_image'], 'small');
            $variationsArray[] = $variationArray;
        }

        return ['variations' => $variationsArray, 'stock' => $stock];
    }

    public function toggleFavorite($productId)
    {
        $user = auth()->user();
        $userId = $user->user_id;
        


        DB::beginTransaction();
        try {
            $existingFavorite = $this->productRepository->getFavoriteByUserAndProduct($userId, $productId);

            if ($existingFavorite) {
                $this->productRepository->deleteFavorite($existingFavorite);
                DB::commit();
                return false; // Unfavorited
            } else {
                $data = [
                    'user_id' => $userId,
                    'product_id' => $productId
                ];
                
                $this->productRepository->saveFavorite($data);
                $product = $this->productRepository->getProductById($productId);
                $this->KlavioService->trackFavoriteEvent($user, $product);

                DB::commit();
                return true; // Favorited
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getBuyerFavorites($filters)
    {
        $userId = auth()->user()->user_id;

        $favorites = $this->productRepository->getFavoritesByUser($userId, $filters);
        if ($favorites->isEmpty()) {
            throw new ItemNotFoundException('No favorite products found');
        }
        // $products = [];

        // foreach ($favorites as $product) {
        //     $rating = $this->getProductRatingById($product['product_id']);
        //     $featureImage = $this->getAttachmentUrlById($product['feature_Image']);

        //     unset($product['feature_Image']); // Remove the original key
        //     $product['feature_image'] = $featureImage;
        //     $product['rating'] = $rating;

        //     $products[] = $product;
        // }

        // return $products;

        $favorites->getCollection()->transform(function ($product) {
            $rating = $this->getProductRatingById($product->product_id);
            $featureImage = $this->getAttachmentUrlById($product->feature_Image);

            unset($product->feature_Image);
            $product->feature_image = $featureImage;
            $product->feature_image_alt = ProductHelpers::getImageAltById($product->feature_Image);
            $product->rating = $rating;

            return $product;
        });

        return $favorites;
    }

    public function getProducts()
    {
        try {
            $userId = auth()->user()->user_id;
            if (!$userId) {
                throw new UnauthorizedException('No User found');
            }
            $store = ProductHelpers::getAccessibleStore($userId);
            $all_products = $this->productRepository->getProducts($store);

            $formattedProducts = [];

            foreach ($all_products as $key => $product) {

                // Adjust status based on conditions
                if ($product->product_type != 'variant') {
                    if ($product->status == ProductConstants::STATUS_ACTIVE && $product->current_stock > 0) {
                        $product->status = 'active';
                    } elseif ($product->status == ProductConstants::STATUS_DEACTIVATED) {
                        $product->status = 'deactive';
                    } elseif ($product->status ==  ProductConstants::STATUS_PENDING) {
                        $product->status = 'pending';
                    } elseif ($product->status == ProductConstants::STATUS_DRAFT) {
                        $product->status = 'draft';
                    } elseif ($product->status ==  ProductConstants::STATUS_REJECTED) {
                        $product->status = 'rejected';
                    }

                    // Check for out-of-stock for non-variant products
                    if ($product->status == ProductConstants::STATUS_ACTIVE  && $product->current_stock <= 0) {
                        $product->status = 'out-of-stock';
                    }
                } elseif ($product->product_type == 'variant') {
                    // foreach($product->variations as $variation){

                    // }
                    if ($product->status == ProductConstants::STATUS_ACTIVE) {
                        $product->status = 'active';
                    } elseif ($product->status == ProductConstants::STATUS_DEACTIVATED) {
                        $product->status = 'deactive';
                    } elseif ($product->status ==  ProductConstants::STATUS_PENDING) {
                        $product->status = 'pending';
                    } elseif ($product->status == ProductConstants::STATUS_DRAFT) {
                        $product->status = 'draft';
                    } elseif ($product->status ==  ProductConstants::STATUS_REJECTED) {
                        $product->status = 'rejected';
                    }
                }

                // Set feature image using the 'getAttachmentUrlSmallById' function
                if (isset($product->feature_image) && !is_null($product->feature_image)) {
                    $product->feature_image = $this->getAttachmentUrlSmallById(intval($product->feature_image), 'small');
                }

                // Process variations for variant products
                if ($product->product_type == 'variant' && isset($product->variations) && !empty($product->variations)) {
                    $variableproductStock = 0;
                    foreach ($product->variations as $key => $variation) {
                        if ($key == 0) {
                            $product->price = $variation->price;
                        }
                        // Set variation image using the 'getAttachmentUrlSmallById' function
                        if (isset($variation->current_stock) && !is_null($variation->current_stock)) {
                            $variableproductStock += intval($variation->current_stock);
                        }
                        if (isset($variation->product_variation_image) && !is_null($variation->product_variation_image)) {
                            $imageId = intval($variation->product_variation_image);
                            $variation->product_variation_image = $this->getAttachmentUrlSmallById($imageId, 'small');
                        } else {
                            // Set variation image to the feature image of the main product if not provided
                            $variation->product_variation_image = $product->feature_image;
                        }
                    }
                    // dd($variableproductStock);
                    $product->current_stock = $variableproductStock;
                    if ($product->status == 'active' && $variableproductStock == 0) {
                        $product->status = 'out-of-stock';
                        // dd($product->status);
                    }
                }

                // Add only necessary data to the formattedProducts array
                $formattedProducts[] = [
                    'product_id' => $product->product_id,
                    'slug' => $product->slug,
                    'title' => $product->title,
                    'store_id' => $product->store_id,
                    'price' => $product->price,
                    'current_stock' => $product->current_stock,
                    'on_sale' => $product->on_sale,
                    'discount_id' => $product->discount_id == 0 ? null : $product->discount_id,
                    'feature_image' => $product->feature_image,
                    'product_type' => $product->product_type,
                    'status' => $product->status,
                    'variations' => $product->variations,
                    'category' => $product->categories,
                    'order_items' => $product->orderItems,
                    'labels' => $product->labels,
                ];
            }



            if (!empty($formattedProducts)) {
                return [
                    'data' => $formattedProducts,
                    'count' => count($formattedProducts),
                ];
            } else {
                throw new ItemNotFoundException('You have no Product');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function uploadProductImage($productsimages)
    {
        try {
            if (empty($productsimages)) {
                throw new ItemNotFoundException('Image not found');
            }

            $destinationPath = public_path() . '/uploads/temp';
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }

            return $this->productRepository->uploadProductImage($destinationPath, $productsimages);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function deleteProductImage($data)
    {
        try {
            if ((isset($data['imageName']) && is_numeric($data['imageName'])) && isset($data['edit_product_id'])) {
                // return 12;
                // This block will execute if $imageName is numeric and $product_id is set
                // return $data['imageId'];

                $deleteImage = $this->productRepository->deleteProductImageRequest($data);


                // Check if the operation was successful before returning
                if ($deleteImage) {
                    return true;
                } else {
                    // Handle the case where the operation failed
                    throw new ItemNotFoundException('Image not found');
                }
            } else {
                // This block will execute if the conditions are not met
                $result = $this->productRepository->deleteProductTemRequest($data);
                if ($result) {
                    return true;
                } else {
                    // Handle the case where the operation failed
                    throw new ItemNotFoundException('Image not found');
                }
            }
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException('Image not found');
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateProductShipping($data)
    {
        try {
            return $this->productRepository->updateProductShipping($data);
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getProductsSlug()
    {
        return $this->productRepository->getProductsSlug();
    }
    public function productHeadingSeo($slug){
        $data = $this->productRepository->productMetaTags($slug);
        $featureImage = getAttachmentUrlById($data->feature_image,'medium');
        $response= array();
        if($data){
            $response = [
                'title' => $data->title,
                'description' => $data->short_description,
                'feature_image' => $featureImage,
                'tags' => $data->tags->pluck('tag_title')
            ];
        }
        return $response;
    }
    public function updateProductSlug($storeId)
    {
        return $this->productRepository->updateProductSlug($storeId); 
    }

    public function assignLabels($productId)
    {
        try {
            $shippingProfiles = $this->productRepository->getProductShippingProfiles($productId);

            $hasBestSales = false;
            $hasFastDelivery = false;
            $hasFreeDelivery = false;

            $unitSold = $this->productRepository->getProductOrderCount($productId);
            if ($unitSold >= ProductConstants::BEST_SELLER_COUNT) {
                $hasBestSales = true;
            }

            if ($shippingProfiles) {
                foreach ($shippingProfiles as $profile) {
                    if ($profile->is_general) {
                        if (!empty($profile->freeShipping)) {
                            $hasFreeDelivery = true;
                            if ($profile->freeShipping->free_shipping_time === '1-2 Days') {
                                $hasFastDelivery = true;
                            }
                        }

                        if (!empty($profile->fixedShipping) && $profile->fixedShipping->expected_delivery_time === '1-2 Days') {
                            $hasFastDelivery = true;
                        }
                    } else {
                        foreach ($profile->zones as $zone) {
                            if (!empty($zone->freeShipping)) {
                                $hasFreeDelivery = true;
                                if ($zone->freeShipping->free_shipping_time === '1-2 Days') {
                                    $hasFastDelivery = true;
                                }
                            }

                            if (!empty($zone->fixedShipping) && $zone->fixedShipping->expected_delivery_time === '1-2 Days') {
                                $hasFastDelivery = true;
                            }

                            if ($hasFastDelivery && $hasFreeDelivery) {
                                break 2;
                            }
                        }
                    }
                }
            }

            // Collect slugs
            $labelSlugs = [];
            if ($hasFastDelivery) $labelSlugs[] = 'fast_delivery';
            if ($hasFreeDelivery) $labelSlugs[] = 'free_shipping';
            if ($hasBestSales) $labelSlugs[] = 'best_seller';

            // Fetch label IDs
            $labelIds = $this->productRepository->getLabelIdsBySlugs($labelSlugs);

            // Sync to product
            $product = $this->productRepository->getProduct($productId);
            $product->labels()->sync($labelIds);
            $product->searchable();

            return ['labels_assigned' => $labelSlugs, 
                'product' => $product->load('labels')
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function storeSpecifications($productId, $titles, $values)
    {
        try {

            if (isset($titles) && isset($values) && !empty($titles) && !empty($values)) {

                return $this->productRepository->storeSpecifications($productId, $titles, $values);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateSpecifications($productId, $titles, $values)
    {
        try {
            if (isset($titles) && isset($values) && !empty($titles) && !empty($values)) {

                return $this->productRepository->updateSpecifications($productId, $titles, $values);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function storeProperties($productId, $properties)
    {
        try {
            if (!empty($properties) && is_array($properties)) {
                return $this->productRepository->storeProperties($productId, $properties);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateProperties($productId, $properties)
    {
        try {
            if (!empty($properties) && is_array($properties)) {
                return $this->productRepository->updateProperties($productId, $properties);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getProductsTitle() {
        $products = $this->productRepository->getProductsTitle();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="product_titles.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Add headers
            fputcsv($file, ['Id', 'Title', 'Category']);

            // Add rows
            foreach ($products as $product) {
                fputcsv($file, [
                    $product['product_id'],
                    $product['title'],
                    $product['category']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function getAllProductsIdByStoreId($storeId)
    {
        return $this->productRepository->getAllProductsIdByStoreId($storeId);
    }
    public function rephrase($text, $field, $vendorId, $provider = 'openai',$promptCheck,)
    {
        $this->checkQuota($vendorId);
        if($field == 'title'){
            if ($promptCheck === 'rephrase') {
                $prompt = "Keep all the tex, Just fix the spellings or grammer mistakes {$field} and do not cut-short the original sentence."
                    . $text;
            } elseif ($promptCheck === 'seoFriendly') {
                $prompt = "Keep all the text, fix spelling or grammer mistakes and Make the given text {$field} SEO-friendly and expand as well. "
                        . "Return ONLY the improved text. No explanations:\n\n"
                        . $text;
            }
            $tokens = $this->estimateTokens($prompt);
            $response = $this->callAI($vendorId, $prompt, $provider);
            $this->checkSufficientTokens($vendorId, $tokens);
            $this->logUsage($vendorId, $field, $tokens);
            $this->tokenDeduction($vendorId, $tokens);
            $result = ['rephrase' => $response];
        }else{
            if ($promptCheck === 'rephrase') {
                $prompt = "Keep all the tex and Just fix the spellings or grammer mistakes {$field} and do not cut-short the original sentence."
                    ."kindly return html tags as it attached:\n\n"
                    . $text;
            } elseif ($promptCheck === 'seoFriendly') {
                $prompt = "Keep all the text, fix spelling or grammer mistakes and Make the given text {$field} SEO-friendly and briefly expand the text as well."
                        . "Return ONLY the improved text. No explanations:\n\n"
                        . $text;
            }
            $tokens = $this->estimateTokens($prompt);
            $response = $this->callAI($vendorId, $prompt, $provider);
            $result = ['rephrase' => $response];
            $this->checkSufficientTokens($vendorId, $tokens);
            $this->logUsage($vendorId, $field, $tokens);
            $this->tokenDeduction($vendorId, $tokens);
            if($field == 'description'){
             $tags = $this->suggestTags($text,$vendorId);
               $result['tags'] = $tags;
            }
        }
        return $result;
    }

    public function suggestTags($description, $vendorId)
    {
        $this->checkQuota($vendorId);

        $prompt = "Suggest up to 5 short, SEO-friendly product related tags based on the product description. 
        Return only a comma-separated list, no hashtags, no numbers, no bullet points, no extra text:\n\n" . strip_tags($description);
        $tokens = $this->estimateTokens($prompt);
        $response = $this->callAI($vendorId, $prompt);
        $this->checkSufficientTokens($vendorId, $tokens);
        $this->logUsage($vendorId, 'tag', $tokens);

        return explode(',', $response);
    }

    protected function callAI($vendorId, $prompt, $provider = 'openai')
    {
        // if ($provider === 'gemini') {
            return $this->callGemini($prompt);
        // }

        // return $this->callOpenAI($prompt);
    }

    protected function getAIProvider($vendorId)
    {
        $store = Store::findOrFail($vendorId);
        return $store->ai_provider ?? 'openai'; // fallback to OpenAI
    }

    protected function callOpenAI($prompt)
    {
        $apiKey = config('services.openai.api_key');

        $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        return trim($response->json()['choices'][0]['message']['content'] ?? '');
    }

    protected function callGemini($prompt)
    {
        $apiKey = env('GEMINI_API_KEY');

    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    $response = Http::post(
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
        $payload
    );
//         $apiKey = env('GEMINI_API_KEY');
// $response = Http::get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
// dd($response);
        // dd($response->json());
        return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    protected function estimateTokens($text)
    {
        // Rough estimation: 1 token ~ 4 characters in English
        return (int) ceil(strlen($text) / 4);
    }

    protected function checkQuota($storeId)
    {
        $store = Store::findOrFail($storeId);

        if (!$store->ai_enabled) {
            throw new \Exception('AI access is not enabled for this store.');
        }

        $usage = $store->ai_tokens_remaining;

        if ($usage <= 0) {
            throw new \Exception('Monthly AI token quota exceeded.');
        }
    }

    /**
     * Check if the store has enough tokens left for this request.
     */
    protected function checkSufficientTokens($storeId, $tokensNeeded)
    {
        $store = Store::findOrFail($storeId);
        $remaining = $store->ai_tokens_remaining;

        if ($remaining < $tokensNeeded) {
            throw new \Exception("Not enough AI tokens remaining for this request. Needed: {$tokensNeeded}, Remaining: {$remaining}");
        }
    }
    protected function tokenDeduction($storeId, $tokensNeeded)
    {
        $store = Store::findOrFail($storeId);
        $remaining = $store->ai_tokens_remaining;
        $finalTokens = $remaining - $tokensNeeded;
        $store->ai_tokens_remaining = $finalTokens;
        $store->save();
    }
    protected function logUsage($vendorId, $feature, $tokens)
    {
        AiUsageToken::create([
            'store_id' => $vendorId,
            'field' => $feature,
            'tokens_used' => $tokens,
        ]);
    }

    public function storeProductTiers($data)
    {
        try {
            return $this->productRepository->storeProductTiers($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getProductTiers($productId)
    {
        try {
            return ProductHelpers::getProductTiersByProductId($productId);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
