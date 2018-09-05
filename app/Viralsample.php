<?php

namespace App;

use App\BaseModel;

class Viralsample extends BaseModel
{
    // protected $dates = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'dateinitiatedontreatment', 'datesynched'];


    public function tat($datedispatched)
    {
        return \App\Misc::working_days($this->datecollected, $datedispatched);
    }

    public function patient()
    {
    	return $this->belongsTo('App\Viralpatient', 'patient_id');
    }

    public function batch()
    {
        return $this->belongsTo('App\Viralbatch', 'batch_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\Viralworksheet', 'worksheet_id');
    }


    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\Viralsample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\Viralsample', 'parentid');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'createdby');
    }

    public function canceller()
    {
        return $this->belongsTo('App\User', 'cancelledby');
    }

    public function reviewer()
    {
        return $this->belongsTo('App\User', 'reviewedby');
    }

    public function approver()
    {
        return $this->belongsTo('App\User', 'approvedby');
    }

    public function scopeRuns($query, $sample)
    {
        if($sample->parentid == 0){
            return $query->whereRaw("parentid = {$sample->id} or id = {$sample->id}");
        }
        else{
            return $query->whereRaw("parentid = {$sample->parentid} or id = {$sample->parentid}");
        }
    }


    

    /**
     * Get the sample's coloured result name
     *
     * @return string
     */

    public function getColouredResultAttribute()
    {
        if(is_numeric($this->result)){
            if($this->result < 1000){
                return "<strong><div style='color: #00ff00;'>{$this->result} </div></strong>";
            }
            else{
                return "<strong><div style='color: #ff0000;'>{$this->result} </div></strong>";             
            }
        }
        else if($this->result == "< LDL copies/ml"){
            return "<strong><div style='color: #00ff00;'>&lt; LDL copies/ml</div></strong>";
        }
        else{
            return "<strong><div style='color: #cccc00;'>{$this->result} </div></strong>";
        }
    }

    public function getSampleTypeOutputAttribute()
    {
        if($this->sampletype == 1) return "PLASMA";
        else if($this->sampletype == 2) return "EDTA";
        else if($this->sampletype == 3) return "DBS Venous";
        else if($this->sampletype == 4) return "DBS Capillary";
        return null;
    }

    public function last_test()
    {
        $sample = \App\Viralsample::where('patient_id', $this->patient_id)
                ->whereRaw("datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$this->patient_id} AND repeatt=0 AND rcategory between 1 and 4 AND datetested < '{$this->datetested}')")
                ->get()->first();
        $this->recent = $sample;
    }

    public function prev_tests()
    {
        $s = $this;
        $samples = \App\Viralsample::where('patient_id', $this->patient_id)
                ->when(true, function($query) use ($s){
                    if($s->datetested) return $query->where('datetested', '<', $s->datetested);
                    return $query->where('datecollected', '<', $s->datecollected);
                })
                ->where('repeatt', 0)
                ->whereIn('rcategory', [1, 2, 3, 4])
                ->orderBy('id', 'desc')
                ->get();
        $this->previous_tests = $samples;
    }
    
}
