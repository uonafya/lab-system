<?php

namespace App;

use App\ViewModel;

class Cd4SampleView extends ViewModel
{	
	protected $table = 'cd4_samples_view';

    public function first_approver(){
        return $this->belongsTo('App\User', 'approvedby');
    }

    public function second_approver(){
        return $this->belongsTo('App\User', 'approvedby2');
    }

    public function printer(){
        return $this->belongsTo('App\User', 'printedby');
    }

    /**
     * Get the patient's age in months
     *
     * @return integer
     */
    public function getAgeAttribute()
    {
        return \App\Lookup::calculate_viralage(date('Y-m-d'), $this->dob);
    }
}
