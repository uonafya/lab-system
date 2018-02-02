<?php

namespace App\Http\Controllers;

use App\Sample;
use App\Patient;
use App\Mother;
use App\Facility;
use App\Batch;
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
        $batch_no = session('batch_no', 0);

        if($batch_no == 0){
            $batch = new Batch;
            $batch->save();
            $batch_no = $batch->id;
            session(['batch_no' => $batch_no]);
        }

        $new_patient = $request->input('new_patient');

        if($new_patient == 0){
            $data = $request->except(['_token', 'facility_id', 'gender', 'sample_months', 'sample_weeks', 'entry_point', 'caregiver_phone', 'hiv_status', 'patient']);
            $sample = new Sample;
            $sample->fill($data);
            $sample->facility = $request->input('facility_id');
            $sample->age = $request->input('sample_months') + ( $request->input('sample_weeks') / 4 );
            $sample->save();
        }

        else{
            $data = $request->only(['hiv_status', 'entry_point', 'facility_id']);
            $mother = new Mother;
            $mother->fill($data);
            $mother->save();

            $data = $request->only(['gender', 'date_of_birth', 'facility_id', 'caregiver_phone', 'patient']);
            $patient = new Patient;
            $patient->fill($data);
            $patient->mother_id = $mother->id;
            $patient->save();

            $data = $request->except(['_token', 'facility_id', 'gender', 'sample_months', 'sample_weeks', 'entry_point', 'caregiver_phone', 'hiv_status', 'patient']);
            $sample = new Sample;
            $sample->fill($data);
            $sample->facility = $request->input('facility_id');
            $sample->patient_id = $patient_id;
            $sample->age = $request->input('sample_months') + ( $request->input('sample_weeks') / 4 );
            $sample->save();

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
        //
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
