<?php

namespace App\Observers;

use App\QuarantineSite;
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
        $id = DB::connection('covid')->table('quarantine_sites')->insertGetId($$quarantineSite->toArray());
        $quarantineSite->id = $id;
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
