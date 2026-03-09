<?php

namespace App\Http\Controllers;

use App\Models\CategoryProduct;
use App\Models\ProductVariation;
use App\Models\category;
use App\Models\Supplier;
use App\Models\{Discount, Product, Attachments};
use App\Models\ProductFaq;
use App\Models\ProductTags;
use App\Models\Store;
use App\Repositories\Product\Productaccess;
use Exception;
use Illuminate\Http\Request;
use PHPShopify\ShopifySDK;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;
use File;
use Image;
use DB;
use Illuminate\Support\Facades\Storage;
use SebastianBergmann\LinesOfCode\Counter;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Graphql;
use Shopify\Context;
use Shopify\Utils;

class ShopifyController extends Controller
{

  private $accessToken = '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';
  // private $accessToken = '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';
  private $productObj;
  private $req;

  private $_product;
  private $_esearch;
  public function __construct(Request $request)
  {
    $this->req = $request;
    $this->productObj = new Productaccess();
    $this->_product = new Product();
    $this->_esearch = new ElasticSearchController();
  }

  public function connectShopify()
  {


    $req = $this->req;
    if ($req->has(['shopUrl', 'accessToken']) && $req->filled(['shopUrl', 'accessToken'])) {

      $client = new Client([
        'base_uri' => $req->shopUrl,
        'headers' => [
          'Content-Type' => 'application/graphql',
          'X-Shopify-Access-Token' => $req->accessToken,
          'X-GraphQL-Cost-Include-Fields' => true
        ]
      ]);
    }
    $PQ = '{
      shop {
        id,
        name,
        currencyCode

       }

    }';
    try {

      $request = $client->request('POST', '/admin/api/2023-04/graphql.json', [
        'body' => $PQ,
      ]);
      // dd($request->getStatusCode())
      if ($request->getStatusCode() == 200) {
        if (Session::has('userData')) {
          $body = $request->getBody();
          $response = json_decode($body);

          $userData = Session::get('userData');
          $userData['accessToken'] = $req->accessToken;
          $userData['currencyCode'] = $response->data->shop->currencyCode;
          $userData['shopUrl'] = $req->shopUrl;
          Session::put('userData', $userData);
        }
        // dd($response->data->shop->currencyCode);
        return response()->json(['message' => 'Connected', 'status' => true], 200);
      } else {

        return response()->json(['message' => 'Connection Failed', 'status' => false], 201);
        // API connection failed
        // Handle the error as needed
      }
    } catch (Exception $e) {
      return response()->json(['message' => 'Connection Failed', 'status' => false], 201);
      // dd($e->getMessage());
    }
    $body = $request->getBody();
    $response = json_decode($body);
    dd($response);
    // dd($data);
  }
  public function disconnectShopify()
  {



    if (Session::has('userData')) {
      $userData = Session::get('userData');
      if (isset($userData['accessToken']) && isset($userData['shopUrl'])) {
        unset($userData['accessToken']);
        unset($userData['shopUrl']);
      }
      Session::put('userData', $userData);
    }
    return response()->json(['message' => 'Disconnected', 'status' => true], 200);

    // dd($data);
  }


  public function testResults($client)
  {
    ini_set('max_execution_time', 0);

    $cursor = null;
    $categories = [];
    $counter = 0;
    do {
      $args = [
        "first: 250"
      ];
      if (!empty($cursor)) {
        $args[] = 'after:"' . $cursor . '"';
        // dump($cursor);
      }
      $args = implode(', ', $args);
      // dump($args);
      $PQ = '{
        products(' . $args . '){
          edges {
            cursor
            node {
              productCategory {
                productTaxonomyNode {
                  id
                  name
                }
              }
            }
          }
          pageInfo {
            endCursor
            hasNextPage
            hasPreviousPage
            startCursor
          }
        }
            
          
          
        }';
      try {

        $request = $client->request('POST', '/admin/api/2023-04/graphql.json', [
          'body' => $PQ,
        ]);
        if ($request->getStatusCode() != 200) {
          return ['data' => [], 'status' => false];
        }
      } catch (Exception $e) {
        return ['data' => [], 'status' => false];
        dd($e->getMessage());

      }
      $body = $request->getBody();
      $response = json_decode($body);

      if (!isset($response->data)) {
        return ['data' => [], 'status' => false];
      } else
        if (!empty($response->data->products->edges)) {
          $edges = $response->data->products->edges;
          foreach ($edges as $edge) {

            $id = $edge->node->productCategory ? $edge->node->productCategory->productTaxonomyNode->id : null;
            $name = $edge->node->productCategory ? $edge->node->productCategory->productTaxonomyNode->name : null;
            $cursor = $edge->cursor;
            if ($id && $name && !array_key_exists($id, $categories)) {

              $categories[$id] = $name;
            }
          }
        }
      // sleep(2);
      $counter++;
    } while ($response->data->products->pageInfo->hasNextPage && $counter < 300);
    asort($categories);

    return ['data' => $categories, 'status' => true];
    return $categories;
  }
  public function results($client)
  {
    ini_set('max_execution_time', 0);
    $ids = [];
    $cursor = null;
    $products = [];

    $counter = 0;
    do {
      $args = [
        "first: 10"
      ];
      if (!empty($cursor)) {
        $args[] = 'after:"' . $cursor . '"';
        // dump($cursor);
      }
      $args = implode(', ', $args);
      // dump($args);
      $PQ = '{
          products(' . $args . ') {
            edges{
              cursor
              node {
                id
                title
               
                handle
                status
                
                totalInventory
                totalVariants
                
                
                
                
                priceRangeV2 {
                  maxVariantPrice {
                    amount
                    currencyCode
                  }
                  minVariantPrice {
                    amount
                    currencyCode
                  }
                }
                images(first: 1) {
               
                  nodes {
                  
                    url
                   
                  }
                
              }
              productCategory {
                productTaxonomyNode {
                  id
                  fullName
                  isRoot
                  name
                  isLeaf
                }
              }
          
              
              
              variants(first: 10) {
                edges {
                  node {
                    title
                    sku
                    price
                    weight
                    weightUnit
                    inventoryQuantity
                    requiresShipping
                    
                    
                  
                  }
                }
              }
              }
            }
            
            pageInfo {
              endCursor
              hasNextPage
              hasPreviousPage
              startCursor
            }
          }
        }';
      try {

        $request = $client->request('POST', '/admin/api/2023-04/graphql.json', [
          'body' => $PQ,
        ]);
        if ($request->getStatusCode() != 200) {
          return ['data' => [], 'status' => false];
        }
      } catch (Exception $e) {
        return ['data' => [], 'status' => false];
        dd($e->getMessage());

      }
      $body = $request->getBody();
      $response = json_decode($body);
      // dump($response->extensions);
      // dd($response->data);
      // dump($ids);
      if (!isset($response->data)) {
        continue;
        return ['data' => [], 'status' => false];
        // dd($response);
      } else
        if (!empty($response->data->products->edges)) {
          $edges = $response->data->products->edges;
          foreach ($edges as $edge) {
            // if ($edge->node->totalVariants == 2) //if a product has one physical and one digital sckip that otherwise include that product
            // {
            //   foreach ($edge->node->variants->edges as $variant) {
            //     $digital = false;
            //     $physical = false;
            //     if (!$digital || !$physical) {

            //       if ($variant->node->requiresShipping) {
            //         // dump($variant->node->requiresShipping);
            //         $physical = true;
            //       }
            //       if (!$variant->node->requiresShipping) {
            //         // dump($variant->node->requiresShipping);

            //         $digital = true;
            //       }
            //     }
            //     // if ($digital && $physical) {
            //     //   continue 2;
            //     // }
            //   }
            //   // dd($digital);
            // }
            // foreach($ed)
            // dd($edge->node->productCategory->productTaxonomyNode->name);
            $ids[] = $edge->node->id;
            $cursor = $edge->cursor;
            $products[] = $edge->node;
            if ($edge->node->productCategory) {
              $name = $edge->node->productCategory->productTaxonomyNode->name;
              $id = $edge->node->productCategory->productTaxonomyNode->id;

            }
            // dd($products);
          }
        }
      // sleep(2);
      $counter++;
    } while (isset($response->data) && $response->data->products->pageInfo->hasNextPage && $counter < 10);
    // dd($ids);
    return ['data' => $products, 'status' => true];
    return $products;
  }





  public function index()
  {
    try {

      $shop = 'nft-goo';
      $first = '2';


      $client = new Client([
        'base_uri' => "https://nft-goo.myshopify.com",
        // 'base_uri' => "https://8c4cbe.myshopify.com/",
        'headers' => [
          'Content-Type' => 'application/graphql',
          'X-Shopify-Access-Token' => $this->accessToken,
          'X-GraphQL-Cost-Include-Fields' => true
        ]
      ]);
      $DQ = '{
    product(id: "gid://shopify/Product/8180265156886") {
    
      id
      title
      handle
      tags
      productType
      totalVariants
      status
      description
      descriptionHtml
      createdAt
      updatedAt
      priceRangeV2 {
        maxVariantPrice {
          amount
          currencyCode
        }
        minVariantPrice {
          amount
          currencyCode
        }
      }
      images(first: 1) {
         
        nodes {
          src
          url
          originalSrc
          transformedSrc(maxWidth: 800, maxHeight: 800)
        }
      
    }
    productCategory {
      productTaxonomyNode {
        id
        fullName
        isRoot
        name
        isLeaf
      }
    }
    collections(first:10) {
      
        nodes {
          id
          title
        }
      
    }

     
      variants(first: 10) {
        edges {
          node {
            title
            price
            weight
            requiresShipping
            displayName
            image {
              originalSrc
              transformedSrc(maxHeight: 800, maxWidth: 800)
            }
            
            deliveryProfile {
              id
              name
              profileLocationGroups {
                countriesInAnyZone {
                  country {
                    id
                    name
                  }
                }
              }
            }
          }
        }
      }
    }
  }';
      $PQ = '{
    products(first: 1) {
      edges{
        cursor
        node {
          id
          title
          handle
          status
          createdAt
          productType
          totalVariants
          updatedAt
          images(first: 1) {
         
              nodes {
                src
                url
                originalSrc
                transformedSrc(maxWidth: 800, maxHeight: 800)
              }
            
          }
          productCategory {
            productTaxonomyNode {
              id
              fullName
              isRoot
              name
              isLeaf
            }
          }
          collections(first:10) {
            
              nodes {
                id
                title
              }
            
          }
          variants(first: 1) {
           
              nodes {
                id
                title
                sku
                price
                image {
                  originalSrc
                  transformedSrc(maxHeight: 800, maxWidth: 800)
                }
                weight
              }
            
          }
        }
      }
      
      pageInfo {
        endCursor
        hasNextPage
        hasPreviousPage
        startCursor
      }
    }
  }';
      // $sing = true;
      if (isset($sing)) {

        $response = $client->request('POST', '/admin/api/2023-04/graphql.json', [
          'body' => $DQ,
        ]);
        $body = $response->getBody();
        $data = json_decode($body);
        dd($data);
      }

      $resp = $this->results($client);
      // dd($resp);
    } catch (Exception $e) {
      dd($e->getMessage());
    }
  }


  public function shopify()
  {
    $shopName = '';
    $connected = false;
    $foundError = false;
    if (Session::has('userData')) {
      $userData = Session::get('userData');
      $accessToken = isset($userData['accessToken']) ? $userData['accessToken'] : '';
      $shopUrl = isset($userData['shopUrl']) ? $userData['shopUrl'] : '';
      $currencyCode = isset($userData['currencyCode']) ? $userData['currencyCode'] : '';
      if ($currencyCode != 'USD') {
        // $foundError = true;
      }
      if (!empty($accessToken) && !empty($shopUrl)) {
        $client = new Client([
          'base_uri' => $shopUrl,
          'headers' => [
            'Content-Type' => 'application/graphql',
            'X-Shopify-Access-Token' => $accessToken,
            'X-GraphQL-Cost-Include-Fields' => true
          ]
        ]);
        $PQ = '{
          shop {
            id,
            name,
            currencyCode
           }
    
        }';
        try {

          $request = $client->request('POST', '/admin/api/2023-04/graphql.json', [
            'body' => $PQ,
          ]);
          $body = $request->getBody();
          $response = json_decode($body);

          $shopName = $response->data->shop->name;
          // dd($request->getStatusCode())
          if ($request->getStatusCode() == 200) {

            $connected = true;
          } else {
            dd('failed');
            // API connection failed
            // Handle the error as needed
          }
        } catch (Exception $e) {
          dd($e->getMessage());
        }
      } else {
        $connected = false;
        $foundError = true;
      }
    } else {
      $connected = false;
    }

    $store = new Store();
    $userID = Session::get('userData')['user_id'];
    $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
    $store_id = $storeID->store_id;
    $storeData = getStoreDataById($store_id);

    $stripe = 0;
    if (count($storeData) > 0) {

      $stripe = $storeData[0]->stripe;
    }
    // $stripe = 0;

    // echo 'Data: <pre>' .print_r($stripe,true). '</pre>';

    // dd($products);
    return view('home.shopify', compact('connected', 'shopName', 'stripe', 'foundError'));
  }
  public function importallproducts()
  {
    set_time_limit(2000);
    if (Session::has('userData')) {
      $store = new Store();
      $userID = Session::get('userData')['user_id'];

      $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first()->toArray();

      $userData = Session::get('userData');
      $accessToken = $userData['accessToken'];
      $shopUrl = $userData['shopUrl'];
      if (!empty($accessToken) && !empty($shopUrl)) {
        $client = new Client([
          'base_uri' => $shopUrl,
          'headers' => [
            'Content-Type' => 'application/graphql',
            'X-Shopify-Access-Token' => $accessToken,
            'X-GraphQL-Cost-Include-Fields' => true
          ]
        ]);
        $response = $this->results($client);
        $shopifyCategories = [];
        $NA = false;
        // dd($response);
        if ($response['status'] == true) {
          $products = $response['data'];
          $categories = $this->productObj->getParentWithCildCategories();
          $shippingProfiles = $this->productObj->getShippingProfile()['shipping_profile'];
          foreach ($products as $key => $item) {

            if ($item->totalInventory && $item->totalInventory < 1) {
              unset($products[$key]);
              continue;
            }
            foreach ($item->variants->edges as $variant) {

              $price = $variant->node->price;
              $weight = $variant->node->weight;
              $sku = $variant->node->sku;
              if (empty($price) || $price == 0.0) {
                // if(empty($sku) || empty($weight) || $weight == 0.0  || empty($price) || $price == 0.0){
                unset($products[$key]);
                continue;

              }
            }
            // if(empty($item->variants->edges[0]->node->sku)){
            //   unset($products[$key]);
            //   continue;
            // }
            if ($item->variants->edges[0]->node->requiresShipping == false) {
              unset($products[$key]);
              continue;

            }
            // Check if variants array exists and has at least one element
            if (isset($item->variants) && count($item->variants->edges) > 0) {
              // Check if the SKU of the variant at the 0 index exists before in db
              $skuExists = $this->checkSkuExists($storeID['store_id'], $item->handle);
              // $skuExists = $this->checkSkuExists($storeID['store_id'],$item->variants->edges[0]->node->sku);

              if (isset($skuExists) && $skuExists == true) {

                unset($products[$key]);
                continue;
              }
              // dump($skuExists);
              // if ($item->variants->edges[0]->node->sku == "sku-hosted-1") {
              //     // Remove the item from the array
              // }
            }
            // dd($products);

          }
          // dd('');
          if (count($products) > 0) {
            foreach ($products as $product) {
              if ($product->productCategory) {
                $shopifyCategories[$product->productCategory->productTaxonomyNode->id] = $product->productCategory->productTaxonomyNode->name;
              } else if (!$NA) {
                $NA = true;
              }
            }
          }



          $view = view('home.shopifyProducts', compact('products', 'shopifyCategories', 'categories', 'shippingProfiles', 'NA'))->render();
          return response()->json(['view' => $view, 'status' => true], 200);

        } else {

          return response()->json(['view' => '', 'status' => false], 201);

        }

      }
    } else {
      $connected = false;
    }
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
  public function saveImportedProducts()
  {
    set_time_limit(2000);
    $req = $this->req;

    if (Session::has('userData')) {
      $userData = Session::get('userData');
      $accessToken = $userData['accessToken'];
      $shopUrl = $userData['shopUrl'];
      if (!empty($accessToken) && !empty($shopUrl)) {
        $client = new Client([
          'base_uri' => $shopUrl,
          'headers' => [
            'Content-Type' => 'application/graphql',
            'X-Shopify-Access-Token' => $accessToken,
            'X-GraphQL-Cost-Include-Fields' => true
          ]
        ]);
        if (isset($req->productsData)) {
          $data = json_decode(json_encode($req->productsData));

          if (count($data) > 0) {

            foreach ($data as $item) {
              if ($item->categoryId) {
                $this->singleProduct($client, $item);
              }

            }
          }

        }

      }
    }
  }
  public function singleProduct($client, $data)
  {

    $productQuery = '{
      product(id: "' . $data->productId . '") {
        id
        title
        tags
        handle
        status
        productType
        customProductType
        totalVariants
        description
        descriptionHtml
        updatedAt
        createdAt
        options(first: 3) {
          id
          name
          values
        }
        priceRangeV2 {
          maxVariantPrice {
            amount
            currencyCode
          }
          minVariantPrice {
            amount
            currencyCode
          }
        }
        images(first: 30) {
          nodes {
            url
            src
            originalSrc
            transformedSrc(maxWidth:800,maxHeight:800)
          }
        }
        productCategory{
          productTaxonomyNode{
            id
            fullName
            isRoot
            name
            isLeaf
          }
        }
        collections(first:2){
          nodes{
            id
            title
          }
        }
        variants(first:50){
          edges{
            node{
              title
              price
              sku
              weight
              weightUnit
              inventoryQuantity
              requiresShipping
              displayName
              image{
                originalSrc
                transformedSrc(maxWidth:800,maxHeight:800)
              }
              selectedOptions {
                name
                value
              }
            }
          }
        }
      }

    }';
    // try{
    $request = $client->request('POST', '/admin/api/2023-04/graphql.json', [
      'body' => $productQuery,
    ]);
    $body = $request->getBody();
    $response = json_decode($body);
    $this->storeProduct($data, $response->data->product);
    // }catch(Exception $e){
    //   // dd($e->getMessage());
    // }
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
  public function storeProduct($data, $product)
  {
    $store = new Store();
    $userID = Session::get('userData')['user_id'];
    $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
    // simple products
    // if($product->totalVariants > 0 && $product->totalVariants < 2){=
    if ($product->totalVariants > 0) {
      $variant1 = $product->variants->edges[0]->node;
      $productTitle = $product->title;
      // ***Handle is shopify slug*******
      // $productSlug = $product->handle;

      // $productSlug = $this->productObj->convert_to_slug(substr($productTitle, 0, 20));
      $productPrice = isset($variant1->price) ? $variant1->price : 0;
      $productStatus = $product->status == 'ACTIVE' ? 1 : 0;
      $productSKU = isset($variant1->sku) ? $variant1->sku : null;
      $productDescription = $product->description;
      $productWeightUnit = $variant1->weightUnit;

      $tempWeight = isset($variant1->weight) ? round($variant1->weight) : 0;
      if ($tempWeight < 1) {
        // $tempWeight = 1;
      }
      $tempWeight = number_format($tempWeight);
      $productWeight = $this->convertToPounds($tempWeight, $productWeightUnit);

      $productSizeLength = null;
      $productSizeHeight = null;
      $productSizeWidth = null;
      // $productSizeLength = 6;
      // $productSizeWidth = 3;
      // $productSizeHeight = 5;
      // $productWeight = 7;
      $productCurrentStock = isset($variant1->inventoryQuantity) ? $variant1->inventoryQuantity : 0;
      $weightType = 'pound';
      $dimentionType = 'inch';
      $productType = $product->totalVariants > 1 ? 'variant' : 'simple';
      $shippingOptionId = $data->profileId;


      // $productType = !$variant1->requiresShipping ? 'virtual' : null;
      $images = [];
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
      $handle = strtolower(trim($product->handle));
      $handle = preg_replace('/[^a-z0-9-]/', '-', $handle);
      $handle = preg_replace('/-+/', "-", $handle);
      $slug = rtrim($handle, '-');
      $productSlug = $slug . '-' . $storeId;
      if (!$userID) {
        $userID = 1;
      }
      if ($store_super_status != 1 || $storeStatus != 2) {
        $status = '0'; //active
      } else {
        $status = '1';
      }

      $productImages = $product->images->nodes;
      if (count($productImages) > 0) {
        $tempDestinationPath = public_path() . '/uploads/temp/';
        $destinationPath = public_path() . '/uploads/';
        $getDirectorywithSizes = $this->productObj->getAllAttachmentSizes();







        // dd($getDirectorywithSizes);
        if (!File::exists($destinationPath)) {
          File::makeDirectory($destinationPath, 0775, true);
        }

        foreach ($productImages as $image) {
          $src = $image->transformedSrc;
          // $filename = basename($src);
          $OriginalfileName = pathinfo($src, PATHINFO_FILENAME);
          $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
          $extension = explode('?', pathinfo($src, PATHINFO_EXTENSION));
          if (isset($extension[0])) {
            $fileExtension = $extension[0];
            $fileName = $this->productObj->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
            $client = new Client();
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
              $images[$OriginalfileName] = $fileName;
            } catch (Exception $e) {
              dd($e->getMessage());
            }

          }
          // dd('$stored');

        }
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

      $productFeatureImage = '';
      $productGallery = $image_array = array();
      $variationGallery = array();
      if (is_array($images) && count($images) > 0) {
        $counterImage = 0;
        foreach ($images as $key => $image) {
          $attachment = $this->productObj->getAttachmentId($image, 'image', false);
          if ($counterImage == 0) {
            $productFeatureImage = $attachment ? $attachment : null;
          }
          $productGallery[] = $attachment;
          $variationGallery[$key] = $attachment;
          // }
          $counterImage++;
        }
      }

      if ($productFeatureImage) {
        $newProduct->feature_Image = $productFeatureImage;
      }
      if (!empty($productGallery)) {
        $newProduct->gallery = serialize($productGallery);
      }
      $discount = Discount::where(['store_id' => $storeId, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();

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
              $productTags->tag_title = $tag;
              $productTags->save();
            }
            $tagCounter++;
          }
        }
        // add product variation
        if ($product->totalVariants > 1) {
          $vars = [];
          $options = [];
          if (isset($product->options)) {

            // add attributes
            $counter = 1;
            foreach ($product->options as $item) {
              // dd($item);
              $attributeKey = 'attribute' . $counter;
              $vars[$attributeKey][] = $item->name;

              $optionKey = 'option' . $counter;
              $options[] = $optionKey;


              $counter++;
            }

            //  dd($options);
            $counter = 1;
            $variationImages = array();
            $productInfo = array();
            foreach ($product->variants->edges as $variant) {
              // dump('variant',$variant->node->image);
              $node = $variant->node;
              // dd($node);
              // if(!empty($node->sku)){


              // dump($node->image ? $node->image->transformedSrc : null);
              $optionKey = 'option' . $counter;
              $priceKey = 'price';
              $skuKey = 'sku';
              $currentStockKey = 'current_stock';
              $visibilityKey = 'visibility';
              $variantionImageKey = 'product_variation_image';
              $vars[$priceKey][] = $node->price;
              $vars[$skuKey][] = $node->sku;
              $vars[$currentStockKey][] = $node->inventoryQuantity;
              $vars[$visibilityKey][] = $counter;
              $vars[$variantionImageKey][] = null;
              foreach ($options as $key => $opt) {
                $vars[$opt][] = $node->selectedOptions[$key]->value;
              }
              // $vars[$variantionImageKey][] = $node->image ? $node->image->transformedSrc : null; //uncomment to add images
              // $productInfo['$product_variation_image'][] = $node->image ? $node->image->transformedSrc : null; //uncomment to add images
              $src = $node->image ? $node->image->transformedSrc : null;
              $value = null;
              if ($src) {

                $imageName = pathinfo($src, PATHINFO_FILENAME);
                foreach ($variationGallery as $key => $val) {
                  if ($key == $imageName) {
                    $value = $val;
                    break;
                  }
                }
                $productInfo['$product_variation_image'][] = $value;
              } else {

                $productInfo['$product_variation_image'][] = $productFeatureImage;
              }
              // $vars[$optionKey] = $item->values;

              $counter++;
              // }
            }
            // dd($productInfo);
            $productInfo['productID'] = $productId;
            $productInfo['store_id'] = $storeId;



          }
          // dd($variationImages);
          if (count($vars['option1']) > 0) {
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
  public function testSingleProduct()
  {

    $client = new Client([
      'base_uri' => "https://nft-goo.myshopify.com/",
      'headers' => [
        'Content-Type' => 'application/graphql',
        'X-Shopify-Access-Token' => "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",
        'X-GraphQL-Cost-Include-Fields' => true
      ]
    ]);
    $categories = '{
      products(first: 250) {
        edges {
          cursor
          node {
            productCategory {
              productTaxonomyNode {
                name
              }
            }
          }
        }
      }

    }';
    $productQuery = '{
    
      product(id: "gid://shopify/Product/8312067195199") {
        id
        title
        tags
        handle
        status
        productType
        customProductType
        totalVariants
        description
        descriptionHtml
        updatedAt
        createdAt
        metafields(first: 10) {
          edges {
            node {
              id
              value
              key
              createdAt
              definition {
                name
                namespace
                ownerType
                pinnedPosition
                useAsCollectionCondition
                validationStatus
              }
            }
          }
        }
        options(first: 3) {
          id
          name
          values
        }
        priceRangeV2 {
          maxVariantPrice {
            amount
            currencyCode
          }
          minVariantPrice {
            amount
            currencyCode
          }
        }
        images(first: 1) {
          nodes {
            url
            src
            originalSrc
            transformedSrc(maxWidth:800,maxHeight:800)
          }
        }
        productCategory{
          productTaxonomyNode{
            id
            fullName
            isRoot
            name
            isLeaf
          }
        }
        collections(first:2){
          nodes{
            id
            title
          }
        }
        variants(first:10){
          edges{
            node{
              title
              price
              sku
              weight
              weightUnit
              inventoryQuantity
              requiresShipping
              displayName
              metafields(first: 10) {
                edges {
                  node {
                    id
                    value
                    key
                    createdAt
                    definition {
                      name
                      namespace
                      ownerType
                      pinnedPosition
                      useAsCollectionCondition
                      validationStatus
                    }
                  }
                }
              }
              image{
                originalSrc
                transformedSrc(maxWidth:800,maxHeight:800)
              }
              selectedOptions {
                name
              }
            }
          }
        }
      }

    }';
    try {
      $categories = $this->testResults($client);
      dd($categories);

    } catch (Exception $e) {
      dd($e->getMessage());
    }
  }
  public function shopifyCsv()
  {
    $foundError = false;
    $store = new Store();
    $userID = Session::get('userData')['user_id'];
    $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
    $store_id = $storeID->store_id;
    $storeData = getStoreDataById($store_id);

    $stripe = 0;
    if (count($storeData) > 0) {

      $stripe = $storeData[0]->stripe;
    }
    return view('home.shopify-csv', compact('stripe', 'foundError'));
  }
  public function importShopifyCsv(Request $request)
  {

    try {
      $foundError = false;
      $store = new Store();
      $userID = Session::get('userData')['user_id'];
      $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
      $store_id = $storeID->store_id;
      $storeData = getStoreDataById($store_id);

      $stripe = 0;
      if (count($storeData) > 0) {

        $stripe = $storeData[0]->stripe;
      }
      $shopifyCategories = [];
      $NA = false;
      $products = [];
      $product = [];
      $csvFile = $request->file('csv_file');
      $categories = $this->productObj->getParentWithCildCategories();
      $shippingProfiles = $this->productObj->getShippingProfile()['shipping_profile'];
      if (($handle = fopen($csvFile, 'r')) !== false) {
        // Read the headers
        $headers = fgetcsv($handle);
        if (!in_array('Handle', $headers) || !in_array('Title', $headers) || !in_array('Variant Price', $headers)) {
          $view = view('home.shopify-csv', compact('stripe', 'foundError'))->render();
          return response()->json(['view' => $view, 'status' => false], 200);
        } else {
          $distinationPath = public_path() . '/uploads/temp/';
          $fileName = $csvFile->getClientOriginalName();
          $moved = $csvFile->move($distinationPath, $fileName);
          while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, $data);
            $skuExists = $this->checkSkuExists($storeID['store_id'], $row['Handle']);
            if (isset($skuExists) && $skuExists == true) {
              continue;
            }
            if (
              (!array_key_exists('Variant Price', $row) || empty($row['Variant Price']) || $row['Variant Price'] == 0.0) ||
              (!array_key_exists('Status', $row) || empty($row['Status']) || $row['Status'] != 'active') ||
              (!array_key_exists('Variant Requires Shipping', $row) || empty($row['Variant Requires Shipping']) || $row['Variant Requires Shipping'] != 'TRUE')
              || (!array_key_exists('Title', $row) || empty($row['Title']))
            ) {
              continue;
            } else {
              $product['handle'] = $row['Handle'];
              $product['title'] = $row['Title'];
              $product['status'] = $row['Status'];
              $product['sku'] = $row['Variant SKU'];
              $product['stock'] = $product['stock'] = !empty($row['Variant Inventory Qty']) ? $row['Variant Inventory Qty'] : 0;
              $product['price'] = $row['Variant Price'];
              $product['weight'] = $row['Variant Grams'];

              if (array_key_exists('Image Src', $row) && !empty($row['Image Src'])) {
                $imgSrc = str_replace(' ', '_', $row['Image Src']);
                $product['img'] = $imgSrc;
              } else {
                $product['img'] = '';
              }
              if (array_key_exists('Product Category', $row) && !empty($row['Product Category'])) {
                $category = !empty($row['Product Category']) ? explode("?", $row['Product Category']) : null;
                $shopifyCategories['id'] = $category[0];
                $product['category'] = $category[0];
              } else if (!$NA) {
                $NA = true;
                $product['category'] = 'NA';
              }
              if ((!empty($row['Option1 Name']) || !empty($row['Option2 Name']) || !empty($row['Option3 Name']))) {

                $product['type'] = 'variable';
              } else {
                $product['type'] = 'simple';
              }

              $products[] = $product;
            }
          }
          //    foreach ($shopifyCategories as $key => $sCategory){
          //   dd($shopifyCategories);
          //  }
          $view = view('home.csv-products', compact('products', 'categories', 'shippingProfiles', 'NA', 'shopifyCategories', 'fileName', 'foundError'))->render();
          return response()->json(['view' => $view, 'status' => true], 200);
        }
      }
    } catch (\Throwable $th) {
      echo $th->getMessage();
    }
  }

  public function CsvProducts()
  {
    set_time_limit(0);
    try {
      $req = $this->req;
      $filePath = $req->input('uploadedFilePath');
      $productsData = $req->product;
      $file = public_path('/uploads/temp/' . "{$filePath}");
      if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle);
        $single_product = [];
        rewind($handle);
        // Skip the header row
        fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== false) {
          $row = array_combine($headers, $data);
          if ($row['Handle'] == $productsData['productHandle'] && $row['Title'] == $productsData['title']) {

            $single_product['productTitle'] = !empty($row['Title']) ? $row['Title'] : null;
            $single_product['handle'] = !empty($row['Handle']) ? $row['Handle'] : null;
            $single_product['status'] = !empty($row['Status']) ? $row['Status'] : null;
            $single_product['ProductvariationData']['sku'][''] = !empty($row['Variant SKU']) ? $row['Variant SKU'] : null;
            $single_product['ProductvariationData']['price'][] = !empty($row['Variant Price']) ? $row['Variant Price'] : 0;
            $single_product['ProductvariationData']['attribute1'] = !empty($row['Option1 Name']) ? $row['Option1 Name'] : null;
            $single_product['ProductvariationData']['attribute2'] = !empty($row['Option2 Name']) ? $row['Option2 Name'] : null;
            $single_product['ProductvariationData']['attribute3'] = !empty($row['Option3 Name']) ? $row['Option3 Name'] : null;
            $single_product['feature_image'] = !empty($row['Image Src']) ? $row['Image Src'] : '';
            $category = !empty($row['Product Category']) ? explode("?", $row['Product Category']) : null;
            $single_product['productCategory'] = isset($category) ? $category[0] : 'NA';
            $single_product['description'] = !empty($row['Body (HTML)']) ? $row['Body (HTML)'] : null;
            $single_product['ProductvariationData']['option1'][] = !empty($row['Option1 Value']) ? $row['Option1 Value'] : null;
            $single_product['ProductvariationData']['option2'][] = !empty($row['Option2 Value']) ? $row['Option2 Value'] : null;
            $single_product['ProductvariationData']['option3'][] = !empty($row['Option3 Value']) ? $row['Option3 Value'] : null;
            $single_product['ProductvariationData']['product_variation_image'][] = !empty($row['Variant Image']) ? $row['Variant Image'] : null;
            $single_product['ProductvariationData']['weightUnit'] = !empty($row['Variant Weight Unit']) ? $row['Variant Weight Unit'] : null;
            $single_product['ProductvariationData']['weight'] = !empty($row['Variant Grams']) ? $row['Variant Grams'] : null;
            $single_product['product_tag'] = !empty($row['Tags']) ? explode(",", $row['Tags']) : null;
            $single_product['productPrice'] = !empty($row['Variant Price']) ? $row['Variant Price'] : null;
            $single_product['ProductvariationData']['current_stock'][] = !empty($row['Variant Inventory Qty']) ? $row['Variant Inventory Qty'] : 0;
            $single_product['productSKU'] = !empty($row['Variant SKU']) ? $row['Variant SKU'] : null;
            $single_product['images'][] = !empty($row['Image Src']) ? $row['Image Src'] : null;
          } else if (($row['Handle'] == $productsData['productHandle'] && $row['Title'] != $productsData['title']) && !empty($row['Variant Price'])) {
            $single_product['ProductvariationData']['product_variation_image'][] = !empty($row['Variant Image']) ? $row['Variant Image'] : null;
            $single_product['ProductvariationData']['sku'][] = !empty($row['Variant SKU']) ? $row['Variant SKU'] : '';
            $single_product['ProductvariationData']['price'][] = !empty($row['Variant Price']) ? $row['Variant Price'] : 0;
            $single_product['ProductvariationData']['current_stock'][] = !empty($row['Variant Inventory Qty']) ? $row['Variant Inventory Qty'] : 0;
            $single_product['ProductvariationData']['option1'][] = !empty($row['Option1 Value']) ? $row['Option1 Value'] : null;
            $single_product['ProductvariationData']['option2'][] = !empty($row['Option2 Value']) ? $row['Option2 Value'] : null;
            $single_product['ProductvariationData']['option3'][] = !empty($row['Option3 Value']) ? $row['Option3 Value'] : null;
            $single_product['images'][] = !empty($row['Image Src']) ? $row['Image Src'] : null;
            $single_product['productType'] = 'variant';
          } else if (($row['Handle'] == $productsData['productHandle'] && $row['Title'] != $productsData['title']) && !empty($row['Image Src'])) {
            $single_product['images'][] = !empty($row['Image Src']) ? $row['Image Src'] : null;
          } else {
            continue;
          }
        }
        if (!empty($single_product)) {
          if (isset($single_product['images'])) {
            $single_product['images'] = array_filter($single_product['images'], function ($value) {
              return !is_null($value);
            });
            if (count($single_product['images']) === 0) {
              $single_product['images'] = [];
            }
          }
          $product = $single_product;
          $productsSave = $this->saveCsvProducts($productsData, $product);
        }
      }
    } catch (\Exception $e) {
      dd($e->getMessage());
    }
  }
  public function saveCsvProducts($productData, $product)
  {
    DB::beginTransaction();
    try {
    $store = new Store();
    $userID = Session::get('userData')['user_id'];
    $storeID = $store->select('store_id')->where('user_id', '=', $userID)->first();
    $variant1 = $product['ProductvariationData'];
    $productTitle = $product['productTitle'];
    $productPrice = isset($product['productPrice']) ? $product['productPrice'] : 0;
    $productStatus = $product['status'] == 'active' ? 1 : 0;
    $productSKU = $product['productSKU'];
    $productDescription = $product['description'];
    $shippingOptionId = isset($productData[0]['profileId']) ? $productData[0]['profileId'] : null;
    $productWeightUnit = $variant1['weightUnit'];
    if ($productWeightUnit == 'g') {
      $productWeightUnit = 'GRAMS';
    }

    $tempWeight = isset($variant1['weight']) ? round($variant1['weight']) : 0;
    if ($tempWeight < 1) {
    }
    // $tempWeight = number_format($tempWeight);
    $productWeight = $this->convertToPounds($tempWeight, $productWeightUnit);

    $productSizeLength = null;
    $productSizeHeight = null;
    $productSizeWidth = null;
    $productCurrentStock = 0;
    $weightType = 'pound';
    $dimentionType = 'inch';
    $productType = isset($product['productType']) ? $product['productType'] : 'simple';
    $images = [];
    $storeId = '';
    if (isset($storeID->store_id) && $storeID->store_id != '') {
      $storeId = $storeID->store_id;
      $storeStatus = getStoreInfoById($storeId);
      $store_super_status = $storeStatus[0]->super_status;
      $storeStatus = $storeStatus[0]->store_status;
    } else {
      $storeId = 1;
    }
    if ($store_super_status != 1 || $storeStatus != 2) {
      $status = '0'; //active
    } else {
      $status = '1';
    }
    $handle = strtolower(trim($product['handle']));
    $handle = preg_replace('/[^a-z0-9-]/', '-', $handle);
    $handle = preg_replace('/-+/', "-", $handle);
    $slug = rtrim($handle, '-');
    $productSlug = $slug . '-' . $storeId;
    if (!$userID) {
      $userID = 1;
    }
    $productImages = $product['images'];
    if (count($productImages) > 0) {
      $tempDestinationPath = public_path() . '/uploads/temp/';
      $destinationPath = public_path() . '/uploads/';
      $getDirectorywithSizes = $this->productObj->getAllAttachmentSizes();
      if (!File::exists($destinationPath)) {
        File::makeDirectory($destinationPath, 0775, true);
      }
      $startTime = microtime(true);
      foreach ($productImages as $image) {
        if (isset($image)) {
          $src = $image;
          $OriginalfileName = pathinfo($src, PATHINFO_FILENAME);
          // dd($OriginalfileName);
          $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
          $extension = explode('?', pathinfo($src, PATHINFO_EXTENSION));
          if (isset($extension[0])) {
            $fileExtension = $extension[0];
            $fileName = $this->productObj->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
            $client = new Client();
            $response = $client->get($src);
            try {

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

            } catch (Exception $e) {
              dd($e->getMessage());
            }
          }
        } else {
          continue;
        }

      }
      $endTime = microtime(true);
      \Log::info("Image processing time: " . ($endTime - $startTime) . " seconds");
    } else {
      $status = '3';
    }
    $newProduct = new Product();
    $newProduct->user_id = $userID;
    $newProduct->store_id = $storeId;
    $newProduct->title = $productTitle;
    $newProduct->slug = $productSlug;
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
    $productFeatureImage = '';
    $productGallery = $image_array = array();
    $variationGallery = array();
    if (is_array($images) && count($images) > 0) {
      $counterImage = 0;
      foreach ($images as $key => $image) {
        $attachment = $this->productObj->getAttachmentId($image, 'image', false);
        if ($counterImage == 0) {
          $productFeatureImage = $attachment;
        }

        //  else{

        $productGallery[] = $attachment;
        $variationGallery[$key] = $attachment;
        // }
        $counterImage++;
      }
    }

    if ($productFeatureImage) {
      $newProduct->feature_Image = $productFeatureImage;
    }
    if (!empty($productGallery)) {
      $newProduct->gallery = serialize($productGallery);
    }
    $discount = Discount::where(['store_id' => $storeId, 'type' => 'sale', 'status' => 'active', 'state' => 'live'])->first();

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
    if ($newProduct) {

      $productId = $newProduct->product_id;
      if (isset($productData['categoryId'])) {

        $category = category::find($productData['categoryId']);
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
      if (isset($product['product_tag']) && count($product['product_tag']) > 0) {
        $tagCounter = 1;

        foreach ($product['product_tag'] as $tag) {
          if ($tagCounter < 11) {

            $productTags = new ProductTags();
            $productTags->product_id = $productId;
            $productTags->tag_title = $tag;
            $productTags->save();
          }
          $tagCounter++;
        }
      }

      $vars = [];
      $options = [];
      if (isset($product['productType']) && $product['productType'] == 'variant') {

        // add attributes
        $counter = 1;
        if (isset($product['ProductvariationData']['attribute1'])) {
          $attributeKey = 'attribute1';
          $vars[$attributeKey][] = $product['ProductvariationData']['attribute1'];

          $optionKey = 'option1';
          $options[] = $optionKey;
        }
        if (isset($product['ProductvariationData']['attribute2'])) {
          $attributeKey = 'attribute2';
          $vars[$attributeKey][] = $product['ProductvariationData']['attribute2'];

          $optionKey = 'option2';
          $options[] = $optionKey;
        }
        if (isset($product['ProductvariationData']['attribute3'])) {
          $attributeKey = 'attribute3';
          $vars[$attributeKey][] = $product['ProductvariationData']['attribute3'];

          $optionKey = 'option3';
          $options[] = $optionKey;
        }
        $productInfo = array();
        $variants = $product['ProductvariationData'];
        $productInfo['productID'] = $productId;
        $productInfo['store_id'] = $storeId;
        foreach ($product['ProductvariationData']['product_variation_image'] as $imageUrl) {
          $fileName = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_FILENAME);
          $attachment = DB::table('attachment')->where('fileName', $fileName)->first();
          $productInfo['$product_variation_image'][] = $attachment ? $attachment->attachment_id : $productFeatureImage;
        }
        if (count($variants['option1']) > 0) {
          $this->productObj->addProductVariation($variants, $productInfo, 'add');
        }
      }
    }
    $this->_esearch->esMApping($productId);
  
    DB::commit(); 

  } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Error saving product: ' . $e->getMessage());
      throw $e;
  }
}


}








