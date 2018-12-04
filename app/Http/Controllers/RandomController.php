<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LabEquipmentTracker;
use App\LabPerformanceTracker;
use App\Sample;
use App\Viralsample;

class RandomController extends Controller
{

	public function refresh_cache()
	{		
		$lookup = \App\Lookup::refresh_cache();
		return back();
	}

	public function refresh_dashboard()
	{		
		$lookup = \App\DashboardCacher::refresh_cache();
		return back();
	}

	public function download_api()
	{		
    	$path = public_path('Lab.postman_collection.json');
    	return response()->download($path);
	}

	public function send_to_login($param = null)
	{
		return redirect('/login');
	}



	public function sysswitch($sys)
	{
		if($sys == 'EID'){
			$new = session(['testingSystem' => 'EID']);
		}else if ($sys == 'Viralload'){
			$new = session(['testingSystem' => 'Viralload']);
		}else if ($sys == 'DR'){
			$new = session(['testingSystem' => 'DR']);
		}else if ($sys == 'CD4'){
			$new = session(['testingSystem' => 'CD4']);
		}
		
		echo json_encode(session('testingSystem'));
	}

	public function search()
	{
		return view('forms.search')->with('pageTitle', 'Search');
	}

	public function lablogs($year = null, $month = null){
		if ($year == null || $year=='null') {
			if(null !== session('lablogyear')) {
				$year = session('lablogyear');
			} else {
				$set = session(['lablogyear' => date('Y')]);
			}
		} else {
			$set = session(['lablogyear' => $year]);
		}


		if ($month == null || $month=='null') {
			if (null !== session('lablogmonth')) {
				$month = session('lablogmonth');
			} else {
				$set = session(['lablogmonth' => date('m') - 1]);
			}
		} else {
			$set = session(['lablogmonth' => $month]);
		}

		$year = session('lablogyear');
		$month = session('lablogmonth');
		$performance = LabPerformanceTracker::where('year', $year)->where('month', $month)->get();
		$eidcount = Sample::selectRaw("count(*) as tests")->whereYear('datetested', $year)->whereMonth('datetested', $month)->where('flag', '=', 1)->first()->tests;
		$eidrejected = Sample::selectRaw('distinct rejectedreasons.name')->join('rejectedreasons', 'rejectedreasons.id', '=', 'samples.rejectedreason')->where('receivedstatus', '=', 2)->get();

		$vlplasmacount = Viralsample::selectRaw("count(*) as tests")->whereYear('datetested', $year)->whereMonth('datetested', $month)->where('flag', 1)->whereBetween('sampletype', [1,2])->first()->tests;
		$vlplasmarejected = Viralsample::selectRaw('distinct rejectedreasons.name')->join('rejectedreasons', 'rejectedreasons.id', '=', 'viralsamples.rejectedreason')->where('receivedstatus', '=', 2)->whereBetween('sampletype', [1,2])->get();

		$vldbscount = Viralsample::selectRaw("count(*) as tests")->whereYear('datetested', $year)->whereMonth('datetested', $month)->where('flag', 1)->whereBetween('sampletype', [3,4])->first()->tests;
		$vldbsrejected = Viralsample::selectRaw('distinct rejectedreasons.name')->join('rejectedreasons', 'rejectedreasons.id', '=', 'viralsamples.rejectedreason')->where('receivedstatus', '=', 2)->whereBetween('sampletype', [3,4])->get();
		
		$equipment = LabEquipmentTracker::where('year', $year)->where('month', $month)->get();
		$data = (object)['performance' => $performance, 'equipments' => $equipment, 'year' => $year, 'month' => $month, 'eidcount' => $eidcount, 'vlplasmacount' => $vlplasmacount, 'vldbscount' => $vldbscount, 'eidrejected' => $eidrejected, 'vlplasmarejected' => $vlplasmarejected, 'vldbsrejected' => $vldbsrejected];
		// dd($data);
		return view('reports.labtrackers', compact('data'))->with('pageTitle', 'Lab Equipment Log/Tracker');
	}

	public function config()
	{
		return phpinfo();
	}
}
