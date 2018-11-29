<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LabEquipmentTracker;
use App\LabPerformanceTracker;

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

	public function lab_equipment(){
		$month = date('m') - 1;
		$performance = LabPerformanceTracker::where('year', date('Y'))->where('month', $month)->get();
		$data = (object)['performance' => $performance];
		// dd($data);
		return view('reports.labtrackers', compact('data'))->with('pageTitle', 'Lab Equipment Log/Tracker');
	}

	public function config()
	{
		return phpinfo();
	}
}
