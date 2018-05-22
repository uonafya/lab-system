<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $fillable = ['facility','lab','request','supply','comments','createdby','created_at','approvedby','approvecomments','disapprovecomments','status','flag','parentid','requisitiondate','datesubmitted','submittedby','dateapproved','datesynchronized'];
}
