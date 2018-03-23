<?php

namespace App\Http\Controllers;

use App\Viralsample;
use App\Viralpatient;
use App\Viralbatch;
use App\Facility;
use App\Lookup;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ViralsampleController extends Controller
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
        $data = Lookup::viralsample_form();
        return view('forms.viralsamples', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $viralsamples_arrays = Lookup::viralsamples_arrays();
        $submit_type = $request->input('submit_type');

        if($submit_type == "cancel"){
            $batch_no = session()->pull('viral_batch_no');
            $this->clear_session();
            DB::table('viralbatches')->where('id', $batch_no)->update(['input_complete' => 1]);
            return redirect()->route('viralsample.create');
        }

        $batch_no = session('viral_batch_no', 0);
        $batch_dispatch = session('viral_batch_dispatch', 0);

        $ddispatched = $request->input('datedispatchedfromfacility');

        $high_priority = $request->input('high_priority');

        if($high_priority == 1)
        {
            $facility_id = $request->input('facility_id');

            $batch = new Viralbatch;
            $data = $request->only($viralsamples_arrays['batch']);
            $batch->fill($data);
            $batch->user_id = auth()->user()->id;
            $batch->lab_id = auth()->user()->lab_id;

            if(auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4){
                $batch->received_by = auth()->user()->id;
                $batch->site_entry = 0;
            }

            if(auth()->user()->user_type_id == 5){
                $batch->site_entry = 1;
            }

            $batch->save();
            $message = 'The high priority sample has been saved in batch no ' . $batch->id . '.';

            session(['viral_message' => $message]);
            return redirect()->route('viralsample.create');
        }


        if($batch_no == 0){
            $facility_id = $request->input('facility_id');
            $facility = Facility::find($facility_id);
            session(['viral_facility_name' => $facility->name, 'viral_facility_id' => $facility_id, 'viral_batch_total' => 0, 'viral_batch_received' => $request->input('datereceived')]);

            $batch = new Viralbatch;
            $data = $request->only($viralsamples_arrays['batch']);
            $batch->fill($data);
            $batch->user_id = auth()->user()->id;
            $batch->lab_id = auth()->user()->lab_id;

            if(auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4){
                $batch->received_by = auth()->user()->id;
                $batch->site_entry = 0;
            }

            if(auth()->user()->user_type_id == 5){
                $batch->site_entry = 1;
            }

            if($ddispatched == null){
                session(['viral_batch_dispatch' => 0]);
            }
            else{
                session(['viral_batch_dispatch' => 1, 'viral_batch_dispatched' => $ddispatched]);
                $batch->datedispatchedfromfacility = $ddispatched;
            }

            $batch->save();
            $batch_no = $batch->id;
            session(['viral_batch_no' => $batch_no]);
        }

        if($ddispatched && $batch_dispatch == 0){
            DB::table('viralbatches')->where('id', $batch_no)->update(['datedispatchedfromfacility' => $ddispatched]);
            session(['viral_batch_dispatch' => 1]);
        }

        $new_patient = $request->input('new_patient');

        if($new_patient == 0){

            $repeat_test = Viralsample::where(['patient_id' => $request->input('patient_id'),
            'batch_id' => $batch_no])->first();

            if($repeat_test){
                return redirect()->route('viralsample.create');
            }

            $data = $request->only($viralsamples_arrays['sample']);
            $viralsample = new Viralsample;
            $viralsample->fill($data);
            $viralsample->batch_id = $batch_no;

            $dc = Carbon::createFromFormat('Y-m-d', $request->input('datecollected'));
            $dob = Carbon::parse( $request->input('dob') );
            $years = $dc->diffInYears($dob, true);

            if($years == 0) $years =  ($dc->diffInMonths($dob)/12);

            $viralsample->age = $years;
            $viralsample->save();
        }

        else{
            $data = $request->only($viralsamples_arrays['patient']);
            $viralpatient = new Viralpatient;
            $viralpatient->fill($data);
            $viralpatient->save();

            $dc = Carbon::createFromFormat('Y-m-d', $request->input('datecollected'));
            $dob = Carbon::parse( $request->input('dob') );
            $viralpatient_age = $dc->diffInYears($dob, true);

            $data = $request->only($viralsamples_arrays['sample']);
            $viralsample = new Viralsample;
            $viralsample->fill($data);
            $viralsample->patient_id = $viralpatient->id;
            $viralsample->age = $viralpatient_age;
            $viralsample->batch_id = $batch_no;
            $viralsample->save();

        }

        $submit_type = $request->input('submit_type');

        if($submit_type == "release"){
            $batch_no = session()->pull('viral_batch_no');
            $this->clear_session();
            DB::table('viralbatches')->where('id', $batch_no)->update(['input_complete' => 1]);
        }
        else if($submit_type == "add"){

        }

        $batch_total = session('viral_batch_total', 0) + 1;

        session(['viral_batch_total' => $batch_total]);

        if($batch_total == 10){
            $batch_no = session()->pull('batch_no', $batch_no);
            $this->clear_session();
            DB::table('viralbatches')->where('id', $batch_no)->update(['input_complete' => 1, 'batch_full' => 1]);
        }


        return redirect()->route('viralsample.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function show(Viralsample $viralsample)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function edit(Viralsample $viralsample)
    {
        $viralsample->load(['patient', 'batch']);
        $data = Lookup::viralsample_form();
        $data['viralsample'] = $viralsample;
        return view('forms.viralsamples', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Viralsample $viralsample)
    {
        $viralsamples_arrays = Lookup::viralsamples_arrays();
        $data = $request->only($viralsamples_arrays['sample']);
        $viralsample->fill($data);

        $dc = Carbon::createFromFormat('Y-m-d', $request->input('datecollected'));
        $dob = Carbon::parse( $request->input('dob') );
        $years = $dc->diffInYears($dob, true);

        $viralsample->age = $years;

        $batch = Viralbatch::find($viralsample->batch_id);
        $data = $request->only($viralsamples_arrays['batch']);
        $batch->fill($data);
        $batch->save();

        $data = $request->only(['sex', 'patient_name', 'facility_id', 'caregiver_phone', 'patient', 'dob']);

        $new_patient = $request->input('new_patient');

        if($new_patient == 0){            
            $viralpatient = Viralpatient::find($viralsample->patient_id);
        }

        else{
            $data = $request->only($viralsamples_arrays['patient']);
            $viralpatient = new Viralpatient;
        }
        $viralpatient->fill($data);
        $viralpatient->save();

        $viralsample->patient_id = $viralpatient->id;
        $viralsample->save();

        $site_entry_approval = session()->pull('site_entry_approval');

        if($site_entry_approval){
            return redirect('viralbatch/site_approval/' . $batch->id);
        }

        return redirect('viralbatch/' . $batch->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralsample $viralsample)
    {
        if($viralsample->run != 1 && $viralsample->inworksheet == 0){
            $viralsample->delete();
        }        
        return back();
    }

    public function new_patient(Request $request)
    {
        $facility_id = $request->input('facility_id');
        $patient = $request->input('patient');

        $viralpatient = Viralpatient::where(['facility_id' => $facility_id, 'patient' => $patient])->first();
        $data;
        if($viralpatient){
            $data[0] = 0;
            $data[1] = $viralpatient->toArray();

            $viralsample = Viralsample::select('id')->where(['patient_id' => $viralpatient->id])->where('result', '>', 1000)->first();
            if($viralsample){
                $data[2] = ['previous_nonsuppressed' => 1];
            }
            else{
                $data[2] = ['previous_nonsuppressed' => 0];
            }
        }
        else{
            $data[0] = 1;
        }
        return $data;
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function individual(Viralsample $sample)
    {
        $batch = $sample->batch;
        $sample->load(['patient']);
        $samples[0] = $sample;
        $batch->load(['facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_viral_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

        return view('exports.viralsamples', $data);
    }

    public function release_redraw(Viralsample $viralsample)
    {
        $viralsample->repeatt = 0;
        $viralsample->result = "Collect New Sample";
        $viralsample->approvedby = auth()->user()->id;
        $viralsample->dateapproved = date('Y-m-d');
        $viralsample->save();
        $my = new \App\MiscViral;
        $my->check_batch($sample->batch_id);
        return back();
    }

    public function release_redraws(Request $request)
    {
        $viralsamples = $request->input('samples');
        // DB::table('viralsamples')->whereIn('id', $viralsamples)->update(['repeatt' => 0, 'result' => "Collect New Sample"]);

        $viralsamples = Viralsample::whereIn('id', $viralsamples)->get();

        foreach ($viralsamples as $key => $viralsample) {
            $this->release_redraw($viralsample);
        }

        return back();
    }

    private function clear_session(){
        session()->forget('viral_batch_no');
        session()->forget('viral_batch_total');
        session()->forget('viral_batch_dispatch');
        session()->forget('viral_batch_dispatched');
        session()->forget('viral_batch_received');
        session()->forget('viral_facility_id');
        session()->forget('viral_facility_name');
    }
}
