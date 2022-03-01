<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use App\Lookup;
use App\Facility;
use App\Viralpatient;
use App\Viralbatch;
use App\ViralsampleView;
use App\Viralsample;
use App\Viralworksheet;
use App\Exports\ViralInterLabSampleExport;
use Carbon\Carbon;

class ViralInterLabSampleImport implements ToCollection, WithHeadingRow
{
	private $receivedby;

	public function __construct($request)
	{
		$this->receivedby = $request->input('receivedby');
	}

    /*$u = \App\User::where('email', 'like', 'joelkith%')->first();
    $viralbatches = \App\Viralbatch::where('user_id', $u->id)->where('created_at', '>', date('Y-m-d'))->get();
    $batch_ids = $viralbatches->pluck('id')->toArray();
    \App\Viralsample::whereIn('batch_id', $batch_ids)->delete();
    \App\Viralbatch::whereIn('id', $batch_ids)->delete();
    \App\Viralworksheet::where('createdby', $u->id)->where('created_at', '>', date('Y-m-d'))->delete();

    $u = \App\User::where('email', 'like', 'joelkith%')->first(); $viralbatches = \App\Viralbatch::where('user_id', $u->id)->where('created_at', '>', date('Y-m-d'))->get(); $batch_ids = $viralbatches->pluck('id')->toArray(); \App\Viralsample::whereIn('batch_id', $batch_ids)->delete(); \App\Viralbatch::whereIn('id', $batch_ids)->delete(); \App\Viralworksheet::where('createdby', $u->id)->where('created_at', '>', date('Y-m-d'))->delete();
    */
    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $batch = null;
       	$lookups = Lookup::get_viral_lookups();
       	// $dataArray = [];
       	// $countItem = $collection->count();
       	$counter = 0;
        $worksheet_counter = 0;
       	$receivedby = $this->receivedby;

       	foreach ($collection as $samplekey => $samplevalue) {
            // Formatting the dates from the excel data
            // printf($samplevalue);
            // printf(isset($samplevalue['dob']));
            if (isset($samplevalue['dob']) != 1){
                printf('empty object');
                continue;
            }
       		$dob = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($samplevalue['dob']))->format('Y-m-d');
            
        	$initiation_date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($samplevalue['art_init_date']))->format('Y-m-d');
        	$datecollected = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($samplevalue['datecollected']))->format('Y-m-d');
        	$datereceived = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($samplevalue['datereceived']))->format('Y-m-d');

       		$counter++;
            $sample_count = $counter % 100;
            if($sample_count == 1){
                $worksheet = $this->createWorksheet($receivedby);
                $worksheet_counter = 0;
            }

            $facility = Facility::where('facilitycode', '=', $samplevalue['mflcode'])->first();

            $existing = Viralpatient::existing($facility->id, $samplevalue['specimenclientcode'])->first();
            
            if ($existing){
                $patient = $existing;
            } else {            
                $patient = new Viralpatient();
                $patient->patient = $samplevalue['specimenclientcode'];
                $patient->facility_id = $facility->id;
                $patient->sex = $lookups['genders']->where('gender', strtoupper($samplevalue['sex']))->first()->id;
                $patient->dob = $dob;
                $patient->initiation_date = $initiation_date;
                $patient->save();
            }

            $batch = $this->createBatch($facility, $patient, $datecollected, $receivedby, $datereceived);

            $existingSample = ViralsampleView::existing(['facility_id' => $facility->id, 'patient' => $patient->patient, 'datecollected' => $datecollected])->first();

            if($existingSample) continue;
            $worksheet_counter++;
            $sample = new Viralsample();
            $sample->batch_id = $batch->id;
            $sample->receivedstatus = $samplevalue['receivedstatus'];
            $sample->age = $samplevalue['age'];
            $sample->patient_id = $patient->id;
            $sample->pmtct = $samplevalue['pmtct'];
            $sample->dateinitiatedonregimen = $initiation_date;
            $sample->datecollected = $datecollected;
            $sample->regimenline = $samplevalue['regimenline'];
            $sample->prophylaxis = $lookups['prophylaxis']->where('code', $samplevalue['currentregimen'])->first()->id ?? 15;
            $sample->justification = $lookups['justifications']->where('rank_id', $samplevalue['justification'])->first()->id ?? 8;
            $sample->sampletype = $samplevalue['sampletype'];
            $sample->recency_number = $samplevalue['recencyno']; 
             

            if($worksheet_counter < 94) $sample->worksheet_id = $worksheet->id;             
            $sample->save();

            $batch_sample_count = $batch->sample->count();

            if($batch_sample_count > 9) $batch->full_batch();

       	}
        
        session(['toast_message' => "The worksheet has been updated with the results."]);
        return back();
    }

    private function createBatch($facility, $patient, $datecollected, $receivedby, $datereceived)
    {
        $batch = Viralbatch::eligible($facility->id, $datereceived)->withCount(['sample'])->first();
        if($batch && $batch->sample_count < 10){
            unset($batch->sample_count);
        }
        else if($batch && $batch->sample_count > 9){
            unset($batch->sample_count);
            $batch->full_batch();
            $batch = new Viralbatch;
        }
        else{
            $batch = new Viralbatch;
        }
        $batch->user_id = $receivedby;
        $batch->lab_id = env('APP_LAB');
        $batch->received_by = $receivedby;
        $batch->site_entry = 0;
        $batch->entered_by = $receivedby;
        $batch->datereceived = $datereceived;
        $batch->facility_id = $facility->id;
        $batch->save();
        return $batch;
    }

    private function createWorksheet($receivedby)
    {
        $worksheet = new Viralworksheet();
        $worksheet->lab_id = env('APP_LAB');
        $worksheet->machine_type = 1;
        $worksheet->sampletype = 2;
        $worksheet->createdby = $receivedby;
        $worksheet->sample_prep_lot_no = 44444;
        $worksheet->bulklysis_lot_no = 44444;
        $worksheet->control_lot_no = 44444;
        $worksheet->calibrator_lot_no = 44444;
        $worksheet->amplification_kit_lot_no = 44444;
        $worksheet->sampleprepexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
        $worksheet->bulklysisexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
        $worksheet->controlexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
        $worksheet->calibratorexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
        $worksheet->amplificationexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
        $worksheet->save();
        return $worksheet;
    }
}
