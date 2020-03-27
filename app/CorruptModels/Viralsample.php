<?php

namespace App\CorruptModels;

use Illuminate\Database\Eloquent\Model;

class Viralsample extends Model
{
    public function patient()
    {
    	return $this->belongsTo('App\CorruptModels\Viralpatient', 'patient_id');
    }

    public function batch()
    {
        return $this->belongsTo('App\CorruptModels\Viralbatch', 'batch_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\CorruptModels\Viralworksheet', 'worksheet_id');
    }


    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\CorruptModels\Viralsample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\CorruptModels\Viralsample', 'parentid');
    }
}
