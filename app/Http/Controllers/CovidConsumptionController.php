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
        $user = auth()->user();
    	$tests = $consumption->getTestsDone($time->week_start, $time->week_end);
        $kits = CovidKit::with('machine')->when($user, function($query) use ($user){
                                        if ($user->user_type_id == 12)
                                            return $query->where('type', '<>', 'Kit');
                                        else
                                            return $query->where('type', '<>', 'Manual');
                                    })->get()->groupby('machine');
        
        // dd($kits);
    	return view('tasks.covid.consumption',
    		[
                'covidkits' => $kits,
                'tests' => $tests,
                'time' => $time
            ]);
    }

    public function pending()
    {
        // $data['covidconsumption'] = CovidConsumption::where('start_of_week', '=', $this->getPreviousWeek()->week_start)
        //                                 ->where('lab_id', '=', auth()->user()->lab_id)->count();
        $covidconsumption = new CovidConsumption;
        $data['time'] = $covidconsumption->getMissingConsumptions();
        // dd($data);
        return view('tasks.covid.manual', $data);
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
                $lab = env('APP_LAB');
                if (auth()->user()->user_type_id == 12)
                    $lab = auth()->user()->lab_id;
                $consumption = new CovidConsumption;
                $consumption->fill([
                                'start_of_week' => $time->week_start,
                                'end_of_week' => $time->week_end,
                                'week' => $time->week,
                                'lab_id' => $lab
                            ]);
                $consumption->tests = $consumption->getTestsDone($consumption->start_of_week, $consumption->end_of_week);
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
            if (auth()->user()->user_type_id != 12)
                $this->reportRelease();
            Synch::synchCovidConsumption();
        }
        if (auth()->user()->user_type_id == 12)
            return redirect('covidkits/pending');    
        return redirect('pending');
    	
    }

    public function reports(Request $request, CovidConsumption $consumption)
    {
        if (null !== $consumption->start_of_week)
            return view('reports.covidconsumptiondetails', ['consumption' => $consumption]);
        $user = auth()->user();
    	return view('reports.covidconsumption',
                    ['consumptions' => CovidConsumption::when($user, function ($query) use ($user){
                                                if ($user->user_type_id == 12)
                                                    return $query->where('lab_id', '=', $user->lab_id);
                                        })->get()
                ]);
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

