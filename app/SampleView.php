<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleView extends Model
{
	protected $table = 'samples_view';


    public function scopeSample($query, $facility, $patient, $datecollected)
    {
        return $query->where(['facility_id' => $facility, 'patient' => $patient, 'datecollected' => $datecollected]);
    }

    public function scopePatient($query, $facility, $patient)
    {
        return $query->where(['facility_id' => $facility, 'patient' => $patient]);
    }
}
