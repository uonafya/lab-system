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

	// Filter routes
	public function filter_date(Request $request)
	{
		if(!session('filter_groupby')) abort(400);
		$default_financial = session('filter_financial_year');

		$year = $request->input('year');
		$month = $request->input('month');

		$to_year = $request->input('to_year');
		$to_month = $request->input('to_month');
		$prev_year = ($year - 1);

		$financial_year = $request->input('financial_year', $default_financial);
		$quarter = $request->input('quarter');

		$range = ['filter_year' => $year, 'filter_month' => $month, 'to_year' => $to_year, 'to_month' => $to_month, 'filter_financial_year' => $financial_year, 'filter_quarter' => $quarter];

		session($range);

		$display_date = ' (October, ' . ($financial_year-1) . ' - September ' . $financial_year . ')';
		if($quarter){
			switch ($quarter) {
				case 1:
					$display_date = "(October - December " . ($financial_year-1) . ")";
					break;
				case 2:
					$display_date = "(January - March " . $financial_year . ")";
					break;
				case 3:
					$display_date = "(April - June " . $financial_year . ")";
					break;
				case 4:
					$display_date = "(July - September " . $financial_year . ")";
					break;					
				default:
					break;
			}
		}
		if($month){
			if($month < 10) $display_date = '(' . $financial_year . ' ' . Lookup::resolve_month($month) . ')';
			if($month > 9) $display_date = '(' . ($financial_year-1) . ' ' . Lookup::resolve_month($month) . ')';
		}
		if($to_year){
			if($year == $to_year) 
				$display_date = '(' . Lookup::resolve_month($month) . ' - ' . Lookup::resolve_month($to_month) . " {$year})";
			else{
				$display_date = "(" . Lookup::resolve_month($month) . ", {$year} - " . Lookup::resolve_month($to_month) . ", {$to_year})";
			}
		}

		return ['year' => $year, 'prev_year' => $prev_year, 'range' => $range, 'display_date' => $display_date];
	}


	public function filter_any(Request $request)
	{
		if(!session('filter_groupby')) abort(400);
		$var = $request->input('session_var');
		$val = $request->input('value');

		if($val == null || (!is_array($val) && in_array($val, ['null', ''])) || (is_array($val) && in_array('null', $val)) ) $val = null;
		session([$var => $val]);

		return [$var => $val];
	}



	public function index()
	{
		return view('dashboard.dr', DrDashboard::get_divisions());
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
				$data["outcomes"][$i]["data"][$key] = (int) ($rows->where('short_name_id', $drug->id)->where('call', $call_key)->first()->samples ?? 0);
				$i++;
			}
		}
		return view('charts.bar_graph', $data);
	}

	public function heat_map()
	{
		$rows = DrCallDrug::join('dr_calls', 'dr_calls.id', '=', 'dr_call_drugs.call_id')
			->join('dr_samples', 'dr_samples.id', '=', 'dr_calls.sample_id')
			->join('view_facilitys', 'view_facilitys.id', '=', 'dr_samples.facility_id')
			->selectRaw("county, dr_call_drugs.call, COUNT(dr_call_drugs.id) AS samples")
			->groupBy('county_id', 'call')
			->orderBy('county_id')
			->get();

		$data = DrDashboard::bars(['Low Coverage', 'Resistant', 'Intermediate Resistance', 'Susceptible'], 'column', ['#595959', "#ff0000", "#ff9900", "#00ff00"]);
		$data['categories'] = [];

		$call_array = MiscDr::$call_array;

		$category_id = 0;

		$res = ['LC' => 0, 'R' => 1, 'I' => 2, 'S' => 3];

		foreach ($rows as $key => $row){
			if($data['categories'] && $data['categories'][$category_id] != $row->county) $category_id ++;

			$out_key = $res[$row->call];
			$data['categories'][$category_id] = $row->county;
			$data['outcomes'][$category_id]['data'][$out_key] = (int) $row->samples;
		}
		return view('charts.bar_graph', $data);
	}
}
