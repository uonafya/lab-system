<?php

namespace App;

use App\BaseModel;

class DrWorksheet extends BaseModel
{
    

    public function sample()
    {
        return $this->hasMany('App\DrSample', 'worksheet_id');
    }

    public function sample_view()
    {
        return $this->hasMany('App\DrSampleView', 'worksheet_id');
    }

    public function warning()
    {
        return $this->hasMany('App\DrWorksheetWarning', 'worksheet_id');
    }

    public function extraction_worksheet()
    {
        return $this->belongsTo('App\DrExtractionWorksheet', 'extraction_worksheet_id');
    }

    public function creator()
    {
    	return $this->belongsTo('App\User', 'createdby');
    }

    public function runner()
    {
        return $this->belongsTo('App\User', 'runby');
    }

    public function uploader()
    {
        return $this->belongsTo('App\User', 'uploadedby');
    }

    public function canceller()
    {
        return $this->belongsTo('App\User', 'cancelledby');
    }

    public function reviewer()
    {
        return $this->belongsTo('App\User', 'reviewedby');
    }

}
