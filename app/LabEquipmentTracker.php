<?php

namespace App;

use App\BaseModel;

class LabEquipmentTracker extends BaseModel
{
    // protected $fillable = ['month','year','lab_id','equipment_id','datesubmitted','submittedBy','dateemailsent','datebrokendown','datereported','datefixed','downtime','samplesnorun','failedruns','reagentswasted','breakdownreason','othercomments'];



    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function submitter()
    {
        return $this->belongsTo('App\User', 'submittedBy');
    }

    public function equipment() {
    	return $this->belongsTo('App\LabEquipment', 'equipment_id');
    }

    public function scopeExisting($query, $year, $month, $equipment_id)
    {
        return $query->where(['year' => $year, 'month' => $month, 'equipment_id' => $equipment_id]);
    }

    public static function resetemail($restrict = false)
    {
        $today = date('Y-m-d');
        $threemonthsago = date('Y-m-d', strtotime("-3 Months", strtotime($today)));
        
        $performances = LabEquipmentTracker::whereNull('dateemailsent')
                        ->when($restrict, function($query) use ($threemonthsago){
                            return $query->whereDate('datesubmitted', '<', $threemonthsago);
                        })->get();
        echo "==> Records found {$performances->count()}";
        foreach ($performances as $key => $performance) {
            $performance->dateemailsent = $performance->datesubmitted;
            $performance->save();
        }
        return true;
    }
}
