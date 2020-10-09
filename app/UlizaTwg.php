<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UlizaTwg extends BaseModel
{

    public function county()
    {
        return $this->hasMany('App\County', 'twg_id');
    }

    public function clinical_form()
    {
        return $this->hasMany('App\UlizaClinicalForm', 'twg_id');
    }

    public function user()
    {
        return $this->hasMany('App\User', 'twg_id');
    }

    public function getEmailArrayAttribute()
    {
        return $this->user->pluck('email')->toArray();
    }
}
