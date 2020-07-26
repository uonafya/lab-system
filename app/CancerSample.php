<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CancerSample extends BaseModel
{
    public function patient()
    {
    	return $this->belongsTo(CancerPatient::class, 'patient_id', 'id');
    }

    public function facility()
    {
    	return $this->belongsTo('App\Facility');
    }
}
