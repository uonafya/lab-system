<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidConsumption extends BaseModel
{
    public function details()
    {
    	return $this->hasMany(CovidConsumptionDetail::class, 'consumption_id', 'id');
    }

    public function synchComplete()
    {
        foreach ($this->details as $key => $detail) {
            $detail->synced = 1;
            $detail->datesynced = date('Y-m-d');
            $detail->save();
        }
        $this->synced = 1;
        $this->datesynced = date('Y-m-d');
        $this->save();
    }

    public function lastweekConsumption()
    {
        $time = $this->getPreviousWeek();
        return $this->whereDate('start_of_week', $time->week_start)->get();
    }
}
