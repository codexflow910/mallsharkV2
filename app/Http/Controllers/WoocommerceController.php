<?php

namespace App\Http\Controllers;

use App\Models\importPlatform;
use App\Models\{Store,Discount};
use App\Repositories\Product\Productaccess;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

use GuzzleHttp\Client as GC;
use Exception;
use Google\Service\VersionHistory\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use File;
use Image;
use App\Models\CategoryProduct;
use App\Models\category;
use App\Models\Product;
use App\Models\ProductTags;
use Illuminate\Support\Facades\DB;
use stdClass;

class WoocommerceController extends Controller
{
    // private $url = 'https://bukram.com/';
    // private $consumer_key = 'ck_cd64a7647f4fbb739586a6f46be8169d9b478be8';
    // private $consumer_secret = 'cs_f756e8f7331dc5cf583412d8235839da9dad8893';
    private $url;
    private $consumer_key;
    private $consumer_secret;
    private $_esearch;
    private $productObj;
    private $req;

    public function __construct(Request $request)
    {
        $this->req = $request;
        $this->productObj = new Productaccess();
        $this->_esearch = new ElasticSearchController();
    }
    public function connect()
    {


        $req = $this->req;

        if ($req->has(['shopUrl', 'consumerKey', 'consumerSecret']) && $req->filled(['shopUrl', 'consumerKey', 'consumerSecret'])) {

            $this->url = $req->shopUrl;
            $this->consumer_key = $req->consumerKey;
            $this->consumer_secret = $req->consumerSecret;

            try {

                $wc = new Client(
                    $this->url,
                    $this->consumer_key,
                    $this->consumer_secret,
                    [
                        'wp_api' => true,
                        'version' => 'wc/v3',
                        'query_string_auth' => true //Force Basic Authentication as query string true and using under HTTPS
                    ]
                );
                $response = $wc->get('products');

                if ($response) {
                    $store = new Store();
                    if (Session::has('userData')) {

                        $userID = Session::get('userData')['user_id'];
                        $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
                        $store_id = $storeID->store_id;

                        $platform = new importPlatform();
                        $platform->platform_type = 'woocommerce';
                        $platform->store_id = $store_id;
                        $platform->shop_url = $this->url;
                        $platform->consumer_key = $this->consumer_key;
                        $platform->secret_token = $this->consumer_secret;
                        $platform->save();

                        return response()->json(['message' => 'Connected', 'status' => true], 200);
                    }
                } else {
                    return response()->json(['message' => 'Connection Failed', 'status' => false], 201);
                }
            } catch (Exception $e) {
                return response()->json(['message' => 'Connection Failed', 'status' => false], 201);
            }
        }
    }
    public function index()
    {


        $shopName = '';
        $connected = false;
        $foundError = false;
        $stripe = 0;
        if (Session::has('userData')) {

            $store = new Store();
            $userID = Session::get('userData')['user_id'];
            $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
            try {

                if ($storeID) {

                    $store_id = $storeID->store_id;

                    $platform = importPlatform::where('store_id', $store_id)->where('platform_type', 'woocommerce')->first();
                    if ($platform) {
                        $this->url = $platform->shop_url;
                        $this->consumer_key = $platform->consumer_key;
                        $this->consumer_secret = $platform->secret_token;
                        $wc = new Client(
                            $this->url,
                            $this->consumer_key,
                            $this->consumer_secret,
                            [
                                'wp_api' => true,
                                'version' => 'wc/v3',
                                'query_string_auth' => true //Force Basic Authentication as query string true and using under HTTPS
                            ]
                        );
                        $params = ['per_page' => 1];
                        $response = $wc->get('products', $params);
                        if ($response) {
                            $connected = true;
                            $foundError = false;
                        } else {
                            $connected = false;
                            $foundError = true;
                        }
                    }



                    $storeData =  getStoreDataById($store_id);


                    if (count($storeData) > 0) {

                        $stripe = $storeData[0]->stripe;
                    }
                }
            } catch (Exception $e) {
                $connected = false;
                $foundError = true;
            }
        }
        // $stripe = 0;
        // $stripe = !$connected ? 1 : $stripe;

        $shopName = 'Test';
        return view('home.woocommerce', compact('stripe', 'shopName', 'connected', 'foundError'));
    }
    public function disconnectWoo()
    {

        if (Session::has('userData')) {

            $store = new Store();
            $userID = Session::get('userData')['user_id'];
            $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
            try {

                if ($storeID) {

                    $store_id = $storeID->store_id;

                    $platform = importPlatform::where('store_id', $store_id)->where('platform_type', 'woocommerce')->first();
                    if ($platform) {
                        $platform->delete();
                    }

                    return response()->json(['message' => 'Diconnected', 'status' => true], 200);
                }
            } catch (Exception $e) {
            }
        }
    }

    public function importWoocommerce()
    {
        // ini_set('max_execution_time', 180);
        set_time_limit(1000);
        $shopifyCategories = [];
        $NA = false;
        if (Session::has('userData')) {

            $store = new Store();
            $userID = Session::get('userData')['user_id'];
            $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
            try {

                if ($storeID) {

                    $store_id = $storeID->store_id;

                    $platform = importPlatform::where('store_id', $store_id)->where('platform_type', 'woocommerce')->first();
                    if ($platform) {
                        $this->url = $platform->shop_url;
                        $this->consumer_key = $platform->consumer_key;
                        $this->consumer_secret = $platform->secret_token;
                        $per_page = 10;
                        $response = [];
                        $wc = new Client(
                            $this->url,
                            $this->consumer_key,
                            $this->consumer_secret,
                            [
                                'wp_api' => true,
                                'version' => 'wc/v3',
                                'query_string_auth' => true, //Force Basic Authentication as query string true and using under HTTPS
                            ]
                        );
                        $page = 1;
                        while (true) {
                            $params = ['per_page' => $per_page, 'page' => $page];
                            $products = $wc->get('products', $params);


                            if (empty($products)) {
                                break;
                            }


                            $response = array_merge($response, $products);

                            $page++;
                            // usleep(50); //avoid rate limitting
                        }


                        $itemArray = json_decode(json_encode($response), true);

                        $result = array_map(function ($item) use ($store_id) {

                            $skuExists = $this->checkSkuExists($store_id, $item['slug']);
                            if (empty($item['price']) || $item['status'] != 'publish' || $skuExists ||  $item['type'] == 'virtual') {
                                return null;
                            }

                            return [
                                "id" => $item['id'],
                                "title" => $item['name'],
                                "handle" => $item['slug'],
                                "category" => $item['categories'],
                                "type" => $item['type'],
                                "images" => $item['images'],
                                "sku" => $item['sku'],
                                "price" => $item['price'],
                                "manage_stock" => $item['manage_stock'],
                                "stock_status" => $item['stock_status'],
                                "stock_quantity" => $item['stock_quantity'],
                                "variations" => $item['variations'],

                            ];
                        }, $itemArray);
                        $result = array_filter($result);
                        $result = array_values($result);
                        foreach ($result as &$pr) {
                            if ($pr['type'] == 'variable') {

                                $url = 'products/' . $pr['id'] . '/variations';
                                $var = $wc->get($url);
                                $var = json_decode(json_encode($var), true);
                                $var = array_map(function ($item) {
                                    // dump($item['price']);
                                    if (empty($item['price'])) {
                                        return null;
                                    }

                                    return [
                                        "id" => $item['id'],
                                        "price" => $item['price'],
                                        "manage_stock" => $item['manage_stock'],
                                        "stock_status" => $item['stock_status'],
                                        "stock_quantity" => $item['stock_quantity'],
                                    ];
                                }, $var);

                                $minmax =  $this->findMinMaxValues($var);
                                $pr['min_price'] = $minmax['min'];
                                $pr['max_price'] = $minmax['max'];

                                $pr['variations_data'] = $var;
                            } else if ($pr['type'] == 'simple') {
                                $pr['variations_data'] = [];

                                $pr['min_price'] = 0;
                                $pr['max_price'] = 0;
                            }
                        }
                        // working on stock status to show on fetched products
                        // dd($result);
                        $products = json_decode(json_encode($result));
                        // dd($products);
                        $categories = $this->productObj->getParentWithCildCategories();
                        $shippingProfiles = $this->productObj->getShippingProfile()['shipping_profile'];
                        // dump($product);
                        foreach ($products as $product) {
                            // dump($product);
                            if (isset($product->category[0])) {
                                $shopifyCategories[$product->category[0]->id] = $product->category[0]->name;
                            } else if (!$NA) {
                                $NA = true;
                            }
                        }
                        //   dd($shippingProfiles);
                        $view =  view('home.wooProducts', compact('products', 'shopifyCategories', 'categories', 'shippingProfiles', 'NA'))->render();
                        return response()->json(['view' => $view, 'status' => true], 200);
                    }
                }
            } catch (Exception $e) {
                dd($e);
                return response()->json(['view' => '', 'status' => false], 201);
            }
        }
    }
    public function findMinMaxValues($arr)
    {
        // Initialize variables to store the minimum and maximum values
        $minValue = INF;
        $maxValue = -INF;
        if (count($arr) > 0) {

            // Iterate through the array to find min and max values
            foreach ($arr as $element) {
                if ($element['price'] < $minValue) {
                    $minValue = $element['price'];
                }
                if ($element['price'] > $maxValue) {
                    $maxValue = $element['price'];
                }
            }

            // Return the minimum and maximum values as an associative array
            return [
                'min' => $minValue,
                'max' => $maxValue,
            ];
        } else {
            return [
                'min' => 0,
                'max' => 0,
            ];
        }
    }
    public function  saveWooProducts()
    {

        $req = $this->req;
        // dd($req->all());

        // ini_set('max_execution_time', 180);
        set_time_limit(1000);
        if (Session::has('userData')) {

            $store = new Store();
            $userID = Session::get('userData')['user_id'];
            $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
            try {

                if ($storeID) {

                    $store_id = $storeID->store_id;

                    $platform = importPlatform::where('store_id', $store_id)->where('platform_type', 'woocommerce')->first();
                    if ($platform) {
                        $this->url = $platform->shop_url;
                        $this->consumer_key = $platform->consumer_key;
                        $this->consumer_secret = $platform->secret_token;
                        $client = new Client(
                            $this->url,
                            $this->consumer_key,
                            $this->consumer_secret,
                            [
                                'wp_api' => true,
                                'version' => 'wc/v3',
                                'query_string_auth' => true //Force Basic Authentication as query string true and using under HTTPS
                            ]
                        );
                        if (isset($req->productsData)) {
                            $data = json_decode(json_encode($req->productsData));
                            if (count($data) > 0) {
                                
                                foreach ($data as $item) {
                                    if ($item->categoryId) {
                                        $this->singleProduct($client, $item);
                                    }
                                }
                                // DB::commit();
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // DB::rollBack();
                // dd($e);
                return response()->json(['view' => '', 'status' => false], 201);
            }
        }
    }
    public function singleProduct($client, $data)
    {
        $url = 'products/' . $data->productId;
        $params = ['per_page' => 30];
        $response = $client->get($url);

        $item = json_decode(json_encode($response), true);
        if (!empty($item['price']) && $item['status'] === 'publish') {
            $result = [
                "id" => $item['id'],
                "title" => $item['name'],
                "handle" => $item['slug'],
                "status" => $item['status'],
                "category" => $item['categories'],
                "type" => $item['type'],
                "sku" => $item['sku'],
                "tags" => $item['tags'],
                "price" => $item['price'],
                "manage_stock" => $item['manage_stock'],
                "stock_status" => $item['stock_status'],
                "stock_quantity" => $item['stock_quantity'],
                "short_description" => $item['short_description'],
                "dimensions" => $item['dimensions'],
                "weight" => $item['weight'],
                "images" => $item['images'],
                "variations" => $item['variations'],
                "regular_price"=>$item['regular_price'],
                "on_sale"=>$item['on_sale']
            ];
        } else {
            $result = null;
            return false;
        }
        if ($result['type'] == 'variable') {

            $url = 'products/' . $result['id'] . '/variations';
            $var = $client->get($url);
            $var = json_decode(json_encode($var), true);
            $var = array_map( function ($item) {

                if (!empty($item['price'])) {

                    return [
                        "id" => $item['id'],
                        "price" => $item['price'],
                        "sku" => $item['sku'],
                        "status" => $item['status'],
                        "manage_stock" => $item['manage_stock'],
                        "stock_status" => $item['stock_status'],
                        "stock_quantity" => $item['stock_quantity'],
                        "image" => $item['image'],
                        "attributes" => $item['attributes'],
                        "regular_price"=>$item['regular_price'],
                        "on_sale"=>$item['on_sale']
                    ];
                }
            }, $var);
            // dd($var);
            $minmax =  $this->findMinMaxValues($var);
            $result['min_price'] = $minmax['min'];
            $result['max_price'] = $minmax['max'];

            $result['variations_data'] = $var;
        } else if ($result['type'] == 'simple') {
            $result['variations_data'] = [];

            $result['min_price'] = 0;
            $result['max_price'] = 0;
        }

        // working on stock status to show on fetched products
        // dd($result);
        $product = json_decode(json_encode($result));
        $this->storeProduct($data, $product);
        // dd($products);


    }
    public function storeProduct($data, $product)
    {
        $store = new Store();
        $userID = Session::get('userData')['user_id'];
        $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
        // simple products
        // if($product->totalVariants > 0 && $product->totalVariants < 2){
        if (!empty($product->price) && $product->status == 'publish') {
            $variant1 = new stdClass();
            $productTitle = $product->title;
            $productPrice = $product->on_sale ? $product->regular_price : ($product->price ?? 0);
            $productStatus = $product->status == 'publish' ? 1 : 0;
            // dd($productStatus);
            $productSKU = isset($product->sku) ? $product->sku : null;
            $productDescription = $product->short_description;
            $productWeightUnit = "POUNDS";

            $tempWeight = isset($product->weight) ? ($product->weight) : 0;
            if ($tempWeight < 1) {
                $tempWeight = 1;
            }
            $productWeight = $this->convertToPounds($tempWeight, $productWeightUnit);
            // dd($product->dimensions);
            $productSizeLength = $product->dimensions->length;
            $productSizeHeight = $product->dimensions->height;
            $productSizeWidth = $product->dimensions->width;
            // $productSizeLength = 6;
            // $productSizeWidth = 3;
            // $productSizeHeight = 5;
            // $productWeight = 7;
            $productCurrentStock = $this->countStock($product);

            $weightType = 'pound';
            $dimentionType = 'inch';
            $productType = $product->type == 'variable' ? 'variant' : ($product->type == 'simple' ? 'simple' : 'virtual');
            $shippingOptionId = $data->profileId;



            // $images = [];
            $storeId = '';
            // $status = '3'; //pending
            if (isset($storeID->store_id) && $storeID->store_id != '') {
                $storeId = $storeID->store_id;
                $storeStatus = getStoreInfoById($storeId);
                $store_super_status = $storeStatus[0]->super_status;
                $storeStatus = $storeStatus[0]->store_status;
            } else {
                $storeId = 1;
            }
            if($store_super_status != 1 || $storeStatus != 2){
                $status = '0'; //active
              }else{
                $status = '1';
              }
            $handle = strtolower(trim($product->handle));
            $handle = preg_replace('/[^a-z0-9-]/', '-', $handle);
            $handle = preg_replace('/-+/', "-", $handle);
            $slug = rtrim($handle, '-');
            $productSlug = $slug . '-' . $storeId;

            if (!$userID) {
                $userID = 1;
            }
            $productImages = $product->images;
            $productFeatureImage = '';
            if (count($productImages) > 0) {
                $tempDestinationPath =  public_path() . '/uploads/temp/';
                $destinationPath =  public_path() . '/uploads/';
                $getDirectorywithSizes = $this->productObj->getAllAttachmentSizes();







                // dd($getDirectorywithSizes);
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }
                $productGallery = array();
                foreach($productImages as $key1 => $image){
                    $src = $image->src;
                    // $filename = basename($src);
                    $OriginalfileName = pathinfo($src, PATHINFO_FILENAME);
                    $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                    $extension = explode('?', pathinfo($src, PATHINFO_EXTENSION));
    
                    if (isset($extension[0])) {
                        $fileExtension = $extension[0];
                        $fileName = $this->productObj->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                        $client = new GC();
                        $response = $client->get($src);
                        // dump($fileName);
                        try {
    
                            $tempPath = $tempDestinationPath . $fileName;
                            file_put_contents($tempPath, $response->getBody());
    
    
                            foreach ($getDirectorywithSizes as $key => $value) {
                                $directoryName = $value['name'];
                                $path = $destinationPath . $directoryName . '/' . $fileName;
                                // dump($path);
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
                            if($key1 == 0){
                                $productFeatureImage = $this->productObj->getAttachmentId($fileName, 'image', false);
                            }
                            $productGallery[] = $this->productObj->getAttachmentId($fileName, 'image', false);
                        } catch (Exception $e) {
                            dd($e->getMessage());
                        }
                    }
    
                }
                
                // foreach ($productImages as $image) {
                                // dd('$stored');

                // }
            } else {
                $status = '3';
            }


            
            $newProduct = new Product();
            $newProduct->user_id = $userID;
            $newProduct->store_id = $storeId;
            $newProduct->title = $productTitle;
            $newProduct->slug = $productSlug;
            // $newProduct->short_description = $productShortDescription;
            $newProduct->description = $productDescription;
            $newProduct->price = $productPrice;
            $newProduct->current_stock = $productCurrentStock;
            $newProduct->sku = $productSKU;
            $newProduct->status = $status;
            $newProduct->weight = $productWeight;
            $newProduct->productSize_length = $productSizeLength;
            $newProduct->productSize_width = $productSizeWidth;
            $newProduct->productSize_height = $productSizeHeight;
            $newProduct->shipping_option_id = $shippingOptionId;
            $newProduct->weight_type = $weightType;
            $newProduct->dimention_type = $dimentionType;

            $newProduct->product_type = $productType;
            $newProduct->product_status = $productStatus;
            $newProduct->maxquantitylimit = 10;

           
            



            if ($productFeatureImage) {
                $newProduct->feature_Image = $productFeatureImage;
            }
            if (!empty($productGallery)) {
                $newProduct->gallery = serialize($productGallery);
            }
            $discount = Discount::where(['store_id' => $storeId , 'type' => 'sale', 'status'=>'active','state' => 'live'])->first();

            if (isset($discount) && !empty($discount)) {
                if ($discount->discount_value_type == 'percent') {
                  $discount_value = $newProduct->price * ($discount->discount_value / 100);
                  $sale_price = $newProduct->price - $discount_value;
                  if ($sale_price > 0) {
          
                    $newProduct->on_sale = true;
                    $newProduct->sale_price = $sale_price;
                    $newProduct->discount_id = $discount->id;
                    $newProduct->discount_percentage = $discount->discount_value;
                  } else {
                    $newProduct->sale_price = $newProduct->price;
                    $newProduct->on_sale = false;
                  }
                } elseif ($discount->discount_value_type == 'fixed') {
                  $discount_value = $productPrice - $discount->discount_value;
                  if ($discount_value > 0) {
                    // Prevent division by zero and calculate discount percentage
                    if ($productPrice > 0) {
                      if ($productPrice > $discount->discount_value) {
                        $discount_percentage = round(floatval(($discount->discount_value / $newProduct->price) * 100));
                      } else {
                        $discount_percentage = 100;
                      }
                    } else {
                      $discount_percentage = 0; // Handle case for zero price
                    }
                    $newProduct->on_sale = true;
                    $newProduct->sale_price = $discount_value;
                    $newProduct->discount_percentage = $discount_percentage; // Optional: store percentage
                    $newProduct->discount_id = $discount->id;
                  } else {
                    $newProduct->sale_price = $newProduct->price;
                    $newProduct->on_sale = false;
                  }
                }
          
              }
            $newProduct->save();
            // dd($newProduct);
            // dd($newProduct);
            if ($newProduct) {
                $productId = $newProduct->product_id;
                if (isset($data->categoryId)) {

                    $category = category::find($data->categoryId);
                    $productCategory = new CategoryProduct();
                    $productCategory->product_id = $productId;
                    $productCategory->category_id = $category->id;
                    $productCategory->save();

                    if ($category->parentCategory()->exists()) {
                        $productParentCategory = new CategoryProduct();
                        $productParentCategory->product_id = $productId;
                        $productParentCategory->category_id = $category->parentCategory->id;
                        $productParentCategory->save();
                    }
                }
                if (isset($product->tags) && count($product->tags) > 0) {
                    $tagCounter = 1;

                    foreach ($product->tags as $tag) {
                        if ($tagCounter < 11) {

                            $productTags = new ProductTags();
                            $productTags->product_id = $productId;
                            $productTags->tag_title = $tag->name;
                            $productTags->save();
                        }
                        $tagCounter++;
                    }
                }
                // add product variation
                // dd($product->variations_data);
                if (count($product->variations_data) > 0) {
                    // dd($product->variations_data->attributes);
                    $vars = [];
                    $options = [];
                    $productInfo = array();
                    $variationGallery = array();
                    $counter2 = 1;
                    foreach ($product->variations_data as $key => $variation) {
                        if (count($variation->attributes) > 0) {

                            // add attributes
                            $counter = 1;
                            // dump($variation->attributes);
                            if ($key < 1) {

                                foreach ($variation->attributes as $item) {
                                    // dd($item);
                                    $attributeKey = 'attribute' . $counter;
                                    $vars[$attributeKey][] = $item->name;

                                    $optionKey = 'option' . $counter;
                                    $options[] = $optionKey;


                                    $counter++;
                                }
                            }

                            //  dump($options);
                            
                            $variationImages = array();

                            // foreach ($variation as $variant) {
                            // dump('variant',$variant->node->image);
                            // dd($variation);
                            // dd($node);
                            if (!empty($variation->price) && $variation->status == 'publish') {

                                // dump($variation->image ? $variation->image->transformedSrc : null);
                                
                                $optionKey = 'option' . $counter2;
                                $priceKey = 'price';
                                $skuKey = 'sku';
                                $currentStockKey = 'current_stock';
                                $visibilityKey = 'visibility';
                                $variationionImageKey = 'product_variation_image';
                                $vars[$priceKey][] = $variation->on_sale ? $variation->regular_price : $variation->price;
                                $vars[$skuKey][] = $variation->sku;

                                $vars[$currentStockKey][] = $this->countVariationStock($variation);

                                $vars[$visibilityKey][] = $counter2;
                                $vars[$variationionImageKey][] =  null;
                                // dd($variation);
                                foreach ($options as $key => $opt) {
                                    $vars[$opt][] = $variation->attributes[$key]->option;
                                }
                                // dd($vars);
                                // $vars[$variationionImageKey][] = $variation->image ? $variation->image->transformedSrc : null; //uncomment to add images
                                // $productInfo['$product_variation_image'][] = $variation->image ? $variation->image->transformedSrc : null; //uncomment to add images
                                $src = ($variation->image && !empty($variation->image)) ? $variation->image->src : null;
                                
                                $value = null;
                                if ($src) {


                                    // $filename = basename($src);
                                    $OriginalfileName = pathinfo($src, PATHINFO_FILENAME);
                                    $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                                    $extension = explode('?', pathinfo($src, PATHINFO_EXTENSION));

                                    if (isset($extension[0])) {
                                        $fileExtension = $extension[0];
                                        $fileName = $this->productObj->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                                        $client = new GC();
                                        $response = $client->get($src);
                                        // dump($fileName);
                                        try {

                                            $tempPath = $tempDestinationPath . $fileName;
                                            file_put_contents($tempPath, $response->getBody());


                                            foreach ($getDirectorywithSizes as $key => $value) {
                                                $directoryName = $value['name'];
                                                $path = $destinationPath . $directoryName . '/' . $fileName;
                                                // dump($path);
                                                $width = $value['width'];
                                                $height = $value['height'];

                                                if ($width == 0 && $height == 0) {
                                                    $readImage = file_get_contents($tempPath);
                                                    file_put_contents($path, $readImage);
                                                } else {

                                                    $img = Image::make($tempPath);
                                                    $img->resize($width, $height, function ($constraint) {
                                                        $constraint->aspectRatio();
                                                    })->save($path);
                                                }
                                            }
                                            

                                            

                                            // $variationGallery[$key] = 
                                            // $imageName = pathinfo($src, PATHINFO_FILENAME);
                                            // foreach ($variationGallery as $key => $val) {
                                            //     if ($key == $imageName) {
                                            //         $value = $val;
                                            //         break;
                                            //     }
                                            // }
                                            $productInfo['$product_variation_image'][] = $this->productObj->getAttachmentId($fileName, 'image', false);
                                            // dd($productInfo);
                                            // $images[$OriginalfileName] = $fileName;
                                            // unlink($tempPath);



                                            //   if (filesize($path) > 0) {
                                            //     // The file was saved successfully
                                            //     echo 'Image saved.' .  '  '. $path;
                                            // } else {
                                            //     // There was an error saving the file
                                            //     echo 'Error saving image.';
                                            // }
                                            // $stored = Storage::disk('public')->put($destinationPath . $fileName, '$response->getBody()');
                                        } catch (Exception $e) {
                                            dd($e->getMessage());
                                        }
                                    }
                                } else {

                                    $productInfo['$product_variation_image'][] = null;
                                }
                                // $vars[$optionKey] = $item->values;

                                $counter2++;
                            }
                            // }
                            // dd($productInfo);
                            $productInfo['productID'] = $productId;
                            $productInfo['store_id'] = $storeId;
                        }
                    }
                    // dd($productInfo);
                    // dd($variationImages);
                    if (count($vars['option1']) > 0) {
                        // dd($vars);
                        $this->productObj->addProductVariation($vars, $productInfo, 'add');
                    }
                    // dd('called');
                    // dd($vars);
                    // dump($product);
                    // dd($product->variants->edges);
                }
                $this->_esearch->esMApping($productId);
                // return 'save';
            }
            // dd($newProduct);
        } else {
        }
    }
    function convertToPounds($weight, $unit)
    {
        switch ($unit) {
            case 'GRAMS':
                return $weight * 0.00220462;
            case 'KILOGRAMS':
                return $weight * 2.20462;
            case 'OUNCES':
                return $weight * 0.0625;
            default:
                return $weight;
        }
    }
    function countStock($product)
    {
        // dd($product);
        if ($product->type == 'simple') {
            // product level MANAGE STOCK for simple false
            if (!$product->manage_stock) {
                if ($product->stock_status == 'instock') {
                    $stock = 1;
                } else {
                    $stock = 0; //out of stock
                }
            } else if ($product->manage_stock) {  //if MANAGE STOCK true
                // $stock 
                if ($product->stock_status == 'instock') {
                    $stock = $product->stock_quantity;
                } else {
                    $stock = 0;
                }
            }
            // dd($stock);
        } else if ($product->type == 'variable') {
            $stock = 0;

            foreach ($product->variations_data as $key => $variation) {
                //  count stock quantity of total variations, and check if there's any variation with instock
                if ($variation->stock_quantity && $variation->stock_quantity > 0) {

                    $stock += $variation->stock_quantity;
                    // dump($stock);
                }
                if ($variation->stock_status == 'instock') {
                    $inStock = true;
                }
            }
            // now check if quantity is less than 0 show instock else show quantity

            // dump($product->variations_data);
        }
        $stock = $stock > 0 ? $stock  : ($inStock ? 1 : 0);

        return $stock;
    }
    function countVariationStock($variation)
    {

        $stock = 0;


        //  count stock quantity of total variations, and check if there's any variation with instock
        if ($variation->stock_quantity && $variation->stock_quantity > 0) {

            $stock += $variation->stock_quantity;
            // dump($stock);
        }
        if ($variation->stock_status == 'instock') {
            $inStock = true;
        }

        // now check if quantity is less than 0 show instock else show quantity

        $stock = $stock > 0 ? $stock  : ($inStock ? 1 : 0);
        return $stock;
        // dump($product->variations_data);
    }
    public function checkSkuExists($storeID, $str)
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        $slug = rtrim($str, '-');
        $slug = $slug . '-' . $storeID;
        if (!empty($slug) && !empty($storeID)) {
            $product = Product::where('store_id', $storeID)->where('slug', $slug)->where('status', '<>', '2')->get()->count();

            if ($product > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function wooTerms(){
        return view('home/woo-terms');
    }
}
