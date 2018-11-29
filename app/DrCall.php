<?php

namespace App;

use App\BaseModel;

class DrCall extends BaseModel
{

    public function sample()
    {
        return $this->belongsTo('App\DrResult', 'sample_id');
    }

    public function call_drug()
    {
        return $this->hasMany('App\DrCallDrug', 'call_id');
    }

	

    public function setOtherMutationsAttribute($value)
    {
        if($value){
            $val = '[';
            foreach ($value as $v) {
                $val .= "'" . $v . "',";
            }
            $this->attributes['other_mutations'] = $val . ']';            
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
    }

    public function getMajorMutationsArrayAttribute()
    {
        return eval("return " . $this->major_mutations . ";");
    }
}
