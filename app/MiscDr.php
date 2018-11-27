<?php

namespace App;

use GuzzleHttp\Client;

use App\Common;
use App\DrSample;

use App\DrWorksheetWarning;
use App\DrWarning;

use App\DrCall;
use App\DrCallDrug;

use App\DrGenotype;
use App\DrResidue;

class MiscDr extends Common
{

	public static $hyrax_url = 'http://blablabla';

	public static function get_hyrax_key()
	{
		return env('DR_KEY');
	}

	public static function create_plate($worksheet)
	{
		$client = new Client(['base_uri' => self::$hyrax_url]);

		$sample_data = self::get_worksheet_files($worksheet);

		$response = $client->request('POST', 'sanger/plate', [
			'headers' => [
				'Accept' => 'application/json',
				// 'X-Hyrax-Apikey' => env('DR_KEY'),
				'x-hyrax-daemon-apikey' => self::get_hyrax_key(),
			],
			'json' => [
				[
					'type' => 'plate_create',
					'attributes' => [
						'plate_name' => "{$worksheet->id}",
					],
				],
				'included' => $sample_data,
			],
		]);

		$body = json_decode($response->getBody());

		if($response->getStatusCode() < 400)
		{
			$worksheet->plate_id = $body->data->id;

			foreach ($body->data->attributes->samples as $key => $value) {
				$sample = DrSample::find($value->sample_name);
				$sample->sanger_id = $value->id;
				$sample->save();
			}
		}

	}

	public static function get_worksheet_files($worksheet)
	{
		$path = storage_path('app/public/results/dr/' . $worksheet->id . '/');

		$samples = $worksheet->sample;
		$samples->load(['result']);

		$primers = ['F1', 'F2', 'F3', 'R1', 'R2', 'R3'];

		$sample_data = [];

		foreach ($samples as $key => $sample) {
			$s = [
				'type' => 'sample_create',
				'attributes' => [
					'sample_name' => "{$sample->id}",
					'pathogen' => 'hiv',
					'assay' => 'cdc-hiv',
					'enforce_recall' => false,
					'sample_type' => 'data',
				],
			];

			if($sample->control == 1) $s['attributes']['sample_type'] = 'negative';
			if($sample->control == 2) $s['attributes']['sample_type'] = 'positive';

			$abs = [];

			foreach ($primers as $primer) {
				$abs[] = self::find_ab_file($path, $sample, $primer);
			}
			$s['attributes']['ab1s'] = $abs;
			$sample_data[] = $s;
		}

		return $sample_data;
	}

	public static function find_ab_file($path, $sample, $primer)
	{
		$files = scandir($path);
		if(!$files) return null;

		foreach ($files as $file) {
			if($file == '.' || $file == '..') continue;

			$new_path = $path . $file;
			if(is_dir($new_path)){
				$a = self::find_ab_file($new_path, $sample, $primer);

				if(!$a) continue;
				return $a;
			}
			else{
				// if(starts_with($file, $sample->id . $primer)){
				if(starts_with($file, $sample->id . '-') && str_contains($file, $primer))
				{
					$a = [
						'filename' => $file,
						'data' => base64_encode(file_get_contents($new_path)),
					];
					return $a;
				}
				continue;
			}
		}
		return false;
	}

	public static function get_plate_result($worksheet)
	{
		$client = new Client(['base_uri' => self::$hyrax_url]);

		$response = $client->request('GET', "sanger/plate/result/{$worksheet->plate_id}", [
			'headers' => [
				'Accept' => 'application/json',
				// 'X-Hyrax-Apikey' => env('DR_KEY'),
				'x-hyrax-daemon-apikey' => self::get_hyrax_key(),
			],
		]);

		$body = json_decode($response->getBody());

		if($response->getStatusCode() == 200)
		{
			$w = $body->data->attributes;
			$worksheet->sanger_status_id = self::get_worksheet_status($w->status);
			$worksheet->plate_controls_pass = $w->plate_controls_pass;
			$worksheet->qc_run = $w->plate_qc_run;
			$worksheet->qc_pass = $w->plate_qc;

			// if($w->errors){
			// 	foreach ($w->errors as $error) {
			// 		$e = DrWorksheetWarning::firstOrCreate([
			// 			'worksheet_id' => $worksheet->id,
			// 			'warning_id' => self::get_sample_warning($error->title),
			// 			'system' => $error->system,
			// 			'detail' => $error->detail,
			// 		]);

			// 	}
			// }

			$worksheet->save();

			if($worksheet->sanger_status_id == 4) return null;

			foreach ($body->included as $key => $value) {

				$sample = DrSample::where(['sanger_id' => $value->attributes->id])->first();

				if($sample){

					if($worksheet->sanger_status_id == 5 && !$worksheet->plate_controls_pass && !$sample->control) continue;

					$s = $value->attributes;
					$sample->status_id = self::get_sample_status($s->status_id);					

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
							$e = DrWarning::firstOrCreate([
								'sample_id' => $sample->id,
								'warning_id' => self::get_sample_warning($error->title),
								'system' => $error->system,
								'detail' => $error->detail,
							]);
						}
					}

					if($s->warnings){
						$sample->has_warnings = true;

						foreach ($s->errors as $error) {
							$e = DrWarning::firstOrCreate([
								'sample_id' => $sample->id,
								'warning_id' => self::get_sample_warning($error->title),
								'system' => $error->system,
								'detail' => $error->detail,
							]);
						}
					}

					if($s->calls){
						$sample->has_calls = true;

						foreach ($s->calls as $call) {
							$c = DrCall::firstOrCreate([
								'sample_id' => $sample->id,
								'drug_class' => $call->drug_class,
								'other_mutations' => $call->other_mutations,
								'major_mutations' => $call->major_mutations,
							]);

							foreach ($call->drugs as $drug) {
								$d = DrCallDrug::firstOrCreate([
									'call_id' => $c->id,
									'short_name' => $drug->short_name,
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
									'residue' => $residue->residues[0],
									'position' => $residue->position,
								]);
							}
						}
					}

					if($s->pending_action == "PendChromatogramManualIntervention"){
						$sample->pending_manual_intervention = true;
					}

					$sample->assembled_sequence = $s->assembled_sequence;
					$sample->chromatogram_url = $s->chromatogram_url;
					$sample->exatype_version = $s->exatype_version;
					$sample->algorithm = $s->algorithm;
					$sample->save();
				}
			}
		}
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
		return DB::table('dr_warning_codes')->where(['name' => $id])->first()->id;
	}


}
