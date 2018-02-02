<?php

namespace App\Http\Controllers;

use App\Sample;
use App\Patient;
use App\Mother;
use App\Facility;
use App\Batch;
use DB;
use Carbon\Carbon;
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
        $facilities = Facility::all();
        $amrs_locations = DB::table('amrslocations')->get();
        $genders = DB::table('gender')->get();
        $feedings = DB::table('feedings')->get();
        $iprophylaxis = DB::table('prophylaxis')->where(['ptype' => 2, 'flag' => 1])->orderBy('rank', 'asc')->get();
        $interventions = DB::table('prophylaxis')->where(['ptype' => 1, 'flag' => 1])->orderBy('rank', 'asc')->get();
        $entry_points = DB::table('entry_points')->get();
        $hiv_statuses = DB::table('results')->whereNotIn('id', [3, 5])->get();
        $pcrtypes = DB::table('pcrtype')->get();
        $receivedstatuses = DB::table('receivedstatus')->get();

        return view('forms.samples', [
            'facilities' => $facilities,
            'amrs_locations' => $amrs_locations,
            'genders' => $genders,
            'feedings' => $feedings,
            'iprophylaxis' => $iprophylaxis,
            'interventions' => $interventions,
            'entry_points' => $entry_points,
            'hiv_statuses' => $hiv_statuses,
            'pcrtypes' => $pcrtypes,
            'receivedstatuses' => $receivedstatuses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $submit_type = $request->input('submit_type');
        $facility_id = $request->input('facility_id');

        if($submit_type == "cancel"){
            $batch_no = session()->pull('batch_no');
            DB::table('batches')->where('id', $batch_no)->update(['input_complete' => 1]);
            return redirect()->route('sample.create');
        }

        $batch_no = session('batch_no', 0);
        $batch_dispatch = session('batch_dispatch', 0);

        $ddispatched = $request->input('datedispatchedfromfacility');

        if($batch_no == 0){
            $batch = new Batch;
            $batch->user_id = auth()->user()->id;
            $batch->lab_id = auth()->user()->lab_id;
            $batch->facility_id = $facility_id;
            $batch->datereceived = $request->input('datereceived');

            if($ddispatched == null){
                session(['batch_dispatch' => 0]);
            }
            else{
                session(['batch_dispatch' => 1]);
                $batch->datedispatchedfromfacility = $ddispatched;
            }

            if(auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4){
                $batch->received_by = auth()->user()->id;
                $batch->site_entry = 1;
            }

            if(auth()->user()->user_type_id == 5){
                $batch->site_entry = 2;
            }

            $batch->save();
            $batch_no = $batch->id;
            session(['batch_no' => $batch_no]);
        }

        if($ddispatched && $batch_dispatch == 0){
            DB::table('batches')->where('id', $batch_no)->update(['datedispatchedfromfacility' => $ddispatched]);
        }

        $new_patient = $request->input('new_patient');

        if($new_patient == 0){
            $data = $request->except(['_token', 'patient_name', 'submit_type', 'facility_id', 'gender', 'sample_months', 'sample_weeks', 'entry_point', 'caregiver_phone', 'hiv_status', 'patient', 'new_patient', 'datereceived', 'datedispatchedfromfacility']);
            $sample = new Sample;
            $sample->fill($data);
            // $sample->facility = $request->input('facility_id');
            $sample->age = $request->input('sample_months') + ( $request->input('sample_weeks') / 4 );
            $sample->batch_id = $batch_no;
            $sample->save();
        }


        else{
            $data = $request->only(['hiv_status', 'entry_point', 'facility_id']);
            $mother = new Mother;
            $mother->fill($data);
            $mother->save();

            $patient_age = $request->input('sample_months') + ( $request->input('sample_weeks') / 4 );

            $data = $request->only(['gender', 'patient_name', 'facility_id', 'caregiver_phone', 'patient']);
            $patient = new Patient;
            $patient->fill($data);
            $patient->mother_id = $mother->id;

            $dt = Carbon::today();
            $dt->subMonth($patient_age);

            $patient->dob = $dt->toDateString();
            $patient->save();

            $data = $request->except(['_token', 'patient_name', 'submit_type', 'facility_id', 'gender', 'sample_months', 'sample_weeks', 'entry_point', 'caregiver_phone', 'hiv_status', 'patient', 'new_patient', 'datereceived', 'datedispatchedfromfacility']);
            $sample = new Sample;
            $sample->fill($data);
            $sample->patient_id = $patient->id;
            $sample->age = $patient_age;
            $sample->batch_id = $batch_no;
            $sample->save();

        }

        $submit_type = $request->input('submit_type');

        if($submit_type == "release"){
            $batch_no = session()->pull('batch_no');
            DB::table('batches')->where('id', $batch_no)->update(['input_complete' => 1]);
        }
        else if($submit_type == "add"){

        }

        $batch = Sample::where('batch_id', $batch_no)->get()->count();

        if($batch == 10){
            $batch_no = session()->pull('batch_no', $batch_no);
            DB::table('batches')->where('id', $batch_no)->update(['input_complete' => 1, 'batch_full' => 1]);
        }


        return redirect()->route('sample.create');
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
        $sample->load(['patient.mother']);
        $facilities = Facility::all();
        $amrs_locations = DB::table('amrslocations')->get();
        $genders = DB::table('gender')->get();
        $feedings = DB::table('feedings')->get();
        $iprophylaxis = DB::table('prophylaxis')->where(['ptype' => 2, 'flag' => 1])->orderBy('rank', 'asc')->get();
        $interventions = DB::table('prophylaxis')->where(['ptype' => 1, 'flag' => 1])->orderBy('rank', 'asc')->get();
        $entry_points = DB::table('entry_points')->get();
        $hiv_statuses = DB::table('results')->whereNotIn('id', [3, 5])->get();
        $pcrtypes = DB::table('pcrtype')->get();
        $receivedstatuses = DB::table('receivedstatus')->get();

        return view('forms.samples', [
            'sample' => $sample,
            'facilities' => $facilities,
            'amrs_locations' => $amrs_locations,
            'genders' => $genders,
            'feedings' => $feedings,
            'iprophylaxis' => $iprophylaxis,
            'interventions' => $interventions,
            'entry_points' => $entry_points,
            'hiv_statuses' => $hiv_statuses,
            'pcrtypes' => $pcrtypes,
            'receivedstatuses' => $receivedstatuses,
        ]);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sample $sample)
    {
        //
    }

    public function new_patient($patient, $facility_id)
    {
        $patient = Patient::where(['facility_id' => $facility_id, 'patient' => $patient])->first();
        $data;
        if($patient){
            $mother = $patient->mother;
            $data[0] = 0;
            $data[1] = $patient->toArray();
            $data[2] = $mother->toArray();
        }
        else{
            $data[0] = 1;
        }
        return $data;
    }
}
