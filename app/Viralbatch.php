<?php

namespace App;

use App\BaseModel;

class Viralbatch extends BaseModel
{
    // protected $dates = ['datereceived', 'datedispatchedfromfacility', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'datemodified', 'dateapproved', 'dateapproved2', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted', 'datesynched'];

    protected $withCount = ['sample'];

    public function tat()
    {
        if(!$this->datereceived) return '';

        $max;
        if($this->batch_complete == 1){
            $max = $this->datedispatched;
        }
        else{
            $max = date('Y-m-d');
        }
        return \App\Misc::working_days($this->datereceived, $max);
    }

	public function sample()
    {
        return $this->hasMany('App\Viralsample', 'batch_id');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }


    public function scopeExisting($query, $facility, $datereceived, $lab)
    {
        return $query->where(['facility_id' => $facility, 'datereceived' => $datereceived, 'lab_id' => $lab]);
    }
}
