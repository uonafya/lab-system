<?php

namespace App;

use DB;

class CovidPatient extends BaseModel
{
	protected $dates = ['dob', 'date_symptoms', 'date_admission', 'date_isolation', 'date_death'];

    public function travel()
    {
        return $this->hasMany('App\CovidTravel', 'patient_id');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function view_facility()
    {
        return $this->belongsTo('App\ViewFacility', 'facility_id');
    }

    public function quarantine_site()
    {
        return $this->belongsTo('App\QuarantineSite');
    }

    public function sample()
    {
        return $this->hasMany('App\CovidSample', 'patient_id');
    }

    public function contact()
    {
        return $this->hasMany('App\CovidContact', 'patient_id');
    }

    public function setHealthStatusAttribute($value)
    {
        $this->attributes['current_health_status'] = $value;
    }


    public function setSexAttribute($value)
    {
        if(is_numeric($value)) $this->attributes['sex'] = $value;
        else{
            if(str_contains($value, ['F', 'f'])) $this->attributes['sex'] = 2;
            else if(str_contains($value, ['M', 'm'])){
                $this->attributes['sex'] = 1;
            }
        }
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

    public function getCountyAttribute()
    {
        return County::find($this->county_id)->name ?? '';
    }
}
