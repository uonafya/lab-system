<?php

namespace App\Api\V1\Controllers;

use App\CovidPatient;
use App\CovidSample;
use App\CovidTravel;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\ApiRequest;

class CovidController extends Controller
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
        unset($patient_details['id']);
        unset($patient_details['original_patient_id']);
        $p->fill($patient_details);
        $p->save();

        unset($sample->patient);

        $s = new CovidSample;
        $s->fill(get_object_vars($sample));
        $s->patient_id = $p->id;
        $s->national_sample_id = $s->id;
        unset($s->id);
        unset($s->national_sample_id);
        $s->save();

        return response()->json([
                'ok' => $ok,
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
    public function show(Batch $batch)
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
        $batch = Batch::findOrFail($id);
        $fields = json_decode($request->input('batch'));
        $site_entry = $request->input('site_entry');

        if($site_entry == 2 && $batch->site_entry != 2) return $this->response->errorBadRequest("This batch does not exist here.");

        $unset_array = ['id', 'original_batch_id', 'sent_email', 'dateindividualresultprinted', 'datebatchprinted', 'dateemailsent', 'printedby'];

        foreach ($unset_array as $value) {
            unset($fields->$value);
        }

        $batch->fill(get_object_vars($fields));

        $batch->synched = 1;
        $batch->datesynched = date('Y-m-d');
        $batch->save();

        return response()->json([
                'message' => 'The update was successful.',
                'status_code' => 200,
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Batch $batch)
    {
        //
    }
}
