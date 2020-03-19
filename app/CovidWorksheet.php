<?php

namespace App;

class CovidWorksheet extends BaseModel
{

    public function sample()
    {
        return $this->hasMany('App\CovidSample', 'worksheet_id');
    }
}
