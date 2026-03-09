<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StoreOrder;
use Illuminate\Database\Eloquent\Model;
use App\Models\ShippingAddress;
use Session;
use App\Repositories\Location\LocationAccess;

class ShippingController extends Controller {

    private $location;
    private $shipping_address;

    public function __construct() {
        $this->location = new LocationAccess();
        $this->shipping_address = new ShippingAddress();
    }

    public function index(Request $request) {
        $all_inputs = $request->all();
        if (count($all_inputs) > 0) {
            if (isset($all_inputs['sh_id']) && $all_inputs['sh_id'] != '0') {
                $address = ShippingAddress::find($all_inputs['sh_id']);

                $address->user_id = getCurrentUserId();
                $address->full_name = $all_inputs['full_name'];
                $address->country = $all_inputs['country'];
                $address->state = $all_inputs['state'];
                $address->city = $all_inputs['city'];
                $address->street = $all_inputs['street'];
                $address->post_code = $all_inputs['post_code'];
                $address->flat_other = $all_inputs['flat_other'];
                $address->save();
            } else {
                $this->shipping_address->user_id = getCurrentUserId();
                $this->shipping_address->full_name = $all_inputs['full_name'];
                $this->shipping_address->country = $all_inputs['country'];
                $this->shipping_address->state = $all_inputs['state'];
                $this->shipping_address->street = $all_inputs['street'];
                $this->shipping_address->city = $all_inputs['city'];
                $this->shipping_address->post_code = $all_inputs['post_code'];
                $this->shipping_address->flat_other = $all_inputs['flat_other'];
                $this->shipping_address->save();
            }
        }
        $last_record = ShippingAddress::where('user_id', '=', getCurrentUserId())->orderBy('sh_id', 'desc')->first();

//print_r($last_record);
        $countries = $this->location->getAllCountries();
        $states = array();
        if (isset($last_record['country']) && $last_record['country'] != '') {
            $states = $this->location->getStateByCountryCode($last_record['country']);
        }
        return view('shipping.shipping_address', array('countries' => $countries, 'last_record' => $last_record, 'states' => $states));
    }

    public function get_states_by_country_code(Request $request) {
     $inputs = $request->input('country_code');
       
        if ($inputs != '') {
             $states = $this->location->getStateByCountryCode($inputs);
            return json_encode($states);
        }
    }

    public function addStoreShippingMethod() {
        return view('shipping/shippingmethod');
    }

    public function createShippingOption(){
        // if ($this->isMobileDevice()) {
        // return view('product.shipping.mobile.manageShippingOptions');
        // }else{
            return view('product.shipping.manageShippingOptions');
        // }

    }


}
