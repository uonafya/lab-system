<?php

namespace App;

use GuzzleHttp\Client;

use App\SampleView;
use App\Sample;
use App\Batch;
use App\Patient;
use App\Mother;
use App\Misc;

use App\ViralsampleView;
use App\Viralsample;
use App\Viralbatch;
use App\Viralpatient;
use App\MiscViral;


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
		$my = new Misc;
		$my->save_tat(SampleView::class, Sample::class);

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
				],

			]);

			$body = json_decode($response->getBody());

			foreach ($body->patients as $key => $value) {
				$update_data = ['national_patient_id' => $value->national_patient_id, 'synched' => 1, 'datesynched' => $today,];
				Viralpatient::where('id', $value->original_id)->update($update_data);
			}
		}
	}

	public static function synch_vl_batches()
	{
		$client = new Client(['base_uri' => self::$base]);
		$today = date('Y-m-d');
		$my = new MiscViral;
		$my->save_tat(ViralsampleView::class, Viralsample::class);

		while (true) {
			$batches = Viralbatch::with(['sample.patient:id,national_patient_id'])->where('synched', 0)->limit(10)->get();
			if($batches->isEmpty()) break;

			$response = $client->request('post', 'synch/viralbatches', [
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
				Viralbatch::where('id', $value->original_id)->update($update_data);
			}

			foreach ($body->samples as $key => $value) {
				$update_data = ['national_sample_id' => $value->national_sample_id, 'synched' => 1, 'datesynched' => $today,];
				Viralsample::where('id', $value->original_id)->update($update_data);
			}
		}
	}


}
