<?php
namespace Packages\Dashboard\Contracts\V4;

use Packages\Store\Models\V4\States;
use Packages\Product\Models\V4\Product;
use Packages\Product\Models\V4\Attachments;
use Illuminate\Support\Facades\DB;
use Packages\Distributors\Models\V4\DistributorDocument;

trait DashboardHelpers
{

    function getStatebyId($stateId = '') {
        $query = States::where('state_id', $stateId)->first();
        return $query;
    }

    function getAttachmentUrlById($id, $size = 'media') {

        if(isset($id)) $id = json_decode($id);
    
        if ($id != '' && is_int($id)) {

            $data = Attachments::find($id);
            if ($data) {
                return url('/uploads') . '/' . $size . '/' . $data->fileName . '_' . $id . '.' . $data->fileExtension;
            } 
        }
    }

    function getStoreProductsCount($storeId, $decode = null)
    {
        if ($decode == null || $decode = "") {
            $storeId = base64_decode($storeId);
        }
        $query = Product::where('store_id', '=', $storeId);
       
            $query->where('status', '!=' , '2');
        
        $count = $query->count();
        if ($count > 0) {
            return $count;
        } else {
            return 0;
        }
    }
  

    function checkStoreStatus($status) {
        if ($status == "0") {
            return $status;
        }  else if($status == "3") {
            return $status;
        }else if($status == "1"){
            return $status;
        }else if($status == "2"){
            return $status;
        }else{
            return '';
        }
    }
   
    public static function getDistributorDocs($storeId)
    {
        return DistributorDocument::where('distributor_id', $storeId)->get();
    }
}