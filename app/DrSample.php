<?php

namespace App;

use App\BaseModel;

class DrSample extends BaseModel
{

    public function patient()
    {
        return $this->belongsTo('App\Viralpatient', 'patient_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\DrWorksheet', 'worksheet_id');
    }

    public function extraction_worksheet()
    {
        return $this->belongsTo('App\DrExtractionWorksheet', 'extraction_worksheet_id');
    }

    public function bulk_registration()
    {
        return $this->hasMany('App\DrBulkRegistration', 'bulk_registration_id');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function view_facility()
    {
        return $this->belongsTo('App\ViewFacility', 'facility_id');
    }


    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\DrSample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\DrSample', 'parentid');
    }



    public function warning()
    {
        return $this->hasMany('App\DrWarning', 'sample_id');
    }

    public function dr_call()
    {
        return $this->hasMany('App\DrCall', 'sample_id');
    }

    public function genotype()
    {
        return $this->hasMany('App\DrGenotype', 'sample_id');
    }

    public function approver()
    {
        return $this->belongsTo('App\User', 'approvedby');
    }

    public function final_approver()
    {
        return $this->belongsTo('App\User', 'approvedby2');
    }

    /**
     * Get if rerun has been created
     *
     * @return string
     */
    public function getHasRerunAttribute()
    {
        if($this->parentid == 0){
            $child_count = $this->child->count();
            if($child_count) return true;
        }
        else{
            $run = $this->run + 1;
            $child = \App\DrSample::where(['parentid' => $this->parentid, 'run' => $run])->first();
            if($child) return true;
        }
        return false;
    }


    public function scopeRuns($query, $sample)
    {
        if($sample->parentid == 0){
            return $query->whereRaw("parentid = {$sample->id} or id = {$sample->id}")->orderBy('run', 'asc');
        }
        else{
            return $query->whereRaw("parentid = {$sample->parentid} or id = {$sample->parentid}")->orderBy('run', 'asc');
        }
    }

    public function remove_rerun()
    {
        if($this->parentid == 0) $this->remove_child();
        else{
            $this->remove_sibling();
        }
    }

    public function remove_child()
    {
        $children = $this->child;

        foreach ($children as $s) {
            $s->delete();
        }

        $this->repeatt=0;
        $this->save();
    }

    public function remove_sibling()
    {
        $parent = $this->parent;
        $children = $parent->child;

        foreach ($children as $s) {
            if($s->run > $this->run) $s->delete();            
        }

        $this->repeatt=0;
        $this->save();
    }




    // mid being my id
    // Used when sending samples to sanger
    public function getMidAttribute()
    {
        return env('DR_PREFIX') . $this->id;
    }

    public function getChromatogramLinkAttribute()
    {
        $ui_url = 'http://sangelamerkel.exatype.co.za';
        return $ui_url . $this->chromatogram_url;
    }

    public function getViewChromatogramAttribute()
    {
        $full_link = "<a href='{$this->chromatogram_link}' target='_blank'> View Chromatogram </a>";
        return $full_link;
    }



    public function setArvToxicitiesAttribute($value)
    {
        if($value && is_array($value)){
            $val = '[';
            foreach ($value as $v) {
                $val .= "'" . $v . "',";
            }
            $this->attributes['arv_toxicities'] = $val . ']';
        }
        else{
            $this->attributes['arv_toxicities'] = "[]";
        }
    }

    public function getArvToxicitiesArrayAttribute()
    {
        return eval("return " . $this->arv_toxicities . ";");
    }

    public function setClinicalIndicationsAttribute($value)
    {
        if($value && is_array($value)){
            $val = '[';
            foreach ($value as $v) {
                $val .= "'" . $v . "',";
            }
            $this->attributes['clinical_indications'] = $val . ']';
        }
        else{
            $this->attributes['clinical_indications'] = "[]";
        }
    }

    public function getClinicalIndicationsArrayAttribute()
    {
        return eval("return " . $this->clinical_indications . ";");
    }

    public function setOtherMedicationsAttribute($value)
    {
        if($value && is_array($value)){
            $val = '[';
            foreach ($value as $v) {
                $val .= "'" . $v . "',";
            }
            $this->attributes['other_medications'] = $val . ']';
        }
        else{
            $this->attributes['other_medications'] = "[]";
        }
    }

    public function getOtherMedicationsArrayAttribute()
    {
        return eval("return " . $this->other_medications . ";");   
    }

    public function getOtherMedicationsStringAttribute()
    {
        $my_array = $this->other_medications_array;
        $str = '';

        if(is_array($my_array)){
            foreach ($my_array as $value) {
                if(!is_numeric($value)) $str .= trim($value) . ', ';
            }
        }

        return $str;   
    }

    public function get_primers($date_created=null, $row, $column = 1)
    {
        $primers = ['F1', 'F2', 'F3', 'R1', 'R2', 'R3'];
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        if(!$date_created) return null;

        $str = '';

        foreach ($primers as $key => $value) {
            $loc = $key+1;
            if($column == 2) $loc += 6;

            $str .= '<td>';

            
        }
    }

    public function create_rerun($data=null)
    {
        $fields = \App\Lookup::viralsamples_arrays();

        if(!$this->has_rerun && !$this->control){
            $child = new DrSample;
            $child->fill($this->only($fields['dr_sample_rerun']));                
            $child->run++;
            if($child->parentid == 0) $child->parentid = $this->id;
            $child->save();

            // $child = $this->replicate(['exatype_id', 'datetested', ]);

            if($data) $this->fill($data);
            $this->collect_new_sample = 0;
            $this->repeatt = 1;
            $this->pre_update();
        }

        if($this->control) $this->save();           
    }


    /**
     * Get the patient's gender
     *
     * @return string
     */
    public function getControlTypeAttribute()
    {
        if($this->control == 1){ return "Negative Control"; }
        else if($this->control == 2){ return "Positive Control"; }
        else{ return "Normal Sample"; }
    }


    /**
     * Get the sample's Sample Type
     *
     * @return string
     */
    public function getSampleTypeOutputAttribute()
    {
        if($this->sample_type == 1) return "Public";
        if($this->sample_type == 2) return "Surveillance";
        if($this->sample_type == 3) return "Study";
        return "";
    }






}
