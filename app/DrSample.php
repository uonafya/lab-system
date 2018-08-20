<?php

namespace App;

use App\BaseModel;

class DrSample extends BaseModel
{

    public function patient()
    {
        return $this->belongsTo('App\Viralpatient', 'patient_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\DrWorksheet', 'worksheet_id');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function result()
    {
        return $this->hasMany('App\DrResult', 'sample_id');
    }


    public function setArvToxicitiesAttribute($value)
    {
        $val = '[';
        foreach ($value as $v) {
            $val .= "'" . $v . "',";
        }
        $this->attributes['arv_toxicities'] = $val . ']';
    }

    public function getArvToxicitiesArrayAttribute()
    {
        return eval("return " . $this->arv_toxicities . ";");
    }

    public function setClinicalIndicationsAttribute($value)
    {
        $val = '[';
        foreach ($value as $v) {
            $val .= "'" . $v . "',";
        }
        $this->attributes['clinication_indications'] = $val . ']';
    }

    public function getClinicalIndicationsArrayAttribute()
    {
        return eval("return " . $this->clinication_indications . ";");
    }

    public function setOtherMedicationsAttribute($value)
    {
        $val = '[';
        foreach ($value as $v) {
            $val .= "'" . $v . "',";
        }
        $this->attributes['other_medications'] = $val . ']';
    }

    public function getOtherMedicationsArrayAttribute()
    {
        return eval("return " . $this->other_medications . ";");   
    }

    public function getOtherMedicationsStringAttribute()
    {
        $my_array = $this->other_medications_array;
        $str = '';

        if(is_array($my_array)){
            foreach ($my_array as $value) {
                if(!is_numeric($value)) $str .= trim($value) . ', ';
            }
        }

        return $str;   
    }

}
