<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Twilio;
use DB;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
class SendSmsOffer extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smsoffer:run';

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
        $sendsmsOffer=array();
        $userIntrest = DB::table('user_intrests')
                ->join('users', 'users.user_id', '=', 'user_intrests.user_id')
                ->select('user_intrests.category_id', 'users.user_id', 'users.contact_no', 'users.name')
                ->where('users.sms_alerts', 1)
                ->get();
        $alldataarray = objToArray($userIntrest, true);
        // $aa=getflashSaleProductsBycategoryId(1);
        $arr = array();
        foreach ($alldataarray as $ui) {
            $aa = getflashSaleProductsBycategoryId($ui['category_id']);
            $arr[$ui['user_id']][] = array('category_title' => $aa['categoryTitle'], 'contact_no' => $ui['contact_no'], 'name' => $ui['name']);
        }
        //print_rr($arr);
        foreach ($arr as $key => $ar) {
            $sendsmsOffer[]=$ar[0]['name'];
            $html = '';
            $html .= 'Hey ' . $ar[0]['name'] . ' !Exciting Flash sales on ';
            foreach ($ar as $a) {
                $html .= $a['category_title'] . " ";
            }
            $html .= 'of your interest are now online Click on the http://shopzit.mindblaze.net/search?cashback=75  to access them now';
            Twilio::message("+".$ar[0]['contact_no'], $html);
        }
         $orderLog = new Logger('');
        $orderLog->pushHandler(new StreamHandler(storage_path('logs/smsoffer.log')), Logger::INFO);
        $orderLog->info('Sms offer run', $sendsmsOffer);
    }

}
