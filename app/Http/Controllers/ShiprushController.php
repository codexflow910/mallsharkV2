<?php
define("SHIPPINGZSIMULATOR_VERSION","4.0.12.1905");
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShiprushController extends Controller {

    private $_request;

    public function __construct(Request $request) {
        $this->_request = $request;
    }

    public function fetchDbOrders() {
        $posteData= $this->_request->input();
        
    }

}
