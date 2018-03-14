<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Facility;
use DB;

class Lookup extends Model
{
	public function get_lookups()
	{
        $rejectedreasons = DB::table('rejectedreasons')->get();
        $genders = DB::table('gender')->get();
        $feedings = DB::table('feedings')->get();
        $iprophylaxis = DB::table('prophylaxis')->where(['ptype' => 2, 'flag' => 1])->where('rank', '>', 0)->orderBy('rank', 'asc')->get();
        $interventions = DB::table('prophylaxis')->where(['ptype' => 1, 'flag' => 1])->where('rank', '>', 0)->orderBy('rank', 'asc')->get();
        $entry_points = DB::table('entry_points')->get();
        $results = DB::table('results')->get();
        $receivedstatuses = DB::table('receivedstatus')->get();

        return [
        	'rejected_reasons' => $rejectedreasons,
        	'genders' => $genders,
        	'feedings' => $feedings,
        	'iprophylaxis' => $iprophylaxis,
        	'interventions' => $interventions,
        	'entry_points' => $entry_points,
        	'results' => $results,
        	'received_statuses' => $receivedstatuses,
        ];
	}

	public function samples_form()
	{
        $facilities = Facility::select('id', 'name')->get();
        $amrs_locations = DB::table('amrslocations')->get();
        $rejectedreasons = DB::table('rejectedreasons')->get();
        $genders = DB::table('gender')->get();
        $feedings = DB::table('feedings')->get();
        $iprophylaxis = DB::table('prophylaxis')->where(['ptype' => 2, 'flag' => 1])->where('rank', '>', 0)->orderBy('rank', 'asc')->get();
        $interventions = DB::table('prophylaxis')->where(['ptype' => 1, 'flag' => 1])->where('rank', '>', 0)->orderBy('rank', 'asc')->get();
        $entry_points = DB::table('entry_points')->get();
        $hiv_statuses = DB::table('results')->whereNotIn('id', [3, 5])->get();
        $pcrtypes = DB::table('pcrtype')->get();
        $receivedstatuses = DB::table('receivedstatus')->get();

        return [
            'facilities' => $facilities,
            'amrs_locations' => $amrs_locations,
            'rejectedreasons' => $rejectedreasons,
            'genders' => $genders,
            'feedings' => $feedings,
            'iprophylaxis' => $iprophylaxis,
            'interventions' => $interventions,
            'entry_points' => $entry_points,
            'hiv_statuses' => $hiv_statuses,
            'pcrtypes' => $pcrtypes,
            'receivedstatuses' => $receivedstatuses,

            'batch_no' => session('batch_no', 0),
            'batch_dispatch' => session('batch_dispatch', 0),
            'batch_dispatched' => session('batch_dispatched', 0),
            'batch_received' => session('batch_received', 0),

            'facility_id' => session('facility_id', 0),
            'facility_name' => session('facility_name', 0),
        ];
	}

	public function cacher()
	{
		
	}
}
