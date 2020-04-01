<?php

namespace App\Api\V1\Controllers;

use App\CovidPatient;
use App\CovidSample;
use App\CovidTravel;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\ApiRequest;

class CovidSampleController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ApiRequest $request)
    {
        $sample = json_decode($request->input('sample'));
        $p = CovidPatient::where('national_patient_id', $sample->patient->id)->first();
        if(!$p){
            $p = new CovidPatient;
        }
        $patient_details = get_object_vars($sample->patient);
        $p->national_patient_id = $patient_details['id'];
        unset($patient_details['original_patient_id']);
        unset($patient_details['cif_patient_id']);
        $p->fill($patient_details);
        $p->save();

        unset($sample->patient);

        $s = new CovidSample;
        $s->fill(get_object_vars($sample));
        $s->patient_id = $p->id;
        $s->national_sample_id = $sample->id;
        unset($s->original_sample_id);
        unset($s->cif_sample_id);
        $s->save();

        return response()->json([
                'status' => 'ok',
                'sample' => $s,
                'patient' => $p,
                'message' => 'The transfer was successful.',
                'status_code' => 201,
            ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function update(ApiRequest $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
