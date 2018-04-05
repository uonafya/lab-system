<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\EidRequest;
use DB;

use App\Lookup;
use App\SampleView;
use App\Batch;
use App\Sample;receivedStatus
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
        $patient = $request->input('patientId');
        $order_no = $request->input('order_no');
        $provider_identifier = $request->input('provider_identifier');
        $patient_name = $request->input('patient_name');
        $gender = $request->input('gender');
        $birthdate = $request->input('birthdate');
        $infantprophylaxis = $request->input('infantprophylaxis');
        $pmtctIntervention = $request->input('pmtctIntervention');
        $feedingType = $request->input('feedingType');
        $entryPoint = $request->input('entryPoint');
        $motherHivStatus = $request->input('motherHivStatus');
        $dateDrawn = $request->input('dateDrawn');
        $numberOfSpots = $request->input('numberOfSpots');
        $dateReceived = $request->input('dateReceived');
        $receivedStatus = $request->input('receivedStatus');
        $birthday = $request->input('birthday');

        $facility = Lookup::facility_mfl($code);
        $age = Lookup::calculate_age($dateDrawn, $birthday);
        $sex = Lookup::get_gender($gender);
        $lab = 5;

        $double = SampleView::sample($facility, $patient, $dateDrawn)->first();

        if($double){
            return json_encode("EID HEI Number {$patient} collected on {$dateDrawn} already exists in database.");
        }

        
    }




}
