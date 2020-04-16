<?php

namespace App;

use DB;

class CovidSampleView extends BaseModel
{
	protected $table = "covid_sample_view";

	public function patient()
    {
        return $this->belongsTo('App\CovidPatient', 'patient_id');
    }
    
    /**
     * Get the sample's result name
     *
     * @return string
     */
    public function getResultNameAttribute()
    {
        if($this->result == 1){ return "Negative"; }
        else if($this->result == 2){ return "Positive"; }
        else if($this->result == 3){ return "Failed"; }
        else if($this->result == 5){ return "Collect New Sample"; }
        else{ return ""; }
    }

    public function getSampletypeAttribute()
    {
        return DB::table('covid_test_types')->where('id', '=', "{$this->test_type}")->first()->name ?? null;
    }
}
