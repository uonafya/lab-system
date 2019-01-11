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



    public function warning()
    {
        return $this->hasMany('App\DrWarning', 'sample_id');
    }

    public function dr_call()
    {
        return $this->hasMany('App\DrCall', 'sample_id');
    }

    public function genotype()
    {
        return $this->hasMany('App\DrGenotype', 'sample_id');
    }


    // mid being my id
    // Used when sending samples to sanger
    public function getMidAttribute()
    {
        return env('DR_PREFIX') . $this->id;
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
        $this->attributes['clinical_indications'] = $val . ']';
    }

    public function getClinicalIndicationsArrayAttribute()
    {
        return eval("return " . $this->clinical_indications . ";");
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

    public function get_primers($date_created=null, $row, $column = 1)
    {
        $primers = ['F1', 'F2', 'F3', 'R1', 'R2', 'R3'];
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        if(!$date_created) return null;

        $str = '';

        foreach ($primers as $key => $value) {
            $loc = $key+1;
            if($column == 2) $loc += 6;

            $str .= '<td>';

            
        }
    }







}
