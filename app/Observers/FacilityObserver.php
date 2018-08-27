<?php

namespace App\Observers;

use GuzzleHttp\Client;
use App\Facility;
use App\Synch;

class FacilityObserver
{

    public function created(Facility $facility)
    {
        $user = \App\User::create([
                'user_type_id' => 5,
                'surname' => '',
                'oname' => '',
                'lab_id' => env('APP_LAB'),
                'facility_id' => $facility->id,
                'email' => 'facility' . $facility->id . '@nascop-lab.com',
                'password' => encrypt($facility->name)
            ]);
    }

    public function updated(Facility $facility)
    {
        $base = Synch::$base;
        $client = new Client(['base_uri' => $base]);
        $today = date('Y-m-d');
        $url = 'facility/' . $facility->id;

        $response = $client->request('put', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . Synch::get_token(),
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