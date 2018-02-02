<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    protected $guarded = [];
    protected $dates = ['datereceived', 'datecollected', 'datetested', 'datedispatchedfromfacility', 'datemodified', 'dateapproved', 'dateapproved2', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted', 'datesynched', 'dateinitiatedontreatment'];

    public function setDatedispatchedfromfacilityAttribute($value)
    {
        $this->attributes['datedispatchedfromfacility'] = $value ? $value : null;
        // $this->attributes['date_of_expiry'] = $value;
    }

    public function patient()
    {
    	return $this->belongsTo('App\Patient');
    }

    public function batch()
    {
        return $this->belongsTo('App\Batch');
    }


    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\Sample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\Sample', 'parentid');
    }

}
