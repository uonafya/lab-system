<?php

namespace App;

use App\ViewModel;

class DrSampleView extends ViewModel
{
	protected $table = 'dr_samples_view';
	


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

    public function getChromatogramLinkAttribute()
    {
        $ui_url = \App\MiscDr::$ui_url;
        return $ui_url . $this->chromatogram_url;
    }

    public function getViewChromatogramAttribute()
    {
        $full_link = "<a href='{$this->chromatogram_link}' target='_blank'> View Chromatogram </a>";
        return $full_link;
    }
}
