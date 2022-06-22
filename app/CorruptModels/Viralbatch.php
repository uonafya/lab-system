<?php

namespace App\CorruptModels;

use Illuminate\Database\Eloquent\Model;

class Viralbatch extends BaseModel
{
    public function sample()
    {
        return $this->hasMany('App\Viralsample', 'batch_id');
    }
}
