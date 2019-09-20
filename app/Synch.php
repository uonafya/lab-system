<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
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
use App\FacilityChange;

use App\Mail\AllocationReview;

class Synch
{
	// public static $base = 'http://eiddash.nascop.org/api/';
	public static $base = 'http://lab-2.test.nascop.org/api/';
	private static $allocationReactionCounts, $users, $lab, $from, $to;

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
				'grand_child_class' => AllocationDetailsBreakdown::class,
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

	public static function synch_time()
	{
		$client = new Client(['base_uri' => self::$base]);
		try {
			$response = $client->request('get', 'time', ['timeout' => 1]);
			$body = json_decode($response->getBody());
			exec("date +%Y%m%d -s '" . $body->date . "'");
			exec("date +%T -s '" . $body->time . "'");			
		} catch (Exception $e) {
			
		}
	}

	public static function test_nascop()
	{
		$base = 'https://api.nascop.org/eid/ver2.0';
		$client = new Client(['base_uri' => $base]);
		$response = $client->request('get', '', ['timeout' => 1]);
		$body = json_decode($response->getBody());
		if($response->getStatusCode() < 399) return true;
		return false;
	}

	public static function clean_emails()
	{
		$base = 'https://api.mailgun.net/v3/nascop.or.ke/complaints';
		$client = new Client(['base_uri' => $base]);
		$response = $client->request('get', '', [
			'auth' => ['api', env('MAIL_API_KEY')],
		]);
		$body = json_decode($response->getBody());
		if($response->getStatusCode() > 399) return false;

		$emails = [];

		foreach ($body->items as $key => $value) {
			$emails[] = $value->address;
		}

		\App\Facility::whereIn('email', $emails)->update(['email' => null]);
		\App\Facility::whereIn('ContactEmail', $emails)->update(['ContactEmail' => null]);

		\App\FacilityContact::whereIn('email', $emails)->update(['email' => null]);
		\App\FacilityContact::whereIn('ContactEmail', $emails)->update(['ContactEmail' => null]);
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
		if($status_code > 399)  die();
		$body = json_decode($response->getBody());
		// dd($body);
		Cache::store('file')->put('api_token', $body->token, 60);

		// dd($body);
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

			// dd($body);

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
				echo 'Status code is ' . $response->getStatusCode();
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
			if (isset($value['child_class'])) {
				$update_child_class = $value['child_class'];
				if (isset($value['grand_child_class']))
					$update_grand_child_class = $value['grand_child_class'];
			}
			$column = self::$column_array[$key];

			$sheet = $sample = $eid_patient = $allocate = false;
			if($key == 'worksheets') $sheet = true;
			if($key == 'samples') $sample = true;
			if($key == 'patients' && $type == 'eid') $eid_patient = true;
			if($key == 'allocations') $allocate = true;
			// dd($column);
			while(true){
				$models = $update_class::where('synched', 2)
										->when($sample, function($query){
							                return $query->with(['batch', 'patient']);
										})->when($allocate, function($query){
											return $query->with(array('details' => function($childquery){
												return $childquery->where('synched', 2);
											}, 'details.breakdowns' => function($childquery){
												return $childquery->where('synched', 2);
											}));
										})->when($sheet, function($query){
							                return $query->where('status_id', 3);
										})->limit(20)->get();
				
				if($models->isEmpty()) break;
				if($key == 'batches'){
					foreach ($models as $batch) {
						$my->save_tat($sampleview_class, $sample_class, $batch->id);
					}
				}
				// dd($value['update_url']);
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
				// dd($body);
				$original_column = 'original_id';
				if ($type == 'allocations')
					$original_column = 'original_allocation_id';
				// dd($body);
				foreach ($body->$key as $row) {
					$update_data = [$column => $row->$column, 'synched' => 1, 'datesynched' => $today,];
					$update_class::where('id', $row->$original_column)->update($update_data);
					if ($type == 'allocations') {
						foreach ($row->details as $key => $new) {
							$update_child_data = ['national_id' => $new->$column, 'synched' => 1, 'datesynched' => $today];
							$update_child_class::where('id', $new->original_allocation_detail_id)->update($update_child_data);
							foreach($new->breakdowns as $new_breakdown) {
								$update_data = ['national_id' => $new_breakdown->$column, 'synched' => 1, 'datesynched' => $today];
								$update_grand_child_class::where('id', $new_breakdown->original_allocation_details_breakdown_id)->update($update_data);
							}
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
								foreach($new->breakdowns as $breakdown) {
									$update_data = ['synched' => 1, 'datesynched' => $today];
									$update_grand_child_class::where('id', $breakdown->id)->update($update_data);
								}
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
			$allocations = Allocation::with(['details', 'details.breakdowns'])->where('synched', 0)->limit(20)->get();
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
				$update_data = ['national_id' => $value->id, 'synched' => 1, 'datesynched' => $today];
				$allocationUpdate = Allocation::find($value->original_allocation_id);
				if (isset($allocationUpdate)){
					$allocationUpdate->update($update_data);
					foreach ($value->details as $key => $detailvalue) {
						$detail_update_data = ['national_id' => $detailvalue->id, 'synched' => 1, 'datesynched' => $today];
						$allocationDetailUpdate = AllocationDetail::find($detailvalue->original_allocation_detail_id);
						if (isset($allocationDetailUpdate)) {
							$allocationDetailUpdate->update($detail_update_data);
							foreach ($detailvalue->breakdown as $key => $breakdownvalue) {
								$breakdown_update_data = ['national_id' => $breakdownvalue->id, 'synched' => 1, 'datesynched' => $today];
								$allocationDetailBreakdownUpdate = AllocationDetailsBreakdown::find($breakdownvalue->original_allocation_details_breakdown_id);
								if (isset($allocationDetailBreakdownUpdate))
									$allocationDetailBreakdownUpdate->update($breakdown_update_data);
							}
						}
					}
				}
			}
		}
		return 'All Allocations Synched';
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
		// $lab_id = NULL;
		// if(!isset($lab_id)) 
		$lab_id = env('APP_LAB', null);
		
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
								->first()->totals;

		$data['monthtodate'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereYear('datetested', date('Y'))
								->whereMonth('datetested', date('m'))
								->where(['flag' => 1, 'repeatt' => 0, 'lab_id' => $lab_id])
								->first()->totals;

		$data['receivedsamples'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datereceived', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id])
								->first()->totals;

		$data['enteredsamplesatlab'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id, 'site_entry' => 0])
								->first()->totals;

		$data['enteredsamplesatsite'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id, 'site_entry' => 1])
								->first()->totals;

		$data['enteredreceivedsameday'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where('datereceived', $today)
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id])
								->first()->totals;

		$data['enterednotreceivedsameday'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereDate('created_at', $today)
								->where('datereceived', '!=', date('Y-m-d'))
								->where(['flag' => 1, 'parentid' => 0, 'lab_id' => $lab_id])
								->first()->totals;

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
								->first()->totals;

		$mindate = $sampleview_class::selectRaw('MIN(datereceived) as mindate')
								->where('datereceived', '>', '2017-09-31')
								->whereNull('worksheet_id')
								->whereNull('approvedby')
								->whereNull('datedispatched')
								->whereIn('receivedstatus', [1, 3])
								->whereRaw("(result is null or result=0)")
								->where(['flag' => 1, 'input_complete' => 1, 'lab_id' => $lab_id])
								->when(($type == 'vl'), function($query){
									return $query->where('sampletype', '>', 0);
								})
								->first()->mindate;

		$data['oldestinqueuesample'] = \App\Common::get_days($mindate, $today);

		$data['inprocesssamples'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 1)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;

		$data['abbottinprocess'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 1)
						->where('machine_type', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;

		$data['rocheinprocess'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 1)
						->where('machine_type', 1)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;

		$data['panthainprocess'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 1)
						->where('machine_type', 4)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;

		// Check error in Tim's code
		$data['processedsamples'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->where('datetested', $today)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;

		$data['abbottprocessed'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->where('machine_type', 2)
						->where('datetested', $today)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;

		$data['rocheprocessed'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->where('machine_type', 1)
						->where('datetested', $today)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;

		$data['panthaprocessed'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->where('machine_type', 4)
						->where('datetested', $today)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;

		$data['updatedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datemodified', $today)
								->where(['flag' => 1, 'lab_id' => $lab_id])
								->first()->totals;

		$data['approvedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('dateapproved', $today)
								->where(['flag' => 1, 'lab_id' => $lab_id])
								->first()->totals;


		$data['pendingapproval'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->when(true, self::join_worksheets($type))
						->where('status_id', 2)
						->whereNull('approvedby')
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => $lab_id])
						->first()->totals;


		$data['dispatchedresults'] = $sampleview_class::selectRaw('count(id) as totals')
								->where('datedispatched', $today)
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0])
								->first()->totals;


		$data['oneweek'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0, 'receivedstatus' => 1])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 1 AND 7")
								->first()->totals;

		$data['twoweeks'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0, 'receivedstatus' => 1])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 8 AND 14")
								->first()->totals;

		$data['threeweeks'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0, 'receivedstatus' => 1])
								->whereRaw("DATEDIFF(NOW(), datereceived) BETWEEN 15 AND 28")
								->first()->totals;

		$data['aboveamonth'] = $sampleview_class::selectRaw('count(id) as totals')
								->whereNull('datedispatched')
								->whereYear('datereceived', date('Y'))
								->where(['flag' => 1, 'lab_id' => $lab_id, 'repeatt' => 0, 'receivedstatus' => 1])
								->whereRaw("DATEDIFF(NOW(), datereceived) > 28")
								->first()->totals;

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
			"VL\nSamples Received - {$vl['numsamplesreceived']}\nTotal Tests Done - {$vl['tested']}\nTaqman Tests - {$vl['roche_tested']}\nAbbott Tests - {$vl['abbott_tested']}\nC8800 Tests - {$vl['c8800_tested']}\nPanther Tests - {$vl['pantha_tested']}\nIn Process Samples - {$vl['inprocess']}\nWaiting (Testing) Samples - {$vl['pendingresults']}\nResults Dispatched - {$vl['dispatched']}\nLAB TAT => {$vl['tat']}\nOldest Sample In Queue - {$vl['oldestinqueuesample']}";

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
						->first()->totals;

		$data['abbott_tested'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->join($worksheets_table, "{$view_table}.worksheet_id", '=', "{$worksheets_table}.id")
						->where('machine_type', 2)
						->where('site_entry', '!=', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => env('APP_LAB', null)])
						->whereBetween('datetested', [$weekstartdate, $today])
						->first()->totals;

		$data['c8800_tested'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->join($worksheets_table, "{$view_table}.worksheet_id", '=', "{$worksheets_table}.id")
						->where('machine_type', 3)
						->where('site_entry', '!=', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => env('APP_LAB', null)])
						->whereBetween('datetested', [$weekstartdate, $today])
						->first()->totals;

		$data['pantha_tested'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->join($worksheets_table, "{$view_table}.worksheet_id", '=', "{$worksheets_table}.id")
						->where('machine_type', 4)
						->where('site_entry', '!=', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => env('APP_LAB', null)])
						->whereBetween('datetested', [$weekstartdate, $today])
						->first()->totals;

		$data['tested'] = $data['roche_tested'] + $data['abbott_tested'] + $data['pantha_tested'];

		$data['inprocess'] = $sampleview_class::selectRaw("count({$view_table}.id) as totals")
						->join($worksheets_table, "{$view_table}.worksheet_id", '=', "{$worksheets_table}.id")
						->where('status_id', 1)
						->where('site_entry', '!=', 2)
						->where(["{$view_table}.flag" => 1, "{$view_table}.lab_id" => env('APP_LAB', null)])
						->first()->totals;


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
								->first()->mindate;

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
    	$totaleidsamplesrun = SampleView::selectRaw("count(*) as samples_run")
    								->join('worksheets', 'worksheets.id', '=', 'samples_view.worksheet_id')
    								->where('site_entry', '!=', 2)
    								->where(['samples_view.lab_id' => env('APP_LAB'), 'receivedstatus' => 1])
    								->where('worksheets.status_id', '<', 3)->first()->samples_run;
    	$totalvlsamplesrun = ViralsampleView::selectRaw("count(*) as samples_run")
    								->join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples_view.worksheet_id')
    								->where('site_entry', '!=', 2)
    								->where(['viralsamples_view.lab_id' => env('APP_LAB'), 'receivedstatus' => 1])
    								->where('viralworksheets.status_id', '<', 3)->first()->samples_run;

    	/**** Samples pending results ****/
    	$pendingeidsamples = SampleView::selectRaw("count(*) as pending_samples")
							    	->whereNull('worksheet_id')
    								->whereNull('approvedby')->whereRaw("YEAR(datereceived) > 2015")
    								->whereRaw("((result IS NULL ) OR (result = 0 ))")
    								->where('site_entry', '!=', 2)
    								->where(['lab_id' => env('APP_LAB'), 'repeatt' => 0, 'receivedstatus' => 1, 'input_complete' => 1])
    								->where('flag', '=', 1)->first()->pending_samples;
    	$pendingvlsamples = ViralsampleView::selectRaw("count(*) as pending_samples")
							    	->whereNull('worksheet_id')
    								->whereNull('approvedby')->whereRaw("YEAR(datereceived) > 2015")
    								->whereRaw("((result IS NULL ) OR (result ='0' ) OR (result !='Collect New Sample') )")
    								->where('sampletype', '>', 0)
    								->where('site_entry', '!=', 2)
    								->where(['lab_id' => env('APP_LAB'), 'repeatt' => 0, 'receivedstatus' => 1, 'input_complete' => 1])
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

    public static function synch_facilities()
    {
        ini_set('memory_limit', '-1');
        $client = new Client(['base_uri' => self::$base]);
        $today = date('Y-m-d');

        $max_temp = FacilityChange::selectRaw("max(temp_facility_id) as maximum ")->first()->maximum ?? 1000000;

        while (true) {
            $facilities = Facility::where('synched', 0)->limit(30)->get();
            if($facilities->isEmpty()) break;

            $response = $client->request('post', 'facility', [
                'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . self::get_token(),
                ],
                'json' => [
                        'facilities' => $facilities->toJson(),
                        'lab_id' => env('APP_LAB', null),
                ],
            ]);

            $body = json_decode($response->getBody());

            $update_data = ['synched' => 1,];

            foreach ($body->facilities as $key => $value) {
                Facility::where('id', $value->old_facility_id)->update($update_data);
                if($value->new_facility_id != $value->old_facility_id){

                    $f = new FacilityChange;
                    $f->old_facility_id = $value->old_facility_id;
                    $f->new_facility_id = $value->new_facility_id;
                    $f->temp_facility_id = $max_temp++;
                    $f->save();

                    \App\Common::change_facility_id($value->old_facility_id, $f->temp_facility_id, true);
                }
            }
        }

        $changes = FacilityChange::where(['implemented' => 0])->get();

        foreach ($changes as $f) {
            \App\Common::change_facility_id($f->temp_facility_id, $f->new_facility_id, true);
            $f->implemented = 1;
            $f->save();
        }
    }

    public static function synch_updates_facilities()
    {
        ini_set('memory_limit', '-1');
        $client = new Client(['base_uri' => self::$base]);
        $today = date('Y-m-d');

        $facilities = Facility::where(['synched' => 1])->get();

        foreach ($facilities as $facility) {

            $response = $client->request('get', "facility/{$facility->id}", [
            	'http_errors' => false,
                'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . self::get_token(),
                ],
            ]);

            if($response->getStatusCode() > 399){
            	echo "Facility {$facility->id} not found.\n";
            	continue;
            }
            
            $body = json_decode($response->getBody());

            $facility->district = $body->facility->district;
            $facility->partner = $body->facility->partner;
            $facility->save();
        }
    }

    public static function sendAllocationReview($allocationReactionCounts = null)
    {
    	$users = new User;
		$users = $users->notifiedAllocation()->orWhere('user_type_id', 0)->get();
		// dd($users);
		$lab = Lab::find(env('APP_LAB'));
		$date = date('Y-m-d');
		$fromAllocationDate = date('d M, Y', strtotime($date));
		$toAllocationDate = date('d M, Y', strtotime($date. ' + 14 days'));
		self::$allocationReactionCounts = $allocationReactionCounts;
		self::$users = $users;
		self::$lab = $lab;
		self::$from = $fromAllocationDate;
		self::$to = $toAllocationDate;
		// return self::$allocationReactionCounts;
		self::sendAllocationReviewSms();
		self::sendAllocationReviewEmail();
		foreach ($users as $key => $user) {
			$user->allocation_notification_date = date('Y-m-d H:i:s');
			$user->save();
		}
		return true;
    }


	private static function sendAllocationReviewSms()
	{
		$message = "";
		foreach (self::$users as $user) {
			if (null !== $user->telephone) {
				if (self::$allocationReactionCounts->approved > 0)
					$message .= "{self::$lab->labname}, {self::$allocationReactionCounts->approved} of your {self::$allocationReactionCounts->month} {self::$allocationReactionCounts->year} allocation have been approved. The commodities will be the delivered between {self::$from} and {self::$to} by KEMSA.\n\n";
				if (self::$allocationReactionCounts->rejected > 0)
					$message .= "{self::$lab->labname}, {self::$allocationReactionCounts->rejected} of your {self::$allocationReactionCounts->month} {self::$allocationReactionCounts->year} allocation have been rejected. Kindly log into the system under the ‘Kits’ link to view the comments for your review then re-submit the allocation as soon as possible.";
				$client = new Client(['base_uri' => \App\Common::$sms_url]);

				$response = $client->request('post', '', [
					'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
					'http_errors' => false,
					'json' => [
						'sender' => env('SMS_SENDER_ID'),
						'recipient' => $user->telephone,
						'message' => $message,
					],
				]);
				$body = json_decode($response->getBody());
			}
		}
	}

	private static function sendAllocationReviewEmail()
	{
		if ($allocationReactionCounts->approved > 0)
			Mail::to(self::$users->pluck('email')->toArray())->send(new AllocationReview(self::$allocationReactionCounts, self::$lab, self::$from, self::$to, true, false));
			// Mail::to(['bakasajoshua09@gmail.com'])->send(new AllocationReview($allocationReactionCounts, $lab, $from, $to, true, false));
		if ($allocationReactionCounts->rejected > 0)
			Mail::to(self::$users->pluck('email')->toArray())->send(new AllocationReview(self::$allocationReactionCounts, self::$lab, self::$from, self::$to, false, true));

	}
}