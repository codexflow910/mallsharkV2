<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlideSet extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'type', 'start_date'];
    public $timestamps = true;

    public function slides()
    {
        return $this->hasMany(Slide::class);
    }
}
