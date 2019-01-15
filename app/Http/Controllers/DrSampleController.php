<?php

namespace App\Http\Controllers;

use App\DrSample;
use App\DrPatient;
use App\User;
use App\Lookup;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\DrugResistance;


class DrSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Lookup::get_dr();
        $data['dr_samples'] = DrSample::with(['patient.facility'])->paginate();
        $data['dr_samples']->setPath(url()->current());
        return view('tables.dr_samples', $data)->with('pageTitle', 'Drug Resistance Samples');        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Lookup::get_dr();
        return view('forms.dr_samples', $data)->with('pageTitle', 'Drug Resistance Samples');  
    }

    public function create_from_patient(DrPatient $patient)
    {        
        $data = $patient->only(['patient_id', 'dr_reason_id']);
        $data['user_id'] = auth()->user()->id;
        // $sample = DrSample::create($data);
        $sample = new DrSample;
        $sample->fill($data);
        $facility = $sample->patient->facility;
        $sample->facility_id = $facility_id;
        $sample->save();      

        $patient->status_id=2;
        $patient->save();

        // if($facility->email_array)
        {
            $mail_array = ['joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com', 'jlusike@clintonhealthaccess.org'];
            // if(env('APP_ENV') == 'production') $mail_array = [$facility->email];
            Mail::to($mail_array)->send(new DrugResistance($sample));
            session(['toast_message' => 'The sample has been created and the email has been sent to the facility.']);
        // }  
        // else
        // {
        //     session(['toast_message' => 'The sample has been created but the email has not been sent to the facility because the facility does not have an email address in the system.'])
        // } 

        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $drSample = new DrSample;
        if($request->input('submit_type') == 'cancel') return back();
        $viralsamples_arrays = Lookup::viralsamples_arrays();
        $data = $request->only($viralsamples_arrays['dr_sample']);
        $data['user_id'] = auth()->user()->id;
        if(auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4) $data['received_by'] = auth()->user()->id;
        $drSample->fill($data);

        $others = $request->input('other_medications_text');
        $other_medications = $request->input('other_medications');
        $others = explode(',', $others);
        $drSample->other_medications = array_merge($other_medications, $others);
        $drSample->save();

        session(['toast_message' => 'The sample has been created.']);
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrSample  $drSample
     * @return \Illuminate\Http\Response
     */
    public function show(DrSample $drSample)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrSample  $drSample
     * @return \Illuminate\Http\Response
     */
    public function edit(DrSample $drSample)
    {
        $drSample->load(['patient.facility']);
        $data = Lookup::get_dr();
        $data['sample'] = $drSample;
        return view('forms.dr_samples', $data)->with('pageTitle', 'Drug Resistance Samples');  
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrSample  $drSample
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrSample $drSample)
    {
        if($request->input('submit_type') == 'cancel') return redirect('/dr_sample');
        $viralsamples_arrays = Lookup::viralsamples_arrays();
        $data = $request->only($viralsamples_arrays['dr_sample']);

        if((auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4) && !$drSample->received_by){
            $data['received_by'] = auth()->user()->id;
        }

        $drSample->fill($data);

        $others = $request->input('other_medications_text');
        $other_medications = $request->input('other_medications');
        $others = explode(',', $others);
        $drSample->other_medications = array_merge($other_medications, $others);
        $drSample->save();

        session(['toast_message' => 'The sample has been updated.']);
        return redirect('/dr_sample');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrSample  $drSample
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrSample $drSample)
    {
        //
    }

    public function facility_edit(Request $request, User $user, DrSample $sample)
    {
        if(Auth::user()) Auth::logout();
        Auth::login($user);

        $fac = \App\Facility::find($user->facility_id);
        session(['logged_facility' => $fac]);

        $sample->load(['patient.facility']);
        $data = Lookup::get_dr();
        $data['sample'] = $sample;
        // dd($request);
        return view('forms.dr_samples', $data)->with('pageTitle', 'Edit Drug Resistance Sample');
    }
}
