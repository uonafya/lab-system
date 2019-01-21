<?php

namespace App\Api\V1\Controllers;

use App\Viralpatient;
use Illuminate\Http\Request;

class ViralpatientController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Viralpatient  $viralpatient
     * @return \Illuminate\Http\Response
     */
    public function show(Viralpatient $viralpatient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Viralpatient  $viralpatient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Viralpatient $viralpatient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralpatient  $viralpatient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralpatient $viralpatient)
    {
        //
    }
}
