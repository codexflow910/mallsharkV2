<?php

namespace Packages\Search\Services;

use Packages\Search\Contracts\SearchHelpers;
use Packages\Search\Contracts\SearchRepositoryInterface;
use Illuminate\Support\ItemNotFoundException;
use Packages\Search\Repositories\SearchRepository;
use Algolia\AlgoliaSearch\SearchClient;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class SearchService

{
    use SearchHelpers;
    protected $searchRepository;

    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    public function suggestion(array $data)
    {
        try {
            $query = $data['query'];
            $client = SearchClient::create(
                env('ALGOLIA_APP_ID'),
                env('ALGOLIA_SECRET')
            );
            $index = $client->initIndex(env('LOCAL_PRODUCTS_QUERY_SUGGESTION'));
            $results = $index->search($query, [
                'hitsPerPage' => 50, // Limit the number of suggestions returned
                'attributesToRetrieve' => ['query', 'products.categories'], // Retrieve relevant attributes
                'highlightPreTag' => '<em>',
                'highlightPostTag' => '</em>'
            ]);

            // Format the suggestions for products and categories
            $suggestions = collect($results['hits'])->map(function ($hit) {
                return [
                    'query' => $hit['query'],
                    'categories' => $hit['products']['facets']['exact_matches'] ?? [], // Retrieve categories if available
                    'exact_matches' => $hit['products']['exact_nb_hits'] ?? 0, // Exact matches count
                ];
            });
            return $suggestions;
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
            $sortBy = isset($data['sort_by']) && $data['sort_by'] != '' ? $data['sort_by'] : null; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== '' ? $data['order'] : null; // Default order ascending

           
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
            $results = $this->searchRepository->searchAlgolia($data,$indexName);
          
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

    public function bestSellers($data)
    {
        try {

            $results = $this->searchRepository->bestSellers($data);
            // dd($products);
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
            ];
        });

            // Replace the original product collection with the modified one
            $products = $modifiedProducts;
            return $products;
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
                    'categories' => $product->categories,
                    'created_at' => $product->created_at,
                    "query_id" => $response['queryId'] // Generating a random query ID
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
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name'; // Default sort by name
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc'; // Default order ascending

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
                'categories' => array_values($categoriesData), // Attach categories
                'created_at' => $product['created_at'],
                'query_id' => $results['queryID'],
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
                        'shipping_option_id' => $product->shipping_option_id,
                        'discount_percentage' => $product->discount_percentage,
                        'feature_image' => $item['feature_image'], // Ensure image exists
                        'categories' => $product->categories,
                        'created_at' => $product->created_at,
                        "query_id" => $response['queryId'] // Generating a random query ID
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
}
