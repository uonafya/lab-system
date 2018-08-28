<?php

namespace App\Http\Controllers;

use App\Worksheet;
use App\Sample;
use App\SampleView;
use App\User;
use App\Misc;
use App\Lookup;
use DB;
use Excel;
use Illuminate\Http\Request;

class WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state=0, $date_start=NULL, $date_end=NULL, $worksheet_id=NULL)
    {
        // $state = session()->pull('worksheet_state', null); 
        $worksheets = Worksheet::with(['creator'])->withCount(['sample'])
        ->when($worksheet_id, function ($query) use ($worksheet_id){
            return $query->where('worksheets.id', $worksheet_id);
        })
        ->when($state, function ($query) use ($state){
            return $query->where('status_id', $state);
        })
        ->when($date_start, function($query) use ($date_start, $date_end){
            if($date_end)
            {
                return $query->whereDate('worksheets.created_at', '>=', $date_start)
                ->whereDate('worksheets.created_at', '<=', $date_end);
            }
            return $query->whereDate('worksheets.created_at', $date_start);
        })
        ->orderBy('worksheets.created_at', 'desc')
        ->paginate();

        $worksheets->setPath(url()->current());

        $worksheet_ids = $worksheets->pluck(['id'])->toArray();
        $samples = $this->get_worksheets($worksheet_ids);
        $data = Lookup::worksheet_lookups();

        $worksheets->transform(function($worksheet, $key) use ($samples, $data){
            $status = $worksheet->status_id;
            $total = $worksheet->sample_count;

            if(($status == 2 || $status == 3) && $samples){
                $neg = $samples->where('worksheet_id', $worksheet->id)->where('result', 1)->first()->totals ?? 0;
                $pos = $samples->where('worksheet_id', $worksheet->id)->where('result', 2)->first()->totals ?? 0;
                $failed = $samples->where('worksheet_id', $worksheet->id)->where('result', 3)->first()->totals ?? 0;
                $redraw = $samples->where('worksheet_id', $worksheet->id)->where('result', 5)->first()->totals ?? 0;
                $noresult = $samples->where('worksheet_id', $worksheet->id)->where('result', 0)->first()->totals ?? 0;
            }
            else{
                $neg = $pos = $failed = $redraw = $noresult = 0;

                if($status == 1){
                    $noresult = $worksheet->sample_count;
                }
            }
            $worksheet->neg = $neg;
            $worksheet->pos = $pos;
            $worksheet->failed = $failed;
            $worksheet->redraw = $redraw;
            $worksheet->noresult = $noresult;
            $worksheet->mylinks = $this->get_links($worksheet->id, $status);
            $worksheet->machine = $data['machines']->where('id', $worksheet->machine_type)->first()->output ?? '';
            $worksheet->status = $data['worksheet_statuses']->where('id', $status)->first()->output ?? '';

            return $worksheet;
        });

        return view('tables.worksheets', ['worksheets' => $worksheets, 'myurl' => url('worksheet/index/' . $state . '/')])->with('pageTitle', 'Worksheets');

        // $table_rows = "";

        // foreach ($worksheets as $key => $worksheet) {
        //     $new_key = $key+1;
        //     $table_rows .= "<tr> <td>{$new_key}</td> <td>" . $worksheet->my_date_format('created_at') . "</td><td> " . $worksheet->creator->full_name . "</td><td>" . $this->mtype($worksheet->machine_type) . "</td><td>";
        //     $status = $worksheet->status_id;
        //     $table_rows .= $this->wstatus($status) . "</td><td>";

        //     if($status == 2 || $status == 3){
        //         $neg = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 1));
        //         $pos = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 2));
        //         $failed = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 3));
        //         $redraw = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 5));
        //         $noresult = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 0));

        //         $total = $neg + $pos + $failed + $redraw + $noresult;

        //     }
        //     else{
        //         $neg = $pos = $failed = $redraw = $noresult = $total = 0;

        //         if($status == 1){
        //             $noresult = $total = $this->checknull($samples->where('worksheet_id', $worksheet->id));
        //         }
        //     }

        //     $table_rows .= "{$pos}</td><td>{$neg}</td><td>{$failed}</td><td>{$redraw}</td><td>{$noresult}</td><td>{$total}</td><td>" . $worksheet->my_date_format('daterun') . "</td><td>" . $worksheet->my_date_format('dateuploaded') . "</td><td>" . $worksheet->my_date_format('datereviewed') . "</td><td>" . $this->get_links($worksheet->id, $status) . "</td></tr>";

        // }
        return view('tables.worksheets', ['worksheets' => $worksheets, 'myurl' => url('worksheet/index/' . $state . '/')])->with('pageTitle', 'Worksheets');
        // return view('tables.worksheetsdtsvr', ['myurl' => url('worksheet/index/' . $state . '/')])->with('pageTitle', 'Worksheets');
    }

    
    static function query($state=0, $date_start=NULL, $date_end=NULL)
    {
        $worksheets = Worksheet::with(['creator'])
        ->when($state, function ($query) use ($state){
            return $query->where('status_id', $state);
        })
        ->when($date_start, function($query) use ($date_start, $date_end){
            if($date_end)
            {
                return $query->whereDate('worksheets.created_at', '>=', $date_start)
                ->whereDate('worksheets.created_at', '<=', $date_end);
            }
            return $query->whereDate('worksheets.created_at', $date_start);
        });
        // ->orderBy('worksheets.created_at', 'desc')
        // ->get();
        return $worksheets;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($machine_type=2)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        $test = in_array(env('APP_LAB'), Lookup::$worksheet_received);
        // $test = false;
        $user = auth()->user();

        if($machine == NULL || $machine->eid_limit == NULL) return back();

        $limit = $machine->eid_limit;
        $year = date('Y') - 2;

        if($test){
            $repeats = Sample::selectRaw("samples.*, patients.patient, facilitys.name, batches.datereceived, batches.highpriority, batches.site_entry, users.surname, users.oname, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
                ->join('batches', 'samples.batch_id', '=', 'batches.id')
                ->leftJoin('users', 'users.id', '=', 'batches.user_id')
                ->join('patients', 'samples.patient_id', '=', 'patients.id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
                ->whereYear('datereceived', '>', $year)
                ->where('site_entry', '!=', 2)
                ->having('isnull', 0)
                ->whereNull('worksheet_id')
                ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw('((result IS NULL ) OR (result =0 ))')
                ->orderBy('samples.id', 'asc')
                ->limit($limit)
                ->get();
            $limit -= $repeats->count();
        }

        $samples = Sample::selectRaw("samples.*, patients.patient, facilitys.name, batches.datereceived, batches.highpriority, batches.site_entry, users.surname, users.oname, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->leftJoin('users', 'users.id', '=', 'batches.user_id')
            ->join('patients', 'samples.patient_id', '=', 'patients.id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->whereYear('datereceived', '>', $year)
            ->when($test, function($query) use ($user){
                return $query->where('received_by', $user->id)->having('isnull', 1);
            })
            ->where('site_entry', '!=', 2)
            ->whereNull('worksheet_id')
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('highpriority', 'desc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('site_entry', 'asc')
            ->orderBy('samples.id', 'asc')
            ->limit($limit)
            ->get();

        if($test) $samples = $repeats->merge($samples);
        $count = $samples->count();

        if($count == $machine->eid_limit){
            return view('forms.worksheets', ['create' => true, 'machine_type' => $machine_type, 'samples' => $samples, 'machine' => $machine])->with('pageTitle', 'Create Worksheet');
        }

        return view('forms.worksheets', ['create' => false, 'machine_type' => $machine_type, 'count' => $count])->with('pageTitle', 'Create Worksheet');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $worksheet = new Worksheet;
        $worksheet->fill($request->except('_token'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $worksheet->machine_type)->first();

        $test = in_array(env('APP_LAB'), Lookup::$worksheet_received);
        // $test=false;
        $user = auth()->user();

        $limit = $machine->eid_limit;
        $year = date('Y') - 2;

        if($test){
            $repeats = Sample::selectRaw("samples.*, patients.patient, facilitys.name, batches.datereceived, batches.highpriority, batches.site_entry, users.surname, users.oname, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
                ->join('batches', 'samples.batch_id', '=', 'batches.id')
                ->leftJoin('users', 'users.id', '=', 'batches.user_id')
                ->join('patients', 'samples.patient_id', '=', 'patients.id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
                ->whereYear('datereceived', '>', $year)
                ->where('site_entry', '!=', 2)
                ->having('isnull', 0)
                ->whereNull('worksheet_id')
                ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw('((result IS NULL ) OR (result =0 ))')
                ->orderBy('samples.id', 'asc')
                ->limit($limit)
                ->get();
            $limit -= $repeats->count();
        }

        $samples = Sample::selectRaw("samples.id, patient_id, samples.parentid, batches.datereceived, batches.highpriority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->whereYear('datereceived', '>', $year)
            ->when($test, function($query) use ($user){
                return $query->where('received_by', $user->id)->having('isnull', 1);
            })
            ->where('site_entry', '!=', 2)
            ->whereNull('worksheet_id')
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result = 0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('highpriority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('site_entry', 'asc')
            ->orderBy('samples.id', 'asc')
            ->limit($limit)
            ->get();

        if($test) $samples = $repeats->merge($samples);

        if($samples->count() != $machine->eid_limit){
            $worksheet->delete();
            session(['toast_message' => "The worksheet could not be created."]);
            return back();
        }

        $sample_ids = $samples->pluck('id');

        Sample::whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id]);

        return redirect()->route('worksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        $samples = Sample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples];

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Worksheets');
        }
    }

    public function find(Worksheet $worksheet)
    {
        session(['toast_message' => 'Found 1 worksheet.']);
        return $this->index(0, null, null, $worksheet->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Worksheet $worksheet)
    {
        $samples = $worksheet->sample;
        return view('forms.worksheets', ['create' => true, 'machine_type' => $worksheet->machine_type, 'samples' => $samples, 'worksheet' => $worksheet])->with('pageTitle', 'Edit Worksheet');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Worksheet $worksheet)
    {
        $worksheet->fill($request->except('_token'));
        $worksheet->save();
        return redirect('worksheet/print/' . $worksheet->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Worksheet $worksheet)
    {
        // DB::table("samples")->where('worksheet_id', $worksheet->id)->update(['worksheet_id' => NULL, 'result' => NULL]);
        // $worksheet->status_id = 4;
        // $worksheet->save();

        // return redirect("/worksheet");
    }

    public function print(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        $samples = Sample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples, 'print' => true];

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Worksheets');
        }
    }

    public function convert_worksheet($machine_type, Worksheet $worksheet)
    {
        if($machine_type == 1 || $worksheet->machine_type == 1 || $worksheet->status_id != 1){
            session(['toast_message' => 'The worksheet cannot be converted to the requested type.']);
            session(['toast_error' => 1]);
            return back();            
        }
        $worksheet->machine_type = $machine_type;
        $worksheet->save();
        return redirect('worksheet/' . $worksheet->id . '/edit');
    }

    public function cancel(Worksheet $worksheet)
    {
        if($worksheet->status_id != 1){
            session(['toast_message' => 'The worksheet is not eligible to be cancelled.']);
            session(['toast_error' => 1]);
            return back();
        }
        $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Sample::whereIn('id', $sample_array)->update(['worksheet_id' => null, 'result' => null]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        session(['toast_message' => 'The worksheet has been cancelled.']);
        return redirect("/worksheet");
    }

    public function cancel_upload(Worksheet $worksheet)
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

        $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Sample::whereIn('id', $sample_array)->update(['result' => null, 'interpretation' => null, 'datemodified' => null, 'datetested' => null]);
        $worksheet->status_id = 1;
        $worksheet->neg_control_interpretation = $worksheet->pos_control_interpretation = $worksheet->neg_control_result = $worksheet->pos_control_result = $worksheet->daterun = $worksheet->dateuploaded = $worksheet->uploadedby = $worksheet->datereviewed = $worksheet->reviewedby = $worksheet->datereviewed2 = $worksheet->reviewedby2 = null;
        $worksheet->save();

        session(['toast_message' => 'The upload has been reversed.']);
        return redirect("/worksheet/upload/" . $worksheet->id);
    }

    public function reverse_upload(Worksheet $worksheet)
    {
        $worksheet->status_id = 1;
        $worksheet->neg_control_interpretation = $worksheet->pos_control_interpretation = $worksheet->neg_control_result = $worksheet->pos_control_result = $worksheet->daterun = $worksheet->dateuploaded = $worksheet->uploadedby = $worksheet->datereviewed = $worksheet->reviewedby = $worksheet->datereviewed2 = $worksheet->reviewedby2 = null;
        $worksheet->save();

        $batches_data = ['batch_complete' => 0, 'sent_email' => 0, 'printedby' => null,  'dateemailsent' => null, 'datebatchprinted' => null, 'dateindividualresultprinted' => null, 'datedispatched' => null, ];
        $samples_data = ['datetested' => null, 'result' => null, 'interpretation' => null, 'repeatt' => 0, 'approvedby' => null, 'approvedby2' => null, 'datemodified' => null, 'dateapproved' => null, 'dateapproved2' => null, 'tat1' => null, 'tat2' => null, 'tat3' => null, 'tat4' => null];

        // $samples = Sample::where(['worksheet_id' => $worksheet->id, 'repeatt' => 1])->get();
        $samples = Sample::where(['worksheet_id' => $worksheet->id])->get();

        foreach ($samples as $key => $sample) {
            if($sample->parentid == 0) $del_samples = Sample::where('parentid', $sample->id)->get();
            else{
                $run = $sample->run+1;
                $del_samples = Sample::where(['parentid' => $sample->parentid, 'run' => $run])->get();
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
            $batch = \App\Batch::find($id);
            $batch->fill($batches_data);
            $batches->pre_update();
        }

    }

    public function upload(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $users = User::where('user_type_id', '<', 5)->where('user_type_id', '!=', 0)->get();
        return view('forms.upload_results', ['worksheet' => $worksheet, 'users' => $users])->with('pageTitle', 'Worksheet Upload');
    }




    /**
     * Update the specified resource in storage with results file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function save_results(Request $request, Worksheet $worksheet)
    {
        $worksheet->fill($request->except(['_token', 'upload']));
        $file = $request->upload->path();
        $path = $request->upload->store('public/results/eid'); 
        $today = $dateoftest = date("Y-m-d");
        $positive_control;
        $negative_control;

        if($worksheet->machine_type == 2)
        {
            $dateoftest = $today;
            // config(['excel.import.heading' => false]);
            $data = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $check = array();

            // dd($data);

            $bool = false;
            $positive_control = $negative_control = "Passed";

            foreach ($data as $key => $value) {
                if($value[5] == "RESULT"){
                    $bool = true;
                    continue;
                }

                if($bool){
                    $sample_id = $value[1];
                    $interpretation = $value[5];
                    $error = $value[10];

                    switch ($interpretation) {
                        case 'Not Detected':
                            $result = 1;
                            break;
                        case 'HIV-1 Detected':
                            $result = 2;
                            break;
                        case 'Detected':
                            $result = 2;
                            break;
                        case 'Collect New Sample':
                            $result = 5;
                            break;
                        default:
                            $result = 3;
                            $interpretation = $error;
                            break;
                    }

                    if($sample_id == "HIV_NEG") $negative_control = $value[5];
                    if($sample_id == "HIV_HIPOS") $positive_control = $value[5];

                    $data_array = ['datemodified' => $today, 'datetested' => $today, 'interpretation' => $interpretation, 'result' => $result];
                    // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    // Sample::where($search)->update($data_array);
                    $sample = Sample::find($sample_id);
                    if(!$sample) continue;
                    if($sample->worksheet_id != $worksheet->id) continue;
                    $sample->fill($data_array);
                    $sample->save();
                }

                if($bool && $value[5] == "RESULT") break;
            }

            if($positive_control == "Passed"){
                $pos_result = 6;
            }else{
                $pos_result = 7;
            }

            if($negative_control == "Passed"){
                $neg_result = 6;
            }else{
                $neg_result = 7;
            }
        }
        else
        {
            $handle = fopen($file, "r");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $interpretation = $data[8];

                $flag = $data[10];
                if($flag != NULL) $interpretation = $flag;

                $dateoftest=date("Y-m-d", strtotime($data[3]));

                if($interpretation == "Target Not Detected" || $interpretation == "Not Detected DBS")
                {
                    $result = 1;
                }else if($interpretation == 1 || $interpretation == "1" || $interpretation == ">1" || $interpretation == ">1 " || $interpretation == "> 1" || $interpretation == "> 1 " || $interpretation == "1.00E+00" || $interpretation == ">1.00E+00" || $interpretation == ">1.00E+00 " || $interpretation == "> 1.00E+00")
                {
                    $result = 2;
                }else{
                    $result = 3;
                }

                if($data[5] == "NC") $negative_control = $result;
                if($data[5] == "LPC" || $data[5] == "PC") $positive_control = $result;

                $data_array = ['datemodified' => $today, 'datetested' => $dateoftest, 'interpretation' => $interpretation, 'result' => $result];

                // $search = ['id' => $data[4], 'worksheet_id' => $worksheet->id];
                // Sample::where($search)->update($data_array);
                $sample = Sample::find($data[4]);
                if(!$sample) continue;
                if($sample->worksheet_id != $worksheet->id) continue;
                $sample->fill($data_array);
                $sample->save();

            }
            fclose($handle);

            switch ($negative_control) {
                case 'Target Not Detected':
                    $neg_result = 1;
                    break;
                case 'Valid':
                    $neg_result = 6;
                    break;
                case 'Invalid':
                    $neg_result = 7;
                    break;
                case '5':
                    $neg_result = 5;
                    break;                
                default:
                    $neg_result = 3;
                    break;
            }

            if($positive_control == 1 || $positive_control == "1" || $positive_control == ">1" || $positive_control == "> 1 " || $positive_control == "> 1" || $positive_control == "1.00E+00" || $positive_control == ">1.00E+00" || $positive_control == "> 1.00E+00" || $positive_control == "> 1.00E+00 ")
            {
                $pos_result = 2;
            }
            else if($positive_control == "5")
            {
                $pos_result = 5;
            }
            else if($positive_control == "Valid")
            {
                $pos_result = 6;
            }
            else if($positive_control == "Invalid")
            {
                $pos_result = 7;
            }
            else
            {
                $pos_result = 3;
            }

        }

        // $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Sample::where(['worksheet_id' => $worksheet->id, 'run' => 0])->update(['run' => 1]);

        $worksheet->neg_control_interpretation = $negative_control;
        $worksheet->neg_control_result = $neg_result;

        $worksheet->pos_control_interpretation = $positive_control;
        $worksheet->pos_control_result = $pos_result;
        $worksheet->daterun = $dateoftest;
        $worksheet->uploadedby = auth()->user()->id;
        $worksheet->save();

        Misc::requeue($worksheet->id);
        session(['toast_message' => "The worksheet has been updated with the results."]);

        return redirect('worksheet/approve/' . $worksheet->id);
    }

    public function approve_results(Worksheet $worksheet)
    {        
        $worksheet->load(['reviewer', 'creator', 'runner', 'sorter', 'bulker']);

        $samples = Sample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();

        $s = $this->get_worksheets($worksheet->id);

        $neg = $this->checknull($s->where('result', 1));
        $pos = $this->checknull($s->where('result', 2));
        $failed = $this->checknull($s->where('result', 3));
        $redraw = $this->checknull($s->where('result', 5));
        $noresult = $this->checknull($s->where('result', 0));

        $total = $neg + $pos + $failed + $redraw + $noresult;

        $subtotals = ['neg' => $neg, 'pos' => $pos, 'failed' => $failed, 'redraw' => $redraw, 'noresult' => $noresult, 'total' => $total];

        $data = Lookup::worksheet_approve_lookups();
        $data['samples'] = $samples;
        $data['subtotals'] = $subtotals;
        $data['worksheet'] = $worksheet;

        return view('tables.confirm_results', $data)->with('pageTitle', 'Approve Results');
    }

    public function approve(Request $request, Worksheet $worksheet)
    {
        $double_approval = Lookup::$double_approval;
        $samples = $request->input('samples');
        $batches = $request->input('batches');
        $results = $request->input('results');
        $actions = $request->input('actions');

        $today = date('Y-m-d');
        $approver = auth()->user()->id;

        $batch = array();

        foreach ($samples as $key => $value) {

            if(in_array(env('APP_LAB'), $double_approval) && $worksheet->reviewedby && !$worksheet->reviewedby2 && $worksheet->reviewedby != $approver){
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

            $data['result'] = $results[$key];
            $data['repeatt'] = $actions[$key];

            if($data['result'] == 5){
                $data['labcomment'] = "Failed Run";
                $data['repeatt'] = 0;
            } 

            // Sample::where('id', $samples[$key])->update($data);
            
            $sample = Sample::find($samples[$key]);
            $sample->fill($data);
            $sample->pre_update();

            // if($actions[$key] == 1){
            if($data['repeatt'] == 1){
                Misc::save_repeat($samples[$key]);
            }
        }

        if(in_array(env('APP_LAB'), $double_approval)){
            if($worksheet->reviewedby && $worksheet->reviewedby != $approver){
                $batch = collect($batches);
                $b = $batch->unique();
                $unique = $b->values()->all();

                foreach ($unique as $value) {
                    Misc::check_batch($value);
                }

                $worksheet->status_id = 3;
                $worksheet->datereviewed2 = $today;
                $worksheet->reviewedby2 = $approver;
                $worksheet->save();
                session(['toast_message' => "The worksheet has been approved."]);

                return redirect('/batch/dispatch');                 
            }
            else{
                $worksheet->datereviewed = $today;
                $worksheet->reviewedby = $approver;
                $worksheet->save();
                session(['toast_message' => "The worksheet has been approved. It is awaiting the second approval before the results can be prepared for dispatch."]);

                return redirect('/worksheet');
            }
        }

        else{
            $batch = collect($batches);
            $b = $batch->unique();
            $unique = $b->values()->all();

            foreach ($unique as $value) {
                Misc::check_batch($value);
            }

            $worksheet->status_id = 3;
            $worksheet->datereviewed = $today;
            $worksheet->reviewedby = $approver;
            $worksheet->save();
            session(['toast_message' => "The worksheet has been approved."]);

            return redirect('/batch/dispatch');            
        }
    }

    public function mtype($machine)
    {
        if($machine == 1){
            return "<strong> TaqMan </strong>";
        }
        else{
            return " <strong><font color='#0000FF'> Abbott </font></strong> ";
        }
    }

    public function wstatus($status)
    {
        switch ($status) {
            case 1:
                return "<strong><font color='#FFD324'>In-Process</font></strong>";
                break;
            case 2:
                return "<strong><font color='#0000FF'>Tested</font></strong>";
                break;
            case 3:
                return "<strong><font color='#339900'>Approved</font></strong>";
                break;
            case 4:
                return "<strong><font color='#FF0000'>Cancelled</font></strong>";
                break;            
            default:
                break;
        }
    }

    public function get_links($worksheet_id, $status)
    {
        if($status == 1)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to view Samples in this Worksheet' target='_blank'>Details</a> | "
                . "<a href='" . url('worksheet/print/' . $worksheet_id) . "' title='Click to Print this Worksheet' target='_blank'>Print</a> | "
                . "<a href='" . url('worksheet/cancel/' . $worksheet_id) . "' title='Click to Cancel this Worksheet' onClick=\"return confirm('Are you sure you want to Cancel Worksheet {$worksheet_id}?'); \" >Cancel</a> | "
                . "<a href='" . url('worksheet/upload/' . $worksheet_id) . "' title='Click to Upload Results File for this Worksheet'>Update Results</a>";
        }
        else if($status == 2)
        {
            $d = "<a href='" . url('worksheet/approve/' . $worksheet_id) . "' title='Click to Approve Samples Results in worksheet for Rerun or Dispatch' target='_blank'> Approve Worksheet Results</a>";

        }
        else if($status == 3)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to view Samples in this Worksheet' target='_blank'>Details</a> | "
                . "<a href='" . url('worksheet/approve/' . $worksheet_id) . "' title='Click to View Approved Results & Action for Samples in this Worksheet' target='_blank'>View Results</a> | "
                . "<a href='" . url('worksheet/print/' . $worksheet_id) . "' title='Click to Print this Worksheet' target='_blank'>Print</a> ";

        }
        else if($status == 4)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to View Cancelled Worksheet Details' target='_blank'>Details</a> ";

        }
        return $d;
    }

    public function get_worksheets($worksheet_id=NULL)
    {
        if(!$worksheet_id) return false;
        $samples = SampleView::selectRaw("count(*) as totals, worksheet_id, result")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){                
                if (is_array($worksheet_id)) {
                    return $query->whereIn('worksheet_id', $worksheet_id);
                }
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->whereNotNull('worksheet_id')
            ->where('receivedstatus', '!=', 2)
            ->where('site_entry', '!=', 2)
            ->groupBy('worksheet_id', 'result')
            ->get();

        return $samples;
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $worksheets = Worksheet::whereRaw("id like '" . $search . "%'")->paginate(10);
        $worksheets->setPath(url()->current());
        return $worksheets;
    }

    public function checknull($var)
    {
        return $var->first()->totals ?? 0;
    }

}
