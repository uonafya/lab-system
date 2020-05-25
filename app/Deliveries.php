<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deliveries extends BaseModel
{
   
   public function details()
   {
   		return $this->hasMany('App\DeliveryDetail', 'delivery_id');
   }

   public function scopeExisting($query, $year, $quarter, $type, $lab_id)
   {
        return $query->where(['year' => $year, 'quarter' => $quarter, 'type' => $type, 'lab_id' => $lab_id]);
   }

   	public function getLastMissingDelivery()
   	{
   		
   	}

}
