<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CancerSample extends BaseModel
{
    public function patient()
    {
    	return $this->belongsTo(CancerPatient::class, 'patient_id', 'id');
    }

    public function facility()
    {
    	return $this->belongsTo('App\Facility');
    }

    public function facility_lab()
    {
        return $this->belongsTo(Facility::class, 'lab_id', 'id');
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
}
