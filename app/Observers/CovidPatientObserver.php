<?php

namespace App\Observers;

use App\CovidPatient;

class CovidPatientObserver
{
    /**
     * Handle the covid patient "saving" event.
     *
     * @param  \App\CovidPatient  $covidPatient
     * @return void
     */
    public function saving(CovidPatient $covidPatient)
    {
        if($covidPatient->facility_id && !$covidPatient->county_id) $covidPatient->county_id = $covidPatient->view_facility->county_id;
        if($covidPatient->facility_id && !$covidPatient->subcounty_id) $covidPatient->subcounty_id = $covidPatient->view_facility->subcounty_id;

        if($covidPatient->county && !$covidPatient->county_id){
            $county = \DB::table('countys')->where('name', $covidPatient->county)->first();
            $covidPatient->county_id = $county->id ?? null;
        }
    }

    /**
     * Handle the covid patient "updated" event.
     *
     * @param  \App\CovidPatient  $covidPatient
     * @return void
     */
    public function updated(CovidPatient $covidPatient)
    {
        //
    }

    /**
     * Handle the covid patient "deleted" event.
     *
     * @param  \App\CovidPatient  $covidPatient
     * @return void
     */
    public function deleted(CovidPatient $covidPatient)
    {
        //
    }

    /**
     * Handle the covid patient "restored" event.
     *
     * @param  \App\CovidPatient  $covidPatient
     * @return void
     */
    public function restored(CovidPatient $covidPatient)
    {
        //
    }

    /**
     * Handle the covid patient "force deleted" event.
     *
     * @param  \App\CovidPatient  $covidPatient
     * @return void
     */
    public function forceDeleted(CovidPatient $covidPatient)
    {
        //
    }
}
