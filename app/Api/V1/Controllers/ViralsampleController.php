<?php

namespace App\Api\V1\Controllers;

use App\Viralsample;
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
    public function show(Viralsample $viralsample)
    {
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
    public function update(ApiRequest $request, Viralsample $viralsample)
    {
        $fields = $request->input('sample');
        $site_entry = $request->input('site_entry');

        if($site_entry == 2 && $viralsample->batch->site_entry != 2) return $this->response->errorBadRequest("This sample does not exist here.");

        $viralsample->national_sample_id = $fields->id;

        $unset_array = ['id', 'batch_id', 'patient_id', 'original_sample_id', 'amrs_location'];

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
}
