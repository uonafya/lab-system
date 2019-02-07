<?php

namespace App;

use App\BaseModel;

class DrCallDrug extends BaseModel
{

	private $call_array = [
		'LC' => [
			'resistance' => 'Low Coverage',
			'resistance_colour' => "#595959",
		],
		'R' => [
			'resistance' => 'Resistant',
			'resistance_colour' => "#ff0000",
		],
		'I' => [
			'resistance' => 'Intermediate Resistance',
			'resistance_colour' => "#ff9900",
		],
		'S' => [
			'resistance' => 'Susceptible',
			'resistance_colour' => "#00ff00",
		],
	];

    public function dr_call()
    {
        return $this->belongsTo('App\DrCall', 'call_id');
    }



    public function getResistanceAttribute()
    {
    	return $this->call_array[$this->call]['resistance'];
    }

    public function getResistanceColourAttribute()
    {
    	return $this->call_array[$this->call]['resistance_colour'];
    }


    public function getResistanceCellAttribute()
    {
    	$colour = $this->call_array[$this->call]['resistance_colour'];
    	return "<div style='background-color:" . $colour . ";'><td bgcolor='" . $colour . "'></td></div>";
    }


}
