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

	public static function synch_eid_patients()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		while (true) {
			$patients = Patient::with(['mother'])->where('synched', 0)->limit(20)->get();
			if($patients->isEmpty()) break;

			$response = $client->request('post', 'synch/patients', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'form_params' => [
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

			$response = $client->request('post', 'synch/viralpatients', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'form_params' => [
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
		$misc_class::save_tat($sampleview_class, $sample_class);

		if($batch_class == "App\\Batch"){
			$url = 'synch/batches';
		}else{
			$url = 'synch/viralbatches';
		}

		while (true) {
			$batches = $batch_class::with(['sample.patient:id,national_patient_id'])->where('synched', 0)->limit(10)->get();
			if($batches->isEmpty()) break;

			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
				],
				'form_params' => [
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
			$url = 'synch/worksheets';
		}else{
			$url = 'synch/viralworksheets';
		}

		while (true) {
			$worksheets = $worksheet_class::where('synched', 0)->where('status_id', 3)->limit(30)->get();
			if($worksheets->isEmpty()) break;

			$response = $client->request('post', $url, [
				'headers' => [
					'Accept' => 'application/json',
				],
				'form_params' => [
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

		$updates = self::$update_arrays[$type];

		foreach ($updates as $key => $value) {
			$update_class = $value['class'];
			$column = self::$column_array[$key];

			while(true){
				$models = $update_class::where('synched', 2)->limit(20)->get();
				if($models->isEmpty()) break;

				$response = $client->request('post', $value['update_url'], [
					'headers' => [
						'Accept' => 'application/json',
					],
					'form_params' => [
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

			while(true){
				$models = $update_class::where('synched', 3)->limit(20)->get();
				if($models->isEmpty()) break;

				$response = $client->request('post', $value['delete_url'], [
					'headers' => [
						'Accept' => 'application/json',
					],
					'form_params' => [
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



	public static function synch_facilities()
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
				'form_params' => [
					'facilities' => $facilities->toJson(),
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






}
