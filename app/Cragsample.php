<?php

namespace App;

use App\BaseModel;

class Cragsample extends BaseModel
{


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
    	return $this->belongsTo('App\CragPatient', 'patient_id');
    }
}
