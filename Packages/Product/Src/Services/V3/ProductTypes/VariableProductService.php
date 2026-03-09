<?php

namespace Packages\Product\Services\V3\ProductTypes;

use FFI\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Repositories\V3\ProductRepository;
use Packages\Product\Services\V3\ProductService;
use Packages\Product\Contracts\V3\ProductHelpers;

class VariableProductService extends ProductService
{
    use ProductHelpers;

    public function store(array $data)
    {
        $user_id = auth()->id();

        $store = ProductHelpers::getAccessibleStore($user_id);
        if (!$store) {
            throw new ItemNotFoundException('User does not belong or have access to any store.');
        }

        $store_id = $store->store_id;
        $featuredImage = $data['feature_image'];
        $productImages = $data['productImages'];
        $productImagesAlt = $data['productImagesAlt'] ?? [];
        $featureImageAlt = $data['featureImageAlt'] ?? [];
        $imagesGallery = [];
        $productFeaturedImage = '';


        foreach ($productImages as $index => $image) {
            $alt = $data['productImagesAlt'][$index] ?? null;

            if (!is_numeric($image)) {
                // New image uploaded
                $attachmentId = $this->productRepository->getAttachmentId($image, 'image', true, true, $alt);
                $imagesGallery[] = $attachmentId;

                // Update all variations that use this image
                foreach ($data['ProductvariationData'] as &$variation) {
                    if (isset($variation['product_variation_image']) && $variation['product_variation_image'] == $image) {
                        $variation['product_variation_image'] = $attachmentId;
                    }
                }
                unset($variation);
            } else {
                // Old image exists
                $attachmentId = $image;
                ProductHelpers::updateImageAlt($attachmentId, $alt);
                $imagesGallery[] = $attachmentId;
            }
        }

        if (!empty($data['feature_image'])) {
            // Normalize to array
            $featureImages = is_array($data['feature_image']) 
                ? $data['feature_image'] 
                : [$data['feature_image']];

            foreach ($featureImages as $index => $image) {
                $alt = $data['featureImageAlt'][$index] ?? null;
                $productFeaturedImage = $this->productRepository->getAttachmentId($image, 'image', true, true, $alt);
            }
        } else {
            $productFeaturedImage = $imagesGallery[0] ?? null;
        }

        $status = ProductConstants::STATUS_PENDING;

        $childCategory= $data['productCategories'][1] ?? $data['productCategories'][0];
        $slug = $this->createSlug($store->user_id, $childCategory, 0);
        if(isset($data['unique_product']) && $data['unique_product'] == true){
            $data['productcurrentstock'] = $data['productcurrentstock'] ?? 1;
        }else{
            $current_stock_total = array_sum(array_column($data['ProductvariationData'], 'current_stock'));
        }
        $productPrice = $data['ProductvariationData'][0]['price'];
         if (!empty($variationData) && count($variationData) > 0) {
            $priceFlag = false;
            $quantityFlag = false;
            $minPrice = null;
            foreach ($variationData as $key => $variation) {
                if(is_null($minPrice) || $variation['price'] < $minPrice){
                    $minPrice = $variation['price'];
                    $priceFlag = true;
                }

                if (!is_null($variation['sku'])) {
                    $productSKU = $variation['sku'];
                }
                $productPrice = $minPrice;
                if($priceFlag){
                    break;
                }
            }
        }
        $productData = [
            'title' => $data['productTitle'],
            'slug' => $slug,
            'store_id' => $store_id,
            'user_id' => $store->user_id,
            'short_description' => $data['productShortDescription'],
            'description' => $data['productDescription'],
            'price' => $productPrice,
            'current_stock' => $current_stock_total ?? $data['productcurrentstock'],
            'sku' => $data['productSKU'],
            'delivery_days' => $data['delivery_days'],
            'status' => $status,
            'weight' => $data['productWeight'] ?? null,
            'weight_type' => $data['weight_type'] ?? null,
            'productSize_length' => $data['productSize_length'] ?? null,
            'productSize_width' => $data['productSize_width'] ?? null,
            'productSize_height' => $data['productSize_height'] ?? null,
            'dimention_type' => $data['dimention_type'] ?? null,
            'video_url' => $data['productVideoURL'],
            'item_condition' => $data['item_condition'],
            'product_type' => $data['product_type'],
            'gallery' => serialize($imagesGallery),
            'feature_Image' => $productFeaturedImage,
            'shipping_option_id' => !empty($data['new_shipping']) ? null : $data['shipping_option_id'][0],
            'new_shipping' => $data['new_shipping'] ?? false,
            'maxquantitylimit' => $data['maxquantitylimit'],
            'brand_distributor_id' => $data['brand_distributor_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'unique_product' => $data['unique_product'] ?? false,
            'is_custom' => $data['is_custom'] ?? false,
        ];

        DB::beginTransaction();

        try {
            $product = $this->productRepository->storeProduct($productData);

            //update slug
            $slug = $this->createSlug($store->user_id, $childCategory, $product->product_id);
            $product->slug = $slug;
            $product->save();

            $this->productRepository->attachRelations($product, 'categories', $data['productCategories']);
            $this->productRepository->storeTags($product, $data['productTags']);
            $this->storeFaqs($product->product_id, $data['faqQuestions'], $data['faqAnswers']);
            if (!empty($data['specification_titles']) && !empty($data['specification_values'])) {
                $this->storeSpecifications(
                    $product->product_id,
                    $data['specification_titles'],
                    $data['specification_values']
                );
            }
            if (!empty($data['properties'])) {
                $this->storeProperties($product->product_id, $data['properties']);
            }

            if ($data['new_shipping']) {
                $product->shippingProfiles()->sync($data['shipping_option_id']);
            }

            $validatedVariations =  $this->validateVariationsData($data['ProductvariationData']);

            $this->productRepository->storeVariations($product, $validatedVariations,$productImages,$imagesGallery);
            $this->assignLabels($product->product_id);

            DB::commit();
            ProductHelpers::generateAiTagForProduct($product);

            $product->load('variations', 'categories', 'tags', 'faqs', 'specifications');
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data)
    {
        
        $user_id = auth()->id();

        $store = ProductHelpers::getAccessibleStore($user_id);
        if (!$store) {
            throw new ItemNotFoundException('User does not belong or have access to any store.');
        }

        $store_id = $store->store_id;

        $productImages = $data['productImages'];
        $imagesGallery = [];
        $productFeaturedImage = '';
        foreach ($productImages as $index => $image) {
            if (!is_numeric($image)) {
                // new image uploaded
                $alt = $data['productImagesAlt'][$index] ?? null;
                $attachmentId = $this->productRepository->getAttachmentId($image, 'image', true, true, $alt);
                $imagesGallery[] = $attachmentId;
            } else {
                //old image exists
                $attachmentId = $image;
                $alt = $data['productImagesAlt'][$index] ?? null;
                ProductHelpers::updateImageAlt($attachmentId, $alt);
                $imagesGallery[] = $attachmentId;
            }
        }
        foreach ($data['ProductvariationData'] as &$variation) {
            if (isset($variation['product_variation_image'])) {
                // Find the matching gallery ID
                $originalImage = $variation['product_variation_image'];

                $key = array_search($originalImage, $productImages);

                if ($key !== false && isset($imagesGallery[$key])) {
                    $variation['product_variation_image'] = $imagesGallery[$key];
                }
            }
        }
        unset($variation);
        if(isset($data['feature_image']) && !empty($data['feature_image'])){
            if(!is_numeric($data['feature_image'])){
                $alt = $data['featureImageAlt'][0] ?? null;
                $productFeaturedImage = $this->productRepository->getAttachmentId($data['feature_image'], 'image', true, true, $alt);
            }else{
                $productFeaturedImage = $data['feature_image'];
                ProductHelpers::updateImageAlt($productFeaturedImage, $data['featureImageAlt'][0] ?? null);
            }
        }else{
            $productFeaturedImage = $imagesGallery[0];
        }

        //check if any image is deleted
        $productGallery = $this->getProductGallery($data['product_id']);


        $deleteImages = array_diff(array_column($productGallery, 'id'), $imagesGallery);

        foreach ($deleteImages as $deleteImage) {
            $this->productRepository->deleteAttachment($deleteImage);
        }

        $variationData = $this->validateVariationsData($data['ProductvariationData']);
        $productPrice = $data['productPrice'];
        // $productQuantity = $data['productcurrentstock'];
        if(isset($data['unique_product']) && $data['unique_product'] == true){
            $data['productcurrentstock'] = $data['productcurrentstock'] ?? 1;
        }else{
            $current_stock_total = array_sum(array_column($data['ProductvariationData'], 'current_stock'));
        }
        $productQuantity = $current_stock_total ?? $data['productcurrentstock'];
        if (!empty($variationData) && count($variationData) > 0) {
            $priceFlag = false;
            $quantityFlag = false;
            $minPrice = null;
            foreach ($variationData as $key => $variation) {
                if(is_null($minPrice) || $variation['price'] < $minPrice){
                    $minPrice = $variation['price'];
                    $priceFlag = true;
                }

                if (!is_null($variation['sku'])) {
                    $productSKU = $variation['sku'];
                }
                $productPrice = $minPrice;
                if($priceFlag){
                    break;
                }
            }
        }

        $status = ProductConstants::STATUS_PENDING;
        $childCategory = $data['productCategories'][1] ?? $data['productCategories'][0];
        $slug = $this->createSlug($store->user_id, $childCategory, $data['product_id']);

        $productData = [
            'title' => $data['productTitle'],
            'slug' => $slug,
            'store_id' => $store_id,
            'user_id' => $store->user_id,
            'short_description' => $data['productShortDescription'],
            'description' => $data['productDescription'],
            'price' => $productPrice,
            'current_stock' => $productQuantity,
            'sku' => $data['productSKU'],
            'delivery_days' => $data['delivery_days'],
//            'status' => $status,
            'weight' => $data['productWeight'] ?? null,
            'weight_type' => $data['weight_type'] ?? null,
            'productSize_length' => $data['productSize_length'] ?? null,
            'productSize_width' => $data['productSize_width'] ?? null,
            'productSize_height' => $data['productSize_height'] ?? null,
            'dimention_type' => $data['dimention_type'] ?? null,
            'video_url' => $data['productVideoURL'],
            'item_condition' => $data['item_condition'],
            'product_type' => $data['product_type'],
            'gallery' => serialize($imagesGallery),
            'feature_image' => $productFeaturedImage,
            'shipping_option_id' => null,
            'maxquantitylimit' => $data['maxquantitylimit'],
            'brand_distributor_id' => $data['brand_distributor_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'unique_product' => $data['unique_product'] ?? false,
            'is_custom' => $data['is_custom'] ?? false,
            // 'only_wholesale' => $data['unique_product'] ? false : $data['only_wholesale'] ?? false,

        ];

        DB::beginTransaction();
        try {
            $product = $this->productRepository->updateProduct($data['product_id'], $productData);
            $this->productRepository->attachRelations($product, 'categories', $data['productCategories']);
            $this->productRepository->updateTags($product, $data['productTags']);
            $this->updateFaqs($product->product_id, $data['faqQuestions'], $data['faqAnswers']);
            if (!empty($data['specification_titles']) && !empty($data['specification_values'])) {
                $this->updateSpecifications(
                    $product->product_id,
                    $data['specification_titles'],
                    $data['specification_values']
                );
            }
            if (!empty($data['properties'])) {
                $this->updateProperties($product->product_id, $data['properties']);
            }
            $product->shippingProfiles()->sync($data['shipping_option_id']);
            $deleteVariations = $data['product_variation_deleted'];

            $this->productRepository->updateVariations($product, $variationData, $productImages, $imagesGallery, $deleteVariations);
            $this->assignLabels($product->product_id);
            ProductHelpers::generateAiTagForProduct($product);
            DB::commit();
            $product->load('variations', 'categories', 'tags', 'faqs','specifications','properties');
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function validateVariationsData($variations)
    {
        $rules = [
            '*.variation_id' => 'nullable|integer',
            '*.price' => 'required|numeric|min:0',
            '*.current_stock' => 'required|integer|min:0',
            '*.sku' => 'nullable|string',
            '*.product_variation_image' => 'required',

            '*.option1' => 'required|string',
            '*.option2' => 'nullable|string',
            '*.option3' => 'nullable|string',

            '*.attribute1' => 'required|string',
            '*.attribute2' => 'nullable|string',
            '*.attribute3' => 'nullable|string',

            '*.unit1' => 'nullable|string',
            '*.unit2' => 'nullable|string',
            '*.unit3' => 'nullable|string',
        ];

        $validator = Validator::make($variations, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }
    public function getProductVariationImages($productId)
    {
        try {
            $product = $this->productRepository->getProductById($productId);
            $product->feature_image = [
                'id' => $product->feature_image,
                'url' => $this->getAttachmentUrlSmallById($product->feature_image) ?? null
            ];
            $product->load('variations', 'categories', 'tags', 'faqs');
            if ($product->variations->count() > 0) {
                foreach ($product->variations as $variation) {

                    $variation->product_variation_image = [
                        'id' => $variation->product_variation_image,
                        'url' => $this->getAttachmentUrlSmallById($variation->product_variation_image) ?? null
                    ];
                }
            }
            return $product;
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Product not found');
        }
    }
     public function changestatus($productId,$productStatus, $store_id,$p_status)
    {
        $this->productRepository->changestatus($productId,$productStatus, $store_id,$p_status);
    }

    public function getVariationsInfo($data)
    {
        $results = '';
        $results =  $this->productRepository->getVariationsInfo($data);
        try{
        $productImage = "";
            if (!empty($results)) {
                $product = ProductHelpers::checkCouponExists($results[0]['product_id'],'variant',$results[0]);
                $results[0]['coupon'] = $product['coupon'] ?? false;
               $product = DB::table('products')->where('product_id',$data['data']['productId'])->first();
               $imagePathId = isset($results[0]['product_variation_image']) ? $results[0]['product_variation_image'] : $product->feature_image;

                $path =  !empty($imagePathId)  ? getAttachmentUrlById((int) $imagePathId, 'small') : '';

            $webpUrl = webPImages($path,'small');

            if ($webpUrl) {
                $results[0]['product_variation_image'] = $webpUrl;
                $productImage = $webpUrl;
            }else{
                $results[0]['product_variation_image'] = $path;
                $productImage = $path;
            }
            // $results

            $results = array(
                'results' => $results,
                'productImage' => $productImage,

            );
                return $results;
            }
            else{
                throw new ItemNotFoundException('Product not found');
            }
        } catch (\Exception $e) {
            throw new ItemNotFoundException('Product not found');
        }
         
  
    }

    public function syncVariationTiers($data)
    {
        try {
            return $this->productRepository->syncVariationTiers($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
