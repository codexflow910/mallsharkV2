<?php

namespace App\Repositories\Herd;

use App\BaseAccess;
use App;
use DB;
use App\Models\HerdAipHistory;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class HerdHistoryAccess extends BaseAccess {

    private $_herdHistory;

    public function __construct() {
        $this->_herdHistory = new HerdAipHistory();
    }

    public function addHerdHistory($herdId, $aipAmount, $aipDebt, $aipCurrentPrice, $aipDesc) {
        $this->_herdHistory->herd_id = $herdId;
        $this->_herdHistory->aip_amount = $aipAmount;
		$this->_herdHistory->aip_debt = $aipDebt;
		$this->_herdHistory->aip_price = $aipCurrentPrice;
		$this->_herdHistory->aip_desc = $aipDesc;
        $this->_herdHistory->save();
    }

}
