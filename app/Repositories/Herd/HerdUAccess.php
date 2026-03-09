<?php

namespace App\Repositories\Herd;

use App\BaseAccess;
use App;
use DB;
use App\Models\HerdUsers;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class HerdUAccess extends BaseAccess {

    private $_herdUsers;

    public function __construct() {
        $this->_herdUsers = new HerdUsers();
    }

    public function addHerdUsers($userHerdId, $sellerHerdId) {
        $this->_herdUsers->user_herd_id = $userHerdId;
        $this->_herdUsers->seller_herd_id = $sellerHerdId;
        $this->_herdUsers->save();
    }

}
