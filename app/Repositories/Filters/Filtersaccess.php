<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Filtersaccess
 *
 * @author Asif
 */

namespace App\Repositories\Filters;

use App\BaseAccess;
use App\Models\Filters;
use App\Models\Filtervalues;
use App\Models\Product;
use DB;

class Filtersaccess extends BaseAccess {

    private $_filters;
    private $_filtersvalues;
    private $_products;

    public function __construct() {
        $this->_filters = new Filters();
        $this->_filtersvalues = new Filtervalues();
    }

    public function allFilters() {
        $data = array();
        $query = $this->_filters->get();
        if (!empty($query)) {
            $filterData = $query->toArray();
            foreach ($filterData as $fd) {
                $query2 = $this->_filtersvalues->where('filter_id', '=', $fd['filter_id'])->get()->toArray();
                $data[$fd['filter_id']] = $query2;
            }
            return $data;
        } else {
            return false;
        }
    }

    public function updateFilters($data) {
        $this->_filtersvalues->where('f_value_id', '=', $data['value_id'])->update(['value' => $data['value']]);
    }

    public function ChangeStatusFilters($id, $status) {
        if ($status == "Deactivate") {
            $status = '0';
        } else if ($status == "Activate") {
            $status = '1';
        }
        $this->_filters->where('filter_id', '=', $id)->update(['filter_status' => $status]);
    }

    function deleteFilter($id) {
        //for delete add status 2

        if ($id == 1) {
            $filerValues = $this->_filtersvalues->select('value_name')->where('filter_id', '=', $id)->get()->toArray();
            if (!empty($filerValues))
                foreach ($filerValues as $fv) {
                    $arr[] = $fv['value_name'];
                }
            $productCount = DB::table('products')->whereIn('product_condition', $arr)->count();
            if ($productCount > 0) {
                return false;
            }
        }
        $this->_filters->where('filter_id', '=', $id)->update(['filter_status' => '2']);
        return true;
    }

    public function getFiltervaluesBYId($id) {
        $query = $this->_filtersvalues->where('filter_id', '=', $id)->get();
        if (!empty($query)) {
            return $query->toArray();
        } else {
            return false;
        }
    }

}
