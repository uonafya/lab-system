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
use Exception;
use Carbon\Carbon;
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
                if($state == 11 && env('APP_LAB') == 9){
                    return $query->where('status_id', 3)->whereRaw("viralworksheets.id in (
                        SELECT DISTINCT worksheet_id
                        FROM viralsamples_view
                        WHERE facility_id IN (50001, 3475)
                    )");
                }
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

        $data = Lookup::worksheet_lookups();

        $ids = $worksheets->pluck(['id'])->toArray();

        $data['noresult'] = $this->get_worksheet_results(0, $ids);
        $data['failed'] = $this->get_worksheet_results(3, $ids);
        $data['detected'] = $this->get_worksheet_results(2, $ids);
        $data['undetected'] = $this->get_worksheet_results(1, $ids);
        
        $data['reruns'] = $this->get_reruns($ids);

        $data['status_count'] = Viralworksheet::selectRaw("count(*) AS total, status_id, machine_type")
            ->groupBy('status_id', 'machine_type')
            ->orderBy('status_id', 'asc')
            ->orderBy('machine_type', 'asc')
            ->get();
        $data['worksheets'] = $worksheets;
        $data['myurl'] = url('viralworksheet/index/' . $state . '/');
        return view('tables.viralworksheets', $data)->with('pageTitle', 'Worksheets');
    }

    public function set_sampletype_form($machine_type, $calibration=false, $limit=false)
    {
        $data = Lookup::worksheet_lookups();
        $data['machine_type'] = $machine_type;
        $data['calibration'] = $calibration;
        $data['limit'] = $limit;

        return view('forms.set_viralworksheet_sampletype', $data)->with('pageTitle', 'Set Sample Type');
    }

    public function set_sampletype(Request $request)
    {
        $sampletype = $request->input('sampletype');
        $machine_type = $request->input('machine_type');
        $calibration = $request->input('calibration', 0);
        $limit = $request->input('limit');
        return redirect("/viralworksheet/create/{$sampletype}/{$machine_type}/{$calibration}/{$limit}");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($sampletype, $machine_type=2, $calibration=false, $limit=false)
    {
        $data = MiscViral::get_worksheet_samples($machine_type, $calibration, $sampletype, $limit);
        if(!$data){
            session(['toast_message' => 'An error has occurred.', 'toast_error' => 1]);
            return back();
        }
        return view('forms.viralworksheets', $data)->with('pageTitle', 'Create Worksheet');
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
        $worksheet->fill($request->except('_token', 'limit'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();
        $sampletype = $worksheet->sampletype;

        $data = MiscViral::get_worksheet_samples($worksheet->machine_type, $worksheet->calibration, $worksheet->sampletype, $request->input('limit'));

        if(!$data || (!$data['create'])){
            dd($data);
            $worksheet->delete();
            session(['toast_message' => "The worksheet could not be created.", 'toast_error' => 1]);
            return back();            
        }
        
        $samples = $data['samples'];

        $sample_ids = $samples->pluck('id');
        Viralsample::whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id]);
        return redirect()->route('viralworksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Viralworksheet $Viralworksheet, $print=false)
    {
        $Viralworksheet->load(['creator']);
        $sample_array = ViralsampleView::select('id')->where('worksheet_id', $Viralworksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        // $samples = Viralsample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();
        
        $samples = Viralsample::join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
                    ->with(['patient', 'batch.facility'])
                    ->select('viralsamples.*', 'viralbatches.facility_id')
                    ->whereIn('viralsamples.id', $sample_array)
                    ->orderBy('run', 'desc')
                    ->when(true, function($query){
                        // if(!in_array(env('APP_LAB'), [8, 9, 1])) return $query->orderBy('facility_id')->orderBy('batch_id', 'asc');
                        if(in_array(env('APP_LAB'), [3])) $query->orderBy('datereceived', 'asc');
                        if(!in_array(env('APP_LAB'), [8, 9, 1])) return $query->orderBy('batch_id', 'asc');
                    })
                    ->orderBy('viralsamples.id', 'asc')
                    ->get();

        $data = ['worksheet' => $Viralworksheet, 'samples' => $samples, 'i' => 0];

        if($print) $data['print'] = true;

        if($Viralworksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Other Worksheets');
        }
        else if($Viralworksheet->machine_type == 3){
            return view('worksheets.c-8800', $data)->with('pageTitle', 'C8800 Worksheets');
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
        if($Viralworksheet->status_id != 4){
            session(['toast_error' => 1, 'toast_message' => 'The worksheet cannot be deleted.']);
            return back();
        }
        // DB::table("viralsamples")->where('worksheet_id', $Viralworksheet->id)->update(['worksheet_id' => NULL, 'result' => NULL]);
        $Viralworksheet->delete();
        return back();
    }

    public function print(Viralworksheet $worksheet)
    {
        return $this->show($worksheet, true);
    }

    public function convert_worksheet(Viralworksheet $worksheet, $machine_type)
    {
        if($machine_type == 1 || $worksheet->machine_type == 1 || $worksheet->status_id != 1){
            session(['toast_message' => 'The worksheet cannot be converted to the requested type.']);
            session(['toast_error' => 1]);
            return back();            
        }
        $worksheet->machine_type = $machine_type;
        $worksheet->save();
        // return back();
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

        if($worksheet->uploadedby != auth()->user()->id && auth()->user()->user_type_id != 0){
            session(['toast_message' => 'Only the user who uploaded the results can reverse the upload.']);
            session(['toast_error' => 1]);
            return back();
        }

        $samples = Viralsample::where(['repeatt' => 1, 'worksheet_id' => $worksheet->id])->get();

        foreach ($samples as $sample) {
            $sample->remove_rerun();
        }

        $sample_array = ViralsampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();

        Viralsample::whereIn('id', $sample_array)->update(['result' => null, 'interpretation' => null, 'datemodified' => null, 'datetested' => null, 'repeatt' => 0, 'dateapproved' => null, 'approvedby' => null]);
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
            $batch->pre_update();
        }
        return back();

    }

    public function upload(Viralworksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $users = User::whereIn('user_type_id', [1, 4])->get();
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
        $today = $datetested = date("Y-m-d");
        $nc = $nc_int = $lpc = $lpc_int = $hpc = $hpc_int = $nc_units = $hpc_units = $lpc_units =  NULL;

        $my = new MiscViral;
        $sample_array = $doubles = [];

        // Abbott
        if($worksheet->machine_type == 2)
        {
            $date_tested = $request->input('daterun');
            if(strtotime($date_tested) > strtotime($worksheet->created_at)) $datetested = $date_tested;
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

                    MiscViral::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

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

                    $data_array = ['datemodified' => $today, 'datetested' => $datetested, 'interpretation' => $result_array['interpretation'], 'result' => $result_array['result'], 'units' => $result_array['units']];
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
        // C8800
        else if($worksheet->machine_type == 3){
            $handle = fopen($file, "r");
            while (($value = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                if(!isset($value[1])) break;
                $sample_id = $value[1];
                $interpretation = $value[6];
                $result_array = MiscViral::exponential_result($interpretation);

                MiscViral::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

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

                $datetested = $today;

                try {
                    $dt = Carbon::parse($value[12]);
                    $datetested = $dt->toDateString();
                } catch (Exception $e) {
                    $datetested = $today;
                }

                $data_array = array_merge(['datemodified' => $today, 'datetested' => $datetested], $result_array);


                $sample_id = (int) $sample_id;
                $sample = Viralsample::find($sample_id);
                if(!$sample) continue;
                if($sample->worksheet_id != $worksheet->id) continue;
                $sample->fill($data_array);
                $sample->save();
            }
        }
        // Panther
        else if($worksheet->machine_type == 4){
            $handle = fopen($file, "r");
            while (($value = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $sample_id = (int) trim($value[0]);

                $interpretation = $value[4];

                MiscViral::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                if($value[19] == "Control"){
                    $name = strtolower($value[20]);
                    $result_array = MiscViral::sample_result($interpretation);

                    if(str_contains($name, 'low')){
                        $lpc = $result_array['result'];
                        $lpc_int = $result_array['interpretation'];
                        $lpc_units = $result_array['units'];
                    }
                    else if(str_contains($name, 'high')){
                        $hpc = $result_array['result'];
                        $hpc_int = $result_array['interpretation'];
                        $hpc_units = $result_array['units'];
                    }
                    else if(str_contains($name, 'negative')){
                        $nc = $result_array['result'];
                        $nc_int = $result_array['interpretation']; 
                        $nc_units = $result_array['units'];
                    }
                    continue;
                }

                $result_array = MiscViral::sample_result($interpretation);
                $data_array = array_merge(['datemodified' => $today, 'datetested' => $datetested], $result_array);

                $sample = Viralsample::find($sample_id);
                if(!$sample) continue;
                if($sample->worksheet_id != $worksheet->id) continue;
                $sample->fill($data_array);
                $sample->save();
            }
        }
        // Taqman
        else
        {
            $handle = fopen($file, "r");
            while (($value = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $datetested=date("Y-m-d", strtotime($value[3]));

                $sample_id = trim($value[4]);
                $interpretation = $value[8];
                $error = $value[10];

                MiscViral::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                $result_array = MiscViral::sample_result($interpretation, $error);

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

                // $data_array = ['datemodified' => $today, 'datetested' => $datetested, 'interpretation' => $result_array['interpretation'], 'result' => $result_array['result'], 'units' => $result_array['units']];
                // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                // Viralsample::where($search)->update($data_array);

                $data_array = array_merge(['datemodified' => $today, 'datetested' => $datetested], $result_array);

                // $sample_id = substr($sample_id, 0, -1);
                $sample_id = (int) $sample_id;


                $sample = Viralsample::find($sample_id);
                if(!$sample) continue;
                if($sample->worksheet_id != $worksheet->id) continue;
                $sample->fill($data_array);
                $sample->save();

            }
            fclose($handle);

        }

        if($doubles){
            session(['toast_error' => 1, 'toast_message' => "Worksheet {$worksheet->id} upload contains duplicate rows. Please fix and then upload again."]);
            $file = "Samples_Appearing_More_Than_Once_In_Worksheet_" . $worksheet->id;
        
            Excel::create($file, function($excel) use($doubles){
                $excel->sheet('Sheetname', function($sheet) use($doubles) {
                    $sheet->fromArray($doubles);
                });
            })->download('csv');
        }

        Viralsample::where(['worksheet_id' => $worksheet->id])->where('run', 0)->update(['run' => 1]);
        Viralsample::where(['worksheet_id' => $worksheet->id])->whereNull('repeatt')->update(['repeatt' => 0]);
        Viralsample::where(['worksheet_id' => $worksheet->id])->whereNull('result')->update(['repeatt' => 1]);

        $worksheet->neg_units = $nc_units;
        $worksheet->neg_control_interpretation = $nc_int;
        $worksheet->neg_control_result = $nc;

        $worksheet->hpc_units = $hpc_units;
        $worksheet->highpos_control_interpretation = $hpc_int;
        $worksheet->highpos_control_result = $hpc;

        $worksheet->lpc_units = $lpc_units;
        $worksheet->lowpos_control_interpretation = $lpc_int;
        $worksheet->lowpos_control_result = $lpc;

        $worksheet->daterun = $datetested;
        $worksheet->uploadedby = auth()->user()->id;

        $worksheet->save();

        MiscViral::requeue($worksheet->id);
        session(['toast_message' => "The worksheet has been updated with the results."]);

        return redirect('viralworksheet/approve/' . $worksheet->id);
    }


    public function approve_results(Viralworksheet $worksheet)
    {
        $worksheet->load(['reviewer', 'creator', 'runner', 'sorter', 'bulker']);
        
        // $samples = Viralsample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();
        
        $samples = Viralsample::join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
                    ->with(['approver', 'final_approver'])
                    ->select('viralsamples.*', 'viralbatches.facility_id')
                    ->where('worksheet_id', $worksheet->id)
                    ->orderBy('run', 'desc')
                    // ->orderBy('facility_id')
                    ->orderBy('batch_id', 'asc')
                    ->orderBy('viralsamples.id', 'asc')                    
                    ->get();

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
        $interpretations = $request->input('interpretations');
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

            if(is_numeric($results[$key])){
                $interpretation = $interpretations[$key];
                if(is_numeric($interpretation)) $data['result'] = (int) $interpretation * $dilutions[$key];
                else{
                    $r = MiscViral::sample_result($interpretation);
                    $data['result'] = (int) $r['result'] * $dilutions[$key];
                }
                // $data['result'] = (int) $results[$key] * $dilutions[$key];
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
            if($sample->repeatt == 0 && in_array($sample->result, ["", "Failed"])){
                $sample->result = "Collect New Sample";
            }
            $sample->pre_update();

            // Viralsample::where('id', $samples[$key])->update($data);

            if($data['repeatt'] == 1) MiscViral::save_repeat($samples[$key]);
        }

        // if(env('APP_LAB') == 9) MiscViral::dump_worksheet($worksheet->id);
        // $random_var = true;

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

    public function download_dump(Viralworksheet $worksheet)
    {
        return MiscViral::dump_worksheet($worksheet->id);
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
                if(is_array($worksheet_id)) return $query->whereIn('worksheet_id', $worksheet_id);
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->whereNotNull('worksheet_id')
            ->where('site_entry', '!=', 2)
            ->where('receivedstatus', '!=', 2)
            ->when(true, function($query) use ($result){
                if ($result == 0) {
                    return $query->whereRaw("(result is null or result='')");
                }
                else if ($result == 1) {
                    return $query->where('result', '< LDL copies/ml');
                }
                else if ($result == 2) {
                    return $query->whereNotIn('result', ['Failed', 'Invalid', '< LDL copies/ml', 'Target Not Detected', 'Collect New Sample', '']);
                }
                else if ($result == 3) {
                    return $query->whereRaw("(result='Failed' or result='invalid' or result='Collect New Sample')");
                }                
            })
            ->groupBy('worksheet_id')
            ->get();
        
        return $samples;
    }

    public function get_reruns($worksheet_id=NULL)
    {
        if(!$worksheet_id) return false;
        $samples = ViralsampleView::selectRaw("count(*) as totals, worksheet_id")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){                
                if (is_array($worksheet_id)) {
                    return $query->whereIn('worksheet_id', $worksheet_id);
                }
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->where('parentid', '>', 0)
            ->where('receivedstatus', '!=', 2)
            ->where('site_entry', '!=', 2)
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

    public function exceluploadworksheet(Request $request) {
        if ($request->method() == "GET") {
            $data = Lookup::get_viral_lookups();

            return view('forms.viralworksheetsexcel', $data)->with('pageTitle', 'Add Worksheet');
        } else {
            $file = $request->excelupload->path();
            $path = $request->excelupload->store('public/samples/otherlab/batches');
            $excelData = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $batches = collect($excelData->toArray())->first();
            $samples = Viralsample::whereIn('batch_id', $batches)->orderBy('id','asc')->get();
            $sample_count = $samples->count();
            
            $dataArray = [];
            $dataArray[] = ['Lab ID', 'Batch ID'];
            $worksheet = null;
            $counter = 0;
            foreach ($samples as $key => $sample) {
                $counter++;
                if (($counter == 1) && ($sample_count > 93)) {
                    $worksheet = new Viralworksheet();
                    $worksheet->lab_id = env('APP_LAB');
                    $worksheet->machine_type = 3;
                    $worksheet->sampletype = $request->input('sampletype');
                    $worksheet->createdby = $sample->batch->user_id;
                    $worksheet->sample_prep_lot_no = 44444;
                    $worksheet->bulklysis_lot_no = 44444;
                    $worksheet->control_lot_no = 44444;
                    $worksheet->calibrator_lot_no = 44444;
                    $worksheet->amplification_kit_lot_no = 44444;
                    $worksheet->sampleprepexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
                    $worksheet->bulklysisexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
                    $worksheet->controlexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
                    $worksheet->calibratorexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
                    $worksheet->amplificationexpirydate = date('Y-m-d', strtotime("+ 6 Months"));
                    $worksheet->save();
                }

                if ($sample_count > 93){
                    $sample->worksheet_id = $worksheet->id;
                    $sample->save();
                }
                else 
                    $dataArray[] = ['id' => $sample->id, 'batch' => $sample->batch_id];

                if ($counter == 93){
                    $worksheet = null;
                    $sample_count -= $counter;
                    $counter = 0;
                }
            }
            $title = "EDARP Overflow samples";
            Excel::create($title, function($excel) use ($dataArray, $title) {
                $excel->setTitle($title);
                $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('WJ Gilmore, LLC');
                $excel->setDescription($title);

                $excel->sheet('Sheet1', function($sheet) use ($dataArray) {
                    $sheet->fromArray($dataArray, null, 'A1', false, false);
                });

            })->download('csv');
            
            return back();
        }
    }
}
 