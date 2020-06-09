<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Machine extends Model
{
    protected $table = "machines";

    public function kits() {
    	return $this->hasMany('App\Kits');
    }

    public function deliveries()
    {
        return $this->hasMany(Deliveries::class, 'machine', 'id');
    }

    public function consumptions()
    {
        return $this->hasMany(Consumption::class, 'machine', 'id');
    }

    public function eid_worksheets()
    {
        return $this->hasMany(Worksheet::class, 'machine_type', 'id');
    }

    public function viral_worksheets()
    {
        return $this->hasMany(Viralworksheet::class, 'machine_type', 'id');
    }

    public function covid_worksheets()
    {
        return $this->hasMany(CovidWorksheet::class, 'machine_type', 'id');
    }

    public function missingDeliveries($year, $month)
    {
        $data = [];
        foreach ($this->get() as $key => $machine) {
            if ($machine->deliveries->where('year', $year, 'month', $month)->isEmpty())
                $data[] = $machine;
        }
        return $data;
    }

    public function missingConsumptions($year, $month)
    {
        $data = [];
        foreach ($this->get() as $key => $machine) {
            if ($machine->consumptions->where('year', $year, 'month', $month)->isEmpty())
                $data[] = $machine;
        }
        return $data;
    }

    public function testsforLast3Months() {
    	$id = $this->id;
        $eid = Sample::selectRaw("count(*) as tests")
                    ->join('worksheets', 'worksheets.id', '=', 'samples.worksheet_id')
                    ->where('worksheets.machine_type', $id)
                    ->whereRaw("datetested >= last_day(now()) + interval 1 day - interval 3 month")
                    ->first()->tests;
        $vl = Viralsample::selectRaw("count(*) as tests")
                    ->join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples.worksheet_id')
                    ->where('viralworksheets.machine_type', $id)
                    ->whereRaw("datetested >= last_day(now()) + interval 1 day - interval 3 month")
                    ->first()->tests;
        // dd();
    	// $vl = Viralsample::selectRaw("count(*) as tests")->whereHas('worksheet', function($query) use ($id) {
		   //  		return $query->where('machine_type', '=', $id);
		   //  	})->whereRaw("datetested >= last_day(now()) + interval 1 day - interval 3 month")
    	// 		->first()->tests;

    	return (object)['EID' => $eid, 'VL' => $vl];
    }

    public function tests_done($type, $year, $month)
    {
        $returnValue = 0;
        $data = $this->getTestsFromStorage($year, $month);
        dd($data);
        foreach ($data as $key => $value) {
            dd($value);
            if ($value['testtype'] == $type){
                $dataset = $value['data'];
                $machinedata = $dataset->where('machine_type', $this->id)->first();
                $returnValue = $machinedata->tests;
            }
        }
        dd($returnValue);
        return $returnValue;
        // if ($type == 'EID')
        //     return Sample::selectRaw("count(*) as tests")
        //             ->join('worksheets', 'worksheets.id', '=', 'samples.worksheet_id')
        //             ->where('worksheets.machine_type', $this->id)
        //             ->whereYear('datetested', $year)
        //             ->whereMonth('datetested', $month)
        //             ->first()->tests;

        // if ($type == 'VL')
        //     return Viralsample::selectRaw("count(*) as tests")
        //             ->join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples.worksheet_id')
        //             ->where('viralworksheets.machine_type', $this->id)
        //             ->whereYear('datetested', $year)
        //             ->whereMonth('datetested', $month)
        //             ->first()->tests;
    }

    private function getTestsFromStorage($year, $month)
    {
        $pointer = date('Y-m', strtotime($year . '-' . $month));
        dd(Cache::get($pointer));
        if (!Cache::get($pointer)) {
            $eidtests = Sample::selectRaw("count(*) as tests, worksheets.machine_type")
                    ->join('worksheets', 'worksheets.id', '=', 'samples.worksheet_id')
                    ->whereYear('datetested', $year)
                    ->whereMonth('datetested', $month)
                    ->groupBy('machine_type')
                    ->get();
            $vltests = Viralsample::selectRaw("count(*) as tests, viralworksheets.machine_type")
                    ->join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples.worksheet_id')
                    ->whereYear('datetested', $year)
                    ->whereMonth('datetested', $month)
                    ->groupBy('machine_type')
                    ->get();
            $data = [
                'EID' => [
                        'testtype' => 'EID',
                        'year' => $year,
                        'month' => $month,
                        'data' => $eidtests
                    ],
                'VL' => [
                        'testtype' => 'VL',
                        'year' => $year,
                        'month' => $month,
                        'data' => $vltests
                    ]
                ];
            Cache::put($pointer), $data, 100);
        }
        
        return Cache::get($pointer);
    }

    public function saveNullAllocation()
    {
        $allocation = Allocation::where('year', '=', date('Y'))
                                    ->where('month', '=', date('m'))->get();
        if ($allocation->isEmpty()){
            $model = new Allocation;
            $allocation = $model->saveAlloction();
        } else {
            $allocation = $allocation->first();
        }
        
        if($allocation->details->where('machine_id', NULL)->where('testtype', NULL)->isEmpty()){
            $detail = AllocationDetail::create([
                            'allocation_id' => $allocation->id,
                        ]);
            foreach (GeneralConsumables::get() as $key => $kit) {
                $breakdown = AllocationDetailsBreakdown::create([
                                'allocation_detail_id' => $detail->id,
                                'breakdown_id' => $kit->id,
                                'breakdown_type' => Kits::class,
                            ]);
            }
        }

        $types = TestType::get();
        foreach ($types as $key => $type) {
            $details = $allocation->details->where('machine_id', $this->id)->where('testtype', $type->id);
            if ($details->isEmpty()){
                $detail = AllocationDetail::create([
                                'allocation_id' => $allocation->id,
                                'machine_id' => $this->id,
                                'testtype' => $type->id,
                            ]);
                foreach ($this->kits as $key => $kit) {
                    $breakdown = AllocationDetailsBreakdown::create([
                                    'allocation_detail_id' => $detail->id,
                                    'breakdown_id' => $kit->id,
                                    'breakdown_type' => Kits::class,
                                ]);
                }
            }
        }
        return true;
    }

    public function getCovidTestsDone($start_date, $end_date)
    {
        $user = auth()->user();
        return CovidSample::selectRaw("count(*) as `samples`")
                        ->join('covid_worksheets', 'covid_worksheets.id', '=', 'covid_samples.worksheet_id')
                        ->whereBetween('datetested', [$start_date, $end_date])
                        ->where('machine_type', $this->id)
                        ->when($user, function($query) use ($user){
                            if ($user->user_type_id == 12)
                                return $query->where('covid_samples.lab_id', $user->lab_id);
                            else
                                return $query->where('covid_samples.lab_id', env('APP_LAB'));
                        })->first()->samples;
    }

}