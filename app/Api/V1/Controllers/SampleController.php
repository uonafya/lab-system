<?php

namespace App\Api\V1\Controllers;

use App\Sample;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\ApiRequest;

class SampleController extends Controller
{
    // use \Dingo\Api\Routing\Helpers;
    
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
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    // public function show(Sample $sample)
    public function show($id)
    {
        // $sample->load(['patient']);
        // $sample->batch;

        return [
            'id' => $id
        ];

        return $sample->toArray();

        return [
            'sample' => $sample->toJson(),
            'message' => 'The fetch was successful.',
            'status_code' => 200,
        ];

        return response()->json([
                'sample' => $sample->toJson(),
                'message' => 'The fetch was successful.',
                'status_code' => 200,
            ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function update(ApiRequest $request, Sample $sample)
    {
        $fields = $request->input('sample');
        $site_entry = $request->input('site_entry');

        if($site_entry == 2 && $sample->batch->site_entry != 2) return $this->response->errorBadRequest("This sample does not exist here.");

        $sample->national_sample_id = $fields->id;

        $unset_array = ['id', 'batch_id', 'patient_id', 'original_sample_id', 'amrs_location'];

        foreach ($unset_array as $value) {
            unset($fields->$value);
        }

        $sample->fill(get_object_vars($fields));

        $sample->synched = 1;
        $sample->datesynched = date('Y-m-d');
        $sample->save();

        return response()->json([
                'message' => 'The update was successful.',
                'status_code' => 200,
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sample $sample)
    {
        //
    }
}
