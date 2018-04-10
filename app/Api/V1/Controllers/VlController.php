<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\EidRequest;

use App\Lookup;
use App\ViralsampleView;
use App\Viralbatch;
use App\Viralsample;

class VlController extends Controller
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

    public function vl(EidRequest $request)
    {
        $code = $request->input('mflCode');
        $ccc_number = $request->input('patientId');
        $order_no = $request->input('order_no');
        $amrs_location = $request->input('amrs_location');
        $provider_identifier = $request->input('provider_identifier');
        $patient_name = $request->input('patient_name');
        $gender = $request->input('gender');
        $birthdate = $request->input('birthdate');
        $artStartDateInitial = $request->input('artStartDateInitial');
        $artStartDateCurrent = $request->input('artStartDateCurrent');
        $artRegimen = $request->input('artRegimen');
        $justification = $request->input('justification');
        $dateDrawn = $request->input('dateDrawn');
        $dateReceived = $request->input('dateReceived');
        $receivedStatus = $request->input('receivedStatus');
        $birthday = $request->input('birthday');

        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_viralage($dateDrawn, $birthday);
        $sex = Lookup::get_gender($gender);

        $sample_exists = ViralsampleView::sample($facility, $ccc_number, $dateDrawn)->first();

        if($sample_exists){
            return json_encode("VL CCC # {$ccc_number} collected on {$dateDrawn} already exists in database.");
        }

        $batch = Viralbatch::existing($facility, $dateReceived, $lab)->get()->first();

        if($batch && $batch->sample_count < 10){

        }
        else{
            $batch = new Batch;
        }

        $batch->lab_id = 5;
        $batch->user_id = 66;
        $batch->facility_id = $facility;
        $batch->datereceived = $dateReceived;
        $batch->site_entry = 0;
        $batch->save();

        $patient = Viralpatient::existing($facility, $ccc_number)->get()->first();

        if(!$patient){
            $patient = new Viralpatient;
            $patient->patient = $ccc_number;
            $patient->facility_id = $facility;
        } 
        
        $patient->patient_name = $patient_name;
        $patient->sex = $sex;
        $patient->initiation_date = $artStartDateInitial;
        $patient->dob = $birthday;
        $patient->save();

        $sample = new Viralsample;
        $sample->batch_id = $batch->id;
        $sample->patient_id = $patient->id;
        $sample->amrs_location = $amrs_location;
        $sample->provider_identifier = $provider_identifier;
        $sample->order_no = $order_no;
        $sample->age = $age;
        // $sample->pcrtype = 1;
        $sample->justification = $justification;
        $sample->prophylaxis = $artRegimen;
        $sample->save();

        $sample->load(['patient', 'batch']);
        return $sample;

    }

    public function complete_result(EidRequest $request)
    {
        $editted = $request->input('editted');
        $lab = $request->input('lab');
        $code = $request->input('mflCode');
        $specimenlabelID = $request->input('specimenlabelID');
        $specimenclientcode = $request->input('specimenclientcode');
        $datecollected = $request->input('datecollected');
        $gender = $request->input('gender');
        $dob = $request->input('dob');


        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_viralage($datecollected, $dob);
        $sex = Lookup::get_gender($gender);

        $sample_exists = ViralsampleView::sample($facility, $specimenclientcode, $datecollected)->first();
        $fields = Lookup::viralsamples_arrays();

        if($sample_exists && !$editted){

            return json_encode("VL CCC # {$specimenclientcode} collected on {$datecollected} already exists in database.");
        }

        if(!$editted){
            $batch = Viralbatch::existing($facility, $datereceived, $lab)->get()->first();

            if($batch && $batch->sample_count < 10){

            }
            else{
                $batch = new Batch;
            }

            $batch->lab_id = $lab;
            $batch->user_id = 66;
            $batch->facility_id = $facility;
            $batch->datereceived = $datereceived;
            $batch->datedispatched = $datedispatched;
            $batch->site_entry = 0;
            $batch->save();            
        }

        $patient = Viralpatient::existing($facility, $specimenclientcode)->get()->first();

        if(!$patient){
            $patient = new Viralpatient;
            $patient->patient = $specimenclientcode;
            $patient->facility_id = $facility;
        } 

        $patient->fill($request->only($fields['patient']));        
        $patient->sex = $sex;
        $patient->save();

        if($editted){
            $sample = Viralsample::find($sample_exists->id);
        }
        else{
            $sample = new Viralsample;
            $sample->batch_id = $batch->id;
            $sample->patient_id = $patient->id;
        }

        $sample->fill($request->only($fields['sample_api']));
        $sample->age = $age;
        $sample->justification = Lookup::justification($justification);
        $sample->prophylaxis = Lookup::viral_regimen($prophylaxis);
        $sample->comment = $specimenlabelID;
        $sample->dateapproved = $sample->dateapproved2 = $sample->datetested;
        $sample->synched = 5;
        $sample->save();

        $sample->load(['patient', 'batch']);
        return $sample;
    }




}
