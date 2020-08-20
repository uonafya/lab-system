<?php

namespace App\Http\Controllers;

use App\DrTwgFeedback;
use App\DrClinicalForm;
use DB;
use Illuminate\Http\Request;

class DrTwgFeedbackController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(DrClinicalForm $drClinicalForm)
    {
        $reasons = DB::table('uliza_reasons')->get();
        $regimens = DB::table('viralregimen')->get();
        return view('uliza.clinical_review', compact('reasons', 'regimens', 'drClinicalForm'));       
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
     * @param  \App\DrTwgFeedback  $drTwgFeedback
     * @return \Illuminate\Http\Response
     */
    public function show(DrTwgFeedback $drTwgFeedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrTwgFeedback  $drTwgFeedback
     * @return \Illuminate\Http\Response
     */
    public function edit(DrTwgFeedback $drTwgFeedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrTwgFeedback  $drTwgFeedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrTwgFeedback $drTwgFeedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrTwgFeedback  $drTwgFeedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrTwgFeedback $drTwgFeedback)
    {
        //
    }
}
