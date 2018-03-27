<?php

namespace App;

use App\BaseModel;

class Viralbatch extends BaseModel
{
    // protected $dates = ['datereceived', 'datedispatchedfromfacility', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'datemodified', 'dateapproved', 'dateapproved2', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted', 'datesynched'];

    public function getDayReceivedAttribute()
    {
        return $this->date_modifier($this->datereceived);
    }
    
    public function getDayDispatchedAttribute()
    {
        return $this->date_modifier($this->datedispatched);
    }
    
    public function getDayModifiedAttribute()
    {
        return $this->date_modifier($this->datemodified);
    }
    
    public function getDayApprovedAttribute()
    {
        return $this->date_modifier($this->dateapproved);
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
}
