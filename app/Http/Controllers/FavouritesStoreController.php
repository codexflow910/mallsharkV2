<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Favourites\FavouritesStoreAccess;

class FavouritesStoreController extends Controller
{

    private $_request;
    private $_favobj;

    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->_favobj = new FavouritesStoreAccess();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $postedData = $this->_request->input();
        try {
            $result = $this->_favobj->getBuyerFavourites($postedData);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }

        return view('favourites.Store.index', ['favData' => $result]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            try {
                $response = $this->_favobj->saveFavourite($postedData);
                if ($response == true) {
                    return  [
                        'status' => 'success',
                        'message' => 'Favorite Store added successfully',
                    ];
                } else {
                    return  [
                        'status' => 'error',
                        'message' => 'You have unfavorited your Store',
                    ];
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            try {

                $response = $this->_favobj->removeFavourite($postedData);
                if ($response == true) {
                    return  [
                        'status' => 'error',
                        'message' => 'You have unfavorited your Store',
                    ];
                } else {
                    return  [
                        'status' => 'error',
                        'message' => 'please check your store Somethin went wrong',
                    ];
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
        }
    }
}
