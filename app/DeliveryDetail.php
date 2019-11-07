<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryDetail extends BaseModel
{
	public function delivery()
	{
		return $this->belongsTo('App\Delivery', 'delivery_id');
	}

    public function kit()
    {
        return $this->morphTo();
    }
}
