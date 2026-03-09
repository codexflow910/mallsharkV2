<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\search\Searchaccess;
use Redirect;
use App\Http\Controllers\ElasticSearchController;
use DB;

class SearchController extends Controller {

    //private $pdf;
    private $search_obj;
    private $elasticSearchObj;

    public function __construct() {
        $this->search_obj = new Searchaccess();
        $this->elasticSearchObj = new ElasticSearchController();
    }

    public static function searchdata(Request $request) {
        $input = $request->all();
        $obj = new Searchaccess();
        $data = $obj::search_query($input);
        $cartProducts = $obj->getCartAddedProduct();
        $stores = $obj->getAllStores();

        return view('product.searchProduct', ['result' => $data['results'], 'cartProducts' => $cartProducts, 'values' => $data['values'], 'stores' => $stores]);
    }

    public function index() {
        return view('search.srarchresults');
    }

    public function getSearchResults(Request $request, $slug='',$subSlug='') {
       
        $all_filters = $request->all();
       
        if (empty($all_filters)) {
            $all_filters = array();
        }
        if($request->has('keyword') && $request->filled('keyword')){
            // $keyword = $request->input('keyword');
            $keywords = explode(" ",preg_replace("/[^a-zA-Z0-9 ]/",'',$request->input('keyword')));
            $keywords = array_filter($keywords, function($value) {
                return $value !== "";
            });
            $keywords = array_values($keywords);

            if(!empty($_COOKIE['cookie-consent'])){
                $exisitngTags = [];
                $cookieConsent = json_decode($_COOKIE['cookie-consent']);
                if($cookieConsent == '1'){
                    if(!empty($_COOKIE['tags'])){
                        $tags = json_decode($_COOKIE['tags']);
                        $tags = (array)$tags;
                        $tags = array_filter($tags, function($value) {
                            return $value !== "";
                        });
                        $tags = array_values($tags);
                        // echo 'Data: <pre>' .print_r($tags,true). '</pre>'; die();
                   $exisitngTags = [...$tags];
                   
                }
                $data = [...$exisitngTags,...$keywords];
                $expire_at = time() + (30 * 24 * 60 * 60);
                setcookie('tags',json_encode(array_unique($data)),$expire_at);    
                    
                }
      
            }
        }
        if(isset($subSlug) && $subSlug != ''){
            $slug = $subSlug;
        }else{
            $slug = $slug;
        }
        // dd($all_filters);
        $productResults = $this->elasticSearchObj->searchDocument($all_filters,$slug); 
        // dd($productResults);
                    if(isset($slug) && ($slug == 'welcome-deal' || $slug == 'best-sellers' || $slug == 'christmas-deal')){
                        // dd($all_filters,$_GET['category']);
                       
                        if(isset($_GET['category']) || empty($all_filters)){
                            
                            $products = $this->elasticSearchObj->searchFilterDocument($all_filters,$slug); 
                          
                                $filter = array();
                
                                $filter100 = false; 
                                $filter_25_to_50 = false; 
                                $filter_50_to_100 = false; 
                                $filter_1_to_25 = false; 
                                $category_array = [];
                                $parent_array = [];  // Array to store all parent categories
                                if(isset($products['records']) && count($products['records']) > 0){
                                    foreach($products['records'] as $product){
                                
                                        if (isset($product['_source']['categories']) && is_array($product['_source']['categories'])) {
                    
                    
                                            if (!$filter100 && $product['_source']['sale_price'] >= 100) {
                                                $filter100 = true;
                                            }
                                            
                                            if (!$filter_1_to_25 && ($product['_source']['sale_price'] >= 1 && $product['_source']['sale_price'] <= 25)) {
                                                $filter_1_to_25 = true;
                                            }
                                            
                                            if (!$filter_25_to_50 && ($product['_source']['sale_price'] > 25 && $product['_source']['sale_price'] <= 50)) {
                                                $filter_25_to_50 = true;
                                            }
                                            
                                            if (!$filter_50_to_100 && ($product['_source']['sale_price'] > 50 && $product['_source']['sale_price'] < 100)) {
                                                $filter_50_to_100 = true;
                                            }
                                            $filter['filter100'] = $filter100;
                                            $filter['filter_1_to_25'] = $filter_1_to_25;
                                            $filter['filter_25_to_50'] = $filter_25_to_50;
                                            $filter['filter_50_to_100'] = $filter_50_to_100;
                            
                                            
                                            // Loop through the categories of the product
                                            //  dd($product['_source']['categories']);
                                            foreach ($product['_source']['categories'] as $category) {
                                            
                                                $parentCategory = getChildCategoryParent($category); // Retrieve parent category if exists
                                                
                                                // Case: No parent category -> This category is a parent itself
                                                if(!$parentCategory){
                                                    // Check if this parent category already exists in the array
                                                    $found = false;
                                                    foreach ($category_array as &$parent) {
                                                        if (isset($parent[$category])) {
                                                            $found = true;
                                                            break;
                                                        }
                                                    }
                                    
                                                    // If the parent category is not already in the array, add it
                                                    if (!$found) {
                                                        $category_array[] = [
                                                            $category => ['child' => []] // Initialize parent with an empty 'child' array
                                                        ];
                                                    }
                                                } 
                                                // Case: Parent category exists -> Add this category as a child of the parent
                                                else {
                                                    $parentSlug = $parentCategory->category_slug; // Assuming the slug is the unique identifier
                                    
                                                    // Check if the parent category already exists in the array
                                                    $found = false;
                                                    foreach ($category_array as &$parent) {
                                                        if (isset($parent[$parentSlug])) {
                                                            // If the child category is not already in the parent's child array, add it
                                                            if (!in_array($category, $parent[$parentSlug]['child'])) {
                                                                $parent[$parentSlug]['child'][] = $category;
                                                            }
                                                            $found = true;
                                                            break;
                                                        }
                                                    }
                                    
                                                    // If the parent category does not exist, add it with the current category as a child
                                                    if (!$found) {
                                                        $category_array[] = [
                                                            $parentSlug => ['child' => [$category]] // Initialize parent with this category as the first child
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                
                                    
                                }
                                // dd('1',$filter);
                        }else{
                          
                            $filter = array();
                            $filter100 = false; 
                            $filter_25_to_50 = false; 
                            $filter_50_to_100 = false; 
                            $filter_1_to_25 = false; 
                            $category_array = [];
                            $parent_array = [];  // Array to store all parent categories
                            if(isset($productResults['records']) && count($productResults['records']) > 0){
                                foreach($productResults['records'] as $product){
                            
                                    if (isset($product['_source']['categories']) && is_array($product['_source']['categories'])) {
        
        
                                        if (!$filter100 && $product['_source']['sale_price'] >= 100) {
                                            $filter100 = true;
                                        }
                                        
                                        if (!$filter_1_to_25 && ($product['_source']['sale_price'] >= 1 && $product['_source']['sale_price'] <= 25)) {
                                            $filter_1_to_25 = true;
                                        }
                                        
                                        if (!$filter_25_to_50 && ($product['_source']['sale_price'] > 25 && $product['_source']['sale_price'] <= 50)) {
                                            $filter_25_to_50 = true;
                                        }
                                        
                                        if (!$filter_50_to_100 && ($product['_source']['sale_price'] > 50 && $product['_source']['sale_price'] < 100)) {
                                            $filter_50_to_100 = true;
                                        }
                                        $filter['filter100'] = $filter100;
                                        $filter['filter_1_to_25'] = $filter_1_to_25;
                                        $filter['filter_25_to_50'] = $filter_25_to_50;
                                        $filter['filter_50_to_100'] = $filter_50_to_100;
                        
                                        
                                        // Loop through the categories of the product
                                        //  dd($product['_source']['categories']);
                                        foreach ($product['_source']['categories'] as $category) {
                                        
                                            $parentCategory = getChildCategoryParent($category); // Retrieve parent category if exists
                                            
                                            // Case: No parent category -> This category is a parent itself
                                            if(!$parentCategory){
                                                // Check if this parent category already exists in the array
                                                $found = false;
                                                foreach ($category_array as &$parent) {
                                                    if (isset($parent[$category])) {
                                                        $found = true;
                                                        break;
                                                    }
                                                }
                                
                                                // If the parent category is not already in the array, add it
                                                if (!$found) {
                                                    $category_array[] = [
                                                        $category => ['child' => []] // Initialize parent with an empty 'child' array
                                                    ];
                                                }
                                            } 
                                            // Case: Parent category exists -> Add this category as a child of the parent
                                            else {
                                                $parentSlug = $parentCategory->category_slug; // Assuming the slug is the unique identifier
                                
                                                // Check if the parent category already exists in the array
                                                $found = false;
                                                foreach ($category_array as &$parent) {
                                                    if (isset($parent[$parentSlug])) {
                                                        // If the child category is not already in the parent's child array, add it
                                                        if (!in_array($category, $parent[$parentSlug]['child'])) {
                                                            $parent[$parentSlug]['child'][] = $category;
                                                        }
                                                        $found = true;
                                                        break;
                                                    }
                                                }
                                
                                                // If the parent category does not exist, add it with the current category as a child
                                                if (!$found) {
                                                    $category_array[] = [
                                                        $parentSlug => ['child' => [$category]] // Initialize parent with this category as the first child
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                }
                            
                                
                            }
                            
                            
                        }
                        
               $cartProducts = $this->search_obj->getCartAddedProduct();
               
                   
                    
                   
               if ($request->ajax()) {
                    if (isset($_GET['loadmore'])                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         ) {
                        return view('search.deals.ajax-loadmore', ['productResults' => $productResults, 'cartProducts' => $cartProducts]);
                    }
                    $ajax = true;
                    return view('search.deals.search-ajax', ['productResults' => $productResults, 'cartProducts' => $cartProducts,'categories' => $category_array,'filters' => $filter,'ajax' => $ajax]);
                }
                $ajax = false;
               return view('search.deals.search', ['productResults' => $productResults, 'cartProducts' => $cartProducts, 'categories' => $category_array,'filters' => $filter,'ajax' => $ajax]);
           
        }
        $cartProducts = $this->search_obj->getCartAddedProduct();
            if ($request->ajax()) {
                if (isset($_GET['loadmore'])) {
                    return view('search.search-ajax-loadmore', ['productResults' => $productResults, 'cartProducts' => $cartProducts]);
                }
                $ajax = true;
                
                 return view('search.search-ajax', ['productResults' => $productResults, 'cartProducts' => $cartProducts,'ajax' => $ajax]);
            } else {
                $ajax = false;
                return view('search.srarchresults', ['productResults' => $productResults, 'cartProducts' => $cartProducts,'name'=>$slug,'ajax' => $ajax]);
            }
        // }
   
}

    public function getChildCategories(Request $req) {
        $parentCategoryID = $req->input();

        $res = '';
        if ($parentCategoryID != '') {
            $all_categories_child = DB::table('categories')->where('parentCategoryID', '=', $parentCategoryID)->select('category_slug', 'categoryTitle')->get()->toArray();

            if (count($all_categories_child) > 0) {
                $res = '<option value="">Select Child Category</option>';
                foreach ($all_categories_child as $child) {
                    $res .='<option value="' . $child->category_slug . '">' . $child->categoryTitle . '</option>';
                }
            }
        }
        return $res;
    }

}
