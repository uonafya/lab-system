<?php

namespace App;

use App\BaseModel;

class Patient extends BaseModel
{
    // protected $dates = ['datesynched', 'dob'];

    public function getDateBirthAttribute()
    {
        return $this->date_modifier($this->dob);
    }

    public function sample()
    {
    	return $this->hasMany('App\Sample');
    }

    public function mother()
    {
    	return $this->belongsTo('App\Mother');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }
}
