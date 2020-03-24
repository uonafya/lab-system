<?php

namespace App;

use App\BaseModel;

class CovidSample extends BaseModel
{
	protected $dates = ['dob', 'datecollected', 'datereceived', 'datetested', 'datedispatched',];

    protected $casts = [
        'symptoms' => 'array',
    ];


    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\CovidWorksheet', 'worksheet_id');
    }

    public function travel()
    {
        return $this->hasMany('App\CovidTravel', 'sample_id');
    }

    public function calc_age()
    {
    	$this->age = $this->datecollected->diffInYears($this->dob);
    }

    public function setSexAttribute($value)
    {
        if(is_numeric($value)) $this->attributes['sex'] = $value;
        else{
            if(str_contains($value, ['F', 'f'])) $this->attributes['sex'] = 2;
            else{
                $this->attributes['sex'] = 1;
            }
        }
    }
}
