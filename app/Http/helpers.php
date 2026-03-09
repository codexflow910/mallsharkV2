<?php

/*
 * Define helper functions for app
 */

use Carbon\Carbon;
use Stripe\OrderItem;
use App\Models\Brands;
use App\Models\Product;
use App\Models\category;
use App\Models\Discount;
use App\Models\StoreIcon;
// use Illuminate\Http\File;
use App\Models\Attachments;
use App\Models\CountriesList;
use App\Models\DiscountUsage;
use App\Models\CategoryProduct;
use App\Models\Role;
use App\Models\FavouriteStore;
use Illuminate\Support\Facades\DB;
use Huddle\Zendesk\Facades\Zendesk;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Herd\Herdaccess;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
//use App\Repositories\Location\Locationaccess;

/*
 * get all categories
 */
global $cat_Array;

function getAllCategories() {
//return array();
    $all_categories = DB::table('categories')
    ->LeftJoin('product_category', 'product_category.category_id', 'categories.id')
    ->LeftJoin('products', 'products.product_id', 'product_category.product_id')
    ->select(DB::raw('count(product_category.category_id) as totalpros, categories.*'))
    ->groupBy('categories.id')->where('categories.parentCategoryID', '=', 0)
    ->where('products.status', '=', '1')->get();
    //echo ($all_categories);
    // echo 'dddd';
    //die();
    // echo 'Data: <pre>' .print_r($all_categories,true). '</pre>'; die();
    $cat_Array = array();
    foreach ($all_categories as $row) {

        $childs = array();
        $cat_info = ['id' => $row->id, 'title' => $row->categoryTitle, 'slug' => $row->category_slug, 'description' => $row->description, 'image_url' => $row->image_id, 'status' => $row->status, 'total' => $row->totalpros,'updated_at' => $row->updated_at];
        $all_categories_child = DB::table('categories')
        ->leftJoin('product_category', 'categories.id', 'product_category.category_id')
        ->LeftJoin('products', 'products.product_id', 'product_category.product_id')
        ->where('parentCategoryID', '=', $row->id)
        ->where('products.status', '=', '1')
        ->groupby('categories.id')->get();
        foreach ($all_categories_child as $val) {
            $childs[] = ['id' => $val->id, 'title' => $val->categoryTitle, 'slug' => $val->category_slug, 'description' => $val->description, 'image_url' => $val->image_id, 'status' => $val->status ,'updated_at' => $val->updated_at];
        }
        
        $cat_Array[] = ['cat_info' => $cat_info, 'childs' => $childs];
    }
    return $cat_Array;
}
function getCategoriesHaveProducts() {
    //return array();
        $all_categories = DB::table('categories')
        ->join('product_category', 'product_category.category_id', 'categories.id')
        ->join('products', 'products.product_id', 'product_category.product_id')
        ->select(DB::raw('count(product_category.category_id) as totalpros, categories.*'))
        ->where('categories.categoryTitle', '!=', 'Uncategorized')
        ->where('categories.status', '1')
        ->groupBy('categories.id')->where('categories.parentCategoryID', '=', 0)
        ->where('products.status', '=', '1')->get();
        
        $cat_Array = array();
        foreach ($all_categories as $row) {
            
            $childs = array();
            $cat_info = ['id' => $row->id, 'title' => $row->categoryTitle, 'slug' => $row->category_slug, 'description' => $row->description, 'image_url' => $row->image_id, 'status' => $row->status, 'total' => $row->totalpros , 'updated_at' => $row->updated_at];
            $all_categories_child = DB::table('categories')
            ->join('product_category', 'categories.id', 'product_category.category_id')
            ->join('products', 'products.product_id', 'product_category.product_id')
            ->where('parentCategoryID', '=', $row->id)
            ->where('categories.status', '1')
            ->where('products.status', '=', '1')
            ->groupby('categories.id')->get();
            foreach ($all_categories_child as $val) {
                $childs[] = ['id' => $val->id, 'title' => $val->categoryTitle, 'slug' => $val->category_slug, 'description' => $val->description, 'image_url' => $val->image_id, 'status' => $val->status , 'updated_at' => $val->updated_at];
            }
            
            $cat_Array[] = ['cat_info' => $cat_info, 'childs' => $childs];
        }
        return $cat_Array;
    }
function getAllCategoriesAdmin()
{
    $excluded_category_id =  env('uncategory_id'); // Replace 123 with the ID of the category you want to exclude

    $all_categories = DB::table('categories')
        ->where('parentCategoryID', '=', 0)
        ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude 'Uncategorized'
        ->get();

    $cat_Array = array();
    foreach ($all_categories as $category) {
        $childs = array();
        $cat_info = [
            'id' => $category->id,
            'title' => $category->categoryTitle,
            'slug' => $category->category_slug,
            'description' => $category->description,
            'image_url' => $category->image_id,
            'status' => $category->status,
            'parentCategoryID' => $category->parentCategoryID
        ];
        $child_count = DB::table('categories')
            ->where('parentCategoryID', '=', $category->id)
            ->count();
        $all_categories_child = DB::table('categories')
            ->where('parentCategoryID', '=', $category->id)
            ->get();
        foreach ($all_categories_child as $val) {
            $childs[] = [
                'id' => $val->id,
                'title' => $val->categoryTitle,
                'slug' => $val->category_slug,
                'description' => $val->description,
                'image_url' => $val->image_id,
                'status' => $val->status
            ];
        }
        $cat_info['child_count'] = $child_count;
        $cat_Array[] = ['cat_info' => $cat_info, 'childs' => $childs];
    }

    return $cat_Array;
}
// Get All Post Category show data in categories
function getAllPostCategoriesAdmin()
{
    $all_categories = DB::table('blog_categories')->where('category_name', '!=', 'Uncategorized')->where('parent_id', '=', 0)->get();

    $cat_Array = array();
    foreach ($all_categories as $category) {
        $childs = array();
        $cat_info = [
            'id' => $category->id,
            'title' => $category->category_name,
            'slug' => $category->category_slug,
            'description' => $category->description,
            'status' => $category->status,
            'parent_id' => $category->parent_id
        ];
        $child_count = DB::table('blog_categories')
            ->where('parent_id', '=', $category->id)
            ->count();
        $all_categories_child = DB::table('blog_categories')
            ->where('parent_id', '=', $category->id)
            ->get();
        foreach ($all_categories_child as $val) {
            $childs[] = [
                'id' => $val->id,
                'title' => $val->category_name,
                'slug' => $val->category_slug,
                'description' => $val->description,
                'status' => $val->status
            ];
        }
        $cat_info['child_count'] = $child_count;
        $cat_Array[] = ['cat_info' => $cat_info, 'childs' => $childs];
    }

    return $cat_Array;
}


function checkChildCategoriesExist($parentId) {
    $childCategories = DB::table('categories')
        ->where('parentCategoryID', '=', $parentId)
        ->count();

    return $childCategories > 0;
}

global $category_Array;

function getCategories() {

    $all_categories = DB::table('categories')
    ->LeftJoin('product_category', 'product_category.category_id', 'categories.id')
    ->LeftJoin('products', 'products.product_id', 'product_category.product_id')
    ->select(DB::raw('categories.*'))
    ->groupBy('categories.id')->where('categories.parentCategoryID', '=', 0)
    ->where('products.status', '=', '1')
    ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude 'Uncategorized'
    ->get();
    // echo 'Data: <pre>' .print_r($all_categories,true). '</pre>';
    $category_Array = array();

    foreach ($all_categories as $row) {
        $childs = array();
        $cat_info = ['id' => $row->id, 'title' => $row->categoryTitle, 'slug' => $row->category_slug];
        $all_categories_child = DB::table('categories')
        ->leftJoin('product_category', 'categories.id', 'product_category.category_id')
        ->LeftJoin('products', 'products.product_id', 'product_category.product_id')
        ->where('parentCategoryID', '=', $row->id)
        ->where('products.status', '=', '1')
        ->groupby('categories.id')
        ->get();
        // return response()->json($all_categories_child);
        foreach ($all_categories_child as $val) {

            $childs[] = ['id' => $val->id, 'title' => $val->categoryTitle, 'slug' => $val->category_slug, 'parentid' => $row->id];
        }
        $category_Array[] = ['parent_category' => $cat_info, 'childs' => $childs, ];
    }
    return $category_Array;
}

function getSideNavCategories() {

    $all_headings = DB::table('categories')
    ->join('product_category', 'product_category.category_id', 'categories.id')
    ->join('products', 'products.product_id', 'product_category.product_id')
    ->select('categories.*') // Select all columns from the categories table
    ->where('categories.parentCategoryID', '=', 0)
    ->where('products.status', '=', '1')
    ->where(function($query) {
        $query->where('categories.category_heading', '!=', '')
              ->where('categories.category_heading', '!=', '0');
    })
    
    
    ->where('categories.status', '1')
    ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude 'Uncategorized'
    ->groupBy('categories.category_heading') // Group by category_heading for unique records
    ->get();
    $category_Array = array();
    $counterId = 1;
    
    foreach ($all_headings as $row) {
       
       
        $parentArray = array();
        $categories = array();
        $cat_info = ['counterId' => $counterId,'heading' => $row->category_heading];
        $parent_categories = DB::table('categories')
        ->join('product_category', 'product_category.category_id', 'categories.id')
        ->join('products', 'products.product_id', 'product_category.product_id')
        ->where('categories.status', '1')
        ->where('category_heading', '=', $row->category_heading)
        ->groupby('categories.id')
        ->get();
        // echo 'Data: <pre>' .print_r($parent_categories,true). '</pre>';
        $childs = array();
        foreach($parent_categories as $parent){
            if ($parent->parentCategoryID != 0) {
                // Get parent category slug
                $parent_category_info = DB::table('categories')
                    ->where('id', '=', $parent->parentCategoryID)
                    ->first(); // Get the parent category
    
                // Add parent category slug to the array if it exists
                $parentCategorySlug = $parent_category_info ? $parent_category_info->category_slug : null;
            } else {
                $parentCategorySlug = null; // No parent
            }
            $categories[] = ['counterId' => $counterId , 'id' => $parent->id, 'title' => $parent->categoryTitle, 'slug' => $parent->category_slug, 'heading' => $parent->category_heading, 'parentid' => $parent->parentCategoryID,'parent_slug' => $parentCategorySlug ];
            
        }
        
        $category_Array[] = ['headings' => $cat_info, 'categories' => $categories];
        $counterId ++ ;
        
    }
    // echo 'Data: <pre>' .print_r($category_Array,true). '</pre>';
    return $category_Array;
}
function getCategoriesApi() {

    $all_categories = DB::table('categories')
   
    ->select(DB::raw('categories.*'))
  ->where('categories.parentCategoryID', '=', 0)
    ->where('categories.categoryTitle', '!=', 'Uncategorized') // Exclude 'Uncategorized'
    ->get();
    // dd($all_categories);
    $category_Array = array();

    foreach ($all_categories as $row) {
        $childs = array();
        $cat_info = ['id' => $row->id, 'title' => $row->categoryTitle, 'slug' => $row->category_slug, ];
        $all_categories_child = DB::table('categories')
        ->where('parentCategoryID', '=', $row->id)
        ->get();
        // return response()->json($all_categories_child);
        foreach ($all_categories_child as $val) {
        // return response()->json($val);

            $childs[] = ['id' => $val->id, 'title' => $val->categoryTitle, 'slug' => $val->category_slug, 'parentid' => $row->id];
        }
        $category_Array[] = ['parent_category' => $cat_info, 'childs' => $childs, ];
    }
    return $category_Array;
}
global $category_Home;

function getCategoriesHome() {

    $all_categories = DB::table('categories')
        ->join('product_category', 'product_category.category_id', 'categories.id')
        ->join('products', 'products.product_id', 'product_category.product_id')
        ->select(DB::raw('categories.*'))
        ->groupBy('categories.id')
        // ->where('categories.parentCategoryID', '=', 0)  // Top-level categories
        ->where('categories.categoryTitle', '!=', 'Uncategorized')  // Exclude Uncategorized
        ->where('categories.status', '1')  // Active categories
        ->whereNotNull('sort_order')  // Ensure sort_order is not null
        ->orderBy('sort_order', 'asc')  // Order by sort order
        ->limit(12)  // Limit results to 12
        ->get();

    $category_Home = array();

    foreach ($all_categories as $row) {
        // $products = DB::table('products')
        $childs = array();
        $cat_info = ['id' => $row->id, 'title' => $row->categoryTitle, 'slug' => $row->category_slug, 'image' => $row->image_id ];
        $all_categories_child = DB::table('categories')
        ->join('product_category', 'product_category.category_id', 'categories.id')
        ->join('products', 'products.product_id', 'product_category.product_id')
        ->where('categories.status', '1')
        ->where('parentCategoryID', '=', $row->id)
        ->where('products.status', '=', '1')
        ->groupby('categories.id')
        
        ->get();

        foreach ($all_categories_child as $val) {
            $childs[] = ['id' => $val->id, 'title' => $val->categoryTitle, 'slug' => $val->category_slug, 'parentid' => $row->id];
        }
        $category_Home[] = ['parent_category' => $cat_info, 'childs' => $childs, ];
    }
    // echo 'Data: <pre>' .print_r($category_Home,true). '</pre>'; die();
    return $category_Home;
}

function getChildCategoryParent($childCategorySlug) {
    $result = DB::table('categories')->where('category_slug', $childCategorySlug)->select('parentCategoryID')->first();
    if ($result) {
        $catId = $result->parentCategoryID;
        // Ensure $catId is not null or zero
        if ($catId) {
            $parentCategory = DB::table('categories')->where('id', $catId)->select('category_slug', 'categoryTitle')->first();
            if ($parentCategory) {
                return $parentCategory;
            }
        }
    }
    return false;
}

function getChildCategory($childCategorySlug) {
    $result = DB::table('categories')->where('category_slug', $childCategorySlug)->select('category_slug', 'categoryTitle')->first();
    if ($result) {
       return $result;
    }
    return false;
}

function getStoreFollowers($storeId)
{
    $result = DB::table('favourite_stores')
        ->where('store_id', $storeId)
        ->count();

    return $result;
}
/*
 * Get category slug by description
 */

function getDescriptionCategorySlug($CategorySlug) {
    $result = DB::table('categories')->where('category_slug', $CategorySlug)->select('description')->first();
  
    if ($result) {
        return $result;
    }

}


/*
 * fav products
 */

function getBuyerFavProducts() {
    $favArr = array();
    $userId = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']:'';
    $results='';
    if($userId){
    $results = DB::table('favourites')
            ->select('product_id')
            ->where('user_id', '=', $userId)
            ->get();

    }
    if (!empty($results)) {
        $results = objToArray($results);
        foreach ($results as $rs) {
            $favArr[] = $rs['product_id'];
        }
        return $favArr;
    } else {
        return false;
    }
}
/*
 * fav Stores
 */

 function checkFavouriteStore($store_id,$user_id){
    $favourite = FavouriteStore::where(['store_id' => $store_id,'user_id'=>$user_id])->first();
    if(isset($favourite)){
        return true;
    }else{
        return false;
    }
 }

function getBuyerFavStores() {
    $favArr = array();
    $userId = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']:'';
    $results='';
    if($userId){
    $results = DB::table('favourite_stores')
            ->select('store_id')
            ->where('user_id', '=', $userId)
            ->get();

    }
    if (!empty($results)) {
        $results = objToArray($results);
        foreach ($results as $rs) {
            $favArr[] = $rs['store_id'];
        }
        return $favArr;
    } else {
        return false;
    }
}

/*
 * featured products
 */

function getFeaturedProducts() {
    $featuredArr = array();
    $results='';

    $results = DB::table('products')
            ->select('*')
            ->where('is_feature', '=', '1')
            ->get();

    if (!empty($results)) {
        $results = objToArray($results);
        foreach ($results as $rs) {
            $featuredArr[] = $rs;
        }
        return $featuredArr;
    } else {
        return false;
    }
}


/*
  get attachment url by attachment id method 1
 */

function getAttachmentUrlById($id, $size = 'media') {
    $global_obj = new Attachments();
    if(isset($id))$id=json_decode($id);
    if ($id != '' && is_int($id)) {
        $global_obj->id = $id;
        $global_obj->size = $size;
        return $global_obj->getAttachmentUrlById();
    } else {
        return 'Please add a valid id';
    }
}
function getAttachmentUrlByName($image){
    if($image != ''){
        return $imageurl = url('uploads/temp/'. $image);
    }
}
function getAttachmentUrlByIdApi($id, $size = 'media') {
    $global_obj = new Attachments();
    if(isset($id))
    $id=json_decode($id);
    if ($id != ''){
        if(is_int($id)) {
            $global_obj->id = $id;
            $global_obj->size = $size;
            return $global_obj->getAttachmentUrlByIdApi();
        }else {
            return false;
        }
    } else {
        return 'Please add a valid id';
    }
}
function getAttachmentUrlSmallById($id, $size = 'small') {
    $global_obj = new Attachments();
    if ($id != '' && is_int($id)) {
        $global_obj->id = $id;
        $global_obj->size = $size;

        return $global_obj->getAttachmentUrlById();
    } else {
        return 'Please add a valid id';
    }
}
function getAttachmentUrlByIdEmailTemplete($id, $size = 'media') {


    $global_obj = new Attachments();

    if ($id != '' && is_int($id)) {
        $global_obj->id = $id;
        $global_obj->size = $size;

        return $global_obj->getAttachmentUrlByIdEmailTempletes();
    } else {
        return 'Please add a valid id';
    }
}
function getAttachmentUrlByIdAvatarApi($id, $size = 'avatar') {


    $global_obj = new Attachments();

    if ($id != '' && is_int($id)) {
        $global_obj->id = $id;
        $global_obj->size = $size;

        return $global_obj->getAttachmentUrlByIdAvatarApi();
    } else {
        return 'Please add a valid id';
    }
}
function getAttachmentUrlByIdMedia($id, $size = 'media') {


    $global_obj = new Attachments();

    if ($id != '' && is_int($id)) {
        $global_obj->id = $id;
        $global_obj->size = $size;

        return $global_obj->getAttachmentUrlByIdEmailTempletes();
    } else {
        return 'Please add a valid id';
    }
}
function getAttachmentUrlforEmail($id, $size = 'media') {


    $global_obj = new Attachments();

    if ($id != '' && is_int($id)) {
        $global_obj->id = $id;
        $global_obj->size = $size;

        return $global_obj->getAttachmentUrlByIdAvatarApi();
    } else {
        return 'Please add a valid id';
    }
}


/*
  get image id by icon id
 */

function getImageIdByIconId($id) {


    $global_obj = new StoreIcon();

    if ($id != '' && is_int($id)) {
        $global_obj->id = $id;

        return $global_obj->getImageIdByIconId();
    } else {
        return 'Please add a valid id';
    }
}

/*
 * Get categories by product id
 */

function getCategoriesByProductID($ProductID) {

    $ObjeCatogry = new CategoryProduct();

    $getAllProductCategory = $ObjeCatogry->where('product_id', $ProductID)
            ->select('category_id')
            ->get()
            ->toArray();
    return $getAllProductCategory;
}

function getCategoriesByProductIDGTM($ProductID) {

    $ObjeCatogry = new CategoryProduct();

    $getAllProductCategory = $ObjeCatogry->where('product_id', $ProductID)
            ->select('category_id')
            ->get()
            ->first();
    return $getAllProductCategory;
}
 function getCategoryByCateId($cate_id){
    $getAllProductCategory = DB::table('categories')->where('categories.id', $cate_id)->first();
    if (!empty($getAllProductCategory)) {
        return objToArray($getAllProductCategory);
    } else {
        return false;
    }
 }
/*
 * Get categories Name by product id
 */

function getCategoriesNameByProductID($id) {

    $ObjeCatogry = new category();
    $getAllProductCategory = DB::table('categories')->where('categories.id', $id)->get()->toArray();
    $cat_Array = array();
    foreach ($getAllProductCategory as $row) {
        $childs = array();
        $cat_info = ['id' => $row->id, 'title' => $row->categoryTitle, 'slug' => $row->category_slug, 'description' => $row->description, 'image_url' => $row->image_id, 'status' => $row->status];
        $all_categories_child = DB::table('categories')->leftJoin('product_category', 'categories.id', 'product_category.category_id')->LeftJoin('products', 'products.product_id', 'product_category.product_id')->where('parentCategoryID', '=', $row->id)->where('products.status', '=', '1')->groupby('categories.id')->get();
        foreach ($all_categories_child as $val) {
            $childs[] = ['id' => $val->id, 'title' => $val->categoryTitle, 'slug' => $val->category_slug, 'description' => $val->description, 'image_url' => $val->image_id, 'status' => $val->status];
        }
        $cat_Array[] = ['cat_info' => $cat_info, 'childs' => $childs];
    }
    return $cat_Array;
}

function getUserById($user_id = '') {
    if ($user_id != '') {
        return $result = DB::table('users')->where('user_id', '=', $user_id)->first();
    }
}
function getUserByIdApi($user_id = '') {
    if ($user_id != '') {
        return $result = DB::table('users')->select('user_id', 'name', 'email', 'status', 'ip_address', 'user_zip_code', 'contact_no', 'physical_address', 'about', 'user_country_id', 'user_state_id', 'user_city_id as city' )->where('user_id', '=', $user_id)->first();
    }
}

function getUserRolesId($roleId, $userId = "") {
    $arr = "";
    if (Session::has('userData') || !empty($userId)) {
        if (empty($userId)) {
            $userId = Session::get('userData')['user_id'];
        }
    // echo '$userId: <pre>' .print_r($userId,true). '</pre>'; 
    // echo '$roleId: <pre>' .print_r($roleId,true). '</pre>'; die;


        $count = DB::table('role_user')
                ->where('user_id', $userId)->where('role_id', '=', $roleId)
                ->first();
            //    echo '$count: <pre>' .print_r($count,true). '</pre>'; die;
        if (!empty($count)) {
            return objToArray($count);
        } else {

            $arr = [];
            return $arr;
        }
    } else {
      
        $arr = [];
        return $arr;
    }
}

function getUserroles($userId) {

//    $roles = DB::table('role_user')->join('roles', 'roles.role_id', '=', 'role_user.role_id')->where('role_user.user_id', '=', $userId)->orderBy('role_user.role_user_id', 'ASC')->get()->toArray();
    $roles = DB::table('role_user')->where('user_id', '=', $userId)->orderBy('role_user_id', 'ASC')->get()->toArray();
    $roleResult = objToArray($roles);
    return $roleResult;
}

function objToArray($arr) {

    $arr = json_encode($arr);
    $res = json_decode($arr, true);
    return $res;
}

function print_rr($arr) {
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}

function getAllStoreData() {
    $storeData = DB::table('stores')->get()->toArray();

    if (!empty($storeData)) {
        foreach ($storeData as $key => $data) {
            $storeData[$key]->policies_status = $data->policies;
            if ($data->policies == 'Yes' || $data->policies == '') {
                $policies = DB::table('content_pages')
                        ->where('page_slug', '=', 'shop-policies')
                        ->first();

                if (!empty($policies)) {
                    $page_content = $policies->page_content;
                    $page_content = preg_replace('~>\s+<~', '><', $page_content);
                    if (!empty($page_content)) {
                        $storeData[$key]->policies = $policies->page_content;
                    }
                }
            }
        }
    }

    if (!empty($storeData)) {
        return $storeData;
    } else {
        return false;
    }
}
 function getStorDataUserId($userId){
    $query = DB::table('stores')->where('user_id', '=', $userId)->first();
   
    if (!empty($query)) {
        return $query;
    } else {
        return false;
    }
 }

function getStoreDataByUserId($userId, $status = "") {
    $query = DB::table('stores')
            ->where('user_id', '=', $userId);
    if (!empty($status)) {
        $query->whereIn('store_status', ['1', '2']);
    }
    $storeData = $query->first();


    if ($status == "" && !empty($storeData)) {
        $storeData->policies_status = $storeData->policies;
        if ($storeData->policies == 'Yes' || $storeData->policies == '') {
            $policies = DB::table('content_pages')
                    ->where('page_slug', '=', 'shop-policies')
                    ->first();

            if (!empty($policies)) {
                $page_content = $policies->page_content;
                $page_content = preg_replace('~>\s+<~', '><', $page_content);
                if (!empty($page_content)) {
                    $storeData->policies = $policies->page_content;
                }
            }
        }
    }

    if (!empty($storeData)) {
        return $storeData;
    } else {
        return false;
    }
}

function getStoreDataByProductId($productId, $status = "") {
    // First, get the store_id from the products table using the product_id
    $storeId = DB::table('products')->where('product_id', $productId)->value('store_id');

    // If no store_id is found, return false
    if (empty($storeId)) {
        return false;
    }

    // Now, get the store data using the store_id
    $query = DB::table('stores')->where('store_id', '=', $storeId);

    if (!empty($status)) {
        $query->whereIn('store_status', ['1', '2']);
    }

    $storeData = $query->first();

    if ($status == "" && !empty($storeData)) {
        $storeData->policies_status = $storeData->policies;
        if ($storeData->policies == 'Yes' || $storeData->policies == '') {
            $policies = DB::table('content_pages')->where('page_slug', '=', 'shop-policies')->first();

            if (!empty($policies)) {
                $page_content = $policies->page_content;
                $page_content = preg_replace('~>\s+<~', '><', $page_content);
                if (!empty($page_content)) {
                    $storeData->policies = $policies->page_content;
                }
            }
        }
    }

    return !empty($storeData) ? $storeData : false;
}

function getOrderItemsByOrderId($order_id){
   $result = DB::table('order_items')->where("order_id", $order_id)->get();
   if(count($result) > 0) {
    return $result;
   }
}
function getStoreOrder($order_id){
   $result = DB::table('store_orders')->where('charge_status','paid')->where("order_id", $order_id)->first();
 
    return $result;
   
}

function getOrdersCount($userId = '', $role = '', $status = '') {
    if ($userId != '' && $role != '' && $status != '') {
        if ($role == 'buyer') {
            return $results = DB::table('store_orders')->where('buyer_id', $userId)->where('order_status', $status)->count();
        }
        if ($role == 'store') {
            return $results = DB::table('store_orders')->where('store_id', $userId)->where('order_status', $status)->count();
        }
    } else {
        return 'parameters mis matched';
    }
}
//new
function getBuyerOrdersCount($storeid, $userId = '') {
    if ($userId != '') {
        // $userId = Session::get('userData')['user_id'];
             $results = DB::table('store_orders')->where('buyer_id', $userId)->where('charge_status','paid')
             ->where('store_id' , $storeid)->count();
            
            return $results;
        
        
    } else {
        return 'parameters mis matched';
    }
}

function getAllCountries() {
    $query = DB::table('countries')->get();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}

function getCountryById($countryId = '') {
    $query = DB::table('countries')->where('id', $countryId)->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}
function getCountryByName($countryName = '') {
    $query = DB::table('countries')->where('name', $countryName)->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}
function getStatesOfCountry($countryId = '') {
    $query = DB::table('states')->where('country_id', $countryId)->get();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}

function getStatebyId($stateId = '') {
    $query = DB::table('states')->where('state_id', $stateId)->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}

function getAllStates() {
    $query = DB::table('states')->where('country_id', '1')->get();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}

function aboutStore($store_id) {
    $res = DB::table('stores')->select('about_store')->where('store_id', $store_id)->get()->toArray();
    if ($res[0]->about_store != '') {
        return true;
    } else {

        return false;
    }
}

function getCurrentUserId() {
    if (Session::has('userData')) {
        $userId = Session::get('userData')['user_id'];
        return $userId;
    } else {
        return false;
    }
}

function getCurrentUserRoleId() {
    if (Session::has('userData')) {
        $userId = Session::get('userData')['user_id'];
        $role = DB::table('role_user')
                ->where('user_id', $userId)
                ->orderBy('role_user_id', 'DESC')
                ->first();
        return $role;
    } else {
        return false;
    }
}
function getCurrentUserRoleIdApi() {
    $userId = auth()->user()->user_id;
    if (isset($userId) && !is_null($userId)) {
        // $userId = Session::get('userData')['user_id'];
        $role = DB::table('role_user')
                ->where('user_id', $userId)
                ->orderBy('role_user_id', 'DESC')
                ->first();
        return $role;
    } else {
        return false;
    }
}
function getUserHerdsNotNull() {
    $userID = getCurrentUserId();
    $constants = config('constants.shopzit_gamification');
    $userHerds = DB::table('herds')->Join('herd_users', 'herds.herd_id', 'herd_users.user_herd_id')->Join('stores', 'herds.icon_id', 'stores.store_icon')->where('herds.user_id', $userID)->select('stores.store_id')->get()->toArray();
    $arr = array();
    if (count($userHerds) > 0) {
        foreach ($userHerds as $hrd) {
            $arr[] = $hrd->store_id;
        }
    }
    return $arr;
}
function getCategoryUncategorized(){
    $selectedCategory = DB::table('categories')
    ->where('categoryTitle', '=', 'Uncategorized')
    ->get()
    ->toArray();
    if (count($selectedCategory) > 0) {

    return $selectedCategory[0];
    }
}
// For Blog Categories
function getBlogCategoryUncategorized(){
    $selectedCategory = DB::table('blog_categories')
    ->where('category_name', '=', 'Uncategorized')
    ->get()
    ->toArray();
    if (count($selectedCategory) > 0) {

    return $selectedCategory[0];
    }
}

function getCategorySlugById($Id) {
    if ($Id != '') {
//$cat_id = DB::table('categories')->select('id')->where('categoryTitle','=',$category_name);
        $cat_slug = category::where('id', $Id)
                ->select('category_slug')
                ->get()
                ->toArray();
        if (count($cat_slug) > 0) {

            return $cat_slug[0]['category_slug'];
        } else {
            return 'Please give a valid category id';
        }
    } else {
        return 'Please give a valid  id';
    }
}

function cartDetail() {
    $data = array();
    $cartDetail='';
    if (!empty($_COOKIE['cartCookie'])) {
        $key = $_COOKIE['cartCookie'];
         }
        // $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store'])->where('random_key', $key)->get();
    // $userId = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']:'';
    if(!empty($key)){
    $query = DB::table('products_cart')
            ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
            ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
            ->select('products.product_id', 'products.store_id', 'products.user_id', 'products.title', 'products.price','products.sale_price','products.on_sale','products.product_type', 'products.slug', 'products.feature_image',
             'products_cart.cart_id', 'products_cart.product_quantity', 'product_variation.variation_id', 'product_variation.option1',
             'product_variation.attribute1','product_variation.attribute2','product_variation.attribute3', 
             'product_variation.unit1', 'product_variation.option2', 'product_variation.unit2', 'product_variation.option3', 'product_variation.unit3', 
             'product_variation.price as variation_sale_price','product_variation.sale_price as product_variation_sale_price','product_variation.product_variation_image')
            ->where('products_cart.random_key', $key);
    $cartDetail = $query->get();
    }
    if (!empty($cartDetail)) {
        $data['cart_data'] = objToArray($cartDetail);
        $data['total_quantity'] = DB::table('products_cart')->where('random_key', $key)->sum('product_quantity');
        //  echo 'Data: <pre>' .print_r($data['total_quantity'],true). '</pre>'; 
        return $data;
    } else {
        return false;
    }
}
function cartDetailCrypto() {
    $data = array();
    $cartDetail='';
    if (!empty($_COOKIE['cartCookieCrypto'])) {
        $key = $_COOKIE['cartCookieCrypto'];
         }
        // $cartDetail = ProductCart::with(['product', 'variation', 'store', 'product.store'])->where('random_key', $key)->get();
    // $userId = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']:'';
    if(!empty($key)){
    $query = DB::table('products_cart')
            ->Join('products', 'products.product_id', '=', 'products_cart.product_id')
            ->LeftJoin('product_variation', 'product_variation.variation_id', '=', 'products_cart.variation_id')
            ->select('products.product_id', 'products.store_id', 'products.user_id', 'products.title', 'products.price','products.product_type', 'products.slug', 'products.feature_image',
             'products_cart.cart_id', 'products_cart.product_quantity', 'product_variation.variation_id', 'product_variation.option1',
             'product_variation.attribute1','product_variation.attribute2','product_variation.attribute3', 
             'product_variation.unit1', 'product_variation.option2', 'product_variation.unit2', 'product_variation.option3', 'product_variation.unit3', 
             'product_variation.price as variation_sale_price','product_variation.sale_price as product_variation_sale_price','product_variation.product_variation_image')
            ->where('products_cart.crypto_random', $key);
    $cartDetail = $query->get();
    }
    if (!empty($cartDetail)) {
        $data['cart_data'] = objToArray($cartDetail);
        $data['total_quantity'] = DB::table('products_cart')->where('crypto_random', $key)->sum('product_quantity');
        // echo 'Data: <pre>' .print_r($data['total_quantity'],true). '</pre>'; 
        return $data;
    } else {
        return false;
    }
}

function getProductById($product_id = '') {
    if ($product_id != '') {
        return $result = DB::table('products')->where('product_id', '=', $product_id)->get()->toArray();
    }
}
function getProductId($product_id = '') {
    if ($product_id != '') {
        // return $result = DB::table('products')->where('product_id', '=', $product_id)->get()->toArray();
        return $results = Product::where('product_id', '=', $product_id)->first();
    }
}
function getProductIdSpecificColums($product_id = '') {
    if ($product_id != '') {
        // return $result = DB::table('products')->where('product_id', '=', $product_id)->get()->toArray();
        return $results = Product::where('product_id', '=', $product_id)->select('product_id', 'title', 'slug', 'feature_image', 'product_type')->first();
    }
}

function getUserByIdchat($user_id = '') {
    if ($user_id != '') {
        return $result = DB::table('users')->select('user_id as id', 'name', 'contact_no', 'email', 'physical_address', 'zendesk_user_id', 'created_at', 'updated_at')->where('user_id', '=', $user_id)->first();
    }
}
function getGuestUserById($user_id = '') {
    if ($user_id != '') {
        return $result = DB::table('users')->select('user_id as id', 'name', 'contact_no', 'email', 'physical_address', 'zendesk_user_id', 'created_at', 'updated_at')->where('user_id', '=', $user_id)->where('guest_user',1)->first();
    }
}

function getUserIdbyOrderId($orderId) {
    $query = DB::table('store_orders')
                    ->select('stores.user_id')->
                    join('stores', 'stores.store_id', '=', 'store_orders.store_id')->where('store_orders.order_id', '=', $orderId)->first();
    if (!empty($query)) {
        return objToArray($query);
    }
}

function getBuyerIdbyOrderId($orderId) {
    $query = DB::table('store_orders')->where('order_id', '=', $orderId)->select('buyer_id')->first();
    if (!empty($query)) {
        return objToArray($query);
    }
}

function getUserIdBy($name_email) {

    if (filter_var($name_email, FILTER_VALIDATE_EMAIL)) {
        $result = DB::table('users')
                ->where('email', '=', $name_email)
                ->select('user_id')
                ->get()
                ->toArray();
    } else {
        $result = DB::table('users')
                ->where('name', 'like', '%' . $name_email . '%')
                ->select('user_id')
                ->get()
                ->toArray();
    }

    $arr = array();

    if (count($result) > 0) {
        if (count($result) == 1) {
            $arr[] = $result[0]->user_id;
        } else {
            foreach ($result as $res) {
                $arr[] = $res->user_id;
            }
        }
    }
    /*
      echo '<pre>';
      print_r($arr);
      die();
     * 
     */
    return $arr;
}

function recentProducts($count = 6) {
    return $products = DB::table('products')
            ->orderBy('products.created_at', 'DESC')
//->OFFSET($page * $limit)
            ->limit($count)
//->where('productType', '=', 'product')
            ->where('status', '=', '1')
// ->where('is_active', '=', 1)
            ->select('*')
            ->get();
}

function topSellingProducts($count = 6) {
    return $products = DB::table('products')
            ->LeftJoin('stores', 'stores.store_id', 'products.store_id')
            ->orderBy('products.sales', 'DESC')
            //->OFFSET($page * $limit)
            ->limit($count)
            //->where('productType', '=', 'product')
            ->where('status', '=', '1')
            // ->where('is_active', '=', 1)
            ->select('products.*', 'stores.store_icon', 'stores.store_slug', 'stores.store_name')
            ->get();
}

function isImageExists($image_url) {
    $image_url = str_replace(url('/'), public_path(), $image_url);
    if (file_exists($image_url)) {
        return true;
    } else {
        return false;
    }
}

function getProductRatingById($product_id = '') {
    if ($product_id != '') {
        $reviews = DB::table('reviews')
                ->where('product_id', '=', $product_id)
                ->select('rating')
                ->get()
                ->toArray();
        $numer = 0;
        $average = 0;
        $result = array();
        if (count($reviews) > 0) {
            foreach ($reviews as $rev) {

                $numer += $rev->rating;
            }

            $average = round($numer / count($reviews));
            $result['average'] = $average;
            $result['total'] = count($reviews);
        }
        return $result;
    } else {
        echo 'please provide a valid product id';
    }
}
 function get_reviews($p_id='') {
      
    if ($p_id == '') {
        return 'No record found';
    }
    return $all_reviews = DB::table('reviews')->where('product_id', '=', $p_id)->orderBy('created_at','DESC')->get();
}
function getProductTotalRatingById($product_id = '') {
    if ($product_id != '') {
        $reviews = DB::table('reviews')
                ->where('product_id', '=', $product_id)
                ->select('rating')
                ->get()
                ->toArray();
        $result = array();
        if (count($reviews) > 0) {
            $result['total'] = count($reviews);
        }else{
            $result['total'] = count($reviews);
        }
        $result['total'] = convertNumber(count($reviews));
        return $result;
    } else {
        echo 'please provide a valid product id';
    }
}
function convertNumber($number) {
    $suffixes = ['', 'k', 'M', 'B', 'T'];
    $suffixIndex = 0;

    while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
        $number /= 1000;
        $suffixIndex++;
    }

    $formattedNumber = number_format($number, ($number >= 100 ? 0 : 2), '.', ',');
    $formattedNumber = rtrim($formattedNumber, '0');
    $formattedNumber = rtrim($formattedNumber, '.');

    $suffix = $suffixes[$suffixIndex];

    return $formattedNumber . ' ' . $suffix;
}


function getSupporters($productId) {
    return $supporters = DB::table('order_items')
            ->join('store_orders', 'store_orders.order_id', 'order_items.order_id')
            ->where('order_items.item_id', '=', $productId)
            ->join('users', 'users.user_id', 'store_orders.buyer_id')
            ->select('store_orders.order_id', 'users.user_id', 'users.name', 'users.user_avatar', 'users.user_state_id', 'users.user_country_id')
            ->get()
            ->toArray();

//die();
}

function getProductAuthorById($productId) {
    $products = DB::table('products')
            ->where('product_id', '=', $productId)
            ->select('user_id')
            ->first();

    if ($products) {
        $userId = $products->user_id;
        return getUserById($userId);
    } else {
        return false;
    }
}

function getAendeskUserInfo($id = "") {
    $result = Zendesk::users()->find($id);
    if (!empty($result)) {
        return $result->user;
    }
}

function getCategoriesBystoreId($storeId, $parent_id = 0) {
    $data = DB::table('products')->
            select('categories.*')->
            join('product_category', 'products.product_id', '=', 'product_category.product_id')->
            join('categories', 'categories.id', '=', 'product_category.category_id')
            ->where('products.store_id', '=', $storeId);
// ->groupBy('product_category.id');
    $catArray = array();
    $val = $data->get();
    foreach ($val as $d) {
        if ($d->parentCategoryID > 0) {
            if (!in_array($d->parentCategoryID, $catArray)) {
                array_push($catArray, $d->parentCategoryID);
            }
        } else {
            if (!in_array($d->id, $catArray)) {
                array_push($catArray, $d->id);
            }
        }
    }
    return $all_categories = DB::table('categories')->whereIn('id', $catArray)->pluck('categoryTitle', 'category_slug');
}

function getCategoriesBystoreIdApi($storeId, $parent_id = 0) {
   
$data = DB::table('products')
    ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug','categories.image_id')
    ->join('product_category', 'products.product_id', '=', 'product_category.product_id')
    ->join('categories', 'categories.id', '=', 'product_category.category_id')
    ->where('products.store_id', '=', $storeId)
    ->where('categories.parentCategoryID', '=', 0);

$catArray = array();
$val = $data->get();
// Organize categories into a nested array
$categories = [];
foreach ($val as $row) {
    $imageUrl = getAttachmentUrlByIdApi(intval($row->image_id),'medium');
    $childs = array();
    $cat_info = [
        'id' => $row->id,
        'title' => $row->categoryTitle,
        'slug' => $row->category_slug,
        'image' => $imageUrl,
    ];
    $all_categories_child = DB::table('products')
    ->select('categories.id', 'categories.categoryTitle', 'categories.category_slug')
    ->join('product_category', 'products.product_id', '=', 'product_category.product_id')
    ->join('categories', 'categories.id', '=', 'product_category.category_id')
    ->where('products.store_id', '=', $storeId)
    ->where('categories.parentCategoryId', '=', $row->id)
    ->whereNotNull('product_category.product_id') // Ensure product_id exists in productCategory table
    ->get();


    foreach ($all_categories_child as $child) {
        $childs[] = [
            'id' => $child->id,
            'title' => $child->categoryTitle,
            'slug' => $child->category_slug,
            'parentid' => $row->id,
        ];
    }

    $categories[] = [
        'parent_category' => $cat_info,
        'childs' => $childs,
    ];
}

return $categories;


}

function getAllCategoriesParent() {
    
    return $all_categories = DB::table('categories')->where('parentCategoryID', '=', 0)->pluck('categoryTitle', 'id');
}
// Get All Post Category Parent
function getAllPostCategoriesParent() {
    return DB::table('blog_categories')
        ->select('category_name', 'id', 'parent_id')
        ->where('category_name', '!=', 'Uncategorized')
        ->get();
}
function getAllParentCategories($limit) {

    return $all_categories = DB::table('categories')->where('parentCategoryID', '=', 0)->take($limit)->orderByRaw('updated_at - created_at DESC')->get()->toArray();
}

function getAllActiveCategories($limit) {
    return $all_categories = DB::table('categories')->where('status', '=', '1')->take($limit)->orderByRaw('updated_at - created_at DESC')->get()->toArray();
}
function getSideCategories($limit) {
    return $all_categories = DB::table('categories')
    ->LeftJoin('product_category', 'product_category.category_id', 'categories.id')->LeftJoin('products', 'products.product_id', 'product_category.product_id')->select(DB::raw('count(product_category.category_id) as totalpros, categories.*'))->groupBy('categories.id')->where('categories.parentCategoryID', '=', 0)->where('products.status', '=', '1')->limit($limit)->get();
    // return $all_categories = DB::table('categories')->where('parentCategoryID', '=', 0)->where('status', '=', '1')->take($limit)->orderByRaw('updated_at - created_at DESC')->get()->toArray();
}

function getUsersRoles($userId, $roleId = "") {
    $query = DB::table('role_user')->
            join('roles', 'role_user.role_id', '=', 'roles.role_id')
            ->where('user_id', $userId);
    if (isset($roleId) && $roleId != "") {
        $query->where('role_user.role_id', $roleId);
    }
    $userRole = $query->get();
    if (!empty($userRole)) {
        return $userRole = objToArray($userRole);
    } else {
        return false;
    }
}

function getRedirectUrl() {
    $redirectUrl = url()->previous();
    $exploded_url = explode('/', $redirectUrl);

    $redirect = true;
    if (in_array('change-password', $exploded_url) && count($exploded_url) == 5) {

        $redirect = false;
    }
    if (in_array('validate-email', $exploded_url) && count($exploded_url) == 5) {

        $redirect = false;
    }
    $denied = array(URL::to('login'), URL::to('access-denied'), URL::to('page-notfound'), URL::to('store/deactivate'), URL::to('signup'), URL::to('forget-password'), 'http://shopzit.mindblaze.net/forget-password', 'http://shopzit.mindblaze.net/login','http://shopzit.mindblaze.net/forget-password?q=%2Fforget-password');
       
    if (!in_array($redirectUrl, $denied)) {
        if ($redirect) {
          
            Session::put('redirect_url', $redirectUrl);
        }
    }
}

function rediredctUrls() {
//$redirectUrl = request()->url();
    $redirectUrl = url()->previous();
    $exploded_url = explode('/', $redirectUrl);
    $urlSegment = isset($exploded_url[3]) ? $exploded_url[3] : '';
    $sencondSegment = isset($exploded_url[4]) ? $exploded_url[4] : '';
    $denied = array('login', 'validate-email', 'access-denied', 'page-notfound', 'store/deactivate', 'signup', 'forget-password', 'change-password' , 'forget-password?q=%2Fforget-password','signup?q=%2Fregister');
    if ($urlSegment != "" && in_array($urlSegment, $denied)) {
        
    } else if ($sencondSegment != "" && in_array($sencondSegment, $denied)) {
        
    } else {

        Session::put('redirect_url', $redirectUrl);
    }
//echo $pageUrl = Session::get('redirect_url');
}

function getFilterData($filterId) {
    $query = DB::table('filters')
                    ->where('filter_id', '=', $filterId)->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}

function getmess() {
    if (Session::has('userData')) {
        $arr = array();
        $userId = Session::get('userData')['user_id'];
        $query = DB::table('messages')->where('receiver_id', $userId)->where('is_seen', 0);
        if (!empty($query)) {
            $res = objToArray($query->get());
            foreach ($res as $rs) {
                if (!in_array($rs['user_id'], $arr)) {
                    $arr[] = $rs['user_id'];
                }
            }
            return $arr;
        } else {
            return false;
        }
    }
}

function getStorebyUserInfo($userID){
    $res = DB::table('users')->select('name', 'email')->where('user_id', $userID)->first();
    if (!empty($res)) {
        return $res;
    } else {
        return false;
    }
} 

function getStoreInfoById($storeID) {
    $res = DB::table('stores')->select('*')->where('store_id', $storeID)->get()->toArray();
    //print_rr($res); exit;
    if (isset($res[0]->policies) && $res[0]->policies == 'Yes') {
        $policies = DB::table('content_pages')
                ->where('page_slug', '=', 'shop-policies')
                ->first();

//        //print_rr($policies->page_content); exit;
        if (!empty($policies)) {
            $page_content = $policies->page_content;
            $page_content = preg_replace('~>\s+<~', '><', $page_content);
            if (!empty($page_content)) {
                $res[0]->shop_policies = $policies->page_content;
            }
        }
    }
    return $res;
}
function getStoreInfoBySlug($slug) {
    $res = DB::table('stores')->select('*')->where('store_slug', $slug)->get()->toArray();
    //print_rr($res); exit;
    if (isset($res[0]->policies) && $res[0]->policies == 'Yes') {
        $policies = DB::table('content_pages')
                ->where('page_slug', '=', 'shop-policies')
                ->first();

//        //print_rr($policies->page_content); exit;
        if (!empty($policies)) {
            $page_content = $policies->page_content;
            $page_content = preg_replace('~>\s+<~', '><', $page_content);
            if (!empty($page_content)) {
                $res[0]->shop_policies = $policies->page_content;
            }
        }
    }
    return $res;
}
function getStoreInfoByIdApi($storeID) {
    $res = DB::table('stores')->select('store_id','store_name','store_slug','store_logo','banner_image_id','store_state','store_city','country','about_store')->where('store_id', $storeID)->first();

    if ($res) {
        return $res;
    } else {
        return null;
    }
}
function getStoreInfoByIdCartApi($storeID) {
    $res = DB::table('stores')
            ->select('store_id','store_name', 'store_slug', 'store_logo')
            ->where('store_id', $storeID)
            ->first();

    if ($res) {
        return (array)$res;
    } else {
        return null;
    }
}



function getStoreInfoByUserid($userID) {
    $res = DB::table('stores')->select('store_id','store_name', 'banner_image_id', 'campaign_id','store_status','super_status')->where('user_id', $userID)->first();

    if ($res) {
        return $res;
    } else {
        return null;
    }
}

// function getCampaignsByStoreId($storeID) {
//     $res = DB::table('campaign_store')->where('store_id', $storeID)->first();

//     if ($res) {
//         return $res;
//     } else {
//         return null;
//     }
// }

function getCampaignsByStoreId($storeId) {
    $query = DB::table('campaign_store')
        ->leftJoin('campaigns', 'campaign_store.campaign_id', '=', 'campaigns.id')
        ->where('campaign_store.store_id', '=', $storeId)
        ->select('campaigns.id', 'campaign_store.store_id', 'campaign_store.status', 'campaigns.benefits', 
        'campaigns.start_at','campaigns.end_at', 'campaign_store.campaign_join', 'campaign_store.campaign_expiry')
        ->first();

    if ($query) {
        return $query;
    } else {
        return null; // Store not found or no products for the store
    }
}

function getStoreDataById($storeID) {
    
    $res = DB::table('stores')->select('*')->where('store_id', $storeID)->get()->toArray();
  
// echo 'Data: <pre>' .print_r($res,true). '</pre>';
    return $res;
}

function getStoreStatusById($storeID) {
    $res = DB::table('stores')->select('*')->where('store_id', $storeID)->get();
    if(isset($res[0]) && !empty($res[0])){
        
        return $res[0];
    }else{
        return false;
    }
    
}
function checkStoreIconSelected() {
    $userId = Session::get('userData')['user_id'];
    $storeData = getStoreDataByUserId($userId);
    if (empty($storeData->store_icon)) {
        return false;
    } else {
        return true;
    }
}

function getcategoriesWithProducts($limit6) {
    $results = DB::table('product_category')
            ->leftJoin('products', 'product_category.product_id', '=', 'products.product_id')
            ->orderby('products.is_feature')
            ->get()
            ->toArray();
    $new_array = array();
    if (count($results) > 0) {
        foreach ($results as $res) {
            if (array_key_exists($res->category_id, $new_array)) {
                $total_reocrds = count($new_array[$res->category_id]);
                $new_array[$res->category_id][$total_reocrds] = $res;
            }
            $new_array[$res->category_id][0] = $res;
        }
    }
    return $new_array;
}

function paginate($item_per_page, $current_page, $total_records, $total_pages, $page_url) {


    $pagination = '';
    if ($total_pages > 0 && $total_pages != 1) { //verify total pages and current page number
        $pagination .= '<ul class="pagination">';

        $right_links = $current_page + 3;
        $previous = $current_page - 3; //previous link 
        $next = $current_page + 1; //next link
        $first_link = true; //boolean var to decide our first link

        if ($current_page > 1) {
            $previous_link = ($previous == 0) ? 1 : $previous;
            $previous_link = $current_page - 1;
            $pagination .= '<li class="first page-item"><a class="page-link" href="' . $page_url . '&page=1" title="First">&laquo;</a></li>'; //first link
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $page_url . '&page=' . $previous_link . '" title="Previous">&lt;</a></li>'; //previous link
            for ($i = ($current_page - 2); $i < $current_page; $i++) { //Create left-hand side links
                if ($i > 0) {
                    $i = ceil($i);
                    $pagination .= '<li><a class="page-link" href="' . $page_url . '&page=' . $i . '">' . $i . '</a></li>';
                }
            }
            $first_link = false; //set first link to false
        }

        if ($first_link) { //if current active page is first link
            $pagination .= '<li class="first page-item active"><a class="page-link">' . $current_page . '</a></li>';
        } elseif ($current_page == $total_pages) { //if it's the last active link
            $pagination .= '<li class="last page-item active"><a class="page-link">' . $current_page . '</a></li>';
        } else { //regular current link
            $pagination .= '<li class="active page-item"><a class="page-link">' . $current_page . '</a></li>';
        }

        for ($i = $current_page; $i < $right_links; $i++) { //create right-hand side links
            if ($i <= $total_pages) {
                $i = ceil($i);
                if ($i == $total_pages) {
                    break;
                }
                $showi = $i + 1;
                $pagination .= '<li class="page-item"><a class="page-link" href="' . $page_url . '&page=' . $showi . '">' . $showi . '</a></li>';
            }
        }
        if ($current_page < $total_pages) {
            $next_link = ($i > $total_pages) ? $total_pages : $i;
            $next_link = ceil($next_link);
            $total_pages = ceil($total_pages);
            $next_link = $current_page + 1;
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $page_url . '&page=' . $next_link . '" >&gt;</a></li>'; //next link
            $pagination .= '<li class="last page-item"><a class="page-link" href="' . $page_url . '&page=' . $total_pages . '" title="Last">&raquo;</a></li>'; //last link
        }

        $pagination .= '</ul>';

        return $pagination; //return pagination links
    }
}

function getYearlyOrdersOfStore($status = "", $to = "", $from = "") {
    $data = array();
    $userId = getCurrentUserId();
    if (empty($userId)) {
        $userId = Request::segment(2);
    }
    $storeData = getStoreDataByUserId($userId);
    if (isset($storeData) && $status != "") {
        $storeId = $storeData->store_id;
        $query = DB::table('store_orders')->select('order_id', 'created_at', 'store_id', 'total_amount')->where('store_id', $storeId)->orderBy('created_at');
        if ($status == "yearly") {
            $query->whereYear('created_at', date('Y'));
            $data = $query->get();
        } elseif ($status == "monthly") {
            $query->whereMonth('created_at', date('m'));
            $data = $query->get();
        } else if ($status == "lastmonth") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query->whereMonth('created_at', $month);
            $data = $query->get();
        } else if ($status == "date" && $to != "" && $from != "") {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($from)));
            $query->whereBetween('created_at', [$to, $from]);
            $data = $query->get();
        } else if ($status == "weekly") {
            $date = date('Y-m-d');
            $from = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query->whereBetween('created_at', [$from, $to]);
            $data = $query->get();
        } else if ($status == "daily") {
            $date = date('Y-m-d');
            $query->whereDate('created_at', $date);
            $data = $query->get();
        }
        $arr2 = objToArray($data);
        $new_arr = array();
        foreach ($arr2 as $key => $arr) {
            $arrcreated_at = explode(' ', $arr['created_at']);
            $arrcreated_at = $arrcreated_at[0];
            $updated = true;
            foreach ($new_arr as $key2 => $arr3) {
                if ($arr3['created_at'] == $arrcreated_at) {
                    $existing_total = $arr3['total_amount'] + $arr['total_amount'];
                    $new_arr[$key2]['total_amount'] = $existing_total;
                    $updated = false;
                }
            }
            if ($updated) {
                $new_arr[] = array('created_at' => $arrcreated_at, 'total_amount' => $arr['total_amount']);
            }
        }
        //        if (count($new_arr) == 1) {
        //            $new_arr[1]['created_at'] = date('Y-m-d', strtotime("-1 days", strtotime($new_arr[0]['created_at'])));
        //            $new_arr[1]['total_amount'] = 0;
        //        }
        return $new_arr;
    } else {
        return false;
    }
}
function getYearlyOrdersOfStoreApi($status = "", $to = "", $from = "") {
    $data = array();
    $userId = Auth::user()->user_id;
   
    $storeData = getStoreDataByUserId($userId);
    if (isset($storeData) && $status != "") {
        $storeId = $storeData->store_id;
        $query = DB::table('store_orders')->select('order_id', 'created_at', 'store_id', 'total_amount')->where('store_id', $storeId)->orderBy('created_at');
        if ($status == "yearly") {
            $query->whereYear('created_at', date('Y'));
            $data = $query->get();
        } elseif ($status == "monthly") {
            $query->whereMonth('created_at', date('m'));
            $data = $query->get();
        } else if ($status == "lastmonth") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query->whereMonth('created_at', $month);
            $data = $query->get();
        } else if ($status == "date" && $to != "" && $from != "") {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($from)));
            $query->whereBetween('created_at', [$to, $from]);
            $data = $query->get();
        } else if ($status == "weekly") {
            $date = date('Y-m-d');
            $from = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query->whereBetween('created_at', [$from, $to]);
            $data = $query->get();
        } else if ($status == "daily") {
            $date = date('Y-m-d');
            $query->whereDate('created_at', $date);
            $data = $query->get();
        }
        $arr2 = objToArray($data);
        $new_arr = array();
        foreach ($arr2 as $key => $arr) {
            $arrcreated_at = explode(' ', $arr['created_at']);
            $arrcreated_at = $arrcreated_at[0];
            $updated = true;
            foreach ($new_arr as $key2 => $arr3) {
                if ($arr3['created_at'] == $arrcreated_at) {
                    $existing_total = $arr3['total_amount'] + $arr['total_amount'];
                    $new_arr[$key2]['total_amount'] = $existing_total;
                    $updated = false;
                }
            }
            if ($updated) {
                $new_arr[] = array('created_at' => $arrcreated_at, 'total_amount' => $arr['total_amount']);
            }
        }
        //        if (count($new_arr) == 1) {
        //            $new_arr[1]['created_at'] = date('Y-m-d', strtotime("-1 days", strtotime($new_arr[0]['created_at'])));
        //            $new_arr[1]['total_amount'] = 0;
        //        }
        return $new_arr;
    } else {
        return false;
    }
}

function getTotalProductsByStore($storeId) {
    $query = DB::table('stores')
        ->leftJoin('products', 'stores.store_id', '=', 'products.store_id')
        ->where('stores.store_id', '=', $storeId)
        ->select(DB::raw('COUNT(products.product_id) AS product_count')
        )
        ->first();

    if ($query) {
        return $query->product_count;
    } else {
        return 0; // Store not found or no products for the store
    }
}
function getTotalActiveProductsByStore($storeId) {
    $query = DB::table('stores')
        ->leftJoin('products', 'stores.store_id', '=', 'products.store_id')
        ->where('stores.store_id', '=', $storeId)
        ->where('products.status', '=', '1')
        ->select(DB::raw('COUNT(products.product_id) AS product_count')
        )
        ->first();

    if ($query) {
        return $query->product_count;
    } else {
        return 0; // Store not found or no products for the store
    }
}

function getTotalStoresale($status, $to = "", $from = "") {
    $storeData = getStoreDataByUserId(getCurrentUserId());
    if (isset($storeData) && $status != "") {
        $storeId = $storeData->store_id;
        $query = DB::table('store_orders')->where('store_id', $storeId)->orderBy('created_at');
        if ($status == "yearly") {
            $query->whereYear('created_at', date('Y'));
            $data = $query->sum('total_amount');
        } elseif ($status == "monthly") {
            $query->whereMonth('created_at', date('m'));
            $data = $query->sum('total_amount');
        } else if ($status == "lastmonth") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query->whereMonth('created_at', $month);
            $data = $query->sum('total_amount');
        } else if ($status == "date" && $to != "" && $from != "") {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($from)));
            $query->whereBetween('created_at', [$to, $from]);
            $data = $query->sum('total_amount');
        } else if ($status == "weekly") {
            $date = date('Y-m-d');
            $from = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query->whereBetween('created_at', [$from, $to]);
            $data = $query->sum('total_amount');
        } else if ($status == "daily") {
            $date = date('Y-m-d');
            $query->whereDate('created_at', $date);
            $data = $query->sum('total_amount');
        }
        return $data;
    }
}

function getProductSale($status = "", $productId = 112, $to = "", $from = "") {
    $storeData = getStoreDataByUserId(getCurrentUserId());
    $storeId = $storeData->store_id;
    if ($productId != "" && $storeId != "") {
        $arr = array();
        $sale = 0;
        $query = DB::table('store_orders')->select('order_items.price', 'order_items.item_id', 'order_items.quantity', 'store_orders.created_at')
                ->Join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->where('order_items.store_id', $storeId)
                ->where('order_items.item_id', $productId);
        if ($status == "yearly") {
            $query->whereYear('store_orders.created_at', date('Y'));
            $data = $query->get();
        } elseif ($status == "monthly") {
            $query->whereMonth('store_orders.created_at', date('m'));
            $data = $query->get();
        } else if ($status == "date" && $to != "" && $from != "") {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($from)));
            $query->whereBetween('created_at', [$to, $from]);
            $data = $query->get();
        } else if ($status == "weekly") {
            $date = date('Y-m-d');
            $from = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query->whereBetween('store_orders.created_at', [$from, $to]);
            $data = $query->get();
        } else if ($status == "daily") {
            $date = date('Y-m-d');
            $query->whereDate('store_orders.created_at', $date);
            $data = $query->get();
        } else if ($status == "lastmonth") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query->whereMonth('store_orders.created_at', $month);
            $data = $query->get();
        }
        $arr2 = objToArray($data);
        $new_arr = array();
        foreach ($arr2 as $key => $arr) {
            $arrcreated_at = explode(' ', $arr['created_at']);
            $arrcreated_at = $arrcreated_at[0];
            $updated = true;
            $arr['total_amount'] = $arr['quantity'] * $arr['price'];
            foreach ($new_arr as $key2 => $arr3) {
                if ($arr3['created_at'] == $arrcreated_at) {
                    $existing_total = $arr3['total_amount'] + $arr['quantity'] * $arr['price'];
                    $new_arr[$key2]['total_amount'] = $existing_total;
                    $updated = false;
                }
            }
            if ($updated) {
                $new_arr[] = array('created_at' => $arrcreated_at, 'total_amount' => $arr['total_amount']);
            }
        }
        //        if (count($new_arr) == 1) {
        //            $new_arr[1]['created_at'] = date('Y-m-d', strtotime("-1 days", strtotime($new_arr[0]['created_at'])));
        //            $new_arr[1]['total_amount'] = 0;
        //        }
        return $new_arr;
    } else {
        return false;
    }
}
function getProductSaleApi($status = "", $productId = 112, $to = "", $from = "") {
    $userId = Auth::user()->user_id;
    $storeData = getStoreDataByUserId($userId);
    $storeId = $storeData->store_id;
    if ($productId != "" && $storeId != "") {
        $arr = array();
        $sale = 0;
        $query = DB::table('store_orders')->select('order_items.price', 'order_items.item_id', 'order_items.quantity', 'store_orders.created_at')
                ->Join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->where('order_items.store_id', $storeId)
                ->where('order_items.item_id', $productId);
        if ($status == "yearly") {
            $query->whereYear('store_orders.created_at', date('Y'));
            $data = $query->get();
        } elseif ($status == "monthly") {
            $query->whereMonth('store_orders.created_at', date('m'));
            $data = $query->get();
        } else if ($status == "date" && $to != "" && $from != "") {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($from)));
            $query->whereBetween('created_at', [$to, $from]);
            $data = $query->get();
        } else if ($status == "weekly") {
            $date = date('Y-m-d');
            $from = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query->whereBetween('store_orders.created_at', [$from, $to]);
            $data = $query->get();
        } else if ($status == "daily") {
            $date = date('Y-m-d');
            $query->whereDate('store_orders.created_at', $date);
            $data = $query->get();
        } else if ($status == "lastmonth") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query->whereMonth('store_orders.created_at', $month);
            $data = $query->get();
        }
        $arr2 = objToArray($data);
        $new_arr = array();
        foreach ($arr2 as $key => $arr) {
            $arrcreated_at = explode(' ', $arr['created_at']);
            $arrcreated_at = $arrcreated_at[0];
            $updated = true;
            $arr['total_amount'] = $arr['quantity'] * $arr['price'];
            foreach ($new_arr as $key2 => $arr3) {
                if ($arr3['created_at'] == $arrcreated_at) {
                    $existing_total = $arr3['total_amount'] + $arr['quantity'] * $arr['price'];
                    $new_arr[$key2]['total_amount'] = $existing_total;
                    $updated = false;
                }
            }
            if ($updated) {
                $new_arr[] = array('created_at' => $arrcreated_at, 'total_amount' => $arr['total_amount']);
            }
        }
        //        if (count($new_arr) == 1) {
        //            $new_arr[1]['created_at'] = date('Y-m-d', strtotime("-1 days", strtotime($new_arr[0]['created_at'])));
        //            $new_arr[1]['total_amount'] = 0;
        //        }
        return $new_arr;
    } else {
        return false;
    }
}

function getProductTotalSale($arr2 = array(), $productId = 112, $to = "", $from = "") {
    $status = $arr2['type'];
    $to = $arr2['date_to'];
    $from = $arr2['date_from'];
    $productId = $arr2['product_id'];
    $storeData = getStoreDataByUserId(getCurrentUserId());
    $storeId = $storeData->store_id;
    if ($productId != "" && $storeId != "") {
        $arr = array();
        $sale = 0;
        $data = "";
        $query = DB::table('store_orders')->select(DB::raw('sum(order_items.price * order_items.quantity) as store_sale'))
                ->Join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
                ->where('order_items.store_id', $storeId)
                ->where('order_items.item_id', $productId);
        if ($status == "yearly" && $to == "" && $from == "") {
            $query->whereYear('store_orders.created_at', date('Y'));
            $data = $query->get();
        } elseif ($status == "monthly" && $to == "" && $from == "") {
            $query->whereMonth('store_orders.created_at', date('m'));
            $data = $query->get();
        } else if ($to != "" && $from != "") {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($from)));
            $query->whereBetween('created_at', [$to, $from]);
            $data = $query->get();
        } else if ($status == "weekly" && $to == "" && $from == "") {
            $date = date('Y-m-d');
            $from = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query->whereBetween('store_orders.created_at', [$from, $to]);
            $data = $query->get();
        } else if ($status == "daily" && $to == "" && $from == "") {
            $date = date('Y-m-d');
            $query->whereDate('store_orders.created_at', $date);
            $data = $query->get();
        } else if ($status == "lastmonth" && $to == "" && $from == "") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query->whereMonth('store_orders.created_at', $month);
            $data = $query->get();
        }
        if (!empty($data)) {
            $aa = objToArray($data);
            $arr3['store_sale'] = $aa[0]['store_sale'];

            return $arr3;
        } else {
            return false;
        }
    }
}

function canUserReview($product_id = '') {
    if ($product_id == '' || !getCurrentUserId()) {
        return false;
    }
    $results = DB::table('reviews')
            ->where('product_id', '=', $product_id)
            ->where('user_id', '=', getCurrentUserId())
            ->get()
            ->toArray();
    if (count($results) > 0) {
        return false;
    }
    $orders = DB::table('store_orders')
            ->join('order_items', 'store_orders.order_id', '=', 'order_items.order_id')
            ->where('store_orders.buyer_id', '=', getCurrentUserId())
            ->where('order_items.item_id', '=', $product_id)
            ->get()
            ->toArray();
    if (count($orders) == 0) {
        return false;
    } else {
        return true;
    }
}

function alreadyReviewed($product_id = '') {
    if ($product_id == '' || !getCurrentUserId()) {
        return false;
    }
    $results = DB::table('reviews')
            ->where('product_id', '=', $product_id)
            ->where('user_id', '=', getCurrentUserId())
            ->get()
            ->toArray();
    if (count($results) > 0) {
        return true;
    }
}

function getChildCategoriesByParentId($parentCategoryID) {
    return $all_categories_child = DB::table('categories')->where('parentCategoryID', '=', $parentCategoryID)->select('category_slug', 'categoryTitle')->get()->toArray();
}

function checkFlashSaleProduct($campaignId, $productId) {
    $query = DB::table('store_campaigns')->where('product_id', $productId)->where('store_campaign_id', $campaignId)->count();
    if ($query > 0) {
        return true;
    } else {
        return false;
    }
}

function getFlashSaleProduct($campId, $id = "") {
    if (Session::has('userData')) {
        $userId = Session::get('userData')['user_id'];
    } else {
        $userId = $id;
    }
    $storeData = getStoreDataByUserId($userId);
    $storeId = $storeData->store_id;
    $campQuery = DB::table('store_campaigns')->select('product_id')
                    ->where('store_campaign_id', $campId)->where('store_id', $storeId)->first();
    if ($campQuery) {
        $productId = $campQuery->product_id;
        $query = DB::table('products')
                        ->where('products.status', '!=', '2')
                        ->where('products.product_id', '=', $productId)
                        ->orderBy('created_at', 'DESC')
                        ->select('products.product_id', 'products.store_id', 'products.slug', 'products.sales', 'products.status', 'products.user_id', 'products.title', 'products.price', 'products.quantity', 'products.created_at', 'products.description', 'products.short_description', 'products.is_feature', 'products.feature_Image', 'products.product_type', 'products.sales', 'products.current_stock', 'products.user_revenue')->first();
        if (!empty($query)) {

            return $query;
        }
    } else {
        return false;
    }
}

function getvariableProduct($productId) {
    $query = DB::table('product_variation')->select('product_id', 'price', 'current_stock', 'sales')->where('product_id', $productId)->get();
   // echo '$query: <pre>' .print_r($query,true). '</pre>'; die;
    if (!empty($query)) {
        $data = array();
        $arr = array();
        $minPrice = 0;
        // $maxprice = 0;
        $currentStock = 0;
        $totalSales = 0;
        foreach ($query as $rs) {
            $arr[] = $rs->price;

            $currentStock += $rs->current_stock;
            $totalSales += $rs->sales;
        }
        if (!empty($arr)) {
            $data['min_price'] = min($arr); 
        }
        $data['current_stock'] = $currentStock;
        $data['total_sales'] = $totalSales;
        return $data;
    } else {
        return false;
    }
}
// variable product data
function getvariableProduct_id($variation_id) {
    $query = DB::table('product_variation')->select('variation_id', 'product_variation_image')->where('variation_id', $variation_id)->get();
//   echo '$query: <pre>' .print_r($query,true). '</pre>'; die;
    if (!empty($query)) {
        
        
        return $query;
    } else {
        return false;
    }
}
// variable product data
function getvariableByProduct_id($variation_id) {
    $query = DB::table('product_variation')->select('variation_id', 'product_variation_image','price','sale_price','option1','option2','option3','unit1','unit2','unit3','attribute1','attribute2','attribute3')->where('variation_id', $variation_id)->first();
//   echo '$query: <pre>' .print_r($query,true). '</pre>'; die;
    if (!empty($query)) {
        
        
        return $query;
    } else {
        return false;
    }
}

function getvariableDataByProduct_id($product_id) {
    $query = DB::table('product_variation')->select('variation_id', 'product_variation_image','price','sale_price')->where('product_id', $product_id)->first();
//   echo '$query: <pre>' .print_r($query,true). '</pre>'; die;
    if (!empty($query)) {
        
        
        return $query;
    } else {
        return false;
    }
}

function getUserWalletTotal($userId) {
    $walletTotal = DB::table('users')->where('user_id', $userId)->select('user_wallet')->first();
    return $walletTotal = $walletTotal->user_wallet;
}

function isFlashSale($productId = '') {

    if (!empty($productId)) {
        $query = DB::table('products')->select('price')->where('flashsale_status', 'choosen')
                        ->where('product_id', $productId)->first();
        if (!empty($query)) {
            $campQuery = DB::table('store_campaigns')
                            ->select('pledges', 'pledges_used')
                            ->where('product_id', $productId)->where('status', 'active')->first();
            if (empty($campQuery)) {
                return false;
            }
            $totalZit = $campQuery->pledges;
            $usedZit = $campQuery->pledges_used;
            $productPrice = $query->price;
            $constants = config('constants.shopzit_gamification');
            $cashBack = $constants['CHOOSEN_CASHBACK'];
            $cashBack = ($cashBack * $productPrice) / 100;
            $newTotal = $cashBack + $usedZit;

            if ($totalZit >= $newTotal) {

                return true;
            }
        } else {

            return false;
        }
    }
}

function getCampaignIdByStoreID($storeId) {
    $query = DB::table('store_campaigns')->where('store_id', $storeId)->where('status', 'active')->first();
    if (!empty($query)) {
        return $query;
    } else {
        return false;
    }
}

function getCampaignIdataById($campId) {
    $query = DB::table('store_campaigns')->where('store_campaign_id', $campId)->first();
    if (!empty($query)) {
        return $query;
    } else {
        return false;
    }
}

function isStoreActiveCampaign($storeId = '') {
    if (!empty($storeId)) {
        $query = DB::table('store_campaigns')->where('store_id', $storeId)->where('status', 'active')->count();
        if ($query > 0) {
            return true;
        } else {
            return false;
        }
    }
}

/*
  function getFlashSaleDate($productId) {
  $constants = config('constants.shopzit_gamification');
  $flashSaleLimit = $constants['CHOOSEN_CASHBACK_LIMIT'];
  $date = "";
  $query = DB::table('products')->where('flashsale_status', 'choosen')
  ->where('product_id', $productId)->count();
  if (!empty($productId) && $query > 0) {
  $campQuery = DB::table('store_campaigns')->select('flash_sale_start')
  ->where('product_id', $productId)->where('status', 'active')->first();
  if ($campQuery) {
  $endDate = date('Y-m-d H:i:s', strtotime("+$flashSaleLimit hours", strtotime($campQuery->flash_sale_start)));
  $date = date('F d, Y H:i:s', strtotime($endDate));
  return $date;
  } else {
  return false;
  }
  } else {
  return false;
  }
  }
 */

function campaignEndDate($storeId) {

    if (!empty($storeId)) {
        $campQuery = DB::table('store_campaigns')->select('camp_run_end')
                        ->where('store_id', $storeId)->where('status', 'active')->first();
        if ($campQuery) {

            //$date = date('F d, Y H:i:s', strtotime($campQuery->camp_run_end));
            $date = date_convert($campQuery->camp_run_end);
            return $date;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function date_convert($dt, $df2 = "F d, Y H:i:s") {

    //date_convert('28/10/2013 02:00:00', 'Europe/Athens', 'd/m/Y H:i:s', 'UTC', 'F d, Y H:i:s');
    // create DateTime object

    $tz2 = getTimeZone();
    if (empty($tz2)) {
        $tz2 = "UTC";
    }
    $tz1 = "UTC";
    $df1 = "Y-m-d H:i:s";
    //$df2="F d, Y H:i:s";
    // date_default_timezone_set($clientTimeZone);
    $d = DateTime::createFromFormat($df1, $dt, new DateTimeZone($tz1));
    // convert timezone
    $d->setTimeZone(new DateTimeZone($tz2));
    // convert dateformat
    return $d->format($df2);
}

function getGAveBackStats($campaignId, $productId) {
    $campQuery = DB::table('campaign_product_stats')->select('sold_qty', 'sold_amount')
                    ->where('product_id', $productId)->where('campaign_id', $campaignId)->first();
    if (!empty($campQuery)) {
        return $campQuery;
    } else {
        return false;
    }
}

function getVariationNameById($variationId) {
    $variation = DB::table('product_variation')
            ->where('variation_id', '=', $variationId)
            ->select('attribute1', 'attribute2', 'attribute3', 'unit1', 'unit2', 'unit3', 'option1', 'option2', 'option3')
            ->get()
            ->toArray();
    $name = '';
    if (count($variation) > 0) {
        // Start with attribute1, option1, and unit1
        $name = ucfirst($variation[0]->attribute1) . ': ' . $variation[0]->option1 . ' ' . $variation[0]->unit1;
    }
    // Check if attribute2, option2, and unit2 exist and add them
    if (isset($variation[0]->option2) && $variation[0]->option2 != '') {
        $name .= ' ' . ucfirst($variation[0]->attribute2) . ': ' . $variation[0]->option2 . ' ' . $variation[0]->unit2;
    }
    // Check if attribute3, option3, and unit3 exist and add them
    if (isset($variation[0]->option3) && $variation[0]->option3 != '') {
        $name .= ' ' . ucfirst($variation[0]->attribute3) . ': ' . $variation[0]->option3 . ' ' . $variation[0]->unit3;
    }
    return $name;
}

function maxYouCanSell($productPrice, $campaignTotal) {
    $constants = config('constants.shopzit_gamification');
    $cashback = $constants['CHOOSEN_CASHBACK'];
    $cashbackAmount = ((float) $productPrice * (float) $cashback) / 100;
    $maxSell = $campaignTotal / $cashbackAmount;
    return $maxSell = round($maxSell);
}

function getFlashSaleDate($productId) {
    $constants = config('constants.shopzit_gamification');
    $flashSaleLimit = $constants['CHOOSEN_CASHBACK_LIMIT'];

    $date = "";
    $remainHours = "";
    $query = DB::table('products')->where('flashsale_status', 'choosen')
                    ->where('product_id', $productId)->count();
    if (!empty($productId) && $query > 0) {
        $campQuery = DB::table('store_campaigns')->select('flash_sale_start', 'camp_run_end')
                        ->where('product_id', $productId)->where('status', 'active')->first();
        if ($campQuery) {
            //flash sale check time 
            $date_a = new DateTime($campQuery->flash_sale_start);
            //$date_a = new DateTime($currentTimeDate);
            $date_b = new DateTime($campQuery->camp_run_end);
            $interval = date_diff($date_a, $date_b);
            $remainHours = $interval->format('%h');
            $remainMin = $interval->format('%i');
            $remainsecs = $interval->format('%s');
            if ($remainHours > 0 && ($remainHours < $flashSaleLimit)) {
                $endDate = date('Y-m-d H:i:s', strtotime("+$remainHours hours", strtotime($campQuery->flash_sale_start)));
                $endDatemin = date('Y-m-d H:i:s', strtotime("+$remainMin minutes", strtotime($endDate)));
                $finaldate = date('Y-m-d H:i:s', strtotime("+$remainsecs seconds", strtotime($endDatemin)));
            } else {
                $finaldate = date('Y-m-d H:i:s', strtotime("+$flashSaleLimit hours", strtotime($campQuery->flash_sale_start)));
            }

            $date = date_convert($finaldate);
            return $date;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function getProductGaveBack($productID) {
    $total = DB::table('campaign_product_stats')
            ->where('product_id', $productID)
            ->sum('sold_amount');
    return (round($total, 4));
}

function checkStoreActive() {
    $userId = Session::get('userData')['user_id'];
    // $userId = auth()->user()->user_id;
    $storeData = getStoreDataByUserId($userId);
    if ($storeData->store_status == "0") {
        return 'deactivated';
    }  else if($storeData->store_status == "3") {
        return 'rejected';
    }else if($storeData->store_status == "1"){
        return 'pending';
    }else if($storeData->store_status == "2"){
        return 'active';
    }else{
        return '';
    }
}
function checkStoreActiveApi() {
    // $userId = Session::get('userData')['user_id'];
    $userId = auth()->user()->user_id;
    $storeData = getStoreDataByUserId($userId);
    if ($storeData->store_status == "0") {
        return 'deactivated';
    }  else if($storeData->store_status == "3") {
        return 'rejected';
    }else if($storeData->store_status == "1"){
        return 'pending';
    }else if($storeData->store_status == "2"){
        return 'active';
    }else{
        return '';
    }
}

function number_format_short($n, $precision = 1) {
    if ($n < 900) {
// 0 - 900
        $n_format = number_format($n, $precision);
        $suffix = '';
    } else if ($n < 900000) {
// 0.9k-850k
        $n_format = number_format($n / 1000, $precision);
        $suffix = 'K';
    } else if ($n < 900000000) {
// 0.9m-850m
        $n_format = number_format($n / 1000000, $precision);
        $suffix = 'M';
    } else if ($n < 900000000000) {
// 0.9b-850b
        $n_format = number_format($n / 1000000000, $precision);
        $suffix = 'B';
    } else {
// 0.9t+
        $n_format = number_format($n / 1000000000000, $precision);
        $suffix = 'T';
    }
// Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
// Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ($precision > 0) {
        $dotzero = '.' . str_repeat('0', $precision);
        $n_format = str_replace($dotzero, '', $n_format);
    }
    return $n_format . $suffix;
}

function getTagsByProductId($productId = "") {
    $data = array();
    $query = DB::table('product_tags')->where('product_id', $productId)->get();
    if (!empty($query)) {
        $resutlt = objToArray($query);
        foreach ($resutlt as $res) {
            $data[] = $res['tag_title'];
        }
    }
    return $data;
}

function addNotifications($receptId, $senderId, $subject, $description, $urlLink,$return_tab=null,$notification_action_id=null,$app_flag=null) {
    $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']:'';
    $data = array();
    $data['recipient_id'] = !empty($receptId) ? $receptId : "";
    $data['sender_id'] = !empty($senderId) ? $senderId : "";
    $data['subject'] = !empty($subject) ? $subject : "";
    $data['description'] = !empty($description) ? $description : "";
    $data['url_link'] = !empty($urlLink) ? $urlLink : "";
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['app_flag'] = $app_flag;
    $data['return_tab'] = $return_tab;
    $data['notification_action_id'] = $notification_action_id;
    $query = DB::table('notifications')->insert($data);
    $userQuery = DB::table('users')->where('user_id', $receptId)->update(['has_notification' => 1]);
    $userQuery = DB::table('users')->where('user_id', $senderId)->update(['has_notification' => 1]);

    if ($query == true) {
        return true;
    } else {
        return false;
    }
}
function addNotificationsApi($receptId, $senderId, $subject, $description, $urlLink) {
    $userId = auth()->user()->user_id;
    $data = array();
    $data['recipient_id'] = !empty($receptId) ? $receptId : "";
    $data['sender_id'] = !empty($senderId) ? $senderId : "";
    $data['subject'] = !empty($subject) ? $subject : "";
    $data['description'] = !empty($description) ? $description : "";
    $data['url_link'] = !empty($urlLink) ? $urlLink : "";
    $data['created_at'] = date('Y-m-d H:i:s');
    $query = DB::table('notifications')->insert($data);
    $userQuery = DB::table('users')->where('user_id', $receptId)->update(['has_notification' => 1]);
    $userQuery = DB::table('users')->where('user_id', $senderId)->update(['has_notification' => 1]);

    if ($query == true) {
        return true;
    } else {
        return false;
    }
}
function addUserNotifications($receptId,$subject, $description, $urlLink) {
    $data = array();
    $data['recipient_id'] = !empty($receptId) ? $receptId : "";
    // $data['sender_id'] = !empty($senderId) ? $senderId : "";
    $data['subject'] = !empty($subject) ? $subject : "";
    $data['description'] = !empty($description) ? $description : "";
    $data['url_link'] = !empty($urlLink) ? $urlLink : "";
    $data['created_at'] = date('Y-m-d H:i:s');
    $query = DB::table('notifications')->insert($data);
    if ($query == true) {
        return true;
    } else {
        return false;
    }
}

function getuserNotifications($posteddata = '') {
    $data = array();
    if (isset($posteddata['return_source']) && $posteddata['return_source'] == 'true') {
        $userId = Auth::user()->user_id;
    }else{
        $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']:'';
    }
    $query = DB::table('notifications')->where('recipient_id', $userId)->orderBy('notification_id', 'DESC')->limit(10)->get();
    // echo 'Data: <pre>' .print_r($query,true). '</pre>'; die();
    $unreadCount = DB::table('notifications')->where('recipient_id', $userId)->where('seen', 0)->count();
    if (!empty($query)) {
        $data['notification'] = objToArray($query);
        $data['unread'] = $unreadCount;
        return $data;
    } else {
        return false;
    }
}
function getCurrentUserNotifications($posteddata = '') {
    $data = array();
    if (isset($posteddata['return_source']) && $posteddata['return_source'] == 'true') {
        $userId = Auth::user()->user_id;
    }else{
        $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']:'';
    }
    $query = DB::table('notifications')->where('sender_id', $userId)->orderBy('notification_id', 'DESC')->limit(10)->get();
    $unreadCount = DB::table('notifications')->where('sender_id', $userId)->where('status', 0)->count();
    if (!empty($query)) {
        $data['notification'] = objToArray($query);
        $data['unread'] = $unreadCount;
        return $data;
    } else {
        return false;
    }
}

function time_ago_in_php($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;

    $minutes = round($seconds / 60); // value 60 is seconds  
    $hours = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec  
    $days = round($seconds / 86400); //86400 = 24 * 60 * 60;  
    $weeks = round($seconds / 604800); // 7*24*60*60;  
    $months = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60  
    $years = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60

    if ($seconds <= 60) {

        return "Just Now";
    } else if ($minutes <= 60) {

        if ($minutes == 1) {

            return "1 min ago";
        } else {

            return "$minutes mins ago";
        }
    } else if ($hours <= 24) {

        if ($hours == 1) {

            return "an hour ago";
        } else {

            return "$hours hrs ago";
        }
    } else if ($days <= 7) {

        if ($days == 1) {

            return "yesterday";
        } else {

            return "$days days ago";
        }
    } else if ($weeks <= 4.3) {

        if ($weeks == 1) {

            return "a week ago";
        } else {

            return "$weeks weeks ago";
        }
    } else if ($months <= 12) {

        if ($months == 1) {

            return "a month ago";
        } else {

            return "$months months ago";
        }
    } else {

        if ($years == 1) {

            return "one year ago";
        } else {

            return "$years years ago";
        }
    }
}

function getProductReviewAverage($productId) {
    $reviews = DB::table('reviews')->where('product_id', '=', $productId)->orderBy('created_at', 'DESC')->get()->toArray();
//print_rr($reviews);
    $average = 0;

    if (isset($reviews) && count($reviews) > 0) {
        $numer = 0;

        $star1_count = 0;
        $star2_count = 0;
        $star3_count = 0;
        $star4_count = 0;
        $star5_count = 0;
        foreach ($reviews as $rev) {
            $numer += $rev->rating;
        }

        $average = round($numer / count($reviews));
    }
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $average) {
            $class = "fa fa-star";
        } else {
            $class = "fa fa-star-o";
        }

        $html .= '<i class = "' . $class . '" aria-hidden = "true"></i>';
    }


    return $html;
}
function getReviewAverage($reviews) {
    if(!empty($reviews)) {
        $reviews->toArray();
    }
//print_rr($reviews);
    $average = 0;

    if (isset($reviews) && count($reviews) > 0) {
        $numer = 0;

        $star1_count = 0;
        $star2_count = 0;
        $star3_count = 0;
        $star4_count = 0;
        $star5_count = 0;
        foreach ($reviews as $rev) {
            $numer += $rev->rating;
        }

        $average = round($numer / count($reviews));
    }
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $average) {
            $class = "fa fa-star";
        } else {
            $class = "fa fa-star-o";
        }

        $html .= '<i class = "' . $class . '" aria-hidden = "true"></i>';
    }


    return $html;
}

function getStoreReviewAverage($store_id) {
    $reviews = DB::table('reviews')
                    ->join('products', 'products.product_id', 'reviews.product_id')
                    ->join('users', 'users.user_id', 'reviews.user_id')
                    ->where('products.store_id', '=', $store_id)
                    ->select('reviews.*', 'products.store_id', 'products.title', 'products.slug', 'products.short_description', 'products.feature_image', 'users.name', 'users.user_avatar')->get()->toArray();

    $average = 0;
    $star_count = array();
    if (isset($reviews) && count($reviews) > 0) {
        $numer = 0;
        $star1_count = 0;
        $star2_count = 0;
        $star3_count = 0;
        $star4_count = 0;
        $star5_count = 0;
        foreach ($reviews as $rev) {
            $numer += $rev->rating;
            if ($rev->rating == 1) {
                $star1_count++;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['comment'] = $rev->comment;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['added_by'] = $rev->user_id;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['name'] = $rev->name;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['title'] = $rev->title;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['slug'] = $rev->slug;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['short_description'] = $rev->short_description;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['created_at'] = $rev->created_at;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['feature_image'] = $rev->feature_image;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['user_avatar'] = $rev->user_avatar;
            }
            if ($rev->rating == 2) {
                $star2_count++;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['comment'] = $rev->comment;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['added_by'] = $rev->user_id;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['name'] = $rev->name;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['title'] = $rev->title;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['slug'] = $rev->slug;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['short_description'] = $rev->short_description;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['created_at'] = $rev->created_at;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['feature_image'] = $rev->feature_image;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['user_avatar'] = $rev->user_avatar;
            }
            if ($rev->rating == 3) {
                $star3_count++;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['comment'] = $rev->comment;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['added_by'] = $rev->user_id;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['name'] = $rev->name;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['title'] = $rev->title;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['slug'] = $rev->slug;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['short_description'] = $rev->short_description;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['created_at'] = $rev->created_at;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['feature_image'] = $rev->feature_image;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['user_avatar'] = $rev->user_avatar;
            }
            if ($rev->rating == 4) {
                $star4_count++;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['comment'] = $rev->comment;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['added_by'] = $rev->user_id;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['name'] = $rev->name;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['title'] = $rev->title;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['slug'] = $rev->slug;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['short_description'] = $rev->short_description;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['created_at'] = $rev->created_at;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['feature_image'] = $rev->feature_image;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['user_avatar'] = $rev->user_avatar;
            }
            if ($rev->rating == 5) {
                $star5_count++;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['comment'] = $rev->comment;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['added_by'] = $rev->user_id;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['name'] = $rev->name;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['title'] = $rev->title;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['slug'] = $rev->slug;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['short_description'] = $rev->short_description;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['created_at'] = $rev->created_at;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['feature_image'] = $rev->feature_image;
                $star_count[$rev->rating]['comment_details'][$rev->review_id]['user_avatar'] = $rev->user_avatar;
            }
        }
        $star_count[1]['ratings'] = $star1_count;
        $star_count[2]['ratings'] = $star2_count;
        $star_count[3]['ratings'] = $star3_count;
        $star_count[4]['ratings'] = $star4_count;
        $star_count[5]['ratings'] = $star5_count;

        $total = count($reviews);
        $star_count[1]['percentage'] = round(($star1_count / $total) * 100);
        $star_count[2]['percentage'] = round(($star2_count / $total) * 100);
        $star_count[3]['percentage'] = round(($star3_count / $total) * 100);
        $star_count[4]['percentage'] = round(($star4_count / $total) * 100);
        $star_count[5]['percentage'] = round(($star5_count / $total) * 100);

        $average = $numer / count($reviews);
    }

    $html = '';
    $ratings_reviews = array();
    if (!empty($star_count)) {
        for ($i = 1; $i <= 5; $i++) {
            if (($i + 4) == 5) {
                $ratings_reviews[5] = $star_count[5];
            }
            if (($i + 2) == 4) {
                $ratings_reviews[4] = $star_count[4];
            }
            if ($i == 3) {
                $ratings_reviews[3] = $star_count[3];
            }
            if (($i - 2) == 2) {
                $ratings_reviews[2] = $star_count[2];
            }
            if (($i - 4) == 1) {
                $ratings_reviews[1] = $star_count[1];
            }
            if ($i <= round($average)) {
                $class = "fa fa-star";
            } else {
                $class = "fa fa-star-o";
            }

            $html .= '<i class = "' . $class . '" aria-hidden = "true"></i>';
        }
    }
    $result = array('html' => $html, 'total_count' => count($reviews), 'average' => $average, 'ratings_reviews' => $ratings_reviews);
    return $result;
}
function getStoreReviewAverageApi($store_id) {
    $reviews = DB::table('reviews')
                    ->join('products', 'products.product_id', 'reviews.product_id')
                    ->join('users', 'users.user_id', 'reviews.user_id')
                    ->where('products.store_id', '=', $store_id)
                    ->select('reviews.*', 'products.store_id', 'products.title', 'products.slug', 'products.short_description', 'products.feature_image', 'users.name', 'users.user_avatar')->get()->toArray();

    // $average = 0;
    $reviews_array = array();
    // if (isset($reviews) && count($reviews) > 0) {
    //     $numer = 0;
    //     foreach ($reviews as $rev) {
    //         $numer += $rev->rating;
    //     }

    //     $average = round($numer / count($reviews));
    // }
    // echo 'Data: <pre>' .print_r($reviews,true). '</pre>'; die();
    if (isset($reviews) && count($reviews) > 0) {
        $numer = 0;
        foreach ($reviews as $rev) {
            $numer += $rev->rating;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['comment'] = $rev->comment;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['added_by'] = $rev->user_id;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['name'] = $rev->name;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['title'] = $rev->title;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['slug'] = $rev->slug;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['short_description'] = $rev->short_description;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['created_at'] = $rev->created_at;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['feature_image'] = $rev->feature_image;
                $reviews_array[$rev->rating]['comment_details'][$rev->review_id]['user_avatar'] = $rev->user_avatar;
        }

        $total = count($reviews);
        $average= round($numer / $total);
        $result = array('rating'=>$average,'reviews_count' => count($reviews_array), 'review' => $reviews_array);
        return $result;
        
    }else{
        $result = array('message' => 'no review found');
        return $result;
    }

    
    // $ratings_reviews = array();
    // if (!empty($star_count)) {
    //     for ($i = 1; $i <= 5; $i++) {
    //         if (($i + 4) == 5) {
    //             $ratings_reviews[5] = $star_count[5];
    //         }
    //         if (($i + 2) == 4) {
    //             $ratings_reviews[4] = $star_count[4];
    //         }
    //         if ($i == 3) {
    //             $ratings_reviews[3] = $star_count[3];
    //         }
    //         if (($i - 2) == 2) {
    //             $ratings_reviews[2] = $star_count[2];
    //         }
    //         if (($i - 4) == 1) {
    //             $ratings_reviews[1] = $star_count[1];
    //         }
    //         if ($i <= round($average)) {
    //             $class = "fa fa-star";
    //         } else {
    //             $class = "fa fa-star-o";
    //         }

    //         // $html .= '<i class = "' . $class . '" aria-hidden = "true"></i>';
    //     }
    // }
    // $result = array('total_count' => count($reviews));
   
}
function getTotalSupports($storeId) {
    $supporter = new Herdaccess();
    $totalSupporters = $supporter->getSupporters($storeId);
    if ($totalSupporters) {
        return count($totalSupporters);
    } else {
        return false;
    }
}

function getTotalgaveBack($storeId) {
    $query = DB::table('store_campaigns')->select('toal_gave_back')
                    ->where('store_id', $storeId)->where('status', 'active')->first();
    if (!empty($query)) {
        return $query;
    } else {
        return false;
    }
}

function productTotalRatings($productId) {
    $reviews = DB::table('reviews')->where('product_id', '=', $productId)->orderBy('created_at', 'DESC')->get()->toArray();
    return count($reviews);
}

function getTotalPledgeCampaign($storeId) {
    $constants = config('constants.shopzit_gamification');
    $flasSaleTarget = $constants['FLASH_SALE_TARGET'];
    $query = DB::table('store_campaigns')->where('store_id', $storeId)->where('status', 'support')->first();
    if (!empty($query)) {
        $constants = config('constants.shopzit_gamification');
        $flasSaleTarget = $constants['FLASH_SALE_TARGET'];
        $totalPledges = $query->pledges;
        if ($totalPledges > $flasSaleTarget) {
            return 0;
        } else {
            $collectedAmount = $flasSaleTarget - $totalPledges;
            if ($collectedAmount >= 0) {
                return $collectedAmount;
            }
        }
    }
}

function getStoreTotalProducts($store_id) {
    return $total_items = DB::table('products')->where('store_id', $store_id)->where('status', '1')->count();
}

function getPercentageTarget($store_id) {
    $constants = config('constants.shopzit_gamification');
    $toatl = $flasSaleTarget = $constants['FLASH_SALE_TARGET'];
    $used = getTotalPledgeCampaign($store_id);
    $rem = $toatl - $used;
    if ($rem == $toatl) {
        return 0;
    }
    if ($rem < 0) {
        return 100;
    }

    $percentage = ($rem / $toatl) * 100;
    return round($percentage, 2);
}

function simpleUpdateCurrentStock($productId, $sale) {
    $currentStock = DB::table('products')->where('product_id', $productId)->value('current_stock');

// Check if the current stock is greater than or equal to the sale quantity
if ($currentStock >= $sale) {
    // If there is enough stock, decrement the current stock by the sale quantity
    DB::table('products')->where('product_id', $productId)->decrement('current_stock', $sale);
    DB::table('products')->where('product_id', $productId)->increment('sales', $sale);
}else{
    return false;
}
   
}

function variableUpdateCurrentStock($varId, $productId, $sale) {
    $currentStock =  DB::table('product_variation')->where('variation_id', $varId)->where('product_id', $productId)->value('current_stock');

    // Check if the current stock is greater than or equal to the sale quantity
    if ($currentStock >= $sale) {
        // If there is enough stock, decrement the current stock by the sale quantity
        DB::table('product_variation')->where('variation_id', $varId)->where('product_id', $productId)->decrement('current_stock', $sale);
        DB::table('products')->where('product_id', $productId)->decrement('current_stock', $sale);
    DB::table('product_variation')->where('variation_id', $varId)->where('product_id', $productId)->increment('sales', $sale);
    }else{
        return false;
    }
    
}

function CalculateWorkedTime($WorkedTime) {

    $minutes = (int) ($WorkedTime / 60);
    if ($minutes >= 60) {
        $minutes = $minutes % 60;
    }


    $seconds = $WorkedTime % 60;
    $hours = (int) ((int) ($WorkedTime / 60) / 60);
//        if($hours==1 && $minutes==60){
//            $minutes=0;
//        }

    if ($minutes < 10) {
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }
    if ($seconds < 10) {
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);
    }
    if ($hours < 10) {
        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
    }
    if (strlen($minutes) > 1) {
        //$minutes = ltrim($minutes, '0');
    }
    if (strlen($hours) > 1) {
        //$hours = ltrim($hours, '0');
    }
    if ($hours != '0') {
        /* if($minutes != '00'){

          }
          else{

          } */
        if ($hours == 0) {
            $hours .= ' Hr';
        } else if ($hours == 1) {
            $hours .= ' Hr';
        } else if ($hours > 1) {
            $hours .= ' Hrs';
        }
        if ($minutes == '00') {
            $minutes = '';
        } elseif ($minutes == '01') {
            $minutes = " : " . $minutes . ' Min';
        } else {
            $minutes = " : " . $minutes . ' Mins';
        }

        return $hours . $minutes;
    } else {
        if ($minutes == '00') {
            $minutes = '0';
        }
        return $minutes . ' Mins';
    }
}

function getTimeZone() {
    if (isset($_COOKIE['clientTimeZone'])) {
        $timezone = $_COOKIE['clientTimeZone'];

// Convert minutes to seconds
        $timezone_name = timezone_name_from_abbr("", $timezone * 60, false);
// Asia/Kolkata
        if ($timezone_name == true) {
            return $timezone_name;
        } else {
            return "UTC";
        }
    }
}
function getShippingAddressbyUserId($user_id = '') {
    $query = DB::table('shipping_addresses')->where('user_id', $user_id)->latest('sh_id')->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}

function getShippingAddressbyId($shipping_id = '') {
    $query = DB::table('shipping_addresses')->where('sh_id', $shipping_id)->latest('sh_id')->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}


function getShippingOptionByUserId($userId) {
    return DB::table('shipping_options')
    ->where(function ($query) use ($userId) {
        $query->where('user_id', $userId)
            ->orWhereNull('user_id');
    })
    ->orderBy('user_id','asc')->select('*')
    ->get()
    ->toArray();
}

function getShippingOptionById($shippingId) {
    $query = DB::table('shipping_options')->where('id', $shippingId)->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}

function getShippingOptionMethodById($shippingId) {
    // echo 'Data: <pre>' .print_r($shippingId,true). '</pre>'; die();
    $shippingData = array();
    $shippingOptions = DB::table('shipping_options')->where('id', $shippingId)->select('*')->get()->toArray();
    // echo 'Data: <pre>' .print_r($shippingOptions,true). '</pre>'; die();
    if ($shippingOptions) {

        $shippingData['shippingOptions'] = isset($shippingOptions[0]) ? $shippingOptions[0] : NULL;
        $checkMethod = isset($shippingOptions[0]->shipping_cost) ? $shippingOptions[0]->shipping_cost : '';
        if ($checkMethod == 'fixedCost') {
            $shippingData['shippingUpgrades'] = DB::table('shipping_upgrades')->where('shipping_id', $shippingId)->select('*')->get()->toArray();
            $shippingData['shippingFixCost'] = DB::table('shipping_fix_cost')->where('shipping_id', $shippingId)->select('*')->get()->toArray();
        } else {
            $shippingData['shippingServices'] = DB::table('shipping_services')->where('shipping_id', $shippingId)->select('service_value')->get()->toArray();
            $countries = DB::table('shipping_countries')->where('shipping_id', $shippingId)->select('country_id')->get()->toArray();
            $countriesIds = array();
            if (count($countries) > 0) {
                foreach ($countries as $key => $value) {
                    $countriesIds[] = $value->country_id;
                }
            }
            $shippingData['shippingCountries'] = $countriesIds;
            //// $shippingData['shippingCountries'] = DB::table('shipping_countries')->join('country_list', 'country_list.country_id', '=', 'shipping_countries.country_id')->where('shipping_id', $shippingId)->select('country_list.country_id', 'country_list.country_name')->get()->toArray();
        }
    }
    return $shippingData;
}

function getProcessTimeList() {
    $userId = Session::get('userData')['user_id'];
    $processTimeArray = DB::table('processing_time')->WhereNull('user_id')->orWhere('user_id', $userId)->get();

    return $processTimeArray;
}

function getTimeProcess($id) {
    $query = DB::table('processing_time')->Where('processing_time_id', $id)->first();
    if (!empty($query)) {
        return $query;
    } else {
        return false;
    }
}

function getShippingServicesList() {
    $shippingServiceArray = array();

    $shippingServiceArray[] = array(
        'keyId' => 'First',
        'title' => 'USPS First Class Package Services',
        'description' => '13 lbs or less cost based on weight and distance.',
        'days' => '<span>1-3 days</span>'
    );
    $shippingServiceArray[] = array(
        'keyId' => 'ParcelSelect',
        'title' => 'USPS ParcelSelect Class Package Services',
        'description' => '13 lbs or less cost based on weight and distance.',
        'days' => '<span>1-3 days</span>'
    );
    $shippingServiceArray[] = array(
        'keyId' => 'Priority',
        'title' => 'USPS Priority',
        'description' => '70 lbs or less cost based on weight and distance.',
        'days' => ' <span>1-3 days</span>'
    );
    $shippingServiceArray[] = array(
        'keyId' => 'Express',
        'title' => 'USPS Priority Express',
        'description' => '70 lbs or less cost based on weight and distance.',
        'days' => ' <span>1-2 days</span>'
    );
//    $shippingServiceArray[] = array(
//        'keyId' => 'ps-pm-inter',
//        'title' => 'PS Priority Mail International',
//        'description' => 'An expedited international delivery service for a wide range of package types.',
//        'days' => '<p>6-10 days</p><p>International</p> '
//    );
//    $shippingServiceArray[] = array(
//        'keyId' => 'usps-pm-exp-inter',
//        'title' => 'USPS Priority Mail Express International',
//        'description' => 'The fastest international option offered by USPS for a wide range of package types.',
//        'days' => '<p>3-5 days</p><p>International</p>'
//    );
//    $shippingServiceArray[] = array(
//        'keyId' => 'usps-fc-inter-ps',
//        'title' => 'USPS First Class International Package Service',
//        'description' => 'An inexpensive way to send light small packages and envelopes internationally.',
//        'days' => '<p>Ship time varies</p><p>International</p>     '
//    );
    return $shippingServiceArray;
}

function getZAnimalById($icon_id = '') {
    if ($icon_id != '') {
        return $result = DB::table('store_icons')->where('icon_id', '=', $icon_id)->first();
    }
}

function alreadyPurchased($sellerHerdId) {
    if (empty($sellerHerdId)) {
        return false;
    }
    $sellerHerd = DB::table('herds')->where('herd_id', $sellerHerdId)->first();
    $userId = getCurrentUserId();
    if (!empty($sellerHerd)) {
        $userHerd = DB::table('herds')->where('icon_id', $sellerHerd->icon_id)->where('user_id', $userId)->first();
        if (empty($userHerd)) {
            return true;
        }
        $herdUsers = DB::table('herd_users')->where([['user_herd_id', $userHerd->herd_id], ['seller_herd_id', $sellerHerdId]])->orWhere([['user_herd_id', $sellerHerdId], ['seller_herd_id', $userHerd->herd_id]])->value('user_herd_id');
        if ($herdUsers) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function alreadyPurchasedInSameCampaign($itemId, $userID) {
    $constants = config('constants.shopzit_gamification');
    $CHOOSEN_CASHBACK_LIMIT = $constants['CHOOSEN_CASHBACK_LIMIT'];
    return $results = DB::table('order_items')
            ->LeftJoin('store_orders', 'order_items.order_id', 'store_orders.order_id')
            ->where('order_items.item_id', $itemId)
            ->where('store_orders.buyer_id', $userID)
            ->where('store_orders.created_at', '>=', Carbon::now()->subHours($CHOOSEN_CASHBACK_LIMIT)->toDateTimeString())
            ->count();
}

function expireFlashsaleTime($productId = "") {
    $response = "";
    $flashEndate = "";
    // if (!empty($flashsalequery)) {
    $currentTimeDate = date('Y-m-d H:i:s');
    $constants = config('constants.shopzit_gamification');
    $flashSaleDuration = $constants['CHOOSEN_CASHBACK_LIMIT'];
    $query = DB::table('store_campaigns')
                    ->select('flash_sale_start')->where('product_id', $productId)->where('status', 'active')->first();
    if (!empty($query->flash_sale_start)) {
        $flashEndate = date('Y-m-d H:i:s', strtotime("+$flashSaleDuration hours", strtotime($query->flash_sale_start)));
        if ($flashEndate < $currentTimeDate) {
            $response = "expire";
        } else {
            $response = "canactive";
        }
    } else {
        $response = "notselected";
    }
    return $response;



    // }
}

function getProductsByStoreId($storeId, $flashSaleStatus = "") {
    $qurey = DB::table('products')->where('store_id', $storeId)->where('status', '1');
    if (!empty($flashSaleStatus)) {
        $qurey->where('flashsale_status', $flashSaleStatus);
    }
    $res = $qurey->get();
    if (!empty($res)) {
        return $res;
    } else {
        return false;
    }
}

function addFlashSaleLog($productId = "", $saleType = "", $amount = "") {
    $data = array();
    $data['product_id'] = !empty($productId) ? $productId : "";
    $data['sale_type'] = !empty($saleType) ? $saleType : "";
    $data['sale'] = !empty($amount) ? $amount : "";
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['updated_at'] = date('Y-m-d H:i:s');
    if (isset($data)) {
        $query = DB::table('flash_sale_log')->insert($data);
    }
}

function get_tiny_url($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function getflashSaleProductsBycategoryId($catId = "") {
    if (!empty($catId)) {
        $query = DB::table('categories')->select('categories.categoryTitle')
                ->join('product_category', 'product_category.category_id', '=', 'product_category.category_id')
                ->join('products', 'products.product_id', '=', 'product_category.product_id')
                ->where('products.flashsale_status', 'all')
                ->where('categories.id', $catId)
                ->groupBy('categories.id')
                ->first();
        if (!empty($query)) {
            return objToArray($query);
        }
    }
}

function getStoreSale($storeId = "", $data = array()) {
    $data['store_id'] = $storeId;
    $arr = array();
    if (!empty($data['flashsale']) && !empty($data['store_id'])) {
        $res = getFlashSaletotal($data);
        $arr['store_sale'] = $res;
        return $arr;
    }
    if (!empty($data['type']) && $data['type'] == "yearly" && $data['date_to'] == "" && $data['date_from'] == "") {

        $totalSale = DB::table('store_orders')->whereYear('created_at', date('Y'))->sum('total_amount');
        if (!empty($data['store_id'])) {
            $totalSale = DB::table('store_orders')->where('store_id', $data['store_id'])->whereYear('created_at', date('Y'))->sum('total_amount');
        }
    } elseif (!empty($data['type']) && $data['type'] == "monthly") {
        $totalSale = DB::table('store_orders')->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('total_amount');
        if (!empty($data['store_id'])) {
            $totalSale = DB::table('store_orders')->where('store_id', $data['store_id'])->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('total_amount');
        }
    } else if (!empty($data['type']) && $data['type'] == "lastmonth") {
        $date = date('Y-m-d');
        $month = date('m', strtotime("-1 month", strtotime($date)));
        $totalSale = DB::table('store_orders')->whereMonth('created_at', $month)->whereYear('created_at', date('Y'))->sum('total_amount');
        if (!empty($data['store_id'])) {
            $totalSale = DB::table('store_orders')->where('store_id', $data['store_id'])->whereMonth('created_at', $month)->whereYear('created_at', date('Y'))->sum('total_amount');
        }
    } else if (!empty($data['date_to']) && !empty($data['date_from'])) {
        $from = date('Y-m-d', strtotime("+1 days", strtotime($data['date_from'])));
        $totalSale = DB::table('store_orders')->whereBetween('created_at', [$data['date_to'], $from])->sum('total_amount');
        if (!empty($data['store_id'])) {
            $totalSale = DB::table('store_orders')->where('store_id', $data['store_id'])->whereBetween('created_at', [$data['date_to'], $from])->sum('total_amount');
        }
    } else if (!empty($data['type']) && $data['type'] == "weekly") {
        $date = date('Y-m-d');
        $from = date('Y-m-d', strtotime($date));
        $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
        $totalSale = DB::table('store_orders')->whereBetween('created_at', [$from, $to])->sum('total_amount');
        if (!empty($data['store_id'])) {
            $totalSale = DB::table('store_orders')->where('store_id', $data['store_id'])->whereBetween('created_at', [$from, $to])->sum('total_amount');
        }
    } else if (!empty($data['type']) && $data['type'] == "daily") {
        $date = date('Y-m-d');
        $totalSale = DB::table('store_orders')->whereDate('created_at', $date)->sum('total_amount');
        if (!empty($data['store_id'])) {
            $totalSale = DB::table('store_orders')->where('store_id', $data['store_id'])->whereDate('created_at', $date)->sum('total_amount');
        }
    }

    if (!empty($totalSale)) {
        $arr['store_sale'] = $totalSale;
        return $arr;
    } else {
        return false;
    }
}

function getFlashSaletotal($data) {
    $productArr = array();
    $arr = array();
    $query = DB::table('store_campaigns')->select('product_id')->where('store_id', $data['store_id'])->whereIn('status', ['active', 'completed'])->get();
    if (!empty($query)) {
        //print_rr($query);
        foreach ($query as $q) {
            $productArr[] = $q->product_id;
        }
        $query2 = DB::table('flash_sale_log')->select('sale', 'created_at')->whereIn('product_id', $productArr)->where('sale_type', 'choosen');
        if (!empty($data['type']) && $data['type'] == 'yearly') {
            $query2->whereYear('created_at', date('Y'));
            $records = $query2->sum('sale');
        } elseif (!empty($data['type']) && $data['type'] == "monthly") {
            $query2->whereMonth('created_at', date('m'));
            $records = $query2->sum('sale');
        } else if (!empty($data['type']) && $data['type'] == "lastmonth") {
            $date = date('Y-m-d');
            $month = date('m', strtotime("-1 month", strtotime($date)));
            $query2->whereMonth('created_at', $month);
            $records = $query2->sum('sale');
        } else if (!empty($data['date_to']) && !empty($data['date_from'])) {
            $from = date('Y-m-d', strtotime("+1 days", strtotime($data['date_from'])));
            $query2->whereBetween('created_at', [$data['date_to'], $from]);
            $records = $query2->sum('sale');
        } else if (!empty($data['type']) && $data['type'] == "weekly") {
            $date = date('Y-m-d');
            $from = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
            $query2->whereBetween('created_at', [$from, $to]);
            $records = $query2->sum('sale');
        } else if (!empty($data['type']) && $data['type'] == "daily") {
            $date = date('Y-m-d');
            $query2->whereDate('created_at', $date);
            $records = $query2->sum('sale');
        }
        if (!empty($records)) {
            return $records;
        }
    } else {
        return false;
    }
}
 function userIntrests(){
     $arr=array();
     $userId = Session::get('userData')['user_id'];
    $userIntests=DB::table('user_intrests')->select('category_id')->where('user_id',$userId)->get();
    if(!empty($userIntests)){
        foreach($userIntests as $ui){
            $arr[]=$ui->category_id;
        }
        return $arr;
    }else{
        return false;
    }
}

function gatStoreDataById($storeId) {
    $query = DB::table('stores')->where('store_id', $storeId)->first();
    if (!empty($query)) {
        return $query;
    } else {
        return false;
    }
}
function StoreStatusById($id = ''){
    $store_status = null;
    $store = DB::table('stores')->where('store_id',$id)->first();
    // dd($store);
    if($store->store_status == '2'){
        $store_status = 'active';
    }elseif($store->store_status == 0){
        $store_status = 'deactive';
    }elseif($store->store_status == '1'){
        $store_status = 'pending';
    }
    return $store_status;
}
function getProductStatus($id = ''){
    $status= null;
    $product = Product::where('product_id',$id)->first();
    // dd($store);
    // if(isset($product) && !is_null($product)){
    //     if($product->status == '2'){
    //         $status = 'delete';
    //     }elseif($product->status == '0'){
    //         $status = 'deactive';
    //     }elseif($product->status == '1'){
    //         $status = 'active';
    //     }elseif($product->status == '3'){
    //         $status = 'pending';
    //     }elseif($product->status == '4'){
    //         $status = 'rejected';
    //     }elseif($product->status == '5'){
    //         $status = 'draft';
    //     }
    // }
    if ($product->product_type != 'variant') {
        
        if ($product->status == 1 && $product->current_stock > 0) {
            $status = 'active';
        } elseif ($product->status == 0) {
            $status = 'deactive';
        } elseif ($product->status == 3) {
            $status = 'pending';
        } elseif ($product->status == 5) {
            $status = 'draft';
        } elseif ($product->status == 4) {
            $status = 'rejected';
        }
        
    // Check for out-of-stock for non-variant products
        if ($product->status == 1 && $product->current_stock == 0) {
            $status = 'out-of-stock';
        }
    }elseif ($product->product_type == 'variant'){
        // foreach($product->variations as $variation){

        // }
        if ($product->status == 1) {
            $status = 'active';
        } elseif ($product->status == 0) {
            $status = 'deactive';
        } elseif ($product->status == 3) {
            $status = 'pending';
        } elseif ($product->status == 5) {
            $status = 'draft';
        } elseif ($product->status == 4) {
            $status = 'rejected';
        }
        if (isset($product->variations) && !empty($product->variations)) {
            $variableproductStock = 0;
            foreach ($product->variations as $key => $variation) {
               
                // Set variation image using the 'getAttachmentUrlSmallById' function
                if(isset($variation->current_stock) && !is_null($variation->current_stock)){
                    $variableproductStock += intval($variation->current_stock);
                }
            }
            // dd($variableproductStock);
            if ($product->status == 1 && $variableproductStock == 0) {
                $status = 'out-of-stock';
            }
        }
    }
   
    return $status;
}
function setProductStatus($status = ''){
    $status= null;
    // $product = DB::table('products')->where('product_id',$id)->first();
    // dd($store);
    if(isset($status) && !empty($status)){
        if($status == 'delete'){
            $status = '2';
        }else if($status == 'delete'){
            $status = '2';
        }else if($status == 'delete'){
            $status = '2';
        }
    }else if($status == 'delete'){
            $status = '2';
        }else if($status == 'delete'){
            $status = '2';
        }else if($status == 'delete'){
            $status = '2';
    }
    return $status;
}
function saleUpto($limit){
    $products = Product::with(['store', 'variations'])
        ->where('status', '1') // Product status is 2
        ->where('on_sale', true) // Product is on sale
        ->where('discount_percentage', '<=', $limit) // Product discounted percentage is less than 20
        ->whereHas('store', function($query) {
            $query->where('store_status', '2') // Store status is 2
                ->where('stripe', '1'); // Store has stripe enabled
        })->inRandomOrder()->distinct()->limit(10)->get();
        return $products;
}
function flashDeal(){
    $cutoffDate = Carbon::create(2024, 12, 25);

    // Step 2: Get the active Christmas deal discount IDs
    $discountIds = Discount::where('type', 'sale')
        ->where('status', 'active')
        ->where('state', 'live')
        ->whereDate('end_time', '=', $cutoffDate)
        ->pluck('id');
    
    // Step 3: Get products associated with these discounts
    $products = Product::where('status', '1') // Product status is 1
        ->where('on_sale', true) // Product is on sale
        ->whereIn('discount_id', $discountIds) // Products for Christmas deal discounts
        ->whereHas('store', function($query) {
            $query->where('store_status', '2') // Store status is 2
                  ->where('stripe', '1'); // Store has Stripe enabled
        })
        ->orderBy('updated_at', 'asc') // Sort by updated_at in ascending order
        ->take(10) // Limit to the first 10 products
        ->get(); // Retrieve the products
    
    // Output the products
    return $products;
}
function getCategoryBySlug($slug){
    if ($slug != '') {
       
        //$cat_id = DB::table('categories')->select('id')->where('categoryTitle','=',$category_name);
        $cat_slug = category::where('category_slug', $slug)
        ->first();
        
                if (!empty($cat_slug)) {
                    return $cat_slug;
                } else {
                    return false;
                }
            } else {
                return false;
            }
}
function getCategoryBySlugApi($slug){
    if ($slug != '') {
       
        //$cat_id = DB::table('categories')->select('id')->where('categoryTitle','=',$category_name);
        $cat_slug = category::where('category_slug', $slug)
        ->first();
        
                if (!empty($cat_slug)) {

                    return $cat_slug;
                } else {
                    return false;
                }
            } else {
                return false;
            }
}
function getDealsCategoryProduct($key,$slug){
  
    if($slug == 'welcome-deal'){
        $discount = '20';
    }elseif($slug == 'best-sellers'){
        $discount = '50';
    }elseif($slug == 'christmas-deal'){
    $discount = '100';
    }
    $all_categories = array();
    $all_categories = Category::where('categoryTitle', '!=', 'Uncategorized')
    ->where('status', '1')
    ->where('category_slug', '=', $key)
    ->whereHas('products', function($query) use ($discount) {
        $query->where('status', '1')
              ->where('discount_percentage', '<=', $discount)->where('on_sale',1);
    })
    ->withCount(['products' => function($query) use ($discount) {
        $query->where('status', '1')
              ->where('discount_percentage', '<=', $discount)->where('on_sale',1);
    }])->first();
    // dd($all_categories);
    if(!empty($all_categories)){
        return $all_categories->products_count;
    }else{
        return $all_categories;
    }
    
}

function getProductsCountByStoreId($storeId) {
    $query = DB::table('products')->where('store_id', $storeId); 
    $count = $query->count();  
    return $count;
}
    

function copyImageFromUrlToNewFolder($imageUrl)
{
    if(!empty($imageUrl)){
        $imageName = basename($imageUrl); // Get the filename from the URL (e.g., "th (5)_15607.jpg")
        $imageName =uniqid() . '_' . $imageName;
        
        $newFolder = 'uploads/orders'; // New folder to store the image
        
        $newImagePath = $newFolder . '/' . $imageName;

        // Ensure the folder exists
        $absoluteNewFolder = public_path($newFolder);
        if (!File::exists($absoluteNewFolder)) {
            File::makeDirectory($absoluteNewFolder, 0755, true);
        }

        // Convert the URL to a local path
        $relativePath = parse_url($imageUrl, PHP_URL_PATH); // Extract the relative path from the URL
        $sourcePath = public_path(ltrim($relativePath, '/'));

        // Check if the source file exists
        if (!File::exists($sourcePath)) {
            return response()->json(['message' => 'Source file not found!'], 404);
        }

        // Copy the file to the new folder
        if (File::copy($sourcePath, public_path($newImagePath))) {
            $newImagePath = basename($newImagePath);       
        }

        return $newImagePath;
    } else {
        return null;
    }
}

function getOrderImage($image)
{
    $path = 'uploads/orders/';

    if (!empty($image)) {
        return asset($path . $image);
    }
    return null;
}
function isValidPrice($value) {
    return is_numeric($value) && $value >= 0 && !preg_match('/[^0-9.]/', $value);
}

function isValidStock($value) {
    return is_numeric($value) && $value >= 0 && floor($value) == $value;
}

function webPImages($path,$size){
    try{
        $response = Http::withOptions(['allow_redirects' => true])->get($path);
        // Check if the request was successful
        if (!$response->successful()) {
            return null; // Image not found or inaccessible
        }
        // Get the filename without extension
        $filename = pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_FILENAME) . '.webp';

        // Convert image to WebP
        $image = Image::make($response->body())->encode('webp', 80);

        // Define the WebP save path (same directory as original image)
        $webpPath = public_path('uploads/'.$size .'/'. $filename);

        // Save the WebP image
        $image->save($webpPath);

        // Return the WebP image URL
        return asset('uploads/'.$size .'/' . $filename);
    }catch(Exception $e){
        dd($e->getMessage());
    }
    
}
function getCountryByCode($code = '') {
    $query = DB::table('countries')->where('code', $code)->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}
function getStatebyCode($code = '') {
    $query = DB::table('states')->where('code', $code)->first();
    if (!empty($query)) {
        return objToArray($query);
    } else {
        return false;
    }
}

function getImageAltById($id) {
    $attachment = Attachments::find($id);
    if ($attachment) {
        return $attachment->alt ?? '';
    }
    return '';
}
if (!function_exists('hasPermission')) {
    function hasPermission($permission)
    {
        return auth()->check() && auth()->user()->hasPermission($permission);
    }
}

if (!function_exists('hasRole')) {
    function hasRole($role)
    {
        return auth()->check() && auth()->user()->hasRole($role);
    }
}

function canAccess(string $permission): bool
{
    $user = Auth::user();
    if (!$user) {
        return false;
    }

    // If `all_permissions` is set (from CheckRole middleware)
    if ($user->relationLoaded('all_permissions')) {
        return $user->all_permissions->contains('name', $permission);
    }

    // fallback: use hasPermission()
    return $user->hasPermission($permission);
}

function getAllRoles()
{
    return Role::all()->toArray();
}