<?php

namespace App\Api\V1\Controllers;

use App\Viralsample;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\ApiRequest;

class ViralsampleController extends Controller
{
    use Dingo\Api\Routing\Helpers;
    
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
        //
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
