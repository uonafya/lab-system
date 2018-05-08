<?php

namespace App\Http\Controllers;

use App\Sample;
use App\SampleView;
use App\Patient;
use App\Mother;
use App\Batch;
use App\Facility;
use App\Lookup;

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
            session(['facility_name' => $facility->name, 'batch_total' => 0]);

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
                session(['toast_error' => 1]);
                return redirect()->route('sample.create');
            }

            $patient = Patient::find($patient_id);
            $data = $request->only($samples_arrays['patient']);
            $patient->fill($data);
            $patient->pre_update();

            $data = $request->only($samples_arrays['mother']);
            $mother = Mother::find($patient->mother_id);
            $mother->dob = Lookup::calculate_mother_dob($request->input('datecollected'), $request->input('mother_age'));
            $mother->fill($data);
            $mother->pre_update();

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
            $mother->dob = Lookup::calculate_mother_dob($request->input('datecollected'), $request->input('mother_age'));
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

        $sample_count = session('batch_total') + 1;
        session(['batch_total' => $sample_count]);

        if($sample_count == 10){
            $this->clear_session();
            $batch->full_batch();
        }

        session(['toast_message' => 'The sample has been created.']);

        // return redirect()->route('sample.create');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function show(SampleView $sample)
    {
        // $samples->load(['patient.mother']);
        // $batch->load(['facility', 'receiver', 'creator']);
        // $data = Lookup::get_lookups();
        // $data['batch'] = $batch;
        // $data['samples'] = $samples;

        $data = Lookup::get_lookups();
        // dd($sample);
        $data['samples'] = $sample;
        return view('tables.sample_search', $data)->with('pageTitle', 'Sample Summary');
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
        $batch->pre_update();

        $new_patient = $request->input('new_patient');

        if($new_patient == 0){
        
            $data = $request->only($samples_arrays['patient']);
            $patient = Patient::find($sample->patient_id);
            $patient->fill($data);
            $patient->pre_update();

            $data = $request->only($samples_arrays['mother']);
            $mother = Mother::find($patient->mother_id);
            $mother->dob = Lookup::calculate_mother_dob($request->input('datecollected'), $request->input('mother_age'));
            $mother->fill($data);
            $mother->pre_update();
        }
        else
        {
            $data = $request->only($samples_arrays['mother']);
            $mother = new Mother;
            $mother->dob = Lookup::calculate_mother_dob($request->input('datecollected'), $request->input('mother_age'));
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
        $sample->pre_update();

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
        if($sample->worksheet_id == NULL && $sample->result == NULL){
            $sample->delete();
            session(['toast_message' => 'The sample has been deleted.']);
        }  
        else{
            session(['toast_message' => 'The sample has not been deleted.']);
            session(['toast_error' => 1]);
        }      
        return back();
    }

    public function new_patient(Request $request)
    {
        $facility_id = $request->input('facility_id');
        $patient = $request->input('patient');

        // Add check for in process sample

        $patient = Patient::where(['facility_id' => $facility_id, 'patient' => $patient])->first();
        $data;
        if($patient){
            $mother = $patient->mother;
            $mother->calc_age();
            $data[0] = 0;
            $data[1] = $patient->toArray();
            $data[2] = $mother->toArray();

            $prev_samples = Sample::where(['patient_id' => $patient->id, 'repeatt' => 0])->orderBy('datetested', 'asc')->get();
            $previous_positive = 0;
            $recommended_pcr = 1;
            $message = null;
            $error_message = null;
            $age = $patient->age;

            if($prev_samples->count() > 0){
                $pos_sample = $prev_samples->where('result', 2)->first();
                if($pos_sample){
                    $previous_positive = 1;
                    $recommended_pcr = 4;

                    $bool = false;
                    foreach ($prev_samples as $key => $sample) {
                        if($sample->result == 2) $bool = true;
                        if($bool && $sample->result == 1) $recommended_pcr = 5;
                    }
                }
                else{
                    if($age < 12){
                        $recommended_pcr = 2;
                    }
                    else{
                        $recommended_pcr = 3;
                    }
                }                
            }

            if($age > 24) $error_message = "The patient is over age.";

            $data[3] = ['previous_positive' => $previous_positive, 'recommended_pcr' => $recommended_pcr, 'message' => $message, 'error_message' => $error_message];
        }
        else{
            $data[0] = 1;
        }
        return $data;
    }

    public function runs(Sample $sample)
    {
        $samples = Sample::runs($sample)->orderBy('run', 'asc')->get();

        $patient = $sample->patient; 
        return view('tables.sample_runs', ['patient' => $patient, 'samples' => $samples]); 
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
        if($sample->run == 1){
            session(['toast_message' => 'The sample cannot be released as a redraw.']);
            session(['toast_error' => 1]);
            return back();
        } 
        else if($sample->run == 2){
            // $prev_sample = Sample::find($sample->parentid);
            $prev_sample = $sample->parent;
        }
        else{
            $run = $sample->run - 1;
            $prev_sample = Sample::where(['parentid' => $sample->parentid, 'run' => $run])->get()->first();
        }
        
        $sample->delete();

        $prev_sample->repeatt = 0;
        $prev_sample->result = 5;
        $prev_sample->approvedby = auth()->user()->id;
        $prev_sample->approvedby2 = auth()->user()->id;
        $prev_sample->dateapproved = date('Y-m-d');
        $prev_sample->dateapproved2 = date('Y-m-d');

        $prev_sample->save();
        \App\Misc::check_batch($prev_sample->batch_id);
        session(['toast_message' => 'The sample has been released as a redraw.']);
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

    public function approve_edarp(Request $request)
    {
        $samples = $request->input('samples');
        $submit_type = $request->input('submit_type');
        $user = auth()->user();

        $batches = Sample::selectRaw("distinct batch_id")->whereIn('id', $samples)->get();

        if($submit_type == "release"){
            Sample::whereIn('id', $samples)->update(['synched' => 0, 'approvedby' => $user->id]);
        }
        else{
            Sample::whereIn('id', $samples)->delete();
        }

        foreach ($batches as $key => $value) {
            \App\Misc::check_batch($value->batch_id);
        } 
        return back();
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $samples = Sample::whereRaw("id like '" . $search . "%'")->paginate(10);
        return $samples;
    }

    private function clear_session(){
        session()->forget('batch');
        session()->forget('facility_name');
        session()->forget('batch_total');
        // session()->forget('batch_dispatch');
        // session()->forget('batch_dispatched');
        // session()->forget('batch_received');
        // session()->forget('facility_id');
    }
}
