<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidTravel extends BaseModel
{
	protected $dates = ['travel_date'];



    public function sample()
    {
        return $this->belongsTo('App\CovidSample', 'sample_id');
    }


    public function city()
    {
        return $this->belongsTo('App\City', 'city_id');
    }
}
