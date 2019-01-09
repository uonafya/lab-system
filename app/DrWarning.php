<?php

namespace App;

use App\BaseModel;

class DrWarning extends BaseModel
{

    public function sample()
    {
        return $this->belongsTo('App\DrResult', 'sample_id');
    }
}
