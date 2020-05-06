<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\DrDashboard;
use App\MiscDr;
use App\DrSample;
use App\DrCall;
use App\DrCallDrug;

use DB;

class DrDashboardController extends Controller
{

	public function index()
	{
		return view('dashboard.dr');
	}

	public function drug_resistance()
	{
		$rows = DrCallDrug::join('dr_calls', 'dr_calls.id', '=', 'dr_call_drugs.call_id')
			->join('dr_samples', 'dr_samples.id', '=', 'dr_calls.sample_id')
			->join('view_facilitys', 'view_facilitys.id', '=', 'dr_samples.facility_id')
			->selectRaw("dr_call_drugs.short_name_id, dr_call_drugs.call, COUNT(dr_call_drugs.id) AS samples")
			->groupBy('short_name_id', 'call')
			->get();

		$drugs = DB::table('regimen_classes')->get();
		$call_array = MiscDr::$call_array;

		$data = DrDashboard::bars(['Low Coverage', 'Resistant', 'Intermediate Resistance', 'Susceptible'], 'column', ['#595959', "#ff0000", "#ff9900", "#00ff00"]);

		foreach ($drugs as $key => $drug) {
			$data['categories'][$key] = $drug->short_name;
			$i = 0;
			foreach ($call_array as $call_key => $c) {
				if($i==4) break;
				$data["outcomes"][$i]["data"][$key] = (int) $rows->where('short_name_id', $drug->id)->where('call', $call_key)->first()->samples ?? 0;
				$i++;
			}
		}
		return view('charts.bar_graph', $data);
	}
}
