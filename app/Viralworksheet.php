<?php

namespace App;

use App\BaseModel;

class Viralworksheet extends BaseModel
{
    // protected $dates = ['datecut', 'datereviewed', 'datereviewed2', 'dateuploaded', 'datecancelled', 'daterun', 'kitexpirydate',  'sampleprepexpirydate',  'bulklysisexpirydate',  'controlexpirydate',  'calibratorexpirydate',  'amplificationexpirydate', ];

    // protected $withCount = ['sample'];  
    
    // public $timestamps = false;

    public function sample()
    {
    	return $this->hasMany('App\Viralsample', 'worksheet_id');
    }

    public function runner()
    {
    	return $this->belongsTo('App\User', 'runby');
    }

    public function sorter()
    {
        return $this->belongsTo('App\User', 'sortedby');
    }

    public function bulker()
    {
        return $this->belongsTo('App\User', 'bulkedby');
    }

    public function quoter()
    {
        return $this->belongsTo('App\User', 'alliquotedby');
    }

    public function creator()
    {
    	return $this->belongsTo('App\User', 'createdby');
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

    public function reviewer2()
    {
        return $this->belongsTo('App\User', 'reviewedby2');
    }



    public function getSampleTypeNameAttribute()
    {
        if($this->sampletype == 1) return "DBS";
        if($this->sampletype == 2) return "Plasma / EDTA";
    }

    public function getDumpLinkAttribute()
    {
        if(env('APP_LAB') == 9){
            $s = \App\ViralsampleView::where('worksheet_id', $this->id)->whereIn('facility_id', [50001, 3475])->first();
            if(!$s) return '';
            $url = url('viralworksheet/download_dump/' . $this->id);
            return "<a href='{$url}'> Download For EMR (IQCare) </a> |";
        }
        if(env('APP_LAB') == 8){
            // $s = \App\ViralsampleView::where('worksheet_id', $this->id)->whereIn('facility_id', [50001, 3475])->first();
            // if(!$s) return '';
            $url = url('viralworksheet/download_dump/' . $this->id);
            return "<a href='{$url}'> Download For EMR (IQCare) </a> |";
        }
        return '';
    }

    public function release_as_redraw()
    {
        $today = date('Y-m-d');

        $this->status_id = 3;
        $this->daterun = $this->datereviewed = $this->datereviewed2 = $today;
        $this->save();

        $samples = \App\ViralsampleView::where('worksheet_id', $this->id)->where('site_entry', '!=', 2)->get();

        foreach ($samples as $key => $sample) {
            $s = \App\Viralsample::find($sample->id);

            $s->labcomment = "Failed Test";
            $s->repeatt = 0;
            $s->result = "Collect New Sample";
            $s->dateapproved = $s->dateapproved2 = $today; 

            $s->save();
            \App\MiscViral::check_batch($s->batch_id);
        }
    }
}
