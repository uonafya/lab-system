<?php

namespace App;

use GuzzleHttp\Client;

use App\Common;
use App\DrSample;

class MiscDr extends Common
{

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

			$abs = [];

			foreach ($primers as $primer) {
				$abs[] = self::find_ab_file($path, $sample, $primer);
			}
			$s['attributes']['ab1s'] = $abs;
			$sample_data[] = $s;
		}

		return $sample_data;
	}

	public static function create_plate($worksheet)
	{
		$client = new Client(['base_uri' => 'https://blablabla']);

		$sample_data = self::get_worksheet_files($worksheet);

		$response = $client->request('post', '', [
			'headers' => [
				'Accept' => 'application/json',
				'X-Hyrax-Apikey' => env('DR_KEY'),
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

	}

	public static function find_ab_file($path, $obj, $primer)
	{
		$files = scandir($path);
		if(!$files) return null;

		foreach ($files as $file) {
			if($file == '.' || $file == '..') continue;

			$new_path = $path . $file;
			if(is_dir($new_path)){
				$a = self::find_ab_file($new_path, $obj, $primer);

				if(!$a) continue;
				return $a;
			}
			else{
				if(starts_with($file, $obj->id . $primer)){
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


}
