<?php

namespace App;

use App\BaseModel;

class DrExtractionWorksheet extends BaseModel
{

    public function sample_view()
    {
        return $this->hasMany('App\DrSampleView', 'extraction_worksheet_id');
    }

    public function sample()
    {
        return $this->hasMany('App\DrSample', 'extraction_worksheet_id');
    }

    public function worksheet()
    {
        return $this->hasMany('App\DrWorksheet', 'extraction_worksheet_id');
    }

    public function creator()
    {
    	return $this->belongsTo('App\User', 'createdby');
    }


    public function getCanCreateSequencingAttribute()
    {
        $sample = \App\DrSample::where('extraction_worksheet_id', $this->id)->whereNull('worksheet_id')->first();
        if($sample) return true;
        return false;
    }

}
