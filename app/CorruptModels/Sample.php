<?php

namespace App\CorruptModels;

use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    public function patient()
    {
    	return $this->belongsTo('App\CorruptModels\Patient');
    }

    public function batch()
    {
        return $this->belongsTo('App\CorruptModels\Batch');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\CorruptModels\Worksheet');
    }

    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\CorruptModels\Sample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\CorruptModels\Sample', 'parentid');
    }
}
