<?php

namespace App\Observers;

use App\Facility;

use GuzzleHttp\Client;

class FacilityObserver
{

    // Consider creating facilities on the national db first then returning the created facility

    public function created(Facility $facility)
    {
        $base = \App\Synch::$base;
        $client = new Client(['base_uri' => self::$base]);
        $today = date('Y-m-d');

        $response = $client->request('post', 'synch/facility', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'facility' => $facility->toJson(),
                'lab_id' => env('APP_LAB', null),
            ],
        ]);

        $body = json_decode($response->getBody());

        $update_data = ['id' => $value->id, 'synched' => 1, 'datesynched' => $today,];
        $facility->fill($update_data);
        $facility->save();
    }

    public function updated(Facility $facility)
    {
        $base = \App\Synch::$base;
        $client = new Client(['base_uri' => self::$base]);
        $today = date('Y-m-d');
        $url = 'synch/facility/' . $facility->id;

        $response = $client->request('put', $url, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'facility' => $facility->toJson(),
                'lab_id' => env('APP_LAB', null),
            ],
        ]);

        $body = json_decode($response->getBody());

        $update_data = ['synched' => 1, 'datesynched' => $today,];
        $facility->fill($update_data);
        $facility->save();

    }

}