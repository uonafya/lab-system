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
        $consumption = new CovidConsumption;
        $weeks = $consumption->getMissingConsumptions();
        if (sizeof($weeks) == 0) {
            session(['toast_message' => "Covid Consumption already filled.",
                'toast_error' => true]);
            return redirect('pending');
        }

        $time = collect($weeks)->first();
    	$tests = CovidSample::whereBetween('datetested', [$time->week_start, $time->week_end])->where('receivedstatus', '<>', 2)->get()->count();
    	return view('tasks.covid.consumption',
    		[
                'covidkits' => CovidKit::get(),
                'tests' => $tests,
                'time' => $time
            ]);
    }

    public function submitConsumption(Request $request)
    {
        $consumption = new CovidConsumption;
        $time = collect($consumption->getMissingConsumptions())->first();
        // $time = $this->getPreviousWeek();
        if ($request->input('week_start') != $time->week_start) {
            session(['toast_message' => "Bad Request in submitting the form kindly refresh your browser and try again.",
                'toast_error' => true]);
            return back();
        }
        
        $data = $this->buildConsumptionData($request);
    	
        if (CovidConsumption::where('start_of_week', '=', $time->week_start)->get()->isEmpty()) {
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
                DB::commit();
            } catch(\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }
    	$consumption = new CovidConsumption;
        $weeks = $consumption->getMissingConsumptions();
        if (sizeof($weeks) == 0) {
            $this->reportRelease();
            Synch::synchCovidConsumption();
        }
        return redirect('pending');
    	
    }

    public function reports(Request $request, CovidConsumption $consumption)
    {
        if (null !== $consumption->start_of_week)
            return view('reports.covidconsumptiondetails', ['consumption' => $consumption]);
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
}

