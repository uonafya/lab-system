<?php

namespace App;

use App\BaseModel;

class DrCall extends BaseModel
{
    protected $casts = [
        'mutations' => 'array',
    ];


    public function sample()
    {
        return $this->belongsTo('App\DrSample', 'sample_id');
    }

    public function call_drug()
    {
        return $this->hasMany('App\DrCallDrug', 'call_id');
    }

    

    /*public function setMutationsAttribute($value)
    {
        if($value){
            $val = '[';
            foreach ($value as $v) {
                $val .= "'" . $v . "',";
            }
            $this->attributes['mutations'] = $val . ']';            
        }
        else{
            // $this->attributes['mutations'] = null;
            $this->attributes['mutations'] = "[]";
        }
    }

    public function getMutationsArrayAttribute()
    {
        return eval("return " . $this->mutations . ";");
    }*/



	

    /*public function setOtherMutationsAttribute($value)
    {
        if($value){
            $val = '[';
            foreach ($value as $v) {
                $val .= "'" . $v . "',";
            }
            $this->attributes['other_mutations'] = $val . ']';            
        }
        else{
            $this->attributes['other_mutations'] = null;
        }
    }

    public function getOtherMutationsArrayAttribute()
    {
        return eval("return " . $this->other_mutations . ";");
    }


    public function setMajorMutationsAttribute($value)
    {
        if($value){
            $val = '[';
            foreach ($value as $v) {
                $val .= "'" . $v . "',";
            }
            $this->attributes['major_mutations'] = $val . ']';
        }
        else{
            $this->attributes['major_mutations'] = null;
        }
    }

    public function getMajorMutationsArrayAttribute()
    {
        return eval("return " . $this->major_mutations . ";");
    }*/
}
