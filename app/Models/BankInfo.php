<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankInfo extends Model {

    protected $primaryKey = 'id';
    protected $table = 'bank_info';

    public function user()
    {
        return $this->hasOne(User::class);
    }

}
