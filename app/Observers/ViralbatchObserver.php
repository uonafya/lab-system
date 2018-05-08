<?php

namespace App\Observers;

use App\Viralbatch;
use App\ViralsampleView;

use DB;

class ViralbatchObserver
{
    /**
     * Listen to the Viralbatch created event.
     *
     * @param  \App\Viralbatch  $viralbatch
     * @return void
     */
    public function updating(Viralbatch $viralbatch)
    {
        // Check if the facility of the batch has been changed
        if($viralbatch->facility_id != $viralbatch->getOriginal('facility_id'))
        {
            $samples = ViralsampleView::where('batch_id', $viralbatch->id)->get();
            $patient_ids = $samples->pluck(['patient_id'])->toArray();

            $time = date("Y-m-d H:i:s");

            DB::table('viralpatients')->whereIn('id', $patient_ids)->update(['facility_id' => $batch->facility_id, 'updated_at' => $time]);
        }
    }
}