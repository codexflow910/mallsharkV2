<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Twilio;
use App\Http\Controllers\MandrillT;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SendOffers extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offer:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sales offers command run';

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
        $offerArray = array();
        $userIntrest = DB::table('user_intrests')
                ->join('product_category', 'user_intrests.category_id', '=', 'product_category.category_id')
                ->join('products', 'products.product_id', '=', 'product_category.product_id')
                ->join('users', 'users.user_id', '=', 'user_intrests.user_id')
                // ->select('user_intrests.category_id','users.email','users.contact_no','user_intrests.user_id', 'product_category.product_id', 'products.title')
                ->select('user_intrests.user_id', 'products.title', 'products.slug', 'products.product_id', 'users.email', 'users.contact_no', 'products.feature_image', 'users.name')
                ->where('users.email_alerts', 1)
                ->where('products.status', '1')
                ->where('products.flashsale_status', 'all')
                ->groupBy('products.product_id')
                ->limit(6)
                ->get();
        $alldataarray = objToArray($userIntrest, true);
        $newarray = array();
        foreach ($alldataarray as $key => $ui) {
            $newarray[$ui['user_id']][] = array('title' => $ui['title'], 'email' => $ui['email'], 'slug' => $ui['slug'], 'feature_image' => getAttachmentUrlforEmail((int) $ui['feature_image'], 'small'), 'username' => $ui['name']);
        }
        foreach ($newarray as $key2 => $na) {
            $text_producta = '';
            foreach ($na as $n) {
                $text_producta .= '<div style="width:30%;float:left;margin-left:15px;margin-bottom:20px;"><img src="' . $n["feature_image"] . '"><br /><br /><a style="margin-top:10px;" href="https://shopzit.mindblaze.net/product/' . $n['slug'] . '">' . $n['title'] . '</a></div>';
                $offerArray[] = $na[0]['email'];
            }
            //echo $text_producta;
            $user_email = $na[0]['email'];
            $user_name = $na[0]['username'];
            $subject = 'Flash sale Products';
            $templateSlug = 'flash-sale-products';
            $toEmail = $user_email;
            $varsArray = array(
                array(
                    'name' => 'USERNAME',
                    'content' => $user_name),
                array(
                    'name' => 'PRODUCTDETAIL',
                    'content' => $text_producta),
            );
            $mandrillObj = new MandrillT();
            $mandrillObj->sendMandrillEmail($subject, $templateSlug, $toEmail, $user_name, $varsArray);
//
            //DB::table('user_intrests')->where('user_id',$ui['user_id'])->update(['status'=>1]);
        }
        // print_rr($newarray);
        $orderLog = new Logger('');
        $orderLog->pushHandler(new StreamHandler(storage_path('logs/emailsendoffer.log')), Logger::INFO);
        $orderLog->info('Email offer run', $offerArray);
    }

}
