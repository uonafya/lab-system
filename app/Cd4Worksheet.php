<?php

namespace App;

use App\BaseModel;

class Cd4Worksheet extends BaseModel
{
	protected $table = 'cd4worksheets';

	public function creator(){
		return $this->belongsTo('App\User', 'createdby');
	}

	public function uploader(){
		return $this->belongsTo('App\User', 'uploadedby');
	}

	public function first_reviewer(){
		return $this->belongsTo('App\User', 'reviewedby');
	}

	public function second_reviewer(){
		return $this->belongsTo('App\User', 'reviewedby2');
	}

	public function cancellor(){
		return $this->belongsTo('App\User', 'cancelledby');
	}

	public function samples(){
		return $this->hasMany('App\Cd4SampleView', 'worksheet_id');
	}
}
