<?php

namespace App\Observers;

use App\CovidPatient;

class CovidPatientObserver
{
    /**
     * Handle the covid patient "created" event.
     *
     * @param  \App\CovidPatient  $covidPatient
     * @return void
     */
    public function created(CovidPatient $covidPatient)
    {
        //
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
