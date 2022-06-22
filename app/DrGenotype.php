<?php

namespace App;

use App\BaseModel;

class DrGenotype extends BaseModel
{

    public function sample()
    {
        return $this->belongsTo('App\DrSample', 'sample_id');
    }
	
    public function residue()
    {
        return $this->belongsTo('App\DrResidue', 'genotype_id');
    }
}
