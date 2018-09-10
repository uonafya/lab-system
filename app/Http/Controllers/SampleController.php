<?php

namespace App\Http\Controllers;

use App\Sample;
use App\SampleView;
use App\Patient;
use App\Mother;
use App\Batch;
use App\Facility;
use App\Viralpatient;
use App\Lookup;
use App\Misc;

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

    public function list_poc()
    {
        $user = auth()->user();
        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}' OR lab_id='{$user->facility_id}')";
        $data = Lookup::get_lookups();
        $samples = SampleView::with(['facility'])->whereRaw($string)->where(['site_entry' => 2])->get();
        $data['samples'] = $samples;
        $data['pre'] = '';
        return view('tables.poc_samples', $data)->with('pageTitle', 'Eid POC Samples');
    }

    public function list_sms()
    {
        $data = Lookup::get_lookups();
        $samples = SampleView::with(['facility'])->whereNotNull('time_result_sms_sent')->get();
        $data['samples'] = $samples;
        $data['pre'] = '';
        return view('tables.sms_log', $data)->with('pageTitle', 'Eid Patient SMS Log');
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
        return view('forms.samples', $data)->with('pageTitle', 'Add POC Sample');
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
            session(['toast_message' => "The batch {$batch->id} has been released."]);
            return redirect("batch/{$batch->id}");
        }   

        $existing = SampleView::existing( $request->only(['facility_id', 'patient', 'datecollected']) )->get()->first();
        if($existing){
            session(['toast_message' => 'The sample already exists in batch {$existing->batch_id} and has therefore not been saved again']);
            session(['toast_error' => 1]);
            return back();            
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
        }

        $data = $request->only($samples_arrays['batch']);
        $batch->fill($data);
        $batch->save();
        session(['batch' => $batch]);

        $new_patient = $request->input('new_patient');
        $last_result = $request->input('last_result');
        $mother_last_result = $request->input('mother_last_result');

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
            $mother->mother_dob = Lookup::calculate_dob($request->input('datecollected'), $request->input('mother_age')); 
            $mother->fill($data);

            $viralpatient = Viralpatient::existing($mother->facility_id, $mother->ccc_no)->get()->first();
            if($viralpatient) $mother->patient_id = $viralpatient->id;

            $mother->pre_update();
        }

        else{

            $data = $request->only($samples_arrays['mother']);
            $mother = Mother::existing($data['facility_id'], $data['ccc_no'])->get()->first();
            if(!$mother) $mother = new Mother;
            
            $mother->mother_dob = Lookup::calculate_dob($request->input('datecollected'), $request->input('mother_age'));
            $mother->fill($data);

            $viralpatient = Viralpatient::existing($mother->facility_id, $mother->ccc_no)->get()->first();
            if($viralpatient) $mother->patient_id = $viralpatient->id;

            $mother->save();

            $data = $request->only($samples_arrays['patient']);
            $patient = new Patient;
            $patient->fill($data);
            $patient->mother_id = $mother->id;
            $patient->save();
        }

        $data = $request->only($samples_arrays['sample']);
        $sample = new Sample;
        $sample->fill($data);
        $sample->batch_id = $batch->id;
        $sample->patient_id = $patient->id;

        if($last_result){
            $sample->mother_last_result = $last_result;
            $sample->mother_last_rcategory = 1;
        }
        else if($mother_last_result){
            $sample->mother_last_result = $mother_last_result;
            $my = new \App\MiscViral;
            $res = $my->set_rcategory($mother_last_result);
            $sample->mother_last_rcategory = $res['rcategory'] ?? null;
        }

        $sample->age = Lookup::calculate_age($request->input('datecollected'), $request->input('dob'));
        $sample->save();

        session(['toast_message' => "The sample has been created in batch {$batch->id}."]);

        $submit_type = $request->input('submit_type');

        if($submit_type == "release"){
            $this->clear_session();
            $batch->premature();
            return redirect("batch/{$batch->id}");
        }

        $sample_count = session('batch_total') + 1;
        session(['batch_total' => $sample_count]);

        if($sample_count == 10){
            $this->clear_session();
            $batch->full_batch();
            session(['toast_message' => "The batch {$batch->id} is full and no new samples can be added to it."]);
            return redirect("batch/{$batch->id}");
        }

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

        $s = Sample::find($sample->id);
        $samples = Sample::runs($s)->get();

        $patient = $s->patient; 

        $data = Lookup::get_lookups();
        $data['sample'] = $sample;
        $data['samples'] = $samples;
        $data['patient'] = $patient;
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
        $sample->load(['patient.mother', 'batch.facility']);
        $data = Lookup::samples_form();
        $data['sample'] = $sample;
        return view('forms.samples', $data)->with('pageTitle', 'Samples');
    }

    /**
     * Show the form for editing the specified resource (poc).
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function edit_poc(Sample $sample)
    {
        $sample->load(['patient', 'batch.facility_lab']);
        if($sample->batch->site_entry != 2) abort(409, 'This sample is not a POC sample.');
        $data = Lookup::get_lookups();
        $data['sample'] = $sample;
        $data['pre'] = '';
        return view('forms.poc_result', $data)->with('pageTitle', 'Edit Result');
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
        $submit_type = $request->input('submit_type');

        $samples_arrays = Lookup::samples_arrays();
        $data = $request->only($samples_arrays['sample']);
        $sample->fill($data);

        $batch = $sample->batch;
        
        $last_result = $request->input('last_result');
        $mother_last_result = $request->input('mother_last_result');

        $new_batch = false;

        if($submit_type == "new_batch" && $batch->facility_id != $request->input('facility_id')){
            $batch = new Batch;
            $new_batch = true;

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
        }

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
            $mother->mother_dob = Lookup::calculate_dob($request->input('datecollected'), $request->input('mother_age'));
            $mother->fill($data);

            $viralpatient = Viralpatient::existing($mother->facility_id, $mother->ccc_no)->get()->first();
            if($viralpatient) $mother->patient_id = $viralpatient->id;

            $mother->pre_update();
        }
        else
        {
            $data = $request->only($samples_arrays['mother']);
            $mother = new Mother;
            $mother->mother_dob = Lookup::calculate_dob($request->input('datecollected'), $request->input('mother_age'));
            $mother->fill($data);

            $viralpatient = Viralpatient::existing($mother->facility_id, $mother->ccc_no)->get()->first();
            if($viralpatient) $mother->patient_id = $viralpatient->id;

            $mother->pre_update();
            
            $data = $request->only($samples_arrays['patient']);
            $patient = new Patient;
            $patient->fill($data);
            $patient->mother_id = $mother->id;
            $patient->pre_update();
        }
        
        if($last_result){
            $sample->mother_last_result = $last_result;
            $sample->mother_last_rcategory = 1;
        }
        else if($mother_last_result){
            $sample->mother_last_result = $mother_last_result;
            $my = new \App\MiscViral;
            $res = $my->set_rcategory($mother_last_result);
            $sample->mother_last_rcategory = $res['rcategory'];
        }
        
        $sample->age = Lookup::calculate_age($request->input('datecollected'), $request->input('dob'));
        $sample->patient_id = $patient->id; 
        $sample->batch_id = $batch->id; 

        session(['toast_message' => 'The sample has been updated.']);

        if($sample->receivedstatus == 2 && $sample->getOriginal('receivedstatus') == 1 && $sample->worksheet_id){
            $worksheet = $sample->worksheet;
            if($worksheet->status_id == 1){
                $d = Misc::get_worksheet_samples($worksheet->machine_type, 1);
                $s = $d['samples']->first();
                if($s){
                    $sample->worksheet_id = null;

                    $s->worksheet_id = $worksheet->id;
                    $s->save();
                    session(['toast_message' => 'The sample has been rejected and it has been replaced in worksheet ' . $worksheet->id]);
                }
                else{
                    session([
                        'toast_message' => 'The sample has been rejected but no sample could be found to replace it in the worksheet.',
                        'toast_error' => 1
                    ]);
                }
            }
            else{
                session([
                    'toast_message' => 'The worksheet has already been run.',
                    'toast_error' => 1
                ]);
            }
        }


        $sample->pre_update();   

        if($new_batch){
            session(['batch' => $batch, 'batch_total' => 1,
                'toast_message' => 'The sample has been saved to batch number ' . $batch->id]);
            return redirect('sample/create');
        }     

        $site_entry_approval = session()->pull('site_entry_approval');

        if($site_entry_approval){
            session(['toast_message' => 'The site entry sample has been approved.']);
            return redirect('batch/site_approval/' . $batch->id);
        }

        return redirect('batch/' . $batch->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function save_poc(Request $request, Sample $sample)
    {
        $sample->fill($request->except(['_token', 'lab_id']));
        $sample->pre_update();
        Misc::check_batch($sample->batch_id);
        Misc::check_worklist(SampleView::class, $sample->worksheet_id);

        $batch = $sample->batch;
        $batch->lab_id = $request->input('lab_id');
        if($batch->batch_complete == 2){
            $batch->datedispatched = date('Y-m-d');
            $batch->batch_complete = 1;
        }
        $batch->pre_update();
        session(['toast_message' => 'The sample has been updated.']);

        return redirect('sample/list_poc');        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sample $sample)
    {
        if($sample->result == NULL){
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

            $viralpatient = $mother->viral_patient;

            if($viralpatient){
                $viralpatient->last_test();
                if($viralpatient->recent){
                    if($viralpatient->recent->rcategory == 1) $mother->recent_test = 0;
                    else{
                        $mother->recent_test = $viralpatient->recent->result;
                    }
                }
            }

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
        $samples = Sample::runs($sample)->get();

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
        $data = Lookup::get_lookups();
        $sample->load(['patient.mother', 'approver', 'batch.lab', 'batch.facility', 'batch.receiver', 'batch.creator']);
        $data['samples'] = [$sample];

        return view('exports.mpdf_samples', $data)->with('pageTitle', 'Individual Sample');
    }

    public function send_sms(SampleView $sample)
    {
        Misc::send_sms($sample);
        session(['toast_message' => 'The sms has been sent.']);
        return back();
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

        $prev_sample->labcomment = "Failed Test";
        $prev_sample->repeatt = 0;
        $prev_sample->result = 5;
        $prev_sample->approvedby = auth()->user()->id;
        $prev_sample->approvedby2 = auth()->user()->id;
        $prev_sample->dateapproved = date('Y-m-d');
        $prev_sample->dateapproved2 = date('Y-m-d');

        $prev_sample->save();
        Misc::check_batch($prev_sample->batch_id);
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
            Misc::check_batch($value->batch_id);
        } 
        return back();
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(batches.facility_id='{$user->facility_id}' OR batches.user_id='{$user->id}')";

        $samples = Sample::select('samples.id')
            ->whereRaw("samples.id like '" . $search . "%'")
            ->when($facility_user, function($query) use ($string){
                return $query->join('batches', 'samples.batch_id', '=', 'batches.id')->whereRaw($string);
            })
            ->paginate(10);

        $samples->setPath(url()->current());
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
