<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Packages\Product\Models\V2\Attachments;
use Packages\Product\Models\V2\AttachmentSizes;
use Packages\Product\Models\V2\Product;

class ProcessProductImagesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the unique lock should be maintained.
     *
     * @var int
     */
    public $uniqueFor = 1800; // 30 minutes

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes

    protected $productId;
    protected $imageNodes;
    protected $variantImageMap;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productId, $imageNodes, $variantImageMap = [])
    {
        $this->productId = $productId;
        $this->imageNodes = $imageNodes;
        $this->variantImageMap = $variantImageMap;
        $this->afterCommit = true;
    }

    /**
     * The unique ID of the job.
     * Prevents duplicate image processing for the same product.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'process_images_' . $this->productId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $product = Product::find($this->productId);

            if (!$product) {
                Log::error('Product not found for image processing', ['product_id' => $this->productId]);
                return;
            }

            // Skip if images are already processed (feature_image exists)
            if ($product->feature_image && $product->gallery) {
                Log::info('Product images already processed, skipping', [
                    'product_id' => $this->productId,
                    'feature_image' => $product->feature_image
                ]);
                return;
            }

            $images = [];
            $destinationPath = public_path() . '/uploads/';
            $tempDestinationPath = public_path() . '/uploads/temp/';
            $getDirectorywithSizes = $this->getAllAttachmentSizes();

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0775, true);
            }

            if (!File::exists($tempDestinationPath)) {
                File::makeDirectory($tempDestinationPath, 0775, true);
            }

            foreach ($this->imageNodes as $image) {
                $src = $image->transformedSrc;
                $OriginalfileName = pathinfo($src, PATHINFO_FILENAME);
                $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                $extension = explode('?', pathinfo($src, PATHINFO_EXTENSION));
                
                if (isset($extension[0])) {
                    $fileExtension = $extension[0];
                    $currentDate = date('ymd');
                    $fileName = $this->generateRandomString(15) . '_' . $currentDate . '.' . $fileExtension;
                    $client = new Client();

                    try {
                        $response = $client->get($src);
                        $tempPath = $tempDestinationPath . $fileName;
                        file_put_contents($tempPath, $response->getBody());

                        foreach ($getDirectorywithSizes as $key => $value) {
                            $directoryName = $value['name'];
                            $path = $destinationPath . $directoryName . '/' . $fileName;
                            $width = $value['width'];
                            $height = $value['height'];

                            if ($width == 0 && $height == 0) {
                                $readImage = file_get_contents($tempPath);
                                file_put_contents($path, $readImage);
                            } else {
                                $img = Image::make($tempPath);
                                $img->resize($width, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                })->save($path);
                            }
                        }

                        $images[$OriginalfileName] = $fileName;

                        // Don't delete temp file yet - getAttachmentId needs it
                        // It will be deleted by getAttachmentId when remove=true
                    } catch (\Exception $e) {
                        Log::error('Failed to process image', [
                            'product_id' => $this->productId,
                            'image_url' => $src,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            }

            // Update product with images
            if (count($images) > 0) {
                $productGallery = [];
                $productFeatureImage = null;
                $variationGallery = [];
                $counterImage = 0;

                foreach ($images as $key => $image) {
                    $attachment = $this->getAttachmentId($image, 'image', true); // remove=true to cleanup temp files
                    if ($counterImage == 0) {
                        $productFeatureImage = $attachment ? $attachment : null;
                    }
                    $productGallery[] = $attachment;
                    $variationGallery[$key] = $attachment; // Map by original filename for variations
                    $counterImage++;
                }

                if ($productFeatureImage) {
                    $product->feature_image = $productFeatureImage;
                }
                if (!empty($productGallery)) {
                    $product->gallery = serialize($productGallery);
                }

                // Update product status to active if it was pending due to missing images
                if ($product->status == 3) { // ProductConstants::STATUS_PENDING
                    $storeStatus = getStoreInfoById($product->store_id);
                    if ($storeStatus && $storeStatus[0]->super_status == 1 && $storeStatus[0]->store_status == 2) {
                        $product->status = 1; // ProductConstants::STATUS_ACTIVE
                    }
                }

                $product->save();

                // Update variation images if product has variants
                if ($product->product_type === 'variant') {
                    $this->updateVariationImages($product, $variationGallery, $productFeatureImage);
                }

                Log::info('Product images processed successfully', [
                    'product_id' => $this->productId,
                    'images_count' => count($images),
                    'variations_updated' => $product->product_type === 'variant'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('ProcessProductImagesJob failed', [
                'product_id' => $this->productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getAllAttachmentSizes()
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

    private function generateRandomString($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function getAttachmentId($file, $type = 'digital', $remove = false, $crop = true)
    {
        if ($file) {

                $getItem = $file;
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
                        unlink($getRealPath);
                    }
                    return $attachmentId;
                }
            }
        
    }

    /**
     * Update variation images based on the processed image gallery
     *
     * @param Product $product
     * @param array $variationGallery
     * @param mixed $productFeatureImage
     */
    private function updateVariationImages($product, $variationGallery, $productFeatureImage)
    {
        try {
            // Get all variations for this product
            $variations = \Packages\Product\Models\V2\ProductVariation::where('product_id', $product->product_id)->get();

            if ($variations->isEmpty()) {
                return;
            }

            $updatedCount = 0;

            // Map variation images based on SKU from variantImageMap
            foreach ($variations as $variation) {
                $variantSku = $variation->sku;

                // Check if this variant has a specific image URL from Shopify
                if (isset($this->variantImageMap[$variantSku])) {
                    $variantImageUrl = $this->variantImageMap[$variantSku];

                    // Find the matching attachment by comparing image URLs
                    $imageName = pathinfo($variantImageUrl, PATHINFO_FILENAME);

                    if (isset($variationGallery[$imageName])) {
                        $attachmentId = $variationGallery[$imageName];
                        $variation->product_variation_image = $attachmentId;
                        $variation->save();
                        $updatedCount++;

                        Log::info('Variation image mapped from Shopify', [
                            'variation_id' => $variation->variation_id,
                            'sku' => $variantSku,
                            'attachment_id' => $attachmentId
                        ]);
                    }
                } else {
                    // If no specific image for this variant, use product feature image
                    if (empty($variation->product_variation_image)) {
                        $variation->product_variation_image = $productFeatureImage;
                        $variation->save();
                        $updatedCount++;

                        Log::info('Variation assigned feature image (no specific variant image)', [
                            'variation_id' => $variation->variation_id,
                            'sku' => $variantSku,
                            'attachment_id' => $productFeatureImage
                        ]);
                    }
                }
            }

            Log::info('Variation images updated', [
                'product_id' => $product->product_id,
                'variations_updated' => $updatedCount,
                'total_variants' => $variations->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update variation images', [
                'product_id' => $product->product_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
