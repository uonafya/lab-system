<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViralsampleView extends Model
{
	protected $table = 'viralsamples_view';


    public function scopeSample($query, $facility, $patient, $datecollected)
    {
        return $query->where(['facility_id' => $facility, 'patient' => $patient, 'datecollected' => $datecollected]);
    }
}
