<?php

namespace App;

use App\ViewModel;

class Cd4SampleView extends ViewModel
{	
	protected $table = 'cd4_samples_view';

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
