<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\EidRequest;

use App\Lookup;
use App\SampleView;
use App\Batch;
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
        $hei_number = $request->input('patientId');
        $order_no = $request->input('order_no');
        $amrs_location = $request->input('amrs_location');
        $provider_identifier = $request->input('provider_identifier');
        $patient_name = $request->input('patient_name');
        $gender = $request->input('gender');
        $birthdate = $request->input('birthdate');
        $infantprophylaxis = $request->input('infantprophylaxis');
        $mother_prophylaxis = $request->input('pmtctIntervention');
        $feedingType = $request->input('feedingType');
        $entryPoint = $request->input('entryPoint');
        $mother_ccc = $request->input('mother_ccc');
        $motherHivStatus = $request->input('motherHivStatus');
        $dateDrawn = $request->input('dateDrawn');
        $spots = $request->input('numberOfSpots');
        $dateReceived = $request->input('dateReceived');
        $receivedStatus = $request->input('receivedStatus');
        $birthday = $request->input('birthday');

        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_age($dateDrawn, $birthday);
        $sex = Lookup::get_gender($gender);
        $lab = 5;

        $sample_exists = SampleView::sample($facility, $hei_number, $dateDrawn)->first();

        if($sample_exists){
            return json_encode("EID HEI Number {$hei_number} collected on {$dateDrawn} already exists in database.");
        }

        $batch = Batch::existing($facility, $dateReceived, $lab)->get()->first();

        if($batch && $batch->sample_count < 10){

        }
        else{
            $batch = new Batch;
        }

        $batch->lab_id = $lab;
        $batch->facility_id = $facility;
        $batch->datereceived = $dateReceived;
        $batch->user_id = 66;
        $batch->site_entry = 0;
        $batch->save();

        $patient = Patient::existing($facility, $hei_number)->get()->first();

        if($patient){
            $mom = $patient->mother;
        } 
        else{
            $patient = new Patient;
            $patient->patient = $hei_number;
            $patient->facility_id = $facility;
            $mom = new Mother;
        }

        $mom->ccc_no = $mother_ccc;
        $mom->facility_id = $facility;
        $mom->hiv_status = $motherHivStatus;
        $mom->save();
        
        $patient->mother_id = $mom->id;
        $patient->patient_name = $patient_name;
        $patient->entry_point = $entryPoint;
        $patient->sex = $sex;
        $patient->dob = $birthday;
        $patient->save();

        $sample = new Sample;
        $sample->batch_id = $batch->id;
        $sample->patient_id = $patient->id;
        $sample->amrs_location = $amrs_location;
        $sample->provider_identifier = $provider_identifier;
        $sample->order_no = $order_no;
        $sample->age = $age;
        // $sample->pcrtype = 1;
        $sample->regimen = $infantprophylaxis;
        $sample->mother_prophylaxis = $mother_prophylaxis;
        $sample->feeding = $feedingType;
        $sample->spots = $spots;

        $sample->save();

        $sample->load(['patient.mother', 'batch']);
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
        
        $ccc_no = $request->input('ccc_no');
        $hiv_status = $request->input('hiv_status');

        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_age($datecollected, $dob);
        $sex = Lookup::get_gender($gender);

        $sample_exists = SampleView::sample($facility, $specimenclientcode, $datecollected)->first();
        $fields = Lookup::samples_arrays();

        if($sample_exists && !$editted){
            return json_encode("VL CCC # {$ccc_number} collected on {$datecollected} already exists in database.");
        }

        if(!$editted){
            $batch = Batch::existing($facility, $datereceived, $lab)->get()->first();

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

        $patient = Patient::existing($facility, $specimenclientcode)->get()->first();

        if($patient){
            $mom = $patient->mother;
        } 
        else{
            $patient = new Patient;
            $patient->patient = $specimenclientcode;
            $patient->facility_id = $facility;
            $mom = new Mother;
        }

        $mom->ccc_no = $ccc_no;
        $mom->facility_id = $facility;
        $mom->hiv_status = $hiv_status;
        $mom->save();

        $patient->fill($request->only($fields['patient']));
        $patient->mother_id = $mom->id;      
        $patient->sex = $sex;
        $patient->save();

        if($editted){
            $sample = Sample::find($sample_exists->id);
        }
        else{
            $sample = new Sample;
            $sample->batch_id = $batch->id;
            $sample->patient_id = $patient->id;
        }

        $sample->fill($request->only($fields['sample_api']));
        $sample->age = $age;
        $sample->comment = $specimenlabelID;
        $sample->dateapproved = $sample->dateapproved2 = $sample->datetested;
        $sample->synched = 5;
        $sample->save();

        $sample->load(['patient.mother', 'batch']);
        return $sample;
    }




}
