<?php

namespace App\Observers;

use App\Viralbatch;
use App\Viralpatient;
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

            $patients = Viralpatient::whereIn('id', $patient_ids)->get();

            foreach ($patients as $key => $patient) {
                $patient->facility_id = $viralbatch->facility_id;
                $patient->pre_update();
            }
        }
    }
}