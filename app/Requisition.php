<?php

namespace App;

use App\BaseModel;

class Requisition extends BaseModel
{
    // protected $fillable = ['facility','lab','request','supply','comments','createdby','created_at','approvedby','approvecomments','disapprovecomments','status','flag','parentid','requisitiondate','datesubmitted','submittedby','dateapproved','datesynchronized'];


    public function lab()
    {
        return $this->belongsTo('App\Lab', 'lab');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility', 'facility', 'facilitycode');
    }

    public function submitter()
    {
        return $this->belongsTo('App\User', 'submittedby');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'createdby');
    }

    public function approver()
    {
        return $this->belongsTo('App\User', 'approvedby');
    }

    
}
