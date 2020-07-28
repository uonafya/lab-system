<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CancerSampleView extends ViewModel
{
    protected $table = 'cancer_samples_view';

    /**
     * Get the sample's result name
     *
     * @return string
     */
    public function getResultNameAttribute()
    {
        if($this->result == 1){ return "Negative"; }
        else if($this->result == 2){ return "Positive"; }
        else if($this->result == 3){ return "Failed"; }
        else if($this->result == 5){ return "Collect New Sample"; }
        else{ return ""; }
    }
}
