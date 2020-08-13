<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class HCMPCovidAllocations extends Model
{
    protected $table = 'hcmp_covid_allocations';

    private $base = 'https://rtk.nascop.org/api/';

    public function kit()
    {
    	return $this->belongsTo(CovidKit::class, 'material_number', 'material_no');
    }

    public function pullAllocations($params = [])
    {
    	$client = new Client(['base_uri' => $this->base]);
		$response = $client->request('get', 'covid_19', [
            'http_errors' => true,
            'debug' => true,
			'headers' => [
				'Accept' => 'application/json',
				'apitoken' => env('HCMP_TOKEN'),
			],
			'json' => [
				'type' => 'automated'
			],
		]);
		$body = json_decode($response->getBody());
		// print_r($body->data);
		foreach ($body->data as $key => $item) {
			if (env('APP_LAB') == $item->lab_id) {
				$lab = Lab::find($item->lab_id);
				$kit = CovidKit::where('material_no', $item->material_number)->first();

				$model = new $this;
				$model->allocation_date = $item->allocation_date;
				$model->allocation_type = $item->allocation_type;
				$model->lab_id = $lab->id;
				$model->material_number = $kit->material_no;
				$model->allocated_kits = $item->allocated_kits;
				$model->comments = $item->comments;
				$model->save();
			}
		}
		return true;
    }
}
