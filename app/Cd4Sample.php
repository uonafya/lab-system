<?php

namespace App;

use App\BaseModel;

class Cd4Sample extends BaseModel
{
	protected $table = 'cd4samples';

	public function first_approver(){
		return $this->belongsTo('App\User', 'approvedby');
	}

	public function second_approver(){
		return $this->belongsTo('App\User', 'approvedby2');
	}

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function patient(){
    	return $this->belongsTo('App\Cd4Patient', 'patient_id');
    }
}
