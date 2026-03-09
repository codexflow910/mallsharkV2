<?php

namespace Packages\Product\Services\V2\ProductTypes;

use Illuminate\Support\Facades\DB;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Services\V2\ProductService;
use Packages\Product\Contracts\V2\ProductHelpers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ItemNotFoundException;
use Packages\Product\Models\V2\{ProductAiTags};
use Packages\Store\Services\V2\StoreSetupService;
use Packages\Product\Models\V2\Product;
use App\Http\Controllers\MailgunController;

class SimpleProductService extends ProductService
{
    use ProductHelpers;

    public function store(array $data)
    {
        $user_id = auth()->id();
        $store = ProductHelpers::getAccessibleStore($user_id);  
        if (!$store) {
            throw new ItemNotFoundException('User does not belong or have access to any store.');
        }
        $storeCountry = $store->store_country; // country_id
        $hsCode = $data['HS_code'] ?? null;
        
        
        // If seller is NOT from US (1 = US)
        if ($storeCountry !== 1) {

            if (empty($hsCode)) {
                throw new \Exception('HS Code is required for sellers outside the US.');
            }

        }
        $store_id = $store->store_id;
        $productImages = $data['productImages'];
        $productImagesAlt = $data['productImagesAlt'] ?? [];
        $featureImageAlt = $data['featureImageAlt'] ?? [];
        $imagesGallery = [];
        $productFeaturedImage = '';

        foreach ($productImages as $index => $image) {
            $alt = $productImagesAlt[$index] ?? null; 
            $attachmentId = $this->productRepository->getAttachmentId($image, 'image', true, true, $alt);
            $imagesGallery[] = $attachmentId;
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

        $productData = [
            'title' => $data['productTitle'],
            'slug' => $slug,
            'store_id' => $store_id,
            'user_id' => $store->user_id,
            'short_description' => $data['productShortDescription'],
            'description' => $data['productDescription'],
            'price' => $data['productPrice'],
            'current_stock' => $data['productcurrentstock'],
            'sku' => $data['productSKU'],
            'HS_code' => $data['HS_code'] ?? null,
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
            'maxquantitylimit'=> $data['maxquantitylimit'],
            'brand_distributor_id' => $data['brand_distributor_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'unique_product' => $data['unique_product'] ?? false,
            'is_custom' => $data['is_custom'] ?? false,
        ];
        DB::beginTransaction();
        try {
            $product = $this->productRepository->storeProduct($productData);
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
            if ($data['new_shipping']) {
                $product->shippingProfiles()->sync($data['shipping_option_id']);
            }
            $slug = $this->createSlug($store->user_id, $childCategory, $product->product_id);
            $product->slug = $slug;
            $product->save();
            if ($data['new_shipping']) {
                $product->shippingProfiles()->sync($data['shipping_option_id']);
            }
            $this->assignLabels($product->product_id);
            
            try {
                $status = ProductConstants::STATUS_DELETED;
                $count = Product::where('store_id', $store_id)
                ->where('status', '!=', $status)
                ->count();
                if ($count === 1) {
                    $setupSvc = new StoreSetupService(app(\Packages\Store\Repositories\Eloquent\V2\StoreRepository::class)
                    , new MailgunController()
                    );
                    $setupSvc->productAdded($product);
                }
            } catch (\Exception $e) {
                throw $e;
            }
            DB::commit();
            ProductHelpers::generateAiTagForProduct($product);

            $product->load('categories', 'tags', 'faqs', 'specifications');
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
        $storeCountry = $store->store_country; // country_id
        $hsCode = $data['HS_code'] ?? null;
        // If seller is NOT from US (1 = US)
        if ($storeCountry !== 1) {

            if (empty($hsCode)) {
                throw new \Exception('HS Code is required for sellers outside the US.');
            }
        }
        $featuredImage = $data['feature_image'];
        $productImages = $data['productImages'];
        $imagesGallery = [];
        $productFeaturedImage = '';

        foreach ($productImages as $index => $image) {
            if (!is_numeric($image)) {
                $alt = $data['productImagesAlt'][$index] ?? null;
                $attachmentId = $this->productRepository->getAttachmentId($image, 'image', true, true, $alt);
                $imagesGallery[] = $attachmentId;
            } else {
                $attachmentId = $image;
                $alt = $data['productImagesAlt'][$index] ?? null;
                ProductHelpers::updateImageAlt($attachmentId, $alt);
                $imagesGallery[] = $attachmentId;
            }
        }
        if (isset($data['feature_image']) && !empty($data['feature_image'])) {
            if (!is_numeric($data['feature_image'])) {
                $alt = $data['featureImageAlt'][0] ?? null;
                $productFeaturedImage = $this->productRepository->getAttachmentId($data['feature_image'], 'image', true, true, $alt);
            } else {
                $productFeaturedImage = $data['feature_image'];
                ProductHelpers::updateImageAlt($productFeaturedImage, $data['featureImageAlt'][0] ?? null);
            }
        } else {
            $productFeaturedImage = $imagesGallery[0];
        }
        
        $status = ProductConstants::STATUS_PENDING;
        $childCategory= $data['productCategories'][1] ?? $data['productCategories'][0];
        $slug = $this->createSlug($store->user_id, $childCategory, $data['product_id']);
        $productData = [
            'title' => $data['productTitle'],
            'slug' => $slug,
            'store_id' => $store_id,
            'user_id' => $store->user_id,
            'short_description' => $data['productShortDescription'],
            'description' => $data['productDescription'],
            'price' => $data['productPrice'],
            'current_stock' => $data['productcurrentstock'],
            'sku' => $data['productSKU'],
            'HS_code' => $data['HS_code'] ?? null,
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
            'shipping_option_id' => !empty($data['new_shipping']) ? null : $data['shipping_option_id'][0],
            'new_shipping' => $data['new_shipping'] ?? false,
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

            if ($data['new_shipping']) {
                $product->shippingProfiles()->sync($data['shipping_option_id']);
            }
            $this->assignLabels($product->product_id);

            DB::commit();
            ProductHelpers::generateAiTagForProduct($product);
            $product->load('categories', 'tags', 'faqs');
            $product->searchable();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function changestatus($productId, $productStatus, $store_id)
    {
        $this->productRepository->changestatus($productId, $productStatus, $store_id);
    }

}
