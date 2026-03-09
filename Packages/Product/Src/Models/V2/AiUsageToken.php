<?php

namespace Packages\Product\Models\V2;

use Illuminate\Database\Eloquent\Model;

class AiUsageToken extends Model
{
    protected $table = 'ai_usage_tokens';

    protected $fillable = [
        'store_id',
        'field',
        'tokens_used',
    ];

    public $timestamps = true;
}
