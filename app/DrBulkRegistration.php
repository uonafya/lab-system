<?php

namespace App;

use App\BaseModel;

class DrBulkRegistration extends BaseModel
{

    public function sample_view()
    {
        return $this->hasMany('App\DrSampleView', 'bulk_registration_id');
    }

    public function sample()
    {
        return $this->hasMany('App\DrSample', 'bulk_registration_id');
    }
}
