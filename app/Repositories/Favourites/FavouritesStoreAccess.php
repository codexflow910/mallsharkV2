<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Favourites;

use App\BaseAccess;
use App\Models\FavouriteStore;
use DB;
use Session;
use Request;
/**
 * Description of FavouritesStoreAccess
 *
 * @author Noman
 */
class FavouritesStoreAccess extends BaseAccess {

    private $_favStore;

    public function __construct() {
        $this->_favStore = new FavouriteStore();
    }

    public function saveFavourite($data) {
       
        $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']: "";
        $favStoredata = $this->_favStore->where('store_id', '=', $data['store_id'])->where('user_id', '=', $userId)->first();
      
        if (!empty($favStoredata)) {
            $favId = $this->_favStore->find($favStoredata['id']);    
            $favId->delete();
            return false;
        } else {
            $this->_favStore->store_id = $data['store_id'];
            $this->_favStore->user_id = $userId;
            $this->_favStore->save();
            return true;
        }
    }

    public function getBuyerFavourites($data) {
        $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']: "";
        if (empty($userId)) {
            $userId = Request::segment(2);
            
        }
        $order = 'DESC';
        $query = DB::table('favourite_stores')
                ->join('stores', 'favourite_stores.store_id', '=', 'stores.store_id')
                ->where('favourite_stores.user_id', $userId)
                ->select('favourite_stores.id', 'favourite_stores.store_id', 'favourite_stores.created_at', 'favourite_stores.user_id',
                 'stores.store_name', 'stores.store_slug', 'stores.store_status', 'stores.store_logo',  'stores.created_at');
            
        if (isset($data['order']) && $data['order'] != '') {
            $order = $data['order'];
            $query->orderBy('favourite_stores.id', $order);
        } else {
            $query->orderBy('favourite_stores.id', $order);
        }
        $favData = $query->get();


        if (!empty($favData)) {
            return $this->objToArray($favData);
        } else {
            return false;
        }
    }

    public function removeFavourite($data) {
        $favId = $this->_favStore->where('store_id', $data['store_id']);
        $favId->delete();
        return true;
    }

    public function objToArray($arr) {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }

}
