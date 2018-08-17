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

    public function setClinicationIdAttribute($value)
    {
        $val = '[';
        foreach ($value as $v) {
            $val .= "'" . $v . "',";
        }
        $this->attributes['clinication_id'] = $val . ']';
    }

    public function setOtherMedicationsAttribute($value)
    {
        $val = '[';
        foreach ($value as $v) {
            $val .= "'" . $v . "',";
        }
        $this->attributes['other_medications'] = $val . ']';
    }
}
