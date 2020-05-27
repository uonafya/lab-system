<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $table = "machines";

    public function kits() {
    	return $this->hasMany('App\Kits');
    }

    public function eid_worksheets()
    {
        return $this->hasMany(Worksheet::class, 'machine_type', 'id');
    }

    public function viral_worksheets()
    {
        return $this->hasMany(Viralworksheet::class, 'machine_type', 'id');
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

}