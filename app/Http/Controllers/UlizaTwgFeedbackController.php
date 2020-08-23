<?php

namespace App\Http\Controllers;

use App\UlizaTwgFeedback;
use App\UlizaClinicalForm;
use DB;
use Illuminate\Http\Request;

class UlizaTwgFeedbackController extends Controller
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
    public function create(UlizaClinicalForm $ulizaClinicalForm)
    {
        $reasons = DB::table('uliza_reasons')->get();
        $regimens = DB::table('viralregimen')->get();
        return view('uliza.clinical_review', compact('reasons', 'regimens', 'ulizaClinicalForm'));       
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
     * @param  \App\UlizaTwgFeedback  $ulizaTwgFeedback
     * @return \Illuminate\Http\Response
     */
    public function show(UlizaTwgFeedback $ulizaTwgFeedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UlizaTwgFeedback  $ulizaTwgFeedback
     * @return \Illuminate\Http\Response
     */
    public function edit(UlizaTwgFeedback $ulizaTwgFeedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UlizaTwgFeedback  $ulizaTwgFeedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UlizaTwgFeedback $ulizaTwgFeedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UlizaTwgFeedback  $ulizaTwgFeedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(UlizaTwgFeedback $ulizaTwgFeedback)
    {
        //
    }
}
