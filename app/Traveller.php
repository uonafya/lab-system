<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Traveller extends BaseModel
{


    public function setResultAttribute($value)
    {
        if(is_numeric($value) || !$value) $this->attributes['result'] = $value;
        else{
            $value = strtolower($value);
            if(\Str::contains($value, ['neg'])) $this->attributes['result'] = 1;
            else if(\Str::contains($value, ['pos'])) $this->attributes['result'] = 2;
            else if(\Str::contains($value, ['coll'])) $this->attributes['result'] = 5;
        }
    }

    public function getResultNameAttribute()
    {
        if($this->result == 1){ return "Negative"; }
        else if($this->result == 2){ return "Positive"; }
        else if($this->result == 3){ return "Failed"; }
        else if($this->result == 5){ return "Collect New Sample"; }
        else{ return ""; }
    }

    public function setIgmResultAttribute($value)
    {
        if(is_numeric($value) || !$value) $this->attributes['igm_result'] = $value;
        else{
            $value = strtolower($value);
            if(\Str::contains($value, ['neg'])) $this->attributes['igm_result'] = 1;
            else if(\Str::contains($value, ['pos'])) $this->attributes['igm_result'] = 2;
            else if(\Str::contains($value, ['coll'])) $this->attributes['igm_result'] = 5;
        }
    }

    public function getIgmResultNameAttribute()
    {
        if($this->igm_result == 1){ return "Negative"; }
        else if($this->igm_result == 2){ return "Positive"; }
        else if($this->igm_result == 3){ return "Failed"; }
        else if($this->igm_result == 5){ return "Collect New Sample"; }
        else{ return ""; }
    }

    public function setIggIgmResultAttribute($value)
    {
        if(is_numeric($value) || !$value) $this->attributes['igg_igm_result'] = $value;
        else{
            $value = strtolower($value);
            if(\Str::contains($value, ['neg'])) $this->attributes['igg_igm_result'] = 1;
            else if(\Str::contains($value, ['pos'])) $this->attributes['igg_igm_result'] = 2;
            else if(\Str::contains($value, ['coll'])) $this->attributes['igg_igm_result'] = 5;
        }
    }

    public function getIggIgmResultNameAttribute()
    {
        if($this->igg_igm_result == 1){ return "Negative"; }
        else if($this->igg_igm_result == 2){ return "Positive"; }
        else if($this->igg_igm_result == 3){ return "Failed"; }
        else if($this->igg_igm_result == 5){ return "Collect New Sample"; }
        else{ return ""; }
    }


    public function setSexAttribute($value)
    {
        if(is_numeric($value)) $this->attributes['sex'] = $value;
        else{
            if(\Str::contains($value, ['F', 'f'])) $this->attributes['sex'] = 2;
            else if(\Str::contains($value, ['M', 'm'])){
                $this->attributes['sex'] = 1;
            }
        }
    }


    /**
     * Get the patient's gender
     *
     * @return string
     */
    public function getGenderAttribute()
    {
        if($this->sex == 1){ return "Male"; }
        else if($this->sex == 2){ return "Female"; }
        else{ return "No Gender"; }
    }

}
