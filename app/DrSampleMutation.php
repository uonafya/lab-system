<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DrSampleMutation extends BaseModel
{

    public function sample()
    {
        return $this->belongsTo('App\DrSample', 'sample_id');
    }
	
    public function mutation()
    {
        return $this->belongsTo('App\DrMutation', 'mutation_id');
    }
}
