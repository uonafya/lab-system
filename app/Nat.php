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

	public static function email_csv($filename, $data, $emails=['joelkith@gmail.com'])
	{		
		Excel::create($filename, function($excel) use($rows){
			$excel->sheet('Sheetname', function($sheet) use($rows) {
				$sheet->fromArray($rows);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $filename . ".csv")];

		Mail::to($emails)->send(new TestMail($data));
	}

	public static function get_county_current_query($year, $paed=false, $suppressed=true)
	{
    	$sql = 'SELECT f.county_id, sex, count(*) as totals ';
		$sql .= 'FROM ';
		$sql .= '(SELECT v.id, v.facility_id, v.sex, v.rcategory ';
		$sql .= 'FROM viralsamples_view v ';
		$sql .= 'RIGHT JOIN ';
		$sql .= '(SELECT ID, patient_id, max(datetested) as maxdate ';
		$sql .= 'FROM viralsamples_view ';
		$sql .= "WHERE ( datetested between '{$year}-01-01' and '{$year}-12-31' ) ";
		$sql .= "AND patient != '' AND patient != 'null' AND patient is not null ";
		if($paed) $sql .= "AND age < 15 ";
		else{
			$sql .= "AND age > 14 ";
		}
		$sql .= 'AND flag=1 AND repeatt=0 AND rcategory in (1, 2, 3, 4) ';
		$sql .= 'AND justification != 10 and facility_id != 7148 ';
		$sql .= 'GROUP BY patient_id) gv ';
		$sql .= 'ON v.id=gv.id) tb ';
		$sql .= 'JOIN view_facilitys f on f.id=tb.facility_id ';
		if($suppressed) $sql .= 'WHERE rcategory IN (1,2) ';
		else{
			$sql .= 'WHERE rcategory IN (3,4) ';
		}
		$sql .= 'GROUP BY f.county_id, sex ';
		$sql .= 'ORDER BY f.county_id, sex ';

		// return $sql;
		return collect(DB::select($sql));
	}

	public static function get_county_current()
	{
		$data = [];

		$counties = DB::table('countys')->get();

		for ($year=2013; $year < 2020; $year++) { 
			$paeds_sup = self::get_county_current_query($year, true);
			$paeds = self::get_county_current_query($year, true, false);
			$adults_sup = self::get_county_current_query($year, false);
			$adults = self::get_county_current_query($year, false, false);
			foreach ($counties as $county) {
				$row = ['Year' => $year, 'County' => $county->name];

				$row['Paedeatric Suppressed Male'] = $paeds_sup->where('county_id', $county->id)->where('sex', 1)->first()->totals ?? 0;
				$row['Paedeatric Suppressed Female'] = $paeds_sup->where('county_id', $county->id)->where('sex', 2)->first()->totals ?? 0;

				$row['Paedeatric Non-Suppressed Male'] = $paeds->where('county_id', $county->id)->where('sex', 1)->first()->totals ?? 0;
				$row['Paedeatric Non-Suppressed Female'] = $paeds->where('county_id', $county->id)->where('sex', 2)->first()->totals ?? 0;

				$row['Adult Suppressed Male'] = $adults_sup->where('county_id', $county->id)->where('sex', 1)->first()->totals ?? 0;
				$row['Adult Suppressed Female'] = $adults_sup->where('county_id', $county->id)->where('sex', 2)->first()->totals ?? 0;

				$row['Adult Non-Suppressed Male'] = $adults->where('county_id', $county->id)->where('sex', 1)->first()->totals ?? 0;
				$row['Adult Non-Suppressed Female'] = $adults->where('county_id', $county->id)->where('sex', 2)->first()->totals ?? 0;

				$data[] = $row;
			}
		}
		self::email_csv('county_suppression', $data);
	}

	public static function get_current_gender_query($facility_id, $date_params=null)
	{
    	$sql = 'SELECT sex, rcategory, count(*) as totals ';
		$sql .= 'FROM ';
		$sql .= '(SELECT v.id, v.facility_id, v.sex, v.rcategory ';
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

		Mail::to(['joelkith@gmail.com', 'kmugambi@clintonhealthaccess.org', 'tngugi@clintonhealthaccess.org'])->send(new TestMail($data, 'Gender Totals All Facilities'));
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

		Mail::to(['joelkith@gmail.com', 'kmugambi@clintonhealthaccess.org', 'tngugi@clintonhealthaccess.org'])->send(new TestMail($data, 'Gender Totals Ordering Facilities'));
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


	public static function dtg_llv()
	{
		$data = [];

		$sql = "SELECT v.id, v.patient_id, v.datetested, v.result
				FROM viralsamples_view v
				RIGHT JOIN 
				(
					SELECT id, patient_id, max(datetested) as maxdate
					FROM viralsamples_view
					WHERE datetested > '2018-01-01'
					AND patient != '' AND patient != 'null' AND patient is not null
					AND flag=1 AND repeatt=0 AND result > 400
					AND justification != 10 and facility_id != 7148
					AND prophylaxis=1
					GROUP BY patient_id
				) gv
				ON v.id=gv.id
		";

		$rows = DB::select($sql);

		foreach ($rows as $key => $row) {

			$sql = "SELECT v.id, v.patient_id, v.patient, v.facility_id, v.datetested, v.result, f.name, f.facilitycode, f.subcounty, f.county
					FROM viralsamples_view v
					RIGHT JOIN 
					(
						SELECT id, patient_id, max(datetested) as maxdate
						FROM viralsamples_view
						WHERE datetested < '{$row->datetested}'
						AND patient_id = {$row->patient_id}
						AND patient != '' AND patient != 'null' AND patient is not null
						AND flag=1 AND repeatt=0 AND result > 400
						AND justification != 10 and facility_id != 7148
						AND prophylaxis=1
						GROUP BY patient_id
					) gv
					ON v.id=gv.id
					LEFT JOIN view_facilitys f ON f.id=v.facility_id
			";

			$p = DB::select($sql);

			if(!$p) continue;
			$p = $p[0];
			if($p->result <= 400) continue;

			$data[] = [
				'CCC Number' => $p->patient,
				'Facility' => $p->name,
				'Facility MFL Code' => $p->facilitycode,
				'Subcounty' => $p->subcounty,
				'County' => $p->county,
				'Most Recent Result' => $row->result,
				'Most Recent Test Date' => $row->datetested,
				'Second Most Recent Result' => $p->result,
				'Second Most Recent Test Date' => $p->datetested,
			];
		}

		$file = "dtg_llv";
		
		Excel::create($file, function($excel) use($data){
			$excel->sheet('Sheetname', function($sheet) use($data) {
				$sheet->fromArray($data);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		Mail::to(['joelkith@gmail.com', 'kmugambi@clintonhealthaccess.org', 'tngugi@clintonhealthaccess.org'])->send(new TestMail($data, 'Gender Totals Ordering Facilities'));
	}

	public static function dtg_llv_two()
	{
		ini_set('memory_limit', '-1');
		$data = [];

		$sql = "SELECT v.id, v.patient_id, v.datetested, v.result
				FROM viralsamples_view v
				RIGHT JOIN 
				(
					SELECT id, patient_id, max(datetested) as maxdate
					FROM viralsamples_view
					WHERE datetested > '2018-07-01'
					AND patient != '' AND patient != 'null' AND patient is not null
					AND flag=1 AND repeatt=0 AND result > 400
					AND justification != 10 and facility_id != 7148
					AND prophylaxis=1
					GROUP BY patient_id
				) gv
				ON v.id=gv.id
		";

		$rows = DB::select($sql);

		foreach ($rows as $key => $row) {

			$sql = "SELECT v.id, v.patient_id, v.patient, v.facility_id, v.datetested, v.result, r.name AS `regimen`, f.name, f.facilitycode, f.subcounty, f.county
					FROM viralsamples_view v
					LEFT JOIN view_facilitys f ON f.id=v.facility_id
					LEFT JOIN viralregimen r ON r.id=v.prophylaxis
					WHERE patient_id = {$row->patient_id} AND datetested > '2018-07-01' and repeatt=0 and rcategory IN (1,2,3,4)
					ORDER BY datetested asc
			";

			$results = DB::select($sql);

			foreach ($results as $key2 => $p) {
				$data[] = [
					'Patient' => $key+1,
					'Order' => $key2+1,
					'CCC Number' => $p->patient,
					'Facility' => $p->name,
					'Result' => $p->result,
					'Date Tested' => $p->datetested,
					'Regimen' => $p->regimen,
					'Facility MFL Code' => $p->facilitycode,
					'Subcounty' => $p->subcounty,
					'County' => $p->county,
				];
			}
		}

		$file = "dtg_llv";
		
		Excel::create($file, function($excel) use($data){
			$excel->sheet('Sheetname', function($sheet) use($data) {
				$sheet->fromArray($data);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		Mail::to(['joelkith@gmail.com', 'kmugambi@clintonhealthaccess.org', 'tngugi@clintonhealthaccess.org'])->send(new TestMail($data, 'Gender Totals Ordering Facilities'));


	}

	public static function kids_data()
	{
		$sql =  "SELECT patient AS `CCC Number`, f.facilitycode AS `MFL Code`, f.NAME AS `Facility`, 
			s.initiation_date AS `Date Started on Treatment`, r.name AS `Regimen`, s.dob, s.age, 
			s.result, s.datecollected, s.datetested, s.datedispatched
			FROM viralsamples_view s
			LEFT JOIN facilitys f ON f.id=s.facility_id
			LEFT JOIN viralregimen r ON r.id=s.regimen
			WHERE s.age < 15 AND s.initiation_date IS NOT NULL AND repeatt=0 AND datetested BETWEEN '2018-06-01' AND '2019-05-30'";


		ini_set('memory_limit', '-1');
		$data = [];
		$rows = DB::select($sql);

		foreach ($rows as $key => $row) {
			// $data[] = $row->toArray();
			$data[] = get_object_vars($row);
		}

		$file = "2018-06-01_2019-05-30_children_below_15_with_date_started_on_treatment";
		
		Excel::create($file, function($excel) use($data){
			$excel->sheet('Sheetname', function($sheet) use($data) {
				$sheet->fromArray($data);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		Mail::to(['joelkith@gmail.com'])->send(new TestMail($data));
	} 

	public static function gt_k()
	{
		ini_set('memory_limit', '-1');
		$data = [];

		$sql = "SELECT v.patient_id, v.patient AS `ccc_number`, f.name as `facility`, f.subcounty, f.county, v.patient_name, v.dob AS `date_of_birth`, v.age, g.gender, v.initiation_date AS `date_enrolled`, v.dateinitiatedonregimen AS `start_regimen_date`, vr.name AS `current_regimen`, v.result AS `VL>1000`
				FROM viralsamples_view v
				RIGHT JOIN 
				(
					SELECT id, patient_id, max(datetested) as maxdate
					FROM viralsamples_view
					WHERE datetested BETWEEN '2016-07-01' AND '2019-06-30'
					AND patient != '' AND patient != 'null' AND patient is not null
					AND flag=1 AND repeatt=0 AND result > 1000
					AND justification != 10 and facility_id != 7148
					GROUP BY patient_id
				) gv
				ON v.id=gv.id
				LEFT JOIN gender g on g.id=v.sex
				LEFT JOIN viralregimen vr on vr.id=v.prophylaxis
				LEFT JOIN view_facilitys f on f.id=v.facility_id
		";

		$rows = DB::select($sql);

		foreach ($rows as $key => $row) {
			$sql2 = "SELECT * FROM viralsamples_view WHERE patient_id={$row->patient_id} AND repeatt=0 AND rcategory IN (1,2,3,4) ORDER BY datetested DESC LIMIT 3";

			$samples = DB::select($sql2);

			foreach ($samples as $s) {
				if($s->rcategory < 3) continue 2;
			}

			// $data[] = $row->toArray();
			$data[] = get_object_vars($row);
		}

		$file = "three_years_nonsuppressed_three_consecutive";
		
		Excel::create($file, function($excel) use($data){
			$excel->sheet('Sheetname', function($sheet) use($data) {
				$sheet->fromArray($data);
			});
		})->store('csv');

		$data = [storage_path("exports/" . $file . ".csv")];

		Mail::to(['joelkith@gmail.com'])->send(new TestMail($data));

	}

}
