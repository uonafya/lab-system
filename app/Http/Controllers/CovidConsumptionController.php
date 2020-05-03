<?php

namespace App\Http\Controllers;

use App\CovidConsumption;
use App\CovidConsumptionDetail;
use App\CovidKit;
use App\CovidSample;
use App\Synch;
use DB;
use Illuminate\Http\Request;

class CovidConsumptionController extends Controller
{
    public function index()
    {
    	$time = $this->getPreviousWeek();

        if (!CovidConsumption::whereDate('start_of_week', $time->week_start)->get()->isEmpty()) {
            session(['toast_message' => "Covid Consumption already filled.",
                    'toast_error' => true]);
            return redirect('pending');
        }
    	$tests = CovidSample::whereBetween('datetested', [$time->week_start, $time->week_end])->where('receivedstatus', '<>', 2)->get()->count();
    	return view('tasks.covid.consumption',
    		['covidkits' => CovidKit::get(),
    		'tests' => $tests]);
    }

    public function submitConsumption(Request $request)
    {
        $data = $this->buildConsumptionData($request);
    	$time = $this->getPreviousWeek();
    	
    	// Start transaction!
        DB::beginTransaction();

        try {
        	$consumption = new CovidConsumption;
        	$consumption->fill([
        					'start_of_week' => $time->week_start,
        					'end_of_week' => $time->week_end,
        					'week' => $time->week,
        					'lab_id' => env('APP_LAB')
        				]);
        	$consumption->save();

        	foreach ($data as $key => $detail) {
        		$consumption_detail = new CovidConsumptionDetail;
        		$consumption_detail->consumption_id = $consumption->id;
        		$consumption_detail->fill($detail);
        		$consumption_detail->save();
        	}
            Synch::synchCovidConsumption();
        	DB::commit();
            return redirect('home');
        } catch(\Exception $e) {
            DB::rollback();
            throw $e;
        }
    	
    }

    public function reports(Request $request)
    {
    	return view('reports.covidconsumption', ['consumptions' => CovidConsumption::get()]);
    }

    private function buildConsumptionData($request)
    {
    	$datacolumns = $request->only(["kits_used","begining_balance","received","positive",
							"negative","wastage","ending","requested"]);
    	$data = [];
    	foreach ($datacolumns as $columnkey => $datacolumn) {
    		foreach ($datacolumn as $key => $value) {
    			$kit = CovidKit::where('material_no', $key)->first();
    			$data[$key]['kit_id'] = $kit->id;
    			$data[$key][$columnkey] = $value;
    		}
    	}
    	return $data;
    }

 //    private function getPreviousWeek()
 //    {
 //    	$date = strtotime('-7 days', strtotime(date('Y-m-d')));
 //    	return $this->getStartAndEndDate(date('W', $date),
 //    							date('Y', $date));
 //    }

 //    private function getStartAndEndDate($week, $year) {
	// 	$dto = new \DateTime();
	// 	$dto->setISODate($year, $week);
	// 	$ret['week_start'] = $dto->format('Y-m-d');
	// 	$dto->modify('+6 days');
	// 	$ret['week_end'] = $dto->format('Y-m-d');
	// 	$ret['week'] = date('W', strtotime($ret['week_start']));
	// 	return (object)$ret;
	// }
}

