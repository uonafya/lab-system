<?php

namespace App;

use App\BaseModel;

class Mother extends BaseModel
{
    // protected $dates = ['datesynched'];


    public function patient()
    {
    	return $this->hasMany('App\Patient');
    }


    public function scopeExisting($query, $facility, $ccc)
    {
        return $query->where(['facility_id' => $facility, 'ccc_no' => $ccc]);
    }

    public function calc_age()
    {
        $today = date("Y-m-d");
        $this->age = \App\Lookup::calculate_viralage($today, $this->dob);
    }

    public function last_vl()
    {
    	// $result = 
    }
}
