<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidConsumption extends BaseModel
{
    public function details()
    {
    	return $this->hasMany(CovidConsumptionDetail::class, 'consumption_id', 'id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id', 'id');
    }

    public function getTestsDone($start_date, $end_date)
    {
        return CovidSample::whereBetween('datetested', [$start_date, $end_date])
                    ->where('receivedstatus', '<>', 2)->get()->count();
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

    public function getMissingConsumptions()
    {
        $previous_weeks = [
                        'first' => '2020-04-06',
                        'second' => '2020-04-13',
                        'third' => '2020-04-20',
                    ];
        $data = [];
        foreach ($previous_weeks as $key => $week) {
            if ($key == 'first' && env('APP_LAB') == 1){
                if ($this->getdata($week))
                    $data[] = $this->getdata($week);
            } else if (in_array(env('APP_LAB'), [1, 5, 9]) && $key != 'first') {
                if ($this->getdata($week))
                    $data[] = $this->getdata($week);
            }
            
        }
        $data[] = $this->getPreviousWeek();
        return $data;
    }

    public function fillTestsDone()
    {
        foreach ($this->whereNull('tests')->get() as $key => $consumption) {
            $consumption->tests = (int)$this->getTestsDone($consumption->start_of_week, $consumption->end_of_week);
            $consumption->save();
        }
        // Synch::synchCovidConsumption();
    }

    private function getdata($week)
    {
        if ($this->whereDate('start_of_week', $week)->get()->isEmpty())
            return (object)['week_start' => $week,
                            'week_end' => date('Y-m-d', strtotime('+6 days', strtotime($week))),
                            'week' => date('W', strtotime($week))
                        ];
        return null;
    }
}
