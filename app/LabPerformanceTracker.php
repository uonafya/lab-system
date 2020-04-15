<?php

namespace App;

use App\BaseModel;

class LabPerformanceTracker extends BaseModel
{
    // protected $fillable = ['lab_id','month','year','dateemailsent','testtype','sampletype','received','rejected','loggedin','notlogged','tested','reasonforbacklog','datesubmitted','submittedBy'];

    


    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function submitter()
    {
        return $this->belongsTo('App\User', 'submittedBy');
    }

    public function scopeExisting($query, $year, $month, $testtype, $sampletype)
    {
        return $query->where(['year' => $year, 'month' => $month, 'testtype' => $testtype, 'sampletype' => $sampletype]);
    }
}
