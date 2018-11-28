<?php

namespace App;

use App\BaseModel;

class DrResidue extends BaseModel
{
	
    public function genotype()
    {
        return $this->belongsTo('App\DrGenotype', 'genotype_id');
    }
}
