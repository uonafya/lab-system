<?php

namespace App;

use App\BaseModel;

class Cd4Sample extends BaseModel
{
	protected $table = 'cd4samples';

	

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function patient(){
    	return $this->belongsTo('App\Cd4Patient', 'patient_id');
    }
}
