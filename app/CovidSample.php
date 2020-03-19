<?php

namespace App;

class CovidSample extends BaseModel
{
	protected $dates = ['dob', 'datecollected', 'datereceived', 'datetested', 'datedispatched',];


    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\CovidWorksheet', 'worksheet_id');
    }

    public function travel()
    {
        return $this->hasMany('App\CovidTravel', 'sample_id');
    }

    public function calc_age()
    {
    	$this->age = $this->datecollected->diffInYears($this->dob);
    }
}
