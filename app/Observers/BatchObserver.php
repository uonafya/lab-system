<?php

namespace App\Observers;

use App\Batch;
use App\Patient;
use App\Mother;
use App\SampleView;

use DB;

class BatchObserver
{
    /**
     * Listen to the Batch updating event.
     *
     * @param  \App\Batch  $batch
     * @return void
     */
    public function updating(Batch $batch)
    {
        // Check if the facility of the batch has been changed
        if($batch->facility_id != $batch->getOriginal('facility_id'))
        {
            $samples = SampleView::where('batch_id', $batch->id)->get();
            $patient_ids = $samples->pluck(['patient_id'])->toArray();
            $mother_ids = $samples->pluck(['mother_id'])->toArray();

            $time = date("Y-m-d H:i:s");

            $patients = Patient::whereIn('id', $patient_ids)->get();
            $mothers = Mother::whereIn('id', $mother_ids)->get();

            foreach ($patients as $key => $patient) {
                $patient->facility_id = $batch->facility_id;
                $patient->pre_update();
            }

            foreach ($mothers as $key => $mother) {
                $mother->facility_id = $batch->facility_id;
                $mother->pre_update();
            }
        }
    }
}