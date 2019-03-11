<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use DB;

use App\Common;
use App\DrSample;
use App\DrSampleView;

use App\DrWorksheetWarning;
use App\DrWarning;

use App\DrCall;
use App\DrCallDrug;

use App\DrGenotype;
use App\DrResidue;

class MiscDr extends Common
{

	public static $hyrax_url = 'https://sanger20181106v2-sanger.hyraxbio.co.za';
	public static $ui_url = 'http://sangelamerkel.exatype.co.za';

    public static $call_array = [
        'LC' => [
            'resistance' => 'Low Coverage',
            'resistance_colour' => "#595959",
            'cells' => [],
        ],
        'R' => [
            'resistance' => 'Resistant',
            'resistance_colour' => "#ff0000",
            'cells' => [],
        ],
        'I' => [
            'resistance' => 'Intermediate Resistance',
            'resistance_colour' => "#ff9900",
            'cells' => [],
        ],
        'S' => [
            'resistance' => 'Susceptible',
            'resistance_colour' => "#00ff00",
            'cells' => [],
        ],
    ];

    public static function dump_log($postData, $encode_it=true)
    {
    	if(!is_dir(storage_path('app/logs/'))) mkdir(storage_path('app/logs/'), 0777);

		if($encode_it) $postData = json_encode($postData);
		
		$file = fopen(storage_path('app/logs/' . 'dr_logs2' .'.txt'), "a");
		if(fwrite($file, $postData) === FALSE) fwrite("Error: no data written");
		fwrite($file, "\r\n");
		fclose($file);
    }

	public static function get_hyrax_key()
	{
		if(Cache::store('file')->has('dr_api_token')){}
		else{
			self::login();
		}
		return Cache::store('file')->get('dr_api_token');
	}

	public static function login()
	{
		Cache::store('file')->forget('dr_api_token');
		$client = new Client(['base_uri' => self::$hyrax_url]);

		$response = $client->request('POST', 'sanger/authorisations', [
            'http_errors' => false,
			'headers' => [
				// 'Accept' => 'application/json',
			],
			'json' => [
				'data' => [
					'type' => 'authorisations',
					'attributes' => [
						'email' => env('DR_USERNAME'),
						'password' => env('DR_PASSWORD'),
					],
				],
			],
		]);

		

		if($response->getStatusCode() < 400)
		{
			$body = json_decode($response->getBody());

			$key = $body->data->attributes->api_key ?? null;

			if(!$key) dd($body);

			Cache::store('file')->put('dr_api_token', $key, 60);

			// echo $key;
			return;
		}
		else{
			$body = json_decode($response->getBody());
			dd($body);
		}
	}


	public static function create_plate($worksheet)
	{
		$client = new Client(['base_uri' => self::$hyrax_url]);

		$files = self::get_worksheet_files($worksheet);

		$sample_data = $files['sample_data'];
		$errors = $files['errors'];

		if($errors){
			session(['toast_error' => 1, 'toast_message' => 'The upload has errors.']);
			return $errors;
		}

		$postData = [
				'data' => [
					'type' => 'plate_create',
					'attributes' => [
						'plate_name' => "{$worksheet->id}",
					],
				],
				'included' => $sample_data,
			];

		// self::dump_log($postData);

		// die();

		$response = $client->request('POST', 'sanger/plate', [
            'http_errors' => false,
            // 'debug' => true,
			'headers' => [
				// 'Accept' => 'application/json',
				// 'x-hyrax-daemon-apikey' => self::get_hyrax_key(),
				'X-Hyrax-Apikey' => self::get_hyrax_key(),
			],
			'json' => $postData,
		]);

		$body = json_decode($response->getBody());

		if($response->getStatusCode() < 400)
		{
			$worksheet->plate_id = $body->data->id;
			$worksheet->time_sent_to_sanger = date('Y-m-d H:i:s');
			$worksheet->status_id = 5;
			$worksheet->save();

			dd($body->data->attributes->samples);

			foreach ($body->data->attributes->samples as $key => $value) {
				$sample = DrSample::find($value->sample_name);
				$sample->exatype_id = $value->id;
				$sample->save();
			}
		}

		echo "\n The status code is " . $response->getStatusCode() . "\n";

		// dd($body);
	}


	public static function get_worksheet_files($worksheet)
	{
		$path = storage_path('app/public/results/dr/' . $worksheet->id . '/');

		$samples = $worksheet->sample;
		// $samples->load(['result']);

		$primers = ['F1', 'F2', 'F3', 'R1', 'R2', 'R3'];

		$sample_data = [];
		$print_data = [];
		$errors = [];

		foreach ($samples as $key => $sample) {

			// if($key == 4) break;

			// if($key != 4) continue;

			$s = [
				'type' => 'sample_create',
				'attributes' => [
					'sample_name' => "{$sample->mid}",
					'pathogen' => 'hiv',
					'assay' => 'thermo_PR_RT',
					// 'assay' => 'cdc-hiv',
					'enforce_recall' => false,
					'sample_type' => 'data',
				],
			];

			if($sample->control == 1) $s['attributes']['sample_type'] = 'negative';
			if($sample->control == 2) $s['attributes']['sample_type'] = 'positive';

			$abs = [];
			$abs2 = [];

			foreach ($primers as $primer) {
				$ab = self::find_ab_file($path, $sample, $primer);
				// if($ab) $abs[] = $ab;
				if($ab){
					$abs[] = $ab;
					// $abs2[] = ['file_name' => $ab['file_name']];
				}
				else{
					$errors[] = "Sample {$sample->id} ({$sample->mid}) Primer {$primer} could not be found.";
				}
			}
			if(!$abs) continue;
			$s['attributes']['ab1s'] = $abs;
			$sample_data[] = $s;

			// $s['attributes']['ab1s'] = $abs2;
			// $print_data[] = $s;
		}
		// self::dump_log($print_data);
		// die();
		return ['sample_data' => $sample_data, 'errors' => $errors];
	}

	public static function find_ab_file($path, $sample, $primer)
	{
		$files = scandir($path);
		if(!$files) return null;

		foreach ($files as $file) {
			if($file == '.' || $file == '..') continue;

			$new_path = $path . '/' . $file;
			if(is_dir($new_path)){
				$a = self::find_ab_file($new_path, $sample, $primer);

				if(!$a) continue;
				return $a;
			}
			else{
				// if(starts_with($file, $sample->mid . $primer)){
				if(starts_with($file, $sample->mid . '-') && str_contains($file, $primer))
				{
					$a = [
						'file_name' => $file,
						'data' => base64_encode(file_get_contents($new_path)),
					];
					return $a;
				}
				continue;
			}
		}
		return false;
	}

	public static function create_warning($type, $model, $error)
	{
		if($type == 1){
			$class = \App\DrWorksheetWarning::class;
			$column = 'worksheet_id';
		}
		else{
			$class = \App\DrWarning::class;
			$column = 'sample_id';			
		}

		$e = $class::firstOrCreate([
			$column => $model->id,
			'warning_id' => self::get_sample_warning($error->title),
			'system' => $error->system ?? '',
			'detail' => $error->detail ?? '',
		]);

		if(!$e->warning_id){
			$e->detail .= " error_name " . $error->title;
			$e->save();
		}
		return $e;
	}

	public static function get_plate_result($worksheet)
	{
		$client = new Client(['base_uri' => self::$hyrax_url]);

		$response = $client->request('GET', "sanger/plate/result/{$worksheet->plate_id}", [
			'headers' => [
				// 'Accept' => 'application/json',
				'X-Hyrax-Apikey' => self::get_hyrax_key(),
			],
		]);

		$body = json_decode($response->getBody());

		if($response->getStatusCode() == 200)
		{
			$w = $body->data->attributes;
			$worksheet->exatype_status_id = self::get_worksheet_status($w->status);
			$worksheet->plate_controls_pass = $w->plate_controls_pass;
			$worksheet->qc_run = $w->plate_qc_run;
			$worksheet->qc_pass = $w->plate_qc;

			if($worksheet->exatype_status_id == 4) return null;

			if($worksheet->exatype_status_id != 5){

				if($w->errors){
					foreach ($w->errors as $error) {
						self::create_warning(1, $worksheet, $error);
					}
				}

				if($w->warnings){
					foreach ($w->warnings as $error) {
						self::create_warning(1, $worksheet, $error);
					}
				}
			}

			$worksheet->status_id = 6;
			$worksheet->save();

			// dd($body->included);

			foreach ($body->included as $key => $value) {

				$sample = DrSample::where(['exatype_id' => $value->attributes->id])->first();

				if($sample){

					// echo " {$sample->id} ";

					// if($worksheet->exatype_status_id == 5 && !$worksheet->plate_controls_pass && !$sample->control) continue;

					$s = $value->attributes;
					$sample->status_id = self::get_sample_status($s->status_id);	

					if($sample->status_id == 3)	$sample->qc_pass = 0;			

					if($s->sample_qc_pass){
						$sample->qc_pass = $s->sample_qc_pass;

						$sample->qc_stop_codon_pass = $s->sample_qc->stop_codon_pass;
						$sample->qc_plate_contamination_pass = $s->sample_qc->plate_contamination_pass;
						$sample->qc_frameshift_codon_pass = $s->sample_qc->frameshift_codon_pass;
					}

					if($s->sample_qc_distance){
						$sample->qc_distance_to_sample = $s->sample_qc_distance[0]->to_sample_id;
						$sample->qc_distance_from_sample = $s->sample_qc_distance[0]->from_sample_id;
						$sample->qc_distance_difference = $s->sample_qc_distance[0]->difference;
						$sample->qc_distance_strain_name = $s->sample_qc_distance[0]->strain_name;
						$sample->qc_distance_compare_to_name = $s->sample_qc_distance[0]->compare_to_name;
						$sample->qc_distance_sample_name = $s->sample_qc_distance[0]->sample_name;
					}

					if($s->errors){
						$sample->has_errors = true;

						foreach ($s->errors as $error) {
							self::create_warning(2, $sample, $error);
						}
					}

					if($s->warnings){
						$sample->has_warnings = true;

						foreach ($s->warnings as $error) {
							self::create_warning(2, $sample, $error);
						}
					}

					if($s->calls){
						$sample->has_calls = true;

						foreach ($s->calls as $call) {
							// $c = DrCall::where(['sample_id' => $sample->id, 'drug_class' => $call->drug_class])->first();
							// if(!$c) $c = new DrCall;

							// $c->fill([
							// 	'sample_id' => $sample->id,
							// 	'drug_class' => $call->drug_class,
							// 	'other_mutations' => $call->other_mutations,
							// 	'major_mutations' => $call->major_mutations,
							// ]);

							// $c->save();

							// dd($call);

							$c = DrCall::firstOrCreate([
								'sample_id' => $sample->id,
								'drug_class' => $call->drug_class,
								'drug_class_id' => self::get_drug_class($call->drug_class),
								'mutations' => self::escape_null($call->mutations),
								// 'other_mutations' => self::escape_null($call->other_mutations),
								// 'major_mutations' => self::escape_null($call->major_mutations),
							]);

							foreach ($call->drugs as $drug) {
								$d = DrCallDrug::firstOrCreate([
									'call_id' => $c->id,
									'short_name' => $drug->short_name,
									'short_name_id' => self::get_short_name_id($drug->short_name),
									'call' => $drug->call,
								]);
							}
						}
					}

					if($s->genotype){
						$sample->has_genotypes = true;

						foreach ($s->genotype as $genotype) {
							$g = DrGenotype::firstOrCreate([
								'sample_id' => $sample->id,
								'locus' => $genotype->locus,
							]);

							foreach ($genotype->residues as $residue) {
								$r = DrResidue::firstOrCreate([
									'genotype_id' => $g->id,
									'residue' => $residue->residues[0] ?? null,
									'position' => $residue->position,
								]);
							}
						}
					}

					if($s->pending_action == "PendChromatogramManualIntervention"){
						$sample->pending_manual_intervention = true;
					}

					if(!$s->pending_action && $sample->pending_manual_intervention){
						$sample->pending_manual_intervention = false;
						$sample->had_manual_intervention = true;
					}				

					$sample->assembled_sequence = $s->assembled_sequence ?? '';
					$sample->chromatogram_url = $s->chromatogram_url ?? '';
					$sample->exatype_version = $s->exatype_version ?? '';
					$sample->algorithm = $s->algorithm ?? '';
					// $sample->pdf_download_link = $s->sample_pdf_download->signed_url ?? '';
					$sample->save();

					// echo " {$sample->id} ";
				}
			}
		}

		// dd($body);
	}

	public static function get_worksheet_status($id)
	{
		return DB::table('dr_plate_statuses')->where(['name' => $id])->first()->id;
	}

	public static function get_sample_status($id)
	{
		return DB::table('dr_sample_statuses')->where(['other_id' => $id])->first()->id;
	}

	public static function get_sample_warning($id)
	{
		// if(!DB::table('dr_warning_codes')->where(['name' => $id])->first()) dd($id);
		return DB::table('dr_warning_codes')->where(['name' => $id])->first()->id ?? 0;
	}

	public static function get_drug_class($id)
	{
		return DB::table('regimen_classes')->where(['drug_class' => $id])->first()->drug_class_id ?? null;
	}

	public static function get_short_name_id($id)
	{
		return DB::table('regimen_classes')->where(['short_name' => $id])->first()->id ?? null;
	}

	public static function escape_null($var)
	{
		if($var) return $var;
		return null;
	}

	public static function set_drug_classes()
	{
		ini_set('memory_limit', '-1');

		$calls = DrCall::all();

		foreach ($calls as $key => $value) {
			$value->drug_class_id = self::get_drug_class($value->drug_class);
			$value->save();
		}

		$calls = DrCallDrug::all();

		foreach ($calls as $key => $value) {
			$value->short_name_id = self::get_short_name_id($value->short_name);
			$value->save();
		}
	}


	public static function get_extraction_worksheet_samples($limit=48)
	{
		$samples = DrSampleView::whereNull('worksheet_id')
		->whereNull('extraction_worksheet_id')
		->where('datereceived', '>', date('Y-m-d', strtotime('-1 year')))
		->where(['receivedstatus' => 1, 'control' => 0])
		->orderBy('run', 'desc')
		->orderBy('datereceived', 'asc')
		->orderBy('id', 'asc')
		->limit($limit)
		->get();

		if($samples->count() == $limit){
			return ['samples' => $samples, 'create' => true, 'limit' => $limit];
		}
		return ['samples' => $samples, 'create' => false];
	}

	public static function get_worksheet_samples($extraction_worksheet_id)
	{
		$samples = DrSampleView::whereNull('worksheet_id')
		->where(['passed_gel_documentation' => true, 'extraction_worksheet_id' => $extraction_worksheet_id])
		->orderBy('control', 'desc')
		->orderBy('run', 'desc')
		->orderBy('id', 'asc')
		->limit(16)
		->get();

		if($samples->count() > 0){
			return ['samples' => $samples, 'create' => true, 'extraction_worksheet_id' => $extraction_worksheet_id];
		}
		return ['create' => false, 'extraction_worksheet_id' => $extraction_worksheet_id];
	}


	public static function generate_samples()
	{
		$potential_patients = \App\DrPatient::where('status_id', 1)->limit(300)->get();

		foreach ($potential_patients as $patient) {
	        $data = $patient->only(['patient_id', 'dr_reason_id']);
	        $data['user_id'] = 0;
	        $data['receivedstatus'] = 1;
	        $data['datecollected'] = date('Y-m-d', strtotime('-2 days'));
	        $data['datereceived'] = date('Y-m-d');
	        // $sample = DrSample::create($data);
	        $sample = new DrSample;
	        $sample->fill($data);
	        $facility = $sample->patient->facility;
	        $sample->facility_id = $facility->id;
	        $sample->save();      

	        $patient->status_id=2;
	        $patient->save();
		}
	}


	public static function regimens()
	{
		$calls = \App\DrCallView::all();

		foreach ($calls as $key => $value) {
			$reg = DB::table('regimen_classes')->where(['drug_class' => $value->drug_class, 'short_name' => $value->short_name])->first();

			if(!$reg){
				DB::table('regimen_classes')->insert(['drug_class' => $value->drug_class, 'short_name' => $value->short_name]);
			}
		}
	}


	public static function seed()
	{		
    	$e = \App\DrExtractionWorksheet::create(['lab_id' => env('APP_LAB'), 'createdby' => 2, 'date_gel_documentation' => date('Y-m-d')]);

    	$w = \App\DrWorksheet::create(['extraction_worksheet_id' => $e->id]);

    	DB::table('dr_samples')->insert([
    		['id' => 1, 'control' => 1, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 2, 'control' => 2, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    	]);

    	DB::table('dr_samples')->insert([
    		['id' => 6, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 10, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 14, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 17, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 20, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 22, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 99, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 2009695759, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 2012693909, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 2012693911, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 2012693943, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 3005052934, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 3005052959, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    	]);
	}

	public static function seed_nhrl()
	{		
    	$u = \App\User::where('user_type_id', 0)->first();
    	$e = \App\DrExtractionWorksheet::create(['lab_id' => env('APP_LAB'), 'createdby' => $u->id, 'date_gel_documentation' => date('Y-m-d')]);

    	$w = \App\DrWorksheet::create(['extraction_worksheet_id' => $e->id, 'createdby' => $u->id, ]);

    	DB::table('dr_samples')->insert([
    		['id' => 1, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 2, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 3, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 4, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 5, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 6, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 7, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 9, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 10, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 14, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 17, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 20, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 22, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    	]);

    	DB::table('dr_samples')->insert([
    		['id' => 23, 'control' => 1, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    		['id' => 24, 'control' => 2, 'patient_id' => 1, 'worksheet_id' => $w->id, 'extraction_worksheet_id' => $e->id],
    	]);
	}

	public static function download_pdf()
	{
		$client = new Client();
		// $url = "https://hyrax-development-sanger-sample.s3-eu-west-1.amazonaws.com/1550136209464705939/4569f586f6df15baf4cb92d8a94279bc?response-content-disposition=attachment%3B%20filename%3D22.pdf&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=ASIARH5SJVFVYGFOJCHP%2F20190214%2Feu-west-1%2Fs3%2Faws4_request&X-Amz-Date=20190214T105733Z&X-Amz-Expires=3600&X-Amz-Security-Token=FQoGZXIvYXdzEJP%2F%2F%2F%2F%2F%2F%2F%2F%2F%2FwEaDIN78kttnR2IssO03SK3A1z0d2oC2zkwdgFx2rG5tluWSX6gOUawwS79leG0dgkxAuoZi3Du52z%2FgLTgeb0QIAilCWCGujyUXSeDojB%2FFeDyq7Tj%2FvfyvxQscXn%2BF%2FcD9Ts%2BRBWdlvbbpGh8%2Bo21%2B8JHkvDM4G4InWMi3RMtIvST%2BKhMJkUSZxkRgrCb2c%2BzX45sWuXhbkZngr7KEPDwCVABAWKgcKmKhauLlUXIl67D8Jg9QGKKYA2I%2B4yayCdF17NyWS3lysYciNvxIYXVBAWIF6UVLrk1X6rotjehkUYqysoq%2FuTAe99xR6TpT2eDX8EZR7KkbMbOib%2BYcSjfX1ybjabkB1u8YsDYyg7k6OOiTdA%2FerC4hi8xGcEJNrK1yhjpJlwX3UYr2kIj9UmzPRf6NQO0dkVkmjzRvKHGziYeynqgAhKlMdpAo8Zr6W5wOclULMUJpQ2BcYxKvk0G2SoV0Hn4sgOeVEy4sP81B9UjhK4q8RXyxqTbfJ%2FYlMUuJCjA2AGR6kmWY%2Bc1v2OTy9wi3FEwkTTjvNVKAY5t8rMPb6JNvxl3ftqta%2FPYvb3gs5PzWXvQ1m%2FrH0XTSD5ZyZbZLFu%2FWo0oueSU4wU%3D&X-Amz-SignedHeaders=host&X-Amz-Signature=23421a4f7244956b86bcbbcf6b7d5a56163c1481be84a0a6e10c225d6c6efcf7";

		$url = "https://hyrax-development-sanger-sample.s3-eu-west-1.amazonaws.com/1550136214181171526/d33cce822e88cb26f34a8ff0f7641af4?response-content-disposition=attachment%3B%20filename%3D2009695759.pdf&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=ASIARH5SJVFVYGFOJCHP%2F20190214%2Feu-west-1%2Fs3%2Faws4_request&X-Amz-Date=20190214T105733Z&X-Amz-Expires=3600&X-Amz-Security-Token=FQoGZXIvYXdzEJP%2F%2F%2F%2F%2F%2F%2F%2F%2F%2FwEaDIN78kttnR2IssO03SK3A1z0d2oC2zkwdgFx2rG5tluWSX6gOUawwS79leG0dgkxAuoZi3Du52z%2FgLTgeb0QIAilCWCGujyUXSeDojB%2FFeDyq7Tj%2FvfyvxQscXn%2BF%2FcD9Ts%2BRBWdlvbbpGh8%2Bo21%2B8JHkvDM4G4InWMi3RMtIvST%2BKhMJkUSZxkRgrCb2c%2BzX45sWuXhbkZngr7KEPDwCVABAWKgcKmKhauLlUXIl67D8Jg9QGKKYA2I%2B4yayCdF17NyWS3lysYciNvxIYXVBAWIF6UVLrk1X6rotjehkUYqysoq%2FuTAe99xR6TpT2eDX8EZR7KkbMbOib%2BYcSjfX1ybjabkB1u8YsDYyg7k6OOiTdA%2FerC4hi8xGcEJNrK1yhjpJlwX3UYr2kIj9UmzPRf6NQO0dkVkmjzRvKHGziYeynqgAhKlMdpAo8Zr6W5wOclULMUJpQ2BcYxKvk0G2SoV0Hn4sgOeVEy4sP81B9UjhK4q8RXyxqTbfJ%2FYlMUuJCjA2AGR6kmWY%2Bc1v2OTy9wi3FEwkTTjvNVKAY5t8rMPb6JNvxl3ftqta%2FPYvb3gs5PzWXvQ1m%2FrH0XTSD5ZyZbZLFu%2FWo0oueSU4wU%3D&X-Amz-SignedHeaders=host&X-Amz-Signature=52fac846320d254fe5094518e226701ba2c1a044da9247cdb8a24059251d3dee";


		$response = $client->request('GET', $url, [
			// 'headers' => [
				// 'Accept' => 'application/json',
				// 'X-Hyrax-Apikey' => self::get_hyrax_key(),
			// ],
		]);

		$body = $response->getBody();
		$headers = $response->getHeaders();

		// dd($body);
		dd($headers);
	}

	public static function columns()
	{
		DB::statement("ALTER TABLE `dr_samples`
			ADD `age` tinyint unsigned NULL AFTER `sampletype`,

			ADD `approvedby` int unsigned NULL AFTER `datedispatched`,
			ADD `approvedby2` int unsigned NULL AFTER `approvedby`,
			ADD `dateapproved` date NULL AFTER `approvedby2`,
			ADD `dateapproved2` date NULL AFTER `dateapproved`,

			ADD `repeatt` tinyint unsigned NOT NULL DEFAULT 0 AFTER `other_medications`,
			ADD `run` tinyint unsigned NOT NULL DEFAULT 1 AFTER `repeatt`,
			ADD `parentid` int unsigned NOT NULL DEFAULT 0 AFTER `run`,
			ADD `collect_new_sample` tinyint unsigned NOT NULL DEFAULT 0 AFTER `parentid`,

			CHANGE `sanger_id` `exatype_id` int(10) unsigned NULL DEFAULT '0' AFTER `control`
		;");


	}

	public static function w_columns()
	{
		DB::statement("ALTER TABLE `dr_worksheets`
			CHANGE `sanger_status_id` `exatype_status_id` int(10) unsigned NULL DEFAULT '0' AFTER `status_id`,
			ADD `daterun` DATE NULL AFTER `exatype_status_id`
		;");


	}

	


}
