<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreIcon extends Model {

    protected $primaryKey = 'icon_id';
    public $id;
	protected $table = 'store_icons';
    public $timestamps = false;
	
	public function getImageIdByIconId(){
		$data = StoreIcon::find($this->id);
		if($data!=''){
			return $data->image_id;
		}
	}

}
