<?php

namespace App\Observers;

use GuzzleHttp\Client;
use App\QuarantineSite;
use App\Synch;
use DB;

class QuarantineSiteObserver
{
    /**
     * Handle the quarantine site "created" event.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return void
     */
    public function creating(QuarantineSite $quarantineSite)
    {
        if($quarantineSite->email) $quarantineSite->email = str_replace(' ', '', $quarantineSite->email);


        $client = new Client(['base_uri' => Synch::$cov_base]);

        if(in_array(env('APP_LAB'), [1,2,3,6])){
            $id = DB::connection('covid')->table('quarantine_sites')->insertGetId($quarantineSite->toArray());
            $quarantineSite->id = $id;
        }else{
            $response = $client->request('post', 'quarantine_site', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . Synch::get_covid_token(),
                ],
                'http_errors' => false,
                'json' => [
                    'quarantine_site' => $quarantineSite->toJson(),
                    'lab_id' => env('APP_LAB', null),
                ],
            ]);
            if($response->getStatusCode() > 399) dd($response);
            

            $body = json_decode($response->getBody());

            $quarantineSite->id = $body->id;
        }
    }

    /**
     * Handle the quarantine site "updated" event.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return void
     */
    public function updated(QuarantineSite $quarantineSite)
    {
        //
    }

    /**
     * Handle the quarantine site "deleted" event.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return void
     */
    public function deleted(QuarantineSite $quarantineSite)
    {
        //
    }

    /**
     * Handle the quarantine site "restored" event.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return void
     */
    public function restored(QuarantineSite $quarantineSite)
    {
        //
    }

    /**
     * Handle the quarantine site "force deleted" event.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return void
     */
    public function forceDeleted(QuarantineSite $quarantineSite)
    {
        //
    }
}
