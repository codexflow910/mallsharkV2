<?php

namespace Packages\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class Userrole extends Model {

    protected $primaryKey = 'role_user_id';
    protected $table = 'role_user';
    public $timestamps = false;

}
