<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DrClinicalForm extends BaseModel
{

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function visit()
    {
        return $this->hasMany('App\DrClinicalVisit');
    }
}
