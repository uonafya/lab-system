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
}
