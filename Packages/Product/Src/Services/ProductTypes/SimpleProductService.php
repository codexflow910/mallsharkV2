<?php

namespace Packages\Product\Services\ProductTypes;

use Illuminate\Support\Facades\DB;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Services\ProductService;

class SimpleProductService extends ProductService
{


    public function store(array $data)
    {
        $user_id = auth()->id();

        $store = $this->getStore();

        $store_id = $store->store_id;

        $productImages = $data['productImages'];
        $imagesGallery = [];
        $productFeaturedImage = '';

        foreach ($productImages as $image) {
            $attachmentId = $this->productRepository->getAttachmentId($image, 'image', true);
            $imagesGallery[] = $attachmentId;
        }
        $productFeaturedImage = isset($data['feature_image']) && !empty($data['feature_image']) ? $this->productRepository->getAttachmentId($data['feature_image'], 'image', true) : $imagesGallery[0];

        $status = ProductConstants::STATUS_PENDING;

        $productData = [
            'title' => $data['productTitle'],
            'slug' => $this->convert_to_slug(!empty($data['slug']) ? $data['slug'] : $data['productTitle']),
            'store_id' => $store_id,
            'user_id' => $user_id,
            'short_description' => $data['productShortDescription'],
            'description' => $data['productDescription'],
            'price' => $data['productPrice'],
            'current_stock' => $data['productcurrentstock'],
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
            'maxquantitylimit'=> $data['maxquantitylimit'],
        ];
        DB::beginTransaction();

        try {
            $product = $this->productRepository->storeProduct($productData);
            $this->productRepository->attachRelations($product, 'categories', $data['productCategories']);
            $this->productRepository->storeTags($product, $data['productTags']);
            $this->storeFaqs($product->product_id, $data['faqQuestions'], $data['faqAnswers']);
            
            if ($data['new_shipping']) {
                $product->shippingProfiles()->sync($data['shipping_option_id']);
            }

            DB::commit();

            $product->load('categories', 'tags', 'faqs');

            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $user_id = auth()->id();
        $store = $this->getStore();
        $store_id = $store->store_id;
        $featuredImage = $data['feature_image'];
        $productImages = $data['productImages'];
        $imagesGallery = [];
        $productFeaturedImage = '';

        foreach ($productImages as $image) {
            if (!is_numeric($image)) {
                $attachmentId = $this->productRepository->getAttachmentId($image, 'image', true);
                $imagesGallery[] = $attachmentId;
            } else {
                $attachmentId = $image;
                $imagesGallery[] = $attachmentId;
            }
        }
        if (isset($data['feature_image']) && !empty($data['feature_image'])) {
            if (!is_numeric($data['feature_image'])) {
                $productFeaturedImage = $this->productRepository->getAttachmentId($data['feature_image'], 'image', true);
            } else {
                $productFeaturedImage = $data['feature_image'];
            }
        } else {
            $productFeaturedImage = $imagesGallery[0];
        }

        $status = ProductConstants::STATUS_PENDING;

        $productData = [
            'title' => $data['productTitle'],
            'slug' => $this->convert_to_slug_for_update(!empty($data['slug']) ? $data['slug'] : $data['productTitle'], $data['product_id']),
            'store_id' => $store_id,
            'user_id' => $user_id,
            'short_description' => $data['productShortDescription'],
            'description' => $data['productDescription'],
            'price' => $data['productPrice'],
            'current_stock' => $data['productcurrentstock'],
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
        ];

        DB::beginTransaction();

        try {
            $product = $this->productRepository->updateProduct($data['product_id'], $productData);
            $this->productRepository->attachRelations($product, 'categories', $data['productCategories']);
            $this->productRepository->updateTags($product, $data['productTags']);
            $this->updateFaqs($product->product_id, $data['faqQuestions'], $data['faqAnswers']);
            $product->shippingProfiles()->sync($data['shipping_option_id']);

            DB::commit();
            $product->load('categories', 'tags', 'faqs');
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
