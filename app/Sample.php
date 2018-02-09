<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    protected $guarded = [];
    protected $dates = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'dateinitiatedontreatment', 'datesynched'];


    // protected $dateFormat = 'Y-m-d';

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

    public function worksheet()
    {
        return $this->belongsTo('App\Worksheet');
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
