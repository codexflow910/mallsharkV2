<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Filters\Filtersaccess;

class FiltersController extends Controller {

    private $_request;
    private $_filterobj;

    public function __construct(Request $request) {
        $this->_request = $request;
        $this->_filterobj = new Filtersaccess();
    }

    public function getfilters() {
        $filterData = $this->_filterobj->allFilters();
        //print_rr($filterData); exit;
        return view('admin/listing-filters', ['filterData' => $filterData]);
    }

    public function updateFilters() {
        if ($this->_request->ajax()) {
            $postedData = $this->_request->input();
            $this->_filterobj->updateFilters($postedData);
            echo "success";
        }
    }

    public function statusFilters($id, $status) {
        $this->_filterobj->ChangeStatusFilters($id, $status);
        $this->_request->session()->flash('SuccessMessage', 'Section has been updated successfully.');
        return redirect()->back();
    }

    public function deleteFilters($id) {
        $res = $this->_filterobj->deleteFilter($id);
        if ($res == false) {
            $this->_request->session()->flash('ErrorMessage', 'You Cannot Delete this filter Because there are some products associated with this filter please update those products first.');
            return redirect()->back();
        } else {
            $this->_request->session()->flash('SuccessMessage', 'Filter has been deleted successfully.');
            return redirect()->back();
        }
    }

    

}
