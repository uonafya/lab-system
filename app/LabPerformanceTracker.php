<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabPerformanceTracker extends Model
{
    protected $fillable = ['lab_id','month','year','dateemailsent','testtype','sampletype','received','rejected','loggedin','notlogged','tested','reasonforbacklog','datesubmitted','submittedBy'];
}
