<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\search;

use App\BaseAccess;
use App;
use DB;
use File;
use Image;
use Session;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Brands;
use App\Models\CategoryProduct;
use App\Models\Cart;
use App\Repositories\Product\Productaccess;

/*
 * Description of Productaccess
 *
 * @author Kamran
 */

class Searchaccess extends BaseAccess {

    private $_product;
    private $_user;
    private $_cart;
    private $productAccessObj;

    public function __construct() {
        $this->_product = new Product();
        $this->_cart = new Cart();



        // $this->_user = new User();
    }

    public static function search_query($params) {
        $productAccessObj = new Productaccess();
        $limit = 500;
        $page = 0;
        if (isset($params['page']) && $params['page'] != '') {
            $page = $params['page'] - 1;
        }
        $values = $productAccessObj->getAllValues();
        if (empty($params) || ($page > 0 && count($params) == 0)) { /*
         * all results
         */
            $products = DB::table('products')
                    ->orderBy('products.created_at', 'DESC')
                    ->OFFSET($page * $limit)
                    ->limit($limit)
                    ->where('productType', '=', 'product')
                    ->where('status', '=', '1')
                    ->where('is_active', '=', 1)
                    ->select('*')
                    ->get();
            return array('results' => $products, 'values' => $values);
        } else {
            /*
             * search filter resutls
             */
            //$values = array();
            $child_categories = isset($params['child_categories']) ? $params['child_categories'] : array();
            $keyword = isset($params['keyword']) ? $params['keyword'] : '';
            $child_categories_sub = isset($params['child_categories_sub']) ? $params['child_categories_sub'] : array();
            $child_brands = isset($params['child_brand']) ? $params['child_brand'] : '';
            $array_ids = array();

            if (isset($params['parent_category']) && $params['parent_category'] != '') {
                $category = DB::table('categories')->where('category_slug', $params['parent_category'])->value('id');

                $values = $productAccessObj->getAllValues($category);

                $parent_cat = $category;

                if ($child_categories != '' && count($child_categories) > 0) {
                    /*
                     * array of child categories ids
                     */
                    $array1 = array();
                    $category = DB::table('categories')->select('id')->whereIn('category_slug', $child_categories)->get();
                    if (isset($category[0])) {
                        //$values = $productAccessObj->getAllValues($category[0]->id);
                    }

                    $new_arr = array();
                    /*
                     * fetching child categoires sub child categories
                     */
                    foreach ($category as $row) {
                        array_push($array1, $row->id);
                    }
                    foreach ($array1 as $id) {
                        $child_ids = getChildsCategoriesIds($id);
                        foreach ($child_ids as $key => $id) {
                            array_push($new_arr, $key);
                        }
                    }
                    /*
                     * child categories and their child categories
                     */
                    $array_ids = array_merge($new_arr, $array1);
                }
                if ($child_categories_sub != '' && count($child_categories_sub) > 0) {
                    /*
                     * array of sub child categories ids
                     */
                    $array_ids = array();
                    $category = DB::table('categories')->select('id')->whereIn('category_slug', $child_categories_sub)->get();


                    foreach ($category as $row) {
                        array_push($array_ids, $row->id);
                    }
                }



                if (count($array_ids) > 0) {

                    /*
                     * query if child or sub child categories are available
                     */

                    $query = DB::table('product_category')
                            ->join('products', 'products.product_id', '=', 'product_category.product_id')
                            ->whereIn('product_category.category_id', $array_ids)
                            ->groupBy('product_category.product_id');
                } else {
                    /*
                     * query if childs or  sub childs are not selected in query string 
                     */
                    $categories_child_sub_child = array();
                    /*
                     * fetching parent category's child categories
                     */
                    $child_ids = getChildsCategoriesIds($parent_cat);

                    foreach ($child_ids as $key => $id) {
                        array_push($categories_child_sub_child, $key);
                    }
                    $arra_sub_sub = array();
                    /*
                     * fetching child category's sub child categories
                     */
                    foreach ($categories_child_sub_child as $id) {
                        $child_ids = getChildsCategoriesIds($id);
                        foreach ($child_ids as $key => $id) {
                            array_push($arra_sub_sub, $key);
                        }
                    }
                    /*
                     * geting all categories (parent,child,sub child)
                     */
                    array_push($categories_child_sub_child, $category);
                    $new_array = array_merge($categories_child_sub_child, $arra_sub_sub);

                    // die();
                    $query = DB::table('product_category')
                            ->join('products', 'products.product_id', '=', 'product_category.product_id')
                            ->whereIn('product_category.category_id', $new_array)
                            ->groupBy('product_category.product_id');
                }
            } else {
                /*
                 * query if no categoryt is selected
                 */
                $query = DB::table('products')
                        ->where('productType', '=', 'product');
            }



            /*
             *  Brand filert in search
             */
            if (isset($params['brand']) && $params['brand'] != '') {
                $brand = DB::table('brands')->where('brand_name', $params['brand'])->value('id');
                $query->join('brands', 'products.brand_id', '=', 'brands.id')
                        ->where('brands.id', '=', $brand);
            }

            /*
             *  store filert in search
             */
            if (isset($params['store']) && $params['store'] != '') {

                $query->join('stores', 'products.store_id', '=', 'stores.store_id')
                        ->where('stores.store_id', '=', $params['store']);
            }
            /*
             * Values filter in search
             */
            if (isset($params['value_id']) && count($params['value_id']) > 0) {

                $query->join('product_values', 'products.product_id', '=', 'product_values.product_id')
                        ->wherein('product_values.value_id', $params['value_id']);
            }

            /*
             * min and max price filter
             */
            if (isset($params['price_min']) && isset($params['price_max']) && $params['price_min'] != '' && $params['price_max'] != '') {
                $query->whereBetween('products.price', [$params['price_min'], $params['price_max']]);
            }
            /*
             * when only min price is available
             */
            if (isset($params['price_min']) && !isset($params['price_max'])) {
                $query->where('products.price', '>=', $params['price_min']);
            }
            /*
             * when only max price is available
             */
            if (isset($params['price_max']) && !isset($params['price_min'])) {
                $query->where('products.price', '<=', $params['price_max']);
            }

            if ($keyword != '') {
                /// shop_name
                $query->join('stores', 'products.store_id', '=', 'stores.store_id');
            }


            $query->orderBy('products.created_at', 'DESC')
                    ->OFFSET($page * $limit)
                    ->limit($limit)
                    ->select('*')
                    ->where('products.status', '=', '1')
                    ->where('products.is_active', '=', 1)
                    ->where('products.productType', '=', 'product');
            if ($keyword != '') {
                $query->where('products.title', 'like', '%' . $keyword . '%')
                        ->orwhere('stores.shop_name', 'like', '%' . $keyword . '%');
            }
            //echo $query->toSql();
            //print_rr($query->get());
            //exit;
            $arr = array('results' => $query->get(), 'values' => $values);

            return $arr;
        }
    }

    public function getCartAddedProduct() {

        $arr = array();
        $userId = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']:'';
        if (!empty($userId)) {
            $producData = $this->_cart->where('user_id', $userId)->select('product_id', 'product_quantity')->get();
            if (!empty($producData)) {
                $proDuctArr = $producData->toArray();
                foreach ($proDuctArr as $pA) {
                    $arr[$pA['product_id']] = $pA['product_quantity'];
                }
                return $arr;
            } else {
                return false;
            }
        } else if (!empty($_COOKIE['cartCookie'])) {
            $producData = $this->_cart->where('random_key', $_COOKIE['cartCookie'])->select('product_id', 'product_quantity')->get();
            if (!empty($producData)) {
                $proDuctArr = $producData->toArray();
                foreach ($proDuctArr as $pA) {
                    $arr[$pA['product_id']] = $pA['product_quantity'];
                }
                return $arr;
            } else {
                return false;
            }
        }
    }

    public function searchResults() {
        $results = $this->_product
                ->where('products.status', '=', '1')->orderBy('is_feature', 'DESC')->
                get();
        if (!empty($results)) {
            return $results->toArray();
        } else {
            return false;
        }
    }

    public function getAllStores() {
        return $allStores = DB::table('stores')
                ->select('store_id', 'shop_name')
                ->get()
                ->toArray();
    }

}