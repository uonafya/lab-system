<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\EidRequest;
use App\Api\V1\Requests\EidCompleteRequest;

use App\Lookup;
use App\SampleView;
use App\Batch;
use App\Patient;
use App\Sample;
use App\Mother;

class EidController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('jwt:auth', []);
    }

    public function eid(EidRequest $request)
    {
        $code = $request->input('mflCode');
        $mother_ccc = $request->input('ccc_no');
        $hiv_status = $request->input('hiv_status');
        $hei_number = $request->input('patient_identifier');

        $datereceived = $request->input('datereceived');
        $datecollected = $request->input('datecollected');
        $dob = $request->input('dob');

        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_age($datecollected, $dob);
        $lab = $request->input('lab') ?? env('APP_LAB');

        $sample_exists = SampleView::sample($facility, $hei_number, $datecollected)->get()->first();
        $fields = Lookup::samples_arrays();

        if($sample_exists){
            // return json_encode("EID HEI Number {$hei_number} collected on {$datecollected} already exists in database.");

            return response()
                ->json([
                    'error' => 500,
                    'message' =>  "EID HEI Number {$hei_number} collected on {$datecollected} already exists in database.",
                ]);
        }

        $batch = Batch::existing($facility, $datereceived, $lab)->withCount(['sample'])->get()->first();

        if($batch && $batch->sample_count < 10){
            unset($batch->sample_count);
        }
        else if($batch && $batch->sample_count > 9){
            unset($batch->sample_count);
            $batch->full_batch();
            $batch = new Batch;
        }
        else{
            $batch = new Batch;
        }

        $batch->lab_id = $lab;
        $batch->facility_id = $facility;
        $batch->datereceived = $datereceived;
        $batch->user_id = 0;
        $batch->site_entry = 0;
        $batch->save();

        $patient = Patient::existing($facility, $hei_number)->get()->first();

        // if($patient){
        //     $mom = $patient->mother;
        // } 
        // else{
            $patient = new Patient;
            $mom = new Mother;
        // }

        $mom->ccc_no = $mother_ccc;
        $mom->facility_id = $facility;
        $mom->hiv_status = $hiv_status;
        // $mother->mother_dob = Lookup::calculate_mother_dob($datecollected, $request->input('mother_age'));
        $mom->save();
        
        $patient->fill($request->only($fields['patient']));
        $patient->mother_id = $mom->id;
        $patient->patient = $hei_number;
        $patient->facility_id = $facility;
        $patient->save();

        $sample = new Sample;
        $sample->fill($request->only($fields['sample']));
        $sample->batch_id = $batch->id;
        $sample->patient_id = $patient->id;
        $sample->age = $age;
        $sample->save();

        $sample->load(['patient.mother', 'batch']);
        return $sample;
    }

    public function complete_result(EidCompleteRequest $request)
    {
        $editted = $request->input('editted');
        $lab = $request->input('lab') ?? env('APP_LAB');
        $code = $request->input('mflCode');
        $specimenlabelID = $request->input('specimenlabelID');
        $patient_identifier = $request->input('patient_identifier');
        $datecollected = $request->input('datecollected');
        $datereceived = $request->input('datereceived');
        $datedispatched = $request->input('datedispatched');
        // $gender = $request->input('gender');
        $dob = $request->input('dob');
        
        $ccc_no = $request->input('ccc_no');
        $hiv_status = $request->input('hiv_status');

        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_age($datecollected, $dob);
        // $sex = Lookup::get_gender($gender);

        $sample_exists = SampleView::sample($facility, $patient_identifier, $datecollected)->first();
        $fields = Lookup::samples_arrays();

        if($sample_exists && !$editted){
            // return json_encode("EID HEI Number # {$patient_identifier} collected on {$datecollected} already exists in database.");

            return response()
                ->json([
                    'error' => 500,
                    'message' =>  "EID HEI Number {$hei_number} collected on {$datecollected} already exists in database.",
                ]);
        }

        if(!$editted){
            $batch = Batch::existing($facility, $datereceived, $lab)->withCount(['sample'])->get()->first();

            if($batch && $batch->sample_count < 10){
                unset($batch->sample_count);
            }
            else if($batch && $batch->sample_count > 9){
                unset($batch->sample_count);
                $batch->full_batch();
                $batch = new Batch;
            }
            else{
                $batch = new Batch;
            }

            $batch->lab_id = $lab;
            $batch->user_id = 0;
            $batch->facility_id = $facility;
            $batch->datereceived = $datereceived;
            $batch->datedispatched = $datedispatched;
            $batch->site_entry = 0;
            $batch->save();            
        }

        $patient = Patient::existing($facility, $patient_identifier)->get()->first();

        if($patient){
            $mom = $patient->mother;
        } 
        else{
            $patient = new Patient;
            $mom = new Mother;
        }

        $mom->ccc_no = $ccc_no;
        $mother->mother_dob = Lookup::calculate_mother_dob($datecollected, $request->input('mother_age'));
        $mom->facility_id = $facility;
        $mom->hiv_status = $hiv_status;
        $mom->save();

        $patient->fill($request->only($fields['patient']));
        $patient->mother_id = $mom->id;
        $patient->patient = $patient_identifier;
        $patient->facility_id = $facility;      
        // $patient->sex = $sex;
        $patient->save();

        if($editted){
            $sample = Sample::find($sample_exists->id);
        }
        else{
            $sample = new Sample;
            $sample->batch_id = $batch->id;
            $sample->patient_id = $patient->id;
        }

        $sample->fill($request->only($fields['sample']));
        $sample->age = $age;
        $sample->comment = $specimenlabelID;
        $sample->dateapproved = $sample->dateapproved2 = $sample->datetested;
        $sample->synched = 5;
        $sample->save();

        $sample->load(['patient.mother', 'batch']);
        return $sample;
    }




}
