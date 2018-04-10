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

    public function scopeExisting($query, $facility_id, $ccc_no)
    {
        return $query->where(['facility_id' => $facility_id, 'patient' => $ccc_no]);
    }
}
