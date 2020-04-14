<?php

namespace App;

class CovidSampleView extends BaseModel
{
	protected $table = "covid_sample_view";
	
	public function patient()
    {
        return $this->belongsTo('App\CovidPatient', 'patient_id');
    }
}
