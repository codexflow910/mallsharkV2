<?php

namespace Database\Seeders;

use App\Http\Controllers\ElasticSearchController;
use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreDeactivateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    protected $_esearch;

    public function __construct()
    {
        // $this->_esearch = $user;
        $this->_esearch = new ElasticSearchController();
    }
    public function run()
    {
        $stores = Store::all();
        foreach ($stores as $store) {
            if (empty($store->stripe) || empty($store->account_id)) {
                echo "$store->store_id\n";
            }
            $store->store_status = 0;
            $store->save();
            foreach ($store->products as $products) {
                $products->status = 0;
                $products->save();
                // echo "$store->store_id\n";
                // echo "$products->product_id\n";
                $this->_esearch->esMApping($products->product_id);
            }
        }
    }
}
