<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DrTwg extends BaseModel
{

    public function county()
    {
        return $this->hasMany('App\County', 'twg_id');
    }
}
