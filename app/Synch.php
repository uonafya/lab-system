<?php

namespace App;

use GuzzleHttp\Client;

use App\Sample;
use App\Batch;
use App\Patient;
use App\Mother;
use App\Worksheet;

use App\Viralsample;
use App\Viralbatch;
use App\Viralpatient;
use App\Viralworksheet;

use App\Facility;

class Synch
{
	// public static $base = 'http://127.0.0.1:9000/api/';
	// public static $base = 'http://eid-dash.nascop.org/api/';
	public static $base = 'http://lab-2.test.nascop.org/api/';

	public static $synch_arrays = [
		'eid' => [
			'misc_class' => \App\Misc::class,
			'sample_class' => Sample::class,
			'sampleview_class' => \App\SampleView::class,
			'batch_class' => Batch::class,
			'worksheet_class' => Worksheet::class,
		],

		'vl' => [
			'misc_class' => \App\MiscViral::class,
			'sample_class' => Viralsample::class,
			'sampleview_class' => \App\ViralsampleView::class,
			'batch_class' => Viralbatch::class,
			'worksheet_class' => Viralworksheet::class,
		],
	];

	public static $update_arrays = [
		'eid' => [
			'worksheets' => [
				'class' => Worksheet::class,
				'update_url' => 'update/worksheets',
				'delete_url' => 'delete/worksheets',
			],
			// 'mothers' => [
			// 	'class' => Mother::class,
			// 	'update_url' => 'update/mothers',
			// 	'delete_url' => 'delete/mothers',
			// ],
			'patients' => [
				'class' => Patient::class,
				'update_url' => 'update/patients',
				'delete_url' => 'delete/patients',
			],
			'batches' => [
				'class' => Batch::class,
				'update_url' => 'update/batches',
				'delete_url' => 'delete/batches',
			],
			'samples' => [
				'class' => Sample::class,
				'update_url' => 'update/samples',
				'delete_url' => 'delete/samples',
			],
		],

		'vl' => [
			'worksheets' => [
				'class' => Viralworksheet::class,
				'update_url' => 'update/viralworksheets',
				'delete_url' => 'delete/viralworksheets',
			],
			'patients' => [
				'class' => Viralpatient::class,
				'update_url' => 'update/viralpatients',
				'delete_url' => 'delete/viralpatients',
			],
			'batches' => [
				'class' => Viralbatch::class,
				'update_url' => 'update/viralbatches',
				'delete_url' => 'delete/viralbatches',
			],
			'samples' => [
				'class' => Viralsample::class,
				'update_url' => 'update/viralsamples',
				'delete_url' => 'delete/viralsamples',
			],
		],
	];

	public static $column_array = [
		'worksheets' => 'national_worksheet_id',
		'mothers' => 'national_mother_id',
		'patients' => 'national_patient_id',
		'batches' => 'national_batch_id',
		'samples' => 'national_sample_id',
	];

	public static function test_connection()
	{
		$client = new Client(['base_uri' => self::$base]);
		$response = $client->request('get', 'hello');
		$body = json_decode($response->getBody());
		return $body->message;
	}

	public static function synch_eid_patients()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		while (true) {
			$patients = Patient::with(['mother'])->where('synched', 0)->limit(20)->get();
			if($patients->isEmpty()) break;

			$response = $client->request('post', 'insert/patients', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'patients' => $patients->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->patients as $key => $value) {
				$update_data = ['national_patient_id' => $value->national_patient_id, 'synched' => 1, 'datesynched' => $today,];
				Patient::where('id', $value->original_id)->update($update_data);
			}

			foreach ($body->mothers as $key => $value) {
				$update_data = ['national_mother_id' => $value->national_mother_id, 'synched' => 1, 'datesynched' => $today,];
				Mother::where('id', $value->original_id)->update($update_data);
			}
		}
	}

	public static function synch_vl_patients()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		while (true) {
			$patients = Viralpatient::where('synched', 0)->limit(30)->get();
			if($patients->isEmpty()) break;

			$response = $client->request('post', 'insert/viralpatients', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'patients' => $patients->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->patients as $key => $value) {
				$update_data = ['national_patient_id' => $value->national_patient_id, 'synched' => 1, 'datesynched' => $today,];
				Viralpatient::where('id', $value->original_id)->update($update_data);
			}
		}
	}

	public static function synch_batches($type)
	{
		$classes = self::$synch_arrays[$type];

		$misc_class = $classes['misc_class'];
		$sample_class = $classes['sample_class'];
		$sampleview_class = $classes['sampleview_class'];
		$batch_class = $classes['batch_class'];

		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		$my = new $misc_class;
		$my->save_tat($sampleview_class, $sample_class);

		if($batch_class == "App\\Batch"){
			$url = 'insert/batches';
		}else{
			$url = 'insert/viralbatches';
		}

		while (true) {
			$batches = $batch_class::with(['sample.patient:id,national_patient_id'])->where('synched', 0)->limit(10)->get();
			if($batches->isEmpty()) break;

			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'batches' => $batches->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->batches as $key => $value) {
				$update_data = ['national_batch_id' => $value->national_batch_id, 'synched' => 1, 'datesynched' => $today,];
				$batch_class::where('id', $value->original_id)->update($update_data);
			}

			foreach ($body->samples as $key => $value) {
				$update_data = ['national_sample_id' => $value->national_sample_id, 'synched' => 1, 'datesynched' => $today,];
				$sample_class::where('id', $value->original_id)->update($update_data);
			}
		}
	}

	public static function synch_worksheets($type)
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		$worksheet_class = self::$synch_arrays[$type]['worksheet_class'];

		if($worksheet_class == "App\\Worksheet"){
			$url = 'insert/worksheets';
		}else{
			$url = 'insert/viralworksheets';
		}

		while (true) {
			$worksheets = $worksheet_class::where('synched', 0)->where('status_id', 3)->limit(30)->get();
			if($worksheets->isEmpty()) break;

			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'worksheets' => $worksheets->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->worksheets as $key => $value) {
				$update_data = ['national_worksheet_id' => $value->national_worksheet_id, 'synched' => 1, 'datesynched' => $today,];
				$worksheet_class::where('id', $value->original_id)->update($update_data);
			}
		}
	}

	public static function synch_updates($type)
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		$classes = self::$synch_arrays;

		foreach ($classes as $c) {
			$misc_class = $c['misc_class'];
			$sample_class = $c['sample_class'];
			$sampleview_class = $c['sampleview_class'];

			$my = new $misc_class;
			$my->save_tat($sampleview_class, $sample_class);		
		}

		$updates = self::$update_arrays[$type];

		foreach ($updates as $key => $value) {
			$update_class = $value['class'];
			$column = self::$column_array[$key];

			$sheet = false;
			if($key == 'worksheets') $sheet = true;

			while(true){
				$models = $update_class::where('synched', 2)
										->when($sheet, function($query){
							                return $query->where('status_id', 3);
							            })->limit(20)->get();
				if($models->isEmpty()) break;

				$response = $client->request('post', $value['update_url'], [
					'headers' => [
						'Accept' => 'application/json',
					],
					'json' => [
						$key => $models->toJson(),
						'lab_id' => env('APP_LAB', null),
					],

				]);

				$body = json_decode($response->getBody());

				foreach ($body->$key as $row) {
					$update_data = [$column => $row->$column, 'synched' => 1, 'datesynched' => $today,];
					$update_class::where('id', $row->original_id)->update($update_data);
				}
			}			
		}
	}

	public static function synch_deletes($type)
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		$updates = self::$update_arrays[$type];

		foreach ($updates as $key => $value) {
			$update_class = $value['class'];
			$column = self::$column_array[$key];

			$sheet = false;
			if($key == 'worksheets') $sheet = true;

			while(true){
				$models = $update_class::where('synched', 3)
										->when($sheet, function($query){
							                return $query->where('status_id', 3);
							            })->limit(20)->get();
				if($models->isEmpty()) break;

				$response = $client->request('post', $value['delete_url'], [
					'headers' => [
						'Accept' => 'application/json',
					],
					'json' => [
						$key => $models->toJson(),
						'lab_id' => env('APP_LAB', null),
					],

				]);

				$body = json_decode($response->getBody());

				foreach ($body->$key as $row) {
					$update_class::where('id', $row->original_id)->delete();
				}
			}			
		}
	}

	public static function labactivity($type)
	{
		$classes = self::$synch_arrays[$type];
		$sample_class = $classes['sample_class'];
		$sampleview_class = $classes['sampleview_class'];

		$samples_table = 'samples';
		$data['testtype'] = 1;
		if($type == 'vl'){
			$samples_table = 'viralsamples';
			$data['testtype'] = 2;
		}

		$today = date('Y-m-d');
		$data['yeartodate'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereYear('datetested', date('Y'))
								->where(['flag' => 1, 'repeatt' => 0, 'lab_id' => env('APP_LAB', null)])
								->get()->first()->totals;

		$data['monthtodate'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereYear('datetested', date('Y'))
								->whereMonth('datetested', date('m'))
								->where(['flag' => 1, 'repeatt' => 0, 'lab_id' => env('APP_LAB', null)])
								->get()->first()->totals;

		$data['receivedsamples'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datereceived', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => env('APP_LAB', null)])
								->get()->first()->totals;

		$data['enteredsamplesatlab'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => env('APP_LAB', null), 'site_entry' => 0])
								->get()->first()->totals;

		$data['enteredsamplesatsite'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => env('APP_LAB', null), 'site_entry' => 1])
								->get()->first()->totals;

		$data['enteredreceivedsameday'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where('datereceived', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => env('APP_LAB', null)])
								->get()->first()->totals;

		$data['enterednotreceivedsameday'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where('datereceived', '!=', date('Y-m-d'))
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => env('APP_LAB', null)])
								->get()->first()->totals;

		$data['inqueuesamples'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datereceived', '>', '2017-09-31')
								->whereNull('worksheet_id')
								->whereNull('approvedby')
								->whereNotIn('receivedstatus', [0, 2])
								->whereRaw("(result is null or result=0)")
								->where(['flag' => 1, 'inputcomplete' => 1, 'lab_id' => env('APP_LAB', null)])
								->when(($type == 'vl'), function($query){
									return $query->where('sampletype', '>', 0);
								})
								->get()->first()->totals;

		$mindate = $sampleview_class::selectRaw('MIN(datereceived) as mindate')
								->where('datereceived', '>', '2017-09-31')
								->whereNull('worksheet_id')
								->whereNull('approvedby')
								->whereIn('receivedstatus', [1, 3])
								->whereRaw("(result is null or result=0)")
								->where(['flag' => 1, 'inputcomplete' => 1, 'lab_id' => env('APP_LAB', null)])
								->when(($type == 'vl'), function($query){
									return $query->where('sampletype', '>', 0);
								})
								->get()->first()->mindate;

		$data['oldestinqueuesample'] = \App\Common::get_days($mindate, $today);

		$data['inprocesssamples'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 1)
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;

		$data['abbottinprocess'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 1)
						->where('machine_type', 2)
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;

		$data['rocheinprocess'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 1)
						->where('machine_type', 1)
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;

		$data['panthainprocess'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 1)
						->where('machine_type', 4)
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;

		// Check error in Tim's code
		$data['processedsamples'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 2)
						->where('datetested', $today)
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;

		$data['abbottprocessed'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 2)
						->where('machine_type', 2)
						->where('datetested', $today)
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;

		$data['rocheprocessed'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 2)
						->where('machine_type', 1)
						->where('datetested', $today)
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;

		$data['panthaprocessed'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 2)
						->where('machine_type', 4)
						->where('datetested', $today)
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;

		$data['updatedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datemodified', $today)
								->where(['flag' => 1, 'lab_id' => env('APP_LAB', null)])
								->get()->first()->totals;

		$data['approvedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('dateapproved', $today)
								->where(['flag' => 1, 'lab_id' => env('APP_LAB', null)])
								->get()->first()->totals;


		$data['pendingapproval'] = $sample_class::selectRaw("count({$samples_table}.id) as totals")
						->when(true, function($query) use ($type){
							if($type == 'eid') return $query->join('worksheets', 'samples.worksheet_id', '=', 'worksheets.id');
							return $query->join('viralworksheets', 'viralsamples.worksheet_id', '=', 'viralworksheets.id');
						})
						->where('status_id', 2)
						->whereNull('approvedby')
						->where(['{$samples_table}.flag' => 1, '{$samples_table}.lab_id' => env('APP_LAB', null)])
						->get()->first()->totals;


		$data['dispatchedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datedispatched', $today)
								->where(['flag' => 1, 'lab_id' => env('APP_LAB', null), 'repeatt' => 0])
								->get()->first()->totals;


		$data['oneweek'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where('receivedstatus', '!=', 0)
								->where(['flag' => 1, 'lab_id' => env('APP_LAB', null), 'repeatt' => 0])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 1 AND 7")
								->get()->first()->totals;

		$data['twoweeks'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where('receivedstatus', '!=', 0)
								->where(['flag' => 1, 'lab_id' => env('APP_LAB', null), 'repeatt' => 0])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 8 AND 14")
								->get()->first()->totals;

		$data['threeweeks'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where('receivedstatus', '!=', 0)
								->where(['flag' => 1, 'lab_id' => env('APP_LAB', null), 'repeatt' => 0])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 15 AND 28")
								->get()->first()->totals;

		$data['aboveamonth'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where('receivedstatus', '!=', 0)
								->where(['flag' => 1, 'lab_id' => env('APP_LAB', null), 'repeatt' => 0])
								->whereRaw("DATEDIFF(NOW(), datereceived) > 28")
								->get()->first()->totals;

		$client = new Client(['base_uri' => self::$base]);

		$response = $client->request('post', 'lablogs', [
			'headers' => [
				'Accept' => 'application/json',
			],
			'json' => [
				'data' => json_encode($data),
				'lab_id' => env('APP_LAB', null),
			],
		]);

	}


	public static function match_eid_patients()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		$done = 0;

		while (true) {
			$patients = Patient::select('id', 'facility_id', 'patient')
				->with(['mother:id'])
				->where('synched', 1)
				->whereNull('national_patient_id')
				->limit(200)
				->get();
			if($patients->isEmpty()) break;

			$response = $client->request('post', 'synch/patients', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'patients' => $patients->toJson(),
					'lab_id' => env('APP_LAB', null),
				],
			]);

			$body = json_decode($response->getBody());

			foreach ($body->patients as $key => $value) {
				$update_data = get_object_vars($value);
				$update_data['national_patient_id'] = $value->id;
				$update_data['synched'] = 1;
				$update_data['datesynched'] = $today;
				unset($update_data['id']);
				unset($update_data['original_patient_id']);

				Patient::where('id', $value->original_patient_id)->update($update_data);
			}

			foreach ($body->mothers as $key => $value) {
				$update_data = get_object_vars($value);
				$update_data['national_mother_id'] = $value->id;
				$update_data['synched'] = 1;
				$update_data['datesynched'] = $today;
				unset($update_data['id']);
				unset($update_data['original_mother_id']);

				Mother::where('id', $value->original_mother_id)->update($update_data);
			}

			$done+=200;
			echo "Matched {$done} eid patient records at " . date('d/m/Y h:i:s a', time()). "\n";
		}
	}

	public static function match_vl_patients()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		$done=0;

		while (true) {
			$patients = Viralpatient::select('id', 'facility_id', 'patient')
				->where('synched', 1)
				->whereNull('national_patient_id')
				->limit(200)
				->get();
			if($patients->isEmpty()) break;

			$response = $client->request('post', 'synch/viralpatients', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'patients' => $patients->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->patients as $key => $value) {
				$update_data = get_object_vars($value);
				$update_data['national_patient_id'] = $value->id;
				$update_data['synched'] = 1;
				$update_data['datesynched'] = $today;
				unset($update_data['id']);
				unset($update_data['original_patient_id']);

				Viralpatient::where('id', $value->original_patient_id)->update($update_data);
			}

			$done+=200;
			echo "Matched {$done} vl patient records at " . date('d/m/Y h:i:s a', time()). "\n";
		}
	}

	public static function match_batches($type)
	{
		$classes = self::$synch_arrays[$type];

		$misc_class = $classes['misc_class'];
		$sample_class = $classes['sample_class'];
		$sampleview_class = $classes['sampleview_class'];
		$batch_class = $classes['batch_class'];

		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		$my = new $misc_class;
		$my->save_tat($sampleview_class, $sample_class);

		if($batch_class == "App\\Batch"){
			$url = 'synch/batches';
		}else{
			$url = 'synch/viralbatches';
		}
		$done=0;

		while (true) {
			$batches = $batch_class::with(['sample:id'])
				->where('synched', 1)
				->whereNull('national_batch_id')
				->limit(200)
				->get();
			if($batches->isEmpty()) break;

			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'batches' => $batches->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->batches as $key => $value) {
				$update_data = ['national_batch_id' => $value->national_batch_id, 'synched' => 1, 'datesynched' => $today,];
				$batch_class::where('id', $value->original_id)->update($update_data);
			}

			foreach ($body->samples as $key => $value) {
				$update_data = ['national_sample_id' => $value->national_sample_id, 'synched' => 1, 'datesynched' => $today,];
				$sample_class::where('id', $value->original_id)->update($update_data);
			}

			$done+=200;
			echo "Matched {$done} {$type} batch records at " . date('d/m/Y h:i:s a', time()). "\n";
		}
	}


	// No longer necessary
	// Facilities will be created nationally then synched to all labs
	/*public static function synch_facilities()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		while (true) {
			$facilities = Facility::where('synched', 0)->limit(30)->get();
			if($facilities->isEmpty()) break;

			$response = $client->request('post', 'synch/facilities', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'json' => [
					'facilities' => $facilities->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->facilities as $key => $value) {
				$update_data = ['id' => $value->id, 'synched' => 1, 'datesynched' => $today,];
				Facility::where('id', $value->original_id)->update($update_data);
			}
		}
	}*/

}
