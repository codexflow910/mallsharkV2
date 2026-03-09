<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
Use DB;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class BrandsSale extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brandsale:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $logArray = array();
        $counter = 0;
        $productCounter = 0;
        $date = date('Y-m-d');
        $from = date('Y-m-d', strtotime("+1 days", strtotime($date)));
        $to = date('Y-m-d', strtotime("-7 days", strtotime($from)));
//         DB::enableQueryLog();
        $query = DB::table('store_orders')->whereBetween('created_at', [$to, $from])->get();
//        dd(DB::getQueryLog());
        $orders = objToArray($query);
        $newArray = array();
        foreach ($orders as $key => $od) {
            if (isset($newArray[$od['store_id']])) {
                $newArray[$od['store_id']] = $od['total_items'] + $newArray[$od['store_id']];
            } else {
                $newArray[$od['store_id']] = $od['total_items'];
            }
        }
        arsort($newArray);
        if (count($newArray) >= 7) {
            DB::table('stores')->update(['top_seller' => 0]);
            foreach ($newArray as $key2 => $na) {
                if ($counter <= 6) {
                    DB::table('stores')->where('store_id', $key2)->update(['top_seller' => 1]);
                    echo 'store id' . $key2 . ' has been set for top selling<br>';
                    $logArray['store_ids'][] = $key2;
                }
                $counter++;
            }
        }
//        print_rr($newArray);
        //top selling products
        $arr = array();
        foreach ($orders as $key => $od) {
            $qery2 = DB::table('order_items')->select('item_id', 'quantity')->where('order_id', $od['order_id'])->first();
            if (!empty($qery2)) {
                $qunatity = $qery2->quantity;
                if (isset($arr[$qery2->item_id])) {
                    $qunatity = $qunatity + $arr[$qery2->item_id];
                }
                $arr[$qery2->item_id] = $qunatity;
            }
        }
        arsort($arr);
        if (count($arr) >= 4) {
            DB::table('products')->update(['top_selling' => 0]);
            foreach ($arr as $key3 => $pi) {
                if ($productCounter <= 6) {
                    DB::table('products')->where('product_id', $key3)->update(['top_selling' => 1]);
                    echo 'product id' . $key3 . ' has been set for top selling<br>';
                    $logArray['product_ids'][] = $key3;
                }
                $productCounter++;
            }
        }
        $orderLog = new Logger('');
        $orderLog->pushHandler(new StreamHandler(storage_path('logs/brandsale.log')), Logger::INFO);
        $orderLog->info('brand sale run', $logArray);
    }

}
