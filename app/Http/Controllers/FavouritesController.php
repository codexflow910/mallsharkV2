<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Favourites\Favouritesaccess;
use GrahamCampbell\ResultType\Success;
use Redirect;

class FavouritesController extends Controller {

    private $_request;
    private $_favobj;

    public function __construct(Request $request) {
        $this->_request = $request;
        $this->_favobj = new Favouritesaccess();
    }

    public function index() {
        $postedData = $this->_request->input();
        $result = $this->_favobj->getBuyerFavourites($postedData);
        // if ($this->isMobileDevice()) {
        //     return view('favourites.mobile.index', ['favData' => $result]);
        // } else {
            return view('favourites.index', ['favData' => $result]);
        // }
    }

    public function addtoFavourite() {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $response = $this->_favobj->saveFavourite($postedData);
            if ($response == true) {
                return  ['status' => 'success',
                'message' => 'Favorite product added successfully',                
                 ];
            } else {
                return  ['status' => 'error',
                'message' => 'You have unfavorited your product',                
                ];
                
            }
        }
    }

    public function removeFavourite() {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $response = $this->_favobj->removeFavourite($postedData);

            return response()->json([
                'status' => $response['status'],
                'counter' => $response['counter'],
            ]);
        }
    }

}
