<?php

namespace App\Http\Controllers;

use App\Patient;
use App\Sample;
use App\Lookup;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($facility_id=null)
    {
        $user = auth()->user();
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;

        // if(!$facility_id && !$facility_user) abort(404);

        $patients = Patient::with(['facility'])
        ->with(['sample' => function ($query){
            $query->where('repeatt', 0);
        } ])
        ->when($facility_user, function($query) use ($user){
            return $query->where('facility_id', $user->facility_id);
        })
        ->when($facility_id, function($query) use ($facility_id){
            return $query->where('facility_id', $facility_id);
        })
        ->get();

        $data = Lookup::get_lookups();
        $data['patients'] = $patients;
        return view('tables.patients', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
     * @param  \App\Patient  $patient
     * @return \Illuminate\Http\Response
     */
    public function show(Patient $patient)
    {
        $samples = $patient->sample;
        $patient->load(['facility']);
        $samples->load(['batch']);
        $data = Lookup::get_lookups();
        $data['samples'] = $samples;
        $data['patient'] = $patient;

        return view('tables.patient_samples', $data)->with('pageTitle', 'Patients');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Patient  $patient
     * @return \Illuminate\Http\Response
     */
    public function edit(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Patient  $patient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Patient $patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Patient  $patient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Patient $patient)
    {
        //
    }

    public function merge(Patient $patient)
    {
        $patient->load(['facility']);
        $url = url('patient/search/' . $patient->facility->id);
        // $submit_url = url('patient/merge/');
        $submit_url = url()->current();
        return view('forms.merge_patients', ['patient' => $patient, 'url' => $url, 'submit_url' => $submit_url]);
    }

    public function merge_patients(Request $request, Patient $patient)
    {
        $patients = $request->input('patients');

        $samples = Sample::whereIn('patient_id', $patients)->get();

        foreach ($samples as $key => $sample) {
            $sample->patient_id = $patient->id;
            $sample->pre_update();
        }

        $patient_array = Patient::whereIn('id', $patients)->update(['synched' => 3]);

        session(['toast_message' => "The patient records have been merged. The records will be propagated to the national database and then they will be removed."]);

        return redirect('patient/index/' . $patient->facility_id);
    }

    public function transfer(Patient $patient)
    {
        $patient->load(['facility']);
        $submit_url = url()->current();
        return view('forms.transfer_patient', ['patient' => $patient, 'submit_url' => $submit_url]);
    }

    public function transfer_patient(Request $request, Patient $patient)
    {
        $prev_facility_id = $patient->facility_id;
        $patient->facility_id = $request->input('facility_id');
        $patient->pre_update();

        session(['toast_message' => "The patient has been transferred to another facility."]);

        return redirect('patient/index/' . $prev_facility_id);
    }

    public function search(Request $request, $facility_id=null)
    {
        $user = auth()->user();
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(facility_id='{$user->facility_id}')";

        $search = $request->input('search');
        $patients = Patient::select('id', 'patient')
            ->whereRaw("patient like '" . $search . "%'")
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('facility_id', $facility_id);
            })
            ->paginate(10);
        return $patients;
    }
}
