<?php

namespace App\Http\Controllers;

use App\Viralworksheet;
use App\Viralsample;
use App\ViralsampleView;
use App\User;
use App\MiscViral;
use App\Lookup;
use DB;
use Excel;
use Illuminate\Http\Request;

class ViralworksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state=0, $date_start=NULL, $date_end=NULL, $worksheet_id=NULL)
    {
        $worksheets = Viralworksheet::selectRaw('viralworksheets.*, count(viralsamples.id) AS samples_no, users.surname, users.oname')
            ->leftJoin('viralsamples', 'viralsamples.worksheet_id', '=', 'viralworksheets.id')
            ->join('users', 'users.id', '=', 'viralworksheets.createdby')
            ->when($worksheet_id, function ($query) use ($worksheet_id){
                return $query->where('viralworksheets.id', $worksheet_id);
            })
            ->when($state, function ($query) use ($state){
                return $query->where('status_id', $state);
            })
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('viralworksheets.created_at', '>=', $date_start)
                    ->whereDate('viralworksheets.created_at', '<=', $date_end);
                }
                return $query->whereDate('viralworksheets.created_at', $date_start);
            })
            ->orderBy('viralworksheets.created_at', 'desc')
            ->groupBy('viralworksheets.id')
            ->paginate();

        $worksheets->setPath(url()->current());

        // return view('tables.viralworksheets', ['worksheets' => $worksheets, 'statuses' => $statuses, 'machines' => $machines]);
        
        // $statuses = collect($this->wstatus());
        // $machines = collect($this->wmachine());

        $data = Lookup::worksheet_lookups();
        $data['worksheets'] = $worksheets;
        $data['myurl'] = url('viralworksheet/index/' . $state . '/');
        return view('tables.viralworksheets', $data)->with('pageTitle', 'Worksheets');
    }

    public function set_sampletype_form($machine_type, $calibration=false)
    {
        $data = Lookup::worksheet_lookups();
        $data['machine_type'] = $machine_type;
        $data['calibration'] = $calibration;
        return view('forms.set_viralworksheet_sampletype', $data)->with('pageTitle', 'Set Sample Type');
    }

    public function set_sampletype(Request $request)
    {
        $sampletype = $request->input('sampletype');
        $machine_type = $request->input('machine_type');
        $calibration = $request->input('calibration');
        return redirect("/viralworksheet/create/{$sampletype}/{$machine_type}/{$calibration}");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($sampletype, $machine_type=2, $calibration=false)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        $test = in_array(env('APP_LAB'), Lookup::$worksheet_received);
        $user = auth()->user();

        if($machine == NULL || $machine->vl_limit == NULL) return back();

        $limit = $machine->vl_limit;
        if($calibration) $limit = $machine->vl_calibration_limit;
        
        $year = date('Y') - 1;
        if(date('m') < 7) $year --;
        $date_str = $year . '-12-31';

        if($test){
            $repeats = Viralsample::selectRaw("viralsamples.*, viralpatients.patient, facilitys.name, viralbatches.datereceived, viralbatches.highpriority, viralbatches.site_entry, users.surname, users.oname, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
                ->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
                ->leftJoin('users', 'users.id', '=', 'viralbatches.user_id')
                ->join('viralpatients', 'viralsamples.patient_id', '=', 'viralpatients.id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
                ->where('datereceived', '>', $date_str)
                ->when($sampletype, function($query) use ($sampletype){
                    if($sampletype == 1) return $query->whereIn('sampletype', [3, 4]);
                    if($sampletype == 2) return $query->whereIn('sampletype', [1, 2]);                    
                })
                ->where('site_entry', '!=', 2)
                ->having('isnull', 0)
                ->whereRaw("(worksheet_id is null or worksheet_id=0)")
                ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw("(result IS NULL OR result='0')")
                ->orderBy('viralsamples.id', 'asc')
                ->limit($limit)
                ->get();
            $limit -= $repeats->count();
        }

        $samples = Viralsample::selectRaw("viralsamples.*, viralpatients.patient, facilitys.name, viralbatches.datereceived, viralbatches.highpriority, viralbatches.site_entry, users.surname, users.oname, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
            ->leftJoin('users', 'users.id', '=', 'viralbatches.user_id')
            ->join('viralpatients', 'viralsamples.patient_id', '=', 'viralpatients.id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->where('datereceived', '>', $date_str)
            ->when($test, function($query) use ($user){
                return $query->where('received_by', $user->id)->having('isnull', 1);
            })
            ->when($sampletype, function($query) use ($sampletype){
                if($sampletype == 1) return $query->whereIn('sampletype', [3, 4]);
                if($sampletype == 2) return $query->whereIn('sampletype', [1, 2]);                    
            })
            ->where('site_entry', '!=', 2)
            ->whereRaw("(worksheet_id is null or worksheet_id=0)")
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw("(result IS NULL OR result='0')")
            ->orderBy('isnull', 'asc')
            ->orderBy('highpriority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('site_entry', 'asc')
            ->orderBy('viralsamples.id', 'asc')
            ->limit($limit)
            ->get();

        if($test) $samples = $repeats->merge($samples);
        $count = $samples->count();

        if($count == $machine->vl_limit || ($calibration && $count == $machine->vl_calibration_limit)){
            return view('forms.viralworksheets', ['create' => true, 'machine_type' => $machine_type, 'samples' => $samples, 'calibration' => $calibration, 'sampletype' => $sampletype])->with('pageTitle', 'Add Worksheet');
        }

        return view('forms.viralworksheets', ['create' => false, 'machine_type' => $machine_type, 'count' => $count])->with('pageTitle', 'Add Worksheet');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $worksheet = new Viralworksheet;
        $worksheet->fill($request->except('_token'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();
        $sampletype = $worksheet->sampletype;

        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $worksheet->machine_type)->first();

        $test = in_array(env('APP_LAB'), Lookup::$worksheet_received);
        $user = auth()->user();

        $limit = $machine->vl_limit;
        if($worksheet->calibration) $limit = $machine->vl_calibration_limit;
        
        $year = date('Y') - 1;
        if(date('m') < 7) $year--;
        $date_str = $year . '-12-31';

        if($test){
            $repeats = Viralsample::selectRaw("viralsamples.*, viralpatients.patient, facilitys.name, viralbatches.datereceived, viralbatches.highpriority, viralbatches.site_entry, users.surname, users.oname, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
                ->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
                ->leftJoin('users', 'users.id', '=', 'viralbatches.user_id')
                ->join('viralpatients', 'viralsamples.patient_id', '=', 'viralpatients.id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
                ->where('datereceived', '>', $date_str)
                ->when($sampletype, function($query) use ($sampletype){
                    if($sampletype == 1) return $query->whereIn('sampletype', [3, 4]);
                    if($sampletype == 2) return $query->whereIn('sampletype', [1, 2]);                    
                })
                ->where('site_entry', '!=', 2)
                ->having('isnull', 0)
                ->whereRaw("(worksheet_id is null or worksheet_id=0)")
                ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw("(result IS NULL OR result='0')")
                ->orderBy('viralsamples.id', 'asc')
                ->limit($limit)
                ->get();
            $limit -= $repeats->count();
        }

        if($limit != 0){

            $samples = Viralsample::selectRaw("viralsamples.*, viralpatients.patient, facilitys.name, viralbatches.datereceived, viralbatches.highpriority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
                ->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
                ->join('viralpatients', 'viralsamples.patient_id', '=', 'viralpatients.id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
                ->where('datereceived', '>', $date_str)
                ->when($sampletype, function($query) use ($sampletype){
                    if($sampletype == 1) return $query->whereIn('sampletype', [3, 4]);
                    if($sampletype == 2) return $query->whereIn('sampletype', [1, 2]);                    
                })
                ->where('site_entry', '!=', 2)
                ->whereRaw("(worksheet_id is null or worksheet_id=0)")
                ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw("(result IS NULL OR result='0')")
                ->orderBy('isnull', 'asc')
                ->orderBy('highpriority', 'asc')
                ->orderBy('datereceived', 'asc')
                ->orderBy('site_entry', 'asc')
                ->orderBy('viralsamples.id', 'asc')
                ->limit($limit)
                ->get();
            $samples = $repeats->merge($samples);
        }

        if(!isset($samples)){
            $samples = $repeats;            
        }

        $count = $samples->count();

        if($count == $machine->vl_limit || ($calibration && $count == $machine->vl_calibration_limit)){

            $sample_ids = $samples->pluck('id')->toArray();
            Viralsample::whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id]);
            return redirect()->route('viralworksheet.print', ['worksheet' => $worksheet->id]);
        }
        else{
            $worksheet->delete();
            session(['toast_message' => "The worksheet could not be created."]);
            return back();            
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Viralworksheet $Viralworksheet)
    {
        $Viralworksheet->load(['creator']);
        $sample_array = ViralsampleView::select('id')->where('worksheet_id', $Viralworksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        $samples = Viralsample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();

        $data = ['worksheet' => $Viralworksheet, 'samples' => $samples];

        if($Viralworksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Other Worksheets');
        }
        else if($Viralworksheet->machine_type == 3){
            return view('worksheets.c8800', $data)->with('pageTitle', 'C8800 Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Abbot Worksheets');
        }
    }

    public function find(Viralworksheet $worksheet)
    {
        session(['toast_message' => 'Found 1 worksheet.']);
        return $this->index(0, null, null, $worksheet->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Viralworksheet $Viralworksheet)
    {
        $samples = $Viralworksheet->sample;
        return view('forms.viralworksheets', ['create' => true, 'machine_type' => $Viralworksheet->machine_type, 'samples' => $samples, 'worksheet' => $Viralworksheet])->with('pageTitle', 'Edit Worksheet');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Viralworksheet $Viralworksheet)
    {
        $Viralworksheet->fill($request->except('_token'));
        $Viralworksheet->save();
        return redirect('viralworksheet/print/' . $Viralworksheet->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralworksheet $Viralworksheet)
    {
        //
    }

    public function print(Viralworksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $sample_array = ViralsampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        $samples = Viralsample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples, 'print' => true];

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Print Worksheet');
        }
        else if($worksheet->machine_type == 3){
            return view('worksheets.c8800', $data)->with('pageTitle', 'C8800 Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Print Abbot Worksheet');
        }
    }

    public function convert_worksheet($machine_type, Viralworksheet $worksheet)
    {
        if($machine_type == 1 || $worksheet->machine_type == 1 || $worksheet->status_id != 1){
            session(['toast_message' => 'The worksheet cannot be converted to the requested type.']);
            session(['toast_error' => 1]);
            return back();            
        }
        $worksheet->machine_type = $machine_type;
        $worksheet->save();
        return redirect('viralworksheet/' . $worksheet->id . '/edit');
    }

    public function cancel(Viralworksheet $worksheet)
    {
        if($worksheet->status_id != 1){
            session(['toast_message' => 'The worksheet is not eligible to be cancelled.']);
            session(['toast_error' => 1]);
            return back();
        }
        $sample_array = ViralsampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Viralsample::whereIn('id', $sample_array)->update(['worksheet_id' => null, 'result' => null]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        session(['toast_message' => 'The worksheet has been cancelled.']);
        return redirect("/viralworksheet");
    }

    public function cancel_upload(Viralworksheet $worksheet)
    {
        if($worksheet->status_id != 2){
            session(['toast_message' => 'The upload for this worksheet cannot be reversed.']);
            session(['toast_error' => 1]);
            return back();
        }

        if($worksheet->uploadedby != auth()->user()->id){
            session(['toast_message' => 'Only the user who uploaded the results can reverse the upload.']);
            session(['toast_error' => 1]);
            return back();
        }

        $sample_array = ViralsampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Viralsample::whereIn('id', $sample_array)->update(['result' => null, 'interpretation' => null, 'datemodified' => null, 'datetested' => null]);
        $worksheet->status_id = 1;
        $worksheet->neg_control_interpretation = $worksheet->highpos_control_interpretation = $worksheet->lowpos_control_interpretation = $worksheet->neg_control_result = $worksheet->highpos_control_result = $worksheet->lowpos_control_result = $worksheet->daterun = $worksheet->dateuploaded = $worksheet->uploadedby = $worksheet->datereviewed = $worksheet->reviewedby = $worksheet->datereviewed2 = $worksheet->reviewedby2 = null;
        $worksheet->save();

        session(['toast_message' => 'The upload has been reversed.']);
        return redirect("/viralworksheet/upload/" . $worksheet->id);
    }

    public function reverse_upload(Viralworksheet $worksheet)
    {
        $worksheet->status_id = 1;
        $worksheet->neg_control_interpretation = $worksheet->highpos_control_interpretation = $worksheet->lowpos_control_interpretation = $worksheet->neg_control_result = $worksheet->highpos_control_result = $worksheet->lowpos_control_result = $worksheet->daterun = $worksheet->dateuploaded = $worksheet->uploadedby = $worksheet->datereviewed = $worksheet->reviewedby = $worksheet->datereviewed2 = $worksheet->reviewedby2 = null;
        $worksheet->save();

        $batches_data = ['batch_complete' => 0, 'sent_email' => 0, 'printedby' => null,  'dateemailsent' => null, 'datebatchprinted' => null, 'dateindividualresultprinted' => null, 'datedispatched' => null, ];
        $samples_data = ['datetested' => null, 'result' => null, 'interpretation' => null, 'repeatt' => 0, 'approvedby' => null, 'approvedby2' => null, 'datemodified' => null, 'dateapproved' => null, 'dateapproved2' => null, 'tat1' => null, 'tat2' => null, 'tat3' => null, 'tat4' => null];

        // $samples = Viralsample::where(['worksheet_id' => $worksheet->id, 'repeatt' => 1])->get();
        $samples = Viralsample::where(['worksheet_id' => $worksheet->id])->get();

        foreach ($samples as $key => $sample) {
            if($sample->parentid == 0) $del_samples = Viralsample::where('parentid', $sample->id)->get();
            else{
                $run = $sample->run+1;
                $del_samples = Viralsample::where(['parentid' => $sample->parentid, 'run' => $run])->get();
            }
            foreach ($del_samples as $del) {
                if($del->worksheet_id && $del->result){  
                    if($sample->parentid == 0){
                        if($del->run == 2){
                            $del->run = 1;
                            $del->parentid = 0;
                            $del->repeatt = 1;
                            $del->pre_update();

                            $sample->run = 2;
                            $sample->parentid = $del->id;
                        }
                    } 
                    else{
                        $del->run--;
                        $del->pre_update();
                        $sample->run++;
                    }
                }
                else{
                    $del->pre_delete();                       
                }
            }

            $sample->fill($samples_data);
            $sample->pre_update();
            $batch_ids[$key] = $sample->batch_id;
        }
        $batch_ids = collect($batch_ids);
        $unique = $batch_ids->unique();

        foreach ($unique as $key => $id) {
            $batch = \App\Viralbatch::find($id);
            $batch->fill($batches_data);
            $batches->pre_update();
        }

    }

    public function upload(Viralworksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $users = User::where('user_type_id', 1)->get();
        return view('forms.upload_results', ['worksheet' => $worksheet, 'users' => $users, 'type' => 'viralload'])->with('pageTitle', 'Worksheet Upload');
    }





    /**
     * Update the specified resource in storage with results file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function save_results(Request $request, Viralworksheet $worksheet)
    {
        $worksheet->fill($request->except(['_token', 'upload']));
        $file = $request->upload->path();
        $path = $request->upload->store('public/results/vl');
        $today = $dateoftest = date("Y-m-d");
        $nc = $nc_int = $lpc = $lpc_int = $hpc = $hpc_int = NULL;

        $my = new MiscViral;

        if($worksheet->machine_type == 2)
        {
            $dateoftest = $today;
            // config(['excel.import.heading' => false]);
            $data = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $bool = false;

            foreach ($data as $key => $value) {
                if($value[5] == "RESULT"){
                    $bool = true;
                    continue;
                }

                if($bool){
                    $sample_id = $value[1];
                    $result = $value[5];
                    $interpretation = $value[6];
                    $error = $value[10];

                    $result_array = MiscViral::sample_result($result, $error);

                    if($sample_id == "HIV_NEG"){
                        $nc = $result_array['result'];
                        $nc_int = $result_array['interpretation']; 
                        $nc_units = $result_array['units']; 
                    }else if($sample_id == "HIV_HIPOS"){
                        $hpc = $result_array['result'];
                        $hpc_int = $result_array['interpretation'];
                        $hpc_units = $result_array['units'];
                    }else if($sample_id == "HIV_LOPOS"){
                        $lpc = $result_array['result'];
                        $lpc_int = $result_array['interpretation'];
                        $lpc_units = $result_array['units'];
                    }

                    $data_array = ['datemodified' => $today, 'datetested' => $dateoftest, 'interpretation' => $result_array['interpretation'], 'result' => $result_array['result'], 'units' => $result_array['units']];
                    // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    // Viralsample::where($search)->update($data_array);

                    $sample = Viralsample::find($sample_id);
                    if(!$sample) continue;
                    if($sample->worksheet_id != $worksheet->id) continue;
                    $sample->fill($data_array);
                    $sample->save();

                }
                if($bool && $value[5] == "RESULT") break;
            }
        }
        else if($worksheet->machine_type == 3){
            $handle = fopen($file, "r");
            while (($value = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $sample_id = $value[1];
                $result = $value[6];
                $result_array = MiscViral::exponential_result($result);

                if(!is_numeric($sample_id)){
                    $control = $value[4];
                    if($control == 'HxV H (+) C'){
                        $hpc = $result_array['result'];
                        $hpc_int = $result_array['interpretation'];
                        $hpc_units = $result_array['units'];                        
                    }
                    else if($control == 'HxV L (+) C'){
                        $lpc = $result_array['result'];
                        $lpc_int = $result_array['interpretation'];
                        $lpc_units = $result_array['units'];
                    }
                    else if($control == '(-) C'){
                        $nc = $result_array['result'];
                        $nc_int = $result_array['interpretation']; 
                        $nc_units = $result_array['units']; 
                    }
                }
                $data_array = array_merge(['datemodified' => $today, 'datetested' => $today], $result_array);

                $sample = Viralsample::find($sample_id);
                if(!$sample) continue;
                if($sample->worksheet_id != $worksheet->id) continue;
                $sample->fill($data_array);
                $sample->save();
            }
        }
        else
        {
            $handle = fopen($file, "r");
            while (($value = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $dateoftest=date("Y-m-d", strtotime($value[3]));

                $sample_id = $value[4];
                $result = $value[8];
                $error = $value[10];

                $result_array = MiscViral::sample_result($result, $error);

                $sample_type = $value[5];

                if($sample_type == "NC"){
                    $nc = $result_array['result'];
                    $nc_int = $result_array['interpretation']; 
                    $nc_units = $result_array['units']; 
                }
                else if($sample_type == "HPC"){
                    $hpc = $result_array['result'];
                    $hpc_int = $result_array['interpretation'];
                    $hpc_units = $result_array['units'];
                }
                else if($sample_type == "LPC"){
                    $lpc = $result_array['result'];
                    $lpc_int = $result_array['interpretation'];
                    $lpc_units = $result_array['units'];
                }

                // $data_array = ['datemodified' => $today, 'datetested' => $dateoftest, 'interpretation' => $result_array['interpretation'], 'result' => $result_array['result'], 'units' => $result_array['units']];
                // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                // Viralsample::where($search)->update($data_array);

                $data_array = array_merge(['datemodified' => $today, 'datetested' => $dateoftest], $result_array);

                $sample = Viralsample::find($sample_id);
                if(!$sample) continue;
                if($sample->worksheet_id != $worksheet->id) continue;
                $sample->fill($data_array);
                $sample->save();

            }
            fclose($handle);

        }

        Viralsample::where(['worksheet_id' => $worksheet->id])->where('run', 0)->update(['run' => 1]);

        $worksheet->neg_units = $nc_units;
        $worksheet->neg_control_interpretation = $nc_int;
        $worksheet->neg_control_result = $nc;

        $worksheet->hpc_units = $hpc_units;
        $worksheet->highpos_control_interpretation = $hpc_int;
        $worksheet->highpos_control_result = $hpc;

        $worksheet->lpc_units = $lpc_units;
        $worksheet->lowpos_control_interpretation = $lpc_int;
        $worksheet->lowpos_control_result = $lpc;

        $worksheet->daterun = $dateoftest;
        $worksheet->uploadedby = auth()->user()->id;

        $worksheet->save();

        MiscViral::requeue($worksheet->id);
        session(['toast_message' => "The worksheet has been updated with the results."]);

        return redirect('viralworksheet/approve/' . $worksheet->id);
    }


    public function approve_results(Viralworksheet $worksheet)
    {
        $worksheet->load(['reviewer', 'creator', 'runner', 'sorter', 'bulker']);
        
        $samples = Viralsample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();

        $noresult = $this->checknull($this->get_worksheet_results(0, $worksheet->id));
        $failed = $this->checknull($this->get_worksheet_results(3, $worksheet->id));
        $detected = $this->checknull($this->get_worksheet_results(2, $worksheet->id));
        $undetected = $this->checknull($this->get_worksheet_results(1, $worksheet->id));

        $total = $detected + $undetected + $failed + $noresult;

        $subtotals = ['detected' => $detected, 'undetected' => $undetected, 'failed' => $failed, 'noresult' => $noresult, 'total' => $total];

        $data = Lookup::worksheet_approve_lookups();
        $data['samples'] = $samples;
        $data['subtotals'] = $subtotals;
        $data['worksheet'] = $worksheet;

        return view('tables.confirm_viral_results', $data)->with('pageTitle', 'Approve Results');
    }

    public function approve(Request $request, Viralworksheet $worksheet)
    {
        $double_approval = Lookup::$double_approval;
        $samples = $request->input('samples');
        $batches = $request->input('batches');
        $redraws = $request->input('redraws');
        $results = $request->input('results');
        $actions = $request->input('actions');
        $dilutions = $request->input('dilutionfactors');

        if(!$redraws) $redraws = [];

        $today = date('Y-m-d');
        $approver = auth()->user()->id;

        $batch = array();

        foreach ($samples as $key => $value) {

            if(in_array(env('APP_LAB'), $double_approval) && $worksheet->reviewedby && !$worksheet->reviewedby2){
                $data = [
                    'approvedby2' => $approver,
                    'dateapproved2' => $today,
                ];
            }
            else{
                $data = [
                    'approvedby' => $approver,
                    'dateapproved' => $today,
                ];
            }

            $data['repeatt'] = $actions[$key];
            $data['dilutionfactor'] = $dilutions[$key];

            if(is_int($results[$key])){
                $data['result'] = $results[$key] * $dilutions[$key];
            }
            else{
                $data['result'] = $results[$key];
            }

            // if(isset($redraws[$value])) {
            if(in_array($samples[$key], $redraws)) {
                $data['result'] = "Collect New Sample";
                $data['labcomment'] = "Failed Test";
                $data['repeatt'] = 0;
                // dd($data);
            }
            $sample = Viralsample::find($samples[$key]);
            $sample->fill($data);
            $sample->pre_update();

            // Viralsample::where('id', $samples[$key])->update($data);

            if($data['repeatt'] == 1) MiscViral::save_repeat($samples[$key]);
        }

        if(in_array(env('APP_LAB'), $double_approval)){
            if($worksheet->reviewedby && $worksheet->reviewedby != $approver){
                $batch = collect($batches);
                $b = $batch->unique();
                $unique = $b->values()->all();

                foreach ($unique as $value) {
                    MiscViral::check_batch($value);
                }

                $worksheet->status_id = 3;
                $worksheet->datereviewed2 = $today;
                $worksheet->reviewedby2 = $approver;
                $worksheet->save();

                return redirect('/viralbatch/dispatch');                 
            }
            else{
                $worksheet->datereviewed = $today;
                $worksheet->reviewedby = $approver;
                $worksheet->save();
                session(['toast_message' => "The worksheet has been approved. It is awaiting the second approval before the results can be prepared for dispatch."]);

                return redirect('/viralworksheet');
            }
        }

        else{
            $batch = collect($batches);
            $b = $batch->unique();
            $unique = $b->values()->all();

            foreach ($unique as $value) {
                MiscViral::check_batch($value);
            }

            $worksheet->status_id = 3;
            $worksheet->datereviewed = $today;
            $worksheet->reviewedby = $approver;
            $worksheet->save();
            session(['toast_message' => "The worksheet has been approved."]);

            return redirect('/viralbatch/dispatch');            
        }
    }




    public function wstatus()
    {
        $statuses = [
            collect(['status' => 1, 'string' => "<strong><font color='#FFD324'>In-Process</font></strong>"]),
            collect(['status' => 2, 'string' => "<strong><font color='#0000FF'>Tested</font></strong>"]),
            collect(['status' => 3, 'string' => "<strong><font color='#339900'>Approved</font></strong>"]),
            collect(['status' => 4, 'string' => "<strong><font color='#FF0000'>Cancelled</font></strong>"]),
        ];

        return $statuses;
    }

    public function wmachine()
    {
        $machines = [
            collect(['machine' => 1, 'string' => "<strong> TaqMan </strong>"]),
            collect(['machine' => 2, 'string' => "<strong><font color='#0000FF'> Abbott </font></strong>"]),
            collect(['machine' => 3, 'string' => "<strong> C8800 </strong>"]),
            collect(['machine' => 4, 'string' => "<strong><font color='#FF00FB'> Panther </font></strong>"]),
        ];

        return $machines;
    }

    public function get_worksheet_results($result, $worksheet_id=NULL)
    {
        $samples = ViralsampleView::selectRaw("count(*) as totals, worksheet_id")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->whereNotNull('worksheet_id')
            ->where('site_entry', '!=', 2)
            ->where('receivedstatus', '!=', 2)
            ->when(true, function($query) use ($result){
                if ($result == 0) {
                    return $query->whereNull('result');
                }
                else if ($result == 1) {
                    return $query->where('result', '< LDL copies/ml');
                }
                else if ($result == 2) {
                    return $query->where('result', '!=', 'Failed')->where('result', '!=', '< LDL copies/ml');
                }
                else if ($result == 3) {
                    return $query->where('result', 'Failed');
                }                
            })
            ->groupBy('worksheet_id')
            ->get();
        
        return $samples;
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $worksheets = Viralworksheet::whereRaw("id like '" . $search . "%'")->paginate(10);
        $worksheets->setPath(url()->current());
        return $worksheets;
    }

    public function checknull($var)
    {
        return $var->first()->totals ?? 0;
    }
}
