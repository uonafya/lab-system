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

    public function my_time_format($value)
    {
        if($this->$value) return date('d-M-Y H:i:s', strtotime($this->$value));

        return '';
    }


    public function scopeSample($query, $facility, $patient, $datecollected)
    {
        $min_date = date('Y-m-d', strtotime($datecollected . ' -3 days'));
        $max_date = date('Y-m-d', strtotime($datecollected . ' +3 days'));
        return $query->where(['facility_id' => $facility, 'patient' => $patient])
                        ->whereBetween('datecollected', [$min_date, $max_date]);
    }

    public function scopeExisting($query, $data_array)
    {
        $min_date = date('Y-m-d', strtotime($data_array['datecollected'] . ' -3 days'));
        $max_date = date('Y-m-d', strtotime($data_array['datecollected'] . ' +3 days'));
        return $query->where(['facility_id' => $data_array['facility_id'], 'patient' => $data_array['patient']])
                    ->whereBetween('datecollected', [$min_date, $max_date]);
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

    public function getIsReadyAttribute()
    {
        if($this->repeatt == 0){
            if(in_array(env('APP_LAB'), \App\Lookup::$double_approval)){
                if(($this->dateapproved && $this->dateapproved2) || ($this->approvedby && $this->approvedby2)){
                    return true;
                }
            }
            else{
                if($this->dateapproved || $this->approvedby) return true;
            }
        }
        return false;
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
