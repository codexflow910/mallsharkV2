<?php

namespace App\Http\Controllers;

use Redirect;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\FeatureProducts;
use Illuminate\Support\Facades\DB;
use App\Repositories\Featured\Featureaccess;

class FeaturedController extends Controller
{
    private $_request;
     private $_featureobj;

    public function __construct(Request $request) {
        $this->_request = $request;
         $this->_featureobj = new Featureaccess();
    }

    public function index() {
        $postedData = $this->_request->input();
        $result = $this->_featureobj->getBuyerFeaturedProduct($postedData);
      //  dd($result);
        // if ($this->isMobileDevice()) {
        //     return view('featuredProduct.mobile.index', ['favData' => $result]);
        // } else {
            return view('featuredProduct.index', ['favData' => $result]);
        // }
    }

    public function removeFeatured() {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $response = $this->_featureobj->removeFeaturedProduct($postedData);
            if ($response == true) {
                echo 'success';
            } else {
                echo 'error';
            }
        }
    }

    public function remove(){

        $event = Product::get();
         foreach($event as $d){
            
            if (Carbon::now() > $d->feature_enddate &&  $d->feature_enddate !=  '0000-00-00' )  {
                DB::table('products')->where('product_id',$d->product_id)->update(['is_feature' => "0" , 'feature_startdate' => 'NULL' , 'feature_enddate' => 'NULL']);

           }
         }
       
    }

}
