<?php

namespace App\Observers;

use App\CovidSample;
use DB;

class CovidSampleObserver
{
    /**
     * Handle the covid sample "saving" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function saving(CovidSample $covidSample)
    {
        $user = auth()->user();
        if(!$covidSample->site_entry){
            if($user->user_type_id == 5) $covidSample->site_entry = 1;
            else{
                $covidSample->site_entry = 0;
            }
        }
        if(!$covidSample->lab_id) $covidSample->lab_id = env('APP_LAB');
        if(!$covidSample->user_id) $covidSample->user_id = $user->id ?? null;
        if(!$covidSample->received_by && $covidSample->datereceived) $covidSample->received_by = $user->id;
        if(($covidSample->dob && !$covidSample->age) || $covidSample->isDirty('dob')) $covidSample->calc_age();

        if($covidSample->county && !$covidSample->county_id){
            $covidSample->county_id = DB::table('countys')->where('name', $covidSample->county)->first()->id ?? null;
        }
    }

    /**
     * Handle the covid sample "created" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function created(CovidSample $covidSample)
    {
        //
    }

    /**
     * Handle the covid sample "updated" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function updated(CovidSample $covidSample)
    {
        //
    }

    /**
     * Handle the covid sample "deleted" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function deleted(CovidSample $covidSample)
    {
        //
    }

    /**
     * Handle the covid sample "restored" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function restored(CovidSample $covidSample)
    {
        //
    }

    /**
     * Handle the covid sample "force deleted" event.
     *
     * @param  \App\CovidSample  $covidSample
     * @return void
     */
    public function forceDeleted(CovidSample $covidSample)
    {
        //
    }
}
