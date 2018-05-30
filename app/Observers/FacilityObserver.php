<?php

namespace App\Observers;

use App\Facility;

use GuzzleHttp\Client;

class FacilityObserver
{

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

}