<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Allocation extends BaseModel
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function machine(){
        return $this->belongsTo('App\Machine');
    }

    public function details() {
    	return $this->hasMany('App\AllocationDetail');
    }
}
