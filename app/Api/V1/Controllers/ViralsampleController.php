<?php

namespace App\Api\V1\Controllers;

use App\ViralsampleView;
use App\Viralsample;
use App\Viralbatch;
use App\Viralpatient;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\ApiRequest;

class ViralsampleController extends Controller
{
    use \Dingo\Api\Routing\Helpers;
    
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    // public function show(Viralsample $viralsample)
    public function show($id)
    {
        $viralsample = Viralsample::findOrFail($id);
        $viralsample->load(['patient']);
        $viralsample->batch;

        return $viralsample;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function update(ApiRequest $request, $id)
    {
        $viralsample = Viralsample::findOrFail($id);
        $fields = json_decode($request->input('sample'));
        $site_entry = $request->input('site_entry');

        if($site_entry == 2 && $viralsample->batch->site_entry != 2) return $this->response->errorBadRequest("This sample does not exist here.");

        $viralsample->national_sample_id = $fields->id;

        $unset_array = ['id', 'batch_id', 'patient_id', 'original_sample_id', 'old_id', 'amrs_location', 'nhrlpoceqa', 'previous_nonsuppressed'];

        foreach ($unset_array as $value) {
            unset($fields->$value);
        }

        $viralsample->fill(get_object_vars($fields));

        $viralsample->synched = 1;
        $viralsample->datesynched = date('Y-m-d');
        $viralsample->save();

        return response()->json([
                'message' => 'The update was successful.',
                'status_code' => 200,
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralsample $viralsample)
    {
        //
    }

    public function transfer(ApiRequest $request)
    {        
        $new_samples = json_decode($request->input('samples'));

        $ok = $samples = $batches = $patients = $others = [];

        foreach ($new_samples as $key => $new_sample) {

            // $new_sample = json_decode(json_encode($new_sample), true);
            // $others[] = $new_sample;
            // $others[] = $new_sample['batch'];
            // break; 

            $existing = ViralsampleView::sample($new_sample->batch->facility_id, $new_sample->patient->patient, $new_sample->datecollected)->first();
            if($existing){
                $ok[] = $new_sample->id;
                continue;
            }

            $user = $new_sample->batch->creator ?? null;
            $user_id = 20000;
            if($new_sample->batch->site_entry && $user){
                $user_id = \App\User::where('facility_id', $user->facility_id)->first()->id ?? 20000;
            }
            unset($new_sample->batch->creator); 

            $b = new Viralbatch;
            // $others[] = get_object_vars($new_sample->batch);
            // $others[] = gettype(get_object_vars($new_sample->batch));
            $b->fill(get_object_vars($new_sample->batch));
            // $b->facility_id = $new_sample->batch->facility_id;
            $b->user_id = $user_id;
            unset($b->id);
            $b->save();
            unset($new_sample->batch);

            $new_patient = false;
            $p = Viralpatient::existing($new_sample->patient->facility_id, $new_sample->patient->patient)->first();
            if(!$p){
                $p = new Viralpatient;
                $new_patient = true;
            }
            // $others[] = get_object_vars($new_sample->patient);
            // $others[] = gettype(get_object_vars($new_sample->patient));
            $p->fill(get_object_vars($new_sample->patient));
            if($new_patient) unset($p->id);
            $p->save();
            unset($new_sample->patient);

            $s = new Viralsample;
            // $others[] = get_object_vars($new_sample);
            // $others[] = gettype(get_object_vars($new_sample));
            $s->fill(get_object_vars($new_sample));
            $s->batch_id = $b->id;
            $s->patient_id = $p->id;
            unset($s->id);
            $s->save();

            $patients[] = $p;
            $batches[] = $b;
            $samples[] = $s;

            $ok[] = $new_sample->id;
            $others[] = get_class($b);
            $others[] = get_class($p);
            $others[] = get_class($s);
        }

        return response()->json([
                'ok' => $ok,
                'samples' => $samples,
                'batches' => $batches,
                'patients' => $patients,
                'others' => $others,
                'message' => 'The transfer was successful.',
                'status_code' => 201,
            ], 201);

    }
}
