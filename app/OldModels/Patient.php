<?php

namespace App\OldModels;

use App\OldModels\BaseModel;

class Patient extends BaseModel
{
	protected $key = 'autoID';


    public function mother()
    {
    	return $this->belongsTo('App\OldModels\Mother', 'mother', 'ID');
    }


	public function setGenderAttribute($value)
	{
		switch ($value) {
			case 1:
				$gender = 'M';
				break;
			case 2:
				$gender = 'F';
				break;			
			default:
				$gender = 'No Data';
				break;
		}
		$this->attributes['gender'] = $gender;
	}
}