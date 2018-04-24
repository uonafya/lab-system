<?php

namespace App\Http\Controllers;

use App\Sample;
use App\Patient;
use App\Mother;
use App\Batch;
use App\Facility;
use App\Lookup;
use DB;
use Illuminate\Http\Request;

class SampleController extends Controller
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
    public function create()
    {
        $data = Lookup::samples_form();
        return view('forms.samples', $data)->with('pageTitle', 'Add Sample');
    }

    public function create_poc()
    {
        $data = Lookup::samples_form();
        $data['poc'] = true;
        return view('forms.samples', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $samples_arrays = Lookup::samples_arrays();
        $submit_type = $request->input('submit_type');

        $batch = session('batch');

        if($submit_type == "cancel"){
            $batch->premature();
            $this->clear_session();
            return redirect()->route('sample.create');
        }        

        if(!$batch){
            $facility_id = $request->input('facility_id');
            $facility = Facility::find($facility_id);
            session(['facility_name' => $facility->name]);

            $batch = new Batch;
            $batch->user_id = auth()->user()->id;
            $batch->lab_id = auth()->user()->lab_id;

            if(auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4){
                $batch->received_by = auth()->user()->id;
                $batch->site_entry = 0;
            }
            else{
                $batch->site_entry = 1;
            }

            $data = $request->only($samples_arrays['batch']);
            $batch->fill($data);
            $batch->save();
            session(['batch' => $batch]);
        }

        $new_patient = $request->input('new_patient');

        if($new_patient == 0){
            $patient_id = $request->input('patient_id');
            $repeat_test = Sample::where(['patient_id' => $patient_id, 'batch_id' => $batch->id])->first();

            if($repeat_test){
                session(['toast_message' => 'The sample already exists in the batch and has therefore not been saved again']);
                return redirect()->route('sample.create');
            }

            $patient = Patient::find($patient_id);
            $data = $request->only($samples_arrays['patient']);
            $patient->fill($data);
            $patient->save();

            $data = $request->only($samples_arrays['sample']);
            $sample = new Sample;
            $sample->fill($data);
            $sample->batch_id = $batch->id;

            $sample->age = Lookup::calculate_age($request->input('datecollected'), $request->input('dob'));
            $sample->save();
        }

        else{

            $data = $request->only($samples_arrays['mother']);
            $mother = new Mother;
            $mother->fill($data);
            $mother->save();

            $data = $request->only($samples_arrays['patient']);
            $patient = new Patient;
            $patient->fill($data);
            $patient->mother_id = $mother->id;
            $patient->save();

            $data = $request->only($samples_arrays['sample']);
            $sample = new Sample;
            $sample->fill($data);
            $sample->patient_id = $patient->id;
            $sample->age = Lookup::calculate_age($request->input('datecollected'), $request->input('dob'));
            $sample->batch_id = $batch->id;
            $sample->save();

        }

        $submit_type = $request->input('submit_type');

        if($submit_type == "release"){
            $this->clear_session();
            $batch->premature();
        }

        $batch->refresh();

        if($batch->sample_count == 10){
            $this->clear_session();
            $batch->full_batch();
        }

        // return redirect()->route('sample.create');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function show(Sample $sample)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function edit(Sample $sample)
    {
        $sample->load(['patient.mother', 'batch']);
        $data = Lookup::samples_form();
        $data['sample'] = $sample;
        return view('forms.samples', $data)->with('pageTitle', 'Samples');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sample $sample)
    {
        $samples_arrays = Lookup::samples_arrays();
        $data = $request->only($samples_arrays['sample']);
        $sample->fill($data);
        // $sample->save();

        $batch = Batch::find($sample->batch_id);
        $data = $request->only($samples_arrays['batch']);
        $batch->fill($data);
        $batch->save();

        $new_patient = $request->input('new_patient');

        if($new_patient == 0){
        
            $data = $request->only($samples_arrays['patient']);
            $patient = Patient::find($sample->patient_id);
            $patient->fill($data);
            $patient->save();

            $data = $request->only($samples_arrays['mother']);
            $mother = Mother::find($patient->mother_id);
            $mother->fill($data);
            $mother->save();
        }
        else
        {
            $data = $request->only($samples_arrays['mother']);
            $mother = new Mother;
            $mother->fill($data);
            $mother->save();
            
            $data = $request->only($samples_arrays['patient']);
            $patient = new Patient;
            $patient->fill($data);
            $patient->mother_id = $mother->id;
            $patient->save();
        }
        
        $sample->age = Lookup::calculate_age($request->input('datecollected'), $request->input('dob'));
        $sample->patient_id = $patient->id;
        $sample->save();

        $site_entry_approval = session()->pull('site_entry_approval');

        if($site_entry_approval){
            return redirect('batch/site_approval/' . $batch->id);
        }

        return redirect('batch/' . $batch->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sample $sample)
    {
        if($sample->run != 1 && $sample->inworksheet == 0){
            $sample->delete();
        }        
        return back();
    }

    public function new_patient(Request $request)
    {
        $facility_id = $request->input('facility_id');
        $patient = $request->input('patient');

        $patient = Patient::where(['facility_id' => $facility_id, 'patient' => $patient])->first();
        $data;
        if($patient){
            $mother = $patient->mother;
            $data[0] = 0;
            $data[1] = $patient->toArray();
            $data[2] = $mother->toArray();

            $sample = Sample::select('id')->where(['patient_id' => $patient->id, 'result' => 2])->first();
            if($sample){
                $data[3] = ['previous_positive' => 1];
            }
            else{
                $data[3] = ['previous_positive' => 0];
            }
        }
        else{
            $data[0] = 1;
        }
        return $data;
    }

    public function runs(Sample $sample)
    {
        $samples = $sample->child;
        $sample->load(['patient']);
        return view('tables.sample_runs', ['sample' => $sample, 'samples' => $samples]);
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function individual(Sample $sample)
    {
        $batch = $sample->batch;
        $sample->load(['patient.mother']);
        $samples[0] = $sample;
        $batch->load(['facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

        return view('exports.samples', $data)->with('pageTitle', 'Individual Sample');
    }

    public function release_redraw(Sample $sample)
    {
        $sample->repeatt = 0;
        $sample->result = 5;
        $sample->approvedby = auth()->user()->id;
        $sample->approved2by = auth()->user()->id;
        $sample->dateapproved = date('Y-m-d');
        $sample->dateapproved2 = date('Y-m-d');
        $sample->save();
        $my = new \App\Misc;
        $my->check_batch($sample->batch_id);
        return back();
    }

    public function release_redraws(Request $request)
    {
        $samples_input = $request->input('samples');
        // DB::table('samples')->whereIn('id', $samples_input)->update(['repeatt' => 0, 'result' => 5, 'approvedby' => auth()->user()->id, 'dateapproved' => date('Y-m-d')]);

        $samples = Sample::whereIn('id', $samples_input)->get();

        foreach ($samples as $key => $sample) {
            $this->release_redraw($sample);
        }
        return back();
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $samples = Viralsample::whereRaw("id like '" . $search . "%'")->paginate(10);
        return $samples;
    }

    private function clear_session(){
        session()->forget('batch');
        session()->forget('facility_name');
        // session()->forget('batch_total');
        // session()->forget('batch_dispatch');
        // session()->forget('batch_dispatched');
        // session()->forget('batch_received');
        // session()->forget('facility_id');
    }
}
