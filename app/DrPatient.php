<?php

namespace App;

use App\BaseModel;

class DrPatient extends BaseModel
{
    //

    public function batch()
    {
        return $this->belongsTo('App\Batch');
    }
}
