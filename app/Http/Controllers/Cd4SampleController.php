<?php

namespace App\Http\Controllers;

use App\Cd4Sample;
use App\Cd4Patient;
use Illuminate\Http\Request;
use App\Lookup;

class Cd4SampleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Lookup::cd4_lookups();
        $data['samples'] = Cd4Sample::get();
        $data = (object) $data;
        // dd($data);
        return view('tables.cd4-samples', compact('data'))->with('pageTitle', 'Samples Summary');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Lookup::cd4sample_form();
        return view('forms.cd4samples', $data)->with('pageTitle', 'Add CD4 Sample');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $checknew = $request->input('new_patient');
        $sampleData = $request->except(['_token','medicalrecordno','patient_name','dob','sex','submit_type','new_patient']);
        $sample = new Cd4Sample();
        $sample->user_id = auth()->user()->id;
        if ($checknew == 0) {
            $patientData = $request->only(['medicalrecordno','patient_name','dob','sex']);
            $patient = new Cd4Patient();
            $patient->fill($patientData);
            $patient->save();
            $sampleData['patient_id'] = $patient->id;
        }

        if ($request->input('receivedstatus') == 2) { //If rejected set status to rejected also
            $sampleData['status_id'] = $request->input('receivedstatus');
        }
        
        $sample->fill($sampleData);
        $sample->save();
        if ($sample) {
            session(['toast_message'=>'Sample Created Successfully']);
        } else {
            session(['toast_message'=>'Sample creation failed', 'toast_error' => 1]);
        }
        
        if ($request->input('submit_type') == 'add') {
            return back();
        } else {
            return redirect('cd4/sample');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cd4Sample  $cd4Sample
     * @return \Illuminate\Http\Response
     */
    public function show(Cd4Sample $cd4Sample)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cd4Sample  $cd4Sample
     * @return \Illuminate\Http\Response
     */
    public function edit(Cd4Sample $cd4Sample)
    {
        $data = Lookup::cd4sample_form();
        $data['sample'] = $cd4Sample->first();
        
        return view('forms.cd4samples', $data)->with('pageTitle', 'Edit CD4 Sample');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cd4Sample  $cd4Sample
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cd4Sample $cd4Sample)
    {
        $sample = $cd4Sample->first(); // Get the sample collection

        //Differentiating the data sets
        $sampleData = $request->except(['_token','medicalrecordno','patient_name','dob','sex','submit_type','new_patient']);
        $patientData = $request->only(['medicalrecordno','patient_name','dob','sex']);

        //Check for changes in the patient data then update
        if ($patientData['medicalrecordno'] == $sample->patient->medicalrecordno) {
            $sample->patient->fill($patientData);
            $sample->patient->save();
        } else {
            $patient = new Cd4Patient();
            $patient->fill($patientData);
            $patient->save();
            $sampleData['patient_id'] = $patient->id;
        }

        if ($request->input('receivedstatus') == 2) { //If rejected set status to rejected also
            $sampleData['status_id'] = $request->input('receivedstatus');
        } else { //If accepted, check if previously rejected
            if($sample->receivedstatus == 2){
                $sampleData['status_id'] = 1;
                $sampleData['rejectedreason'] = null;
            }
        }
        
        //Update sample data
        $sample->fill($sampleData);
        $sample->save();

        if($sample){
            session(['toast_message'=>'Sample update Successful']);
        } else {
            session(['toast_message'=>'Sample update failed', 'toast_error'=>1]);
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cd4Sample  $cd4Sample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cd4Sample $cd4Sample)
    {
        //
    }
}
