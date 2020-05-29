<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consumption extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

   public function scopeExisting($query, $year, $month, $type, $lab_id)
    {
        return $query->where(['year' => $year, 'month' => $month, 'type' => $type, 'lab_id' => $lab_id]);
    }

    public function details()
    {
    	return $this->hasMany(ConsumptionDetail::class, 'consumption_id', 'id');
    }
}
