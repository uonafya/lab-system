<?php

namespace App;

use App\ViewModel;

class SampleView extends ViewModel
{
	protected $table = 'samples_view';

	function lab_entered_by($user=null)
	{
		if($user == null) return '';
		$user = \App\User::where('id', '=', $user)->first();
		return $user->surname . ' '. $user->oname;
	}

	function batch_received_by($user=null)
	{
		if($user == null) return '';
		$user = \App\User::where('id', '=', $user)->first();
		return $user->surname . ' '. $user->oname;
	}

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
