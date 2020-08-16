<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DrClinicalVisit extends BaseModel
{


    public function clinical_form()
    {
        return $this->belongsTo('App\DrClinicalForm');
    }
}
