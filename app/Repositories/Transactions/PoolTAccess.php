<?php

namespace App\Repositories\Transactions;

use App\BaseAccess;
use App;
use DB;
use App\Models\PoolTransactions;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class PoolTAccess extends BaseAccess {

    private $_walletTransactions;

    public function __construct() {
        $this->_poolTransactions = new PoolTransactions();
    }

    public function creditPool($amount, $type = 'credit', $pool_id, $trans_id = 0, $order_id = 0) {
        $poolTotal = $this->getPoolAmount($pool_id);
        $current = $poolTotal + $amount;
        $this->_poolTransactions->ptrans_amount = $amount;
        $this->_poolTransactions->ptrans_type = $type;
        $this->_poolTransactions->order_id = $order_id;
        $this->_poolTransactions->trans_id = $trans_id;
        $this->_poolTransactions->pool_id = $pool_id;
        $this->_poolTransactions->current_amount = $current;
        $this->_poolTransactions->save();
        DB::table('pools')->where('pool_id', $pool_id)->update(array('amount' => $current));
        return $insertedId = $this->_poolTransactions->pool_id;
    }

    public function debitPool($amount, $type = 'debit', $pool_id, $trans_id = 0, $order_id = 0) {
        $poolTotal = $this->getPoolAmount($pool_id);
        $current = $poolTotal - $amount;
        $this->_poolTransactions->ptrans_amount = $amount;
        $this->_poolTransactions->ptrans_type = $type;
        $this->_poolTransactions->order_id = $order_id;
        $this->_poolTransactions->trans_id = $trans_id;
        $this->_poolTransactions->pool_id = $pool_id;
        $this->_poolTransactions->current_amount = $current;
        $this->_poolTransactions->save();
        DB::table('pools')->where('pool_id', $pool_id)->update(array('amount' => $current));
        return $insertedId = $this->_poolTransactions->pool_id;
    }

    public function getTransactionHistory($limit, $userId) {
        return $result = $this->_poolTransactions->where('wuser_id', $userId)->orderby('wtrans_id', 'DESC')->paginate($limit);
    }

    public function getPoolAmount($poolId) {
        $poolTotal = DB::table('pools')->where('pool_id', $poolId)->select('amount')->first();
        if (!empty($poolTotal)) {
            return $walletTotal = $poolTotal->amount;
        } else {
            return 0;
        }
    }

}
