<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Tax\TaxAccess;
use Redirect;
use DB;
use Session;
class TaxControllers extends Controller
{

    private $_request;
    private $_tax;


    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->_tax = new TaxAccess();
    }

    public function index()
    {
        if ($this->_request->post()) {
            $postedData = $this->_request->all();
            $this->_tax->updateTexById($postedData);
            $this->_request->session()->flash('updatesuccessmessage', 'Tax information has been updated successfully.');
            return Redirect::back();
        } else {
            $taxData = $this->_tax->getTaxData();
            return view('tax/tax', ['taxData' => $taxData]);
        }
    }

    public function getTaxDataByStateId()
    {
        if ($this->_request->ajax()) {
            $stateId = $this->_request->input('state_id');
            $taxResult = $this->_tax->getTaxDataByStateId($stateId);
            echo json_encode($taxResult);
        }
    }


}
