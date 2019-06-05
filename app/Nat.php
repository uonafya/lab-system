<?php

namespace App;

use Excel;
use DB;
use App\Facility;

use Carbon\Carbon;

use Illuminate\Support\Facades\Mail;

use App\Mail\CustomMailOld;
use App\Mail\TestMail;

class Nat
{
	public static function get_current_gender_query($facility_id, $date_params=null)
	{
    	$sql = 'SELECT sex, rcategory, count(*) as totals ';
		$sql .= 'FROM ';
		$sql .= '(SELECT v.id, v.facility_id, v.sex, v.rcategory, v.result ';
		$sql .= 'FROM viralsamples_view v ';
		$sql .= 'RIGHT JOIN ';
		$sql .= '(SELECT ID, patient_id, max(datetested) as maxdate ';
		$sql .= 'FROM viralsamples_view ';
		if($date_params) $sql .= 'WHERE ( datetested between "' . $date_params[0] . '" and "' . $date_params[1] . '" ) ';
		else {
			$sql .= 'WHERE ( datetested between "2018-01-01" and "2018-12-31" ) ';
		}
		$sql .= "AND patient != '' AND patient != 'null' AND patient is not null ";
		$sql .= 'AND flag=1 AND repeatt=0 AND rcategory in (1, 2, 3, 4) ';
		$sql .= 'AND justification != 10 and facility_id != 7148 ';
		$sql .= "AND facility_id={$facility_id} ";
		$sql .= 'GROUP BY patient_id) gv ';
		$sql .= 'ON v.id=gv.id) tb ';
		// $sql .= 'WHERE ';		
		// if($param == 1) $sql .= ' rcategory = 1 ';
		// if($param == 2) $sql .= ' rcategory = 2 ';
		// if($param == 4) $sql .= ' (rcategory IN (3,4)) ';
		$sql .= 'GROUP BY sex, rcategory ';
		$sql .= 'ORDER BY sex, rcategory ';

		return $sql;
	}


	public static function save_gender_results()
	{
		ini_set("memory_limit", "-1");

		$start_date = Carbon::now()->subYear();
		$days = $start_date->day;

		$start_date = $start_date->subDays($days-1)->toDateString();
		$end_date = Carbon::now()->subDays($days)->toDateString();
		$date_params = [$start_date, $end_date];

		$facilities = \App\Facility::whereRaw("id IN (SELECT DISTINCT facility_id FROM viralsamples_view where datetested between '{$start_date}' AND '{$end_date}' )")->get();

		foreach ($facilities as $key => $facility) {

			$sql = self::get_current_gender_query($facility->id, $date_params);
			$data = collect(DB::select($sql));

			$rows[] = [
				'No' => ($key+1),
				'MFL Code' => $facility->facilitycode,
				'Facility' => $facility->name,
				'Male 400 and less' => $data->where('sex', 1)->where('rcategory', 1)->first()->totals ?? null,
				'Female 400 and less' => $data->where('sex', 2)->where('rcategory', 1)->first()->totals ?? null,
				'Male Above 400 Less 1000' => $data->where('sex', 1)->where('rcategory', 2)->first()->totals ?? null,
				'Female Above 400 Less 1000' => $data->where('sex', 2)->where('rcategory', 2)->first()->totals ?? null,
				'Male Above 1000' => ($data->where('sex', 1)->where('rcategory', 3)->first()->totals ?? null) + ($data->where('sex', 1)->where('rcategory', 4)->first()->totals ?? null),
				'Female Above 1000' => ($data->where('sex', 2)->where('rcategory', 3)->first()->totals ?? null) + ($data->where('sex', 2)->where('rcategory', 4)->first()->totals ?? null),
			];

		}
		$file = "gender_totals_all_sites_between_{$start_date}_and_{$end_date}_by_most_recent_test";
		
		Excel::create($file, function($excel) use($rows){
			$excel->sheet('Sheetname', function($sheet) use($rows) {
				$sheet->fromArray($rows);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		Mail::to(['joelkith@gmail.com', 'kmugambi@clintonhealthaccess.org', 'tngugi@clintonhealthaccess.org'], 'Gender Totals All Facilities')->send(new TestMail($data));
	}


	public static function save_gender_ordering_results()
	{
		ini_set("memory_limit", "-1");

		$start_date = Carbon::now()->subYear();
		$days = $start_date->day;

		$start_date = $start_date->subDays($days-1)->toDateString();
		$end_date = Carbon::now()->subDays($days)->toDateString();
		$date_params = [$start_date, $end_date];

        config(['excel.import.heading' => true]);
		$path = public_path('art_ordering.csv');

		$facilities = Excel::load($path, function($reader){})->get();

		// $facilities = \App\Facility::whereRaw("id IN (SELECT DISTINCT facility_id FROM viralsamples_view where datetested between '{$start_date}' AND '{$end_date}' )")->get();

		foreach ($facilities as $key => $fac) {
			$facility = \App\Facility::where(['facilitycode' => $fac->mfl_code])->first();
			if(!$facility) continue;

			$sql = self::get_current_gender_query($facility->id, $date_params);
			$data = collect(DB::select($sql));

			$rows[] = [
				'No' => ($key+1),
				'MFL Code' => $facility->facilitycode,
				'Facility' => $facility->name,
				'Male 400 and less' => $data->where('sex', 1)->where('rcategory', 1)->first()->totals ?? null,
				'Female 400 and less' => $data->where('sex', 2)->where('rcategory', 1)->first()->totals ?? null,
				'Male Above 400 Less 1000' => $data->where('sex', 1)->where('rcategory', 2)->first()->totals ?? null,
				'Female Above 400 Less 1000' => $data->where('sex', 2)->where('rcategory', 2)->first()->totals ?? null,
				'Male Above 1000' => ($data->where('sex', 1)->where('rcategory', 3)->first()->totals ?? null) + ($data->where('sex', 1)->where('rcategory', 4)->first()->totals ?? null),
				'Female Above 1000' => ($data->where('sex', 2)->where('rcategory', 3)->first()->totals ?? null) + ($data->where('sex', 2)->where('rcategory', 4)->first()->totals ?? null),
			];

		}
		$file = "gender_totals_ordering_sites_between_{$start_date}_and_{$end_date}_by_most_recent_test";
		
		Excel::create($file, function($excel) use($rows){
			$excel->sheet('Sheetname', function($sheet) use($rows) {
				$sheet->fromArray($rows);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		Mail::to(['joelkith@gmail.com', 'kmugambi@clintonhealthaccess.org', 'tngugi@clintonhealthaccess.org'], 'Gender Totals Ordering Facilities')->send(new TestMail($data));
	}

	/*
	public static function save_gender_results()
	{
		ini_set("memory_limit", "-1");
        config(['excel.import.heading' => true]);
		$path = public_path('facilities.csv');
		$data = Excel::load($path, function($reader){})->get();

		$rows = [];

		$start_date = Carbon::now()->subYear();
		$days = $start_date->day;

		$start_date = $start_date->subDays($days-1)->toDateString();
		$end_date = Carbon::now()->subDays($days)->toDateString();
		$date_params = [$start_date, $end_date];

		foreach ($data as $key => $row) {

			$facility = \App\Facility::where(['facilitycode' => $row->mfl_code])->first();
			if(!$facility) continue;

			$sql = self::get_current_gender_query(1, $facility->id, $date_params);
			$one = collect(DB::select($sql));

			$sql = self::get_current_gender_query(2, $facility->id, $date_params);
			$two = collect(DB::select($sql));

			$sql = self::get_current_gender_query(4, $facility->id, $date_params);
			$four = collect(DB::select($sql));

			$rows[] = [
				'MFL Code' => $facility->facilitycode,
				'Facility' => $facility->name,
				'Male 400 and less' => $one->where('sex', 1)->first()->totals ?? null,
				'Female 400 and less' => $one->where('sex', 2)->first()->totals ?? null,
				'Male Above 400 Less 1000' => $two->where('sex', 1)->first()->totals ?? null,
				'Female Above 400 Less 1000' => $two->where('sex', 2)->first()->totals ?? null,
				'Male Above 1000' => $four->where('sex', 1)->first()->totals ?? null,
				'Female Above 1000' => $four->where('sex', 2)->first()->totals ?? null,
			];

		}
		$file = "gender_totals_ordering_sites_between_{$start_date}_and_{$end_date}_by_most_recent_test";
		
		Excel::create($file, function($excel) use($rows){
			$excel->sheet('Sheetname', function($sheet) use($rows) {
				$sheet->fromArray($rows);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		Mail::to(['joelkith@gmail.com', 'kmugambi@clintonhealthaccess.org'])->send(new TestMail($data));
	}*/

}
