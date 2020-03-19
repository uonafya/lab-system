<?php

namespace App;

class CovidTravel extends BaseModel
{


    public function sample()
    {
        return $this->belongsTo('App\CovidSample', 'sample_id');
    }
}
