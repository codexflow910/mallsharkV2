<?php

namespace Packages\Product\Repositories\Eloquent;


use Exception;
use InvalidArgumentException;
use App\Models\ProductCategory;

use Illuminate\Support\Facades\DB;
use Packages\Core\Models\Category;
use Illuminate\Support\Facades\File;
use Packages\Product\Models\V2\Product;
use Intervention\Image\Facades\Image;
use Packages\Product\Models\Favourites;
use Packages\Product\Models\ProductFaq;
use Packages\Product\Models\Attachments;
use Packages\Product\Models\ProductTags;
use Packages\Discount\Models\DiscountApplicable;
use App\Models\{Discount,AttachmentSizes};
use Packages\Shipping\Models\ShippingOption;
use Illuminate\Support\ItemNotFoundException;
use Packages\Product\Models\ProductVariation;
use Packages\Product\Contracts\ProductHelpers;
use Packages\Discount\Contracts\DiscountHelpers;
use Packages\Product\Contracts\BaseApiResponse;
use App\Http\Controllers\ElasticSearchController;
use Packages\Product\Contracts\ProductRepositoryInterface;
use Packages\Product\Config\ProductConstants;

use function PHPUnit\Framework\isEmpty;

class ProductRepository implements ProductRepositoryInterface
{
    use BaseApiResponse;
    use ProductHelpers;
    use DiscountHelpers;
    protected $model;
    protected $fav;

    public function __construct(Product $model, Favourites $fav)
    {
        $this->model = $model;
        $this->fav = $fav;
    }

    public function storeProduct($data)
    {
        try {
            $storeId = $data['store_id'];
            $productPrice = $data['price'];
            
            $product = $this->model->fill($data);
            $product->save();
            return $product;
        } catch (Exception $e) {
            throw new Exception("Error saving product: " . $e->getMessage());
        }
    }

    public function updateProduct($productId, $data)
    {
        try {
            $discount = '';
            $product = $this->model->findOrFail($productId);
            $productIds =  ProductConstants::PRODUCT_IDS;
            if (in_array($productId, $productIds)) {
            $data['slug'] = $product->slug;
            }
            $previousFeatureImage = $product->feature_image;
            $originalTitle = $product->title;
            $storeId = $data['store_id'];
            $productPrice = $data['price'];
            if($data['product_type'] == 'simple'){
                $discount = Discount::where(['store_id' => $storeId, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();
            }
            if (isset($discount) && !empty($discount)) {

                $discountId = $discount?->id;

                $isProductApplicable = DiscountApplicable::where('discount_id', $discountId)
                    ->where('applicable_type', 'product')
                    ->where('applicable_id', $product->product_id)
                    ->where('excluded', false)
                    ->exists();

                if ($isProductApplicable && $productPrice !== null) {
                    $customDiscount = $this->getCustomDiscount($discountId, 'product', $product->product_id);

                    if (!empty($customDiscount) && !empty($customDiscount['type']) && !empty($customDiscount['value'])) {
                        $discountType = $customDiscount['type'];
                        $discountValue = $customDiscount['value'];
                    } else {
                        $discountType = $discount->discount_value_type;
                        $discountValue = $discount->discount_value;
                    }

                    if ($discountType === 'fixed') {
                        $discount_price = $productPrice - $discountValue;

                        if ($discount_price >= 0.5) {
                            $discount_percentage = $productPrice > 0
                                ? min(100, round(($discountValue / $productPrice) * 100))
                                : 0;

                            $data['sale_price'] = $discount_price;
                            $data['discount_percentage'] = $discount_percentage;
                            $data['on_sale'] = true;
                            $data['discount_id'] = $discountId;
                        } else {
                            $data['sale_price'] = null;
                            $data['on_sale'] = false;
                            $data['discount_id'] = null;
                        }
                    } elseif ($discountType === 'percent') {
                        $discount_price = $productPrice * ($discountValue / 100);
                        $sale_price = $productPrice - $discount_price;

                        if ($sale_price >= 0.5) {
                            $data['sale_price'] = $sale_price;
                            $data['discount_percentage'] = $discountValue;
                            $data['on_sale'] = true;
                            $data['discount_id'] = $discountId;
                        } else {
                            $data['sale_price'] = null;
                            $data['on_sale'] = false;
                            $data['discount_id'] = null;
                        }
                    }
                }
            }

            $product->fill($data);
            // If title is not change then keep the original slug
            // if ($originalTitle === $product->title) {
            //     $product->slug = $product->getOriginal('slug');
            // }

            $product->save();
            return $product;
        } catch (Exception $e) {
            throw new Exception("Error updating product: " . $e->getMessage());
        }
    }

    public function attachRelations($product, $relation, $items)
    {
        if (method_exists($product, $relation)) {
            $product->$relation()->sync($items);
        } else {
            throw new InvalidArgumentException("The relation {$relation} does not exist on the product model.");
        }
    }
    public function storeTags($product, $items)
    {
        if ($product) { {
                if (is_array($items) && !empty($items)) {
                    foreach ($items as $item) {

                        $tag = new ProductTags();
                        $tag->product_id = $product->product_id;
                        $tag->tag_title = $item;
                        $tag->save();
                    }
                } else {
                    throw new InvalidArgumentException("The items provided are not valid tags.");
                }
            }
        }
    }
    public function updateTags($product, $items)
    {
        if ($product) {
            // Delete existing tags
            ProductTags::where('product_id', $product->product_id)->delete();

            // Store new tags
            if (is_array($items) && !empty($items)) {
                foreach ($items as $item) {
                    $tag = new ProductTags();
                    $tag->product_id = $product->product_id;
                    $tag->tag_title = $item;
                    $tag->save();
                }
            } else {
                throw new InvalidArgumentException("The items provided are not valid tags.");
            }
        }
    }



    public function getAttachmentId($file, $type = 'digital', $remove = false, $crop = true)
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
                        // unlink($getRealPath);
                    }

                    return $attachmentId;
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
    public function storeVariations($product, $variations,$productImages,$imagesGallery) {}
    public function updateVariations($product, $variations, $productImage, $imagesGallery, $variationsDeleted, $variationsDiscounts) {}

    public function storeFaqs($product_id, $faqs)
    {
        $questions = $faqs['faqQuestions'];
        $answers = $faqs['faqAnswers'];
        foreach ($questions as $key => $question) {
            $faq = new ProductFaq();
            $faq->product_id = $product_id;
            $faq->question = $question;
            $faq->answer = $answers[$key];
            $faq->save();
        }
    }
    public function updateFaqs($product_id, $faqs)
    {
        // Delete existing FAQs
        ProductFaq::where('product_id', $product_id)->delete();

        // Store new FAQs
        $questions = $faqs['faqQuestions'];
        $answers = $faqs['faqAnswers'];
        foreach ($questions as $key => $question) {
            $faq = new ProductFaq();
            $faq->product_id = $product_id;
            $faq->question = $question;
            $faq->answer = $answers[$key];
            $faq->save();
        }
    }
    public function getProductById($productId)
    {
        try {
            $product = $this->model->findOrFail($productId);
            if ($product->gallery) {
                $galleryImages = unserialize($product->gallery);
                foreach ($galleryImages as $key => $image) {
                    $galleryImages[$key] = [
                        'id' => $image,
                        'url' => $this->getAttachmentUrlSmallById($image) ?? null
                    ];
                }
                $product->gallery = $galleryImages;
            }
            
            return $product;
        } catch (Exception $e) {
            dd($e->getMessage());
            throw new ItemNotFoundException('Product not found');
        }
    }

    public function getProduct($productId)
    {
        $product = $this->model->where('product_id',$productId)->first();
        return $product;
    }
    public function getDiscountApplicable($discountId, $variationId)
    {
        $variationDiscounts = DiscountApplicable::where('applicable_type', 'variation')
            ->where('applicable_id', $variationId)
            // ->where('discount_id', $discountId)
            ->select('id', 'discount_id', 'applicable_type', 'applicable_id', 'custom_discount_type', 'custom_discount_value')->get();

        return $variationDiscounts;
    }

    public function deleteAttachment($imageId)
    {
        $attachment = Attachments::find($imageId);
        if ($attachment) {
            $fileName = $attachment->fileName;
            $fileExtension = $attachment->fileExtension;
            $fileId = $attachment->attachment_id;
            $completeFileName = $fileName . '_' . $fileId . '.' . $fileExtension;
            $getDirectorywithSizes = $this->getAllAttachmentSizes();
            foreach ($getDirectorywithSizes as $key => $value) {
                $directoryName = $value['name'];
                $destinationPath = public_path() . '/uploads/' . $directoryName . '/' . $completeFileName;
                if (File::exists($destinationPath)) {
                    unlink($destinationPath);
                }
            }
            $attachment->delete();
            return true;
        }
        return false;
    }
    public function getOrderProductByUser($buyerId, $productId)
    {
        // Step 1: Get all order_ids from store_orders where buyer_id matches
        $orderIds = DB::table('store_orders')
            ->where('buyer_id', $buyerId)
            ->pluck('order_id');

        if ($orderIds->isEmpty()) {
            return false; // No orders found for this buyer
        }

        // Step 2: Check in order_items if any of those order_ids have the given product_id as item_id
        $exists = DB::table('order_items')
            ->whereIn('order_id', $orderIds)
            ->where('item_id', $productId)
            ->exists();

        return $exists; // true if found, false otherwise
    }


    public function getProductDetailBySlug($slug, $user_id)
    {
        try {
            $resultData = Product::with('shippingOption')
                ->where('slug', $slug)
                ->where('status', '1')
                ->first();
        
            if (!$resultData) {
                return false;
            }
        
            $shipping = $resultData->shippingOption;
        
            // Default to free shipping
            $arr = [
                'shipping_id' => $resultData->shipping_option_id,
                'shipping_price' => 0,
                'shipping_method' => 'free',
                'method' => 'free',
            ];
        
            if ($shipping && !$shipping->is_domesticfree) {
                $arr['shipping_method'] = 'Priority'; // or $shipping->shipping_service if you prefer dynamic value
                $arr['method'] = 'flat';
        
                if ($shipping->flat_shipping_rate > 0) {
                    $arr['shipping_price'] = (float) $shipping->flat_shipping_rate;
                } else {
                    $arr['method'] = 'calculated';
                }
            }
        
            $resultData->shippings = $arr;
        
            return $resultData->toArray();
        
        } catch (\Exception $e) {
            // Optional: log or handle error
            throw new ItemNotFoundException('Product not found');
        }
    }

    public function getProductDetailWithoutRealtionBySlug($slug, $user_id)
    {
        try {
            $resultData = Product::where('slug', '=', $slug)
                ->where('status', '1')
                ->first();

            if (isset($resultData)) {
                if (!empty($resultData)) {
                    return $resultData->toArray();
                } else {
                    return false;
                }
            }
        } catch (Exception $e) {
            throw new ItemNotFoundException('Product not found');
        }
    }


    public function getSellerProductDetailBySlug($slug, $user_id)
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


            $state = $this->getStatebyId($resultData['store']['store_state']);
            $query = ShippingOption::where('id', $resultData->shipping_option_id)->first();

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

    public function getAllProductVariation($product_id)
    {
        $getAllVariations = ProductVariation::where('product_id', $product_id)->select('*')->get()->toArray();
        return $getAllVariations;
    }

    public function getCategoriesOfProduct($productId)
    {
        $categoryIds = ProductCategory::where('product_id', $productId)
            ->pluck('category_id');

        if ($categoryIds->isEmpty()) {
            return false;
        }

        $categories = Category::whereIn('id', $categoryIds)
            ->get();

        return $this->objToArray($categories);
    }

    public function getRelatedProducts($productId, $storeid)
    {
        $query2 = DB::table('product_category')
            ->select('products.product_id', 'products.product_type', 'products.user_id', 'products.current_stock', 'products.store_id', 'categories.categoryTitle', 'parent_categories.categoryTitle as parentCategoryTitle', 'products.title', 'products.slug', 'products.price', 'products.feature_image', 'products.on_sale', 'products.sale_price', 'products.discount_percentage', 'products.shipping_option_id')
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

    public function getShippingDetail($productId)
    {
        $product = Product::with('shippingOption.processingTime') 
            ->find($productId);

        if ($product && $product->shippingOption) {
            return $product->shippingOption; 
        }

        return false; 
    }
    public function getLoveProducts()
    {
        $result = Product::where('status', '1')
            ->select('product_id', 'title', 'store_id','slug', 'shipping_option_id', 'sale_price', 'product_type',
             'price', 'on_sale', 'feature_image', 'discount_percentage', 'description', 'current_stock')
            ->take(10)
            ->get();

        return $result;
    }

    public function getProductFaqsById($productId)
    {
        return $faqs = ProductFaq::where('product_id', '=', $productId)
            ->get()
            ->toArray();
    }

    public function objToArray($arr)
    {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

    public function getFavoriteByUserAndProduct($userId, $productId)
    {
        return Favourites::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    public function deleteFavorite($favorite)
    {
        try {
            return $favorite->delete();
        } catch (\Exception $e) {
            throw new Exception("Error updating product: " . $e->getMessage());
        }
    }

    public function saveFavorite($data)
    {
        try {

            $this->fav = new Favourites();
            $favorite = $this->fav->fill($data);

            $favorite->save();
            return $favorite;
        } catch (\Exception $e) {
            throw new \Exception("Error saving favorite: " . $e->getMessage());
        }
    }
    
    public function getFavoritesByUser($userId, $filters)
    {

        $query = Favourites::join('products', 'favourites.product_id', '=', 'products.product_id')
            ->where('favourites.user_id', $userId)
            ->select(
                'favourites.favourite_id',
                'favourites.product_id',
                'favourites.created_at',
                'favourites.user_id',
                'products.title',
                'products.slug',
                'products.price',
                'products.current_stock',
                'products.status',
                'products.feature_Image',
                'products.created_at',
                'products.on_sale',
                'products.sale_price',
                'products.discount_percentage'
            );

        if (!empty($filters['price'])) {
            $query->orderBy('products.price', $filters['price']);
        } elseif (!empty($filters['order'])) {
            $query->orderBy('favourites.favourite_id', $filters['order']);
        } else {
            $query->orderBy('favourites.favourite_id', 'DESC');
        }

        // return $query->get()->toArray();
        $perPage = $filters['per_page'] ?? 10;

        return $query->paginate($perPage);
    }
    public function getProducts($store)
    {
        try{
        $all_products = Product::with('variations', 'categories')
            ->select('product_id', 'slug', 'title', 'store_id', 'price', 'current_stock', 'feature_image', 'product_type', 'status','on_sale','discount_id')
            ->with(['orderItems' => function ($orderItems) {
                $orderItems->select('order_item_id', 'order_id', 'store_id', 'item_id');
                $orderItems->with('storeProductOrder', function ($storeProductOrder) {
                    $storeProductOrder->select('order_id', 'order_status', 'total_items')->where('order_status', 'pending');
                });
            }])
            ->where('store_id', $store->store_id)
            ->where('status', '!=', '2')
            ->where('status', '!=', '5')
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')->get();

            return $all_products;
        }
            catch (\Exception $e) {
            throw new \Exception("Error saving favorite: " . $e->getMessage());
        }
    }

    public function uploadProductImage($destinationPath, $productsimages)
    {
        try {

            $productGallery = array();

            foreach ($productsimages as $image) {
                $OriginalfileName = $image->getClientOriginalName();
                $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                $fileExtension = $image->getClientOriginalExtension();

                $status = true;
                $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                $image->move($destinationPath, $fileName);
                $productGallery['fileName'][] = $fileName;
                $productGallery['validate'][] = $status;
            }
            return $productGallery;
            
        } catch (\Exception $e) {
            throw new \Exception("Error saving Image: " . $e->getMessage());
        }
    }
    public function deleteProductImageRequest($data)
    {
        try {
            $imageid = $data['imageName'];
            $productId = $data['edit_product_id'];
            $directories = [
                'temp',
                'small',
                'media',
                'medium',
                'product',
                'avatar',
            ];
            $attachment = Attachments::find($imageid);

            if (!$attachment) {
                throw new ItemNotFoundException('Image not found');
            }
            $fileName = $attachment->fileName;
            $fileExtension = $attachment->fileExtension;
            $completeFileName = $fileName . '_' . $imageid . '.' . $fileExtension;
            $deleted = false;

            foreach ($directories as $dir) {
                $destinationPath = public_path("uploads/{$dir}/{$completeFileName}");

                if (File::exists($destinationPath)) {
                    unlink($destinationPath);
                    $deleted = true;
                }
            }
            // $destinationPath = public_path() . '/uploads/media/' . $completeFileName;
            if ($deleted) {
                // unlink($destinationPath);
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
                                Product::where('product_id', $productId)->update(['feature_image' => $value]);
                                $firstIteration = false;
                            }
                        }
                    }
                    return true;
                } else {
                    throw new ItemNotFoundException('Image not found');
                }
            } else {

                throw new ItemNotFoundException('Image not found');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function deleteProductTemRequest($file)
    {
        try {

            $fileName = $file;
            $directories = [
                'temp',
                'small',
                'media',
                'medium',
                'product',
                'avatar',
            ];

            $deleted = false;

            foreach ($directories as $dir) {
                $destinationPath = public_path("uploads/{$dir}/{$fileName}");

                if (File::exists($destinationPath)) {
                    unlink($destinationPath);
                    $deleted = true;
                }
            }

            if (!$deleted) {
                throw new ItemNotFoundException('Image not found in any directory.');
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    

    public function updateProductShipping($data) {
        try {
            $product = Product::find($data['product_id']);

            if ($product) {
                $product->shipping_option_id = $data['shipping_option_id'];
                $product->save();
                return $data;
            } else {
                throw new ItemNotFoundException('Product not found');
            }
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException('Product not found');
        } catch (Exception $e) {
            throw throw new Exception($e->getMessage());
        }
    }

    public function getProductsSlug() {
        try {
            $products = Product::where('status', 1)
                ->select('slug', 'updated_at')
                ->get();

            if ($products->isEmpty()) {
                throw new ItemNotFoundException('Products not found');
            }

            return $products;
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());
        } catch (Exception $e) {
            throw throw new Exception($e->getMessage());
        }
    }

   

    public function productMetaTags($slug){
        $data = Product::where('slug',$slug)->select('title','short_description','feature_image')->first();
        return $data;
    }

    public function getProductsTitle()
    {
        try {
            $activeStatus = ProductConstants::STATUS_ACTIVE;
            $products = Product::where('status', $activeStatus)
                ->select('product_id', 'title')
                ->get();
            
            if ($products->isEmpty()) {
                throw new ItemNotFoundException('Products not found');
            }
            foreach ($products as $product) {
                $productCategory = ProductCategory::where('product_id', $product->product_id)
                    ->select('category_id')
                    ->first();
                if ($productCategory) {
                    $category = Category::where('id', $productCategory->category_id)
                        ->select('categoryTitle')
                        ->first();
                    
                    $product->category = $category ? $category->categoryTitle : null;
                } else {
                    $product->category = null;
                }
            }
            return $products;        
        } catch (ItemNotFoundException $e) {
            throw new ItemNotFoundException($e->getMessage());    
        } catch (Exception $e) {
            throw new Exception($e->getMessage());   
        }
    }
}
