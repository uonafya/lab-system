<?php

namespace App;

use GuzzleHttp\Client;

use App\Common;

class MiscDr extends Common
{

	public static function create_plate($worksheet)
	{
		$client = new Client(['base_uri' => self::$base]);

		$path = storage_path('app/public/results/dr/' . $worksheet->id . '/');

		

		$response = $client->request('post', 'insert/patients', [
			'headers' => [
				'Accept' => 'application/json',
				'X-Hyrax-Apikey' => env('DR_KEY'),
			],
			'json' => [
				'patients' => $patients->toJson(),
				'lab_id' => env('APP_LAB', null),
			],

		]);
	}
}
