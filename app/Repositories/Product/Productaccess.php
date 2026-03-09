<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Product;

use Request;
use Session;
// use Image;
use Exception;
use App\BaseAccess;
use App\Constants;
use App\Models\Store;
use App\Models\Product;
use App\Models\{category, DeviceToken, Discount, User};
use App\Models\ProductFaq;
use App\Models\Attachments;
use App\Models\ProcessTime;
use App\Models\ProductTags;
use App\Models\DigitalFiles;
use App\Models\ShippingOption;
//use App\models\ShoppingOption;
use App\Repositories\Formulas;
use App\Models\AttachmentSizes;
use App\Models\CategoryProduct;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Facade\Ignition\DumpRecorder\Dump;
use App\Http\Controllers\MailgunController;
use App\Http\Controllers\ElasticSearchController;
use App\Services\FCMService;
use App\Services\SellerFcmService;
use Monolog\Logger;

use function PHPUnit\Framework\returnSelf;

/**
 * Description of Productaccess
 *
 * @author Ejaz
 */
class Productaccess extends BaseAccess
{

    private $_product;
    private $_esearch;
    private $mailgun;

    public function __construct()
    {
        $this->_product = new Product();
        $this->_esearch = new ElasticSearchController();
        $this->mailgun = new MailgunController();
    }

    public function generateRandomString($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getVariationAttributes()
    {
        return DB::table('variation_attributes')->where('access', '=', 'global')->get()->toArray();
    }

    /**
     *  Function for get product  related information
     *
     */
    public function getProductByID($productID)
    {
        $productInfo = array();
        $product = DB::table('products')->where('product_id', $productID)->first();
       
        $productInfo['product_id'] = $product->product_id;
        $productInfo['title'] = $product->title;
        // $productInfo['renewalOption'] = $product->renewal_option;
        $productInfo['product_type'] = $product->product_type;
        $productInfo['item_condition'] = isset($product->item_condition) ? $product->item_condition : null;
        $productInfo['aboutCreation'] = @unserialize($product->about_creation);
        $productInfo['description'] = $product->description;
        $productInfo['price'] = $product->price;
        $productInfo['eco_friendly'] = $product->eco_friendly;
        // $productInfo['receiveBackPrice'] = $product->receiveBackPrice;
        $productInfo['current_stock'] = $product->current_stock;
        $productInfo['sku'] = $product->sku;
        $productInfo['weight'] = $product->weight;
        $productInfo['status'] = $product->status;
        //$productInfo['visibility'] = $product->visibility;
        $productInfo['featureImage'] = $product->feature_image;
        $productInfo['gallery'] = @unserialize($product->gallery);
        $productInfo['shortDescription'] = $product->short_description;
        $productInfo['productSizeLength'] = $product->productSize_length;
        $productInfo['productSizeWidth'] = $product->productSize_width;
        $productInfo['productSizeHeight'] = $product->productSize_height;
        $productInfo['faqs'] = DB::table('product_faqs')->where('product_id', $productID)->get()->toArray();
        $productInfo['tags'] = DB::table('product_tags')->where('product_id', $productID)->get()->toArray();
        $productInfo['user_id'] = $product->user_id;
        $productInfo['store_id'] = $product->store_id;
        $productInfo['shipping_option_id'] = $product->shipping_option_id;

        $productInfo['user_revenue'] = $product->user_revenue;
        $productInfo['cashback_pool'] = $product->cashback_pool;
        $productInfo['supersupporter_pool'] = $product->supersupporter_pool;
        $productInfo['video_url'] = $product->video_url;
        $productInfo['weight_type'] = $product->weight_type;
        $productInfo['dimention_type'] = $product->dimention_type;
        $productInfo['video_url'] = $product->video_url;

        $productInfo['category'] = DB::table('product_category')->where('product_id', $productID)->get()->toArray();
        //if($product->product_type == 'virtual') {
        $productInfo['digital_files'] = DB::table('prduct_digital_files')->where('product_id', $productID)->get()->toArray();
        $shippingProfile = DB::table('shipping_options')->where('id', $product->shipping_option_id)->first();
        
        if (isset($shippingProfile) && !is_null($shippingProfile)) {
            $productInfo['shipping_method'] = $shippingProfile;
        } else {
            $shippingProfile = DB::table('shipping_options')->where('profile_name', 'Free Shipping')->where('user_id', null)->first();
            $productInfo['shipping_method'] = $shippingProfile;
            $productInfo['shipping_option_id'] = $shippingProfile->id;
        }
        // 
        //        echo '<pre>';
        //        print_r($productInfo);
        //        echo '</pre>';
        //       die;
        return $productInfo;
    }

    /**
     *  Function for upload Image
     *
     */
    public function uploadProcessProductImage($productsimages)
    {

        $productGallery = array();
        if ($productsimages) {
            if (count($productsimages) > 0) {
                $destinationPath = public_path() . '/uploads/temp';
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }
                foreach ($productsimages as $image) {
                    // dd($image);
                    $OriginalfileName = $image->getClientOriginalName();
                    $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                    $fileExtension = $image->getClientOriginalExtension();
                    // $data = getimagesize($image);
                    // $width = $data[0];
                    // $height = $data[1];
                    // if ($width < 720 || $height < 480) {
                    //     $status = FALSE;
                    // } else {
                    $status = true;
                    // }
                    $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                    $image->move($destinationPath, $fileName);
                    $productGallery['fileName'][] = $fileName;
                    $productGallery['validate'][] = $status;
                }
            }
        }
        return $productGallery;
    }
    /**
     *  Function for upload Process Digital Item
     *
     */
    public function deleteProductFileRequest($file)
    {

        $fileName = $file['fileName'];
        $destinationPath = public_path() . '/uploads/temp/' . $fileName;
        if (File::exists($destinationPath)) {

            unlink($destinationPath);

            return $resposne = 'Image Deleted Successfully';
        } else {
            return $resposne = 'Item Not Found';
        }
    }

    /**
     *  Function for upload Process Digital Item
     *
     */
    public function deleteProductTemRequest($file)
    {
        //   return $file;
        $fileName = $file;
        $destinationPath = public_path() . '/uploads/temp/' . $fileName['imageName'];
        // dd($destinationPath);
        if (File::exists($destinationPath)) {
            // dd($destinationPath);
            // return response()->json(['captured'=>$destinationPath]);
            unlink($destinationPath);

            return response()->json([
                'status' => true,
                'message' => 'Image deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'image not found'
            ]);
        }
    }

    /*
     * * funcation deleteProductImageRequest
     * Used For Delete Upload Image
     */

    public function deleteProductImageRequest($data)
    {
        // return $data['imageId'];
        // dd(12);
        // return $data;
        $imageid = $data['imageId'];
        $productId = $data['productId'];
        // return $imageid;
        $attachment = Attachments::find($imageid);
        // return $attachment;
        if (!$attachment) {
            return $response = 'Attachment Not Found';
        }

        $fileName = $attachment->fileName;
        $fileExtension = $attachment->fileExtension;
        $completeFileName = $fileName . '_' . $imageid . '.' . $fileExtension;

        $destinationPath = public_path() . '/uploads/media/' . $completeFileName;

        if (File::exists($destinationPath)) {
            unlink($destinationPath);
            $attachment->delete();

            // Retrieve and update the serialized array in the Product table
            $product = Product::find($productId);

            if ($product) {
                $gallery = unserialize($product->gallery);
                $index = array_search($imageid, $gallery);

                if ($index !== false) {
                    unset($gallery[$index]);
                }

                $product->gallery = serialize($gallery);
                $product->save();

                $galleryproduct = Product::find($productId);
                $galleryImages = unserialize($galleryproduct->gallery);
                $feature_image = $product->feature_image;

                if ($feature_image == $imageid) {
                    $firstIteration = true;
                    foreach ($galleryImages as $key => $value) {

                        if ($firstIteration) {
                            DB::table('products')->where('product_id', $productId)->update(['feature_image' => $value]);

                            $firstIteration = false;
                        }
                    }
                }

                return $response = 'Image Deleted Successfully';
            } else {
                return $response = 'Product Not Found';
            }
        } else {
            return $response = 'Image Not Found';
        }
    }

    public function deleteEditProductFeatureImage($data)
    {
        try {
            // Get imageId and productId from $data
            $imageId = $data['imageId'];
            $productId = $data['productId'];

            // Find the attachment
            $attachment = Attachments::find($imageId);

            if (!$attachment) {
                return 'Attachment Not Found';
            }

            $product = Product::find($productId);
            if ($product) {
                $gallery = unserialize($product->gallery);
                if (!empty($gallery)) {

                    $firstIteration = true;
                    foreach ($gallery as $key => $value) {
                        // dd($value);
                        if ($firstIteration) {
                            DB::table('products')->where('product_id', $productId)->update(['feature_image' => $value]);
                            return response([
                                'imageData' => getAttachmentUrlById((int) $value, 'media'),
                                'message' => 'Product Thumbnail Deleted Successfully',
                            ]);
                            $firstIteration = false;
                        }
                    }
                } else {
                    return 'Gallery is empty';
                }
            } else {
                return 'Product Not Found';
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }


    public function UpdateSelectedFeatureImage($data)
    {
        try {
            // Get imageId and productId from $data
            $imageId = $data['imageId'];
            $productId = $data['productId'];

            // Find the attachment
            $attachment = Attachments::find($imageId);

            if (!$attachment) {
                return 'Attachment Not Found';
            }

            $product = Product::find($productId);
            if ($product) {
                DB::table('products')->where('product_id', $productId)->update(['feature_image' => $imageId]);

                return response([
                    'ImageData' => getAttachmentUrlById((int) $imageId, 'media'),
                    'message' => 'Product Thumbnail Updated Successfully',
                ]);
            } else {
                return 'Product Not Found';
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }


    // Bannner image delete
    public function deleteBannerImageFile($file)
    {
        $storeId = $file['storeid'];
        $storeData = DB::table('stores')->where('store_id', $storeId)->get()->toArray();

        $bannerSerialize = $storeData[0]->banner_image_id;
        $bannerIds = unserialize($bannerSerialize);

        $fileName = $file['filename'];
        $parts = parse_url($fileName); // Parse the URL into its components
        $path = $parts['path'];   // Get the path component
        $mediaPosition = strpos($path, '/media/');

        if ($mediaPosition !== false) {
            $mediaSubPath = substr($path, $mediaPosition + strlen('/media/'));
            $mediaSubPath;
        } else {
            $mediaSubPath = $fileName;
        }

        $destinationPath = public_path() . '/uploads/media/' . $mediaSubPath;
        if (File::exists($destinationPath)) {
            unlink($destinationPath);
            DB::table('attachment')->where('attachment_id', $file['imageID'])->delete();

            // Find and remove the image ID from the $bannerIds array
            $index = array_search($file['imageID'], $bannerIds);
            if ($index !== false) {
                unset($bannerIds[$index]);
            }

            // Update the serialized array in the stores table
            $updatedBannerSerialize = serialize($bannerIds);
            DB::table('stores')->where('store_id', $storeId)->update(['banner_image_id' => $updatedBannerSerialize]);

            return $response = 'Image Deleted Successfully';
        } else {
            $index = array_search($file['imageID'], $bannerIds);
            if ($index !== false) {
                unset($bannerIds[$index]);
            }
            $updatedBannerSerialize = serialize($bannerIds);
            DB::table('stores')->where('store_id', $storeId)->update(['banner_image_id' => $updatedBannerSerialize]);
            return $response = 'Item Not Found';
        }
    }
    // Bannner image delete When store create
    public function deleteBannerImageFileCreate($file)
    {
        //  dd($file);

        $bannerSerialize = $file['serialize_images'];
        //    $storeData = DB::table('stores')->where('store_id', $storeId)->get()->toArray();
        //    $bannerSerialize = $storeData[0]->banner_image_id;
        $response = DB::table('attachment')->where('attachment_id', $file['imageID'])->delete();
        $bannerIds = unserialize($bannerSerialize);
        $index = array_search($file['imageID'], $bannerIds);
        if ($index !== false) {
            unset($bannerIds[$index]);
        }

        // Update the serialized array in the stores table
        $updatedBannerSerialize = serialize($bannerIds);
        //    dd($updatedBannerSerialize);
        if ($response > 0) {
            return response()->json([
                'status' => true,
                'message' => 'Image Deleted Successfully',
                'data' => $updatedBannerSerialize,
            ], 200);
        } else {
            return $response = 'Item Not Found';
        }

        //    $fileName = $file['filename'];
        //    $destinationPath = public_path() . '/uploads/media/' . $fileName;
        //    if (File::exists($destinationPath)) {
        //        unlink($destinationPath);

        // Find and remove the image ID from the $bannerIds array

        //    DB::table('stores')->where('store_id', $storeId)->update(['banner_image_id' => $updatedBannerSerialize]);

        //        return $response = 'Image Deleted Successfully';
        //    } else {
        //        return $response = 'Item Not Found';
        //    }
    }

    // Bannner image update
    public function updateBannerImageFile($file)
    {
        $storeId = $file['storeid'];
        $storeData = DB::table('stores')->where('store_id', $storeId)->get()->toArray();
        $bannerSerialize = $storeData[0]->banner_image_id;
        $bannerIds = unserialize($bannerSerialize);

        $fileName = $file['filename'];
        $destinationPath = public_path() . '/uploads/media/' . $fileName;
        if (File::exists($destinationPath)) {
            unlink($destinationPath);
            DB::table('attachment')->where('attachment_id', $file['imageID'])->delete();

            // Find and remove the image ID from the $bannerIds array
            $index = array_search($file['imageID'], $bannerIds);
            if ($index !== false) {
                unset($bannerIds[$index]);
            }

            // Update the serialized array in the stores table
            $updatedBannerSerialize = serialize($bannerIds);
            DB::table('stores')->where('store_id', $storeId)->update(['banner_image_id' => $updatedBannerSerialize]);

            return $response = 'Item Deleted';
        } else {
            return $response = 'Item Not Found';
        }
    }


    public function uploadTempFiles($files, $type, $path)
    {
        $itemArray = array();
        if ($files) {
            if (count($files) > 0) {
                $destinationPath = public_path() . '/uploads/temp';
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }
                foreach ($files as $item) {
                    $OriginalfileName = $item->getClientOriginalName();
                    $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                    $fileExtension = $item->getClientOriginalExtension();
                    switch ($type) {
                        case 'file':
                            $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                            $moved = $item->move($destinationPath, $fileName);

                            $itemArray['fileName'][] = $fileName;
                            break;

                        case 'image':
                            $data = getimagesize($item);
                            $width = $data[0];
                            $height = $data[1];
                            if ($width < 720 || $height < 480) {
                                $status = false;
                            } else {
                                $status = true;
                            }
                            $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                            $item->move($destinationPath, $fileName);
                            $itemArray['fileName'][] = $fileName;
                            $itemArray['validate'][] = $status;
                            break;

                        default:
                            break;
                    }
                }
            }
        }
        return $itemArray;
    }
    // Backup function for image upload without sizes
    // public function getAttachmentId($file, $type = 'digital', $remove = false, $crop = true) {

    //     if ($file) {
    //         $arrayItems = explode("--_--", $file);
    //         if (is_array($arrayItems)) {
    //             $getItem = $arrayItems[1];
    //             $fileAttr = pathinfo($getItem);

    //             $nameItemOnly = $fileAttr['filename'];
    //             $fileExtension = $fileAttr['extension'];
    //             $attachments = new Attachments();
    //             $attachments->fileName = $nameItemOnly;
    //             $attachments->fileExtension = $fileExtension;
    //             $attachments->save();
    //             $attachmentId = $attachments->attachment_id;
    //             if ($attachmentId) {
    //                 $getRealPath = public_path() . '/uploads/temp/' . $file;
    //                 $fileName = $nameItemOnly . '_' . $attachmentId . '.' . $fileExtension;
    //                 switch ($type) {
    //                     case 'digital':
    //                         $destinationPath = public_path() . '/uploads/digital';
    //                         if (!File::exists($destinationPath)) {
    //                             File::makeDirectory($destinationPath, 0775, true);
    //                         }
    //                         $readFile = file_get_contents($getRealPath);
    //                         file_put_contents($destinationPath . '/' . $fileName, $readFile);

    //                         break;
    //                     case 'image':
    //                         $getDirectorywithSizes = $this->getAllAttachmentSizes();
    //                         foreach ($getDirectorywithSizes as $key => $value) {
    //                             $directoryName = $value['name'];
    //                             $width = $value['width'];
    //                             $height = $value['height'];
    //                             $destinationPath = public_path() . '/uploads/' . $directoryName;
    //                             if ($width == 0 && $height == 0) {
    //                                 $readImage = file_get_contents($getRealPath);
    //                                 file_put_contents($destinationPath . '/' . $fileName, $readImage);
    //                             } else {
    //                                 if ($crop) {

    //                                     $img = Image::make($getRealPath);
    //                                     $img->fit($width, $height, function ($constraint) {
    //                                         $constraint->upsize();
    //                                     });
    //                                     $img->save($destinationPath . '/' . $fileName);
    //                                 } else {

    //                                     $img = Image::make($getRealPath);
    //                                     $img->resize($width, $height, function ($constraint) {
    //                                         $constraint->aspectRatio();
    //                                     })->save($destinationPath . '/' . $fileName);
    //                                 }
    //                             }
    //                         }
    //                         break;

    //                     default:
    //                         break;
    //                 }
    //                 if ($remove) {
    //                     unlink($getRealPath);
    //                 }

    //                 return $attachmentId;
    //             }
    //         }
    //     }
    // }

    public function getAttachmentId($file, $type = 'digital', $remove = false, $crop = true)
    {
        if ($file) {
            // dd($file);
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

    public function getAttachmentIdCustom($file, $type = 'digital', $remove = false, $crop = true)
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
                    $fileName = $nameItemOnly . '.' . $fileExtension;
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

    /*
     * * Get Category by Type
     */
    public function getParentWithCildCategories()
    {
        // Fetch parent categories with child categories
        $categories = Category::with('childCategories')->where('parentCategoryID', 0)->get();
        return $categories;
    }
    public function getAllCategory($type, $parentID)
    {

        $ObjeCatogry = new category();

        $getAllProductCategory = $ObjeCatogry->all()
            ->where('categoryType', $type)
            ->where('parentCategoryID', $parentID)
            ->where('categoryTitle', '!=', 'Uncategorized') // Exclude category with the title 'Uncategorized'
            ->toArray();

        return $getAllProductCategory;
    }

    /**
     *  Function for Add Variation of Product
     *
     */
    public function addProductVariation($data, $ProductInfo, $Query)
    {
        // dd($ProductInfo);

        try {
            DB::beginTransaction();
            if ($data) {
                $counterCheck = 1;
                foreach ($data['option1'] as $key => $value) {
                    $variationProduct = new ProductVariation();
                    $variationProduct->product_id = $ProductInfo['productID'];
                    $variationProduct->maxquantitylimit = isset($ProductInfo['maxquantitylimit']) ? $ProductInfo['maxquantitylimit'] : 10;
                    if (isset($data['attribute1'])) {
                        $variationProduct->attribute1 = $data['attribute1'][0];
                    }
                    if (isset($data['attribute2'])) {
                        $variationProduct->attribute2 = $data['attribute2'][0];
                    }
                    if (isset($data['attribute3'])) {
                        $variationProduct->attribute3 = $data['attribute3'][0];
                    }
                    if (isset($data['unit1'])) {
                        $variationProduct->unit1 = $data['unit1'][0];
                    }
                    if (isset($data['unit2'])) {
                        $variationProduct->unit2 = $data['unit2'][0];
                    }
                    if (isset($data['unit3'])) {
                        $variationProduct->unit3 = $data['unit3'][0];
                    }
                    if (isset($ProductInfo['$product_variation_image']) && !empty($ProductInfo['$product_variation_image'])) {
                        // Check if the specific variation image exists
                        $variationProduct->product_variation_image = isset($ProductInfo['$product_variation_image'][$key])
                            ? $ProductInfo['$product_variation_image'][$key]
                            : (isset($ProductInfo['feature_image']) ? $ProductInfo['feature_image'] : null);
                    }
                    if (isset($data['option1'])) {
                        $variationProduct->option1 = $data['option1'][$key];
                    }
                    if (isset($data['option2'])) {
                        // dump($data['option2'][$key]);
                        $variationProduct->option2 = $data['option2'][$key];
                    }
                    if (isset($data['option3'])) {
                        $variationProduct->option3 = $data['option3'][$key];
                    }
                    $sales = isset($data['sales'][$key]) ? $data['sales'][$key] : 0;
                    if (isset($data['variationID'][$key])) {
                        $variationProduct->variation_id = $data['variationID'][$key];
                    }
                    $variationProduct->check_box_price1 = 1;
                    $variationProduct->check_box_price2 = 1;
                    $variationProduct->check_box_quantity1 = 1;
                    $variationProduct->check_box_quantity2 = 1;
                    $variationProduct->check_box_sku1 = 1;
                    $variationProduct->check_box_sku2 = 1;

                    $price = isset($data['price'][$key]) ? $data['price'][$key] : 0;

                    $sku = isset($data['sku'][$key]) ? $data['sku'][$key] : '';
                    $currentStock = isset($data['current_stock'][$key]) ? $data['current_stock'][$key] : 0;
                    $variationProduct->price = $price;
                    $variationProduct->sku = $sku;
                    $variationProduct->current_stock = $currentStock;
                    $variationProduct->sales = $sales;
                    if (isset($data['visibility'])) {

                        if (in_array($counterCheck, $data['visibility'])) {

                            $variationProduct->visibility = 1;
                        } else {

                            $variationProduct->visibility = 0;
                        }
                        $counterCheck++;
                    } else {
                        $variationProduct->visibility = 1;
                    }
                    $discount = Discount::where(['store_id' => $ProductInfo['store_id'], 'status' => 'active', 'state' => 'live'])->first();
                    if (isset($discount) && !empty($discount)) {
                        if ($discount->discount_value_type == 'percent') {
                            $discount_value = $data['price'][$key] * (1 - ($discount->discount_value / 100));
                            if ($discount_value > 0) {
                                $variationProduct->sale_price = $discount_value;
                                $variationProduct->discount_percentage = $discount->discount_value;
                            } else {
                                $variationProduct->sale_price = $variationProduct->price;
                                $variationProduct->save();
                            }
                        } elseif ($discount->discount_value_type == 'fixed') {
                            $discount_value = max($data['price'][$key] - $discount->discount_value, 0);
                            if ($discount_value > 0) {
                                if ($data['price'][$key] > $discount->discount_value) {
                                    $discount_percentage = round(floatval(($discount->discount_value / $data['price'][$key]) * 100));
                                } else {
                                    $discount_percentage = 100;
                                }
                                $variationProduct->sale_price = $discount_value;
                                $variationProduct->discount_percentage = $discount_percentage;
                            } else {
                                $variationProduct->sale_price = $variationProduct->price;
                                $variationProduct->save();
                            }
                        }
                    }
                    DB::commit();
                    $variationProduct->save();
                }
            }
        } catch (Exception $e) {
            DB::rollBack(); // Rollback if there's an exception
            $productVariationLog = new Logger('');
            $productVariationLog->pushHandler(new StreamHandler(storage_path('logs/product-variation.log')), Logger::INFO);
            $productVariationLog->info('Add Product Failed', ['created_at' => Carbon::now()]);
        }
    }
    public function addProductVariationApi($data, $ProductInfo, $Query)
    {
        // dd($ProductInfo);
        try {

            if ($data) {
                $discount = Discount::where(['store_id' => $ProductInfo['store_id'], 'status' => 'active', 'state' => 'live'])->first();
                $counterCheck = 1;
                // return $data['option1'];
                foreach ($data['option1'] as $key => $value) {

                    $variationProduct = new ProductVariation();
                    $variationProduct->product_id = $ProductInfo['productID'];

                    $variationProduct->maxquantitylimit = isset($ProductInfo['maxquantitylimit']) ? $ProductInfo['maxquantitylimit'] : 10;
                    if (isset($data['attribute1'])) {
                        $variationProduct->attribute1 = $data['attribute1'][0] ?? null;
                    }
                    // return $data['attribute2'];
                    if (isset($data['attribute2'])) {
                        $variationProduct->attribute2 = $data['attribute2'][0] ?? null;
                    }
                    // return 12;
                    if (isset($data['attribute3'])) {
                        $variationProduct->attribute3 = $data['attribute3'][0] ?? null;
                    }
                    if (isset($data['unit1'])) {
                        $variationProduct->unit1 = $data['unit1'][0] ?? null;
                    }
                    if (isset($data['unit2'])) {
                        $variationProduct->unit2 = $data['unit2'][0] ?? null;
                    }
                    if (isset($data['unit3'])) {
                        $variationProduct->unit3 = $data['unit3'][0] ?? null;
                    }

                    if (isset($data['product_variation_image']) && !empty($data['product_variation_image'])) {
                        $variationProduct->product_variation_image = isset($ProductInfo['$product_variation_image'][$key]) ? $ProductInfo['$product_variation_image'][$key] : $ProductInfo['feature_image'];
                        // die('callled');
                    }
                    // return $data;
                    if (isset($data['option1'])) {
                        $variationProduct->option1 = $data['option1'][$key] ?? null;
                    }
                    if (isset($data['option2'])) {
                        // dump($data['option2'][$key]);
                        $variationProduct->option2 = $data['option2'][$key] ?? null;
                    }
                    if (isset($data['option3'])) {
                        $variationProduct->option3 = $data['option3'][$key] ?? null;
                    }
                    $sales = isset($data['sales'][$key]) ? $data['sales'][$key] : 0;
                    if (isset($data['variationID'][$key])) {
                        $variationProduct->variation_id = $data['variationID'][$key] ?? null;
                    }
                    $variationProduct->check_box_price1 = 1;
                    $variationProduct->check_box_price2 = 1;
                    $variationProduct->check_box_quantity1 = 1;
                    $variationProduct->check_box_quantity2 = 1;
                    $variationProduct->check_box_sku1 = 1;
                    $variationProduct->check_box_sku2 = 1;

                    $price = isset($data['price'][$key]) ? $data['price'][$key] : 0;

                    $sku = isset($data['sku'][$key]) ? $data['sku'][$key] : '';
                    $currentStock = isset($data['current_stock'][$key]) ? $data['current_stock'][$key] : 0;
                    $variationProduct->price = $price;
                    $variationProduct->sku = $sku;
                    $variationProduct->current_stock = $currentStock;
                    $variationProduct->sales = $sales;
                    if (isset($data['visibility'])) {

                        if (in_array($counterCheck, $data['visibility'])) {

                            $variationProduct->visibility = 1;
                        } else {

                            $variationProduct->visibility = 0;
                        }
                        $counterCheck++;
                    }

                    if (isset($discount) && !empty($discount)) {
                        if ($discount->discount_value_type == 'percent') {
                            $discount_value = $data['price'][$key] * (1 - ($discount->discount_value / 100));
                            $variationProduct->sale_price = $discount_value;
                            $variationProduct->discount_percentage = $discount->discount_value;
                        } elseif ($discount->discount_value_type == 'fixed') {
                            $discount_value = $data['price'][$key] - $discount->discount_value;
                            $discount_percentage = round(floatval(($discount->discount_value / $data['price'][$key]) * 100));
                            $variationProduct->sale_price = $discount_value;
                            $variationProduct->discount_percentage = $discount_percentage;
                        }
                    }
                    $variationProduct->save();
                }
            }
        } catch (Exception $e) {
            dd($e);
        }
        //exit();
    }
    /**
     *  Function for Update Variation of Product
     *
     */
    public function UpdateProductVariation($data, $ProductInfo, $Query)
    {
        //echo '$data: <pre>' .print_r($data,true). '</pre>'; die;
        try {

            $product = Product::where('product_id', $ProductInfo['productID'])->first();
        } catch (Exception $e) {
        }
        if ($product) {
            $maxquantitylimit = $product->maxquantitylimit;
        } else {
            $maxquantitylimit = 10;
        }


        if ($data) {
            $discount = Discount::where(['store_id' => $ProductInfo['store_id'], 'status' => 'active', 'state' => 'live'])->first();
            $counterCheck = 1;
            foreach ($data['option1'] as $key => $value) {
                // print_rr($data['variationID'][$key]);
                //                if ($Query == 'update') {
                //
                //                } else {
                //                    ////  $variationProduct = new ProductVariation();
                //                }
                $variationProduct = new ProductVariation();

                $variationProduct->maxquantitylimit = $maxquantitylimit;
                $variationProduct->product_id = $ProductInfo['productID'];
                if (isset($data['attribute1'])) {
                    $variationProduct->attribute1 = $data['attribute1'][0];
                }
                if ($data['attribute2']) {
                    $variationProduct->attribute2 = $data['attribute2'][0];
                }
                if ($data['attribute3']) {
                    $variationProduct->attribute3 = $data['attribute3'][0];
                }
                if (isset($data['unit1'])) {
                    $variationProduct->unit1 = $data['unit1'][0];
                }
                if (isset($data['unit2'])) {
                    $variationProduct->unit2 = $data['unit2'][0];
                }
                if (isset($data['unit3'])) {
                    $variationProduct->unit3 = $data['unit3'][0];
                }
                if (isset($data['product_variation_image'])) {
                    $variationProduct->product_variation_image = isset($data['product_variation_image'][$key]) ? $data['product_variation_image'][$key] : $ProductInfo['feature_image'];
                }
                if (isset($data['option1'])) {
                    $variationProduct->option1 = $data['option1'][$key];
                }
                if (isset($data['option2'])) {
                    $variationProduct->option2 = $data['option2'][$key];
                }
                if (isset($data['option3'])) {
                    $variationProduct->option3 = $data['option3'][$key];
                }
                $sales = isset($data['sales'][$key]) ? $data['sales'][$key] : 0;
                if (isset($data['variationID'][$key])) {
                    $variationProduct->variation_id = $data['variationID'][$key];
                }
                $variationProduct->check_box_price1 = 1;
                $variationProduct->check_box_price2 = 1;
                $variationProduct->check_box_quantity1 = 1;
                $variationProduct->check_box_quantity2 = 1;
                $variationProduct->check_box_sku1 = 1;
                $variationProduct->check_box_sku2 = 1;

                $price = isset($data['price'][$key]) ? $data['price'][$key] : 0;

                $sku = isset($data['sku'][$key]) ? $data['sku'][$key] : '';
                $currentStock = isset($data['current_stock'][$key]) ? $data['current_stock'][$key] : 0;
                $variationProduct->price = $price;
                $variationProduct->sku = $sku;
                $variationProduct->current_stock = $currentStock;
                $variationProduct->sales = $sales;
                if (isset($data['visibility'])) {

                    if (in_array($counterCheck, $data['visibility'])) {

                        $variationProduct->visibility = 1;
                    } else {

                        $variationProduct->visibility = 0;
                    }
                    $counterCheck++;
                }
                if (isset($discount) && !empty($discount)) {
                    if ($discount->discount_value_type == 'percent') {
                        $discount_value = $data['price'][$key] * (1 - ($discount->discount_value / 100));
                        $variationProduct->sale_price = $discount_value;
                        $variationProduct->discount_percentage = $discount->discount_value;
                    } elseif ($discount->discount_value_type == 'fixed') {
                        $discount_value = max($data['price'][$key] - $discount->discount_value, 0);
                        if ($data['price'][$key] > $discount->discount_value) {
                            $discount_percentage = round(floatval(($discount->discount_value / $data['price'][$key]) * 100));
                        } else {
                            $discount_percentage = 100;
                        }
                        $variationProduct->sale_price = $discount_value;
                        $variationProduct->discount_percentage = $discount_percentage;
                    }
                }
                $variationProduct->save();
            }
        }
        //exit();
    }
    public function UpdateProductVariationApi($data, $ProductInfo, $Query)
    {
        //echo '$data: <pre>' .print_r($data,true). '</pre>'; die;
        try {

            $product = Product::where('product_id', $ProductInfo['productID'])->first();
        } catch (Exception $e) {
        }
        if ($product) {
            $maxquantitylimit = $product->maxquantitylimit;
        } else {
            $maxquantitylimit = 10;
        }

        // $keys = array_keys($data['option1']);
        // return $keys;
        // return $data['product_variation_image'];
        if ($data) {
            $counterCheck = 1;
            $discount = Discount::where(['store_id' => $ProductInfo['store_id'], 'status' => 'active', 'state' => 'live'])->first();

            foreach ($data['option1'] as $key => $value) {
                // print_rr($data['variationID'][$key]);
                //                if ($Query == 'update') {
                //
                //                } else {
                //                    ////  $variationProduct = new ProductVariation();
                //                }
                $variationProduct = new ProductVariation();

                $variationProduct->maxquantitylimit = $maxquantitylimit;
                $variationProduct->product_id = $ProductInfo['productID'];

                if (isset($data['attribute1'])) {
                    $variationProduct->attribute1 = isset($data['attribute1'][0]) ? $data['attribute1'][0] : null;
                }
                // return $data['attribute2'];
                if ($data['attribute2']) {
                    $variationProduct->attribute2 = isset($data['attribute2'][0]) ? $data['attribute2'][0] : null;
                }
                if ($data['attribute3']) {
                    $variationProduct->attribute3 = isset($data['attribute3'][0]) ? $data['attribute3'][0] : null;
                }
                if (isset($data['unit1'])) {
                    $variationProduct->unit1 = isset($data['unit1'][0]) ? $data['unit1'][0] : null;
                }
                if (isset($data['unit2'])) {
                    $variationProduct->unit2 = isset($data['unit2'][0]) ? $data['unit2'][0] : null;
                }
                // return 12;
                if (isset($data['unit3'])) {
                    $variationProduct->unit3 = isset($data['unit3'][0]) ? $data['unit3'][0] : null;
                }
                if (isset($data['product_variation_image'])) {
                    // if(isset($data['product_variation_image'][$key]['id'])){$variationProduct->product_variation_image =  $data['product_variation_image'][$key]['id'];}elseif(isset($data['product_variation_image'][$key])){$variationProduct->product_variation_image =  $data['product_variation_image'][$key];}else{
                    //     null;
                    // }
                    $variationProduct->product_variation_image = isset($data['product_variation_image'][$key]) ? $data['product_variation_image'][$key] : $ProductInfo['feature_image'];
                }
                if (isset($data['option1'])) {
                    $variationProduct->option1 = isset($data['option1'][$key]) ? $data['option1'][$key] : null;
                }
                // return $data['option2'][1];
                if (isset($data['option2'])) {
                    $variationProduct->option2 = isset($data['option2'][$key]) ? $data['option2'][$key] : null;
                }
                if (isset($data['option3'])) {
                    $variationProduct->option3 = isset($data['option3'][$key]) ? $data['option3'][$key] : null;
                }

                $sales = isset($data['sales'][$key]) ? $data['sales'][$key] : 0;
                if (isset($data['variationID'][$key])) {
                    $variationProduct->variation_id = isset($data['variationID'][$key]) ? $data['variationID'][$key] : null;
                }
                $variationProduct->check_box_price1 = 1;
                $variationProduct->check_box_price2 = 1;
                $variationProduct->check_box_quantity1 = 1;
                $variationProduct->check_box_quantity2 = 1;
                $variationProduct->check_box_sku1 = 1;
                $variationProduct->check_box_sku2 = 1;

                $price = isset($data['price'][$key]) ? $data['price'][$key] : 0;

                $sku = isset($data['sku'][$key]) ? $data['sku'][$key] : '';
                $currentStock = isset($data['current_stock'][$key]) ? $data['current_stock'][$key] : 0;
                $variationProduct->price = $price;
                $variationProduct->sku = $sku;
                $variationProduct->current_stock = $currentStock;
                $variationProduct->sales = $sales;

                if (isset($data['visibility'])) {

                    if (in_array($counterCheck, $data['visibility'])) {

                        $variationProduct->visibility = 1;
                    } else {

                        $variationProduct->visibility = 0;
                    }
                    $counterCheck++;
                }
                if (isset($discount) && !empty($discount)) {
                    if ($discount->discount_value_type == 'percent') {
                        $discount_value = $data['price'][$key] * (1 - ($discount->discount_value / 100));
                        $variationProduct->sale_price = $discount_value;
                    } elseif ($discount->discount_value_type == 'fixed') {
                        $discount_value = $data['price'][$key] - $discount->discount_value;
                        $discount_percentage = round(floatval(($discount->discount_value / $data['price'][$key]) * 100));
                        $variationProduct->sale_price = $discount_value;
                        $variationProduct->discount_percentage = $discount_percentage;
                    }
                }
                $variationProduct->save();
            }
        }
    }
    /**
     * Function for Add Product in Store
     */
    public function addStoreProduct($data)
    {

        try {
            DB::beginTransaction();
        $store = new Store();
        $userID = Session::get('userData')['user_id'];
        $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
        $productShortDescription = isset($data['productShortDescription']) ? $data['productShortDescription'] : '';
        $productsImages = isset($data['productImage']) ? $data['productImage'] : '';
        /* Pricing , Stock , Sku  */
        $productPrice = isset($data['productPrice']) ? $data['productPrice'] : 0;
        $productCurrentStock = isset($data['productcurrentstock']) ? $data['productcurrentstock'] : 0;
        $productSKU = isset($data['productSKU']) ? $data['productSKU'] : null;
        /* Feature Image select  */
        $feature_image = isset($data['feature_image']) ? $data['feature_image'] : null;
        /* Dimensions  , Weight  */
        $shippingOptionId = isset($data['previousShippingOptions']) ? $data['previousShippingOptions'] : 0;
        $productWeight = isset($data['productWeight']) ? round($data['productWeight']) : null;
        $productSizeLength = isset($data['productSize_length']) ? round($data['productSize_length']) : '';
        $productSizeWidth = isset($data['productSize_width']) ? round($data['productSize_width']) : '';
        $productSizeHeight = isset($data['productSize_height']) ? round($data['productSize_height']) : '';
        $faqQuestion = isset($data['faqQuestion']) ? $data['faqQuestion'] : '';
        $maxquantitylimit = isset($data['maxquantitylimit']) ? $data['maxquantitylimit'] : 10;
        $faqAnswer = isset($data['faqAnswer']) ? $data['faqAnswer'] : '';
        $weightType = isset($data['weight_type']) ? $data['weight_type'] : '';
        $dimentionType = isset($data['dimention_type']) ? $data['dimention_type'] : '';
        $optradio = isset($data['optradio']) ? $data['optradio'] : 0;
        $product_type = isset($data['product_type']) ? $data['product_type'] : null;
        $item_condition = isset($data['item_condition']) ? $data['item_condition'] : null;
        $another_product_add = isset($data['another_product_add']) ? $data['another_product_add'] : null;
        $delivery_days = isset($data['delivery_days']) ? $data['delivery_days'] : null;
        $echoFriendly = isset($data['eco_friendly']) ? $data['eco_friendly'] : null;
        $videoUrl = isset($data['productVideoURL']) ? $data['productVideoURL'] : "";
        $variationData = isset($data['ProductvariationData']) ? $data['ProductvariationData'] : null;
        /* If Product Feature Image , Gallery Images Exist */
        $productFeatureImage = '';
        $productGallery = $image_array = array();
        $product_variation_image = array();
        if (is_array($productsImages) && count($productsImages) > 0) {
            $counterImage = 0;

                foreach ($productsImages as $image) {
                    // dd($image);
                    $attachment = $this->getAttachmentId($image, 'image', false);
                    $productGallery[] = $attachment;
                    if ($image == $feature_image) {
                        // Set the selected image as the feature image
                        $productFeatureImage = $attachment;
                    }
                    if (isset($variationData['product_variation_image'])) {
                        foreach ($variationData['product_variation_image'] as $key => $vimage) {
                            if ($vimage == $image) {
                                $product_variation_image[$key] = $attachment;
                            }
                            if (is_null($vimage)) {
                                $product_variation_image[$key] = $productFeatureImage;
                            }
                        }
                    }
                    $counterImage++;
                }
                if ($productFeatureImage == '') {
                    $productFeatureImage = $productGallery[0];
                }
            }
            $storeId = '';
            $status = '3';
            if ($another_product_add == 5) {
                $status = '5';
            }
            if (isset($storeID->store_id) && $storeID->store_id != '') {
                $storeId = $storeID->store_id;
                $storeStatus = getStoreInfoById($storeId);
                $storeStatus = $storeStatus[0]->store_status;
            } else {
                $storeId = 1;
            }
            if (!$userID) {
                $userID = 1;
            }
            if (isset($variationData['attribute1']) || isset($variationData['attribute2'])) {
                if (isset($variationData['attribute1'])) {
                    $variationArray = $variationData['attribute1'];
                } else {
                    $variationArray = $variationData['attribute1'];
                }

                $priceFlag = $quantityFlag = false;
                foreach ($variationArray as $key => $value) {

                    if (isset($variationData['price'][$key]) && $variationData['price'][$key] > 0) {
                        if (!$priceFlag) {
                            $productPrice = $variationData['price'][$key];
                            $priceFlag = true;
                        }
                    }
                    if (isset($variationData['sku'][$key]) && $variationData['sku'][$key] != '') {
                        $productSKU = $variationData['sku'][$key];
                    }

                if (isset($variationData['quantity'][$key]) && $variationData['quantity'][$key] > 1) {
                    if (!$quantityFlag) {
                        $productQuantity = $variationData['quantity'][$key];
                        $quantityFlag = true;
                    }
                }
                if ($priceFlag && $quantityFlag) {
                    break;
                }
            }
        }
        $title_slug = substr($data['productTitle'], 0, 20);
        $productSlug = $this->convert_to_slug($title_slug);
        $this->_product->user_id = $userID;
        $this->_product->store_id = $storeId;
        $this->_product->title = $data['productTitle'];
        $this->_product->slug = $productSlug;
        $this->_product->short_description = $productShortDescription;
        $this->_product->description = $data['productDescription'];
        $this->_product->price = $productPrice;
        $this->_product->current_stock = $productCurrentStock;
        $this->_product->sku = $productSKU;
        $this->_product->delivery_days = $delivery_days;
        $this->_product->eco_friendly = $echoFriendly;
        $this->_product->status = $status;
        $this->_product->weight = $productWeight;
        $this->_product->productSize_length = $productSizeLength;
        $this->_product->productSize_width = $productSizeWidth;
        $this->_product->productSize_height = $productSizeHeight;
        $this->_product->shipping_option_id = $shippingOptionId;
        $this->_product->video_url = $videoUrl;
        $this->_product->weight_type = $weightType;
        $this->_product->dimention_type = $dimentionType;
        $this->_product->maxquantitylimit = $maxquantitylimit;
        $this->_product->item_condition = $item_condition;

            $this->_product->product_type = $data['product_type'];
            /*
         * revenue section end
         */
        $discount = Discount::where(['store_id' => $storeId, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();
        if (isset($discount) && !empty($discount)) {
            if ($discount->discount_value_type == 'percent') {
                $discount_value = $productPrice * (1 - ($discount->discount_value / 100));

                    $this->_product->on_sale = true;
                    $this->_product->sale_price = $discount_value;
                    $this->_product->discount_id = $discount->id;
                    $this->_product->discount_percentage = $discount->discount_value;
                } elseif ($discount->discount_value_type == 'fixed') {
                    $discount_value = max($productPrice - $discount->discount_value, 0);

                    // Check if the resulting price is greater than 0 before applying the discount
                    if ($discount_value >= 0.5) {
                        if ($productPrice > 0) {
                            if ($productPrice > $discount->discount_value) {
                                $discount_percentage = round(floatval(($discount->discount_value / $productPrice) * 100));
                            } else {
                                $discount_percentage = 100;
                            }
                        } else {
                            $discount_percentage = 0; // Handle case for zero price
                        }
                        // Apply the discount
                        $this->_product->on_sale = true;
                        $this->_product->sale_price = $discount_value;
                        $this->_product->discount_percentage = $discount_percentage; // Optional: store percentage
                        $this->_product->discount_id = $discount->id;
                    } else {
                        $this->_product->on_sale = false;
                        $this->_product->sale_price =  $productPrice;
                    }
                }
            }
            if ($productFeatureImage) {
                $this->_product->feature_Image = $productFeatureImage;
            }
            if (!empty($productGallery)) {
                $this->_product->gallery = serialize($productGallery);
            }
            $this->_product->save();
            $productID = $this->_product->product_id;
            $categories = isset($data['productCategory']) ? $data['productCategory'] : null;
            if (!empty($variationData) && (count($variationData) > 0)) {

                $productInfo = array();
                $productInfo['productID'] = $productID;
                $productInfo['productPrice'] = $productPrice;
                // $productInfo['productSalePrice'] = $productSalePrice;
                // $productInfo['productReceiveBackPrice'] = $productReceiveBackPrice;
                // $productInfo['productQuantity'] = $productQuantity;
                $productInfo['productSKU'] = $productSKU;
                $productInfo['$product_variation_image'] = $product_variation_image;
                $productInfo['maxquantitylimit'] = $maxquantitylimit;
                $ProductInfo['feature_image'] = $productFeatureImage;
                $productInfo['store_id'] = $storeId;
                // print_rr($variationData); exit;
                // dd($variationData,$productInfo);
                $this->addProductVariation($variationData, $productInfo, 'add');
            }
            if ($productID) {
                if (!empty($faqQuestion)) {
                    foreach ($faqQuestion as $key => $question) {
                        $productFaq = new ProductFaq();
                        if (strpos($question, '?') !== false) {
                        } else {
                            $question = $question . " ?";
                        }
                        $productFaq->question = $question;
                        $productFaq->answer = $faqAnswer[$key];
                        $productFaq->product_id = $productID;
                        $productFaq->save();
                    }
                }
                if ((count($categories) > 0) && is_array($categories)) {
                    foreach ($categories as $category_id) {

                        if ($category_id) {
                            $productCategory = new CategoryProduct();
                            $productCategory->category_id = $category_id;
                            $productCategory->product_id = $productID;
                            $productCategory->save();
                        }
                    }
                }
                $productTags = isset($data['productTags']) ? $data['productTags'] : null;
                if ($productTags) {
                    $productTags = explode(",", $data['productTags']);
                    if (is_array($productTags) && (count($productTags) > 0)) {
                        foreach ($productTags as $tag_value) {
                            if ($tag_value) {
                                $productTags = new ProductTags();
                                $productTags->product_id = $productID;
                                $productTags->tag_title = $tag_value;
                                $productTags->save();
                            }
                        }
                    }
                }

                if (isset($data['product_type']) && $data['product_type'] == 'virtual') {
                    $files = !empty($data['product_file']) ? $data['product_file'] : null;
                    if (isset($files)) {

                        foreach ($files as $file) {
                            $fileName = time() . '_' . $file->getClientOriginalName();
                            $OriginalfileName = $file->getClientOriginalName();
                            $destinationPath = public_path() . '/uploads/digital/';
                            if (!File::exists($destinationPath)) {
                                File::makeDirectory($destinationPath, 0775, true);
                            }
                            $file->move($destinationPath, $fileName);
                            $productFiles = new DigitalFiles();
                            $productFiles->product_id = $productID;
                            $productFiles->file_name = $fileName;
                            $productFiles->orignal_file_name = $OriginalfileName;
                            $productFiles->save();
                        }
                    }
                }

                $this->_esearch->esMApping($productID);
            }
            DB::commit(); // Commit the transaction if all operations succeed
            return 'save';
        } catch (Exception $e) {
            DB::rollBack(); // Rollback if there's an exception
            $productLog = new Logger('');
            $productLog->pushHandler(new StreamHandler(storage_path('logs/product.log')), Logger::INFO);
            $productLog->info('Add Product Failed', ['created_at' => Carbon::now()]);
            // Handle the exception
        }
    }
    /**
     * Function for Add Product Api in Store
     */
    public function addStoreProductApi($data)
    {
        // return $data;

        $store = new Store();
        $userID = Auth::user()->user_id;
        $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
        $productShortDescription = isset($data['productShortDescription']) ? $data['productShortDescription'] : null;
        $productDescription = isset($data['productDescription']) ? $data['productDescription'] : null;

        $productsImages = isset($data['productImage']) ? $data['productImage'] : null;
        /* Pricing , Stock , Sku  */
        $productPrice = isset($data['productPrice']) ? $data['productPrice'] : 0;
        $productCurrentStock = isset($data['productcurrentstock']) ? $data['productcurrentstock'] : null;
        $productSKU = isset($data['productSKU']) ? $data['productSKU'] : null;
        /* Feature Image select  */
        $feature_image = isset($data['feature_image']) ? $data['feature_image'] : null;
        /* Dimensions  , Weight  */
        $shippingOptionId = isset($data['previousShippingOptions']) ? $data['previousShippingOptions'] : null;
        $productWeight = isset($data['productWeight']) ? round($data['productWeight']) : null;
        $productSizeLength = isset($data['productSize_length']) ? round($data['productSize_length']) : null;
        $productSizeWidth = isset($data['productSize_width']) ? round($data['productSize_width']) : null;
        $productSizeHeight = isset($data['productSize_height']) ? round($data['productSize_height']) : null;
        $faqQuestion = isset($data['faqQuestion']) ? $data['faqQuestion'] : null;
        $maxquantitylimit = isset($data['maxquantitylimit']) ? $data['maxquantitylimit'] : 10;
        $faqAnswer = isset($data['faqAnswer']) ? $data['faqAnswer'] : null;
        $weightType = isset($data['weight_type']) ? $data['weight_type'] : null;
        $dimentionType = isset($data['dimention_type']) ? $data['dimention_type'] : null;
        $videoUrl = isset($data['productVideoURL']) ? $data['productVideoURL'] : null;
        $item_condition = isset($data['item_condition']) ? $data['item_condition'] : null;
        $another_product_add = isset($data['another_product_add']) ? $data['another_product_add'] : null;
        $product_type = isset($data['product_type']) ? $data['product_type'] : null;
        // return $videoUrl;
        $productFeatureImage = '';
        $productGallery = array();

        if (is_array($productsImages) && count($productsImages) > 0) {
            $counterImage = 0;

            foreach ($productsImages as $image) {
                $attachment = $this->getAttachmentId($image, 'image', false);
                // dd($attachment);
                $productGallery[] = $attachment;
                if ($image == $feature_image) {
                    // Set the selected image as the feature image
                    $productFeatureImage = $attachment;
                }
                if (isset($variationData['product_variation_image'])) {
                    foreach ($variationData['product_variation_image'] as $key => $vimage) {
                        if ($vimage == $image) {
                            $product_variation_image[$key] = $attachment;
                        }
                    }
                }


                $counterImage++;
            }
            if ($productFeatureImage == '') {
                $productFeatureImage = $productGallery[0];
            }
        }
        $storeId = '';
        $status = '3';
        // return 12;
        if ($another_product_add == '5') {
            $status = '5';
        }
        if (isset($storeID->store_id) && $storeID->store_id != '') {
            $storeId = $storeID->store_id;
            $storeStatus = getStoreInfoById($storeId);
            $storeStatus = $storeStatus[0]->store_status;
            //if ($storeStatus == '2') {
            //$status = '1';
            //}
        } else {
            $storeId = 1;
        }
        if (!$userID) {
            $userID = 1;
        }
        $variationData = isset($data['ProductvariationData']) ? $data['ProductvariationData'] : null;
        //echo 'Data: <pre>' .print_r($variationData,true). '</pre>';
        if (isset($variationData['attribute1']) || isset($variationData['attribute2'])) {
            if (isset($variationData['attribute1'])) {
                $variationArray = $variationData['attribute1'];
            } else {
                $variationArray = $variationData['attribute1'];
            }
            $product_variation_image = array();
            // dd($variationData['product_variation_image']);
            foreach ($variationData['product_variation_image'] as $vimage) {
                // dd($vimage);
                $product_variation_image[] = $this->getAttachmentId($vimage, 'image', false);
            }
            $priceFlag = $quantityFlag = false;
            foreach ($variationArray as $key => $value) {

                if (isset($variationData['price'][$key]) && $variationData['price'][$key] > 0) {
                    if (!$priceFlag) {
                        $filteredPrices = array_filter($variationData['price'], function ($price) {
                            return $price > 0 && !empty($price) && $price !== null;
                        });

                        // Get the first element of the filtered array
                        $firstValidPrice = reset($filteredPrices);
                        $productPrice = $firstValidPrice;
                        // $productSalePrice = $variationData['salePrice'][$key];
                        //$productReceiveBackPrice = $productSalePrice / 2; ///$variationData['receiveBackPrice'][$key];
                        $productReceiveBackPrice = 0; ///$variationData['receiveBackPrice'][$key];
                        $priceFlag = true;
                    }
                }
                if (isset($variationData['sku'][$key]) && $variationData['sku'][$key] != '') {
                    $productSKU = $variationData['sku'][$key];
                }

                if (isset($variationData['quantity'][$key]) && $variationData['quantity'][$key] > 1) {
                    if (!$quantityFlag) {
                        $productQuantity = $variationData['quantity'][$key];
                        $quantityFlag = true;
                    }
                }
                if ($priceFlag && $quantityFlag) {
                    break;
                }
            }
        }
        // return $status;

        $title_slug = substr($data['productTitle'], 0, 20);
        $productSlug = $this->convert_to_slug($title_slug);
        $this->_product->user_id = $userID;
        $this->_product->store_id = $storeId;
        $this->_product->title = $data['productTitle'];
        $this->_product->slug = $productSlug;
        $this->_product->short_description = $productShortDescription;
        $this->_product->description = $productDescription;
        $this->_product->price = $productPrice;
        $this->_product->current_stock = $productCurrentStock;
        $this->_product->sku = $productSKU;
        $this->_product->status = $status;
        $this->_product->weight = $productWeight;
        $this->_product->productSize_length = $productSizeLength;
        $this->_product->productSize_width = $productSizeWidth;
        $this->_product->productSize_height = $productSizeHeight;
        $this->_product->shipping_option_id = $shippingOptionId;
        $this->_product->video_url = $videoUrl;
        $this->_product->weight_type = $weightType;
        $this->_product->dimention_type = $dimentionType;
        $this->_product->maxquantitylimit = $maxquantitylimit;
        $this->_product->item_condition = $item_condition;

        $this->_product->product_type = $product_type;
        // return $this->_product;
        if ($productFeatureImage) {
            $this->_product->feature_Image = $productFeatureImage;
        }
        if (!empty($productGallery)) {
            $this->_product->gallery = serialize($productGallery);
        }
        $discount = Discount::where(['store_id' => $storeId, 'status' => 'active', 'state' => 'live'])->first();

        if (isset($discount) && !empty($discount)) {
            if ($discount->discount_value_type == 'percent') {
                $discount_value = $productPrice * (1 - ($discount->discount_value / 100));
                $this->_product->on_sale = true;
                $this->_product->sale_price = $discount_value;
                $this->_product->discount_id = $discount->id;
                $this->_product->discount_percentage = $discount->discount_value;
            } elseif ($discount->discount_value_type == 'fixed') {
                $discount_value = $productPrice - $discount->discount_value;

                // Calculate percentage discount based on fixed amount
                $discount_percentage = ($discount->discount_value / $productPrice) * 100;

                // Apply the discount
                $this->_product->on_sale = true;
                $this->_product->sale_price = $discount_value;
                $this->_product->discount_percentage = $discount_percentage; // Optional: store percentage
                $this->_product->discount_id = $discount->id;
            }
        }

        $this->_product->save();
        $productID = $this->_product->product_id;

        $categories = isset($data['productCategory']) ? $data['productCategory'] : null;
        // dd($variationData);
        // return $variationData;
        // return 12;

        if (!empty($variationData) && (count($variationData) > 0)) {

            $productInfo = array();
            $productInfo['productID'] = $productID;
            $productInfo['productPrice'] = $productPrice;
            $productInfo['productSKU'] = $productSKU;
            // dd($product_variation_image);
            $productInfo['$product_variation_image'] = $product_variation_image;
            $productInfo['maxquantitylimit'] = $maxquantitylimit;
            $productInfo['store_id'] = $storeId;
            $product['feature_image'] = $productFeatureImage;
            // print_rr($variationData); exit;
            // return $variationData;
            $a = $this->addProductVariationApi($variationData, $productInfo, 'add');
        }
        if ($productID) {
            if (!empty($faqQuestion)) {
                foreach ($faqQuestion as $key => $question) {
                    $productFaq = new ProductFaq();
                    if (strpos($question, '?') !== false) {
                    } else {
                        $question = $question . " ?";
                    }
                    $productFaq->question = $question;
                    $productFaq->answer = $faqAnswer[$key];
                    $productFaq->product_id = $productID;
                    $productFaq->save();
                }
            }
            // return $categories;
            if ((count($categories) > 0) && is_array($categories)) {
                foreach ($categories as $category_id) {
                    // return $category_id;
                    if ($category_id) {
                        $productCategory = new CategoryProduct();
                        $productCategory->category_id = $category_id;
                        $productCategory->product_id = $productID;
                        $productCategory->save();
                    }
                }
            }
            $productTags = isset($data['productTags']) ? $data['productTags'] : null;
            if ($productTags) {
                // return $productTags;
                $productTags = $data['productTags'];
                if (is_array($productTags) && (count($productTags) > 0)) {
                    foreach ($productTags as $tag_value) {
                        // return $tag_value;
                        if ($tag_value) {
                            $productTags = new ProductTags();
                            $productTags->product_id = $productID;
                            $productTags->tag_title = $tag_value;
                            $productTags->save();
                        }
                    }
                }
            }
            if (isset($data['product_type']) && $data['product_type'] == 'virtual') {
                $files = !empty($data['product_file']) ? $data['product_file'] : '';
                foreach ($files as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $OriginalfileName = $file->getClientOriginalName();
                    $destinationPath = public_path() . '/uploads/digital/';
                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath, 0775, true);
                    }
                    $file->move($destinationPath, $fileName);
                    $productFiles = new DigitalFiles();
                    $productFiles->product_id = $productID;
                    $productFiles->file_name = $fileName;
                    $productFiles->orignal_file_name = $OriginalfileName;
                    $productFiles->save();
                }
            }
            $this->_product->status = getProductStatus($productID);
            $this->_esearch->esMApping($productID);
            $data = [
                'status' => 'save',
                'data' => $this->_product
            ];
            return $data;
        }
    }

    /*
     * * Create Directories if Not Exist and Retrun Images Sizes
     */

    public function getAllAttachmentSizes()
    {

        $AllSize = new AttachmentSizes();
        $getDirectorywithSizes = $AllSize->all()->toArray();
        //echo '$getDirectorywithSizes: <pre>' .print_r($getDirectorywithSizes,true). '</pre>'; die;
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

    public function getProductFaqsById($productId)
    {
        return $faqs = DB::table('product_faqs')
            ->where('product_id', '=', $productId)
            ->get()
            ->toArray();
    }

    /*
     * * Get All Prodcut Related Varaitions
     */

    public function getAllProductVariation($product_id)
    {
        // dd($product_id);
        $getAllVariations = DB::table('product_variation')->where('product_id', $product_id)->select('*')->get()->toArray();
        return $getAllVariations;
    }

    public function getVisibleVariation($product_id)
    {
        $getAllVariations = DB::table('product_variation')->where('product_id', $product_id)->where('visibility', 1)->select('*')->get()->toArray();
        return $getAllVariations;
    }

    /*
     * * Get limited charactor function
     */

    public function charFunction($string)
    {
        $string = substr($string, 0, 20);
        $string = substr($string, 0, strrpos($string, " "));
        return $string;
    }

    /*
     * * Get All Prodcut Related Varaitions
     */

    public function convert_to_slug($str)
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        $slug = rtrim($str, '-');

        $result_slug = DB::table('products')->where('slug', $slug)->select('*')->get()->toArray();
        $getRecord = count($result_slug);
        if ($getRecord > 0) {
            $slug = $slug . '-' . rand(45, 568522);
        }
        return $slug;
    }

    /*
     * edit product
     */

    public function edit_product_by_id($params)
    {
    //   dd($params);
        $store = new Store();
        $userID = Session::get('userData')['user_id'];
        if(isset($params['edit-by']) && $params['edit-by'] == 'admin'){
            $storeID = $store->select('store_id')->where('user_id', '=', $params['userID'])->first();
            
        }else{
            $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();

        }
        
        $productTitle = isset($params['productTitle']) ? $params['productTitle'] : '';
        $title_slug = substr($productTitle, 0, 20);
        $productShortDescription = isset($params['productShortDescription']) ? $params['productShortDescription'] : '';
        $productDescription = isset($params['productDescription']) ? $params['productDescription'] : null;
        $productImage = isset($params['productImage']) ? $params['productImage'] : '';

        /* Pricing , Stock , Sku  */
        $productPrice = isset($params['productPrice']) ? $params['productPrice'] : 0;
        $shippingOptionId = isset($params['previousShippingOptions']) ? $params['previousShippingOptions'] : 0;

        $productCurrentStock = isset($params['productcurrentstock']) ? $params['productcurrentstock'] : 0;
        $productSKU = isset($params['productSKU']) ? $params['productSKU'] : null;
        $productDescription = isset($params['productDescription']) ? $params['productDescription'] : null;
        $ecoFriendly = isset($params['eco_friendly']) ? $params['eco_friendly'] : null;
        /* Dimensions  , Weight  */
        $productWeight = isset($params['productWeight']) ? round($params['productWeight']) : null;
        // return $productWeight;
        $productSizeLength = isset($params['productSize_length']) ? round($params['productSize_length']) : '';
        $productSizeWidth = isset($params['productSize_width']) ? round($params['productSize_width']) : '';
        $productSizeHeight = isset($params['productSize_height']) ? round($params['productSize_height']) : '';
        //  $productstatus = isset($params['product_status']) ? $params['product_status'] : '';
        $maxquantitylimit = isset($params['maxquantitylimit']) ? $params['maxquantitylimit'] : 10;
        $productResults = product::where('product_id', '=', $params['edit_product_id'])->select('title')
            ->get()->toArray();
        /* Product Variations  */
        $variationData = isset($params['ProductvariationData']) ? $params['ProductvariationData'] : null;
        $faqQuestion = isset($params['faqQuestion']) ? $params['faqQuestion'] : '';
        $faqAnswer = isset($params['faqAnswer']) ? $params['faqAnswer'] : '';
        $weightType = isset($params['weight_type']) ? $params['weight_type'] : '';
        $dimentionType = isset($params['dimention_type']) ? $params['dimention_type'] : '';
        $delivery_days = isset($params['delivery_days']) ? $params['delivery_days'] : null;

        $formulas = new Formulas();
        $userRevenue = isset($params['productRevenue']) ? $params['productRevenue'] : $formulas->sellerRevenueStartLimit;
        $videoUrl = isset($params['video_url']) ? $params['video_url'] : "";
        $product_status = isset($params['product_status']) ? $params['product_status'] : "";
        $deleteFileIds = isset($params['delete_file_ids']) ? $params['delete_file_ids'] : "";
        $productType = isset($params['edit_product_type']) ? $params['edit_product_type'] : "";
        $item_condition = isset($params['item_condition']) ? $params['item_condition'] : "";
        if ($userRevenue < $formulas->sellerRevenueStartLimit || $userRevenue > $formulas->sellerRevenueEndLimit) {
            $userRevenue = $formulas->sellerRevenueStartLimit;
            $cashbackPool = $formulas->cashbackPoolEndLimit;
            $SuperSupporterPool = $formulas->supersupporterPoolEndLimit;
        } else {
            $cashbackPool = $formulas->getCashbackPercentage($userRevenue);
            $SuperSupporterPool = $formulas->getsupersupporterPercentage($userRevenue);
        }
        if (!empty($variationData) && count($variationData) > 0) {
            $priceFlag = false;
            $quantityFlag = false;
            foreach ($variationData as $key => $value) {

                if (isset($variationData['price'][$key]) && $variationData['price'][$key] > 0) {
                    if (!$priceFlag) {
                        $productPrice = $variationData['price'][$key];
                        $priceFlag = true;
                    }
                }
                if (isset($variationData['sku'][$key]) && $variationData['sku'][$key] != '') {
                    $productSKU = $variationData['sku'][$key];
                }

                if (isset($variationData['quantity'][$key]) && $variationData['quantity'][$key] > 1) {
                    if (!$quantityFlag) {
                        $productQuantity = $variationData['quantity'][$key];
                        $quantityFlag = true;
                    }
                }
                if ($priceFlag && $quantityFlag) {
                    break;
                }
            }

            $productThumbnail = !empty($product_info['featureImage']) ? $product_info['featureImage'] : null;
            $productFeatureImage = '';
            if (in_array($productThumbnail, $productImage)) {
                $productFeatureImage = $productThumbnail;
            } else {
                $productFeatureImage = $productImage[0];
            }
            if (isset($params['productthumbnal-crp']) && !is_numeric($params['productthumbnal-crp'])) {

                $attachmentID = $this->getAttachmentId($params['productthumbnal-crp'], 'image', false);
                $productFeatureImage = $attachmentID;
            } elseif (isset($params['productthumbnal-crp'])) {
                $productFeatureImage = $params['productthumbnal-crp'];
            }
            $ProductInfo = array();
            $ProductInfo['productID'] = $params['edit_product_id'];
            $ProductInfo['store_id'] = $storeID->store_id;
            $ProductInfo['feature_image'] = $productFeatureImage;
            DB::table('product_variation')->where('product_id', $params['edit_product_id'])->delete();
            $this->UpdateProductVariation($variationData, $ProductInfo, 'update');
        }
        if (isset($productResults[0]['title']) && $productResults[0]['title'] != $productTitle) {
            product::where('product_id', '=', $params['edit_product_id'])
                ->update(
                    ['slug' => $this->convert_to_slug($title_slug)]
                );
        }
        // dd($product_status);
        if ($product_status == 5) {
            $status = '3';
        } else {
            $status = $product_status;
        }
        $product = Product::where('product_id', '=', $params['edit_product_id'])
            ->update([
            'title' => $productTitle,
            'description' => $productDescription,
            'eco_friendly' => $ecoFriendly,
            'shipping_option_id' => $shippingOptionId,
            'current_stock' => $productCurrentStock,
            'sku' => $productSKU,
            'delivery_days' => $delivery_days,
            'weight' => $productWeight,
            'status' => $status,
            'productSize_length' => $productSizeLength,
            'productSize_width' => $productSizeWidth,
            'productSize_height' => $productSizeHeight,
            'maxquantitylimit' => $maxquantitylimit,
            'short_description' => $productShortDescription,
            'video_url' => $videoUrl,
            'user_revenue' => $userRevenue,
            'cashback_pool' => $cashbackPool,
            'supersupporter_pool' => $SuperSupporterPool,
            'weight_type' => $weightType,
            'dimention_type' => $dimentionType,
            'item_condition' => $item_condition,
        ]);
        // if($productType != 'variant'){

        if (isset($params['productvarientPrice'])) {
            $productPrice = isset($params['ProductvariationData']['price'][0]) ? $params['ProductvariationData']['price'][0] : 0;
            Product::where('product_id', '=', $params['edit_product_id'])
                ->update(['price' => $productPrice]);
        }
        if (isset($params['productPrice'])) {
            Product::where('product_id', '=', $params['edit_product_id'])
                ->update(['price' => $params['productPrice']]);
        }
        $discount = Discount::where(['store_id' => $storeID->store_id, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();
        if (isset($discount) && !empty($discount)) {
            $product = Product::findorfail($params['edit_product_id']);
            if ($product) {
                if ($discount->discount_value_type == 'percent') {
                    $discount_value = $product->price * (1 - ($discount->discount_value / 100));
                    if ($discount_value >= 0.5) {
                        $product->on_sale = true;
                        $product->sale_price = $discount_value;
                        $product->discount_id = $discount->id;
                        $product->discount_percentage = $discount->discount_value;
                    }
                } elseif ($discount->discount_value_type == 'fixed') {
                    $discount_value = max($productPrice - $discount->discount_value, 0);
                    if ($discount_value >= 0.5) {
                        if ($productPrice > $discount->discount_value) {
                            $discount_percentage = round(floatval(($discount->discount_value / $productPrice) * 100));
                        } else {
                            $discount_percentage = 100;
                        }
                        $product->on_sale = true;
                        $product->sale_price = $discount_value;
                        $product->discount_percentage = $discount_percentage; // Optional: store percentage
                        $product->discount_id = $discount->id;
                    }
                }
                $product->save();
            }

            // }
        }

        if (isset($params['productcondition'])) {
            Product::where('product_id', '=', $params['edit_product_id'])
                ->update(['product_condition' => $params['productcondition']]);
        }

        // check product status if its rejected then change the status to pending
        $product_detail = DB::table('products')
            ->where('product_id', $params['edit_product_id'])->first();
        if (!empty($product_detail) && $product_detail->status == 4) {
            Product::where('product_id', '=', $params['edit_product_id'])
                ->update(['status' => '3']);
        }
        $product_info = $this->getProductByID($params['product_id']);
        $productThumbnail = !empty($product_info['featureImage']) ? $product_info['featureImage'] : null;

        /* If Product Feature Image , Gallery Images Exist */
        $counter = 0;
        $productFeatureImage = '';
        $productGallery = array();
        $attachmentID = '';
        if (isset($productImage) && $productImage != '') {

            if (!empty($params['optradio'])) {

                $productFeatureImage = $params['optradio'];
            } else {
                if (in_array($productThumbnail, $productImage)) {
                    $productFeatureImage = $productThumbnail;
                } else {
                    $productFeatureImage = $productImage[0];
                }
            }

            foreach ($productImage as $key => $val) {
                if (!is_numeric($val)) {
                    $image = $val;
                    $attachmentID = $this->getAttachmentId($image, 'image', false);

                    if ($productFeatureImage == '') {
                        $productFeatureImage = $attachmentID;
                    }
                    $productGallery[] = $attachmentID;
                } else {
                    $productGallery[] = $val;
                }
                $counter++;
            }

            // dump($productFeatureImage);
            if (isset($productGallery)) {
                foreach ($productGallery as $key => $value) {
                    if ($value == $productFeatureImage) {

                        $productFeatureImage = $value;
                    }
                }
            }
        }

        if (isset($params['productthumbnal-crp']) && !is_numeric($params['productthumbnal-crp'])) {

            $attachmentID = $this->getAttachmentId($params['productthumbnal-crp'], 'image', false);
            $productFeatureImage = $attachmentID;
        }
        if ($productFeatureImage && !is_numeric($productFeatureImage)) {
            $attachmentID = $this->getAttachmentId($productFeatureImage, 'image', false);
            $productFeatureImage = $attachmentID;
        }
        $category = isset($params['productCategory']) ? $params['productCategory'] : null;
        if (is_array($category) && (count($category) > 0)) {
            DB::table('product_category')->where('product_id', '=', $params['edit_product_id'])->delete();
            foreach ($category as $category_id) {
                if ($category_id) {

                    $ProductCategory = new CategoryProduct();
                    $ProductCategory->category_id = $category_id;
                    $ProductCategory->product_id = $params['edit_product_id'];
                    $ProductCategory->save();
                }
            }
        }
        if (!empty($faqQuestion)) {
            DB::table('product_faqs')->where('product_id', '=', $params['edit_product_id'])->delete();
            foreach ($faqQuestion as $key => $question) {
                $productFaq = new ProductFaq();
                if (strpos($question, '?') !== false) {
                } else {
                    $question = $question . " ?";
                }
                $productFaq->question = $question;
                $productFaq->answer = $faqAnswer[$key];
                $productFaq->product_id = $params['edit_product_id'];
                $productFaq->save();
            }
        } else {
            $productFaq = new ProductFaq();
            $productFaq->where('product_id', $params['edit_product_id'])->delete();
        }
        DB::table('products')
            ->where('product_id', $params['edit_product_id'])
            ->update(['feature_Image' => $productFeatureImage, 'gallery' => serialize($productGallery)]);
        $productTags = isset($params['productTags']) ? $params['productTags'] : null;

        if ($productTags) {
            $productTags = explode(",", $params['productTags']);
            DB::table('product_tags')->where('product_id', '=', $params['edit_product_id'])->delete();
            foreach ($productTags as $tag_value) {
                if ($tag_value) {
                    $ProductTags = new ProductTags();
                    $ProductTags->product_id = $params['edit_product_id'];
                    $ProductTags->tag_title = $tag_value;
                    $ProductTags->save();
                }
            }
        }
        if (!empty($deleteFileIds)) {
            $ids = explode(',', $deleteFileIds);
            $deleteFiles = DigitalFiles::whereIn('id', $ids)->get();
            //echo 'Data: <pre>' .print_r($deleteFiles,true). '</pre>'; exit;
            foreach ($deleteFiles as $delFile) {
                $file = public_path('uploads/digital/' . $delFile->file_name);
                if (File::exists($file)) {
                    File::delete($file);
                }
                $delFile->delete();
            }
            //DigitalFiles::destroy($deleteFileIds);
        }
        if ($productType == 'virtual') {
            $files = !empty($params['product_file']) ? $params['product_file'] : '';
            if (!empty($files)) {
                foreach ($files as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $OriginalfileName = $file->getClientOriginalName();
                    $destinationPath = public_path() . '/uploads/digital/';
                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath, 0775, true);
                    }
                    $file->move($destinationPath, $fileName);
                    $productFiles = new DigitalFiles();
                    $productFiles->product_id = $params['edit_product_id'];
                    $productFiles->file_name = $fileName;
                    $productFiles->orignal_file_name = $OriginalfileName;
                    $productFiles->save();
                }
            }
        }
        try {
            $product = Product::where('product_id', '=', $params['edit_product_id'])->first();

            $converted_title = $product->slug;
            $userId = Session::get('userData')['user_id'];
            $userData = getUserById($userId);

            $name = $userData->name;
            $store_id = $product_detail->store_id;

            $store = getStoreDataById($store_id);
            $store_user_id = $store[0]->user_id;
            $storeEmail = getUserById($store_user_id)->email ?? '';

            $subject = Constants::EDIT_PRODUCT_SUBJECT;

            if (isset($userData->user_id) && !is_null($userData->user_id)) {
                $userData = User::with('device_token')->findOrFail($userData->user_id);
                if ($userData) {
                    $tokens = [];
                    $adminId = Constants::SUPER_ADMIN_ID;
                    if ($userData->user_id == $adminId) {
                        $device_tokens = DeviceToken::where('user_id', $store_user_id)->where('app_flag', 'seller')->get();
                        if (isset($device_tokens) && count($device_tokens) > 0) {
                            foreach ($device_tokens as $device_token_obj) {
                                $subject = $subject;
                                $notification = SellerFcmService::send(
                                    $device_token_obj->fcm_token,
                                    [
                                        'title' => $subject,
                                    ]
                                );
                            }
                        }
                        addNotifications($store_user_id, getCurrentUserId(), $subject, "", URL::to('store-products'), 'store_products', '', 'seller');
                    }
                }
            }
            $html = view('mail.edit-product-by-admin', compact('productTitle', 'converted_title'));

            $parameter = [
                'from' => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                'to' => $storeEmail,
                'subject' => $subject,
                'html' => "$html",
            ];
            $this->mailgun->SendMailgunMail($parameter);
            // dd($userData->user_id);


        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
        $product->searchable();
        return $params['edit_product_id'];
    }
    public function edit_product_by_id_api($params)
    {
        //   dd($params);
        // return $params;
        $userID = auth()->user()->user_id;
        // $storeID = 1; 
        //  return 12;
        $storeID = getStoreInfoByUserid($userID);
        $productTitle = isset($params['productTitle']) ? $params['productTitle'] : '';
        $title_slug = substr($productTitle, 0, 20);
        $productShortDescription = isset($params['productShortDescription']) ? $params['productShortDescription'] : '';
        $productImage = isset($params['productImage']) ? $params['productImage'] : '';
        $productFeatureImage = isset($params['feature_image']) ? $params['feature_image'] : '';

        /* Pricing , Stock , Sku  */
        $productPrice = isset($params['productPrice']) ? $params['productPrice'] : 0;
        $shippingOptionId = isset($params['previousShippingOptions']) ? $params['previousShippingOptions'] : 0;

        $productCurrentStock = isset($params['productcurrentstock']) ? $params['productcurrentstock'] : 0;
        $productSKU = isset($params['productSKU']) ? $params['productSKU'] : null;
        $productDescription = isset($params['productDescription']) ? $params['productDescription'] : null;
        /* Dimensions  , Weight  */
        $productWeight = isset($params['productWeight']) ? round($params['productWeight']) : null;
        $productSizeLength = isset($params['productSize_length']) ? round($params['productSize_length']) : '';
        $productSizeWidth = isset($params['productSize_width']) ? round($params['productSize_width']) : '';
        $productSizeHeight = isset($params['productSize_height']) ? round($params['productSize_height']) : '';
        //  $productstatus = isset($params['product_status']) ? $params['product_status'] : '';
        $maxquantitylimit = isset($params['maxquantitylimit']) ? $params['maxquantitylimit'] : 10;
        $productResults = product::where('product_id', '=', $params['edit_product_id'])->select('title')
            ->get()->toArray();
        // return $productResults;
        /* Product Variations  */
        $variationData = isset($params['ProductvariationData']) ? $params['ProductvariationData'] : null;
        $faqQuestion = isset($params['faqQuestion']) ? $params['faqQuestion'] : '';
        $faqAnswer = isset($params['faqAnswer']) ? $params['faqAnswer'] : '';
        $weightType = isset($params['weight_type']) ? $params['weight_type'] : '';
        $dimentionType = isset($params['dimention_type']) ? $params['dimention_type'] : '';

        $formulas = new Formulas();
        $userRevenue = isset($params['productRevenue']) ? $params['productRevenue'] : $formulas->sellerRevenueStartLimit;
        $videoUrl = isset($params['productVideoURL']) ? $params['productVideoURL'] : "";
        $product_status = isset($params['product_status']) ? $params['product_status'] : "";
        $deleteFileIds = isset($params['delete_file_ids']) ? $params['delete_file_ids'] : "";
        $productType = isset($params['edit_product_type']) ? $params['edit_product_type'] : "";
        $item_condition = isset($params['item_condition']) ? $params['item_condition'] : "";
        if ($userRevenue < $formulas->sellerRevenueStartLimit || $userRevenue > $formulas->sellerRevenueEndLimit) {
            $userRevenue = $formulas->sellerRevenueStartLimit;
            $cashbackPool = $formulas->cashbackPoolEndLimit;
            $SuperSupporterPool = $formulas->supersupporterPoolEndLimit;
        } else {
            $cashbackPool = $formulas->getCashbackPercentage($userRevenue);
            $SuperSupporterPool = $formulas->getsupersupporterPercentage($userRevenue);
        }
        // return 12;

        if (!empty($variationData) && count($variationData) > 0) {
            $ProductInfo = array();
            $ProductInfo['productID'] = $params['edit_product_id'];
            $ProductInfo['feature_image'] = $productFeatureImage;
            $productInfo['store_id'] = $storeID->store_id;
            DB::table('product_variation')->where('product_id', $params['edit_product_id'])->delete();
            // return 12;
            $a = $this->UpdateProductVariationApi($variationData, $ProductInfo, 'update');
            // return 1;


        }
        if (isset($productResults[0]['title']) && $productResults[0]['title'] != $productTitle) {
            product::where('product_id', '=', $params['edit_product_id'])
                ->update(
                    ['slug' => $this->convert_to_slug($title_slug)]
                );
        }


        if ($product_status == 'draft') {
            $status = '3';
        } else if ($product_status == 'active') {
            $status = '1';
        } else if ($product_status == 'deactive') {
            $status = '0';
        } else if ($product_status == 'pending') {
            $status = '3';
        } else if ($product_status == 'out-of-stock') {
            $status = '1';
        } else if ($product_status == 'rejected') {
            $status = '3';
        }
        // return 12;
        // return $videoUrl;
        // return  $status;
        Product::where('product_id', '=', $params['edit_product_id'])
            ->update([
                'title' => $productTitle,
                'description' => $productDescription,
                'shipping_option_id' => $shippingOptionId,
                'current_stock' => $productCurrentStock,
                'sku' => $productSKU,
                'weight' => $productWeight,
                'status' => $status,
                'productSize_length' => $productSizeLength,
                'productSize_width' => $productSizeWidth,
                'productSize_height' => $productSizeHeight,
                'maxquantitylimit' => $maxquantitylimit,
                'short_description' => $productShortDescription,
                'video_url' => $videoUrl,
                'user_revenue' => $userRevenue,
                'cashback_pool' => $cashbackPool,
                'supersupporter_pool' => $SuperSupporterPool,
                'weight_type' => $weightType,
                'dimention_type' => $dimentionType,
                'item_condition' => $item_condition,
            ]);
        $discount = Discount::where(['store_id' => $storeID->store_id, 'status' => 'active', 'state' => 'live'])->first();

        if (isset($discount) && !empty($discount)) {
            $product = Product::findorfail($params['edit_product_id']);
            if ($product) {
                if ($discount->discount_value_type == 'percent') {
                    $discount_value = $productPrice * (1 - ($discount->discount_value / 100));
                    if ($discount_value >= 0.5) {
                        $product->on_sale = true;
                        $product->sale_price = $discount_value;
                        $product->discount_id = $discount->id;
                    }
                } elseif ($discount->discount_value_type == 'fixed') {
                    $discount_value = $productPrice - $discount->discount_value;
                    if ($discount_value >= 0.5) {
                        // Calculate percentage discount based on fixed amount
                        $discount_percentage = ($discount->discount_value / $productPrice) * 100;

                        // Apply the discount
                        $product->on_sale = true;
                        $product->sale_price = $discount_value;
                        $product->discount_percentage = $discount_percentage; // Optional: store percentage
                        $product->discount_id = $discount->id;
                    }
                }
                $product->save();
            }
        }
        if (isset($params['productvarientPrice'])) {
            $productPrice = isset($params['ProductvariationData']['price'][0]) ? $params['ProductvariationData']['price'][0] : 0;
            Product::where('product_id', '=', $params['edit_product_id'])
                ->update(['price' => $productPrice]);
        }
        if (isset($params['productPrice'])) {
            Product::where('product_id', '=', $params['edit_product_id'])
                ->update(['price' => $params['productPrice']]);
        }
        if (isset($params['productcondition'])) {
            Product::where('product_id', '=', $params['edit_product_id'])
                ->update(['product_condition' => $params['productcondition']]);
        }

        // check product status if its rejected then change the status to pending
        $product_detail = DB::table('products')
            ->where('product_id', $params['edit_product_id'])->first();
        // return $product_detail;
        if (!empty($product_detail) && $product_detail->status == 4) {
            Product::where('product_id', '=', $params['edit_product_id'])
                ->update(['status' => '3']);
        }

        $product_info = $this->getProductByID($params['edit_product_id']);
        //   return $product_info;
        $productThumbnail = !empty($product_info['featureImage']) ? $product_info['featureImage'] : null;

        /* If Product Feature Image , Gallery Images Exist */
        $counter = 0;
        // $productFeatureImage = '';
        $productGallery = array();
        $attachmentID = '';
        // return $productImage;

        if (isset($productImage) && $productImage != '') {

            foreach ($productImage as $key => $val) {
                if (!is_numeric($val)) {
                    $image = $val;
                    $attachmentID = $this->getAttachmentId($image, 'image', false);

                    if ($productFeatureImage == '') {
                        $productFeatureImage = $attachmentID;
                    }

                    $productGallery[] = $attachmentID;
                } else {
                    $productGallery[] = $val;
                }
                $counter++;
            }

            // dump($productFeatureImage);
            if (isset($productGallery)) {
                foreach ($productGallery as $key => $value) {
                    if ($value == $productFeatureImage) {

                        $productFeatureImage = $value;
                    }
                }
            }
        }


        if ($productFeatureImage && !is_numeric($productFeatureImage)) {
            $attachmentID = $this->getAttachmentId($productFeatureImage, 'image', false);
            $productFeatureImage = $attachmentID;
        }


        $category = isset($params['productCategory']) ? $params['productCategory'] : null;
        if (is_array($category) && (count($category) > 0)) {
            DB::table('product_category')->where('product_id', '=', $params['edit_product_id'])->delete();
            foreach ($category as $category_id) {
                if ($category_id) {

                    $ProductCategory = new CategoryProduct();
                    $ProductCategory->category_id = $category_id;
                    $ProductCategory->product_id = $params['edit_product_id'];
                    $ProductCategory->save();
                }
            }
        }
        if (!empty($faqQuestion)) {
            DB::table('product_faqs')->where('product_id', '=', $params['edit_product_id'])->delete();
            foreach ($faqQuestion as $key => $question) {
                $productFaq = new ProductFaq();
                if (strpos($question, '?') !== false) {
                } else {
                    $question = $question . " ?";
                }
                $productFaq->question = $question;
                $productFaq->answer = $faqAnswer[$key];
                $productFaq->product_id = $params['edit_product_id'];
                $productFaq->save();
            }
        } else {
            $productFaq = new ProductFaq();
            $productFaq->where('product_id', $params['edit_product_id'])->delete();
        }
        // return 12;

        DB::table('products')
            ->where('product_id', $params['edit_product_id'])
            ->update(['feature_Image' => $productFeatureImage, 'gallery' => serialize($productGallery)]);
        $productTags = isset($params['productTags']) ? $params['productTags'] : null;
        // return $productTags;
        if ($productTags) {
            // $productTags = explode(",", $params['productTags']);
            DB::table('product_tags')->where('product_id', '=', $params['edit_product_id'])->delete();
            foreach ($productTags as $tag_value) {
                if ($tag_value) {
                    $ProductTags = new ProductTags();
                    $ProductTags->product_id = $params['edit_product_id'];
                    $ProductTags->tag_title = $tag_value;
                    $ProductTags->save();
                }
            }
        }
        // return 12;
        // return $deleteFileIds;
        if (isset($deleteFileIds) && !empty($deleteFileIds)) {
            // return 12;
            $ids = explode(',', $deleteFileIds);
            $deleteFiles = DigitalFiles::whereIn('id', $ids)->get();
            //echo 'Data: <pre>' .print_r($deleteFiles,true). '</pre>'; exit;
            foreach ($deleteFiles as $delFile) {
                $file = public_path('uploads/digital/' . $delFile->file_name);
                if (File::exists($file)) {
                    File::delete($file);
                }
                $delFile->delete();
            }
            //DigitalFiles::destroy($deleteFileIds);
        }
        if ($productType == 'virtual') {
            $files = !empty($params['product_file']) ? $params['product_file'] : '';
            if (!empty($files)) {
                foreach ($files as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $OriginalfileName = $file->getClientOriginalName();
                    $destinationPath = public_path() . '/uploads/digital/';
                    if (!File::exists($destinationPath)) {
                        File::makeDirectory($destinationPath, 0775, true);
                    }
                    $file->move($destinationPath, $fileName);
                    $productFiles = new DigitalFiles();
                    $productFiles->product_id = $params['edit_product_id'];
                    $productFiles->file_name = $fileName;
                    $productFiles->orignal_file_name = $OriginalfileName;
                    $productFiles->save();
                }
            }
        }
        try {

            $product = Product::where('product_id', '=', $params['edit_product_id'])->first();
            $product->status = getProductStatus($product->product_id);
            $converted_title = $product->slug;
            // $userId = Session::get('userData')['user_id'];
            $userData = getUserById($userID);
            $name = $userData->name;

            $subject = $name . ' has edited PRODUCT';
            $html = view('mail.edit-product-by-admin', compact('productTitle', 'converted_title'));

            $parameter = [
                'from' => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                'to' => 'noman@mindblazetech.com',
                'subject' => $subject,
                'html' => "$html",
            ];
            $this->mailgun->SendMailgunMail($parameter);
            // addNotifications(12, getCurrentUserId(), $name . ' has edited Product', "", URL::to('/admin/store-products'));

        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
        $product->searchable();
        return $product;
    }
    public function getVariationsInfo($data)
    {

        if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation1']) && $data['data']['variation1'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('option2', '=', $data['data']['variation2'])
                ->where('option3', '=', $data['data']['variation3'])
                // ->where('visibility', 1)
                ->where('current_stock', '>', 0)
                ->select('price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation1']) && $data['data']['variation1'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('option2', '=', $data['data']['variation2'])
                ->select('price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage')
                // ->where('visibility', 1)
                ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                ->where('option2', '=', $data['data']['variation2'])
                // ->where('visibility', 1)
                ->select('price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage')
                ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation1']) && $data['data']['variation1'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('current_stock', '>', 0)
                // ->where('visibility', 1)
                ->select('price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image', 'discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation1']) && $data['data']['variation1'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                // ->where('visibility', 1)
                ->select('price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage')
                ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option2', '=', $data['data']['variation2'])
                // ->where('visibility', 1)
                ->where('current_stock', '>', 0)
                ->select('price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit', 'discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                // ->where('visibility', 1)
                ->where('current_stock', '>', 0)
                ->select('price', 'sale_price', 'current_stock', 'maxquantitylimit', 'discount_percentage', 'product_variation_image')
                ->get()
                ->toArray();
        }
    }
    public function getVariationsInfoApi($data)
    {
  
        if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation1']) && $data['data']['variation1'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('option2', '=', $data['data']['variation2'])
                ->where('option3', '=', $data['data']['variation3'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image','discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation1']) && $data['data']['variation1'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                ->where('option2', '=', $data['data']['variation2'])
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit','discount_percentage')
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
                
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                ->where('option2', '=', $data['data']['variation2'])
                // ->where('visibility', 1)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image','discount_percentage')
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation1']) && $data['data']['variation1'] != '' && isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                ->where('option1', '=', $data['data']['variation1'])
                // ->where('variation_id','current_stock', '>', 0)
                // ->where('visibility', 1)
                ->select('price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image','discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation1']) && $data['data']['variation1'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option1', '=', $data['data']['variation1'])
                // ->where('visibility', 1)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit','discount_percentage')
                // ->where('current_stock', '>', 0)
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation2']) && $data['data']['variation2'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option2', '=', $data['data']['variation2'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'product_variation_image', 'maxquantitylimit','discount_percentage')
                ->get()
                ->toArray();
        } else if (isset($data['data']['variation3']) && $data['data']['variation3'] != '') {

            return $results = ProductVariation::where('product_id', '=', $data['data']['productId'])
                ->where('option3', '=', $data['data']['variation3'])
                // ->where('visibility', 1)
                // ->where('current_stock', '>', 0)
                ->select('variation_id', 'price', 'sale_price', 'current_stock', 'maxquantitylimit', 'product_variation_image','discount_percentage')
                ->get()
                ->toArray();
        }
    }
  

    public function getProductDetailBySlug($slug)
    {

        $curentUserId = getCurrentUserId();
        $query = $this->_product->where('slug', '=', $slug)->where('status', '1')->firstOrFail();
        $productUserId = $query->user_id;
        if (!empty($productUserId) && !empty($productUserId) && $curentUserId == $productUserId) {
            $resultData = $query;
        } else {
            $query = $this->_product->where('slug', '=', $slug)->where('status', '1')->firstOrFail();
            $resultData = $query;
        }

        if (!empty($resultData)) {
            return $resultData->toArray();
        } else {
            return false;
        }
    }
    public function getProductDetailBySlugApi($slug, $user_id)
    {
        $resultData = Product::with([
            'shippingOption',
            'store',
            'favouriteProducts' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }
        ])
            ->where('slug', '=', $slug)
            ->where('status', '1')
            ->first();
        if (isset($resultData)) {


            $state = getStatebyId($resultData['store']['store_state']);
            $query = DB::table('shipping_options')->where('id', $resultData->shipping_option_id)->first();

            $arr = [];
            if (isset($query) && !is_null($query) && $query->is_domesticfree == 0) {
                // if ($query->is_domesticfree == 0) {
                $shipService = $query->shipping_service;
                $shipService_uspspri = 'Priority';
                $handlingFee = !empty($query->domestic_handling_fee) ? $query->domestic_handling_fee : 0;

                if ($query->flat_shipping_rate > 0) {

                    $arr['shipping_method'] = 'flat';
                    $arr['method'] = 'flat';
                    $arr['shipping_price'] = (float) $query->flat_shipping_rate;
                } else {

                    $arr['shipping_method'] = $shipService_uspspri;
                    $arr['method'] = 'calculated';
                    $arr['shipping_price'] = 0;
                }
            } else {

                $arr['shipping_price'] = 0;
                $arr['shipping_method'] = 'free';
                $arr['method'] = 'free';
            }



            // } else {

            //     if($arr['product_type'] == 'virtual'){

            //         $arr['shipping_method'] = ''; 
            //         $arr['method'] = 'free';    
            //         $arr['shipping_price'] = 0;

            //      }
            // }
            if (isset($resultData->favouriteProducts) && $resultData->favouriteProducts->count() > 0) {
                $resultData->is_favourite = true;
            } else {
                $resultData->is_favourite = false;
            }
            $resultData->shippings = $arr;
            $resultData['store']['store_state'] = $state;
            if (!empty($resultData)) {
                return $resultData->toArray();
            } else {
                return false;
            }
        }
    }
    public function getSellerProductDetailBySlugApi($slug, $user_id)
    {
        $resultData = Product::with([
            'shippingOption',
            'store',
            'favouriteProducts' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }
        ])
            ->where('slug', '=', $slug)
            ->first();
        if (isset($resultData)) {


            $state = getStatebyId($resultData['store']['store_state']);
            $query = DB::table('shipping_options')->where('id', $resultData->shipping_option_id)->first();

            $arr = [];
            if (isset($query) && !is_null($query) && $query->is_domesticfree == 0) {
                // if ($query->is_domesticfree == 0) {
                $shipService = $query->shipping_service;
                $shipService_uspspri = 'Priority';
                $handlingFee = !empty($query->domestic_handling_fee) ? $query->domestic_handling_fee : 0;

                if ($query->flat_shipping_rate > 0) {

                    $arr['shipping_method'] = 'flat';
                    $arr['method'] = 'flat';
                    $arr['shipping_price'] = (float) $query->flat_shipping_rate;
                } else {

                    $arr['shipping_method'] = $shipService_uspspri;
                    $arr['method'] = 'calculated';
                    $arr['shipping_price'] = 0;
                }
            } else {

                $arr['shipping_price'] = 0;
                $arr['shipping_method'] = 'free';
                $arr['method'] = 'free';
            }



            // } else {

            //     if($arr['product_type'] == 'virtual'){

            //         $arr['shipping_method'] = ''; 
            //         $arr['method'] = 'free';    
            //         $arr['shipping_price'] = 0;

            //      }
            // }
            if (isset($resultData->favouriteProducts) && $resultData->favouriteProducts->count() > 0) {
                $resultData->is_favourite = true;
            } else {
                $resultData->is_favourite = false;
            }
            $resultData->shippings = $arr;
            $resultData['store']['store_state'] = $state;
            if (!empty($resultData)) {
                return $resultData->toArray();
            } else {
                return false;
            }
        }
    }
    public function getRemainPledgeAmount($storeId)
    {
        $remainPledges = 0;
        $query = DB::table('store_campaigns')->select('pledges_used', 'pledges', 'toal_gave_back')
            ->where('store_id', $storeId)->where('status', 'active')->first();
        if (!empty($query)) {
            $pledgesUsed = !empty($query->toal_gave_back) ? $query->toal_gave_back : 0;
            $collectedPledges = !empty($query->pledges) ? $query->pledges : 0;
            $remainPledges = ($pledgesUsed / $collectedPledges * 100);
            $remainPledges = 100 - round($remainPledges);
            return $remainPledges;
        } else {
            return false;
        }
    }

    public function getCateoriesOfProducts($productId)
    {
        $categoryData = array();
        $query = DB::table('product_category')->where('product_id', '=', $productId)->get();
        if (!empty($query)) {
            $productCategories = $this->objToArray($query);
            foreach ($productCategories as $pg) {
                $query2 = DB::table('categories')->where('id', '=', $pg['category_id'])->get();
                if (!empty($query2)) {
                    $categoryData[] = $query2;
                }
            }
            return $this->objToArray($categoryData);
        } else {
            return false;
        }
    }

    public function getCateoriesOfProductsApi($productId)
    {
        $categoryData = array();
        $query = DB::table('product_category')->where('product_id', '=', $productId)->get();

        if (!empty($query)) {
            $productCategories = $this->objToArray($query);

            foreach ($productCategories as $pg) {
                $query2 = DB::table('categories')->where('id', '=', $pg['category_id'])->first();

                if (!empty($query2)) {
                    $categoryData[] = $this->objToArray($query2);
                }
            }

            return $categoryData;
        } else {
            return false;
        }
    }


    public function countStoreOrders($storeId)
    {
        $count = DB::table('order_items')
            ->where('order_items.store_id', $storeId)
            ->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }

    public function getStoreProductCount($storeId)
    {
        $count = $this->_product->where('store_id', '=', $storeId)->where('status', '=', '1')->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }

    public function getProductsReports()
    {

        if (Session::has('userData')) {
            $userId = Session::get('userData')['user_id'];
        } else {
            $userId = Request::segment(2);
        }
        $storeData = getStoreDataByUserId($userId);
        $storeId = $storeData->store_id;
        $query = $this->_product->select('product_id', 'title')->where('store_id', '=', $storeId)->where('status', '=', '1')->get();

        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }
    public function getProductsReportsApi()
    {
        $userId = Auth::user()->user_id;

        $storeData = getStoreDataByUserId($userId);
        // echo '$storeData: <pre>' .print_r($storeData,true). '</pre>'; die();
        $storeId = isset($storeData->store_id) ? $storeData->store_id : "";
        $query = $this->_product->select('product_id', 'title')->where('store_id', '=', $storeId)->where('status', '=', '1')->get();

        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function objToArray($arr)
    {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    public function askQuestion($data)
    {
        $userInfo = getProductAuthorById($data['product_id']);

        $product_info = $this->getProductByID($data['product_id']);
        $product_name = isset($product_info['title']) ? $product_info['title'] : '';
        $current_user = getUserById(getCurrentUserId());
        $current_user_email = $current_user->email ? $current_user->email : "";
        $toCompletename = $userInfo->name ? $userInfo->name : "";
        $userEmail = $userInfo->email;
        $templateSlug = 'faqs';
        $question = $data['question'];
        $varsArray = array();
        $varsArray[] = array('name' => 'USER_NAME', 'content' => $toCompletename);
        $varsArray[] = array('name' => 'PRODUCT_NAME', 'content' => $product_name);
        $varsArray[] = array('name' => 'USER_EMAIL', 'content' => $current_user_email);
        $varsArray[] = array('name' => 'QUESTION', 'content' => $question);

        if (!empty($question)) {

            $productFaq = new ProductFaq();
            if (strpos($question, '?') !== false) {
            } else {
                $question = $question . " ?";
            }
            $productFaq->question = $question;
            $productFaq->product_id = $data['product_id'];
            $productFaq->save();
        }
        if ($userEmail != '') {

            //echo $userEmail;

            $subject = 'You Have a Question!';
            $html = view('mail.UserFaqsQuestions', compact('varsArray'));

            $params = [
                'from' => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                'to' => $userEmail,
                'subject' => $subject,
                'html' => "$html",
            ];
            $this->mailgun->SendMailgunMail($params);
            return true;
            //echo $data['question'];
        } else {
            return false;
        }
    }

    public function askQuestionApi($data)
    {
        $userInfo = getProductAuthorById($data['product_id']);
        $product_info = $this->getProductByID($data['product_id']);
        $product_name = isset($product_info['title']) ? $product_info['title'] : '';
        $toCompletename = $userInfo->name ? $userInfo->name : "";
        $userEmail = $userInfo->email;
        $question = $data['question'];
        $varsArray = array();
        $varsArray[] = array('name' => 'USER_NAME', 'content' => $toCompletename);
        $varsArray[] = array('name' => 'PRODUCT_NAME', 'content' => $product_name);
        $varsArray[] = array('name' => 'USER_EMAIL', 'content' => $userEmail);
        $varsArray[] = array('name' => 'QUESTION', 'content' => $question);

        if (!empty($question)) {

            $productFaq = new ProductFaq();
            if (strpos($question, '?') !== false) {
            } else {
                $question = $question . " ?";
            }
            $productFaq->question = $question;
            $productFaq->product_id = $data['product_id'];
            $productFaq->save();

            if ($userEmail != '') {
                $subject = 'You Have a Question!';
                $html = view('mail.UserFaqsQuestions', compact('varsArray'));

                $params = [
                    'from' => 'MallShark  ' . env('NO_REPLY_EMAIL'),
                    'to' => $userEmail,
                    'subject' => $subject,
                    'html' => "$html",
                ];
                $this->mailgun->SendMailgunMail($params);
            }
            return [
                'status' => true,
                'data' => $productFaq
            ];
        } else {
            return ['status' => true];
        }
    }

    public function saveShippingProfile($data)
    {
        // $userId = Session::get('userData')['user_id'];
        $userId = isset($data['store_id']) ? Store::where('store_id', $data['store_id'])->pluck('user_id')->first() : null;
        if (isset($data['process_time']) && $data['process_time'] == "custom-range") {
            $processTime = new ProcessTime();
            $processTime->user_id = $userId;
            $processTime->process_start_range = isset($data['start_range']) ? $data['start_range'] : '';
            $processTime->process_end_range = isset($data['end_range']) ? $data['end_range'] : '';
            $processTime->process_unit = isset($data['process_unit']) ? $data['process_unit'] : '';
            $processTime->process_type = 'custom';
            $processTime->save();
            $processTimeId = $processTime->processing_time_id;
            $data['process_time'] = $processTimeId;
        }
        $is_domesticfree = 0;
        if (isset($data['select-shipoption']) && $data['select-shipoption'] == 'united-state') {

            $shipCountryId = 1;
            $data['flat_shipping_rate'] = 0;
        } else if (isset($data['select-shipoption']) && $data['select-shipoption'] == 'free') {
            $data['flat_shipping_rate'] = 0;
            $is_domesticfree = 1;
        }
        // echo '$data: <pre>' .print_r($data['shipping_width'],true). '</pre>';

        if (isset($data['profile_name']) && !empty($data['profile_name'])) {
            // Query the database to check for existing shipping options with the same name for the current user
            $existingShippingOption = ShippingOption::where('user_id', $userId)
                ->where('profile_name', $data['profile_name'])->where('delete_status', 1)
                ->first();

            // If a shipping option with the same name already exists, handle the error

            if (!is_null($existingShippingOption)) {
                // Handle the error (e.g., return a response or throw an exception)
                // For example, you can return a response indicating that the shipping option name is already in use
                return false;
            }
        }
        $shippingOptions = new ShippingOption();
        $shippingOptions->user_id = !empty($userId) ? $userId : '';
        $shippingOptions->processing_time_id = isset($data['process_time']) ? $data['process_time'] : '';
        $shippingOptions->profile_name = isset($data['profile_name']) ? $data['profile_name'] : '';
        $shippingOptions->shipping_origin = isset($data['shipping_origin']) ? $data['shipping_origin'] : '';
        $shippingOptions->origin_zipcode = isset($data['origin_zipcode']) ? $data['origin_zipcode'] : '';
        // $shippingOptions->is_domesticfree = isset($data['international_free_shipping']) ? 1 : 0;
        $shippingOptions->is_domesticfree = $is_domesticfree;
        // $shippingOptions->is_internationalfree = isset($data['domestic_free_shipping']) ? 1 : 0;
        $shippingOptions->domestic_handling_fee = isset($data['domestic_handling_fee']) ? $data['domestic_handling_fee'] : 0;
        $shippingOptions->international_handling_fee = isset($data['international_handling_fee']) ? $data['international_handling_fee'] : 0;
        // $shippingOptions->shipping_service = isset($data['add_ship_service']) ? $data['add_ship_service'] : '';
        $shippingOptions->shipping_service = isset($data['edit_ship_service']) ? implode(',', $data['edit_ship_service']) : '';
        // $shippingOptions->shipping_country_id = $shipCountryId;
        $shippingOptions->flat_shipping_rate = isset($data['flat_shipping_rate']) ? $data['flat_shipping_rate'] : '';
        $shippingOptions->shipping_country_id = isset($data['shipping_country_id']) ? $data['shipping_country_id'] : '';
        $shippingOptions->shipping_state_id = isset($data['shipping_state_id']) ? $data['shipping_state_id'] : '';
        $shippingOptions->shipping_city = isset($data['shipping_city']) ? $data['shipping_city'] : '';
        $shippingOptions->shipping_address1 = isset($data['shipping_address1']) ? $data['shipping_address1'] : '';
        $shippingOptions->shipping_address2 = isset($data['shipping_address2']) ? $data['shipping_address2'] : '';
        $shippingOptions->store_id = isset($data['store_id']) ? $data['store_id'] : '';
        //   echo '$shippingOptions: <pre>' .print_r($shippingOptions,true). '</pre>'; die;
        $shippingOptions->save();
        return true;
    }
    public function saveShippingProfileApi($data)
    {
        $userId = Auth::user()->user_id;

        if (ShippingOption::where('profile_name', $data['profile_name'])->where('user_id', $userId)->where('delete_status', 1)->exists()) {

            return [
                'status' => false,
                'message' => 'Profile name already exists'
            ];
        } else {


            $is_domesticfree = 0;
            if (isset($data['select_shipoption']) && $data['select_shipoption'] == 'united-state') {

                $data['flat_shipping_rate'] = 0;
            } else if (isset($data['select_shipoption']) && $data['select_shipoption'] == 'free') {
                $data['flat_shipping_rate'] = 0;
                $is_domesticfree = 1;
            }
            // echo '$data: <pre>' .print_r($data['shipping_width'],true). '</pre>';


            $shippingOptions = new ShippingOption();
            $shippingOptions->user_id = !empty($userId) ? $userId : '';
            $shippingOptions->profile_name = isset($data['profile_name']) ? $data['profile_name'] : '';
            $shippingOptions->shipping_origin = isset($data['shipping_origin']) ? $data['shipping_origin'] : '';
            $shippingOptions->origin_zipcode = isset($data['origin_zipcode']) ? $data['origin_zipcode'] : '';
            // $shippingOptions->is_domesticfree = isset($data['international_free_shipping']) ? 1 : 0;
            $shippingOptions->is_domesticfree = $is_domesticfree;
            // $shippingOptions->is_internationalfree = isset($data['domestic_free_shipping']) ? 1 : 0;
            $shippingOptions->domestic_handling_fee = isset($data['domestic_handling_fee']) ? $data['domestic_handling_fee'] : 0;
            $shippingOptions->international_handling_fee = isset($data['international_handling_fee']) ? $data['international_handling_fee'] : 0;
            // $shippingOptions->shipping_service = isset($data['add_ship_service']) ? $data['add_ship_service'] : '';
            $shippingOptions->shipping_service = isset($data['edit_ship_service']) ? implode(',', $data['edit_ship_service']) : '';
            // $shippingOptions->shipping_country_id = $shipCountryId;
            $shippingOptions->flat_shipping_rate = isset($data['flat_shipping_rate']) ? $data['flat_shipping_rate'] : '';
            $shippingOptions->shipping_country_id = isset($data['shipping_country_id']) ? $data['shipping_country_id'] : '';
            $shippingOptions->shipping_state_id = isset($data['shipping_state_id']) ? $data['shipping_state_id'] : '';
            $shippingOptions->shipping_city = isset($data['shipping_city']) ? $data['shipping_city'] : '';
            $shippingOptions->shipping_address1 = isset($data['shipping_address1']) ? $data['shipping_address1'] : '';
            $shippingOptions->shipping_address2 = isset($data['shipping_address2']) ? $data['shipping_address2'] : '';
            $shippingOptions->store_id = isset($data['store_id']) ? $data['store_id'] : '';
            //   echo '$shippingOptions: <pre>' .print_r($shippingOptions,true). '</pre>'; die;
            $shippingOptions->save();
            return ['status' => true, 'data' => $shippingOptions];
        }
    }

    public function updateShippingProfile($data)
    {
        $updateParms = array();
        $userId = Session::get('userData')['user_id'];
        if (isset($data['process_time']) && $data['process_time'] == "custom-range") {
            $processTime = new ProcessTime();
            $processTime->user_id = $userId;
            $processTime->process_start_range = isset($data['start_range']) ? $data['start_range'] : '';
            $processTime->process_end_range = isset($data['end_range']) ? $data['end_range'] : '';
            $processTime->process_unit = isset($data['process_unit']) ? $data['process_unit'] : '';
            $processTime->process_type = 'custom';
            $processTime->save();
            $processTimeId = $processTime->processing_time_id;
            $data['process_time'] = $processTimeId;
        }
        $is_domesticfree = 0;
        if (isset($data['select-shipoption']) && $data['select-shipoption'] == 'united-state') {

            $data['flat_shipping_rate'] = 0;
        } else if (isset($data['select-shipoption']) && $data['select-shipoption'] == 'free') {
            $data['flat_shipping_rate'] = 0;
            $is_domesticfree = 1;
        }
        if (isset($data['profile_name']) && !empty($data['profile_name'])) {
            // Query the database to check for existing shipping options with the same name for the current user
            $existingShippingOption = ShippingOption::where('id', $data['shipping_option_id'])->where('user_id', $userId)
                ->where('profile_name', $data['profile_name'])->where('delete_status', 1)
                ->first();

            // If a shipping option with the same name already exists, handle the error
            if (is_null($existingShippingOption)) {
                $existingShippingOption = ShippingOption::where('user_id', $userId)
                    ->where('profile_name', $data['profile_name'])->where('delete_status', 1)
                    ->first();
                if (!is_null($existingShippingOption)) {
                    return false;
                } else {
                    $shippingOptions = new ShippingOption();
                    // $updateParms['shipping_service'] = isset($data['edit_ship_service']) ? $data['edit_ship_service'] : '';
                    $updateParms['shipping_service'] = isset($data['edit_ship_service']) ? implode(',', $data['edit_ship_service']) : '';

                    $updateParms['user_id'] = $userId;
                    $updateParms['processing_time_id'] = isset($data['process_time']) ? $data['process_time'] : '';
                    $updateParms['profile_name'] = isset($data['profile_name']) ? $data['profile_name'] : '';
                    $updateParms['shipping_origin'] = isset($data['shipping_origin']) ? $data['shipping_origin'] : '';
                    $updateParms['origin_zipcode'] = isset($data['origin_zipcode']) ? $data['origin_zipcode'] : '';
                    $updateParms['is_internationalfree'] = isset($data['international_free_shipping']) ? 1 : 0;
                    $updateParms['is_domesticfree'] = $is_domesticfree;
                    $updateParms['domestic_handling_fee'] = isset($data['domestic_handling_fee']) ? $data['domestic_handling_fee'] : '';
                    $updateParms['international_handling_fee'] = isset($data['international_handling_fee']) ? $data['international_handling_fee'] : '';
                    //        $updateParms['shipping_service'] = isset($data['ship_service']) ? $data['ship_service'] : '';

                    $updateParms['shipping_country_id'] = isset($data['shipping_country_id']) ? $data['shipping_country_id'] : '';
                    $updateParms['shipping_state_id'] = isset($data['shipping_state_id']) ? $data['shipping_state_id'] : '';
                    $updateParms['shipping_city'] = isset($data['shipping_city']) ? $data['shipping_city'] : '';
                    $updateParms['shipping_address1'] = isset($data['shipping_address1']) ? $data['shipping_address1'] : '';
                    $updateParms['shipping_address2'] = isset($data['shipping_address2']) ? $data['shipping_address2'] : '';
                    $updateParms['flat_shipping_rate'] = isset($data['flat_shipping_rate']) ? $data['flat_shipping_rate'] : '';

                    $shippingOptions->where('id', $data['shipping_option_id'])->update($updateParms);
                    return true;
                }

                // Handle the error (e.g., return a response or throw an exception)
                // For example, you can return a response indicating that the shipping option name is already in use

            }
        }
        $shippingOptions = new ShippingOption();
        // $updateParms['shipping_service'] = isset($data['edit_ship_service']) ? $data['edit_ship_service'] : '';
        $updateParms['shipping_service'] = isset($data['edit_ship_service']) ? implode(',', $data['edit_ship_service']) : '';

        $updateParms['user_id'] = $userId;
        $updateParms['processing_time_id'] = isset($data['process_time']) ? $data['process_time'] : '';
        $updateParms['profile_name'] = isset($data['profile_name']) ? $data['profile_name'] : '';
        $updateParms['shipping_origin'] = isset($data['shipping_origin']) ? $data['shipping_origin'] : '';
        $updateParms['origin_zipcode'] = isset($data['origin_zipcode']) ? $data['origin_zipcode'] : '';
        $updateParms['is_internationalfree'] = isset($data['international_free_shipping']) ? 1 : 0;
        $updateParms['is_domesticfree'] = $is_domesticfree;
        $updateParms['domestic_handling_fee'] = isset($data['domestic_handling_fee']) ? $data['domestic_handling_fee'] : '';
        $updateParms['international_handling_fee'] = isset($data['international_handling_fee']) ? $data['international_handling_fee'] : '';
        //        $updateParms['shipping_service'] = isset($data['ship_service']) ? $data['ship_service'] : '';

        $updateParms['shipping_country_id'] = isset($data['shipping_country_id']) ? $data['shipping_country_id'] : '';
        $updateParms['shipping_state_id'] = isset($data['shipping_state_id']) ? $data['shipping_state_id'] : '';
        $updateParms['shipping_city'] = isset($data['shipping_city']) ? $data['shipping_city'] : '';
        $updateParms['shipping_address1'] = isset($data['shipping_address1']) ? $data['shipping_address1'] : '';
        $updateParms['shipping_address2'] = isset($data['shipping_address2']) ? $data['shipping_address2'] : '';
        $updateParms['flat_shipping_rate'] = isset($data['flat_shipping_rate']) ? $data['flat_shipping_rate'] : '';

        $shippingOptions->where('id', $data['shipping_option_id'])->update($updateParms);
        return true;
    }
    public function updateShippingProfileApi($data)
    {
        // return $data['flat_shipping_rate'];
        $updateParms = array();
        $userId = auth()->user()->user_id;
        $is_domesticfree = 0;
        if (isset($data['select_shipoption']) && $data['select_shipoption'] == 'united-state') {

            $data['flat_shipping_rate'] = 0;
        } else if (isset($data['select_shipoption']) && $data['select_shipoption'] == 'free') {
            $data['flat_shipping_rate'] = 0;
            $is_domesticfree = 1;
        }
        $shippingOptions = new ShippingOption();
        $updateParms['shipping_service'] = isset($data['edit_ship_service']) ? $data['edit_ship_service'] : '';
        $updateParms['shipping_service'] = isset($data['edit_ship_service']) ? implode(',', $data['edit_ship_service']) : '';

        $updateParms['user_id'] = $userId;
        $updateParms['processing_time_id'] = isset($data['process_time']) ? $data['process_time'] : '';
        $updateParms['profile_name'] = isset($data['profile_name']) ? $data['profile_name'] : '';
        $updateParms['shipping_origin'] = isset($data['shipping_origin']) ? $data['shipping_origin'] : '';
        $updateParms['origin_zipcode'] = isset($data['origin_zipcode']) ? $data['origin_zipcode'] : '';
        $updateParms['is_internationalfree'] = isset($data['international_free_shipping']) ? 1 : 0;
        $updateParms['is_domesticfree'] = $is_domesticfree;
        $updateParms['domestic_handling_fee'] = isset($data['domestic_handling_fee']) ? $data['domestic_handling_fee'] : '';
        $updateParms['international_handling_fee'] = isset($data['international_handling_fee']) ? $data['international_handling_fee'] : '';
        //        $updateParms['shipping_service'] = isset($data['ship_service']) ? $data['ship_service'] : '';

        $updateParms['shipping_country_id'] = isset($data['shipping_country_id']) ? $data['shipping_country_id'] : '';
        $updateParms['shipping_state_id'] = isset($data['shipping_state_id']) ? $data['shipping_state_id'] : '';
        $updateParms['shipping_city'] = isset($data['shipping_city']) ? $data['shipping_city'] : '';
        $updateParms['shipping_address1'] = isset($data['shipping_address1']) ? $data['shipping_address1'] : '';
        $updateParms['shipping_address2'] = isset($data['shipping_address2']) ? $data['shipping_address2'] : '';
        $updateParms['flat_shipping_rate'] = isset($data['flat_shipping_rate']) ? $data['flat_shipping_rate'] : '';
        // return $data['flat_shipping_rate'];
        $shippingOptions->where('id', $data['shipping_option_id'])->update($updateParms);
        return ['status' => true, 'data' => $shippingOptions];
    }
    public function getShippingProfile()
    {
        $data = array();
        $userId = Session::get('userData')['user_id'];
        $shippingOptions = new ShippingOption();
        // $query = $shippingOptions->where('user_id', $userId)->get()->toArray();
        $query = $shippingOptions->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereNull('user_id');
        })->orderBy('user_id', 'asc')->select('*')
            ->get()
            ->toArray();
        $query2 = ProcessTime::WhereNull('user_id')->orWhere('user_id', $userId)->get();
        $data['process_time'] = objToArray($query2);
        if (!empty($query)) {
            $data['shipping_profile'] = $query;
        }
        return $data;
    }

    public function getShippingDetail($productId)
    {
        $query = $this->_product->select('shipping_option_id')->where('product_id', $productId)->first();
        if (!empty($query)) {
            $res = DB::table('shipping_options')
                ->select('*')
                ->Join('processing_time', 'shipping_options.processing_time_id', '=', 'processing_time.processing_time_id')
                ->where('shipping_options.id', $query->shipping_option_id)->first();
            if (!empty($res)) {
                return $res;
            }
        } else {
            return false;
        }
    }

    public function getRelatedProducts($productId, $storeid)
    {
        $query2 = DB::table('product_category')
            ->select('products.product_id', 'products.product_type', 'products.user_id', 'products.current_stock', 'products.store_id', 'categories.categoryTitle', 'parent_categories.categoryTitle as parentCategoryTitle', 'products.title', 'products.slug', 'products.price', 'products.feature_image', 'products.on_sale', 'products.sale_price', 'products.discount_percentage')
            ->join('products', 'product_category.product_id', '=', 'products.product_id')
            ->join('categories', 'product_category.category_id', '=', 'categories.id')
            ->join('categories as parent_categories', 'categories.parentCategoryID', '=', 'parent_categories.id')
            ->where('products.status', '1')
            ->where('products.product_id', '!=', $productId)
            ->where('products.store_id', $storeid)
            ->orderBy('products.product_id', 'desc')
            ->take(6)
            ->get();
        if (!empty($query2)) {
            return $query2;
        } else {
            return false;
        }
    }
    // public function getRelatedProducts($productId, $storeid)
    // {
    //     $query = DB::table('product_category')->select('category_id')->where('product_id', $productId)->first();
    //     $query2 = DB::table('product_category')
    //         ->select('products.product_id', 'categories.categoryTitle', 'products.title', 'products.slug', 'products.price', 'products.feature_image')
    //         ->join('products', 'product_category.product_id', '=', 'products.product_id')
    //         ->join('categories', 'product_category.category_id', '=', 'categories.id')
    //         ->where('product_category.category_id', $query->category_id)->where('products.status', '1')
    //         ->where('products.product_id', '!=', $productId)
    //         ->where('products.store_id', $storeid)
    //         ->orderBy('products.product_id', 'desc')
    //         ->take(6)->get();
    //     if (!empty($query2)) {
    //         return $query2;
    //     } else {
    //         return false;
    //     }
    // }

    public function getFlashProducts($search = '')
    {
        $query = $this->_product->where('flashsale_status', 'choosen')->where('title', 'LIKE', "%" . $search . "%")->paginate(20);
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function getTopSellingProducts()
    {
        $query = DB::table('products')
            ->select('products.product_id', 'products.title', 'products.price', 'products.store_id', 'products.slug', 'products.feature_image', 'products.on_sale', 'products.sale_price', 'stores.store_icon', 'stores.wallet_address', 'stores.store_name', 'stores.store_slug')
            ->join('stores', 'products.store_id', '=', 'stores.store_id')
            ->where('top_selling', 1)->get();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function getTopSellingProductsApi($limit)
    {
        $query = DB::table('products')
            ->select(
                'products.product_id',
                'products.title',
                'products.price',
                'products.store_id',
                'products.slug',
                'products.feature_image',
                'products.on_sale',
                'products.sale_price',
                'stores.store_icon',
                'stores.wallet_address',
                'stores.store_name',
                'stores.store_slug'
            )
            ->join('stores', 'products.store_id', '=', 'stores.store_id')
            ->where('top_selling', 1)->where('status', 1)
            ->where('products.product_type', '!=', 'virtual')->paginate($limit)->toArray();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }



    public function getFeaturedProducts()
    {
        $query = DB::table('products')
            ->join('stores', 'products.store_id', '=', 'stores.store_id')
            ->select('products.product_id', 'products.title', 'products.price', 'products.store_id', 'products.slug', 'products.feature_image', 'products.on_sale', 'products.sale_price', 'stores.store_icon', 'stores.store_name', 'stores.store_slug')
            ->where('products.is_feature', '1')
            ->limit(8)->inRandomOrder()
            ->get();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }
    public function getFeaturedProductsApi($limit)
    {
        $query = DB::table('products')
            ->join('stores', 'products.store_id', '=', 'stores.store_id')
            ->select(
                'products.product_id',
                'products.title',
                'products.price',
                'products.store_id',
                'products.slug',
                'products.feature_image',
                'products.on_sale',
                'products.sale_price',
                'stores.store_icon',
                'stores.store_name',
                'stores.store_slug'
            )
            ->where('products.is_feature', '1')
            ->where('products.status', '1')
            ->where('products.product_type', '!=', 'virtual')
            ->paginate($limit);

        $items = $query->items();
        $shuffledItems = collect($items)->shuffle()->toArray();

        return $shuffledItems;
    }


    public function getHomePageFlashProducts($status = 0)
    {
        $query = DB::table('products')
            ->select('products.product_id', 'products.title', 'products.price', 'products.store_id', 'products.slug', 'products.feature_image', 'products.home_flash_sale', 'stores.store_icon', 'stores.store_name', 'stores.store_slug')
            ->join('stores', 'products.store_id', '=', 'stores.store_id')
            ->where('products.home_flash_sale', $status)
            ->where('products.flashsale_status', 'choosen');
        if ($status == 0) {
            $data = $query->get();
        } else {
            $data = $query->first();
        }

        if (!empty($query)) {
            return $data;
        } else {
            return false;
        }
    }

    public function getFlashSaleProductsToBegain()
    {
        $constants = config('constants.shopzit_gamification');
        $flashSaleTarget = ($constants['FLASH_SALE_TARGET'] * 90) / 100;
        $query = DB::table('store_campaigns')
            ->select('products.product_id', 'products.title', 'products.slug', 'products.price', 'products.feature_image', 'products.store_id', 'stores.store_icon', 'stores.store_slug', 'stores.store_name', 'store_campaigns.pledges')
            ->join('products', 'store_campaigns.product_id', '=', 'products.product_id')
            ->join('stores', 'products.store_id', '=', 'stores.store_id')
            ->where('store_campaigns.status', 'support')
            ->where('store_campaigns.pledges', '>=', $flashSaleTarget)
            ->get();
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }
    public function getFeatureProducts($page = '', $categories = '')
    {
        if ($page == 'true') {
            // $query = Product::where('feature_status', 1)->orderBy('product_id', 'desc')->paginate(50);
            $query = Product::with(['categories', 'store'])->where('feature_status', 1)
                ->whereHas('categories', function ($q) use ($categories) {
                    if (!empty($categories)) {
                        $q->where('category_slug', $categories);
                    }
                })
                ->whereHas('store', function ($query) {
                    $query->where('store_status', '2')->where('stripe', '1');
                })
                ->orderBy('product_id', 'desc')->paginate(50);
        } else {
            $query = Product::where('feature_status', 1)->orderBy('product_id', 'desc')->limit(10)->get();
        }
        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function getFeatureProductsApi($page = '', $categories = '', $limit)
    {
        if ($page == 'true') {
            $query = Product::with(['categories', 'store'])
                ->where('feature_status', 1)
                ->whereHas('categories', function ($q) use ($categories) {
                    if (!empty($categories)) {
                        $q->where('category_slug', $categories);
                    }
                })
                ->whereHas('store', function ($query) {
                    $query->where('store_status', '2')->where('stripe', '1');
                })
                ->orderBy('product_id', 'desc')
                ->paginate($limit);

            // Get the items from the pagination result
            $items = $query->items();

            // Shuffle the items in the collection
            $shuffledItems = collect($items)->shuffle()->toArray();

            // Replace the items in the pagination result with shuffled items
            $query->setCollection(collect($shuffledItems));

            return $query->toArray(); // Convert the paginator object to an array
        } else {
            $query = Product::where('feature_status', 1)
                ->orderBy('product_id', 'desc')
                ->paginate($limit)
                ->toArray();

            if (!empty($query)) {
                return $query;
            } else {
                return false;
            }
        }
    }

    public function getFeatureProductsApiPagination($tab = '', $categories = '', $limit, $page)
    {
        if ($tab == 'true') {
            $query = Product::with(['categories', 'store'])
                ->where('feature_status', 1)->where('status', 1)->where('product_type', '!=', 'virtual')
                ->whereHas('categories', function ($q) use ($categories) {
                    if (!empty($categories)) {
                        $q->where('category_slug', $categories);
                    }
                })
                ->whereHas('store', function ($query) {
                    $query->where('store_status', '2')->where('stripe', '1');
                })
                ->orderBy('product_id', 'desc')
                ->paginate($limit, ['*'], 'page', $page); // Paginate with page number and limit

            return [
                'current_page' => $query->currentPage(),
                'per_page' => $query->perPage(),
                'total' => $query->total(),
                'data' => $query->items(),
            ];
        } else {
            $query = Product::where('feature_status', 1)
                ->orderBy('product_id', 'desc')
                ->paginate($limit, ['*'], 'page', $page)
                ->toArray();

            if (!empty($query)) {
                return [
                    'current_page' => $query['current_page'],
                    'per_page' => $query['per_page'],
                    'total' => $query['total'],
                    'data' => $query['data'],
                ];
            } else {
                return false;
            }
        }
    }

    public function getRecommendedProducts($limit = 18)
    {
        $query = Product::with(['variations', 'store', 'categories'])
            ->where('status', '1')
            ->whereHas('store', function ($query) {
                $query->where('store_status', '2')->where('stripe', '1');
            })
            ->inRandomOrder()
            ->distinct()
            ->limit($limit)
            ->get();

        return $query;
    }


    public function getProducts($orderBy = 'created_at', $orderType = 'asc', $limit = 10, $shuffle = false)
    {
        $query = Product::with(['variations', 'store', 'categories'])
            ->where('status', '1')
            ->whereHas('store', function ($query) {
                $query->where('store_status', '2')->where('stripe', '1');
            })
            ->orderBy($orderBy, $orderType)
            ->limit($limit);

        if ($shuffle) {
            $query = $query->get()->shuffle();
        } else {
            $query = $query->get();
        }

        return $query;
    }

    public function getTrendingProducts($orderType = 'asc', $limit = 10, $shuffle = false)
    {

        $categorySlug = ["home-kitchen", "sports-and-outdoors", "accessories-supplies", "computers-accessories", "mens-fashion"];
        $results = Product::select('products.*', 'categories.categoryTitle as categoryTitle', 'categories.category_slug', 'stores.store_name', 'stores.store_slug', 'stores.stripe', 'stores.store_status', 'stores.store_country', 'stores.store_city', 'stores.street_address') // Select product and related fields
            ->join('product_category', 'products.product_id', '=', 'product_category.product_id') // Assuming many-to-many relation with a pivot table product_category
            ->join('categories', 'product_category.category_id', '=', 'categories.id')
            ->join('stores', 'products.store_id', '=', 'stores.store_id') // Join with store table
            ->whereIn('categories.category_slug', $categorySlug)
            ->whereNotNull('products.title')
            ->where('products.status', '1')
            ->where('stores.store_status', '2')
            ->where('stores.stripe', '1')
            ->distinct()
            ->orderBy('products.created_at', $orderType)
            ->limit($limit)
            ->get();
        $results = $results->shuffle();
        return $results;
    }

    public function getProductsApi($orderBy = 'created_at', $orderType = 'asc', $limit = 10, $shuffle = false)
    {
        $baseQuery = Product::join('stores', 'products.store_id', '=', 'stores.store_id')
            // ->with(['variations']) // Eager load variations relationship
            // ->select('products.*', 'stores.store_name')
            ->select('products.product_id', 'products.title', 'products.slug', 'products.price', 'products.feature_image', 'products.on_sale', 'products.discount_percentage', 'products.sale_price', 'stores.store_name')
            ->where('products.status', '1')
            ->where('stores.store_status', '2')
            ->where('stores.stripe', '1');

        // Add distinct to eliminate duplicate records
        $baseQuery->distinct();

        $query = $baseQuery
            ->orderBy('products.' . $orderBy, $orderType)
            ->paginate($limit);

        if ($shuffle) {
            $items = $query->items();
            $shuffledItems = collect($items)->shuffle()->toArray();
            $query->setCollection(collect($shuffledItems));
        }

        return $query->toArray();
    }
    public function getRecentProductsApiPagination($orderBy = 'created_at', $orderType = 'asc', $limit = 10, $shuffle = false, $page = 1)
    {
        $baseQuery = Product::join('stores', 'products.store_id', '=', 'stores.store_id')
            // ->with(['variations']) // Eager load variations relationship
            // ->select('products.*', 'stores.store_name',)
            ->select(
                'products.product_id',
                'products.title',
                'products.slug',
                'products.price',
                'products.feature_image',
                'products.on_sale',
                'products.sale_price',
                'stores.store_name'
            )
            ->where('products.status', '1')
            ->where('stores.store_status', '2')
            ->where('stores.stripe', '1')
            ->where('products.product_type', '!=', 'virtual');

        // Calculate the offset based on the page and limit
        $offset = ($page - 1) * $limit;

        $query = $baseQuery
            ->orderBy('products.' . $orderBy, $orderType)
            ->offset($offset)
            ->limit($limit)
            ->distinct() // Add distinct() to the query
            ->get();

        if ($shuffle) {
            $shuffledItems = $query->shuffle()->toArray();
            return $shuffledItems;
        }

        return $query->toArray();
    }
    public function getProductsApiPagination($orderBy = 'created_at', $orderType = 'asc', $limit = 10, $shuffle = false, $page = 1)
    {
        // $baseQuery =  Product::with(['reviews' => function($query) {
        //     $query->select(
        //         'product_id',
        //         DB::raw('SUM(rating) as total_rating'),
        //         DB::raw('COUNT(review_id) as review_count')
        //     )
        //     ->groupBy('product_id');
        // }])
        // $baseQuery = Product::join('stores', 'products.store_id', '=', 'stores.store_id')
        // // ->with(['variations']) // Eager load variations relationship
        // // ->select('products.*', 'stores.store_name',)
        // ->select('products.product_id','products.title','products.slug','products.price','products.feature_image',
        // 'stores.store_name')
        // ->where('products.status', '1')
        // ->where('stores.store_status', '2')
        // ->where('stores.stripe', '1')
        // ->where('products.product_type', '!=', 'virtual');
        $baseQuery = Product::join('reviews', 'products.product_id', '=', 'reviews.product_id')
            ->select(
                'products.product_id',
                'products.title',
                'products.slug',
                'products.price',
                'products.feature_image',
                'products.on_sale',
                'products.sale_price',
                'products.discount_percentage',
                'stores.store_name',
                DB::raw('COUNT(reviews.review_id) as review_count'),
                DB::raw('SUM(reviews.rating) as total_rating')
            )
            ->join('stores', 'products.store_id', '=', 'stores.store_id')

            // ->where('reviews.rating', '>', 0)
            // ->with(['variations']) // Eager load variations relationship
            // ->select('products.*', 'stores.store_name',)
            ->where('products.status', '1')
            ->where('stores.store_status', '2')
            ->where('stores.stripe', '1')
            ->where('products.product_type', '!=', 'virtual')
            ->groupBy(
                'products.product_id',
                'products.title',
                'products.slug',
                'products.price',
                'products.feature_image',
                'stores.store_name'
            )
            ->orderBy('review_count', 'desc');
        // Calculate the offset based on the page and limit
        $offset = ($page - 1) * $limit;

        $query = $baseQuery
            // ->orderBy('products.' . $orderBy, $orderType)
            ->offset($offset)
            ->limit($limit)
            ->distinct() // Add distinct() to the query
            ->get();

        if ($shuffle) {
            $shuffledItems = $query->shuffle()->toArray();
            return $shuffledItems;
        }

        return $query->toArray();
    }



    public function getBestSellerProducts($limit = 10, $shuffle = false)
    {
        if ($shuffle) {

            $query = Product::with('store', 'categories', 'reviews')
                ->select('products.*', DB::raw('COUNT(reviews.review_id) as review_count'))
                ->join('reviews', 'products.product_id', '=', 'reviews.product_id')
                ->join('product_category', 'products.product_id', '=', 'product_category.product_id')
                ->join('categories', 'product_category.category_id', '=', 'categories.id')
                ->where('reviews.rating', '>', 0)
                ->where('products.status', '=', 1)
                ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude Uncategorized category
                ->whereHas('store', function ($query) {
                    $query->where('store_status', '2')->where('stripe', '1');
                })
                ->groupBy('products.product_id')
                ->orderBy('review_count', 'desc')
                ->limit($limit)
                ->get()->shuffle();
        } else {

            $query = Product::with('store', 'categories', 'reviews')
                ->select('products.*', DB::raw('COUNT(reviews.review_id) as review_count'))
                ->join('reviews', 'products.product_id', '=', 'reviews.product_id')
                ->join('product_category', 'products.product_id', '=', 'product_category.product_id')
                ->join('categories', 'product_category.category_id', '=', 'categories.id')
                ->where('reviews.rating', '>', 0)
                ->where('products.status', '=', 1)
                ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude Uncategorized category
                ->groupBy('products.product_id')
                ->orderBy('review_count', 'desc')
                ->limit($limit)
                ->get();
        }
        // print_r($query); die;

        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }
    public function getBestSellerProductsApi($limit = 10, $shuffle = false)
    {
        if ($shuffle) {

            $query = Product::join('reviews', 'products.product_id', '=', 'reviews.product_id')
                ->select(
                    'products.product_id',
                    'products.title',
                    'products.slug',
                    'products.price',
                    'products.feature_image',
                    'products.on_sale',
                    'products.sale_price',
                    'products.discount_percentage',
                    'stores.store_name',
                    DB::raw('COUNT(reviews.review_id) as review_count'),
                    DB::raw('SUM(reviews.rating) as total_rating')
                )
                ->join('stores', 'products.store_id', '=', 'stores.store_id')

                // ->where('reviews.rating', '>', 0)
                // ->with(['variations']) // Eager load variations relationship
                // ->select('products.*', 'stores.store_name',)
                ->where('products.status', '1')
                ->where('stores.store_status', '2')
                ->where('stores.stripe', '1')
                ->where('products.product_type', '!=', 'virtual')
                ->groupBy(
                    'products.product_id',
                    'products.title',
                    'products.slug',
                    'products.price',
                    'products.feature_image',
                    'stores.store_name'
                )
                ->orderBy('review_count', 'desc');

            // Get the paginated results
            $paginatedResults = $query->paginate($limit);
            $items = $paginatedResults->items();
            $shuffledItems = collect($items)->shuffle()->toArray();
            $paginatedResults->setCollection(collect($shuffledItems));
            $paginatedArray = $paginatedResults->toArray(); // Convert paginator object to array
            return $paginatedArray;
        } else {

            $query = Product::with('categories', 'reviews')
                ->select('products.*', 'stores.store_name', DB::raw('COUNT(reviews.review_id) as review_count'))
                ->join('stores', 'products.store_id', '=', 'stores.store_id')
                ->join('reviews', 'products.product_id', '=', 'reviews.product_id')
                ->join('product_category', 'products.product_id', '=', 'product_category.product_id')
                ->join('categories', 'product_category.category_id', '=', 'categories.id')
                ->where('reviews.rating', '>', 0)
                ->where('products.status', '=', 1)
                ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude Uncategorized category
                ->groupBy('products.product_id')
                ->orderBy('review_count', 'desc')
                ->limit($limit)
                ->get();
        }
        // print_r($query); die;

        if (!empty($query)) {
            return $query;
        } else {
            return false;
        }
    }

    public function getBestSellerProductsApiPagination($limit = 10, $shuffle = false, $page = 1)
    {
        $query = Product::with('categories', 'reviews')
            ->select('products.*', 'stores.store_name', DB::raw('COUNT(reviews.review_id) as review_count'))
            ->join('stores', 'products.store_id', '=', 'stores.store_id')
            ->join('reviews', 'products.product_id', '=', 'reviews.product_id')
            ->join('product_category', 'products.product_id', '=', 'product_category.product_id')
            ->join('categories', 'product_category.category_id', '=', 'categories.id')
            ->where('reviews.rating', '>', 0)
            ->where('products.status', '=', 1)
            ->where('products.product_type', '!=', 'virtual')
            ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude Uncategorized category
            ->whereHas('store', function ($query) {
                $query->where('store_status', '2')->where('stripe', '1');
            })
            ->groupBy('products.product_id')
            ->orderBy('review_count', 'desc');

        // If shuffle is enabled
        if ($shuffle) {
            $paginatedResults = $query->paginate($limit, ['*'], 'page', $page);
            $items = $paginatedResults->items();
            $shuffledItems = collect($items)->shuffle()->toArray();
            $paginatedResults->setCollection(collect($shuffledItems));
        } else {
            // Paginate the query results without shuffling
            $paginatedResults = $query->paginate($limit, ['*'], 'page', $page);
        }

        // Get the total count of records
        $totalCount = $paginatedResults->total();

        // Construct the response array
        $response = [
            'current_page' => $paginatedResults->currentPage(),
            'per_page' => $paginatedResults->perPage(),
            'total' => $totalCount,
            'data' => $paginatedResults->items(),
        ];

        return $response;
    }

    public function getExploreMoreProducts($limit = 12)
    {
        // dd($limit);
        $query = Product::with(['variations', 'store', 'categories'])
            ->where('status', '1')->where('current_stock', '!=', 0)
            ->whereHas('store', function ($query) {
                $query->where('store_status', '2')->where('stripe', '1');
            })
            // ->inRandomOrder()
            ->distinct()
            ->limit($limit)
            ->get();

        return $query->shuffle();
    }
}
