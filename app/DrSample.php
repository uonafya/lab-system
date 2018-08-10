<?php

namespace App;

use App\BaseModel;

class DrSample extends BaseModel
{

    public function patient()
    {
        return $this->belongsTo('App\Viralpatient', 'patient_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\DrWorksheet', 'worksheet_id');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
