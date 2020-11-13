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

    public function scopeExisting($query, $data)
    {
        return $query->where([
        					'material_number' => $data['material_number'],
        					'allocation_date' => $data['allocation_date'],
        					'allocation_type' => $data['allocation_type'],
        					'lab_id' => $data['lab_id']
        				]);
    }

    public function pullAllocations($params = [])
    {
    	$client = new Client(['base_uri' => $this->base]);
    	try {
    		$response = $client->request('get', 'covid_19', [
	            'http_errors' => false,
	            'debug' => true,
	            'verify' => false,
				'headers' => [
					'Accept' => 'application/json',
					'apitoken' => env('HCMP_TOKEN'),
					'Content-Type' => 'application/json',
				],
				'json' => [
					'type' => 'automated'
				],
			]);
			$body = json_decode($response->getBody());
			
			$empty = [];
			foreach ($body->data as $key => $item) {
				$data_existing = ['material_number' => $item->material_number, 'allocation_date' => $item->allocation_date, 'allocation_type' => $item->allocation_type, 'lab_id' => $item->lab_id];
				$existing = HCMPCovidAllocations::existing( $data_existing )->get();
				if ($existing->isEmpty()) {
					if (env('APP_LAB') == $item->lab_id) {
						$lab = Lab::find($item->lab_id);
						$kit = CovidKit::withTrashed()->where('material_no', $item->material_number)->get();
						if (!$kit->isEmpty()) {
							$model = new $this;
							$model->allocation_date = $item->allocation_date;
							$model->allocation_type = $item->allocation_type;
							$model->lab_id = $lab->id;
							$model->material_number = $kit->first()->material_no;
							$model->allocated_kits = $item->allocated_kits;
							$model->comments = $item->comments;
							$model->save();
						} else {
							$empty[] = $item;
						}
					}
				}
			}
			CovidAllocation::fillAllocations();
			return true;
    	} catch (Exception $e) {
    		echo "Error{";print_r($e);echo "}";
    		return false;
    	}
    }
}
