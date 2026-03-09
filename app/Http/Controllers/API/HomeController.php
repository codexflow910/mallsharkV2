<?php

namespace App\Http\Controllers\API;


use App\Models\States;
use App\Models\category;
use App\Models\Countries;
use App\Models\StatesList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Store\Storeaccess;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\SlidesController;
use App\Repositories\Product\Productaccess;
use App\Http\Controllers\ElasticSearchController;

class HomeController extends Controller
{
    private $slides_obj;
    private $productObj;
    private $_storeobj;
    private $elasticSearchObj;
    private $_request;

    public function __construct(Request $request)
    {
        $this->productObj = new Productaccess();
        $this->_storeobj = new Storeaccess();
        $this->slides_obj = new SlidesController();
        $this->elasticSearchObj = new ElasticSearchController();

        $this->_request = $request;
    }

    public function index()
    {

        if ($this->_request->filled(['limit'])) {
            $limit = $this->_request->input('limit');

            /*
        * Simple Products array 
        */

            // $simpleProducts = $this->productObj->getProductsApi('created_at', 'asc', $limit, true);
            // $simpleProductsArray = [];
            // if (count($simpleProducts['data']) > 0) {

            //     foreach ($simpleProducts['data'] as $value) {
            //         $simpleProduct = (array) $value; // Convert the object to an array
            //         $simpleProduct['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
            //         $simpleProductsArray[] = $simpleProduct;
            //     }

            // }

            // Accept and Rehect cookie 
            // $productsYouMayLikeDB = [];
            // $productsYouMayLikeElastic = [];
            // if (!empty($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] == 1 && !empty($_COOKIE['tags'])) {
            //     $tags = json_decode($_COOKIE['tags']);
            //     $all_filters['keyword'] = implode(" ", $tags);
            //     $productsYouMayLikeElastic = $this->elasticSearchObj->searchDocument($all_filters);
            // } else {
            //     /*
            // * Product may you like array 
            // */
            //     $productsYouMayLikeDB =  $this->productObj->getProductsApi('created_at', 'desc', $limit, true);
            //     $productsYouMayLikeDBArray = [];

            //     foreach ($productsYouMayLikeDB['data'] as $value) {
            //         $productsYouMayLike = (array) $value; // Convert the object to an array
            //         $productsYouMayLike['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
            //         $productsYouMayLikeDBArray[] = $productsYouMayLike;
            //     }

            // }

            /*
        * Recent Products array 
        */

            $recentProducts = $this->productObj->getProductsApi('created_at', 'desc', $limit);
            $recentProductsArray = [];
            if (count($recentProducts) > 0) {

                foreach ($recentProducts['data'] as $value) {
                    $recentProduct = (array) $value; // Convert the object to an array
                    $recentProduct['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
                    $recentProductsArray[] = $recentProduct;
                }
            }

            /*
        * Top Selling Products array 
        */

            // $topSelling = $this->productObj->getTopSellingProductsApi($limit);
            // $topSellingArray = [];
            // if (count($topSelling['data']) > 0) {

            //     foreach ($topSelling['data'] as $value) {
            //         $topSellingProduct = (array) $value; // Convert the object to an array
            //         $topSellingProduct['image_path'] = getAttachmentUrlById((int) $value['feature_image'], 'media');
            //         $topSellingArray[] = $topSellingProduct;
            //     }
            // }

            /*
        * Feature Products array 
        */

            // $featuredProducts = $this->productObj->getFeaturedProductsApi($limit);
            // $featuredArray = [];
            // if (count($featuredProducts) > 0) {

            //     foreach ($featuredProducts as $value) {
            //         $featuredProduct = (array) $value; // Convert the object to an array
            //         $featuredProduct['image_path'] = getAttachmentUrlById((int) $value['feature_image'], 'media');
            //         $featuredArray[] = $featuredProduct;
            //     }
            // }

            /*
        * Top Brands Products array 
        */

            // $topBrands = $this->_storeobj->getBrandsOfWeekApi(1,$limit);
            // $topBrandsArray = [];
            // if (count($topBrands['data']) > 0) {

            //     foreach ($topBrands['data'] as $value) {
            //         $topBrand = (array) $value; // Convert the object to an array
            //         $topBrand['image_path'] = getAttachmentUrlById($value['store_logo'], 'media');
            //         $topBrandsArray[] = $topBrand;
            //     }
            // }

            /*
        * Best Sellers Products array 
        */

            $bestSellers =  $this->productObj->getBestSellerProductsApi($limit, true);

            $bestSellersArray = [];
            if (count($bestSellers['data']) > 0) {
                foreach ($bestSellers['data'] as $best) {

                    // $average = 0;
                    // if (isset($best['reviews']) && count($best['reviews']) > 0) {

                    //     $numer = 0;
                    //     foreach ($best['reviews'] as $rev) {
                    //         $numer += $rev['rating'];
                    //     }

                    //     $average = round($numer / count($best['reviews']));
                    //     $average = isset($average) ? $average : "";
                    // }
                    // $bestSeller['average'] = $average;
                    $bestSeller = (array) $best; // Convert the object to an array
                    $bestSeller['image_path'] = getAttachmentUrlById((int) $best['feature_image'], 'media');
                    $bestSeller['average'] = round($best['total_rating'] / $best['review_count']);

                    $bestSellersArray[] = $bestSeller;
                }
            }


            /*
        * Explore More Products array 
        */

            // $exploreMore =  $this->productObj->getProductsApi('created_at', 'desc', $limit, true);
            // $exploreMoreArray = [];
            // if (count($exploreMore['data']) > 0) {

            //     foreach ($exploreMore['data'] as $value) {
            //         $explore = (array) $value; // Convert the object to an array
            //         $explore['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
            //         $exploreMoreArray[] = $explore;
            //     }

            // }

            /*
        * Feature Products array 
        */

            // $featureProducts = $this->productObj->getFeatureProductsApi('', '', $limit);
            // $featureProductsArray = [];
            // if (count($featureProducts['data']) > 0) {

            //     foreach ($featureProducts['data'] as $value) {
            //         $featureProduct = (array) $value; // Convert the object to an array
            //         $featureProduct['image_path'] = getAttachmentUrlById((int) $value['feature_image'], 'media');
            //         $featureProductsArray[] = $featureProduct;
            //     }

            // }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please set your limit parameter or check its value.',
            ]);
        }

        try {
            return response()->json([
                'status' => true,
                'message' => 'Homescreen products fetched successfully',
                'data' => [
                    'tabs' => [
                        'bestSellers',
                        'RecommendedProducts',
                        // 'simpleProducts',
                        // 'productsYouMayLikeDB',
                        // 'productsYouMayLikeElastic',
                        // 'topSelling',
                        // 'featuredProducts',
                        // 'topBrands',
                        // 'featureProducts',
                        // 'exploreMore',
                    ],
                    'tabData' => [
                        'bestSellers' => $bestSellersArray,
                        'RecommendedProducts' => $recentProductsArray,
                        // 'simpleProducts' => $simpleProductsArray,
                        // 'productsYouMayLikeDB' => $productsYouMayLikeDBArray,
                        // 'productsYouMayLikeElastic' => $productsYouMayLikeElastic,
                        // 'topSelling' => $topSellingArray,
                        // 'featuredProducts' => $featuredArray,
                        // 'topBrands' => $topBrandsArray,
                        // 'featureProducts' => $featureProductsArray,
                        // 'exploreMore' => $exploreMoreArray,
                    ],
                ],

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function loadMoreProducts()
    {
        if ($this->_request->filled(['page', 'tab', 'limit'])) {
            $limit = $this->_request->input('limit');
            $page = $this->_request->input('page');
            $tab = $this->_request->input('tab');
            /*
            * Simple Products  
            */
            if ($tab == 'simpleProducts') {


                $simpleProducts = $this->productObj->getProductsApiPagination('created_at', 'asc', $limit, true, $page);
                $simpleProductsArray = [];
                if (count($simpleProducts) > 0) {

                    foreach ($simpleProducts as $value) {
                        $simpleProduct = (array) $value; // Convert the object to an array
                        $simpleProduct['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
                        $simpleProductsArray[] = $simpleProduct;
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Homescreen simpleProducts fetched successfully',
                        'tabData' => [
                            'simpleProducts' => $simpleProductsArray,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Products Found',
                    ]);
                }
            } elseif ($tab == 'RecommendedProducts') {

                /*
            * Recent Products array 
            */

                $recentProducts = $this->productObj->getRecentProductsApiPagination('created_at', 'asc', $limit, true, $page);
                $recentProductsArray = [];
                if (count($recentProducts) > 0) {

                    foreach ($recentProducts as $value) {
                        $recentProduct = (array) $value; // Convert the object to an array
                        $recentProduct['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
                        $recentProductsArray[] = $recentProduct;
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'Homescreen RecommendedProducts  fetched successfully',
                        'tabData' => [
                            'RecommendedProducts' => $recentProductsArray,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Products Found',
                    ]);
                }
            } elseif ($tab == 'bestSellers') {


                /*
            * Best Sellers Products array 
            */

                // $bestSellers =  $this->productObj->getBestSellerProductsApiPagination($limit, true, $page);
                //    $bestSellers = $this->productObj->getProductsApiPagination('created_at', 'asc', $limit, true, $page);
                // dd($bestSellers);
                // echo 'Data: <pre>' .print_r($bestSellers,true). '</pre>'; die();

                // $bestSellersArray = [];
                // if (count($bestSellers['data']) > 0) {
                //     foreach ($bestSellers['data'] as $best) {

                //         $average = 0;
                //         if (isset($best['reviews']) && count($best['reviews']) > 0) {

                //             $numer = 0;
                //             foreach ($best['reviews'] as $rev) {
                //                 $numer += $rev['rating'];
                //             }

                //             $average = round($numer / count($best['reviews']));
                //             $average = isset($average) ? $average : "";
                //         }
                //         $bestSeller = (array) $best; // Convert the object to an array
                //         $bestSeller['image_path'] = getAttachmentUrlById((int) $best['feature_image'], 'media');
                //         $bestSeller['average'] = $average;

                //         $bestSellersArray[] = $bestSeller;
                //     }
                //     return response()->json([
                //         'status' => true,
                //         'message' => 'Homescreen bestSellers fetched successfully',
                //         'tabData' => [
                //             'bestSellers' => $bestSellersArray,
                //         ],
                //     ]);
                // } else {
                //     return response()->json([
                //         'status' => false,
                //         'message' => 'No Products Found',
                //     ]);
                // }
                $recentProducts = $this->productObj->getProductsApiPagination('created_at', 'asc', $limit, true, $page);
                // dd($recentProducts);
                $recentProductsArray = [];
                if (count($recentProducts) > 0) {

                    foreach ($recentProducts as $value) {
                        $recentProduct = (array) $value; // Convert the object to an array
                        $recentProduct['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
                        $recentProduct['average'] = round($value['total_rating'] / $value['review_count']);
                        

                        $recentProductsArray[] = $recentProduct;
                    }
                    shuffle($recentProductsArray);
                    return response()->json([
                        'status' => true,
                        'message' => 'Homescreen RecommendedProducts  fetched successfully',
                        'tabData' => [
                            'bestSellers' => $recentProductsArray,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Products Found',
                    ]);
                }
            } elseif ($tab == 'productsYouMayLikeDB') {
                // Accept and Rehect cookie 
                $productsYouMayLikeDB = [];
                $productsYouMayLikeElastic = [];
                if (!empty($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] == 1 && !empty($_COOKIE['tags'])) {
                    $tags = json_decode($_COOKIE['tags']);
                    $all_filters['keyword'] = implode(" ", $tags);
                    $productsYouMayLikeElastic = $this->elasticSearchObj->searchDocument($all_filters);
                } else {
                    /*
                * Product may you like array 
                */
                    $productsYouMayLikeDB =  $this->productObj->getProductsApiPagination('created_at', 'asc', $limit, true, $page);
                    $productsYouMayLikeDBArray = [];

                    foreach ($productsYouMayLikeDB as $value) {
                        $productsYouMayLike = (array) $value; // Convert the object to an array
                        $productsYouMayLike['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
                        $productsYouMayLikeDBArray[] = $productsYouMayLike;
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Homescreen productsYouMayLikeDB fetched successfully',
                    'tabData' => [
                        'productsYouMayLikeDB' => $productsYouMayLikeDBArray,
                    ],
                ]);
            } elseif ($tab == 'topSelling') {
                /*
            * Top Selling Products array 
            */

                $topSelling = $this->productObj->getTopSellingProductsApi($limit);
                $topSellingArray = [];
                if (count($topSelling['data']) > 0) {

                    foreach ($topSelling['data'] as $value) {
                        $topSellingProduct = (array) $value; // Convert the object to an array
                        $topSellingProduct['image_path'] = getAttachmentUrlById((int) $value['feature_image'], 'media');
                        $topSellingArray[] = $topSellingProduct;
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Homescreen topSelling fetched successfully',
                        'tabData' => [
                            'topSelling' => $topSellingArray,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Products Found',
                    ]);
                }
            } elseif ($tab == 'featuredProducts') {

                /*
            * Feature Products array 
            */

                $featuredProducts = $this->productObj->getFeaturedProductsApi($limit);
                $featuredArray = [];
                if (count($featuredProducts) > 0) {

                    foreach ($featuredProducts as $value) {
                        $featuredProduct = (array) $value; // Convert the object to an array
                        $featuredProduct['image_path'] = getAttachmentUrlById((int) $value['feature_image'], 'media');
                        $featuredArray[] = $featuredProduct;
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Homescreen featuredProducts fetched successfully',
                        'tabData' => [
                            'featuredProducts' => $featuredArray,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Products Found',
                    ]);
                }
            } elseif ($tab == 'topBrands') {

                /*
            * Top Brands Products array 
            */

                $topBrands = $this->_storeobj->getBrandsOfWeekApi($page, $limit);
                $topBrandsArray = [];
                if (count($topBrands['data']) > 0) {

                    foreach ($topBrands['data'] as $value) {
                        $topBrand = (array) $value; // Convert the object to an array
                        $topBrand['image_path'] = getAttachmentUrlById((int) $value['store_logo'], 'media');
                        $topBrandsArray[] = $topBrand;
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Homescreen topBrands fetched successfully',
                        'tabData' => [
                            'topBrands' => $topBrandsArray,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Brands Found',

                    ]);
                }
            } elseif ($tab == 'featureProducts') {

                /*
            * Feature Products array 
            */

                $featureProducts = $this->productObj->getFeatureProductsApiPagination('', '', $limit, $page);

                $featureProductsArray = [];
                if (count($featureProducts['data']) > 0) {

                    foreach ($featureProducts['data'] as $value) {
                        $featureProduct = (array) $value; // Convert the object to an array
                        $featureProduct['image_path'] = getAttachmentUrlById((int) $value['feature_image'], 'media');
                        $featureProductsArray[] = $featureProduct;
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Homescreen featureProducts fetched successfully',
                        'tabData' => [
                            'featureProducts' => $featureProductsArray,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Products Found',

                    ]);
                }
            } elseif ($tab == 'exploreMore') {

                /*
            * Explore More Products array 
            */

                $exploreMore =  $this->productObj->getProductsApiPagination('created_at', 'desc', $limit, true, $page);
                $exploreMoreArray = [];
                if (count($exploreMore) > 0) {

                    foreach ($exploreMore as $value) {
                        $explore = (array) $value; // Convert the object to an array
                        $explore['image_path'] = getAttachmentUrlById($value['feature_image'], 'media');
                        $exploreMoreArray[] = $explore;
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Homescreen exploreMore fetched successfully',
                        'tabData' => [
                            'exploreMore' => $exploreMoreArray,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'No Products Found',

                    ]);
                }
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please set your page, tab and limit parameter or check its value.',
            ]);
        }
    }

    public function getHomeCategories()
    {
        try {
            // add limit for products and childCategires.products
            //fetch only selected data which is to be utilize for these mob screens
            // $Categories = Category::with(['products', 'childCategories.products'])->where('parentCategoryID', 0)->where('categoryTitle','!=','Uncategorized')->limit(13)->get()->toArray();
            $Categories = Category::where('parentCategoryID', 0)
                ->where('categoryTitle', '!=', 'Uncategorized')
                ->limit(16)
                ->whereHas('products', function ($query) {
                    $query->where('status', '1');
                })
                ->get();

            // Load products and child category products with limits
            $Categories->each(function ($category) {
                // Load products for the current category
                $category->products = $category->products()->where('status', '1')->limit(10)->get();

                // Load child categories and their products
                $category->childCategories = $category->childCategories()
                ->whereHas('products', function ($query) {
                    $query->where('status', '1');
                })
                ->get();
        
            $category->childCategories->each(function ($childCategory) {
                // Load products for each child category
                $childCategory->products = $childCategory->products()->where('status', '1')->limit(10)->get();
            });
            });
            $categoryArray = [];

            foreach ($Categories as $category) {
                $categoryData = $category->only(['id', 'categoryTitle', 'category_slug','image_id']);
                // $categoryData = $category;
                $categoryData['category_image'] = getAttachmentUrlById((int)$category->image_id, 'small');
            
                $parentProductArray = [];
                foreach ($category->products as $product) {
                    $productData = $product->only(['product_id', 'title', 'slug','price','on_sale','sale_price','discount_percentage']);
                    // $productData = $product;
                    $productData['parent_product_image'] = getAttachmentUrlById((int)$product->feature_image, 'medium');
                    $parentProductArray[] = $productData;
                }
            
                $childCategoryArray = [];
                foreach ($category->childCategories as $childCategory) {
                    $childCategoryData = $childCategory->only(['id', 'categoryTitle', 'category_slug','image_id']);
                    // $childCategoryData = $childCategory;
                    $childCategoryData['child_cetegory_image'] = getAttachmentUrlById((int)$childCategory->image_id, 'small');
            
                    $childProductArray = [];
                    foreach ($childCategory->products as $childProduct) {
                        $childProductData = $childProduct->only(['product_id', 'title', 'slug','price','on_sale','sale_price','discount_percentage']);
                        // $childProductData = $childProduct;
                        $childProductData['child_product_image'] = getAttachmentUrlById((int)$childProduct->feature_image, 'small');
                        $childProductArray[] = $childProductData;
                    }
                    $childCategoryData['products'] = $childProductArray;
            
                    $childCategoryArray[] = $childCategoryData;
                }
            
                $categoryData['products'] = $parentProductArray;
                $categoryData['child_categories'] = $childCategoryArray;
            
                $categoryArray[] = $categoryData;
            }

            if ($categoryArray) {
                return response()->json([
                    'status' => true,
                    'message' => "All Categories",
                    'categories' => $categoryArray,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You dont have any Cateogory",

                ], 401);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public function sortingHomeProducts()
    {
        try {
            // Extract query parameters
            $categoryId = $this->_request->input('category_name');
            $sortBy =  $this->_request->input('sort_by'); // Default to sorting by price
            $sortOrder =  $this->_request->input('sort_order'); // Default to ascending order

            // $query = Category::with(['products', function($query,$sortOrder){
            //     $query->orderBy('price', $sortOrder);
            // }, 'childCategories.products'])
            //     ->where('categoryTitle', $categoryId)
            //     ->where('categoryTitle', '!=', 'Uncategorized');

            // // Apply sorting based on user input


            // $Categories = $query->limit(13)->get();
            // return $Categories;
            $Categories = Category::with([
                'products' => function ($query) use ($sortBy, $sortOrder) {
                    $query->orderBy($sortBy, $sortOrder);
                }
                // 'childCategories.products'
            ])
                ->where('categoryTitle', $categoryId)
                ->where('categoryTitle', '!=', 'Uncategorized')
                ->limit(13)
                ->get()->toArray();

            // return $Categories;
            // Rest of your code remains unchanged...
            $categoryArray = [];
            foreach ($Categories as $key => $value) {
                $category = (array) $value;
                $category['category_image'] = getAttachmentUrlById(intval($value['image_id']), 'media');
                $parentProductArray = [];
                foreach ($value['products'] as $key => $product) {
                    $pp = (array) $product;
                    $pp['parent_product_image'] = getAttachmentUrlById(intval($product['feature_image']), 'media');
                    // dd($pp);
                    // return $pp;
                    if ($product['status'] == 1) {
                        $parentProductArray[] = $pp;
                    }
                }

                $childCategoryArray = [];
                // dd($value['child_categories']);
                // foreach ($value['child_categories'] as $key => $child) {
                //     $childcate = (array) $child;
                //     $childcate['child_cetegory_image'] = getAttachmentUrlById((int)$child['image_id'], 'media');

                //     $childproductArray = [];
                //     foreach ($child['products'] as $childProduct) {
                //         $childproduct = (array) $childProduct;
                //         $childproduct['child_product_image'] = getAttachmentUrlById((int)$childProduct['feature_image'], 'media');
                //         if($childproduct['status'] == 1){
                //             $childproductArray[] = $childproduct;
                //         }

                //     }
                //     $childcate['products'] = $childproductArray;

                //     $childCategoryArray[] = $childcate;
                // }

                $category['products'] = $parentProductArray;
                // $category['child_categories'] = $childCategoryArray;

                $categoryArray[] = $category;
            }
            if ($categoryArray) {
                return response()->json([
                    'status' => true,
                    'message' => "All Categories",
                    'categories' => $categoryArray,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You don't have any product",
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    public function getHomeBanners()
    {
        try {
            /*
        * Home Page Banners array 
        */
            $slides_main = $this->slides_obj->getAllSlidesByCategory('home-small');
            $bannerArray = []; // Initialize an array to store banner information

            if (count($slides_main) > 0) {
                $i = 1;
                foreach ($slides_main as $main_slide) {
                    $banner = []; // Initialize an array for each banner

                    $banner['banner_image_path'] = getAttachmentUrlById($main_slide->attachment_id, 'media');
                    $banner['image_link'] = !empty($main_slide->link) ? $main_slide->link : 'javascript:void(0)';
                    $banner['bg_color'] = !empty($main_slide->bg_color) ? $main_slide->bg_color : 'javascript:void(0)';

                    $bannerArray[] = $banner;

                    $i++;
                }
                return response()->json([
                    'status' => true,
                    'message' => "All Banners",
                    'Banners' => $bannerArray,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You dont have any Banner",

                ], 401);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public function getStatsById($id)
    {

        try {
            $results = StatesList::where('state_id', $id)->get()->first()->toArray();
            if (!empty($results)) {
                return response()->json([
                    'status' => true,
                    'message' => "You have successfully obtained the states.",
                    'StatesData' => $results,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You dont have any states",

                ], 401);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public function getAllStats()
    {

        try {
            $results = StatesList::where('country_id', 1)->get()->toArray();
            if (!empty($results)) {
                return response()->json([
                    'status' => true,
                    'message' => "You have successfully obtained all states.",
                    'StatesData' => $results,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You dont have any states",

                ], 401);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public function getCountryById($id)
    {

        try {
            $results = Countries::where('id', $id)->get()->first()->toArray();
            if (!empty($results)) {
                return response()->json([
                    'status' => true,
                    'message' => "You have successfully obtained the country.",
                    'CountryData' => $results,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You dont have any country",

                ], 401);
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    public function fileSend()
    {
        return view('API/file');
    }

    public function getAllCountries()
    {
        try {
            $results = Countries::all()->toArray();
            if (!empty($results)) {
                return response()->json([
                    'status' => true,
                    'message' => "You have successfully obtained all countries.",
                    'CountryData' => $results,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "No countries found.",
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "An error occurred while retrieving the countries.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function getStatesByCountryId($countryId)
    {
        try {
            $results = StatesList::where('country_id', $countryId)->get()->toArray();
            
            if (!empty($results)) {
                return response()->json([
                    'status' => true,
                    'message' => "You have successfully obtained all states for country ID: $countryId.",
                    'StatesData' => $results,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "No states found for the specified country.",
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "An error occurred while retrieving the states.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }

}
