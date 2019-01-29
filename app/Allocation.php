<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];


    public function details() {
    	return $this->hasMany('App\AllocationDetail');
    }
}
