<?php

namespace App\Api\V1\Controllers;

use App\Allocation;
use App\AllocationDetail;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\ApiRequest;

class AllocationController extends Controller
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
     * @param  \App\Allocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $sample = Sample::findOrFail($id);
        // $sample->load(['patient']);
        // $sample->batch;

        // return $sample;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Allocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function update(ApiRequest $request, $id)
    {
        // return response()->json([
        //     'message' => 'The update was successful.',
        //     'status_code' => 200,
        // ], 200);
    	$allocation = Allocation::findOrFail($id);
        $fields = json_decode($request->input('allocation'));

        $allocation->national_id = $fields->id;
        $unset_array = ['id', 'original_allocation_id', 'created_at', 'updated_at'];
        foreach ($unset_array as $value) {
            unset($fields->$value);
        }

        $allocation->fill(get_object_vars($fields));
        $allocation->synched = 1;
        $allocation->datesynched = date('Y-m-d');
        $allocation->save();

        return response()->json([
                'message' => 'The update was successful.',
                'status_code' => 200,
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Allocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sample $sample)
    {
        //
    }
}
?>