<?php

namespace Packages\Product\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Support\ItemNotFoundException;
use Packages\Product\Contracts\ProductHelpers;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Repositories\Eloquent\ProductRepository;

class ProductService

{
    use ProductHelpers;
 
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
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
            $result=$this->productRepository->getLoveProducts();
            $products = [];
            foreach ($result as $r_product) {
                $featureImage =$this->getAttachmentUrlById($r_product->feature_image);
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
                'url'=>$this->getAttachmentUrlSmallById($product->feature_image) ?? null];
            $product->load('variations', 'categories', 'tags', 'faqs');
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
    public function getProductGallery($productId)
    {
        try {
            $product = $this->productRepository->getProductById($productId);
            return $product->gallery;
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Product not found');
        }
    }

   public function getProductDetail($slug, $userId)
    {
        try {
            $productDetail = $this->productRepository->getProductDetailBySlug($slug, $userId);
        
            if (!$productDetail) {
                throw new ItemNotFoundException('Product not found');
            }
        
            $product = (array) $productDetail;
            $productId = $product['product_id'];
            $featureImageId = $product['feature_image'] ?? null;
            $gallery = $product['gallery'] ?? [];
        
            // Sanitize descriptions once
            $product['product_description'] = $this->sanitizeDescription($product['description'] ?? '');
            $product['product_short_description'] = $this->sanitizeDescription($product['short_description'] ?? '');
        
            // Feature Image
            $featureImagePath = $this->getAttachmentUrlById($featureImageId, 'product');
            $product['feature_image_path'] = $featureImagePath;
           if (isset($product['gallery']) && !is_array($product['gallery'])) {
                $product['gallery'] = unserialize($product['gallery']);
            }
            // Gallery Images
            $product['gallery_images'] = $this->processGallery($gallery, "product");
            $product['gallery_small_images'] = $this->processGallery($gallery, "small");
        
            // Handle variations
            $variationsArray = [];
            if ($product['product_type'] === 'variant') {
                $variationData = $this->processVariations($productId, $featureImagePath);
                $product['current_stock'] = $variationData['stock'];
                $variationsArray = $variationData['variations'];
            }
        
            // Reviews
            $reviewArray = [];
            $reviewsProduct = $this->get_reviews($productId);
            $reviewAverage = '';
            $singleReview = [];
            $avergeSingleReview = [];
            if (!empty($reviewsProduct)) {
                $userIds = array_unique(array_column($reviewsProduct, 'user_id'));
        
                // Fetch all users in one query (assumes getUsersByIds returns id=>user array)
                $userMap = $this->getUsersByIds($userIds); 
                $average = 0;
                if (isset($reviewsProduct) && count($reviewsProduct) > 0) {
    
                    $numer = 0;
                    $star1_count = 0;
                    $star2_count = 0;
                    $star3_count = 0;
                    $star4_count = 0;
                    $star5_count = 0;
                    foreach ($reviewsProduct as $review) {
                        $review = (array) $review;
                        $userId = $review['user_id'];
                        $review['user_name'] = $userMap[$userId]->name ?? 'Guest';
                        $reviewArray[] = $review;

                        $numer += $review['rating'];



                        if ($review['rating'] == 1) {
                            $star1_count = $star1_count + 1;
                        }
                        if ($review['rating'] == 2) {
                            $star2_count = $star2_count + 1;
                        }
                        if ($review['rating'] == 3) {
                            $star3_count = $star3_count + 1;
                        }
                        if ($review['rating'] == 4) {
                            $star4_count = $star4_count + 1;
                        }
                        if ($review['rating'] == 5) {

                            $star5_count = $star5_count + 1;
                        }
                        $total = count($reviewsProduct);
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
                }
                $reviewAverage = $average;
            }
        
            // Store Info
            $storeInfo = $this->getStoreNameInfoById($product['store_id']);
            $product['store'] = $storeInfo;
            return [
                'productDetail' => $product,
                'variations' => $variationsArray,
                'reviews' => $reviewArray,
                'reviewByStars' => $singleReview,
                'reviewAverage' => $reviewAverage,
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
            if(isset($productDetailArray['description']) && !empty($productDetailArray['description'])){ 
                $productDetailArray['product_description'] = $this->sanitizeDescription($productDetailArray['description']);
            }
            if(isset($productDetailArray['short_description']) && !empty($productDetailArray['short_description'])){
                $productDetailArray['product_short_description'] = $this->sanitizeDescription($productDetailArray['short_description']);       
            }
            unset($productDetailArray['description'], $productDetailArray['short_description']);

            // Process gallery images
            $productDetailArray['gallery_images'] = $this->processGalleryImages($productDetail['gallery']);
            $productDetailArray['feature_image_path'] = $this->getAttachmentUrlById($productDetail['feature_image'], 'product');

            // Process variations
            $processVariations = $this->processVariations($productDetailArray['product_id'], $productDetailArray['feature_image_path']);
            if(!empty($processVariations)) {
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

            $storeInfo = $this->getStoreInfoById($productDetail['store_id']);
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

    private function processGallery($serializedData)
    {
        $galleryArray = [];
        if ($serializedData) {
            $unserializedArray = unserialize($serializedData);
            foreach ($unserializedArray as $value) {
                $image = $this->getAttachmentUrlById((int) $value, 'product');
                $galleryArray[] = $image;
            }
        }
        return $galleryArray;
    }

    private function processGalleryImages($serializedData)
    {
        $galleryArray = [];
        if (!empty($serializedData)) {
            $unserializedArray = unserialize($serializedData);
            foreach ($unserializedArray as $imageId) {
                $galleryArray[] = getAttachmentUrlById((int)$imageId, 'product');
            }
        }
        return $galleryArray;
    }

    private function processVariations($productId, $fallbackImage)
    {
        $variations = $this->productRepository->getAllProductVariation($productId);
        $variationsArray = [];
        $stock = 0;

        foreach ($variations as $variation) {
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
        $userId = auth()->user()->user_id;

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
            $product->rating = $rating;
    
            return $product;
        });
    
        return $favorites;
    }

   public function getProducts()
    {
try{
        $userId = auth()->user()->user_id;
        if (!$userId) {
			throw new UnauthorizedException('No User found');
		}
        $store = $this->getStoreInfoByUserid($userId);
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
                } elseif ($product->status == ProductConstants::STATUS_DRAFT ) {
                    $product->status = 'draft';
                } elseif ($product->status ==  ProductConstants::STATUS_REJECTED) {
                    $product->status = 'rejected';
                }

            // Check for out-of-stock for non-variant products
                if ($product->status == ProductConstants::STATUS_ACTIVE  && $product->current_stock <= 0) {
                    $product->status = 'out-of-stock';
                }
            }elseif ($product->product_type == 'variant'){
                // foreach($product->variations as $variation){

                // }
                if ($product->status == ProductConstants::STATUS_ACTIVE ) {
                    $product->status = 'active';
                }elseif ($product->status == ProductConstants::STATUS_DEACTIVATED) {
                    $product->status = 'deactive';
                } elseif ($product->status ==  ProductConstants::STATUS_PENDING) {
                    $product->status = 'pending';
                } elseif ($product->status == ProductConstants::STATUS_DRAFT ) {
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
                    if($key==0){
                        $product->price = $variation->price;
                    }
                    // Set variation image using the 'getAttachmentUrlSmallById' function
                    if(isset($variation->current_stock) && !is_null($variation->current_stock)){
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
                'category'=> $product->categories,
                'order_items' => $product->orderItems,
            ];
        }

      
      
        if (!empty($formattedProducts)) {
            return [
                'data' => $formattedProducts,
                'count' => count($formattedProducts),
            ];
        } else {
              throw new ItemNotFoundException('You have no Product');
        }}
        catch (\Exception $e) {
         throw $e;
        }
    }

    public function uploadProductImage($productsimages)
    {
        try {
            if(empty($productsimages)){
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

    public function updateProductShipping($data) {
        try {
            return $this->productRepository->updateProductShipping($data);
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getProductsSlug() {
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
                'feature_image' => $featureImage
            ];
        }
        return $response;
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
}
