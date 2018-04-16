<?php

namespace App;

use GuzzleHttp\Client;

use App\SampleView;
use App\Sample;
use App\Batch;
use App\Patient;
use App\Mother;

use App\ViralsampleView;
use App\Viralsample;
use App\Viralbatch;
use App\Viralpatient;


class Synch
{
	public static $base = 'http://127.0.0.1:9000/api/';

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

	public static function synch_eid_batches()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');

		while (true) {
			$batches = Batch::with(['sample.patient:id,national_patient_id'])->where('synched', 0)->limit(10)->get();
			if($batches->isEmpty()) break;

			$response = $client->request('post', 'synch/batches', [
				'headers' => [
					'Accept' => 'application/json',
				],
				'form_params' => [
					'batches' => $batches->toJson(),
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->batches as $key => $value) {
				$update_data = ['national_batch_id' => $value->national_batch_id, 'synched' => 1, 'datesynched' => $today,];
				Batch::where('id', $value->original_id)->update($update_data);
			}

			foreach ($body->samples as $key => $value) {
				$update_data = ['national_sample_id' => $value->national_sample_id, 'synched' => 1, 'datesynched' => $today,];
				Sample::where('id', $value->original_id)->update($update_data);
			}
		}
	}

}
