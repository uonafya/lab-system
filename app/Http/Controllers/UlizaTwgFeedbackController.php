<?php

namespace App\Http\Controllers;

use App\UlizaTwgFeedback;
use App\UlizaClinicalForm;
use App\User;
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
        $reasons = DB::table('uliza_reasons')->orderBy('name', 'ASC')->get();
        $recommendations = DB::table('uliza_recommendations')->orderBy('name', 'ASC')->get();
        $regimens = DB::table('viralregimen')->get();
        $reviewers = User::where(['user_type_id' => 104, 'twg_id' => $ulizaClinicalForm->twg_id])->get();
        return view('uliza.clinical_review', compact('reasons', 'recommendations', 'regimens', 'ulizaClinicalForm', 'reviewers'));       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ulizaTwgFeedback = UlizaTwgFeedback::where($request->only(['uliza_clinical_form_id']))->first();
        if(!$ulizaTwgFeedback) $ulizaTwgFeedback = new UlizaTwgFeedback;
        $ulizaTwgFeedback->fill($request->all());
        $ulizaTwgFeedback->user_id = auth()->user()->id;
        $ulizaTwgFeedback->save();
        session(['toast_message' => 'The feedback has been saved.']);
        return redirect('uliza-form');

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
