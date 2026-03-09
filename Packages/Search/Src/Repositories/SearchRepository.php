<?php

namespace Packages\Search\Repositories;

use App\Models\Store;
use Exception;
use InvalidArgumentException;
use Packages\Search\Contracts\SearchRepositoryInterface;
use Packages\Search\Repositories\Contracts\SearchTypes\SimpleProduct;
use App\Models\Attachments;
use App\Models\AttachmentSizes;
use App\Models\Product;
use Facade\FlareClient\Http\Exceptions\NotFound;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ItemNotFoundException;
use Intervention\Image\Facades\Image;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\RecommendClient;
use Algolia\AlgoliaSearch\InsightsClient;
use App\Models\SearchTags;

class SearchRepository implements SearchRepositoryInterface
{

    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function search($data)
    {

        $product =  $this->model->fill($data);
        dd('base');
    }

    public function searchAlgolia($data, $indexName)
    {
        try {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice =  isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $onSale =  isset($data['on_sale']) && $data['on_sale'] !== null ? $data['on_sale'] : null;
            $mallsharkselected =  isset($data['mallshark_selected']) && $data['mallshark_selected'] !== null ? $data['mallshark_selected'] : null;
            $shipOptionId =  isset($data['shipping_option_id']) && $data['shipping_option_id'] !== null ? $data['shipping_option_id'] : null;
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1;
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name';
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc';
          
            $searchQuery = Product::search($query)->within($indexName);

            if (!empty($query)) {

                $existingTag = SearchTags::where('tag', $query)->first();

                if ($existingTag) {
                    $existingTag->increment('counts');
                } else {
                    SearchTags::create([
                        "user_id" => 0,
                        'tag' => $query,
                        'count' => 1
                    ]);
                }
            }
            // Apply category filter if provided
            $filters = [];
            
            if (!empty($category)) {
                $filters[] = "(categories:\"$category\" OR categories.0:\"$category\" OR categories.2:\"$category\")";
            }
            // Apply price range filter if provided
            if (!empty($minPrice) && !empty($maxPrice)) {
                $filters[] = "effective_price >= $minPrice AND effective_price <= $maxPrice";
            }
            if (isset($onSale) && $onSale !== '') { // Checks if $onSale is set and not an empty string
                $filters[] = "on_sale = $onSale";
            }
            if (isset($mallsharkselected) && $mallsharkselected !== '') { // Checks if $onSale is set and not an empty string
                $filters[] = "mallshark_selected = $mallsharkselected";
            }
            if (isset($shipOptionId) && $shipOptionId !== '') { // Checks if $onSale is set and not an empty string
                if ($shipOptionId == 1) {
                    $filters[] = "shipping_option_id = 221";    // Free Shipping
                } else {
                    $filters[] = "shipping_option_id !=221 ";
                }
            }
            // Combine all filters using AND
            if (!empty($filters)) {        
                $filtersString = implode(' AND ', $filters);
                // dd($filtersString);
                $searchQuery->with(['filters' => $filtersString]);
            }
            if (!is_null($sortBy)) {
                $sorting = $sortBy . ':' . $sortOrder;
                $searchQuery = $searchQuery->orderBy($sorting);
            }
            $searchQuery->with([
                'page' => $page - 1, // Algolia pages are 0-based, Laravel uses 1-based
                'hitsPerPage' => $perPage,
                'clickAnalytics' => true
            ]);
            $response = $searchQuery->raw();

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function bestSellers($data)
    {
        try {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice =  isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $onSale =  isset($data['on_sale']) && $data['on_sale'] !== null ? $data['on_sale'] : null;
            $mallsharkselected =  isset($data['mallshark_selected']) && $data['mallshark_selected'] !== null ? $data['mallshark_selected'] : null;
            $shipOptionId =  isset($data['shipping_option_id']) && $data['shipping_option_id'] !== null ? $data['shipping_option_id'] : null;
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1;
            $sortBy = $data['sort_by'] ?? 'sales';
            $sortOrder = $data['order'] ?? 'asc';

            switch ($sortBy) {
                case 'price':
                    $indexName = $sortOrder === 'asc' 
                        ? env('PRODUCTS_SALES_PRICE_ASC') 
                        : env('PRODUCTS_SALES_PRICE_DESC');
                    break;
                case 'created_at':
                    $indexName = $sortOrder === 'asc' 
                        ? env('PRODUCTS_SALES_CREATED_ASC') 
                        : env('PRODUCTS_SALES_CREATED_DESC');
                    break;
                case 'sales':
                default:
                    $indexName = env('PRODUCTS_SALES_ASC'); // Sorted by sales
                    break;
            }
            $searchQuery = Product::search('*')->within($indexName);

            $filters = [];
            if (!empty($category)) {
                $filters[] = "(categories:\"$category\" OR categories.0:\"$category\" OR categories.2:\"$category\")";
            }
            // Apply price range filter if provided
            if (!empty($minPrice) && !empty($maxPrice)) {
                $filters[] = "effective_price >= $minPrice AND effective_price <= $maxPrice";
            }
            if (isset($onSale) && $onSale !== '') { // Checks if $onSale is set and not an empty string
                $filters[] = "on_sale = $onSale";
            }
            if (isset($mallsharkselected) && $mallsharkselected !== '') { // Checks if $onSale is set and not an empty string
                $filters[] = "mallshark_selected = $mallsharkselected";
            }
            if (isset($shipOptionId) && $shipOptionId !== '') { // Checks if $onSale is set and not an empty string
                if ($shipOptionId == 1) {
                    $filters[] = "shipping_option_id = 221";    // Free Shipping
                } else {
                    $filters[] = "shipping_option_id !=221 ";
                }
            }
            // Combine all filters using AND
            if (!empty($filters)) {
                $filtersString = implode(' AND ', $filters);
                $searchQuery->with(['filters' => $filtersString]);
            }
            if (!is_null($sortBy)) {
                $sorting = $sortBy . ':' . $sortOrder;
                $searchQuery = $searchQuery->orderBy($sorting);
            }
            $searchQuery->with([
                'page' => $page - 1, // Algolia pages are 0-based, Laravel uses 1-based
                'hitsPerPage' => $perPage,
                'clickAnalytics' => true
            ]);
            $response = $searchQuery->raw();

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function deals($data, $indexName)
    {
        try {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice =  isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $onSale =  isset($data['on_sale']) && $data['on_sale'] !== null ? $data['on_sale'] : null;
            $mallsharkselected =  isset($data['mallshark_selected']) && $data['mallshark_selected'] !== null ? $data['mallshark_selected'] : null;
            $shipOptionId =  isset($data['shipping_option_id']) && $data['shipping_option_id'] !== null ? $data['shipping_option_id'] : null;
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1;
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'name';
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'asc';
            $slug_deal = isset($data['slug_deal']) && $data['slug_deal'] !== null ? $data['slug_deal'] : 'welcome-deal';
            
            $searchQuery = Product::search($query)->within($indexName);


            // Apply category filter if provided
            $filters = [];
            if (!empty($category)) {
                $filters[] = "(categories:\"$category\" OR categories.0:\"$category\" OR categories.2:\"$category\")";
            }
            // Apply price range filter if provided
            if (!empty($minPrice) && !empty($maxPrice)) {
                $filters[] = "effective_price >= $minPrice AND effective_price <= $maxPrice";
            }
            if (isset($onSale) && $onSale !== '') { // Checks if $onSale is set and not an empty string
                $filters[] = "on_sale = $onSale";
            }
            if (isset($mallsharkselected) && $mallsharkselected !== '') { // Checks if $onSale is set and not an empty string
                $filters[] = "mallshark_selected = $mallsharkselected";
            }
            if (isset($shipOptionId) && $shipOptionId !== '') { // Checks if $onSale is set and not an empty string
                if ($shipOptionId == 1) {
                    $filters[] = "shipping_option_id = 221";    // Free Shipping
                } else {
                    $filters[] = "shipping_option_id !=221 ";
                }
            }
            if ($slug_deal === 'welcome-deal') {
                $filters[] = "discount_percentage <= 50 AND discount_percentage > 0";
            }
            if ($slug_deal === 'super-deal') {
                $filters[] = "discount_percentage > 20 AND discount_percentage < 70";
            }
            if ($slug_deal === 'summer-deal') {
                $filters[] = "discount_percentage >= 50 AND discount_percentage < 90";
            }
         
            // Combine all filters using AND
            if (!empty($filters)) {
                $filtersString = implode(' AND ', $filters);
                $searchQuery->with(['filters' => $filtersString]);
            }
            if (!is_null($sortBy)) {
                $sorting = $sortBy . ':' . $sortOrder;
                $searchQuery = $searchQuery->orderBy($sorting);
            }
            $searchQuery->with([
                'page' => $page - 1, // Algolia pages are 0-based, Laravel uses 1-based
                'hitsPerPage' => $perPage,
                'clickAnalytics' => true
            ]);
            $response = $searchQuery->raw();

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function exploreMore($data, $indexName)
    {
        try {
            $query = isset($data['name']) && $data['name'] !== null ? $data['name'] : null; // Optional product name search term
            $category = isset($data['category']) && $data['category'] !== null ? $data['category'] : null; // Optional category filter
            $minPrice = isset($data['min_price']) && $data['min_price'] !== null ? $data['min_price'] : null; // Minimum price filter
            $maxPrice =  isset($data['max_price']) && $data['max_price'] !== null ? $data['max_price'] : null; // Maximum price filter
            $perPage = isset($data['per_page']) && $data['per_page'] !== null ? $data['per_page'] : 20; // Results per page (default: 20)
            $onSale =  isset($data['on_sale']) && $data['on_sale'] !== null ? $data['on_sale'] : null;
            $mallsharkselected =  isset($data['mallshark_selected']) && $data['mallshark_selected'] !== null ? $data['mallshark_selected'] : null;
            $shipOptionId =  isset($data['shipping_option_id']) && $data['shipping_option_id'] !== null ? $data['shipping_option_id'] : null;
            $page = isset($data['page']) && $data['page'] !== null ? $data['page'] : 1;
            $sortBy = isset($data['sort_by']) && $data['sort_by'] !== null ? $data['sort_by'] : 'effective_price';
            $sortOrder = isset($data['order']) && $data['order'] !== null ? $data['order'] : 'desc';
            $searchQuery = Product::search($query)->within($indexName);


            // Apply category filter if provided
            $filters = [];
            if (!empty($category)) {
                $filters[] = "(categories:\"$category\" OR categories.0:\"$category\" OR categories.2:\"$category\")";
            }
            // Apply price range filter if provided
            if (!empty($minPrice) && !empty($maxPrice)) {
                $filters[] = "effective_price >= $minPrice AND effective_price <= $maxPrice";
            }
            if (isset($onSale) && $onSale !== '') { // Checks if $onSale is set and not an empty string
                $filters[] = "on_sale = $onSale";
            }
            if (isset($mallsharkselected) && $mallsharkselected !== '') { // Checks if $onSale is set and not an empty string
                $filters[] = "mallshark_selected = $mallsharkselected";
            }
            if (isset($shipOptionId) && $shipOptionId !== '') { // Checks if $onSale is set and not an empty string
                if ($shipOptionId == 1) {
                    $filters[] = "shipping_option_id = 221";    // Free Shipping
                } else {
                    $filters[] = "shipping_option_id !=221 ";
                }
            }
         
            // Combine all filters using AND
            if (!empty($filters)) {
                $filtersString = implode(' AND ', $filters);
                $searchQuery->with(['filters' => $filtersString]);
            }
            if (!is_null($sortBy)) {
                $sorting = $sortBy . ':' . $sortOrder;
                $searchQuery = $searchQuery->orderBy($sorting);
            }
            $searchQuery->with([
                'page' => $page - 1, // Algolia pages are 0-based, Laravel uses 1-based
                'hitsPerPage' => $perPage,
                'clickAnalytics' => true
            ]);
            $response = $searchQuery->raw();

            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function relatedProducts($id)
    {
        try {
            $appId = env('ALGOLIA_APP_ID');
            $apiKey = env('ALGOLIA_SECRET');
            $indexName = env('PRODUCT_INDEX');
            $client = RecommendClient::create($appId, $apiKey);
            $searchClient = SearchClient::create($appId, $apiKey);
            $response = $client->getRecommendations([
                [
                    'indexName' => $indexName,
                    'objectID' => $id, // Ensure objectID is a string
                    'model' => 'related-products', // Can also be 'bought-together'
                    'threshold' => 10, // Adjust as needed
                ]
            ]);
            $hits = $response['results'][0]['hits'] ?? [];
            if (empty($hits)) {
                $fallback = $searchClient->initIndex($indexName)->search('', [
                    'filters' => 'effective_price < 200',
                    'hitsPerPage' => 30,
                ]);
                $hits = $fallback['hits'] ?? [];
                $queryID = null; // No queryID in fallback
            } else {
                $queryID = $response['results'][0]['queryID'];
            }
            return ['data' => $hits, 'queryId' => $response['results'][0]['queryID']];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function trendingProducts()
    {
        try {
            $appId = env('ALGOLIA_APP_ID');
            $apiKey = env('ALGOLIA_SECRET');
            $indexName = env('PRODUCT_INDEX');
            $searchClient = SearchClient::create($appId, $apiKey);
            $client = RecommendClient::create($appId, $apiKey);
            $response = $client->getRecommendations([
                [
                    'indexName' => $indexName,
                    'model' => 'trending-items',
                    // 'threshold' => 5,
                    // 'maxRecommendations' => ,
                ]
            ]);
            $hits = $response['results'][0]['hits'] ?? [];
            if (empty($hits)) {
                $fallback = $searchClient->initIndex($indexName)->search('', [
                    'filters' => 'effective_price < 200',
                    'hitsPerPage' => 30,
                ]);
                $hits = $fallback['hits'] ?? [];
            }
            return ['data' => $hits, 'queryId' => $response['results'][0]['queryID']];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getAllTags()
    {
        return SearchTags::select('tag','counts')->get()->toArray();
    }

    public function getTagsByUserId($userId)
    {
        return SearchTags::where('user_id', $userId)
            ->select('tag')
            ->get()
            ->toArray();
    }
    public function getSeacrchProduct($data)
    {
        try {

            $user = auth()->user();
          
            $query = Product::select('product_id', 'title', 'store_id', 'price', 'current_stock', 'feature_image', 'product_type', 'sales', 'status', 'slug')
                ->with([
                    'store:store_id,store_name',
                    'orderItems' => function ($orderItems) {
                        $orderItems->select('order_item_id', 'order_id', 'store_id', 'item_id')
                            ->with('storeProductOrder', function ($storeProductOrder) {
                                $storeProductOrder->select('order_id', 'order_status', 'total_items')
                                    ->where('order_status', 'pending');
                            });
                    }
                ])
                ->whereHas('store', function ($query) use ($user) {
                    $query->where('user_id', $user->user_id);
                });
            $query->where('status', '!=', '2');       // Remove Deleted Products
            if (isset($data['title']) && $data['title'] !== null) {
                $title = $data['title'];
                $query->where('title', 'like', "%$title%");
            }

            // Apply status filter if provided
            if (isset($data['status']) && $data['status'] !== null) {
                $status = $data['status'];

                if (isset($status)) {
                    if ($status == 'deactive') {
                        $query->where('status', '0');
                    }
                    if ($status == 'active') {
                        $query->where('status', '1');
                    }
                    if ($status == 'delete') {
                        $query->where('status', '2');
                    }
                    if ($status == 'pending') {
                        $query->where('status', '3');
                    }
                    if ($status == 'reject') {
                        $query->where('status', '4');
                    }
                    if ($status == 'draft') {
                        $query->where('status', '5');
                    }
                    if ($status == 'discounted') {
                        $query->where('on_sale', '1');
                    }
                    if ($status == 'Featured') {
                        $query->where('status', '7');
                    }
                }
            }

            $all_products = $query->paginate(10);
            if ($all_products->count() > 0) {
                foreach ($all_products->items() as $product) {
                    $product->feature_image = getAttachmentUrlById($product->feature_image, 'media');

                    if ($product->status == 0) {
                        $product->status = 'deActive';
                    } else if ($product->status == 1) {
                        $product->status = 'active';
                    } else if ($product->status == 3) {
                        $product->status = 'pending';
                    } else if ($product->status == 5) {
                        $product->status = 'draft';
                    } else if ($product->status == 2) {
                        $product->status = 'delete';
                    } else if ($product->status == 4) {
                        $product->status = 'rejected';
                    } else if ($product->status == 0) {
                        $product->status = 'deActive';
                    }
                }
              
                return [
                    'data' => $all_products,
                    'count' => $all_products->total(),
                ];
            }

            throw new ItemNotFoundException('No Product Found');
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
