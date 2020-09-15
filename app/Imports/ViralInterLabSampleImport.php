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
use App\Exports\ViralInterLabSampleExport;
use Carbon\Carbon;

class ViralInterLabSampleImport implements ToCollection, WithHeadingRow
{
	private $receivedby;

	public function __construct($request)
	{
		$this->receivedby = $request->input('receivedby');
	}

    
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $batch = null;
       	$lookups = Lookup::get_viral_lookups();
       	$dataArray = [];
       	$countItem = $collection->count();
       	$counter = 0;
       	$receivedby = $this->receivedby;

       	foreach ($collection as $samplekey => $samplevalue) {
            // Formatting the dates from the excel data
       		$dob = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($samplevalue['dob']))->format('Y-m-d');
        	$initiation_date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($samplevalue['art_init_date']))->format('Y-m-d');
        	$datecollected = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($samplevalue['datecollected']))->format('Y-m-d');
        	$datereceived = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($samplevalue['datereceived']))->format('Y-m-d');

       		$counter++;
            $facility = Facility::where('facilitycode', '=', $samplevalue['mflcode'])->first();
            // if (!isset($facility)){
            //     $nofacility[] = $samplevalue;
            //     continue;
            // }
            $existing = Viralpatient::existing($facility->id, $samplevalue['specimenclientcode'])->first();
            
            if ($existing){
                $patient = $existing;
            } else {            
                $patient = new Viralpatient();
                $patient->patient = $samplevalue['specimenclientcode'];
                $patient->facility_id = $facility->id;
                $patient->sex = $lookups['genders']->where('gender', strtoupper($samplevalue['sex']))->first()->id;
                $patient->dob = $dob;
                // $patient->initiation_date = $initiation_date;
                $patient->save();
            }

            if ($counter == 1) 
                $batch = $this->createBatch($facility, $patient, $datecollected, $receivedby, $datereceived);

            if (!(null !== $batch->id))
                $batch = $this->createBatch($facility, $patient, $datecollected, $receivedby, $datereceived);

            $existingSample = ViralsampleView::existing(['facility_id' => $facility->id, 'patient' => $patient->patient, 'datecollected' => $datecollected])->first();
        
            if (!$existingSample) {
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
                $sample->justification = $lookups['justifications']->where('rank', $samplevalue['justification'])->first()->id ?? 8;
                $sample->sampletype = $samplevalue['sampletype'];                
                $sample->save();
            }

            $sample_count = $batch->sample->count();

            $countItem -= 1;
            if($counter == 10) {
                if (!in_array($batch->id, $dataArray))
                    $dataArray[] = $batch->id;
                $batch->full_batch();
                $batch = null;
                $counter = 0;
            } 

            if ($countItem == 1) {
                $sample_count = $batch->sample->count() ?? 0;
                if ($sample_count != 10) {
                    $batch->premature();
                    if (!in_array($batch->id, $dataArray))
                    	$dataArray[] = $batch->id;
                }
            }
       	}
        // 23327
        // 23328
       	// $file = 'public/worksheets/otherlab/SamplesUploadedFromOther' . date('YmdHis') . '.xlsx';

       	// Excel::store(new ViralInterLabSampleExport($dataArray), $file);

        // // Excel::create($file, function($excel) use($rows, $file){
        // //     $excel->setTitle($file);
        // //     $excel->setCreator('Joshua Bakasa')->setCompany($file);
        // //     $excel->setDescription($file);

        // //     $excel->sheet('Sheetname', function($sheet) use($rows) {
        // //         $sheet->fromArray($rows);
        // //     });
        // // })->store('csv');

        // $data = [storage_path($file . ".xlsx")];

        // Mail::to(['bakasajoshua09@gmail.com'])->send(new TestMail($data));
       	// // dd($countItem);
        
        session(['toast_message' => "The worksheet has been updated with the results."]);
        return back();
    }

    private function createBatch($facility, $patient, $datecollected, $receivedby, $datereceived)
    {
        $batch = new Viralbatch();
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
}
