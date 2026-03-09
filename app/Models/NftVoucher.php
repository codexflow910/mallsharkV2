<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NftVoucher extends Model
{
    use HasFactory;
    protected $table = 'nft_vouchers';
    protected $guarded =[];
//     protected $casts = [
//         "price" => "array"
//    ];
   
public function getPriceAttribute($value)
{
    return json_decode($value);
}

    
}
