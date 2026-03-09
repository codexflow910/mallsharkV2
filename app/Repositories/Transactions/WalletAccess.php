<?php

namespace App\Repositories\Transactions;

use App\BaseAccess;
use App;
use DB;
use App\Models\WalletTransactions;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class WalletAccess extends BaseAccess {

    private $_walletTransactions;

    public function __construct() {
        $this->_walletTransactions = new WalletTransactions();
    }

    public function creditWalletWithZit($amount, $userId, $type, $wtransaction_info, $trans_id = 0) {
        if ($amount > 0) {
            $walletTotal = DB::table('users')->where('user_id', $userId)->select('user_wallet')->first();
            $current = $walletTotal->user_wallet + $amount;
            $this->_walletTransactions->wuser_id = $userId;
            $this->_walletTransactions->wtrans_amount = $amount;
            $this->_walletTransactions->wtransac_type = $type;
            $this->_walletTransactions->trans_id = $trans_id;

            $this->_walletTransactions->wcurrent_amount = $current;
            $this->_walletTransactions->wtransaction_info = $wtransaction_info;
            $this->_walletTransactions->save();
            DB::table('users')->where('user_id', $userId)->update(array('user_wallet' => $current));
            return $insertedId = $this->_walletTransactions->wtrans_id;
        } else {
            return false;
        }
    }

    public function debitWalletWithZit($amount, $userId, $type, $wtransaction_info, $trans_id = 0) {
        if ($amount > 0) {
            $walletTotal = DB::table('users')->where('user_id', $userId)->select('user_wallet')->first();
            $current = $walletTotal->user_wallet - $amount;
            $this->_walletTransactions->wuser_id = $userId;
            $this->_walletTransactions->wtrans_amount = $amount;
            $this->_walletTransactions->wtransac_type = $type;
            $this->_walletTransactions->trans_id = $trans_id;
            $this->_walletTransactions->wcurrent_amount = $current;
            $this->_walletTransactions->wtransaction_info = $wtransaction_info;
            $this->_walletTransactions->save();
            DB::table('users')->where('user_id', $userId)->update(array('user_wallet' => $current));
            return $insertedId = $this->_walletTransactions->wtrans_id;
        } else {
            return false;
        }
    }

    public function getTransactionHistory($limit, $userId, $filerData = "") {

        if (isset($filerData['transaction_type']) && $filerData['transaction_type'] != "") {

            return $result = $this->_walletTransactions->where('wtransaction_info', 'like', '%' . $filerData['transaction_type'] . '%')
                            ->where('wuser_id', $userId)->orderby('wtrans_id', 'DESC')->paginate($limit);
        }
        return $result = $this->_walletTransactions->where('wuser_id', $userId)->orderby('wtrans_id', 'DESC')->paginate($limit);
    }

    public function getWalletAmount($userId) {
        $walletTotal = DB::table('users')->where('user_id', $userId)->select('user_wallet')->first();
        return $walletTotal = $walletTotal->user_wallet;
    }

}
