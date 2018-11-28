<?php

namespace App;

use App\BaseModel;

class DrCallDrug extends BaseModel
{

    public function dr_call()
    {
        return $this->belongsTo('App\DrCall', 'call_id');
    }
}
