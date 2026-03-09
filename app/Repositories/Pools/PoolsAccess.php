<?php

namespace App\Repositories\Pools;

use App\BaseAccess;
use App;

use App\Models\Pools;

/**
 * Description of Useraccess
 *
 * @author Asif
 */
class PoolsAccess extends BaseAccess {

    private $_pools;

    public function __construct() {
        $this->_pools = new Pools();
    }

    public function getPoolStats (){
       return $results= $this->_pools->get()->toArray();
    }
}
