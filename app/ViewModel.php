<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewModel extends Model
{

    public function my_date_format($value)
    {
        if($this->$value) return date('d-M-Y', strtotime($this->$value));

        return '';
    }


    public function scopeSample($query, $facility, $patient, $datecollected)
    {
        return $query->where(['facility_id' => $facility, 'patient' => $patient, 'datecollected' => $datecollected]);
    }

    public function scopePatient($query, $facility, $patient)
    {
        return $query->where(['facility_id' => $facility, 'patient' => $patient]);
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
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
     * Get the sample's received status name
     *
     * @return string
     */
    public function getReceivedAttribute()
    {
        if($this->receivedstatus == 1){ return "Accepted"; }
        else if($this->receivedstatus == 2){ return "Rejected"; }
        else{ return ""; }
    }
}
