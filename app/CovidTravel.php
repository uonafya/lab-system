<?php

namespace App;

use App\BaseModel;

class CovidTravel extends BaseModel
{


    public function sample()
    {
        return $this->belongsTo('App\CovidSample', 'sample_id');
    }

    public function city()
    {
        return $this->belongsTo('App\City', 'city_id');
    }
}
