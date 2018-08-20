<?php

namespace App;

use App\BaseModel;

class DrResult extends BaseModel
{
    public function sample()
    {
        return $this->belongsTo('App\DrSample', 'sample_id');
    }
}
