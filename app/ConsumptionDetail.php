<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsumptionDetail extends BaseModel
{
	public function kit() {
    	return $this->belongsTo('App\Kits');
    }

    public function header()
    {
    	return $this->belongsTo(Consumption::class, 'consumption_id', 'id');
    }
}
