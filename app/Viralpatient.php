<?php

namespace App;

use App\BaseModel;

class Viralpatient extends BaseModel
{
    // protected $dates = ['datesynched', 'dob'];

    public function sample()
    {
    	return $this->hasMany('App\Viralsample', 'patient_id');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function mother()
    {
        return $this->hasMany('App\Mother', 'patient_id');
    }

    public function scopeExisting($query, $facility_id, $ccc_no)
    {
        return $query->where(['facility_id' => $facility_id, 'patient' => $ccc_no]);
    }


    /**
     * Get the patient's gender
     *
     * @return string
     */
    public function getGenderAttribute()
    {
        if($this->sex == 1){ return "Male"; }
        else if($this->sex == 2){ return "Female"; }
        else{ return "No Gender"; }
    }

    /**
     * Get the patient's age in years
     *
     * @return integer
     */
    public function getAgeAttribute()
    {
        return \App\Lookup::calculate_viralage(date('Y-m-d'), $this->dob);
    }

    public function last_test()
    {
        $sql = "SELECT * FROM viralsamples WHERE patient_id={$this->id} AND datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$this->id} AND repeatt=0 )
        "; 

    }
}
