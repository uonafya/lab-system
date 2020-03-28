<?php

namespace App;

use App\BaseModel;
use DB;

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

    public function setResultAttribute($value)
    {
        if(is_numeric($value)) $this->attributes['result'] = $value;
        else{
            $value = strtolower($value);
            if(str_contains($value, ['neg'])) $this->attributes['result'] = 1;
            else{
                $this->attributes['result'] = 2;
            }
        }
    }

    public function setIsolationStatusAttribute($value)
    {
        if(is_numeric($value)) $this->attributes['isolation_status'] = $value;
        else{
            $a = explode(' ', $value);
            if(count($a) == 1) $a = explode('-', $value);
            $word = $a[0];
            $this->attributes['isolation_status'] = DB::table('covid_isolations')->where('name', 'like', "{$value}%")->first()->id ?? null;
        }
    }

    public function setSampleTypeAttribute($value)
    {
        if(is_numeric($value)) $this->attributes['sample_type'] = $value;
        else{
            $a = explode(' ', $value);
            if(count($a) == 1) $a = explode('-', $value);
            $word = $a[0];
            $this->attributes['sample_type'] = DB::table('covid_sample_types')->where('name', 'like', "{$value}%")->first()->id ?? null;
        }
    }
}
