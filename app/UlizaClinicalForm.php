<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UlizaClinicalForm extends BaseModel
{

    public function reviewer()
    {
        return $this->belongsTo('App\User', 'reviewer_id');        
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function additional_info()
    {
        return $this->hasMany('App\UlizaAdditionalInfo');
    }

    public function view_facility()
    {
        return $this->belongsTo('App\ViewFacility', 'facility_id');
    }

    public function twg()
    {
        return $this->belongsTo('App\UlizaTwg', 'twg_id');        
    }

    public function visit()
    {
        return $this->hasMany('App\UlizaClinicalVisit', 'uliza_clinical_form_id');
    }

    public function feedback()
    {
        return $this->hasOne('App\UlizaTwgFeedback', 'uliza_clinical_form_id');
    }

    public function getNatNumberAttribute()
    {
        return "NAT-{$this->id}";
    }

    public function getSubjectIdentifierAttribute()
    {
        return 'CCC#: ' . $form->cccno . ' Nat#: ' . $form->nat_number;
    }

}
