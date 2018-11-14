<?php

namespace App;

use App\BaseModel;

class Cd4Worksheet extends BaseModel
{
	protected $table = 'cd4worksheets';

	public function creator(){
		return $this->belongsTo('App\User', 'createdby');
	}

	public function samples(){
		return $this->hasMany('App\Cd4SampleView', 'worksheet_id');
	}
}
