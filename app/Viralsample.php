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
                return "<strong><font color='#00ff00'>{$this->result} </font></strong>";
            }
            else{
                return "<strong><font color='#ff0000'>{$this->result} </font></strong>";                
            }
        }
        else if($this->result == "< LDL copies/ml"){
            return "<strong><font color='#00ff00'>&lt; LDL copies/ml </font></strong>";
        }
        else{
            return "<strong><font color='#ffff00'>{$this->result} </font></strong>";
        }
    }
}
