<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidTravel extends BaseModel
{
	protected $dates = ['travel_date'];


    public function patient()
    {
        return $this->belongsTo('App\CovidPatient', 'patient_id');
    }


    public function town()
    {
        return $this->belongsTo('App\City', 'city_id');
    }
}
