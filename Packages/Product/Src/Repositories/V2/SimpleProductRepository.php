<?php

namespace Packages\Product\Repositories\V2;


use Packages\Product\Models\V2\Product;
use Packages\Product\Config\ProductConstants;
use App\Http\Controllers\ElasticSearchController;

use Packages\Product\Contracts\V2\ProductRepositoryInterface ;


class SimpleProductRepository extends ProductRepository implements ProductRepositoryInterface
{
      private $_esearch;

    public function __construct(Product $model)
    {
        $this->model = $model;
        $this->_esearch = new ElasticSearchController();
    
    }

        public function changestatus($productId,$productStatus, $store_id)
        {
         
            $product = Product::select()->where('product_id',$productId)->first();
                if($productStatus=="Out")
                {
                    $product = Product::where('product_id', $productId) 
                        ->update(['current_stock' =>'0']); 
                         $product = Product::find($productId);
                        $product->searchable();
                }
                else if($productStatus=="Active"){
                    $product = Product::where('product_id', $productId) 
                        ->update(['status' =>ProductConstants::STATUS_ACTIVE]);
                         $product = Product::find($productId);
                        $product->searchable();
                }
                else if($productStatus=="Deactive"){
                    $product = Product::where('product_id', $productId) 
                        ->update(['status' =>ProductConstants::STATUS_DEACTIVATED]); 
                         $product = Product::find($productId);
                        $product->unsearchable();
                }
                else if($productStatus=="Delete"){
                    $product = Product::where('product_id', $productId) 
                        ->update(['is_deleted' =>'1','status'=>ProductConstants::STATUS_DELETED]); 
                       $product = Product::find($productId);
                        $product->unsearchable();
                }

            }
}