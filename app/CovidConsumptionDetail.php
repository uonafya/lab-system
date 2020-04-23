<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidConsumptionDetail extends BaseModel
{
    public function kit()
    {
    	return $this->belongsTo(CovidKit::class, 'kit_id', 'id');
    }
}
