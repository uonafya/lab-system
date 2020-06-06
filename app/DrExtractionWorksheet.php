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


    /**
     * Get if worksheet has samples that can enter a sequencing worksheet
     *
     * @return string
     */
    public function getSequencingAttribute()
    {
        $sample = \App\DrSample::whereNull('worksheet_id')->where(['passed_gel_documentation' => true, 'extraction_worksheet_id' => $this->id, 'control' => 0])->first();
        if($sample) return true;
        return false;
    }

    /**
     * Get if worksheet has samples that can enter a sequencing worksheet
     *
     * @return string
     */
    public function getPendingWorksheetAttribute()
    {
        $work = \App\DrWorksheet::whereNotIn('status_id', [3, 4])->first();
        if($work) return true;
        return false;
    }

    public function runFailed()
    {
        $samples = $this->sample()->where(['passed_gel_documentation' => false])->get();
        foreach ($samples as $key => $sample) {
            $sample->create_vl_sample();
        }
    }
}
