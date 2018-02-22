<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Viralsample extends Model
{
    protected $guarded = [];
    protected $dates = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'dateinitiatedontreatment', 'datesynched'];

    protected $dateFormat = 'Y-m-d';


    public function patient()
    {
    	return $this->belongsTo('App\Viralpatient', 'patient_id');
    }

    public function batch()
    {
        return $this->belongsTo('App\Viralbatch', 'batch_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\Viralworksheet', 'worksheet_id');
    }


    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\Viralsample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\Viralsample', 'parentid');
    }
}
