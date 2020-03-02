<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deliveries extends BaseModel
{
   
   public function details()
   {
   		return $this->hasMany('App\DeliveryDetail', 'delivery_id');
   }
}
