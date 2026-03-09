<?php

namespace App\Http\Controllers\API;

use App\Models\{Product,SearchTags};
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Repositories\search\Searchaccess;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ElasticSearchController;
use Algolia\AlgoliaSearch\SearchClient;
class SearchController extends Controller
{

    private $search_obj;
    private $elasticSearchObj;
    private $_request;

    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->search_obj = new Searchaccess();
        $this->elasticSearchObj = new ElasticSearchController();
    }

    public function getSearchResults(Request $request)
    {
        try {
            $all_filters = $request->all();
            // return response()->json($all_filters);
            if (empty($all_filters)) {
                $all_filters = array();
            }
            if ($request->has('keyword') && $request->filled('keyword')) {
                if ($request->has('user_id') && $request->filled('user_id')) {
                    $userId = $request->user_id;
                    $search_tag = SearchTags::where('tag', $all_filters['keyword'])->where('user_id', $userId)->first();
                    if (is_null($search_tag)) {

                        $search_tag = new SearchTags();
                        $search_tag->tag = $all_filters['keyword'];
                        $search_tag->user_id = $userId;
                        $search_tag->save();
                    }
                }
            }
            $productResults = $this->elasticSearchObj->searchDocument($all_filters,$slug = '');
            if (!empty($productResults) && count($productResults['records']) > 0) {
                $totalProducts = $productResults['total']['value'];

                $productArray = [];
                $categoryAssociativeArray = [];
                foreach ($productResults['records'] as  $key => $records) {
                    $products = (array) $records;

                    $products['_source']['feature_image'] = getAttachmentUrlById($records['_source']['feature_image'], 'media');
                    // $categories = getCategoriesByProductID($records['_source']['product_id']);
                    // echo 'Data: <pre>' .print_r($categories,true). '</pre>'; 
                    $storeData = getStoreInfoByIdApi($records['_source']['store_id']);
                    $storeLogoId = $storeData->store_logo;
                    $storeData->store_logo = getAttachmentUrlById((int)$storeLogoId, 'media');
                    $serializedData = $records['_source']['gallery'];
                    $galleryArray = [];

                    if ($serializedData && !empty($serializedData)) {
                        $unserializedArray = unserialize($serializedData);

                        foreach ($unserializedArray as $value) {
                            $galleryImageInfo = [];
                            $galleryImageInfo = getAttachmentUrlById((int) $value, 'media');
                            $galleryArray[] = $galleryImageInfo;
                        }
                    }



                    // commented below code to avoid an error when no category but price range filter exists

                    // $categories = $products['_source']['categories'];

                    // $parent = $categories[0]; // Parent category is always at index 0

                    // // Check if a subcategory exists at index 1
                    // if (isset($categories[1])) {
                    //     $sub = $categories[1];
                    // } else {
                    //     $sub = null;
                    // }

                    // // Add parent category as key if not already present
                    // $found = false;
                    // foreach ($categoryAssociativeArray as &$item) {
                    //     if ($item['parent'] === $parent) {
                    //         $found = true;
                    //         if ($sub && !in_array($sub, $item['child'])) {
                    //             $item['child'][] = $sub;
                    //         }
                    //         break;
                    //     }
                    // }
                    // if (!$found) {
                    //     $categoryAssociativeArray[] = ["parent" => $parent, "child" => $sub ? [$sub] : []];
                    // }
                    $products['_source']['gallery'] = $galleryArray;
                    $products['_source']['store'] = $storeData;
                    $rating = getProductRatingById($records['_source']['product_id']);
                    $products['_source']['rating'] = $rating;
                    $productArray[] = $products;
                }
            }
            if (!empty($productResults) && count($productResults['records']) > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'You successfully get products',
                    'totalProducts' => $totalProducts,
                    'Products'   => $productArray,
                    'categories' => $categoryAssociativeArray
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You have not any record"
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'messagess' => $th->getMessage()
            ], 500);
        }
    }

    public function getSearchTags()
    {
        try {
            $userId = auth()->user()->user_id;
            if (!empty($userId)) {
                $allTags =  SearchTags::where('user_id', $userId)->select('tag')->get()->toArray();
            }

            if (!empty($allTags) && count($allTags) > 0) {

                return response()->json([
                    'status' => true,
                    'message' => 'You successfully get Tags',
                    'keywords' => $allTags,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "You have not any Tags"
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'messagess' => $th->getMessage()
            ], 500);
        }
    }
    public function getPopularTags()
    {
        $tags = SearchTags::select('tag')->get()->ToArray();
        if ($tags) {
            $tagCounts = array_count_values(array_column($tags, 'tag'));

            // Sort the array in descending order based on counts
            arsort($tagCounts);

            // Get the top 5 tags
            $trendingTags = array_slice(array_keys($tagCounts), 0, 10);
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $trendingTags
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'success' => false,
                'message' => 'No tag found'
            ]);
        }


        // Output the trending tags

    }
    public function searchResults(Request $request){
        $query = $request->input('name', ''); // Optional product name search term
        $category = $request->input('category', ''); // Optional category filter
        $minPrice = $request->input('min_price'); // Minimum price filter
        $maxPrice = $request->input('max_price'); // Maximum price filter
        $perPage = $request->input('per_page'); // Results per page (default: 20)
        $page = $request->input('page');
        $sortBy = $request->input('sort_by', null);
        $sortOrder = $request->input('order', 'asc');
        // Validate input
        $request->validate([
            'name' => 'nullable|string',
            'category' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
        ]);
        $indexName = 'products'; // Default primary index
        if (!is_null($sortBy)) {
            if ($sortBy === 'price' && $sortOrder === 'asc') {
                $indexName = 'products_price_asc';
            } elseif ($sortBy === 'price' && $sortOrder === 'desc') {
                $indexName = 'products_price_desc';
            } elseif ($sortBy === 'created_at' && $sortOrder === 'asc') {
                $indexName = 'products_created_at_asc';
            } elseif ($sortBy === 'created_at' && $sortOrder === 'desc') {
                $indexName = 'products_created_at_desc';
            }
        }
        // Perform the search using the combined term
        $searchQuery = Product::search($query)->within($indexName);
        // Apply category filter if provided
        $filters = [];
        if (!empty($category)) {
            $filters[] = "categories:$category";
        }
      
        // Apply price range filter if provided
        if (!empty($minPrice) && !empty($maxPrice)) {
            $filters[] = "effective_price >= $minPrice AND effective_price <= $maxPrice";
        }
        
        // Combine all filters using AND
        if (!empty($filters)) {
            $filtersString = implode(' AND ', $filters);
            $searchQuery->with(['filters' => $filtersString]);
        }
        if(!is_null($sortBy)){
            $sorting = $sortBy . ':' . $sortOrder;
            $searchQuery = $searchQuery->orderBy($sorting);
        }
        // Perform the search with pagination
        $products = $searchQuery->paginate($perPage, 'page', $page);
        // dd($products);
        $products->load('categories:id,categoryTitle,category_slug,parentCategoryID');
        // Modify each product to include only the fields we need
        $modifiedProducts = $products->getCollection()->map(function ($product) {
            // Get the feature image URL if it exists
            if (isset($product->feature_image)) {
                $product->feature_image = getAttachmentUrlByIdApi($product->feature_image);
            }
            $categoriesData = [];

            // Loop through each category of the product
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
        
            // Add categories to the product data
            $product->categories = array_values($categoriesData); // Re-index to reset keys
            // Only return the product_id and feature_image fields
            return [
                
                'product_id' => $product->product_id,
                'title' => $product->title,
                'user_id' => $product->user_id,
                'slug' => $product->slug,
                'price' => $product->price,
                'tags' => $product->tags,
                'description' => $product->description,
                'quantity' => $product->quantity,
                'current_stock' => $product->current_stock,
                'sku' => $product->sku,
                'status' => $product->status,
                'product_type' => $product->product_type,
                'on_sale' => $product->on_sale,
                'sale_price' => $product->sale_price,
                'discount_percentage' => $product->discount_percentage,
                'feature_image' => $product->feature_image,
                'categories' => $product->categories,
                'created_at' => $product->created_at,
            ];
        });

        // Replace the original product collection with the modified one
        $products->setCollection($modifiedProducts);
     
        if($products->count() > 0){
            $response = ['status' => true, 'data' => $products];
        }else{
            $response = ['status' => false, 'message' => 'no record found'];
        }
        // Return the response with only the required fields
        
        return response()->json($response);
    }

   
    
    

   
    /**
     * Get search suggestions from the Algolia index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(Request $request)
    {
       
        // Validate the query parameter
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        $query = $request->input('query', '');
        
        try {
            // Initialize Algolia client
            $client = SearchClient::create(
                env('ALGOLIA_APP_ID'),
                env('ALGOLIA_SECRET')
            );

            // Define the index for the local_products
            $index = $client->initIndex('local_products_query_suggestions'); // Replace with your actual index name

            // Perform the search query on the index
            $results = $index->search($query, [
                'hitsPerPage' => 10, // Limit the number of suggestions returned
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

            // Return the suggestions in a structured response
            return response()->json([
                'status' => true,
                'data' => $suggestions,
            ]);

        } catch (\Exception $e) {
            // Handle any exceptions and return a generic error message
            return response()->json([
                'status' => false,
                'message' => 'Error fetching suggestions: ' . $e->getMessage(),
            ], 500);
        }
    }
}
