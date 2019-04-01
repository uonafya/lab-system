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
        $reruns = $this->get_reruns($worksheet_ids);
        $data = Lookup::worksheet_lookups();

        $worksheets->transform(function($worksheet, $key) use ($samples, $reruns, $data){
            $status = $worksheet->status_id;
            $total = $worksheet->sample_count;

            if(($status == 2 || $status == 3) && $samples){
                $neg = $samples->where('worksheet_id', $worksheet->id)->where('result', 1)->first()->totals ?? 0;
                $pos = $samples->where('worksheet_id', $worksheet->id)->where('result', 2)->first()->totals ?? 0;
                $failed = $samples->where('worksheet_id', $worksheet->id)->where('result', 3)->first()->totals ?? 0;
                $redraw = $samples->where('worksheet_id', $worksheet->id)->where('result', 5)->first()->totals ?? 0;
                $noresult = $samples->where('worksheet_id', $worksheet->id)->where('result', 0)->first()->totals ?? 0;

                $rerun = $reruns->where('worksheet_id', $worksheet->id)->first()->totals ?? 0;
            }
            else{
                $neg = $pos = $failed = $redraw = $noresult = $rerun = 0;

                if($status == 1){
                    $noresult = $worksheet->sample_count;
                    $rerun = $reruns->where('worksheet_id', $worksheet->id)->first()->totals ?? 0;
                }
            }
            $worksheet->rerun = $rerun;
            $worksheet->neg = $neg;
            $worksheet->pos = $pos;
            $worksheet->failed = $failed;
            $worksheet->redraw = $redraw;
            $worksheet->noresult = $noresult;
            $worksheet->mylinks = $this->get_links($worksheet->id, $status, $worksheet->datereviewed);
            $worksheet->machine = $data['machines']->where('id', $worksheet->machine_type)->first()->output ?? '';
            $worksheet->status = $data['worksheet_statuses']->where('id', $status)->first()->output ?? '';

            return $worksheet;
        });

        $data = Lookup::worksheet_lookups();
        $data['status_count'] = Worksheet::selectRaw("count(*) AS total, status_id, machine_type")
            ->groupBy('status_id', 'machine_type')
            ->orderBy('status_id', 'asc')
            ->orderBy('machine_type', 'asc')
            ->get();
        $data['worksheets'] = $worksheets;
        $data['myurl'] = url('worksheet/index/' . $state . '/');

        return view('tables.worksheets', $data)->with('pageTitle', 'Worksheets');
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
    public function create($machine_type=2, $limit=null)
    {
        $data = Misc::get_worksheet_samples($machine_type, $limit);
        if(!$data){
            session(['toast_message' => 'An error has occurred.', 'toast_error' => 1]);
            return back();
        }
        return view('forms.worksheets', $data)->with('pageTitle', 'Create Worksheet');
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
        $worksheet->fill($request->except('_token', 'limit'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $data = Misc::get_worksheet_samples($worksheet->machine_type, $request->input('limit'));

        if(!$data || !$data['create']){
            $worksheet->delete();
            session(['toast_message' => "The worksheet could not be created.", 'toast_error' => 1]);
            return back();            
        }
        $samples = $data['samples'];
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
    public function show(Worksheet $worksheet, $print=false)
    {
        $worksheet->load(['creator']);
        $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        // $samples = Sample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();


        $samples = Sample::join('batches', 'samples.batch_id', '=', 'batches.id')
                    ->with(['patient', 'batch.facility'])
                    ->select('samples.*', 'batches.facility_id')
                    ->whereIn('samples.id', $sample_array)
                    ->orderBy('run', 'desc')
                    ->when(true, function($query){
                        if(in_array(env('APP_LAB'), [3])) $query->orderBy('datereceived', 'asc');
                        if(!in_array(env('APP_LAB'), [8, 9, 1])) return $query->orderBy('batch_id', 'asc');
                    })
                    ->orderBy('samples.id', 'asc')
                    ->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples, 'i' => 0];

        if($print) $data['print'] = true;

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
        if($worksheet->status_id != 4){
            session(['toast_error' => 1, 'toast_message' => 'The worksheet cannot be deleted.']);
            return back();
        }
        // DB::table("samples")->where('worksheet_id', $worksheet->id)->update(['worksheet_id' => NULL, 'result' => NULL]);
        $worksheet->delete();
        return back();
    }

    public function print(Worksheet $worksheet)
    {
        return $this->show($worksheet, true);
    }

    public function convert_worksheet($machine_type, Worksheet $worksheet)
    {
        if($machine_type == 1 || $worksheet->machine_type == 1 || $worksheet->status_id != 1){
            session(['toast_error' => 1, 'toast_message' => 'The worksheet cannot be converted to the requested type.']);
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

        if($worksheet->uploadedby != auth()->user()->id && auth()->user()->user_type_id != 0){
            session(['toast_message' => 'Only the user who uploaded the results can reverse the upload.']);
            session(['toast_error' => 1]);
            return back();
        }

        $samples = Sample::where(['repeatt' => 1, 'worksheet_id' => $worksheet->id])->get();

        foreach ($samples as $sample) {
            $sample->remove_rerun();
        }

        $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Sample::whereIn('id', $sample_array)->update(['result' => null, 'interpretation' => null, 'datemodified' => null, 'datetested' => null, 'repeatt' => 0, 'dateapproved' => null, 'approvedby' => null]);
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
            $batch->pre_update();
        }
        return back();

    }

    public function upload(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $users = User::whereIn('user_type_id', [1, 4])->where('email', '!=', 'rufus.nyaga@ken.aphl.org')->get();
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
        $today = $datetested = date("Y-m-d");
        $positive_control = $negative_control = null;

        $sample_array = $doubles = [];

        if($worksheet->machine_type == 2)
        {
            $date_tested = $request->input('daterun');
            $datetested = Misc::worksheet_date($date_tested, $worksheet->created_at);
            // config(['excel.import.heading' => false]);
            $data = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $check = array();

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


                    Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                    $data_array = Misc::sample_result($interpretation, $error);

                    if($sample_id == "HIV_NEG") $negative_control = $data_array;
                    if($sample_id == "HIV_HIPOS") $positive_control = $data_array;

                    $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $today]);
                    // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    // Sample::where($search)->update($data_array);

                    $sample_id = (int) $sample_id;
                    $sample = Sample::find($sample_id);
                    if(!$sample) continue;
                    if($sample->worksheet_id != $worksheet->id) continue;
                    $sample->fill($data_array);
                    $sample->save();
                }

                if($bool && $value[5] == "RESULT") break;
            }
        }
        else
        {
            $handle = fopen($file, "r");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $interpretation = rtrim($data[8]);
                $control = rtrim($data[5]);

                $error = $data[10];

                $date_tested=date("Y-m-d", strtotime($data[3]));

                $datetested = Misc::worksheet_date($date_tested, $worksheet->created_at);

                $data_array = Misc::sample_result($interpretation, $error);

                if($control == "NC") $negative_control = $data_array;

                if($control == "LPC" || $control == "PC") $positive_control = $data_array;

                $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $datetested]);

                $sample_id = (int) trim($data[4]);  

                Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                // $sample_id = substr($sample_id, 0, -1);
                $sample = Sample::find($sample_id);
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

        // $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Sample::where(['worksheet_id' => $worksheet->id, 'run' => 0])->update(['run' => 1]);
        Sample::where(['worksheet_id' => $worksheet->id])->whereNull('repeatt')->update(['repeatt' => 0]);
        Sample::where(['worksheet_id' => $worksheet->id])->whereNull('result')->update(['repeatt' => 1]);

        $worksheet->neg_control_interpretation = $negative_control['interpretation'];
        $worksheet->neg_control_result = $negative_control['result'];

        $worksheet->pos_control_interpretation = $positive_control['interpretation'];
        $worksheet->pos_control_result = $positive_control['result'];
        $worksheet->daterun = $datetested;
        $worksheet->uploadedby = auth()->user()->id;
        $worksheet->save();

        Misc::requeue($worksheet->id);
        session(['toast_message' => "The worksheet has been updated with the results."]);

        return redirect('worksheet/approve/' . $worksheet->id);
    }

    public function approve_results(Worksheet $worksheet)
    {        
        $worksheet->load(['reviewer', 'creator', 'runner', 'sorter', 'bulker']);

        // $samples = Sample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();
        
        $samples = Sample::join('batches', 'samples.batch_id', '=', 'batches.id')
                    ->with(['approver', 'final_approver'])
                    ->select('samples.*', 'batches.facility_id')
                    ->where('worksheet_id', $worksheet->id)
                    ->orderBy('run', 'desc')
                    // ->orderBy('facility_id')
                    ->orderBy('batch_id', 'asc')
                    ->orderBy('samples.id', 'asc')  
                    ->get();

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

        if(in_array(env('APP_LAB'), $double_approval) && $worksheet->reviewedby == $approver){
            session(['toast_message' => "You are not permitted to do the second approval.", 'toast_error' => 1]);
            return redirect('/worksheet');            
        }

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

    public function get_links($worksheet_id, $status, $datereviewed)
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
            $d = "<a href='" . url('worksheet/approve/' . $worksheet_id) . "' title='Click to Approve Samples Results in worksheet for Rerun or Dispatch' target='_blank'> Approve Worksheet Results ";

            if($datereviewed) $d .= "(Second Review)";

            $d .= "</a>";

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
            ->where('receivedstatus', '!=', 2)
            ->where('site_entry', '!=', 2)
            ->groupBy('worksheet_id', 'result')
            ->get();

        return $samples;
    }

    public function get_reruns($worksheet_id=NULL)
    {
        if(!$worksheet_id) return false;
        $samples = SampleView::selectRaw("count(*) as totals, worksheet_id")
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
        $worksheets = Worksheet::whereRaw("id like '" . $search . "%'")->paginate(10);
        $worksheets->setPath(url()->current());
        return $worksheets;
    }

    public function checknull($var)
    {
        return $var->first()->totals ?? 0;
    }

}
