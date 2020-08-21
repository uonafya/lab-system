<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidKitType extends BaseModel
{

	public function machine()
    {
        return $this->belongsTo('App\Machine');
    }
}
