<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DrClinicalForm extends BaseModel
{

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function view_facility()
    {
        return $this->belongsTo('App\ViewFacility', 'facility_id');
    }

    public function twg()
    {
        return $this->belongsTo('App\DrTwg', 'twg_id');        
    }

    public function visit()
    {
        return $this->hasMany('App\DrClinicalVisit', 'dr_clinical_form_id');
    }
}
