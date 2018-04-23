<?php

namespace App\Observers;

use App\Batch;
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

            DB::table('patients')->whereIn('id', $patient_ids)->update(['facility_id' => $batch->facility_id]);
            DB::table('mothers')->whereIn('id', $mother_ids)->update(['facility_id' => $batch->facility_id]);
        }
    }
}