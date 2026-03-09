<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use File;
use DB;
use App\Repositories\Transactions\WalletAccess;

class WalletController extends Controller {

    private $_walletaccess;

    public function __construct(Request $request) {
        $this->_walletaccess = new WalletAccess();
    }

    public function creditWalletWithPurchaseZit(Request $request) {
        $zit_amount = $request->input('amount');
        if ($zit_amount != '') {
            $currentUserId=getCurrentUserId();
            
           $wtransaction_info='USDC purchased';
            $this->_walletaccess->creditWalletWithZit($zit_amount,$currentUserId,'credit',$wtransaction_info,0);
            
            
           
            
            return 'yes';
        }
    }

}
