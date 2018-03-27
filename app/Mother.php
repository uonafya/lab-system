<?php

namespace App;

use App\BaseModel;

class Mother extends BaseModel
{
    // protected $dates = ['datesynched'];


    public function patient()
    {
    	return $this->hasMany('App\Patient');
    }
}
