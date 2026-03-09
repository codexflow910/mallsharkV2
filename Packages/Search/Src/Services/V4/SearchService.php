<?php

namespace Packages\Search\Services\V4;

use Packages\Search\Contracts\V4\SearchHelpers;
use Illuminate\Support\ItemNotFoundException;
use Packages\Search\Repositories\V4\SearchRepository;
use Algolia\AlgoliaSearch\SearchClient;
use Packages\Klavio\Services\V4\KlavioService;
use Packages\Home\Models\V4\Cookies;
use Illuminate\Support\Facades\Cache;
use Packages\Search\Models\V4\RecommendedProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use App\Models\Attachments;
use Illuminate\Support\Collection;
use Packages\Product\Models\V4\{Product,ProductTags,ProductAiTags};
use Packages\Search\Config\SearchConstants;
class SearchService

{
    use SearchHelpers;
    protected $searchRepository;
    protected $KlavioService;

    public function __construct(SearchRepository $searchRepository,KlavioService $KlavioService)
    
        
    {
        $this->searchRepository = $searchRepository;
        $this->KlavioService = $KlavioService;
    }

    public function suggestion(array $data)
    {
        try {
            $query = $data['title'];
            $client = SearchClient::create(
                env('ALGOLIA_APP_ID'),
                env('ALGOLIA_SECRET')
            );

            // Single multi-query request to search both indexes simultaneously
            $results = $client->multipleQueries([
                [
                    'indexName' => env('LOCAL_PRODUCTS_QUERY_SUGGESTION'),
                    'query' => $query,
                    'params' => [
                'hitsPerPage' => 50,
                'attributesToRetrieve' => ['query', 'products.categories'],
                'highlightPreTag' => '<em>',
                'highlightPostTag' => '</em>',
                    ],
                ],
                [
                    'indexName' => env('STORE_INDEX', 'stores'),
                    'query' => $query,
                    'params' => [
                        'hitsPerPage' => 5,
                        'attributesToRetrieve' => ['store_id', 'store_name', 'store_slug', 'store_logo'],
                    ],
                ],
            ]);

            $suggestionHits = $results['results'][0]['hits'] ?? [];
            $storeHits = $results['results'][1]['hits'] ?? [];

            $suggestions = collect($suggestionHits)->map(function ($hit) {
                return [
                    'query' => $hit['query'],
                    'categories' => $hit['products']['facets']['exact_matches'] ?? [],
                    'exact_matches' => $hit['products']['exact_nb_hits'] ?? 0,
                ];
            });

            $stores = collect($storeHits)->map(function ($hit) {
                    return [
                        'store_id' => $hit['store_id'],
                        'store_name' => $hit['store_name'],
                        'store_slug' => $hit['store_slug'],
                        'store_logo' => $hit['store_logo'] ?? null,
                    ];
                });

            return [
                'suggestions' => $suggestions,
                'stores' => $stores,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function searchResults(array $data)
    {
        try
        {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name'; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc'; // Default order ascending
            $tag = isset($data['tag']) && $data['tag'] !== null ? $data['tag'] : null; // Optional product name search term



            $userId = $data['user_id'] ?? null;
            $email = null;
            if (!empty($userId)) {
                $email = $this->searchRepository->getUserEmailById($userId);
            }

            if ($query && $email) {
                $this->KlavioService->trackSearchEvent($email, $query);
            }


           
            $indexName = env('PRODUCT_INDEX'); // Default primary index
            if (!is_null($sortBy)) {
                if ($sortBy === 'price' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCTS_PRICE_ASC');
                } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCTS_PRICE_DESC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCT_CREATED_AT_ASC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCT_CREATED_AT_DESC');
                }
            }
            $products = collect();
            $results = [];

            if ($tag) {
                $data['name'] = $tag;
                $data['restrict_search_to_tags'] = true;

                $results = $this->searchRepository->searchAlgolia($data, $indexName);
                $products = collect($results['hits']);
            }

            if ($products->isEmpty() && ($query || $tag)) {
                $data['name'] = $query ?? $tag;
                $data['restrict_search_to_tags'] = false;

                $results = $this->searchRepository->searchAlgolia($data, $indexName);
                $products = collect($results['hits']);
            }

            if ($products->isEmpty() && !$query && !$tag) {
                unset($data['name']);
                unset($data['restrict_search_to_tags']);

                $results = $this->searchRepository->searchAlgolia($data, $indexName);
                $products = collect($results['hits']);
            }                  
            $productIds = $products->pluck('id')->toArray();
           // Fetch categories and tags from the database for these products
            $categories = DB::table('categories')
                ->join('product_category', 'categories.id', '=', 'product_category.category_id')
                ->whereIn('product_category.product_id', $productIds)
                ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
                ->get()
                ->groupBy('product_id');
            // Modify each product to include the additional data
            $modifiedProducts = $products->map(function ($product, $index) use ($categories, $results) {
                // Get categories from the grouped data
                $categoryList = $categories[$product['id']] ?? collect();
                $categoriesData = [];

                foreach ($categoryList as $category) {
                    if ($category->parentCategoryID == 0) {
                        $categoriesData[] = [
                            'parent' => [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID,
                            ],
                            'childCategories' => []
                        ];
                    } else {
                        foreach ($categoriesData as &$parentCategory) {
                            if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                                $parentCategory['childCategories'][] = [
                                    'id' => $category->id,
                                    'categoryTitle' => $category->categoryTitle,
                                    'category_slug' => $category->category_slug,
                                    'parentCategoryID' => $category->parentCategoryID
                                ];
                                break;
                            }
                        }
                    }
                }

                return [
                    'product_id' => $product['id'],
                    'objectID' => $product['objectID'] ?? null,
                    'tags' => $product['tags'],
                    'title' => $product['name'],
                    'store_id' => $product['storeId'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'description' => $product['description'],
                    'current_stock' => $product['current_stock'],
                    'status' => $product['status'],
                    'product_type' => $product['product_type'],
                    'on_sale' => $product['on_sale'],
                    'mallshark_selected' => $product['mallshark_selected'],
                    'sale_price' => $product['sale_price'],
                    'shipping_option_id' => $product['shipping_option_id'],
                    'discount_percentage' => $product['discount_percentage'],
                    'feature_image' => $product['feature_image'] ?? null,
                    'feature_image_alt' => $product['feature_image_alt'] ?? null,
                    'categories' => array_values($categoriesData), // Attach categories
                    'created_at' => $product['created_at'],
                    'query_id' => $results['queryID'],
                    'labels' => $product['labels'] ?? null,
                ];
            });

            // Replace the original product collection with the modified one
            $products = $modifiedProducts;
            $lastPage = $results['nbPages'];
            return [
                'products'  =>$products,
                'last_page' =>$lastPage,
                'count'     =>$results['nbHits'],
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }
    //version 3 functions for website
    public function searchResultsV3(array $data)
    {
        try
        {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'created_at'; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'desc'; // Default order ascending
            $tag = isset($data['tag']) && $data['tag'] !== null ? $data['tag'] : null; // Optional product name search term
            $brandId = isset($data['brand_id']) && $data['brand_id'] !== null ? $data['brand_id'] : null; // Optional brand ID filter



            $userId = $data['user_id'] ?? null;
            $email = null;
            if (!empty($userId)) {
                $email = $this->searchRepository->getUserEmailById($userId);
            }

            if ($query && $email) {
                $this->KlavioService->trackSearchEvent($email, $query);
            }


           
            $indexName = env('PRODUCT_INDEX'); // Default primary index
            if (!is_null($sortBy)) {
                if ($sortBy === 'price' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCTS_PRICE_ASC');
                } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCTS_PRICE_DESC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCT_CREATED_AT_ASC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCT_CREATED_AT_DESC');
                }
            }
            $products = collect();
            $results = [];

            if ($tag) {
                $data['name'] = $tag;
                $data['restrict_search_to_tags'] = true;
                $results = $this->searchRepository->searchAlgoliaV3($data, $indexName);
                $products = collect($results['hits']);
            }

            if ($products->isEmpty() && ($query || $tag)) {
                $data['name'] = $query ?? $tag;
                $data['restrict_search_to_tags'] = false;

                $results = $this->searchRepository->searchAlgoliaV3($data, $indexName);
                $products = collect($results['hits']);
            }

            if ($products->isEmpty() && !$query && !$tag) {
                unset($data['name']);
                unset($data['restrict_search_to_tags']);

                $results = $this->searchRepository->searchAlgoliaV3($data, $indexName);
                $products = collect($results['hits']);
            }                  
            $productIds = $products->pluck('id')->toArray();
           // Fetch categories and tags from the database for these products
            $categories = DB::table('categories')
                ->join('product_category', 'categories.id', '=', 'product_category.category_id')
                ->whereIn('product_category.product_id', $productIds)
                ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
                ->get()
                ->groupBy('product_id');

            $brands = DB::table('brands')
                    ->join('products', 'brands.id', '=', 'products.brand_id')
                    ->whereIn('products.product_id', $productIds)
                    ->select('brands.id', 'brands.name', 'brands.logo', 'products.product_id')
                    ->get()
                    ->groupBy('product_id')
                    ->map(function ($items) {
                        return $items->map(function ($item) {
                            $item->logo = SearchHelpers::getDistributorLogo($item->logo, 'distributors-documents');
                            return $item;
                        });
                    });
                    $selectedBrand = null;
                    if($brandId){
                        // Extract selected brand details from the first product that has it
                        foreach ($products as $product) {
                            $productBrands = $brands[$product['id']] ?? collect();
                            $brand = $productBrands->firstWhere('id', $brandId);
                            if ($brand) {
                                $selectedBrand = [
                                    'id' => $brand->id,
                                    'name' => $brand->name,
                                    'logo' => $brand->logo ?? null,
                                ];
                                break;
                            }
                        }
                    }
            // Modify each product to include the additional data
            $modifiedProducts = $products->map(function ($product, $index) use ($categories, $results) {
                // Get categories from the grouped data
                $categoryList = $categories[$product['id']] ?? collect();
                $categoriesData = [];

                foreach ($categoryList as $category) {
                    if ($category->parentCategoryID == 0) {
                        $categoriesData[] = [
                            'parent' => [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID,
                            ],
                            'childCategories' => []
                        ];
                    } else {
                        foreach ($categoriesData as &$parentCategory) {
                            if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                                $parentCategory['childCategories'][] = [
                                    'id' => $category->id,
                                    'categoryTitle' => $category->categoryTitle,
                                    'category_slug' => $category->category_slug,
                                    'parentCategoryID' => $category->parentCategoryID
                                ];
                                break;
                            }
                        }
                    }
                }

                return [
                    'product_id' => $product['id'],
                    'objectID' => $product['objectID'] ?? null,
                    'tags' => $product['tags'],
                    'title' => $product['name'],
                    'store_id' => $product['storeId'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'description' => $product['description'],
                    'current_stock' => $product['current_stock'],
                    'status' => $product['status'],
                    'is_custom' => $product['is_custom'] ?? 0,
                    'product_type' => $product['product_type'],
                    'on_sale' => $product['on_sale'],
                    'mallshark_selected' => $product['mallshark_selected'],
                    'sale_price' => $product['sale_price'],
                    'shipping_option_id' => $product['shipping_option_id'],
                    'discount_percentage' => $product['discount_percentage'],
                    'feature_image' => $product['feature_image'] ?? null,
                    'feature_image_alt' => $product['feature_image_alt'] ?? null,
                    'categories' => array_values($categoriesData), // Attach categories
                    'created_at' => $product['created_at'],
                    'query_id' => $results['queryID'],
                    'labels' => $product['labels'] ?? null,
                ];
            });

            // Replace the original product collection with the modified one
            $products = $modifiedProducts;
            $lastPage = $results['nbPages'];
            return [
                'products'  =>$products,
                'brands'    => $brands->flatten()->unique('id')->values(),
                'selected_brand' => $selectedBrand,
                'last_page' =>$lastPage,
                'count'     =>$results['nbHits'],
                
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function searchByCategories(array $data)
    {
        try
        {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null;
            $categories = isset($data['category']) && $data['category'] !== null ? $data['category'] : [];
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null;
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null;
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20;
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1;
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'created_at';
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'desc';
            $tag = isset($data['tag']) && $data['tag'] !== null ? $data['tag'] : null;
            $brandId = isset($data['brand_id']) && $data['brand_id'] !== null ? $data['brand_id'] : null;

            $userId = $data['user_id'] ?? null;
            $email = null;
            if (!empty($userId)) {
                $email = $this->searchRepository->getUserEmailById($userId);
            }

            if ($query && $email) {
                $this->KlavioService->trackSearchEvent($email, $query);
            }

            $indexName = env('PRODUCT_INDEX');
            if (!is_null($sortBy)) {
                if ($sortBy === 'price' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCTS_PRICE_ASC');
                } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCTS_PRICE_DESC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCT_CREATED_AT_ASC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCT_CREATED_AT_DESC');
                }
            }

            $products = collect();
            $results = [];

            if ($tag) {
                $data['name'] = $tag;
                $data['restrict_search_to_tags'] = true;

                $results = $this->searchRepository->searchByCategories($data, $indexName);
                $products = collect($results['hits']);
            }

            if ($products->isEmpty() && ($query || $tag)) {
                $data['name'] = $query ?? $tag;
                $data['restrict_search_to_tags'] = false;

                $results = $this->searchRepository->searchByCategories($data, $indexName);
                $products = collect($results['hits']);
            }

            if ($products->isEmpty() && !$query && !$tag) {
                unset($data['name']);
                unset($data['restrict_search_to_tags']);

                $results = $this->searchRepository->searchByCategories($data, $indexName);
                $products = collect($results['hits']);
            }

            $productIds = $products->pluck('id')->toArray();

            // Fetch categories from the database for these products
            $categories = DB::table('categories')
                ->join('product_category', 'categories.id', '=', 'product_category.category_id')
                ->whereIn('product_category.product_id', $productIds)
                ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
                ->get()
                ->groupBy('product_id');

            // Fetch brands from the database for these products
            $brands = DB::table('brands')
                ->join('products', 'brands.id', '=', 'products.brand_id')
                ->whereIn('products.product_id', $productIds)
                ->select('brands.id', 'brands.name', 'brands.logo', 'products.product_id')
                ->get()
                ->groupBy('product_id')
                ->map(function ($items) {
                    return $items->map(function ($item) {
                        $item->logo = SearchHelpers::getDistributorLogo($item->logo, 'distributors-documents');
                        return $item;
                    });
                });

            $selectedBrand = null;
            if ($brandId) {
                foreach ($products as $product) {
                    $productBrands = $brands[$product['id']] ?? collect();
                    $brand = $productBrands->firstWhere('id', $brandId);
                    if ($brand) {
                        $selectedBrand = [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'logo' => $brand->logo ?? null,
                        ];
                        break;
                    }
                }
            }

            // Modify each product to include the additional data
            $modifiedProducts = $products->map(function ($product, $index) use ($categories, $results) {
                $categoryList = $categories[$product['id']] ?? collect();
                $categoriesData = [];

                foreach ($categoryList as $category) {
                    if ($category->parentCategoryID == 0) {
                        $categoriesData[] = [
                            'parent' => [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID,
                            ],
                            'childCategories' => []
                        ];
                    } else {
                        foreach ($categoriesData as &$parentCategory) {
                            if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                                $parentCategory['childCategories'][] = [
                                    'id' => $category->id,
                                    'categoryTitle' => $category->categoryTitle,
                                    'category_slug' => $category->category_slug,
                                    'parentCategoryID' => $category->parentCategoryID
                                ];
                                break;
                            }
                        }
                    }
                }

                return [
                    'product_id' => $product['id'],
                    'objectID' => $product['objectID'] ?? null,
                    'tags' => $product['tags'],
                    'title' => $product['name'],
                    'store_id' => $product['storeId'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'description' => $product['description'],
                    'current_stock' => $product['current_stock'],
                    'status' => $product['status'],
                    'is_custom' => $product['is_custom'] ?? 0,
                    'product_type' => $product['product_type'],
                    'on_sale' => $product['on_sale'],
                    'mallshark_selected' => $product['mallshark_selected'],
                    'sale_price' => $product['sale_price'],
                    'shipping_option_id' => $product['shipping_option_id'],
                    'discount_percentage' => $product['discount_percentage'],
                    'feature_image' => $product['feature_image'] ?? null,
                    'feature_image_alt' => $product['feature_image_alt'] ?? null,
                    'categories' => array_values($categoriesData),
                    'created_at' => $product['created_at'],
                    'query_id' => $results['queryID'],
                    'labels' => $product['labels'] ?? null,
                ];
            });

            $products = $modifiedProducts;
            $lastPage = $results['nbPages'];

            return [
                'products' => $products,
                'brands' => $brands->flatten()->unique('id')->values(),
                'selected_brand' => $selectedBrand,
                'last_page' => $lastPage,
                'count' => $results['nbHits'],
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function bestSellers($data)
    {
        try {

            $results = $this->searchRepository->bestSellers($data);
            $products = collect($results['hits']); 
        $productIds = $products->pluck('id')->toArray();
        // Fetch categories and tags from the database for these products
        $categories = DB::table('categories')
            ->join('product_category', 'categories.id', '=', 'product_category.category_id')
            ->whereIn('product_category.product_id', $productIds)
            ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
            ->get()
            ->groupBy('product_id');
        // Modify each product to include the additional data
        $modifiedProducts = $products->map(function ($product, $index) use ($categories,$results) {
            // Get categories from the grouped data
            $categoryList = $categories[$product['id']] ?? collect();
            $categoriesData = [];

            foreach ($categoryList as $category) {
                if ($category->parentCategoryID == 0) {
                    $categoriesData[] = [
                        'parent' => [
                            'id' => $category->id,
                            'categoryTitle' => $category->categoryTitle,
                            'category_slug' => $category->category_slug,
                            'parentCategoryID' => $category->parentCategoryID,
                        ],
                        'childCategories' => []
                    ];
                } else {
                    foreach ($categoriesData as &$parentCategory) {
                        if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                            $parentCategory['childCategories'][] = [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID
                            ];
                            break;
                        }
                    }
                }
            }

            return [
                    'product_id' => $product['id'],
                    'objectID' => $product['objectID'] ?? null,
                    'title' => $product['name'],
                    'store_id' => $product['storeId'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'description' => $product['description'],
                    'current_stock' => $product['current_stock'],
                    'status' => $product['status'],
                    'is_custom' => $product['is_custom'] ?? 0,
                    'product_type' => $product['product_type'],
                    'on_sale' => $product['on_sale'],
                    'mallshark_selected' => $product['mallshark_selected'],
                    'sale_price' => $product['sale_price'],
                    'shipping_option_id' => $product['shipping_option_id'],
                    'discount_percentage' => $product['discount_percentage'],
                    'feature_image' => $product['feature_image'],
                    'feature_image_alt' => $product['feature_image_alt'] ?? null,
                    'categories' => array_values($categoriesData), // Attach categories
                    'created_at' => $product['created_at'],
                    'query_id' => $results['queryID'],
                    'labels' => $product['labels'] ?? null
            ];
        });

            // Replace the original product collection with the modified one
            $products = $modifiedProducts;
            $lastPage = $results['nbPages'];
            return ['products'=>$products,'last_page'=>$lastPage,'count'=>$results['nbHits']];
        } catch (\Exception $e) {
            throw $e;
        }
    }
    //version 3 functions for website
    public function bestSellersV3($data)
    {
        try {
            $brandId = isset($data['brand_id']) && $data['brand_id'] !== null ? $data['brand_id'] : null; // Optional brand ID filter
            $results = $this->searchRepository->bestSellersV3($data);
            $products = collect($results['hits']); 
        $productIds = $products->pluck('id')->toArray();
        // Fetch categories and tags from the database for these products
        $categories = DB::table('categories')
            ->join('product_category', 'categories.id', '=', 'product_category.category_id')
            ->whereIn('product_category.product_id', $productIds)
            ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
            ->get()
            ->groupBy('product_id');
        
        $brands = DB::table('brands')
            ->join('products', 'brands.id', '=', 'products.brand_id')
            ->whereIn('products.product_id', $productIds)
            ->select('brands.id', 'brands.name', 'brands.logo', 'products.product_id')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    $item->logo = SearchHelpers::getDistributorLogo($item->logo, 'distributors-documents');
                    return $item;
                });
            });
            $selectedBrand = null;
            if($brandId){
                // Extract selected brand details from the first product that has it
                foreach ($products as $product) {
                    $productBrands = $brands[$product['id']] ?? collect();
                    $brand = $productBrands->firstWhere('id', $brandId);
                    if ($brand) {
                        $selectedBrand = [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'logo' => $brand->logo ?? null,
                        ];
                        break;
                    }
                }
            }
        // Modify each product to include the additional data
        $modifiedProducts = $products->map(function ($product, $index) use ($categories,$results) {
            // Get categories from the grouped data
            $categoryList = $categories[$product['id']] ?? collect();
            $categoriesData = [];

            foreach ($categoryList as $category) {
                if ($category->parentCategoryID == 0) {
                    $categoriesData[] = [
                        'parent' => [
                            'id' => $category->id,
                            'categoryTitle' => $category->categoryTitle,
                            'category_slug' => $category->category_slug,
                            'parentCategoryID' => $category->parentCategoryID,
                        ],
                        'childCategories' => []
                    ];
                } else {
                    foreach ($categoriesData as &$parentCategory) {
                        if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                            $parentCategory['childCategories'][] = [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID
                            ];
                            break;
                        }
                    }
                }
            }

            return [
                    'product_id' => $product['id'],
                    'objectID' => $product['objectID'] ?? null,
                    'title' => $product['name'],
                    'store_id' => $product['storeId'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'description' => $product['description'],
                    'current_stock' => $product['current_stock'],
                    'status' => $product['status'],
                    'is_custom' => $product['is_custom'] ?? 0,
                    'product_type' => $product['product_type'],
                    'on_sale' => $product['on_sale'],
                    'mallshark_selected' => $product['mallshark_selected'],
                    'sale_price' => $product['sale_price'],
                    'shipping_option_id' => $product['shipping_option_id'],
                    'discount_percentage' => $product['discount_percentage'],
                    'feature_image' => $product['feature_image'],
                    'feature_image_alt' => $product['feature_image_alt'] ?? null,
                    'categories' => array_values($categoriesData), // Attach categories
                    'created_at' => $product['created_at'],
                    'query_id' => $results['queryID'],
                    'labels' => $product['labels'] ?? null
            ];
        });

            // Replace the original product collection with the modified one
            $products = $modifiedProducts;
            $lastPage = $results['nbPages'];
            return ['products'=>$products,'brands'=> $brands->flatten()->unique('id')->values(),'selected_brand' => $selectedBrand,'last_page'=>$lastPage,'count'=>$results['nbHits']];
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function getRelatedProducts($id)
    {
        try {
            $response = $this->searchRepository->relatedProducts($id);
            $relatedProducts = collect($response['data'])->map(function ($item) use ($response) {
                $categoriesData = [];
                $product = Product::findOrFail($item['id']);
                if (isset($product->feature_image)) {
                    $path = getAttachmentUrlByIdApi($product->feature_image, 'medium');
                    $webpUrl = $this->webPImages($path, 'medium');
                    if ($webpUrl) {
                        $product->feature_image = $webpUrl;
                    }
                }
                foreach ($product->categories as $category) {
                    // Check if the category is a parent (parentCategoryID is 0)
                    if ($category->parentCategoryID == 0) {
                        // Parent category: Add it to the categories array
                        $categoriesData[] = [
                            'parent' => [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID,
                            ],
                            'childCategories' => [] // Initialize empty array for child categories
                        ];
                    } else {
                        // Child category: Add to the childCategories of the corresponding parent
                        foreach ($categoriesData as &$parentCategory) {
                            if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                                $parentCategory['childCategories'][] = [
                                    'id' => $category->id,
                                    'categoryTitle' => $category->categoryTitle,
                                    'category_slug' => $category->category_slug,
                                    'parentCategoryID' => $category->parentCategoryID
                                ];
                                break;
                            }
                        }
                    }
                }
                $product->categories = array_values($categoriesData);
                return [

                    "product_id" => $item['id'],
                    "objectID" => $item['objectID'],
                    "title" => $item['name'] ?? null,
                    'store_id' => $item['storeId'],
                    "user_id" => $product->user_id ?? null,
                    "slug" => $product->slug, // Generating slug if missing
                    "price" => $product->price ?? 0,
                    "tags" => $product->tags,
                    'description' => $product->description,
                    'current_stock' => $product->current_stock,
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'product_type' => $product->product_type,
                    'on_sale' => $product->on_sale,
                    'sale_price' => $product->sale_price,
                    'discount_percentage' => $product->discount_percentage,
                    'feature_image' => $item['feature_image'], // Ensure image exists
                    'feature_image_alt' => $item['feature_image_alt'] ?? null,
                    'categories' => $product->categories,
                    'created_at' => $product->created_at,
                    "query_id" => $response['queryId'], // Generating a random query ID
                    "mallshark_selected" => $product->mallshark_selected,
                    'labels' => $product->labels ?? null
                ];
            });
            return $relatedProducts;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function deals(array $data)
    {
        try{
         $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
            // $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name'; // Default sort by name
            // $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc'; // Default order ascending
            $sortBy = 'created_at';
            $sortOrder = 'desc';

            $indexName = env('PRODUCT_INDEX'); // Default primary index
            if (!is_null($sortBy)) {
                if ($sortBy === 'price' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCTS_PRICE_ASC');
                } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCTS_PRICE_DESC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCT_CREATED_AT_ASC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCT_CREATED_AT_DESC');
                }
            }
            // Perform the search using the combined term
            $results = $this->searchRepository->deals($data,$indexName);
            // Modify each product to include only the fields we need
            $products = collect($results['hits']); 
        $productIds = $products->pluck('id')->toArray();
        // Fetch categories and tags from the database for these products
        $categories = DB::table('categories')
            ->join('product_category', 'categories.id', '=', 'product_category.category_id')
            ->whereIn('product_category.product_id', $productIds)
            ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
            ->get()
            ->groupBy('product_id');
        // Modify each product to include the additional data
        $modifiedProducts = $products->map(function ($product, $index) use ($categories,$results) {

            // Get categories from the grouped data
            $categoryList = $categories[$product['id']] ?? collect();
            $categoriesData = [];

            foreach ($categoryList as $category) {
                if ($category->parentCategoryID == 0) {
                    $categoriesData[] = [
                        'parent' => [
                            'id' => $category->id,
                            'categoryTitle' => $category->categoryTitle,
                            'category_slug' => $category->category_slug,
                            'parentCategoryID' => $category->parentCategoryID,
                        ],
                        'childCategories' => []
                    ];
                } else {
                    foreach ($categoriesData as &$parentCategory) {
                        if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                            $parentCategory['childCategories'][] = [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID
                            ];
                            break;
                        }
                    }
                }
            }

            return [
                'product_id' => $product['id'],
                'objectID' => $product['objectID'] ?? null,
                'title' => $product['name'],
                'store_id' => $product['storeId'],
                'slug' => $product['slug'],
                'price' => $product['price'],
                'description' => $product['description'],
                'current_stock' => $product['current_stock'],
                'status' => $product['status'],
                'product_type' => $product['product_type'],
                'on_sale' => $product['on_sale'],
                'mallshark_selected' => $product['mallshark_selected'],
                'sale_price' => $product['sale_price'],
                'shipping_option_id' => $product['shipping_option_id'],
                'discount_percentage' => $product['discount_percentage'],
                'feature_image' => $product['feature_image'],
                'feature_image_alt' => $product['feature_image_alt'] ?? null,
                'categories' => array_values($categoriesData), // Attach categories
                'created_at' => $product['created_at'],
                'query_id' => $results['queryID'],
                'labels' => $product['labels'] ?? null
            ];
        });

        // Replace the original product collection with the modified one
        $products = $modifiedProducts;
        $lastPage = $results['nbPages'];
        return ['products'=>$products,'last_page'=>$lastPage,'count'=>$results['nbHits']];
            // Return the response with only the required fields
            
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    //version 3 functions for website
    public function dealsV3(array $data)
    {
        try{
         $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'created_at'; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'desc'; // Default order ascending
            $brandId = isset($data['brand_id']) && $data['brand_id'] !== null ? $data['brand_id'] : null; // Optional brand ID filter
            $sortBy = 'created_at';
            $sortOrder = 'desc';
 
            $indexName = env('PRODUCT_INDEX'); // Default primary index
            if (!is_null($sortBy)) {
                if ($sortBy === 'price' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCTS_PRICE_ASC');
                } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCTS_PRICE_DESC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCT_CREATED_AT_ASC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCT_CREATED_AT_DESC');
                }
            }
            // Perform the search using the combined term
            $results = $this->searchRepository->dealsV3($data,$indexName);
            // Modify each product to include only the fields we need
            $products = collect($results['hits']);
        $productIds = $products->pluck('id')->toArray();
        // Fetch featured products from database (max 10)
        $activeStatus = SearchConstants::STATUS_APPROVED;
        $featuredProducts = DB::table('products')
        ->where('status',$activeStatus)
        ->where('feature_status', 1)
        ->orderBy('updated_at', 'desc')->get();
 
        // Get featured product IDs to merge with Algolia products
        $featuredProductIds = $featuredProducts->pluck('product_id')->toArray();
 
        // Remove featured products from Algolia results to avoid duplicates
        $products = $products->filter(function ($product) use ($featuredProductIds) {
            return !in_array($product['id'], $featuredProductIds);
        });
 
        // Merge all product IDs (featured + algolia) for category and brand fetching
        $allProductIds = array_merge($featuredProductIds, $products->pluck('id')->toArray());
 
        // Update categories query to include featured products
        $categories = DB::table('categories')
            ->join('product_category', 'categories.id', '=', 'product_category.category_id')
            ->whereIn('product_category.product_id', $allProductIds)
            ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
            ->get()
            ->groupBy('product_id');
 
        $brands = DB::table('brands')
            ->join('products', 'brands.id', '=', 'products.brand_id')
            ->whereIn('products.product_id', $allProductIds)
            ->select('brands.id', 'brands.name', 'brands.logo', 'products.product_id')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    $item->logo = SearchHelpers::getDistributorLogo($item->logo, 'distributors-documents');
                    return $item;
                });
            });
            $selectedBrand = null;
            if($brandId){
                // Extract selected brand details from the first product that has it
                foreach ($products as $product) {
                    $productBrands = $brands[$product['id']] ?? collect();
                    $brand = $productBrands->firstWhere('id', $brandId);
                    if ($brand) {
                        $selectedBrand = [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'logo' => $brand->logo ?? null,
                        ];
                        break;
                    }
                }
            }
 
        // Map featured products to the same format
        $modifiedFeaturedProducts = $featuredProducts->map(function ($product) use ($categories, $results) {
            // Get categories from the grouped data
            $path = getAttachmentUrlByIdApi($product->feature_image, 'medium');
            // dd($path);
            if ($path) {
                $webpUrl = webPImages($path, 'medium');
                if ($webpUrl) {
                    $product->feature_image = $webpUrl;
                }
            }
            $categoryList = $categories[$product->product_id] ?? collect();
            $categoriesData = [];
 
            foreach ($categoryList as $category) {
                if ($category->parentCategoryID == 0) {
                    $categoriesData[] = [
                        'parent' => [
                            'id' => $category->id,
                            'categoryTitle' => $category->categoryTitle,
                            'category_slug' => $category->category_slug,
                            'parentCategoryID' => $category->parentCategoryID,
                        ],
                        'childCategories' => []
                    ];
                } else {
                    foreach ($categoriesData as &$parentCategory) {
                        if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                            $parentCategory['childCategories'][] = [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID
                            ];
                            break;
                        }
                    }
                }
            }
 
            return [
                'product_id' => $product->product_id,
                'objectID' => null,
                'title' => $product->title,
                'store_id' => $product->store_id,
                'slug' => $product->slug,
                'price' => $product->price,
                'description' => $product->description,
                'current_stock' => $product->current_stock,
                'status' => $product->status,
                'is_custom' => $product->is_custom,
                'product_type' => $product->product_type,
                'on_sale' => $product->on_sale,
                'mallshark_selected' => $product->mallshark_selected,
                'sale_price' => $product->sale_price,
                'shipping_option_id' => $product->shipping_option_id,
                'discount_percentage' => $product->discount_percentage,
                'feature_image' => $product->feature_image,
                'feature_image_alt' => null,
                'categories' => array_values($categoriesData), // Attach categories
                'created_at' => $product->created_at,
                'query_id' => $results['queryID'] ?? null,
                'labels' => null,
                'is_featured' => true
            ];
        });
 
        // Modify Algolia products to include the additional data
        $modifiedProducts = $products->map(function ($product, $index) use ($categories,$results) {
 
            // Get categories from the grouped data
            $categoryList = $categories[$product['id']] ?? collect();
            $categoriesData = [];
 
            foreach ($categoryList as $category) {
                if ($category->parentCategoryID == 0) {
                    $categoriesData[] = [
                        'parent' => [
                            'id' => $category->id,
                            'categoryTitle' => $category->categoryTitle,
                            'category_slug' => $category->category_slug,
                            'parentCategoryID' => $category->parentCategoryID,
                        ],
                        'childCategories' => []
                    ];
                } else {
                    foreach ($categoriesData as &$parentCategory) {
                        if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                            $parentCategory['childCategories'][] = [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID
                            ];
                            break;
                        }
                    }
                }
            }
 
            return [
                'product_id' => $product['id'],
                'objectID' => $product['objectID'] ?? null,
                'title' => $product['name'],
                'store_id' => $product['storeId'],
                'slug' => $product['slug'],
                'price' => $product['price'],
                'description' => $product['description'],
                'current_stock' => $product['current_stock'],
                'status' => $product['status'],
                'is_custom' => $product['is_custom'] ?? 0,
                'product_type' => $product['product_type'],
                'on_sale' => $product['on_sale'],
                'mallshark_selected' => $product['mallshark_selected'],
                'sale_price' => $product['sale_price'],
                'shipping_option_id' => $product['shipping_option_id'],
                'discount_percentage' => $product['discount_percentage'],
                'feature_image' => $product['feature_image'],
                'feature_image_alt' => $product['feature_image_alt'] ?? null,
                'categories' => array_values($categoriesData), // Attach categories
                'created_at' => $product['created_at'],
                'query_id' => $results['queryID'],
                'labels' => $product['labels'] ?? null,
                'is_featured' => false
            ];
        });
 
        // Merge featured products on top of Algolia products
        $modifiedProducts = $modifiedFeaturedProducts->concat($modifiedProducts);
 
        // Replace the original product collection with the modified one
        $products = $modifiedProducts;
        $lastPage = $results['nbPages'];
        return ['products'=>$products,'brands'=> $brands->flatten()->unique('id')->values(),'selected_brand' => $selectedBrand,'last_page'=>$lastPage,'count'=>$results['nbHits']];
            // Return the response with only the required fields
           
           
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function exploreMore(array $data)
    {
        try{
         $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'price'; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'desc'; // Default order ascending
            $indexName = env('PRODUCT_INDEX'); // Default primary index
            if (!is_null($sortBy)) {
                if ($sortBy === 'price' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCTS_PRICE_ASC');
                } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCTS_PRICE_DESC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCT_CREATED_AT_ASC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCT_CREATED_AT_DESC');
                }
            }
            // Perform the search using the combined term
            $results = $this->searchRepository->exploreMore($data,$indexName);
            // Modify each product to include only the fields we need
            $products = collect($results['hits']); 
        $productIds = $products->pluck('id')->toArray();
        // Fetch categories and tags from the database for these products
        $categories = DB::table('categories')
            ->join('product_category', 'categories.id', '=', 'product_category.category_id')
            ->whereIn('product_category.product_id', $productIds)
            ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
            ->get()
            ->groupBy('product_id');
        // Modify each product to include the additional data
        $modifiedProducts = $products->map(function ($product, $index) use ($categories,$results) {
            // Get categories from the grouped data
            $categoryList = $categories[$product['id']] ?? collect();
            $categoriesData = [];

            foreach ($categoryList as $category) {
                if ($category->parentCategoryID == 0) {
                    $categoriesData[] = [
                        'parent' => [
                            'id' => $category->id,
                            'categoryTitle' => $category->categoryTitle,
                            'category_slug' => $category->category_slug,
                            'parentCategoryID' => $category->parentCategoryID,
                        ],
                        'childCategories' => []
                    ];
                } else {
                    foreach ($categoriesData as &$parentCategory) {
                        if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                            $parentCategory['childCategories'][] = [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID
                            ];
                            break;
                        }
                    }
                }
            }

            return [
                'product_id' => $product['id'],
                'objectID' => $product['objectID'] ?? null,
                'title' => $product['name'],
                'store_id' => $product['storeId'],
                'slug' => $product['slug'],
                'price' => $product['price'],
                'description' => $product['description'],
                'current_stock' => $product['current_stock'],
                'status' => $product['status'],
                'product_type' => $product['product_type'],
                'on_sale' => $product['on_sale'],
                'mallshark_selected' => $product['mallshark_selected'],
                'sale_price' => $product['sale_price'],
                'shipping_option_id' => $product['shipping_option_id'],
                'discount_percentage' => $product['discount_percentage'],
                'feature_image' => $product['feature_image'],
                'categories' => array_values($categoriesData), // Attach categories
                'created_at' => $product['created_at'],
                'query_id' => $results['queryID'],
                'labels' => $product['labels'] ?? null
            ];
        });

        // Replace the original product collection with the modified one
        $products = $modifiedProducts;
            // dd($results);
            return ['products'=>$products,'last_page'=>$results['page']];
            // Return the response with only the required fields
            
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function getTrendingProducts(){
        try{
            $response = $this->searchRepository->trendingProducts();
            //   dd($response);
            $response = collect($response['data'])->map(function ($item) use ($response) {

                $categoriesData = [];
                $product = Product::find($item['id']);

                if ($product) {
                    if (isset($product->feature_image)) {
                        $path = getAttachmentUrlByIdApi($product->feature_image, 'medium');
                        $webpUrl = $this->webPImages($path, 'medium');
                        if ($webpUrl) {
                            $product->feature_image = $webpUrl;
                        }
                    }
                    foreach ($product->categories as $category) {
                        // Check if the category is a parent (parentCategoryID is 0)
                        if ($category->parentCategoryID == 0) {
                            // Parent category: Add it to the categories array
                            $categoriesData[] = [
                                'parent' => [
                                    'id' => $category->id,
                                    'categoryTitle' => $category->categoryTitle,
                                    'category_slug' => $category->category_slug,
                                    'parentCategoryID' => $category->parentCategoryID,
                                ],
                                'childCategories' => [] // Initialize empty array for child categories
                            ];
                        } else {
                            // Child category: Add to the childCategories of the corresponding parent
                            foreach ($categoriesData as &$parentCategory) {
                                if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                                    $parentCategory['childCategories'][] = [
                                        'id' => $category->id,
                                        'categoryTitle' => $category->categoryTitle,
                                        'category_slug' => $category->category_slug,
                                        'parentCategoryID' => $category->parentCategoryID
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                    $product->categories = array_values($categoriesData);
                    $labels = $product['labels'] ?? collect();
                    $labelSlugs = array_column($labels->toArray(), 'slug');

                    return [

                        "product_id" => $item['id'],
                        "objectID" => $item['objectID'],
                        "title" => $item['name'] ?? null,
                        'store_id' => $item['storeId'],
                        "user_id" => $product->user_id ?? null,
                        "slug" => $product->slug, // Generating slug if missing
                        "price" => $product->price ?? 0,
                        "tags" => $product->tags,
                        'description' => $product->description,
                        'current_stock' => $product->current_stock,
                        'sku' => $product->sku,
                        'status' => $product->status,
                        'is_custom' => $product->is_custom ?? 0,
                        'product_type' => $product->product_type,
                        'on_sale' => $product->on_sale,
                        'sale_price' => $product->sale_price,
                        'shipping_option_id' => $product->shipping_option_id,
                        'discount_percentage' => $product->discount_percentage,
                        'feature_image' => $item['feature_image'], // Ensure image exists
                        'feature_image_alt' => $item['feature_image_alt'] ?? null,
                        'categories' => $product->categories,
                        'created_at' => $product->created_at,
                        "query_id" => $response['queryId'], // Generating a random query ID
                        "mallshark_selected" => $product->mallshark_selected,
                        'labels' => $labelSlugs ? $labelSlugs : null
                    ];
                }
            });
            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getPopularTags()
    {
        try {
            $tags = $this->searchRepository->getAllTags();

            if (empty($tags)) {
                throw new ItemNotFoundException('No tags found.');
            }
            usort($tags, function ($a, $b) {
                return $b['counts'] <=> $a['counts']; // Sort descending by 'counts'
            });
            $topTags = array_slice($tags, 0, 10); // Get top 10 tags
            $tagNames = array_map(function ($tag) {
                return $tag['tag'];
            }, $topTags);
         
            return $tagNames;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function getUserSearchTags()
    {
        try {
            $userId = auth()->user()->user_id;

            if (!$userId) {
                throw new ItemNotFoundException("User not found.");
            }

            $tags = $this->searchRepository->getTagsByUserId($userId);

            if (empty($tags)) {
                throw new ItemNotFoundException("You do not have any tags.");
            }

            return $tags;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function getSeacrchProduct($data)
    {
        try {
            $products = $this->searchRepository->getSeacrchProduct($data);

            if (empty($products)) {
                throw new ItemNotFoundException('No tags found.');
            }
            return $products;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

   
    public function mostPopular($data)
    {
        try {
            $userId = $data['user_id'] ?? null;
            $recommendedFormatted = collect();
            if ($userId) {
                $recommendation = RecommendedProduct::where('user_id', $userId)->first();
                $recommended = $recommendation ? json_decode($recommendation->products, true) : [];

                if (!empty($recommended)) {
                    return [
                        'products' => $recommended,
                        'recommended_count' => $recommendedFormatted->count(),
                        'best_sellers_count' => 0,
                    ];
                }
            }
            $bestSellerResponse = $this->bestSellers($data);
            $bestSellersFormatted = collect($bestSellerResponse['products'])->map(function ($product) {
                $product['source'] = 'best_seller';
                return $product;
            })->shuffle(); 

            return [
                'products' => $bestSellersFormatted->values(),
                'recommended_count' => 0,
                'best_sellers_count' => $bestSellersFormatted->count(),
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function searchProductsFromImage(UploadedFile $image)
{
    $client = new \GuzzleHttp\Client();
    
    try {
        
        $response = $client->post('http://127.0.0.1:5000/tag', [
            'multipart' => [
                [
                    'name' => 'image',
                    'contents' => fopen($image->getRealPath(), 'r'),
                    'filename' => $image->getClientOriginalName()
                ]
            ],
            'timeout' => 30
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        return response()->json([
            'status' => true,
            'search_term' => $result['keyword']
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'error' => 'Image processing failed: ' . $e->getMessage()
        ], 500);
    }
}

    public function generateTagsForProduct($productId)
    {
        $product = Product::find($productId);
        $imageUrl = getAttachmentUrlById($product->feature_image);
        $relative = ltrim(str_replace(url('/'), '', $imageUrl), '/');
        $localPath = public_path('uploads/' . $relative); 
        $imageBase64 = base64_encode(file_get_contents($localPath));

        $clarifaiResponse = Http::withHeaders([
            'Authorization' => 'Key ' . env('CLARIFAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post(                    
            'https://api.clarifai.com/v2/models/general-image-detection/versions/1580bb1932594c93b7e2e04456af7c6f/outputs'
        , [
            'inputs' => [[
                'data' => [
                    'image' => ['base64' => $imageBase64]
                ]
            ]]
        ]);
        $responseData = $clarifaiResponse->json();
        $regions = $responseData['outputs'][0]['data']['regions'] ?? [];

        $scoredTags = collect($regions)->map(function ($region) {
            $concepts = $region['data']['concepts'] ?? [];
            $box = $region['region_info']['bounding_box'] ?? null;

            $area = 0;
            if ($box) {
                $width = $box['right_col'] - $box['left_col'];
                $height = $box['bottom_row'] - $box['top_row'];
                $area = $width * $height;
            }

            return collect($concepts)->map(function ($concept) use ($area) {
                return [
                    'name' => $concept['name'],
                    'value' => $concept['value'],
                    'score' => $concept['value'] * $area
                ];
            });
        })->flatten(1);

        $topConcept = $scoredTags
            ->sortByDesc('score')
            ->unique('name')
            ->first();
    

        if ($topConcept) {
            $data['name'] = $topConcept['name'];        
            $data['main_tag'] = $topConcept['name'];
        } else {
            $conceptList = $scoredTags->pluck('name')->unique()->take(5)->toArray();
            $data['name'] = implode(' ', $conceptList);
            $data['concepts'] = $conceptList;
        }

        if ($topConcept) {
            $tag = ProductAiTags::create([
                'product_id' => $productId,
                'tag_title' => $topConcept['name'],
            ]);
            $product = Product::find($productId);
            $product->searchable();

            return response()->json([
                'status' => true,
                'message' => 'Tag saved successfully',
                'tag' => $tag
            ], 200);

            
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No concept tags found from Clarifai.'
            ], 204); 
        }
        
    }

    public function generateTagsForAllProducts()
    {
        ini_set('max_execution_time', 18000);
        $products = Product::whereNotNull('feature_image')->get();
        $results = [];

        foreach ($products as $product) {
            try {

                $imageUrl = getAttachmentUrlById($product->feature_image);
                $relative = ltrim(str_replace(url('/'), '', $imageUrl), '/');
                $localPath = public_path( $relative); 

                if (!file_exists($localPath)) {
                    $results[] = [
                        'product_id' => $product->id,
                        'status' => 'error',
                        'message' => 'Image file not found'
                    ];
                    continue;
                }

                $imageBase64 = base64_encode(file_get_contents($localPath));

                $clarifaiResponse = Http::withHeaders([
                    'Authorization' => 'Key ' . env('CLARIFAI_API_KEY'),
                    'Content-Type' => 'application/json',
                ])->post(
                    'https://api.clarifai.com/v2/models/general-image-detection/versions/1580bb1932594c93b7e2e04456af7c6f/outputs',
                    [
                        'inputs' => [[
                            'data' => [
                                'image' => ['base64' => $imageBase64]
                            ]
                        ]]
                    ]
                );

                $responseData = $clarifaiResponse->json();
                $regions = $responseData['outputs'][0]['data']['regions'] ?? [];

                $scoredTags = collect($regions)->map(function ($region) {
                    $concepts = $region['data']['concepts'] ?? [];
                    $box = $region['region_info']['bounding_box'] ?? null;

                    $area = 0;
                    if ($box) {
                        $width = $box['right_col'] - $box['left_col'];
                        $height = $box['bottom_row'] - $box['top_row'];
                        $area = $width * $height;
                    }

                    return collect($concepts)->map(function ($concept) use ($area) {
                        return [
                            'name' => $concept['name'],
                            'value' => $concept['value'],
                            'score' => $concept['value'] * $area
                        ];
                    });
                })->flatten(1);

                $topConcept = $scoredTags
                    ->sortByDesc('score')
                    ->unique('name')
                    ->first();

                if ($topConcept) {

                ProductAiTags::create([
                        'product_id' => $product->product_id,
                        'tag_title' => $topConcept['name']
                    ]);
                    $product->searchable(); 

                    $results[] = [
                        'product_id' => $product->product_id,
                        'status' => 'success',
                        'tag_title' => $topConcept['name']
                    ];
                }
                else {
                    $results[] = [
                        'product_id' => $product->id,
                        'status' => 'warning',
                        'message' => 'No concept tags found from Clarifai.'
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'product_id' => $product->id ?? null,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
            usleep(300000);
        }
        return response()->json([
            'status' => true,
            'message' => 'Tag generation completed',
            'results' => $results
        ]);
    }
   function mostPapular($data)
    {
        try {
            // Extract filters
            
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name'; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc'; // Default order ascending
            $userId = $data['user_id'] ?? null;
            // Collect personalization data
            $tags = $keywords = $categories = [];

            if ($userId) {
                $cookies = Cookies::where('user_id', $userId)->get();
                foreach ($cookies as $cookie) {

                    $tags = array_merge($tags, array_filter(array_map('trim', explode(',', $cookie->tags ?? ''))));
                    if (!empty($cookie->keyword)) $keywords[] = trim($cookie->keyword);
                    if (!empty($cookie->category_name)) $categories[] = trim($cookie->category_name);
                }
            } else {
                if (!empty($data['tags'])) {
                    $tags = array_map('trim', is_array($data['tags']) ? $data['tags'] : explode(',', $data['tags']));
                }
                if (!empty($data['keywords'])) {
                    $keywords = array_map('trim', is_array($data['keywords']) ? $data['keywords'] : explode(',', $data['keywords']));
                }
                if (!empty($data['categories'])) {
                    $categories = array_map('trim', is_array($data['categories']) ? $data['categories'] : explode(',', $data['categories']));
                }
            }
            if (empty($userId) && empty($tags) && empty($keywords) && empty($categories)) {
                $bestSellerResponse = $this->bestSellers($data);
                return [
                    'best_sellers' => $bestSellerResponse['products'],
                    'recommended_count' => 0,
                    'best_sellers_count' => $bestSellerResponse['count'],
                ];
            }
            
        
            // Personalized flow continues here...
            $indexName = match (true) {
                $sortBy === 'price' && $sortOrder === 'asc' => env('PRODUCTS_PRICE_ASC'),
                $sortBy === 'price' && $sortOrder === 'desc' => env('PRODUCTS_PRICE_DESC'),
                $sortBy === 'created_at' && $sortOrder === 'asc' => env('PRODUCT_CREATED_AT_ASC'),
                $sortBy === 'created_at' && $sortOrder === 'desc' => env('PRODUCT_CREATED_AT_DESC'),
                default => env('PRODUCT_INDEX'),
            };

            $client = SearchClient::create(env('ALGOLIA_APP_ID'), env('ALGOLIA_SECRET'));
            $index = $client->initIndex($indexName);

            // Build Algolia filter string
            $filters = [];
            if (!empty($category)) {
                $filters[] = '(categories:"' . addslashes($category) . '" OR categories.0:"' . addslashes($category) . '")';
            }
            if (!empty($minPrice) && !empty($maxPrice)) {
                $filters[] = "effective_price >= $minPrice AND effective_price <= $maxPrice";
            }
            if (isset($data['on_sale'])) {
                $filters[] = "on_sale = " . (int)$data['on_sale'];
            }
            if (isset($data['mallshark_selected'])) {
                $filters[] = "mallshark_selected = " . (int)$data['mallshark_selected'];
            }
            if (isset($data['shipping_option_id'])) {
                $filters[] = $data['shipping_option_id'] == 1 ? "shipping_option_id = 221" : "shipping_option_id != 221";
            }
            $filterString = implode(' AND ', $filters);

            // Personalized results
            $recommended = collect();

            // Search from tags
            if (!empty($tags)) {
                $tagFilter = implode(' OR ', array_map(fn($tag) => 'tags:"' . addslashes($tag) . '"', $tags));
                $combinedFilter = $filterString ? "$tagFilter AND $filterString" : $tagFilter;
                $results = $index->search('', ['filters' => $combinedFilter, 'hitsPerPage' => 10]);
                $recommended = $recommended->merge($results['hits'] ?? []);
            }

            // Search from keywords
            if (!empty($keywords)) {
                $query = implode(' ', $keywords);
                $results = $index->search($query, [
                    'filters' => $filterString, 'hitsPerPage' => 10
                ]);

                $recommended = $recommended->merge($results['hits'] ?? []);
            }
            if (!empty($categories)) {
                $categoryFilters = array_map(function ($cat) {
                    $escaped = addslashes($cat);
                    return '(categories:"' . $escaped . '" OR categories.0:"' . $escaped . '" OR categories.2:"' . $escaped . '")';
                }, $categories);

                $catFilter = implode(' OR ', $categoryFilters);
                $combinedFilter = $filterString ? "$catFilter AND $filterString" : $catFilter;

                $categoryResults = $index->search('', [
                    'filters' => $combinedFilter,
                    'hitsPerPage' => 10
                ]);
                $recommended = $recommended->merge($categoryResults['hits'] ?? []);
            }
                    

            // Deduplicate and shuffle personalized results
            $recommended = $recommended->unique('id')->shuffle()->take(10);
            $recommended = $recommended->map(function ($product) {
                $product['source'] = 'recommended';
                return $product;
            });
            $recommendedIds = $recommended->pluck('id')->toArray();
            // Best sellers
            $bestResults = $this->searchRepository->bestSellers($data);

            $bestSellers = collect($bestResults['hits'] ?? [])->reject(fn($product) => in_array($product['id'], $recommendedIds));

            // Merge product IDs to fetch categories
            $allProducts = $recommended->concat($bestSellers);
            $productIds = $allProducts->pluck('id')->toArray();

            $categoriesDb = DB::table('categories')
                ->join('product_category', 'categories.id', '=', 'product_category.category_id')
                ->whereIn('product_category.product_id', $productIds)
                ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
                ->get()
                ->groupBy('product_id');

            // Format product with category hierarchy
            $formatProduct = function ($product) use ($categoriesDb) {
                $categoryList = $categoriesDb[$product['id']] ?? collect();
                $categoriesData = [];

                foreach ($categoryList as $category) {
                    if ($category->parentCategoryID == 0) {
                        $categoriesData[] = [
                            'parent' => [
                                'id' => $category->id,
                                'categoryTitle' => $category->categoryTitle,
                                'category_slug' => $category->category_slug,
                                'parentCategoryID' => $category->parentCategoryID,
                            ],
                            'childCategories' => []
                        ];
                    } else {
                        foreach ($categoriesData as &$parentCategory) {
                            if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                                $parentCategory['childCategories'][] = [
                                    'id' => $category->id,
                                    'categoryTitle' => $category->categoryTitle,
                                    'category_slug' => $category->category_slug,
                                    'parentCategoryID' => $category->parentCategoryID
                                ];
                                break;
                            }
                        }
                    }
                }

                return [
                    'product_id' => $product['id'],
                    'objectID' => $product['objectID'] ?? null,
                    'title' => $product['name'],
                    'store_id' => $product['storeId'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'description' => $product['description'],
                    'current_stock' => $product['current_stock'],
                    'status' => $product['status'],
                    'product_type' => $product['product_type'],
                    'on_sale' => $product['on_sale'],
                    'mallshark_selected' => $product['mallshark_selected'],
                    'sale_price' => $product['sale_price'],
                    'shipping_option_id' => $product['shipping_option_id'],
                    'discount_percentage' => $product['discount_percentage'],
                    'feature_image' => $product['feature_image'],
                    'categories' => array_values($categoriesData),
                    'created_at' => $product['created_at'],
                    'query_id' => $product['queryID'] ?? null,
                ];
            };

            return [
                'products' => $recommended->map($formatProduct)->values(),
                'best_sellers' => $bestSellers->map($formatProduct)->values(),
                'recommended_count' => $recommended->count(),
                'best_sellers_count' => $bestSellers->count(),
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
	

    public function recommendedProducts($data)
    {
        try {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'created_at'; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'desc'; // Default order ascending

            $indexName = env('PRODUCT_INDEX'); // Default

            if (!is_null($sortBy)) {
                if ($sortBy === 'price' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCTS_PRICE_ASC');
                } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCTS_PRICE_DESC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCT_CREATED_AT_ASC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCT_CREATED_AT_DESC');
                }
            }

            $results = $this->searchRepository->recommended($data,$indexName);
            $recommendedProducts=collect($results['hits']);

            $bestSellerProducts = collect($this->bestSellers($data)['products']);
            $allProducts = $recommendedProducts
            ->merge($bestSellerProducts)
            ->unique('id')
            ->values();
            $productIds = $allProducts->pluck('id')->toArray();
            
            $categories = DB::table('categories')
                ->join('product_category', 'categories.id', '=', 'product_category.category_id')
                ->whereIn('product_category.product_id', $productIds)
                ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
                ->get()
                ->groupBy('product_id');
            $modifiedProducts = $allProducts
                ->filter(function ($product) {
                    // Only keep products that are arrays and have an 'id' key
                    return is_array($product) && isset($product['id']);
                })
                ->map(function ($product, $index) use ($categories) {
                    // ...your existing mapping logic...
                    $categoryList = $categories[$product['id']] ?? collect();
                    $categoriesData = [];
                    foreach ($categoryList as $category) {
                        if ($category->parentCategoryID == 0) {
                            $categoriesData[] = [
                                'parent' => [
                                    'id' => $category->id,
                                    'categoryTitle' => $category->categoryTitle,
                                    'category_slug' => $category->category_slug,
                                    'parentCategoryID' => $category->parentCategoryID,
                                ],
                                'childCategories' => []
                            ];
                        } else {
                            foreach ($categoriesData as &$parentCategory) {
                                if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                                    $parentCategory['childCategories'][] = [
                                        'id' => $category->id,
                                        'categoryTitle' => $category->categoryTitle,
                                        'category_slug' => $category->category_slug,
                                        'parentCategoryID' => $category->parentCategoryID
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                    return [
                        'product_id' => $product['id'],
                        'objectID' => $product['objectID'] ?? null,
                        'title' => $product['name'] ?? '',
                        'store_id' => $product['storeId'] ?? '',
                        'slug' => $product['slug'] ?? '',
                        'price' => $product['price'] ?? 0,
                        'description' => $product['description'] ?? '',
                        'current_stock' => $product['current_stock'] ?? 0,
                        'status' => $product['status'] ?? '',
                        'product_type' => $product['product_type'] ?? '',
                        'on_sale' => $product['on_sale'] ?? false,
                        'mallshark_selected' => $product['mallshark_selected'] ?? false,
                        'sale_price' => $product['sale_price'] ?? 0,
                        'shipping_option_id' => $product['shipping_option_id'] ?? null,
                        'discount_percentage' => $product['discount_percentage'] ?? 0,
                        'feature_image' => $product['feature_image'] ?? '',
                        'feature_image_alt' => $product['feature_image_alt'] ?? null,
                        'categories' => array_values($categoriesData),
                        'created_at' => $product['created_at'] ?? null,
                    ];
                })
                ->values(); // Re-index         
            return $modifiedProducts;
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    } 
    public function recommendedProductsV3($data)
    {
        try {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice = isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1; // Default to page 1
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'created_at'; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'desc'; // Default order ascending
            $indexName = env('PRODUCT_INDEX'); // Default
            $brandId = isset($data['brand_id']) && $data['brand_id'] !== null ? $data['brand_id'] : null; // Brand filter
            if (!is_null($sortBy)) {
                if ($sortBy === 'price' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCTS_PRICE_ASC');
                } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCTS_PRICE_DESC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                    $indexName = env('PRODUCT_CREATED_AT_ASC');
                } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                    $indexName = env('PRODUCT_CREATED_AT_DESC');
                }
            }

            $results = $this->searchRepository->recommendedV3($data,$indexName);
            $recommendedProducts=collect($results['hits']);

            $bestSellerProducts = collect($this->bestSellersV3($data)['products']);
            $allProducts = $recommendedProducts
            ->merge($bestSellerProducts)
            ->unique('id')
            ->values();
            $productIds = $allProducts->pluck('id')->toArray();
            
            $categories = DB::table('categories')
                ->join('product_category', 'categories.id', '=', 'product_category.category_id')
                ->whereIn('product_category.product_id', $productIds)
                ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug', 'categories.parentCategoryID', 'product_category.product_id')
                ->get()
                ->groupBy('product_id');

            $brands = DB::table('brands')
                ->join('products', 'brands.id', '=', 'products.brand_id')
                ->whereIn('products.product_id', $productIds)
                ->select('brands.id', 'brands.name', 'brands.logo', 'products.product_id')
                ->get()
                ->groupBy('product_id')
                ->map(function ($items) {
                    return $items->map(function ($item) {
                        $item->logo = SearchHelpers::getDistributorLogo($item->logo, 'distributors-documents');
                        return $item;
                    });
                });
                // fetch selected brand logo
            $selectedBrand = null;
            if($brandId){
                // Extract selected brand details from the first product that has it
                foreach ($products as $product) {
                    $productBrands = $brands[$product['id']] ?? collect();
                    $brand = $productBrands->firstWhere('id', $brandId);
                    if ($brand) {
                        $selectedBrand = [
                            'id' => $brand->id,
                            'name' => $brand->name,
                            'logo' => $brand->logo ?? null,
                        ];
                        break;
                    }
                }
            }
            $modifiedProducts = $allProducts
                ->filter(function ($product) {
                    // Only keep products that are arrays and have an 'id' key
                    return is_array($product) && isset($product['id']);
                })
                ->map(function ($product, $index) use ($categories) {
                    // ...your existing mapping logic...
                    $categoryList = $categories[$product['id']] ?? collect();
                    $categoriesData = [];
                    foreach ($categoryList as $category) {
                        if ($category->parentCategoryID == 0) {
                            $categoriesData[] = [
                                'parent' => [
                                    'id' => $category->id,
                                    'categoryTitle' => $category->categoryTitle,
                                    'category_slug' => $category->category_slug,
                                    'parentCategoryID' => $category->parentCategoryID,
                                ],
                                'childCategories' => []
                            ];
                        } else {
                            foreach ($categoriesData as &$parentCategory) {
                                if ($parentCategory['parent']['id'] === $category->parentCategoryID) {
                                    $parentCategory['childCategories'][] = [
                                        'id' => $category->id,
                                        'categoryTitle' => $category->categoryTitle,
                                        'category_slug' => $category->category_slug,
                                        'parentCategoryID' => $category->parentCategoryID
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                    return [
                        'product_id' => $product['id'],
                        'objectID' => $product['objectID'] ?? null,
                        'title' => $product['name'] ?? '',
                        'store_id' => $product['storeId'] ?? '',
                        'slug' => $product['slug'] ?? '',
                        'price' => $product['price'] ?? 0,
                        'description' => $product['description'] ?? '',
                        'current_stock' => $product['current_stock'] ?? 0,
                        'status' => $product['status'] ?? '',
                        'is_custom' => $product['is_custom'] ?? 0,
                        'product_type' => $product['product_type'] ?? '',
                        'on_sale' => $product['on_sale'] ?? false,
                        'mallshark_selected' => $product['mallshark_selected'] ?? false,
                        'sale_price' => $product['sale_price'] ?? 0,
                        'shipping_option_id' => $product['shipping_option_id'] ?? null,
                        'discount_percentage' => $product['discount_percentage'] ?? 0,
                        'feature_image' => $product['feature_image'] ?? '',
                        'feature_image_alt' => $product['feature_image_alt'] ?? null,
                        'categories' => array_values($categoriesData),
                        'created_at' => $product['created_at'] ?? null,
                    ];
                })
                ->values(); // Re-index         
            return['products'=>$modifiedProducts,'brands'=> $brands->flatten()->unique('id')->values(),'selected_brand' => $selectedBrand];
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    } 
    // version 3 functions for website
     public function getLookingSimilarProducts($productId) 
    {
        try {
            $response = $this->searchRepository->lookingSimilarProducts($productId);

            $relatedProducts = collect($response['data'])
                // 1️⃣ Remove given productId if provided
                ->filter(function ($item) use ($productId) {
                    return !$productId || $item['id'] != $productId;
                })
                // 2️⃣ Map to formatted product data
                ->map(function ($item) use ($response) {
                    $product = Product::with('variations', 'labels', 'store')->findOrFail($item['id']);
                    if (!$product) return null;
                    if($product->current_stock < 1) return null;
                        $featureImageId = null;
                        // Convert feature image
                        if ($product->feature_image) {
                            $featureImageId = $product->feature_image;
                            $path = getAttachmentUrlByIdApi($product->feature_image, 'medium');
                            $webpUrl = $this->webPImages($path, 'medium');
                            if ($webpUrl) {
                                $product->feature_image = $webpUrl;
                            }
                        }

                        // Convert variation images
                        foreach ($product->variations as $variation) {
                            $path = getAttachmentUrlByIdApi($variation->product_variation_image, 'medium');
                            $variation->product_variation_image = $this->webPImages($path, 'medium');
                        }

                        $labels = $product->labels->pluck('slug')->toArray() ?? null;
                    
                        return [
                            "product_id" => $item['id'],
                            "objectID" => $item['objectID'],
                            "title" => $item['name'] ?? null,
                            'store_id' => $item['storeId'],
                            "user_id" => $product->user_id ?? null,
                            "slug" => $product->slug,
                            "price" => $product->price ?? 0,
                            'description' => $product->description,
                            'current_stock' => $product->current_stock,
                            'sku' => $product->sku,
                            'status' => $product->status,
                            'product_type' => $product->product_type,
                            'on_sale' => $product->on_sale,
                            'sale_price' => $product->sale_price,
                            'discount_percentage' => $product->discount_percentage,
                            'feature_image' => $item['feature_image'],
                            'feature_image_id' => $featureImageId,
                            'created_at' => $product->created_at,
                            "query_id" => $response['queryId'],
                            "mallshark_selected" => $product->mallshark_selected,
                            'labels' => $labels,
                            'variations' => $product->variations,
                            'store_name' => $product->store->store_name ?? null,
                            'store_slug' => $product->store->store_slug ?? null
                        ];
                })
                ->filter()
                ->values(); // Reset array keys

            return $relatedProducts;

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    } 
}
