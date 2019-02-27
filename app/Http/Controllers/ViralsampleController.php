<?php

namespace App\Http\Controllers;

use App\Viralsample;
use App\ViralsampleView;
use App\Viralpatient;
use App\Viralbatch;
use App\Facility;
use App\Lookup;
use App\MiscViral;
use App\User;

use Excel;

use App\Http\Requests\ViralsampleRequest;
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

    public function nhrl_samples(Request $request)
    {
        $samples = Viralsample::where('synched', 5)->with(['batch.facility', 'patient'])->get(); 
        $data['samples'] = $samples;
        return view('tables.confirm_viralsamples', $data)->with('pageTitle', 'Confirm Samples');
    }

    public function list_poc($param=null)
    {
        $user = auth()->user();
        $string = "1";
        if($user->user_type_id == 5) $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}' OR lab_id='{$user->facility_id}')";
        
        $data = Lookup::get_viral_lookups();

        $samples = ViralsampleView::with(['facility'])
            ->when($param, function($query){
                return $query->whereNull('result')->where(['receivedstatus' => 1]);
            })
            ->whereRaw($string)
            ->where(['site_entry' => 2])
            ->orderBy('id', 'desc')
            ->paginate(50);

        $samples->setPath(url()->current());
        $data['samples'] = $samples;
        $data['pre'] = 'viral';
        return view('tables.poc_samples', $data)->with('pageTitle', 'VL POC Samples');
    }

    public function list_sms()
    {
        $user = auth()->user();
        $string = "1";
        if($user->user_type_id == 5) $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}' OR lab_id='{$user->facility_id}')";
        
        $data = Lookup::get_viral_lookups();
        $samples = ViralsampleView::with(['facility'])->whereRaw($string)->whereNotNull('time_result_sms_sent')->get();
        $data['samples'] = $samples;
        $data['pre'] = 'viral';
        return view('tables.sms_log', $data)->with('pageTitle', 'VL Patient SMS Log');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($sampletype=false)
    {
        $data = Lookup::viralsample_form();
        $data['form_sample_type'] = $sampletype;
        $data['excelusers'] = User::where('user_type_id', '<>', 5)->get();
        return view('forms.viralsamples', $data)->with('pageTitle', 'Add Sample');
    }

    public function create_poc()
    {
        $data = Lookup::viralsample_form();
        $data['poc'] = true;
        return view('forms.viralsamples', $data)->with('pageTitle', 'Add POC Sample');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function deleteexcelupload(Request $request) {
        if ($request->method() == "GET") {
            return view('forms.viralsamplesexceldelete')->with('pageTitle', 'Add Sample');
        } else {
            $file = $request->excelupload->path();
            $path = $request->excelupload->store('public/samples/otherlab/delete');
            $excelData = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $batches = collect($excelData->toArray())->first();
            Viralsample::whereIn('batch_id', $batches)->delete();
            Viralbatch::whereIn('id', $batches)->delete();
            return back();
        }
    }

    public function excelupload(Request $request) {
        if ($request->method() == "GET") {
            $data['excelusers'] = User::where('user_type_id', '<>', 5)->get();
            return view('forms.viralsamplesexcel', $data)->with('pageTitle', 'Add Sample');
        } else {
            $file = $request->excelupload->path();
            $path = $request->excelupload->store('public/samples/otherlab');
            $batch = null;
            $lookups = Lookup::get_viral_lookups();
            // dd($lookups);
            $excelData = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();
            $excelsheetvalue = collect($excelData->flatten(1)->values()->all());
            $dataArray = [];
            $dataArray = ['Viral Batches'];
            $countItem = $excelsheetvalue->count();
            $counter = 0;
            if (!$excelsheetvalue->isEmpty()){
                foreach ($excelsheetvalue as $samplekey => $samplevalue) {
                    $counter++;
                    $facility = Facility::where('facilitycode', '=', $samplevalue[5])->first();
                    // if (!isset($facility)){
                    //     $nofacility[] = $samplevalue;
                    //     continue;
                    // }
                    $existing = Viralpatient::existing($facility->id, $samplevalue[3])->first();
                    
                    if ($existing)
                        $patient = $existing;
                    else {
                        $patient = new Viralpatient();
                        $patient->patient = $samplevalue[3];
                        $patient->facility_id = $facility->id;
                        $patient->sex = $lookups['genders']->where('gender', $samplevalue[6])->first()->id;
                        $patient->dob = $samplevalue[9];
                        // $patient->initiation_date = $samplevalue[14];
                        $patient->save();
                    }
                    
                    if ($counter == 1) {
                        $batch = new Viralbatch();
                        $existingSample = ViralsampleView::existing(['facility_id' => $facility->id, 'patient' => $patient->patient, 'datecollected' => $samplevalue[11]])->first();
                        
                        if ($existingSample)
                            continue;
                        $batch->user_id = $request->input('receivedby');
                        $batch->lab_id = env('APP_LAB');
                        $batch->received_by = $request->input('receivedby');
                        $batch->site_entry = 0;
                        $batch->entered_by = $request->input('receivedby');
                        $batch->datereceived = $samplevalue[16];
                        $batch->facility_id = $facility->id;
                        $batch->save();
                    }

                    $sample = new Viralsample();
                    $sample->batch_id = $batch->id;
                    $sample->receivedstatus = $samplevalue[18];
                    $sample->age = $samplevalue[8];
                    $sample->patient_id = $patient->id;
                    $sample->pmtct = $samplevalue[7];
                    $sample->dateinitiatedonregimen = $samplevalue[14];
                    $sample->datecollected = $samplevalue[11];
                    $sample->regimenline = $samplevalue[13];
                    $sample->prophylaxis = $lookups['prophylaxis']->where('category', $samplevalue[12])->first()->id ?? 15;
                    $sample->justification = $lookups['justifications']->where('rank', $samplevalue[15])->first()->id ?? 8;
                    $sample->sampletype = $samplevalue[10];
                    $sample->save();

                    $sample_count = $batch->sample->count();

                    $countItem -= 1;
                    if($counter == 10) {
                        $dataArray[] = $batch->id;
                        $batch->full_batch();
                        $batch = null;
                        $counter = 0;
                    } 

                    if ($countItem == 1) {
                        $sample_count = $batch->sample->count();
                        if ($sample_count != 10) {
                            $batch->premature();
                            $dataArray[] = $batch->id;
                        }
                    }
                    // echo "<pre>";print_r("Close Batch {$batch}");echo "</pre>"; // Close batch
                }
                $title = "EDARP Samples uploaded to KEMRI";
                Excel::create($title, function($excel) use ($dataArray, $title) {
                    $excel->setTitle($title);
                    $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('WJ Gilmore, LLC');
                    $excel->setDescription($title);

                    $excel->sheet('Sheet1', function($sheet) use ($dataArray) {
                        $sheet->fromArray($dataArray, null, 'A1', false, false);
                    });

                })->download('csv');
            }
            return back();
        }
        
    }
    public function store(ViralsampleRequest $request)
    {
        $viralsamples_arrays = Lookup::viralsamples_arrays();
        $submit_type = $request->input('submit_type');
        $user = auth()->user();

        $batch = session('viral_batch');

        if($submit_type == "cancel"){
            $batch->premature();
            $this->clear_session();
            session(['toast_message' => "The batch {$batch->id} has been released."]);
            return redirect("viralbatch/{$batch->id}");
        }

        $data_existing = $request->only(['facility_id', 'patient', 'datecollected']);
        if(!isset($data_existing['facility_id'])){
            session(['toast_message' => "Please set the facility before submitting.", 'toast_error' => 1]);
            return back();   
        }

        $patient_string = trim($request->input('patient'));
        // if(env('APP_LAB') == 4 || env('APP_LAB') == 2){
        if(env('APP_LAB') == 4){
            $fac = Facility::find($data_existing['facility_id']);
            // $patient_string = $fac->facilitycode . '/' . $patient_string;
            $str = $fac->facilitycode;
            if(env('APP_LAB') == 4) $str .= '/';
            if(!starts_with($patient_string, $str)){
                if(starts_with($patient_string, $fac->facilitycode)){
                    $code = str_after($patient_string, $fac->facilitycode);
                    $patient_string = $str . $code;
                }
                else{
                    $patient_string = $str . $patient_string;
                }
            }
        }

        $data_existing['patient'] = $patient_string;

        $existing = ViralsampleView::existing( $data_existing )->get()->first();
        if($existing && !$request->input('reentry')){
            session(['toast_message' => "The sample already exists in batch {$existing->batch_id} and has therefore not been saved again"]);
            session(['toast_error' => 1]);
            return back();            
        }

        $highpriority = $request->input('highpriority');

        if($highpriority == 1)
        {
            $batch = new Viralbatch;
            $batch->user_id = $user->id;
            $batch->lab_id = $user->lab_id;

            if($user->is_lab_user()){
                $batch->received_by = auth()->user()->id;
                $batch->site_entry = 0;
            }

            if($user->user_type_id == 5) $batch->site_entry = 1;

            $data = $request->only($viralsamples_arrays['batch']);
            $batch->fill($data);

            $batch->save();
            $message = 'The high priority sample has been saved in batch no ' . $batch->id . '.';

            session(['toast_message' => $message]);
        }

        if(!$batch){
            $facility_id = $request->input('facility_id');
            $facility = Facility::find($facility_id);
            session(['viral_facility_name' => $facility->name, 'viral_batch_total' => 0]);

            $batch = Viralbatch::eligible($facility_id, $request->input('datereceived'))->first();

            if(!$batch) $batch = new Viralbatch;
            $batch->user_id = $user->id;
            $batch->lab_id = $user->lab_id;

            if($user->is_lab_user()){
                $batch->received_by = auth()->user()->id;
                $batch->site_entry = 0;
            }

            if($user->user_type_id == 5){
                $batch->site_entry = 1;
            }
        }

        $data = $request->only($viralsamples_arrays['batch']);
        $batch->fill($data);

        $batch->save();
        session(['viral_batch' => $batch]);

        $new_patient = $request->input('new_patient');
        $viralpatient = Viralpatient::existing($request->input('facility_id'), $patient_string)->first();
        if(!$viralpatient) $viralpatient = new Viralpatient;

        /*if($new_patient == 0){

            $patient_id = $request->input('patient_id');
            $repeat_test = Viralsample::where(['patient_id' => $patient_id, 'batch_id' => $batch->id])->first();

            if($repeat_test){
                session(['toast_message' => 'The sample already exists in the batch and has therefore not been saved again']);
                session(['toast_error' => 1]);
                return redirect()->route('viralsample.create');
            }

            $viralpatient = Viralpatient::find($patient_id);
            if(!$viralpatient) $viralpatient = Viralpatient::existing($request->input('facility_id'), $request->input('patient'))->first();
            if(!$viralpatient) $viralpatient = new Viralpatient;
        }
        else{
            $viralpatient = new Viralpatient;
        }*/

        $data = $request->only($viralsamples_arrays['patient']);
        if(!$data['dob']) $data['dob'] = Lookup::calculate_dob($request->input('datecollected'), $request->input('age'), 0);
        $viralpatient->fill($data);
        $viralpatient->patient = $patient_string;
        $viralpatient->save();

        $data = $request->only($viralsamples_arrays['sample']);
        $viralsample = new Viralsample;
        $viralsample->fill($data);
        if(env('APP_LAB') == 8){
            $viralsample->areaname = $request->input('areaname');
            $viralsample->label_id = $request->input('label_id');
        }
        $viralsample->patient_id = $viralpatient->id;
        $viralsample->age = Lookup::calculate_viralage($request->input('datecollected'), $viralpatient->dob);
        $viralsample->batch_id = $batch->id;
        $viralsample->save();


        $sample_count = Viralsample::where('batch_id', $batch->id)->get()->count();

        session(['toast_message' => "The sample has been created in batch {$batch->id}.", 'viral_batch_total' => $sample_count, 'viral_last_patient' => $viralpatient->patient]);

        $submit_type = $request->input('submit_type');

        if($submit_type == "release" || $batch->site_entry == 2 || $sample_count > 9){
            if($sample_count > 9) $batch->full_batch(); 
            $this->clear_session();
            if($submit_type == "release" || $batch->site_entry == 2) $batch->premature();
            else{
                $batch->full_batch();
                session(['toast_message' => "The batch {$batch->id} is full and no new samples can be added to it."]);
            }
            if($batch->site_entry == 2) return back();
            MiscViral::check_batch($batch->id);

            if($user->is_lab_user()){           
                $work_samples_dbs = MiscViral::get_worksheet_samples(2, false, 1);
                $work_samples_edta = MiscViral::get_worksheet_samples(2, false, 2);

                $str = '';

                if($work_samples_dbs['count'] > 92) $str .= 'You now have ' . $work_samples_dbs['count'] . ' DBS samples that are eligible for testing.<br />';

                if($work_samples_edta['count'] > 20) $str .= 'You now have ' . $work_samples_edta['count'] . ' Plasma / EDTA samples that are eligible for testing.';

                if($str != '') session(['toast_message' => $str]);                
            }

            return redirect("viralbatch/{$batch->id}");
        }

        if($sample_count == 10){
            $this->clear_session();
            $batch->full_batch();
            MiscViral::check_batch($batch->id);
            session(['toast_message' => "The batch {$batch->id} is full and no new samples can be added to it."]);
            return redirect("viralbatch/{$batch->id}");
        }

        session(['toast_message' => 'The sample has been created.']);

        $stype = $request->input('form_sample_type');

        if($stype) return redirect('viralsample/create/' . $stype);

        return redirect()->route('viralsample.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function show(ViralsampleView $viralsample)
    {
        $s = Viralsample::find($viralsample->id);
        $samples = Viralsample::runs($s)->get();

        $patient = $s->patient; 

        $data = Lookup::get_viral_lookups();
        $data['sample'] = $viralsample;
        $data['samples'] = $samples;
        $data['patient'] = $patient;
        
        return view('tables.viralsample_search', $data)->with('pageTitle', 'Sample Summary');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function edit(Viralsample $viralsample)
    {
        // $viralsample->load(['patient', 'batch.facility']);
        $data = Lookup::viralsample_form();
        $data['viralsample'] = $viralsample;
        // dd($data);
        return view('forms.viralsamples', $data)->with('pageTitle', 'Edit Sample');
    }

    /**
     * Show the form for editing the specified resource (poc).
     *
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function edit_poc(Viralsample $sample)
    {
        $sample->load(['patient', 'batch.facility_lab']);
        // if($sample->batch->site_entry != 2) abort(409, 'This sample is not a POC sample.');
        $data = Lookup::get_lookups();
        $data['sample'] = $sample;
        $data['pre'] = 'viral';
        return view('forms.poc_result', $data)->with('pageTitle', 'Edit Result');
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
        $user = auth()->user();
        
        $batch = Viralbatch::find($viralsample->batch_id);

        if($batch->site_entry == 1 && !$viralsample->receivedstatus && $user->is_lab_user()){
            $viralsample->sample_received_by = $user->id;
        }

        $data = $request->only($viralsamples_arrays['sample']);
        $viralsample->fill($data);

        $data = $request->only($viralsamples_arrays['batch']);
        $batch->fill($data);
        if(!$batch->received_by && $user->is_lab_user()) $batch->received_by = $user->id;
        $batch->pre_update();

        $data = $request->only($viralsamples_arrays['patient']);

        $new_patient = $request->input('new_patient');

        // if($new_patient == 0){            
        //     $viralpatient = Viralpatient::find($viralsample->patient_id);
        // }
        // else{
        //     $viralpatient = new Viralpatient;
        // }

        $viralpatient = $viralsample->patient;

        if($viralpatient->patient != $request->input('patient')){
            $viralpatient = Viralpatient::existing($request->input('facility_id'), $request->input('patient'))->first();

            if(!$viralpatient){
                $viralpatient = new Viralpatient;
                $created_patient = true;
            }
        }
        

        if(!$data['dob']) $data['dob'] = Lookup::calculate_dob($request->input('datecollected'), $request->input('age'), 0);
        $viralpatient->fill($data);
        $viralpatient->pre_update();

        $viralsample->age = Lookup::calculate_viralage($request->input('datecollected'), $viralpatient->dob);
        $viralsample->patient_id = $viralpatient->id;

        session(['toast_message' => 'The sample has been updated.']);

        if($viralsample->receivedstatus == 2 && $viralsample->getOriginal('receivedstatus') == 1 && $viralsample->worksheet_id){
            $worksheet = $viralsample->worksheet;
            /*if($worksheet->status_id == 1){
                $d = MiscViral::get_worksheet_samples($worksheet->machine_type, $worksheet->calibration, $worksheet->sampletype, 1);
                $s = $d['samples']->first();
                if($s){
                    $viralsample->worksheet_id = null;

                    $replacement = Viralsample::find($s->id);

                    $replacement->worksheet_id = $worksheet->id;
                    $replacement->save();
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
            }*/
            $viralsample->worksheet_id = null;
            $viralsample->result = null;
            $viralsample->interpretation = null;
        }
        if(env('APP_LAB') == 8){
            $viralsample->areaname = $request->input('areaname');
            $viralsample->label_id = $request->input('label_id');
        }

        if($viralpatient->sex == 1) $viralsample->pmtct = 3;

        $viralsample->pre_update();

        if(isset($created_patient)){
            if($viralsample->run == 1 && $viralsample->has_rerun){
                $children = $viralsample->child;

                foreach ($children as $kid) {
                    $kid->patient_id = $viralpatient->id;
                    $kid->pre_update();
                }
            }
            else if($viralsample->run > 1){
                $parent = $viralsample->parent;
                $parent->pre_update();
                
                $children = $parent->child;

                foreach ($children as $kid) {
                    $kid->patient_id = $viralpatient->id;
                    $kid->pre_update();
                }
            }
        }

        MiscViral::check_batch($batch->id); 

        if($viralsample->receivedstatus == 1 && $user->is_lab_user()){            
            $work_samples_dbs = MiscViral::get_worksheet_samples(2, false, 1);
            $work_samples_edta = MiscViral::get_worksheet_samples(2, false, 2);

            $str = '';

            if($work_samples_dbs['count'] > 92) $str .= 'You now have ' . $work_samples_dbs['count'] . ' DBS samples that are eligible for testing.<br />';

            if($work_samples_edta['count'] > 20) $str .=  'You now have ' . $work_samples_edta['count'] . ' Plasma / EDTA samples that are eligible for testing.';

            if($str != '') session(['toast_message' => $str]);
        }

        if($viralsample->receivedstatus && !$viralsample->getOriginal('receivedstatus') && $batch->site_entry == 1){
            return redirect('viralbatch/site_approval_group/' . $batch->id);
        }

        $site_entry_approval = session()->pull('site_entry_approval');

        if($site_entry_approval){
            session(['toast_message' => 'The site entry sample has been approved.']);
            return redirect('viralbatch/site_approval/' . $batch->id);
        }

        return redirect('viralbatch/' . $batch->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sample  $sample
     * @return \Illuminate\Http\Response
     */
    public function save_poc(Request $request, Viralsample $sample)
    {
        if($sample->result){
            $mintime = strtotime('now -5days');
            if($sample->datemodified && strtotime($sample->datemodified) < $mintime){
                session(['toast_message' => 'The result cannot be changed as it was first updated long ago.', 'toast_error' => 1]);
                return back();
            }
            else if(strtotime($sample->datetested) < $mintime){
                session(['toast_message' => 'The result cannot be changed as it was first updated long ago.', 'toast_error' => 1]);
                return back();
            }
        }

        $sample->fill($request->except(['_token', 'lab_id', 'result_2']));

        if(!$sample->result) $sample->result = $request->input('result_2');

        if(!$sample->result){
            session(['toast_message' => 'Please set a result value.', 'toast_error' => 1]);
            return back();
        }

        $sample->pre_update();
        MiscViral::check_batch($sample->batch_id);
        MiscViral::check_worklist(ViralsampleView::class, $sample->worksheet_id);

        $batch = $sample->batch;
        $batch->lab_id = $request->input('lab_id');
        if($batch->batch_complete == 2){
            $batch->datedispatched = date('Y-m-d');
            $batch->batch_complete = 1;
        }
        $batch->pre_update();
        session(['toast_message' => 'The sample has been updated.']);

        return redirect('viralsample/list_poc');        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralsample  $viralsample
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralsample $viralsample)
    {
        if($viralsample->result == NULL && $viralsample->run < 2){
            $batch = $viralsample->batch;
            $viralsample->delete();
            $samples = $batch->sample;
            if($samples->isEmpty()) $batch->delete();
            else{
                MiscViral::check_batch($batch->id);
            }
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

        if(!$facility_id || $facility_id == '') return null;

        if(env('APP_LAB') == 4){
            $fac = Facility::find($facility_id);
            $str = $fac->facilitycode . '/';
            if(!str_contains($patient, $str)) $patient = $str . $patient;
        }

        $viralpatient = Viralpatient::where(['facility_id' => $facility_id, 'patient' => $patient])->first();        
        $data;
        if($viralpatient){
            $viralpatient->most_recent();
            $data[0] = 0;
            $data[1] = $viralpatient->toArray();

            $viralsample = Viralsample::select('id')->where(['patient_id' => $viralpatient->id])->where('result', '>', 1000)->where('repeatt', 0)->first();
            if($viralsample){
                $data[2] = ['previous_nonsuppressed' => 1];
            }
            else{
                $data[2] = ['previous_nonsuppressed' => 0];
            } 
            $data[3] = 0;
            if($viralpatient->most_recent){
                $data[3] = "The date collected for the most recent test of the patient is " . $viralpatient->most_recent->my_date_format('datecollected') . " in batch number " . $viralpatient->most_recent->batch_id;
            }
        }
        else{
            $data[0] = 1;
        }
        return $data;
    }


    public function transfer(Viralsample $sample)
    {
        $sample->sample_received_by = auth()->user()->id;
        $sample->save();
        session(['toast_message' => "The sample has been tranferred to your account."]);
        return back();
    }

    public function runs(Viralsample $sample)
    {
        // $samples = $sample->child;
        $samples = Viralsample::runs($sample)->get();
        $patient = $sample->patient;
        return view('tables.sample_runs', ['patient' => $patient, 'samples' => $samples]);
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function individual(Viralsample $sample)
    {
        $data = Lookup::get_viral_lookups();
        $sample->load(['patient', 'approver', 'batch.lab', 'batch.facility', 'batch.receiver', 'batch.creator']);
        $data['samples'] = [$sample];

        return view('exports.mpdf_viralsamples', $data)->with('pageTitle', 'Individual Samples');
    }

    public function send_sms(ViralsampleView $sample)
    {
        MiscViral::send_sms($sample);
        session(['toast_message' => 'The sms has been sent.']);
        return back();
    }

    public function release_redraw(Viralsample $sample)
    {
        $batch = $sample->batch;
        if($sample->run == 1 || $batch->batch_complete != 0 ){
            session(['toast_message' => 'The sample cannot be released as a redraw.']);
            session(['toast_error' => 1]);
            return back();
        }
        else if($sample->run == 2){
            $prev_sample = $sample->parent;
        }
        else{
            $run = $sample->run - 1;
            $prev_sample = Viralsample::where(['parentid' => $sample->parentid, 'run' => $run])->get()->first();
        }
        
        $sample->delete();

        $prev_sample->labcomment = "Failed Test";
        $prev_sample->repeatt = 0;
        $prev_sample->result = "Collect New Sample";
        $prev_sample->approvedby = auth()->user()->id;
        $prev_sample->approvedby2 = auth()->user()->id;
        $prev_sample->dateapproved = date('Y-m-d');
        $prev_sample->dateapproved2 = date('Y-m-d');

        $prev_sample->save();
        MiscViral::check_batch($prev_sample->batch_id);
        session(['toast_message' => 'The sample has been released as a redraw.']);
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

    public function approve_nhrl(Request $request)
    {
        $viralsamples = $request->input('samples');
        $submit_type = $request->input('submit_type');
        $user = auth()->user();

        $batches = Viralsample::selectRaw("distinct batch_id")->whereIn('id', $viralsamples)->get();

        if($submit_type == "release"){
            Viralsample::whereIn('id', $viralsamples)->update(['synched' => 0, 'approvedby' => $user->id, 'dateapproved' => date('Y-m-d')]);
            session(['toast_message' => 'The samples have been sent to NASCOP.']);
        }
        else{
            Viralsample::whereIn('id', $viralsamples)->delete();
            session(['toast_message' => 'The samples have been sent to deleted.']);
        }

        foreach ($batches as $key => $value) {
            MiscViral::check_batch($value->batch_id);
        } 
        return back();
    }

    public function site_sample_page()
    {
        return view('forms.upload_site_samples', ['type' => 'viralload'])->with('pageTitle', 'Upload Facility Samples');
    }

    public function upload_site_samples(Request $request)
    {
        $file = $request->upload->path();
        $path = $request->upload->store('public/site_samples/vl');

        $problem_rows = 0;
        $created_rows = 0;

        $existing_rows = [];

        $handle = fopen($file, "r");

        if(env('APP_LAB') == 8){
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE){
                $facility = Facility::locate($row[4])->get()->first();
                if(!$facility || !is_numeric($row[4])) continue;

                $datecollected = Lookup::other_date($row[9]);
                $datereceived = Lookup::other_date($row[13]);
                if(!$datereceived) $datereceived = date('Y-m-d');
                $patient_string = $row[2];
                $existing = ViralsampleView::where(['facility_id' => $facility->id, 'patient' => $patient_string, 'datecollected' => $datecollected])->get()->first();

                if($existing){
                    $existing_rows[] = $existing->toArray();
                    continue;
                }

                $batch = Viralbatch::withCount(['sample'])
                                        ->where('received_by', auth()->user()->id)
                                        ->where('datereceived', $datereceived)
                                        ->where('input_complete', 0)
                                        ->where('site_entry', 1)
                                        ->where('facility_id', $facility->id)
                                        ->get()->first();

                if($batch){
                    if($batch->sample_count > 9){
                        unset($batch->sample_count);
                        $batch->full_batch();
                        $batch = null;
                    }
                }

                if(!$batch){
                    $batch = new Viralbatch;
                    $batch->user_id = auth()->user()->id;
                    $batch->facility_id = $facility->id;
                    $batch->received_by = auth()->user()->id;
                    $batch->lab_id = auth()->user()->lab_id;
                    $batch->datereceived = $datereceived;
                    $batch->site_entry = 1;
                    $batch->save();
                }

                $patient = Viralpatient::existing($facility->id, $patient_string)->first();
                if(!$patient) $patient = new Viralpatient;

                $patient->patient = $patient_string;
                $patient->facility_id = $facility->id;
                $patient->dob = Lookup::calculate_dob($datecollected, $row[7]);
                $patient->sex = Lookup::get_gender($row[6]);
                $patient->initiation_date = Lookup::other_date($row[11]);
                $patient->save();


                $sample = new Viralsample;
                $sample->batch_id = $batch->id;
                $sample->patient_id = $patient->id;
                $sample->datecollected = $datecollected;
                $sample->age = $row[7];
                if(str_contains(strtolower($row[8]), ['edta'])) $sample->sampletype = 2; 

                $sample->areaname = $row[5];
                $sample->label_id = $row[1];
                $sample->prophylaxis = Lookup::viral_regimen($row[10]);
                $sample->justification = Lookup::justification($row[12]);
                $sample->pmtct = 3;
                $sample->receivedstatus = 1;
                $sample->save();

                $created_rows++;
            }
        }
        else{
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE){

                $facility = Facility::locate($row[3])->get()->first();
                if(!$facility) continue;
                $datecollected = Lookup::other_date($row[8]);
                $datereceived = Lookup::other_date($row[15]);
                if(!$datereceived) $datereceived = date('Y-m-d');
                $existing = ViralsampleView::where(['facility_id' => $facility->id, 'patient' => $row[1], 'datecollected' => $datecollected])->get()->first();

                if($existing){
                    $existing_rows[] = $existing->toArray();
                    continue;
                }

                $site_entry = Lookup::get_site_entry($row[14]);

                $batch = Viralbatch::withCount(['sample'])
                                        ->where('received_by', auth()->user()->id)
                                        ->where('datereceived', $datereceived)
                                        ->where('input_complete', 0)
                                        ->where('site_entry', $site_entry)
                                        ->where('facility_id', $facility->id)
                                        ->get()->first();

                if($batch){
                    if($batch->sample_count > 9){
                        unset($batch->sample_count);
                        $batch->full_batch();
                        $batch = null;
                    }
                }

                if(!$batch){
                    $batch = new Viralbatch;
                    $batch->user_id = $facility->facility_user->id;
                    $batch->facility_id = $facility->id;
                    $batch->received_by = auth()->user()->id;
                    $batch->lab_id = auth()->user()->lab_id;
                    $batch->datereceived = $datereceived;
                    $batch->site_entry = $site_entry;
                    $batch->save();
                }

                $patient = Viralpatient::existing($facility->id, $row[1])->get()->first();
                if(!$patient){
                    $patient = new Viralpatient;
                }
                $dob = Lookup::other_date($row[5]);
                if (!$dob) {
                    if(strlen($row[5]) == 4) $dob = $row[5] . '-01-01';
                }
                if($dob) $patient->dob = $dob;            
                $patient->facility_id = $facility->id;
                $patient->patient = $row[1];
                $patient->sex = Lookup::get_gender($row[4]);
                $patient->initiation_date = Lookup::other_date($row[9]);
                if(!$patient->dob && $row[6]) $patient->dob = Lookup::calculate_dob($datecollected, $row[6]); 
                $patient->pre_update();

                $sample = new Viralsample;
                $sample->batch_id = $batch->id;
                $sample->patient_id = $patient->id;
                $sample->datecollected = $datecollected;
                $sample->age = $row[6];
                if(!$sample->age) $sample->age = Lookup::calculate_viralage($datecollected, $patient->dob);
                $sample->prophylaxis = Lookup::viral_regimen($row[10]);
                $sample->dateinitiatedonregimen = Lookup::other_date($row[11]);
                $sample->justification = Lookup::justification($row[12]);
                $sample->sampletype = (int) $row[7];
                $sample->pmtct = $row[13];
                $sample->receivedstatus = $row[16];
                if(is_numeric($row[17])) $sample->rejectedreason = $row[17];
                $sample->save();
                $created_rows++;
            }
        }
        session(['toast_message' => "{$created_rows} samples have been created."]);

        if($existing_rows){

            Excel::create("samples_that_were_already_existing", function($excel) use($existing_rows) {
                $excel->sheet('Sheetname', function($sheet) use($existing_rows) {
                    $sheet->fromArray($existing_rows);
                });
            })->download('csv');

        }

        return redirect('/viralbatch');        
    }


    public function transfer_samples_form($facility_id=null)
    {
        $samples = ViralsampleView::whereNull('receivedstatus')
                    ->where('site_entry', '!=', 2)
                    ->when($facility_id, function($query) use($facility_id){
                        return $query->where('facility_id', $facility_id);
                    })
                    ->whereNull('datetested')
                    ->where(['repeatt' => 0])
                    ->where('created_at', '>', date('Y-m-d', strtotime("-3 months")))
                    ->paginate(25);

        $samples->setPath(url()->current());

        if($facility_id) $facility = \App\Facility::find($facility_id);

        $data = [
            'samples' => $samples,
            'labs' => \App\Lab::all(),
            'facility' => $facility ?? null,
            'pre' => 'viral',
        ];

        return view('forms.transfer_samples', $data);
    }

    public function transfer_samples(Request $request)
    {
        $samples = $request->input('samples');
        $lab = $request->input('lab');
        // dd($samples);
        \App\Synch::transfer_sample('vl', $lab, $samples);
        return back();
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(viralbatches.facility_id='{$user->facility_id}' OR viralbatches.user_id='{$user->id}')";

        $samples = Viralsample::select('viralsamples.id')
            ->whereRaw("viralsamples.id like '" . $search . "%'")
            ->when($facility_user, function($query) use ($string){
                return $query->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')->whereRaw($string);
            })
            ->paginate(10);

        $samples->setPath(url()->current());
        return $samples;
    }

    public function ord_no(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(facility_id='{$user->facility_id}' OR user_id='{$user->id}')";

        $samples = ViralsampleView::select(['id', 'order_no', 'patient'])
            ->whereRaw("order_no like '%" . $search . "%'")
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->paginate(10);

        $samples->setPath(url()->current());
        return $samples;
    }


    private function clear_session(){
        session()->forget('viral_batch');
        session()->forget('viral_facility_name');
        session()->forget('viral_batch_total');
        session()->forget('viral_last_patient');

        // session()->forget('viral_batch_no');
        // session()->forget('viral_batch_dispatch');
        // session()->forget('viral_batch_dispatched');
        // session()->forget('viral_batch_received');
        // session()->forget('viral_facility_id');
        // session()->forget('viral_facility_name');
    }

    public function extract_excel_results(Request $request) {
        if ($request->method() == 'POST') {
            $file = $request->excelupload->path();
            $excelData = Excel::load($file, function($reader){
                $reader->toArray();                
            })->get();
            $data = $excelData;
            $newData = [];
            $newData[] = ['Test Type','TestingLab','SpecimenLabelID','SpecimenClientCode','FacilityName','MFLCode','Sex','PMTCT','Age','DOB','SampleType','DateCollected','CurrentRegimen','regimenLine','ART Init Date','Justification','DateReceived','loginDate','ReceivedStatus','RejectedReason','ReasonforRepeat','LabComment','Datetested','DateDispatched','Results','Edited'];
            // dd($data);
            foreach ($data as $key => $sample) {
                $sample = collect($sample)->flatten(1)->toArray();
                // dd($sample[3]);
                // $sample = (array)$sample;
                $dbsample = ViralsampleView::where('patient', '=', $sample[3])->where('datecollected', '=', $sample[11])->last();
                $sample[19] = $dbsample->rejectedreason ?? null;
                $sample[20] = $dbsample->reason_for_repeat ?? null;
                $sample[21] = $dbsample->labcomment ?? null;
                $sample[22] = (isset($dbsample->datetested)) ? date('m/d/Y', strtotime($dbsample->datetested)) : null;
                $sample[23] = (isset($dbsample->datedispatched)) ? date('m/d/Y', strtotime($dbsample->datedispatched)) : null;
                // $sample[22] = $dbsample->datetested;
                // $sample[23] = $dbsample->datedispatched;
                $sample[24] = $dbsample->result ?? null;

                $newData[] = $sample->toArray();
            }
            // dd($newData);
            ini_set("memory_limit", "-1");
            ini_set("max_execution_time", "3000");
            $title = 'EDARP reffered sample';
            Excel::create($title, function($excel) use ($newData, $title) {
                $excel->setTitle($title);
                $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('EID/VL System');
                $excel->setDescription($title);

                $excel->sheet('Sheet1', function($sheet) use ($newData) {
                    $sheet->fromArray($newData, null, 'A1', false, false);
                });

            })->download('xlsx');
            
        } else if ($request->method() == 'GET') {
            return view('forms.viralsamplesexcelextract')->with('pageTitle', 'Get Sample');
        }
    }
}