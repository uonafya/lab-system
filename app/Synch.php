<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use DB;

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
	// public static $base = 'http://lab-nat.test/api/';

	public static $synch_arrays = [
		'eid' => [
			'misc_class' => \App\Misc::class,
			'sample_class' => Sample::class,
			'sampleview_class' => \App\SampleView::class,
			'batch_class' => Batch::class,
			'worksheet_class' => Worksheet::class,
			'patient_class' => Patient::class,
			'view_table' => 'samples_view',
			'worksheets_table' => 'worksheets',
			'with_array' => ['batch.creator', 'patient.mother'],
		],

		'vl' => [
			'misc_class' => \App\MiscViral::class,
			'sample_class' => Viralsample::class,
			'sampleview_class' => \App\ViralsampleView::class,
			'batch_class' => Viralbatch::class,
			'worksheet_class' => Viralworksheet::class,
			'patient_class' => Viralpatient::class,
			'view_table' => 'viralsamples_view',
			'worksheets_table' => 'viralworksheets',
			'with_array' => ['batch.creator', 'patient'],
		],
	];

	public static $update_arrays = [
		'eid' => [
			'worksheets' => [
				'class' => Worksheet::class,
				'update_url' => 'update/worksheets',
				'delete_url' => 'delete/worksheets',
			],
			'mothers' => [
				'class' => Mother::class,
				'update_url' => 'update/mothers',
				'delete_url' => 'delete/mothers',
			],
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

		'allocations' => [
			'allocations' => [
				'class' => Allocation::class,
				'child_class' => AllocationDetail::class,
				'update_url' => 'update/allocations',
				'delete_url' => 'delete/allocations',
			]
		]
	];

	public static $column_array = [
		'worksheets' => 'national_worksheet_id',
		'mothers' => 'national_mother_id',
		'patients' => 'national_patient_id',
		'batches' => 'national_batch_id',
		'samples' => 'national_sample_id',
		'allocations' => 'national_id'
	];

	public static function test_connection()
	{
		$client = new Client(['base_uri' => self::$base]);
		$response = $client->request('get', 'hello');
		$body = json_decode($response->getBody());
		return $body->message;
	}

	public static function login()
	{
		Cache::store('file')->forget('api_token');
		$client = new Client(['base_uri' => self::$base]);

		$response = $client->request('post', 'auth/login', [
            'http_errors' => false,
			'headers' => [
				'Accept' => 'application/json',
			],
			'json' => [
				'email' => env('MASTER_USERNAME', null),
				'password' => env('MASTER_PASSWORD', null),
			],
		]);
		$status_code = $response->getStatusCode();
		if($status_code > 399) die();
		$body = json_decode($response->getBody());
		Cache::store('file')->put('api_token', $body->token, 60);
	}

	public static function get_token()
	{
		if(Cache::store('file')->has('api_token')){}
		else{
			self::login();
		}
		return Cache::store('file')->get('api_token');
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
					'Authorization' => 'Bearer ' . self::get_token(),
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
					'Authorization' => 'Bearer ' . self::get_token(),
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
        ini_set("memory_limit", "-1");
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
			$batches = $batch_class::with(['sample.patient:id,national_patient_id,patient'])
			->where('synched', 0)->where('batch_complete', 1)->limit(20)->get();
			// dd($batches);
			if($batches->isEmpty()) break;

			$response = $client->request('post', $url, [
				'http_errors' => false,
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'batches' => $batches->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			if($response->getStatusCode() > 399)
			{
				dd($body);
			}

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


	public static function synch_batches_odd($type)
	{
        ini_set("memory_limit", "-1");
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

		$batch_ids = $sampleview_class::selectRaw("distinct batch_id")->where(['synched' => 0, 'batch_complete' => 1])->where('batch_id', 'like', "%.5%")->get()->pluck('batch_id');
		$offset = 0;

		while (true) {
			$batches = $batch_class::whereIn('id', $batch_ids)->limit(20)->offset($offset)->get();
			$offset+=20;
			// dd($batches);
			if($batches->isEmpty()) break;

			foreach ($batches as $batch) {
				foreach ($batch->sample as $sample) {
					$p = $sample->patient;
				}
			}

			$response = $client->request('post', $url, [
				'http_errors' => false,
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'batches' => $batches->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());

			if($response->getStatusCode() > 399)
			{
				dd($body);
			}

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
					'Authorization' => 'Bearer ' . self::get_token(),
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

		if ($type != 'allocations') {
			$c = self::$synch_arrays[$type];

			$misc_class = $c['misc_class'];
			$sample_class = $c['sample_class'];
			$sampleview_class = $c['sampleview_class'];

			$my = new $misc_class;
			$my->save_tat($sampleview_class, $sample_class);
		}
		
		$updates = self::$update_arrays[$type];
		
		foreach ($updates as $key => $value) {
			$update_class = $value['class'];
			if (isset($value['child_class']))
				$update_child_class = $value['child_class'];
			$column = self::$column_array[$key];

			$sheet = $sample = $eid_patient = false;
			if($key == 'worksheets') $sheet = true;
			if($key == 'samples') $sample = true;
			if($key == 'patients' && $type == 'eid') $eid_patient = true;
			if($key == 'allocations') $allocate = true;

			while(true){
				$models = $update_class::where('synched', 2)
										->when($sample, function($query){
							                return $query->with(['batch', 'patient']);
										})->when($allocate, function($query){
											return $query->with(['details']);
										})->when($sheet, function($query){
							                return $query->where('status_id', 3);
										})->limit(20)->get();
				if($models->isEmpty()) break;
				
				if($key == 'batches'){
					foreach ($models as $batch) {
						$my->save_tat($sampleview_class, $sample_class, $batch->id);
					}
				}
				$response = $client->request('post', $value['update_url'], [
					'headers' => [
						'Accept' => 'application/json',
						'Authorization' => 'Bearer ' . self::get_token(),
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
					if ($type == 'allocations') {
						foreach ($row->details as $key => $new) {
								$update_child_data = ['national_id' => $row->$column, 'synched' => 1, 'datesynched' => $today];
								$update_child_class::where('id', $new->original_id)->update($update_child_data);
						}
					}
				}

				if($body->errors_array){
					foreach ($body->errors_array as $row) {
						$update_data = ['synched' => 1, 'datesynched' => $today,];
						$update_class::where('id', $row->id)->update($update_data);
						if ($type == 'allocations') {
							foreach ($row->details as $key => $new) {
									$update_child_data = ['synched' => 1, 'datesynched' => $today];
									$update_child_class::where('id', $new->id)->update($update_child_data);
							}
						}
					}
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
						'Authorization' => 'Bearer ' . self::get_token(),
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

	public static function synch_allocations() {
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		$url = 'insert/allocations';

		while (true) {
			$allocations = Allocation::with(['details'])->where('synched', 0)->limit(20)->get();
			if($allocations->isEmpty())
				break;
			
			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'allocations' => $allocations->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);
			
			$body = json_decode($response->getBody());
			
			foreach ($body->allocations as $key => $value) {
				$update_data = ['national_id' => $value->national_id, 'synched' => 1, 'datesynched' => $today];
				Allocation::where('id', $value->original_id)->update($update_data);
				foreach ($value->details as $key => $detailvalue) {
					$detail_update_data = ['national_id' => $detailvalue->national_id, 'synched' => 1, 'datesynched' => $today];
					AllocationDetail::where('id', $detailvalue->original_id)->update($detail_update_data);
				}
			}
		}	
	}

	public static function synch_allocations_updates() {
		return self::synch_updates('allocations');
	}

	public static function synch_consumptions() {
		echo "==> Starting consumptions synch";
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		$url = 'insert/consumptions';

		while (true) {
			echo "\n\t Getting consumptions data 20\n";
			$consumptions = Consumption::where('synched', 0)->limit(20)->get();
			if($consumptions->isEmpty())
				break;
			echo "\t Pushing consumptions data to national DB\n";
			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'consumptions' => $consumptions->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);
			echo "\t Receiving national db respose\n";
			$body = json_decode($response->getBody());
			echo "\t Updating consumptions data\n";
			foreach ($body->consumptions as $key => $value) {
				$update_data = ['national_id' => $value->national_id, 'synched' => 1, 'datesynched' => $today];
				Consumption::where('id', $value->original_id)->update($update_data);
			}
		}
		echo "==> Completed consumptions synch\n";
	}

	public static function synch_deliveries(){
		echo "==> Starting deliveries synch";
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		$uri = 'insert/deliveries';

		while (true) {
			echo "\n\t Getting deliveries data 20\n";
			$deliveries = Deliveries::where('synched', 0)->limit(20)->get();
			if($deliveries->isEmpty())
				break;

			echo "\t Pushing deliveries data to national DB";
			$response = $client->request('post', $uri, [
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'deliveries' => $deliveries->toJson(),
					'lab_id' => env('APP_LAB', null),
				],
			]);
			echo "\t Receiving national db response\n";
			$body =json_decode($response->getBody());
			foreach ($body->deliveries as $key => $value) {
				$update_data = ['national_id' => $value->national_id, 'synched' => 1, 'datesynched' => $today];
				Deliveries::where('id', $value->original_id)->update($update_data);
			}
		}
		echo "==> Completed deliveries synch\n";
	}

	public static function labactivity($type)
	{
		if(!$lab_id) $lab_id = env('APP_LAB', null);
		
		$classes = self::$synch_arrays[$type];
		$sample_class = $classes['sample_class'];
		$sampleview_class = $classes['sampleview_class'];

		$samples_table = 'samples';
		$view_table = 'samples_view';
		$data['testtype'] = 1;
		if($type == 'vl'){
			$samples_table = 'viralsamples';
			$view_table = 'viralsamples_view';
			$data['testtype'] = 2;
		}

		$today = date('Y-m-d');
		$data['yeartodate'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereYear('datetested', date('Y'))
								->where(['flag' => 1, 'repeatt' => 0, 'lab_id' => $lab_id])
								->get()->first()->totals;

		$data['monthtodate'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereYear('datetested', date('Y'))
								->whereMonth('datetested', date('m'))
								->where(['flag' => 1, 'repeatt' => 0, 'lab_id' => $lab_id])
								->get()->first()->totals;

		$data['receivedsamples'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datereceived', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id])
								->get()->first()->totals;

		$data['enteredsamplesatlab'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id, 'site_entry' => 0])
								->get()->first()->totals;

		$data['enteredsamplesatsite'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id, 'site_entry' => 1])
								->get()->first()->totals;

		$data['enteredreceivedsameday'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where('datereceived', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id])
								->get()->first()->totals;

		$data['enterednotreceivedsameday'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where('datereceived', '!=', date('Y-m-d'))
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id])
								->get()->first()->totals;

		$data['inqueuesamples'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datereceived', '>', '2017-09-31')
								->whereNull('worksheet_id')
								->whereNull('approvedby')
								->whereNotIn('receivedstatus', [0, 2])
								->whereRaw("(result is null or result=0)")
								->where(['flag' => 1, 'input_complete' => 1, 'lab_id' => $lab_id])
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
								->where(['flag' => 1, 'input_complete' => 1, 'lab_id' => $lab_id])
								->when(($type == 'vl'), function($query){
									return $query->where('sampletype', '>', 0);
								})
								->get()->first()->mindate;

		$data['oldestinqueuesample'] = \App\Common::get_days($mindate, $today);

		$data['inprocesssamples'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 1)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;

		$data['abbottinprocess'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 1)
						->where('machine_type', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;

		$data['rocheinprocess'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 1)
						->where('machine_type', 1)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;

		$data['panthainprocess'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 1)
						->where('machine_type', 4)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;

		// Check error in Tim's code
		$data['processedsamples'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->where('datetested', $today)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;

		$data['abbottprocessed'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->where('machine_type', 2)
						->where('datetested', $today)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;

		$data['rocheprocessed'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->where('machine_type', 1)
						->where('datetested', $today)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;

		$data['panthaprocessed'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->where('machine_type', 4)
						->where('datetested', $today)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;

		$data['updatedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datemodified', $today)
								->where(['flag' => 1, 'lab_id' => $lab_id])
								->get()->first()->totals;

		$data['approvedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('dateapproved', $today)
								->where(['flag' => 1, 'lab_id' => $lab_id])
								->get()->first()->totals;


		$data['pendingapproval'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->whereNull('approvedby')
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->get()->first()->totals;


		$data['dispatchedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datedispatched', $today)
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0])
								->get()->first()->totals;


		$data['oneweek'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0, 'receivedstatus' => 1])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 1 AND 7")
								->get()->first()->totals;

		$data['twoweeks'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0, 'receivedstatus' => 1])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 8 AND 14")
								->get()->first()->totals;

		$data['threeweeks'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0, 'receivedstatus' => 1])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 15 AND 28")
								->get()->first()->totals;

		$data['aboveamonth'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0, 'receivedstatus' => 1])
								->whereRaw("DATEDIFF(NOW(), datereceived) > 28")
								->get()->first()->totals;

		$client = new Client(['base_uri' => self::$base]);

		$response = $client->request('post', 'lablogs', [
            'http_errors' => false,
			'headers' => [
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . self::get_token(),
			],
			'json' => [
				'data' => $data,
				'lab_id' => $lab_id,
			],
		]);
	}

	public static function join_worksheets($type)
	{
		if($type == 'eid'){
	    	return function($query){
	    		return $query->join('worksheets', 'samples_view.worksheet_id', '=', 'worksheets.id');;
	    	};
		}
		else{
	    	return function($query){
	    		return $query->join('viralworksheets', 'viralsamples_view.worksheet_id', '=', 'viralworksheets.id');;
	    	};			
		}
	}

	public static function send_weekly_activity()
	{
		$eid = self::weeklylabactivity('eid');
		$vl = self::weeklylabactivity('vl');

		$users = DB::table('musers')->where('weeklyalert', 1)->get();

		foreach ($users as $user) {

			$message = 
			" Hi {$user->name}\nWEEKLY EID/VL REPORT - {$eid['weekstartdisplay']} - {$eid['currentdaydisplay']}\n{$eid['smsfoot']}\nEID\nSamples Received - {$eid['numsamplesreceived']}\nTotal Tests Done - {$eid['tested']}\nTaqman Tests - {$eid['roche_tested']}\nAbbott Tests - {$eid['abbott_tested']}\nIn Process Samples - {$eid['inprocess']}\nWaiting (Testing) Samples - {$eid['pendingresults']}\nResults Dispatched - {$eid['dispatched']}\nLAB TAT => {$eid['tat']}\nOldest Sample In Queue - {$eid['oldestinqueuesample']}\n";
			$message .=
			"VL\nSamples Received - {$vl['numsamplesreceived']}\nTotal Tests Done - {$vl['tested']}\nTaqman Tests - {$vl['roche_tested']}\nAbbott Tests - {$vl['abbott_tested']}\nPanther Tests - {$vl['pantha_tested']}\nIn Process Samples - {$vl['inprocess']}\nWaiting (Testing) Samples - {$vl['pendingresults']}\nResults Dispatched - {$vl['dispatched']}\nLAB TAT => {$vl['tat']}\nOldest Sample In Queue - {$vl['oldestinqueuesample']}";

	        $client = new Client(['base_uri' => \App\Common::$sms_url]);

			$response = $client->request('post', '', [
				'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
				// 'debug' => true,
				'http_errors' => false,
				'json' => [
					'sender' => env('SMS_SENDER_ID'),
					// 'recipient' => '254702266217',
					'recipient' => $user->mobile,
					'message' => $message,
				],
			]);
			$body = json_decode($response->getBody());
		}
	}

	public static function weeklylabactivity($type)
	{
		ini_set('memory_limit', '-1');
		$classes = self::$synch_arrays[$type];
		$sample_class = $classes['sample_class'];
		$sampleview_class = $classes['sampleview_class'];
		$view_table = $classes['view_table'];
		$worksheets_table = $classes['worksheets_table'];

		$data['smsfoot'] = \App\Lab::find(env('APP_LAB'))->labname ?? '';
		$data['testtype'] = 1;

		if($type == 'vl') $data['testtype'] = 2;

		$today = date("Y-m-d");
		$weekstartdate= date ( "Y-m-d", strtotime ('-4 days') );

		$currentdaydisplay =date('d-M-Y');
		$weekstartdisplay =date("d-M-Y",strtotime($weekstartdate));

		$data['currentdaydisplay'] = $currentdaydisplay;
		$data['weekstartdisplay'] = $weekstartdisplay;

		$minimum_date= date ( "Y-m-d", strtotime ('-1 year') );

		$data['numsamplesreceived'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereBetween('datereceived', [$weekstartdate, $today])
								->where('site_entry', '!=', 2)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => env('APP_LAB', null)])
								->first()->totals;

		$data['roche_tested'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->join($worksheets_table, "{$view_table}.worksheet_id", '=', "{$worksheets_table}.id")
						->where('machine_type', 1)
						->where('site_entry', '!=', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => env('APP_LAB', null)])
						->whereBetween('datetested', [$weekstartdate, $today])
						->get()->first()->totals;

		$data['abbott_tested'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->join($worksheets_table, "{$view_table}.worksheet_id", '=', "{$worksheets_table}.id")
						->where('machine_type', 2)
						->where('site_entry', '!=', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => env('APP_LAB', null)])
						->whereBetween('datetested', [$weekstartdate, $today])
						->get()->first()->totals;

		$data['pantha_tested'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->join($worksheets_table, "{$view_table}.worksheet_id", '=', "{$worksheets_table}.id")
						->where('machine_type', 4)
						->where('site_entry', '!=', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => env('APP_LAB', null)])
						->whereBetween('datetested', [$weekstartdate, $today])
						->get()->first()->totals;

		$data['tested'] = $data['roche_tested'] + $data['abbott_tested'] + $data['pantha_tested'];

		$data['inprocess'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->join($worksheets_table, "{$view_table}.worksheet_id", '=', "{$worksheets_table}.id")
						->where('status_id', 1)
						->where('site_entry', '!=', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => env('APP_LAB', null)])
						->get()->first()->totals;


		$samples = $sampleview_class::select('datereceived', 'datedispatched')
						->where('site_entry', '!=', 2)
						->where('batch_complete', 1)
						->where('repeatt', 0)
						->whereBetween('datetested', [$weekstartdate, $today])
						->get();

		$sample_count = $samples->count();

		$tat = 0;

		foreach ($samples as $sample) {
			$tat += \App\Common::get_days($sample->datereceived, $sample->datedispatched);
		}
		$data['tat'] = round(@($tat / $sample_count), 1);

		$data['dispatched'] = $sample_count;

		$data['pendingresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('site_entry', '!=', 2)
								->whereNull('worksheet_id')
								->whereNull('datedispatched')
								->whereRaw("(result is null or result=0)")
								->where(['receivedstatus' => 1, 'flag' => 1, 'input_complete' => 1, 'lab_id' => env('APP_LAB', null)])
								->first()->totals;

		$mindate = $sampleview_class::selectRaw('MIN(datereceived) as mindate')
								->where('datereceived', '>', $minimum_date)
								->whereNull('worksheet_id')
								->whereNull('approvedby')
								->whereNull('datedispatched')
								// ->where('receivedstatus', '!=', 2)
								->where('site_entry', '!=', 2)
								->whereRaw("(result is null or result=0)")
								->where(['receivedstatus' => 1, 'flag' => 1, 'input_complete' => 1, 'lab_id' => env('APP_LAB', null)])
								->get()->first()->mindate;

		$data['oldestinqueuesample'] = \App\Common::get_days($mindate, $today);
		return $data;
	}

	public static function send_weekly_backlog()
	{
		$currentdaydisplay =date('d-M-Y');
		$lab = \App\Lab::where('id', '=', env('APP_LAB'))->first()->labname;
		$logs = self::get_backlogs();
    	
    	$users = DB::table('musers')->where('weeklyalert', 1)->get();

		foreach ($users as $user) {

			$message = "Hi ".$user->name."\n"." BACK LOG ALERT AS OF ".$currentdaydisplay." " . $lab."\n". " EID "."\n"." Samples Logged in NOT in Worksheet : ". $logs->pendingeidsamples."\n"." Samples In Process : ".$logs->totaleidsamplesrun."\n"." VL "."\n". " Samples Logged in and NOT in Worksheet :".$logs->pendingvlsamples."\n"." Samples In Process:".$logs->totalvlsamplesrun;

	        $client = new Client(['base_uri' => \App\Common::$sms_url]);

			$response = $client->request('post', '', [
				'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
				// 'debug' => true,
				'http_errors' => false,
				'json' => [
					'sender' => env('SMS_SENDER_ID'),
					// 'recipient' => '254702266217',
					'recipient' => $user->mobile,
					'message' => $message,
				],
			]);
			$body = json_decode($response->getBody());
			// print_r($body);
			// break;
		}
	}

	public static function get_backlogs(){    	
    	/**** Total samples run ****/
    	$totaleidsamplesrun = Sample::selectRaw("count(*) as samples_run")
    								->join('worksheets', 'worksheets.id', '=', 'samples.worksheet_id')
    								->where('repeatt', '=', 0)
    								->where('worksheets.status_id', '<', 3)->first()->samples_run;
    	$totalvlsamplesrun = Viralsample::selectRaw("count(*) as samples_run")
    								->join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples.worksheet_id')
    								->where('repeatt', '=', 0)
    								->where('viralworksheets.status_id', '<', 3)->first()->samples_run;

    	/**** Samples pending results ****/
    	$pendingeidsamples = SampleView::selectRaw("count(*) as pending_samples")->whereNull('worksheet_id')
    								->whereNull('approvedby')->whereRaw("YEAR(datereceived) > 2015")
    								->whereRaw("((result IS NULL ) OR (result = 0 ))")->where('input_complete', '=', 1)
    								->where('site_entry', '!=', 2)
    								->where(['lab_id' => env('APP_LAB'), 'repeatt' => 0, 'receivedstatus' => 1])
    								->where('flag', '=', 1)->first()->pending_samples;
    	$pendingvlsamples = ViralsampleView::selectRaw("count(*) as pending_samples")->whereNull('worksheet_id')
    								->whereNull('approvedby')->whereRaw("YEAR(datereceived) > 2015")
    								->whereRaw("((result IS NULL ) OR (result =0 ) OR (result !='Collect New Sample') )")
    								->where('input_complete', '=', 1)->where('sampletype', '>', 0)
    								->where('site_entry', '!=', 2)
    								->where(['lab_id' => env('APP_LAB'), 'repeatt' => 0, 'receivedstatus' => 1])
    								->where('flag', '=', 1)->first()->pending_samples;

    	return (object)[
    					'totaleidsamplesrun' => $totaleidsamplesrun,
						'totalvlsamplesrun' => $totalvlsamplesrun,
						'pendingeidsamples' => $pendingeidsamples,
						'pendingvlsamples' => $pendingvlsamples
					];
	}





	public static function transfer_sample($type, $to_lab, $sample_ids)
	{
    	$sample_model = self::$synch_arrays[$type]['sample_class'];
    	$with_array = self::$synch_arrays[$type]['with_array'];

    	if(!$sample_ids){
    		session(['toast_message' => 'Please select the samples to transfer.', 'toast_error' => 1]);
    		return;
    	}

    	$samples = $sample_model::whereIn('id', $sample_ids)->with($with_array)->get();

		$client = new Client(['base_uri' => self::$base]);

		$response = $client->request('post', 'transfer', [
			'headers' => [
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . self::get_token(),
			],
			'json' => [
				'samples' => $samples->toJson(),
				'lab_id' => env('APP_LAB', null),
				'to_lab' => $to_lab,
				'type' => $type,
			],
		]);

		$body = json_decode($response->getBody());

		$status_code = $response->getStatusCode();

		if($status_code < 400){
			$ok = $body->ok ?? null;

			if($ok) $sample_model::whereIn('id', $ok)->delete();
			session(['toast_message' => 'The transfer has been made.']);
		}
		else{
			session(['toast_message' => "An error has occured. Status code {$status_code}.", 'toast_error' => 1]);
		}
		return;
		// print_r($body);
	}






	public static function match_eid_patients()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		$done = 0;
		$offset=0;

		while (true) {
			$patients = Patient::with(['mother'])
				->where('synched', '>', 0)
				->whereNull('national_patient_id')
				->limit(200)
				// ->offset($offset)
				->get();
			if($patients->isEmpty()) break;

			$response = $client->request('post', 'synch/patients', [
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'patients' => $patients->toJson(),
					'lab_id' => env('APP_LAB', null),
				],
			]);

			$body = json_decode($response->getBody());
			$i=0;

			foreach ($body->patients as $key => $value) {
				// $update_data = get_object_vars($value);
				$update_data['national_patient_id'] = $value->id;
				$update_data['synched'] = 1;
				$update_data['datesynched'] = $today;
				// unset($update_data['id']);
				// unset($update_data['original_patient_id']);

				// $new_update_data['national_patient_id'] = $value->id;
				// $new_update_data['dob'] = $value->dob;
				// $new_update_data['sex'] = $value->sex;

				$update_data = [
					'national_patient_id' => $value->id,
					'synched' => 1,
					'datesynched' => $today,
				];
				$i++;

				Patient::where('id', $value->original_patient_id)->update($update_data);
			}

			$offset += (200 - ($i+1));

			foreach ($body->mothers as $key => $value) {
				// $update_data = get_object_vars($value);
				$update_data = [];
				$update_data['national_mother_id'] = $value->id;
				$update_data['synched'] = 1;
				$update_data['datesynched'] = $today;
				// unset($update_data['id']);
				// unset($update_data['original_mother_id']);

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
		$offset=0;

		while (true) {
			$patients = Viralpatient::where('synched', '>', 0)
				->whereNull('national_patient_id')
				->limit(200)
				// ->offset($offset)
				->get();
			if($patients->isEmpty()) break;

			$response = $client->request('post', 'synch/viralpatients', [
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'patients' => $patients->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);

			$body = json_decode($response->getBody());
			$i=0;

			foreach ($body->patients as $key => $value) {
				// $update_data = get_object_vars($value);
				$update_data['national_patient_id'] = $value->id;
				$update_data['synched'] = 1;
				$update_data['datesynched'] = $today;
				// unset($update_data['id']);
				// unset($update_data['original_patient_id']);
				$i++;

				Viralpatient::where('id', $value->original_patient_id)->update($update_data);
			}

			$offset += (200 - ($i+1));

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
		$offset=0;

		while (true) {
			$batches = $batch_class::with(['sample:id'])
				->where('synched', '>', 0)
				->whereNull('national_batch_id')
				->limit(200)
				->offset($offset)
				->get();
			if($batches->isEmpty()) break;

			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'batches' => $batches->toJson(),
					'lab_id' => env('APP_LAB', null),
				],

			]);
			$i=0;

			$body = json_decode($response->getBody());

			foreach ($body->batches as $key => $value) {
				$update_data = ['national_batch_id' => $value->national_batch_id, 'synched' => 1, 'datesynched' => $today,];
				$batch_class::where('id', $value->original_id)->update($update_data);
				$i++;
			}

			foreach ($body->samples as $key => $value) {
				$update_data = ['national_sample_id' => $value->national_sample_id, 'synched' => 1, 'datesynched' => $today,];
				// if($batch_class == "App\\Viralbatch"){
				// 	$update_data['age_category'] = $value->age_category;
				// 	$update_data['justification'] = $value->justification;
				// 	$update_data['prophylaxis'] = $value->prophylaxis;
				// }
				$sample_class::where('id', $value->original_id)->update($update_data);
			}
			
			$offset += (200 - ($i+1));

			$done+=200;
			echo "Matched {$done} {$type} batch records at " . date('d/m/Y h:i:s a', time()). "\n";
		}
	}

	public static function match_samples($type)
	{
		$classes = self::$synch_arrays[$type];

		$misc_class = $classes['misc_class'];
		$sample_class = $classes['sample_class'];
		$sampleview_class = $classes['sampleview_class'];
		$batch_class = $classes['batch_class'];

		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		// $my = new $misc_class;
		// $my->save_tat($sampleview_class, $sample_class);

		if($batch_class == "App\\Batch"){
			$url = 'synch/samples';
		}else{
			$url = 'synch/viralsamples';
		}
		$done=0;
		$offset=0;

		while (true) {
			$samples = $sample_class::with(['batch:id,national_batch_id,lab_id'])
				->where('synched', '>', 0)
				->whereNull('national_sample_id')
				->limit(200)
				->offset($offset)
				->get();
			if($samples->isEmpty()) break;

			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . self::get_token(),
				],
				'json' => [
					'samples' => $samples->toJson(),
					'lab_id' => env('APP_LAB', null),
				],
			]);
			$i=0;

			$body = json_decode($response->getBody());

			foreach ($body->samples as $key => $value) {
				$update_data = ['national_sample_id' => $value->national_sample_id, 'synched' => 1];
				$i++;
				$sample_class::where('id', $value->original_id)->update($update_data);
			}
			
			$offset += (200 - ($i+1));

			$done+=200;
			echo "Matched {$done} {$type} sample records at " . date('d/m/Y h:i:s a', time()). "\n";
		}
	}


	// No longer necessary
	// Facilities will be created nationally then synched to all labs
	public static function synch_facilities()
	{
		ini_set('memory_limit', '-1');
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
				$update_data = ['id' => $value->id, 'synched' => 1,];
				Facility::where('id', $value->original_id)->update($update_data);
				if($value->id != $value->original_id){
					self::change_facility_id(Batch::class, $value->original_id, $value->id);
					self::change_facility_id(Viralbatch::class, $value->original_id, $value->id);
					self::change_facility_id(Patient::class, $value->original_id, $value->id);
					self::change_facility_id(Viralpatient::class, $value->original_id, $value->id);
				}
			}
		}
	}

	public static function change_facility_id($class_name, $old_id, $new_id)
	{
		$models = $class_name::where('facility_id', $old_id)->get();
		foreach ($models as $m) {
			$m->facility_id = $new_id;
			$m->pre_update();
		}
	}

}
