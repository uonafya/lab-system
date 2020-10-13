<?php

namespace App\Http\Controllers;

use App\UlizaTwgFeedback;
use App\UlizaClinicalForm;
use App\UlizaAdditionalInfo;
use App\User;
use DB;
use Str;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\UlizaMail;

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
        $clinical_form = UlizaClinicalForm::find($request->input('uliza_clinical_form_id'));
        $ulizaTwgFeedback = $clinical_form->feedback;
        // $ulizaTwgFeedback = UlizaTwgFeedback::where($request->only(['uliza_clinical_form_id']))->first();
        if(!$ulizaTwgFeedback) $ulizaTwgFeedback = new UlizaTwgFeedback;
        $ulizaTwgFeedback->fill($request->except(['reviewer_id', 'requested_info']));
        $ulizaTwgFeedback->user_id = auth()->user()->id;
        $ulizaTwgFeedback->save();

        $twg = $clinical_form->twg;

        // $clinical_form = $ulizaTwgFeedback->clinical_form;
        $clinical_form->status_id = 2;
        if($ulizaTwgFeedback->recommendation_id == 3 && auth()->user()->user_type_id < 104) $clinical_form->status_id = 4;
        if(auth()->user()->user_type_id == 104) $clinical_form->status_id = 3;
        if($request->input('reviewer_id')) $clinical_form->fill($request->only(['reviewer_id']));
        $clinical_form->save();
        session(['toast_message' => 'The feedback has been saved.']);

        if($request->input('reviewer_id')){
            Mail::to([$clinical_form->reviewer->email])->send(new UlizaMail($clinical_form, 'case_referral', 'NASCOP ' . $clinical_form->subject_identifier));
        }

        if($request->input('requested_info')){
            // Mail::to([$clinical_form->reviewer->email])->send(new UlizaMail($clinical_form, 'additional_info', 'NASCOP ' . $form->subject_identifier));
            $ulizaAdditionalInfo = new UlizaAdditionalInfo;
            $ulizaAdditionalInfo->requested_info = $request->input('requested_info');
            $ulizaAdditionalInfo->uliza_clinical_form_id = $clinical_form->id;
            $ulizaAdditionalInfo->save();

            if($ulizaTwgFeedback->recommendation_id == 1){
                Mail::to([$clinical_form->facility_email])->send(new UlizaMail($clinical_form, 'additional_info', 'Clinical Summary Form Additional Information Notification ' . $clinical_form->subject_identifier, $ulizaAdditionalInfo));
            }            
            else if($ulizaTwgFeedback->recommendation_id == 5){
                Mail::to($twg->email_array)->send(new UlizaMail($clinical_form, 'additional_info_twg', 'Clinical Summary Form Additional Information Notification ' . $clinical_form->subject_identifier, $ulizaAdditionalInfo));
            }
        }

        // Technical reviewer has given recommendations
        if($ulizaTwgFeedback->recommendation_id == 6){
            Mail::to($twg->email_array)->send(new UlizaMail($clinical_form, 'technical_feedback_provided', $clinical_form->subject_identifier));
        }

        // Feedback is given to the facility
        if($ulizaTwgFeedback->recommendation_id == 3){
            if($ulizaTwgFeedback->facility_recommendation_id == 4){
                Mail::to([$clinical_form->facility_email])->send(new UlizaMail($clinical_form, 'additional_info_twg', 'DRT Approved by NASCOP ' . $clinical_form->subject_identifier));
            }
            else{
                Mail::to([$clinical_form->facility_email])->send(new UlizaMail($clinical_form, 'feedback_facility', 'NASCOP Feedback For ' . $clinical_form->subject_identifier));
            }
        }

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
