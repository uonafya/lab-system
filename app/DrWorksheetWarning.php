<?php

namespace App;

use App\BaseModel;

class DrWorksheetWarning extends BaseModel
{

    public function worksheet()
    {
        return $this->hasMany('App\DrWorksheet', 'worksheet_id');
    }
}
