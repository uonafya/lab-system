<?php

namespace App;

use App\BaseModel;

class DrWorksheet extends BaseModel
{
    

    public function sample()
    {
        return $this->hasMany('App\DrSample', 'worksheet_id');
    }

}
