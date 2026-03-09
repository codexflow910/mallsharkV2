<?php

namespace Packages\Product\Services\ProductTypes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Packages\Product\Config\ProductConstants;
use Packages\Product\Repositories\Eloquent\ProductRepository;
use Packages\Product\Services\ProductService;


class VariableProductService extends ProductService
{




    public function store(array $data)
    {

        $user_id = auth()->id();

        $store = $this->getStore();

        $store_id = $store->store_id;
        $featuredImage = $data['feature_image'];
        $productImages = $data['productImages'];
        $imagesGallery = [];
        $productFeaturedImage = '';


        foreach ($productImages as $image) {
            $attachmentId = $this->productRepository->getAttachmentId($image, 'image', true);
            $imagesGallery[] = $attachmentId;
        }
        if(isset($data['feature_image']) && !empty($data['feature_image'])){
            if(!is_numeric($data['feature_image'])){
                $productFeaturedImage = $this->productRepository->getAttachmentId($data['feature_image'], 'image', true);
            }else{
                $productFeaturedImage = $data['feature_image'];
            }
        }else{
            $productFeaturedImage = $imagesGallery[0];
        }
        $status = ProductConstants::STATUS_PENDING;
        $current_stock_total = array_sum($data['ProductvariationData']['current_stock']);

        $productData = [
            'title' => $data['productTitle'],
            'slug' => $this->convert_to_slug(!empty($data['slug']) ? $data['slug'] : $data['productTitle']),
            'store_id' => $store_id,
            'user_id' => $user_id,
            'short_description' => $data['productShortDescription'],
            'description' => $data['productDescription'],
            'price' => $data['productPrice'] ?? $data['ProductvariationData']['price'][0],
            // 'current_stock' => $data['productcurrentstock'] ?? $data['ProductvariationData']['current_stock'][0],
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
            $validatedVariations =  $this->validateVariationsData($data['ProductvariationData']);

            $this->productRepository->storeVariations($product, $validatedVariations,$productImages,$imagesGallery);

            DB::commit();


            $product->load('variations', 'categories', 'tags', 'faqs');
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

        $productImages = $data['productImages'];
        $imagesGallery = [];
        $productFeaturedImage = '';
        foreach ($productImages as $image) {
            if (!is_numeric($image)) {
                // new image uploaded
                $attachmentId = $this->productRepository->getAttachmentId($image, 'image', true);
                $imagesGallery[] = $attachmentId;
            } else {
                //old image exists
                $attachmentId = $image;
                $imagesGallery[] = $attachmentId;
            }
        }

        if(isset($data['feature_image']) && !empty($data['feature_image'])){
            if(!is_numeric($data['feature_image'])){
                $productFeaturedImage = $this->productRepository->getAttachmentId($data['feature_image'], 'image', true);
            }else{
                $productFeaturedImage = $data['feature_image'];
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

        $current_stock_total = array_sum($variationData['current_stock']);
        $productQuantity = $current_stock_total ?? $data['productcurrentstock'];
        if (!empty($variationData) && count($variationData) > 0) {
            $priceFlag = false;
            $quantityFlag = false;
            foreach ($variationData['price'] as $key => $price) {
                if (!empty($variationData['price'][$key]) && $variationData['price'][$key] > 0) {
                    if (!$priceFlag) {
                        $productPrice = $variationData['price'][$key];
                        $priceFlag = true;
                    }
                }

                if (!empty($variationData['sku'][$key])) {
                    $productSKU = $variationData['sku'][$key];
                }

                // if (!empty($variationData['current_stock'][$key]) && $variationData['current_stock'][$key] > 1) {
                //     if (!$quantityFlag) {
                //         $productQuantity = $variationData['current_stock'][$key];
                //         $quantityFlag = true;
                //     }
                // }

                // if ($priceFlag && $quantityFlag) {
                //     break;
                // }
                if ($priceFlag) {
                    break;
                }
            }
        }

        $status = ProductConstants::STATUS_PENDING;
    
        $productData = [
            'title' => $data['productTitle'],
            'slug' => $this->convert_to_slug_for_update(!empty($data['slug']) ? $data['slug'] : $data['productTitle'], $data['product_id']),
            'store_id' => $store_id,
            'user_id' => $user_id,
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
        ];

        DB::beginTransaction();

        try {
            $product = $this->productRepository->updateProduct($data['product_id'], $productData);
            $this->productRepository->attachRelations($product, 'categories', $data['productCategories']);
            $this->productRepository->updateTags($product, $data['productTags']);
            $this->updateFaqs($product->product_id, $data['faqQuestions'], $data['faqAnswers']);

            $validatedVariations = $this->validateVariationsData($data['ProductvariationData']);
            $this->productRepository->updateVariations($product, $validatedVariations, $productImages, $imagesGallery, $data['ProductvariationData']['product_variation_deleted'], $data['ProductvariationData']['product_variation_discount']);

            DB::commit();
            $product->load('variations', 'categories', 'tags', 'faqs');
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function validateVariationsData($variations)
    {
        //  $variations = array_map(function ($variation) {
        //     return array_merge($variation, [
        //         'option2' => empty($variation['attribute2']) ? array_fill(0, count($variation['option1']), null) : $variation['option2'],

        //     ]);
        // }, $variations);

        $rules = [
            'option1' => 'required|array|min:1',
            'option1.*' => 'string',
            'attribute1' => 'required|array|min:1',
            'attribute1.*' => 'string',
            'unit1' => 'nullable|array',
            'unit1.*' => 'nullable|string',

            'option2' => 'required_if:attribute2,!=,[]|array|min:1',
            'option2.*' => 'required_if:attribute2,!=,[]|nullable|string',
            'attribute2' => 'nullable|array',
            'attribute2.*' => 'nullable|string',
            'unit2' => 'nullable|array',
            'unit2.*' => 'nullable|string',

            'option3' => 'required_if:attribute3,!=,[]|array|min:1',
            'option3.*' => 'required_if:attribute2,!=,[]|nullable|string',
            'attribute3' => 'nullable|array',
            'attribute3.*' => 'nullable|string',
            'unit3' => 'nullable|array',
            'unit3.*' => 'nullable|string',

            'price' => 'required|array|min:1',
            'price.*' => 'numeric',
            'sku' => 'array',
            'sku.*' => 'nullable|string',
            'current_stock' => 'array',
            'current_stock.*' => 'integer',
            'product_variation_image' => 'required|array|min:1',
            'product_variation_image.*' => 'required',
        ];

        // Validate the variations data
        $validator = Validator::make($variations, $rules);
        $validator->after(function ($validator) use ($variations) {
            $option1Count = count($variations['option1'] ?? []);
            $option2Count = count($variations['option2'] ?? []);
            $option3Count = count($variations['option3'] ?? []);

            $priceCount = count($variations['price'] ?? []);
            $stockCount = count($variations['current_stock'] ?? []);
            $imageCount = count($variations['product_variation_image'] ?? []);

            if ($option1Count !== $priceCount || $option1Count !== $stockCount || $option1Count !== $imageCount) {
                $validator->errors()->add('prices', 'All variations must have price, current stock, and product variation image.');
            }
            if ($option1Count !== $option2Count || $option1Count !== $option3Count) {
                $validator->errors()->add('option_count', 'Options 1, 2, and 3 must have the same number of elements.');
            }
        });
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
               $product = DB::table('products')->where('product_id',$data['data']['productId'])->first();
               $imagePathId = isset($results[0]['product_variation_image']) ? $results[0]['product_variation_image'] : $product->feature_image;

                $path =  !empty($imagePathId)  ? getAttachmentUrlById((int) $imagePathId, 'small') : '';

            $webpUrl = webPImages($path,'small');

            if ($webpUrl) {
                $productImage = $webpUrl;
            }else{
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

}
