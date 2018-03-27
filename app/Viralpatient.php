<?php

namespace App;

use App\BaseModel;

class Viralpatient extends BaseModel
{
    // protected $dates = ['datesynched', 'dob'];

    public function getDateBirthAttribute()
    {
        return $this->date_modifier($this->dob);
    }

    public function sample()
    {
    	return $this->hasMany('App\Viralsample', 'patient_id');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }
}
