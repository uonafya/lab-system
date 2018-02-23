<?php

namespace App\Http\Controllers;

use App\Viralsample;
use App\Viralpatient;
use App\Viralbatch;
use App\Facility;
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
        $facilities = Facility::select('id', 'name')->get();
        $amrs_locations = DB::table('amrslocations')->get();
        $rejectedreasons = DB::table('viralrejectedreasons')->get();
        $genders = DB::table('gender')->get();
        $pmtct_types = DB::table('viralpmtcttype')->get();
        $receivedstatuses = DB::table('receivedstatus')->get();
        $prophylaxis = DB::table('viralprophylaxis')->orderBy('category', 'asc')->get();
        $justifications = DB::table('viraljustifications')->get();
        $sampletypes = DB::table('viralsampletype')->where('flag', 1)->get();
        $regimenlines = DB::table('viralregimenline')->where('flag', 1)->get();

        return view('forms.viralsamples', [
            'facilities' => $facilities,
            'amrs_locations' => $amrs_locations,
            'rejectedreasons' => $rejectedreasons,
            'genders' => $genders,
            'receivedstatuses' => $receivedstatuses,
            'pmtct_types' => $pmtct_types,
            'prophylaxis' => $prophylaxis,
            'justifications' => $justifications,
            'sampletypes' => $sampletypes,
            'regimenlines' => $regimenlines,

            'batch_no' => session('viral_batch_no', 0),
            'batch_dispatch' => session('viral_batch_dispatch', 0),
            'batch_dispatched' => session('viral_batch_dispatched', 0),
            'batch_received' => session('viral_batch_received', 0),

            'facility_id' => session('viral_facility_id', 0),
            'facility_name' => session('viral_facility_name', 0),
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

        if($submit_type == "cancel"){
            $batch_no = session()->pull('viral_batch_no');
            $this->clear_session();
            DB::table('viralbatches')->where('id', $batch_no)->update(['input_complete' => 1]);
            return redirect()->route('viralsample.create');
        }

        $batch_no = session('viral_batch_no', 0);
        $batch_dispatch = session('viral_batch_dispatch', 0);

        $ddispatched = $request->input('datedispatchedfromfacility');

        if($batch_no == 0){
            $facility_id = $request->input('facility_id');
            $facility = Facility::find($facility_id);
            session(['viral_facility_name' => $facility->name, 'viral_facility_id' => $facility_id, 'viral_batch_total' => 0, 'viral_batch_received' => $request->input('datereceived')]);

            $batch = new Viralbatch;
            $batch->user_id = auth()->user()->id;
            $batch->lab_id = auth()->user()->lab_id;
            $batch->facility_id = $facility_id;
            $batch->datereceived = $request->input('datereceived');

            if($ddispatched == null){
                session(['viral_batch_dispatch' => 0]);
            }
            else{
                session(['viral_batch_dispatch' => 1, 'viral_batch_dispatched' => $ddispatched]);
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

            $data = $request->except(['_token', 'patient_name', 'submit_type', 'facility_id', 'sex', 'sample_months', 'sample_weeks', 'entry_point', 'caregiver_phone', 'hiv_status', 'patient', 'new_patient', 'datereceived', 'datedispatchedfromfacility', 'dob', 'ccc_no']);
            $sample = new Viralsample;
            $sample->fill($data);
            $sample->batch_id = $batch_no;
            // $sample->age = $request->input('sample_months') + ( $request->input('sample_weeks') / 4 );

            $dc = Carbon::createFromFormat('Y-m-d', $request->input('datecollected'));
            $dob = Carbon::parse( $request->input('dob') );
            $years = $dc->diffInYears($dob, true);

            // $months = $dc->diffInMonths($dob);
            // $weeks = $dc->diffInWeeks($dob->copy()->addMonths($months));
            $sample->age = $years;
            $sample->save();
        }

        else{

            $data = $request->only(['sex', 'patient_name', 'facility_id', 'caregiver_phone', 'patient', 'dob']);
            $patient = new Viralpatient;
            $patient->fill($data);
            $patient->mother_id = $mother->id;
            $patient->save();

            // $patient_age = $request->input('sample_months') + ( $request->input('sample_weeks') / 4 );
            // $dt = Carbon::today();
            // $dt->subMonths($request->input('sample_months'));
            // $dt->subWeeks($request->input('sample_weeks'));
            // $patient->dob = $dt->toDateString();

            $dc = Carbon::createFromFormat('Y-m-d', $request->input('datecollected'));
            $years = $dc->diffInYears($dob, true);

            // $months = $dc->diffInMonths($patient->dob);
            // $weeks = $dc->diffInWeeks($patient->dob->copy()->addMonths($months));

            // $patient_age = $months + ($weeks / 4);

            $data = $request->except(['_token', 'patient_name', 'submit_type', 'facility_id', 'sex', 'sample_months', 'sample_weeks', 'entry_point', 'caregiver_phone', 'hiv_status', 'patient', 'new_patient', 'datereceived', 'datedispatchedfromfacility', 'dob', 'ccc_no']);
            $sample = new Viralsample;
            $sample->fill($data);
            $sample->patient_id = $patient->id;
            $sample->age = $patient_age;
            $sample->batch_id = $batch_no;
            $sample->save();

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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralsample $viralsample)
    {
        //
    }
}
