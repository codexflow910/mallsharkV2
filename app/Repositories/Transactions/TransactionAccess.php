<?php

namespace App\Repositories\Transactions;

use App\BaseAccess;
use App;
use DB;
use App\Models\Transactions;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class TransactionAccess extends BaseAccess {

    private $_transactions;

    public function __construct() {
        $this->_transactions = new Transactions();
    }
	
	public function addTransaction($trans_type, $trans_amount, $trans_data_id, $trans_user_id, $trans_icon_id){
		$this->_transactions->trans_type = $trans_type;
		$this->_transactions->trans_amount = $trans_amount;
		$this->_transactions->trans_data_id = $trans_data_id;
		$this->_transactions->trans_user_id = $trans_user_id;
		$this->_transactions->trans_icon_id = $trans_icon_id;
		$this->_transactions->save();
		return $insertedId = $this->_transactions->trans_id;
	}

}
