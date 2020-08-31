<?php

namespace App\Http\Controllers;

use App\UlizaTwgFeedback;
use App\UlizaClinicalForm;
use App\User;
use DB;
use Str;
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
        $view = true;
        if(Str::contains(url()->current(), ['create'])) $view = false;
        $reasons = DB::table('uliza_reasons')->orderBy('name', 'ASC')->get();
        $recommendations = DB::table('uliza_recommendations')->orderBy('name', 'ASC')->get();
        $feedbacks = DB::table('uliza_facility_feedbacks')->orderBy('name', 'ASC')->get();
        $regimens = DB::table('viralregimen')->get();
        $reviewers = User::where(['user_type_id' => 104, 'twg_id' => $ulizaClinicalForm->twg_id])->get();
        return view('uliza.clinical_review', compact('view', 'reasons', 'recommendations', 'feedbacks', 'regimens', 'ulizaClinicalForm', 'reviewers'));       
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
        $ulizaTwgFeedback->fill($request->except(['reviewer_id']));
        $ulizaTwgFeedback->user_id = auth()->user()->id;
        $ulizaTwgFeedback->save();

        $clinical_form = $ulizaTwgFeedback->clinical_form;
        $clinical_form->status_id = 2;
        if($ulizaTwgFeedback->recommendation_id == 3){
            if(auth()->user()->user_type_id == 103) $clinical_form->status_id = 3;
            else{
                $clinical_form->status_id = 4;
            }
        }
        if($request->input('reviewer_id')) $clinical_form->fill($request->only(['reviewer_id']));
        $clinical_form->save();
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
