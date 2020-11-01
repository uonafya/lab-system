<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\DrDashboard;
use App\MiscDr;
use App\DrSample;
use App\DrCall;
use App\DrCallDrug;

use DB;
use Str;

class DrDashboardProposedController extends DrDashboardBaseController
{


	// Views
	public function index()
	{        
		DrDashboard::clear_cache();
		return view('dashboard.dr_waterfall', DrDashboard::get_divisions());
	}


	// Charts
	public function waterfall()
	{
    	$divisions_query = DrDashboard::divisions_query();
        $date_query = DrDashboard::date_query('created_at');

		$total_requests = DrSample::join('view_facilitys', 'view_facilitys.id', '=', 'dr_samples.facility_id')
			->leftJoin('dr_projects', 'dr_projects.id', '=', 'dr_samples.project')
			->selectRaw("COUNT(dr_samples.id) AS total")
			->whereRaw($divisions_query)
            ->whereRaw($date_query)
			->where(['repeatt' => 0])
			->first();

		$total_accepted = DrSample::join('view_facilitys', 'view_facilitys.id', '=', 'dr_samples.facility_id')
			->leftJoin('dr_projects', 'dr_projects.id', '=', 'dr_samples.project')
			->selectRaw("COUNT(dr_samples.id) AS total")
			->whereRaw($divisions_query)
            ->whereRaw($date_query)
			->where(['repeatt' => 0, 'receivedstatus' => 1])
			->first();

		$total_passed_gel = DrSample::join('view_facilitys', 'view_facilitys.id', '=', 'dr_samples.facility_id')
			->leftJoin('dr_projects', 'dr_projects.id', '=', 'dr_samples.project')
			->selectRaw("COUNT(dr_samples.id) AS total")
			->whereRaw($divisions_query)
            ->whereRaw($date_query)
			->where(['repeatt' => 0, 'receivedstatus' => 1, 'passed_gel_documentation' => 1])
			->first();

		$total_genotyped = DrSample::join('view_facilitys', 'view_facilitys.id', '=', 'dr_samples.facility_id')
			->leftJoin('dr_projects', 'dr_projects.id', '=', 'dr_samples.project')
			->selectRaw("COUNT(dr_samples.id) AS total")
			->whereRaw($divisions_query)
            ->whereRaw($date_query)
			->where(['repeatt' => 0, 'receivedstatus' => 1, 'passed_gel_documentation' => 1, 'status_id' => 3])
			->first();

		// dd($rows);

		$data = DrDashboard::bars(['Total (%)'], 'column', ["#00ff00"]);
		$data['extra_tooltip'] = true;

		$data['categories'][0] = 'Total Requests';
		$data["outcomes"][0]["data"][0]['y'] = (int) 100;
		$data["outcomes"][0]["data"][0]['z'] = ' (' . number_format($total_requests->total) . ')';

		$data['categories'][1] = 'Total Accepted';
		$data["outcomes"][0]["data"][1]['y'] = DrDashboard::get_percentage($total_accepted->total, $total_requests->total);
		$data["outcomes"][0]["data"][1]['z'] = ' (' . number_format($total_accepted->total) . ')';

		$data['categories'][2] = 'Accepted Using VL Criteria';
		$data["outcomes"][0]["data"][2]['y'] = DrDashboard::get_percentage($total_accepted->total, $total_requests->total);
		$data["outcomes"][0]["data"][2]['z'] = ' (' . number_format($total_accepted->total) . ')';

		$data['categories'][3] = 'Passed Gel Documentation';
		$data["outcomes"][0]["data"][3]['y'] = DrDashboard::get_percentage($total_passed_gel->total, $total_requests->total);
		$data["outcomes"][0]["data"][3]['z'] = ' (' . number_format($total_passed_gel->total) . ')';

		$data['categories'][4] = 'Successfully Genotyped';
		$data["outcomes"][0]["data"][4]['y'] = DrDashboard::get_percentage($total_genotyped->total, $total_requests->total);
		$data["outcomes"][0]["data"][4]['z'] = ' (' . number_format($total_genotyped->total) . ')';

		return view('charts.line_graph', $data);
	}


	public function requests_table()
	{
        $date_query = DrDashboard::date_query('created_at');
    	$divisions_query = DrDashboard::divisions_query();

    	$facility = false;
    	if(session('filter_county')) $facility = true;

		$rows = DrSample::join('view_facilitys', 'view_facilitys.id', '=', 'dr_samples.facility_id')
			->leftJoin('dr_projects', 'dr_projects.id', '=', 'dr_samples.project')
			->selectRaw("COUNT(dr_samples.id) AS total")
			->when(true, function($query) use ($facility){
				if($facility){
					return $query->addSelect('view_facilitys.name', 'facilitycode')
						->groupBy('county_id');
				}
				else{
					return $query->addSelect('county')
						->groupBy('county_id');
				}
			})
			->whereRaw($divisions_query)
            ->whereRaw($date_query)
			->where(['repeatt' => 0])
			->get();

		$div = Str::random(15);

		return view('charts.table_requests', compact('div', 'rows', 'facility'));
	}



}
