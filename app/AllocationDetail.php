<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationDetail extends BaseModel
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function kit(){
    	return $this->belongsTo('App\Kits');
    }
}
