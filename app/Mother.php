<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mother extends Model
{
    protected $guarded = [];
    protected $dates = ['datesynched'];


    public function patient()
    {
    	return $this->hasMany('App\Patient');
    }
}
