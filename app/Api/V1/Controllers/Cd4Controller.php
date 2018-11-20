<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Controllers\BaseController;
use App\Api\V1\Requests\Cd4Request;

use App\Lookup;
use App\Cd4Patient;
use App\Cd4Sample;
use App\Cd4SampleView;


class Cd4Controller extends BaseController
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

    public function partial(Cd4Request $request)
    {
        $code = $request->input('mflCode');
        $datecollected = $request->input('datecollected');
        $order_no = $request->input('order_no');
        $dob = $request->input('dob');
        $lab = $request->input('lab') ?? env('APP_LAB');

        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_viralage($datecollected, $dob);
        // $sex = Lookup::get_gender($gender);

        $sample_exists = Cd4SampleView::where(['order_no' => $order_no])->first();
        $fields = Lookup::viralsamples_arrays();

        if($sample_exists) return $this->response->errorBadRequest("This sample already exists.");

        $patient = new Cd4Patient;
        $patient->patient_name = $request->input('patient_name');
        $patient->medicalrecordno = $request->input('medicalrecordno');
        $patient->dob = $request->input('dob');
        $patient->sex = $request->input('sex');
        $patient->save();

        $sample = new Cd4Sample;
        $sample->patient_id = $patient->id;
        $sample->facility_id = $facility;
        $sample->lab_id = $lab;
        $sample->order_no = $order_no;
        $sample->age = $age;
        $sample->status_id = 1;
        $sample->datecollected = $datecollected;
        $sample->serial_no = $request->input('serial_no', 0);
        $sample->amrs_location = $request->input('amrs_location');
        $sample->provider_identifier = $request->input('provider_identifier');
        $sample->save();
        $sample->load(['patient']);
        return $sample;
    }

    public function complete_result(Cd4Request $request)
    {
        $code = $request->input('mflCode');
        $datecollected = $request->input('datecollected');
        $order_no = $request->input('order_no');
        $dob = $request->input('dob');
        $lab = $request->input('lab') ?? env('APP_LAB');

        $editted = $request->input('editted');

        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_viralage($datecollected, $dob);
        // $sex = Lookup::get_gender($gender);

        $sample_exists = Cd4SampleView::where(['order_no' => $order_no])->first();
        $fields = Lookup::viralsamples_arrays();

        if($sample_exists && !$editted) return $this->response->errorBadRequest("This sample already exists.");

        $patient = new Cd4Patient;
        $patient->patient_name = $request->input('patient_name');
        $patient->medicalrecordno = $request->input('medicalrecordno');
        $patient->dob = $request->input('dob');
        $patient->sex = $request->input('sex');
        $patient->save();

        $sample = new Cd4Sample;

        if($editted) $sample = Cd4Sample::where(['order_no' => $order_no])->first();

        $sample->patient_id = $patient->id;
        $sample->facility_id = $facility;
        $sample->lab_id = $lab;
        $sample->order_no = $order_no;
        $sample->age = $age;
        $sample->status_id = 1;
        $sample->datecollected = $datecollected;
        $sample->serial_no = $request->input('serial_no', 0);
        $sample->amrs_location = $request->input('amrs_location');
        $sample->provider_identifier = $request->input('provider_identifier');
        $sample->datedispatched = $request->input('datedispatched');

        if($sample->datedispatched){
            $sample->status_id = 6; 
            $sample->dateapproved = $sample->datedispatched;
        }
        $sample->save();
        $sample->load(['patient']);
        return $sample;
    }


}
