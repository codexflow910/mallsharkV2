<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Favourites;

use App\BaseAccess;
use App\Models\Favourites;
use Illuminate\Support\Str;
use App;
use DB;
use Session;
use Cookie;
use Crypt;
use Request;
/**
 * Description of Favouritesaccess
 *
 * @author Asif
 */
class Favouritesaccess extends BaseAccess {

    private $_favourites;

    public function __construct() {
        $this->_favourites = new Favourites();
    }

    public function saveFavourite($data) {
       
        $userId = Session::get('userData')['user_id'];
        $favProductdata = $this->_favourites->where('product_id', '=', $data['product_id'])->where('user_id', '=', $userId)->first();
      
        if (!empty($favProductdata)) {
            $favId = $this->_favourites->find($favProductdata['favourite_id']);
            $favId->delete();
            return false;
        } else {
            $this->_favourites->product_id = $data['product_id'];
            $this->_favourites->user_id = $userId;
            $this->_favourites->save();
            return true;
        }
    }
    public function saveFavouriteApi($data) {
       
        $userId = auth()->user()->user_id;
        $favProductdata = $this->_favourites->where('product_id', '=', $data['product_id'])->where('user_id', '=', $userId)->first();
        if (!empty($favProductdata)) {
            $favId = $this->_favourites->find($favProductdata['favourite_id']);
            $favId->delete();
            return false;
        } else {
            $this->_favourites->product_id = $data['product_id'];
            $this->_favourites->user_id = $userId;
            $this->_favourites->save();
            return true;
        }
    }
    public function getBuyerFavourites($data) {
        $userId = Session::get('userData')['user_id'];
        if (empty($userId)) {
            $userId = Request::segment(2);
        }
        $order = 'DESC';
        $query = DB::table('favourites')
                ->join('products', 'favourites.product_id', '=', 'products.product_id')
                ->where('favourites.user_id', $userId)
                // ->orderBy('favourites.favourite_id', $order)
                ->select('favourites.favourite_id', 'favourites.product_id', 'favourites.created_at', 'favourites.user_id', 'products.title', 'products.slug', 'products.price', 'products.status', 'products.feature_Image', 'products.created_at');

        if (isset($data['price']) && $data['price'] != '') {
            $query->orderBy('products.price', $data['price']);
        } else if (isset($data['order']) && $data['order'] != '') {
            $order = $data['order'];
            $query->orderBy('favourites.favourite_id', $order);
        } else {
            $query->orderBy('favourites.favourite_id', $order);
        }
        $favData = $query->get();

        if (!empty($favData)) {
            return $this->objToArray($favData);
        } else {
            return false;
        }
    }

    public function getBuyerFavouritesApi($data) {
        $userId = auth()->user()->user_id;
        $order = 'DESC';
        $query = DB::table('favourites')
                ->join('products', 'favourites.product_id', '=', 'products.product_id')
                ->where('favourites.user_id', $userId)
                // ->orderBy('favourites.favourite_id', $order)
                ->select('favourites.favourite_id', 'favourites.product_id', 'favourites.created_at', 'favourites.user_id', 'products.title', 'products.slug', 'products.price', 'products.status', 'products.feature_Image', 'products.created_at','products.on_sale','products.sale_price','products.discount_percentage');

        if (isset($data['price']) && $data['price'] != '') {
            $query->orderBy('products.price', $data['price']);
        } else if (isset($data['order']) && $data['order'] != '') {
            $order = $data['order'];
            $query->orderBy('favourites.favourite_id', $order);
        } else {
            $query->orderBy('favourites.favourite_id', $order);
        }
        $favData = $query->get();

        if (!empty($favData)) {
            return $this->objToArray($favData);
        } else {
            return false;
        }
    }
//
//    public function getBuyerFavourites($data) {
//        $userId = Session::get('userData')['user_id'];
//        $order = 'DESC';
//        if (isset($data['order']) && $data['order'] != '') {
//            $order = $data['order'];
//        }
//        $favData = DB::table('favourites')
//                        ->join('products', 'favourites.product_id', '=', 'products.product_id')
//                        ->where('favourites.user_id', $userId)
//                        ->orderBy('favourites.favourite_id', $order)
//                        ->select('favourites.favourite_id', 'favourites.product_id', 'favourites.created_at', 'favourites.user_id', 'products.title', 'products.slug', 'products.price', 'products.status', 'products.feature_Image', 'products.created_at');
//        if (!empty($favData)) {
//            return $this->objToArray($favData);
//        } else {
//            return false;
//        }
//    }

    public function removeFavourite($data) {
        $favId = $this->_favourites->find($data['favourite_id']);
        if (!$favId) {
            return [
                'status' => false,
                'counter' => 0, // No favorite products left
            ];
            
        }
        // Delete the favorite product
        $favId->delete();
        
        // Recalculate the count of remaining favorite products
        $userId = !empty(Session::get('userData')['user_id'])?Session::get('userData')['user_id']: '';
        $remainingCount = $this->_favourites->where('user_id', $userId)->count();
        if($remainingCount > 0){
            return [
                'status' => true,
                'counter' => $remainingCount,
            ];
        }else{
            return [
                'status' => false,
                'counter' => $remainingCount,
            ];
        }
       
    }

    public function removeFavouriteApi($data) {
        $userId = auth()->user()->user_id;
        $favId = $this->_favourites->find($data['favourite_id']);
        if (!$favId) {
            return [
                'status' => false,
                'counter' => 0, // No favorite products left
            ];
            
        }
        // Delete the favorite product
        $favId->delete();
        
        // Recalculate the count of remaining favorite products
      
        $remainingCount = $this->_favourites->where('user_id', $userId)->count();
        if($remainingCount > 0){
            return [
                'status' => true,
                'counter' => $remainingCount,
            ];
        }else{
            return [
                'status' => true,
                'counter' => $remainingCount,
            ];
        }
       
    }
    public function objToArray($arr) {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

}
