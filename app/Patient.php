<?php

namespace App;

use App\BaseModel;

class Patient extends BaseModel
{
    // protected $dates = ['datesynched', 'dob'];

    public function sample()
    {
    	return $this->hasMany('App\Sample');
    }

    public function mother()
    {
    	return $this->belongsTo('App\Mother');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function scopeExisting($query, $facility_id, $hei_number)
    {
        return $query->where(['facility_id' => $facility_id, 'patient' => $hei_number]);
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
}
