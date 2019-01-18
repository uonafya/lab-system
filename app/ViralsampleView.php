<?php

namespace App;

use App\ViewModel;

class ViralsampleView extends ViewModel
{
	protected $table = 'viralsamples_view';

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

    public function getSampleTypeOutputAttribute()
    {
        if($this->sampletype == 1) return "PLASMA";
        else if($this->sampletype == 2) return "EDTA";
        else if($this->sampletype == 3) return "DBS Capillary";
        else if($this->sampletype == 4) return "DBS Venous";
        return "";
    }
}
