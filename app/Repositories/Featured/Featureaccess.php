<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Featured;

use DB;
use App;
use Crypt;
use Cookie;
use Request;
use Session;
use App\BaseAccess;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\FeatureProducts;
/**
 * Description of Featureaccess
 *
 * @author Noman
 */
class Featureaccess extends BaseAccess {

    private $_favourites;
    private $_product;

    public function __construct() {
        $this->_feature = new FeatureProducts();
        $this->_product = new Product();
    }
              
    public function getBuyerFeaturedProduct($data) {
        $userId = Session::get('userData')['user_id'];
        if (empty($userId)) {
            $userId = Request::segment(2);
        }
        $order = 'DESC';
        $query = DB::table('products')
        ->select( 'product_id', 'is_feature', 'feature_startdate', 'feature_enddate', 'user_id', 'title', 'slug', 'price', 'status', 'feature_Image', 'created_at')
        ->where('is_feature' , '1');
        if (isset($data['price']) && $data['price'] != '') {
            $query->orderBy('price', $data['price']);
        }
        $featureData = $query->get();
       // echo 'Data: <pre>' .print_r($featureData,true). '</pre>'; die;

        if (!empty($featureData)) {
            return $this->objToArray($featureData);
        } else {
            return false;
        }
    }

    public function removeFeaturedProduct($data) {
       
        $featuredId = $data['featured_id'];
        DB::table('products')->where('product_id', $featuredId)->update(['is_feature' => '0' , 'feature_startdate' => '0' , 'feature_enddate' => '0']);
      
        return true;
    }

    public function objToArray($arr) {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

}
