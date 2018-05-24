<?php

namespace App\Http\Controllers;

use App\Facility;
use App\Batch;
use App\Sample;
use App\Misc;
use App\Common;
use App\Lookup;

use DOMPDF;

use App\Mail\EidDispatch;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index($batch_complete=4, $date_start=NULL, $date_end=NULL)
    {
        $user = auth()->user();
        $facility_user = false;
        $date_column = "batches.datereceived";
        if($batch_complete == 1) $date_column = "batches.datedispatched";
        if($user->user_type_id == 5) $facility_user=true;

        $facility_id = session()->pull('facility_search');
        if($facility_id){ 
            $myurl = url("batch/facility/{$facility_id}/{$batch_complete}"); 
            $myurl2 = url("batch/facility/{$facility_id}"); 
        }
        else{ 
            $myurl = $myurl2 = url('batch/index/' . $batch_complete); 
        }

        $string = "(user_id='{$user->id}' OR batches.facility_id='{$user->facility_id}')";

        $batches = Batch::select(['batches.*', 'facilitys.name', 'users.surname', 'users.oname'])
            ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->leftJoin('users', 'users.id', '=', 'batches.user_id')
            ->when($date_start, function($query) use ($date_column, $date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate($date_column, '>=', $date_start)
                    ->whereDate($date_column, '<=', $date_end);
                }
                return $query->whereDate($date_column, $date_start);
            })
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->when($facility_id, function($query) use ($facility_id){
                return $query->where('batches.facility_id', $facility_id);
            })
            ->when(true, function($query) use ($batch_complete){
                if($batch_complete < 4) return $query->where('batch_complete', $batch_complete);
            })
            ->orderBy($date_column, 'desc')
            ->paginate();

        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = Misc::get_subtotals($batch_ids, false);
        $rejected = Misc::get_rejected($batch_ids, false);

        $batches->transform(function($batch, $key) use ($subtotals, $rejected){

            $neg = $subtotals->where('batch_id', $batch->id)->where('result', 1)->first()->totals ?? 0;
            $pos = $subtotals->where('batch_id', $batch->id)->where('result', 2)->first()->totals ?? 0;
            $failed = $subtotals->where('batch_id', $batch->id)->where('result', 3)->first()->totals ?? 0;
            $redraw = $subtotals->where('batch_id', $batch->id)->where('result', 5)->first()->totals ?? 0;
            $noresult = $subtotals->where('batch_id', $batch->id)->where('result', 0)->first()->totals ?? 0;

            $rej = $rejected->where('batch_id', $batch->id)->first()->totals ?? 0;
            $total = $neg + $pos + $failed + $redraw + $noresult + $rej;

            $result = $pos + $neg + $redraw + $failed;

            $batch->creator = $batch->surname . ' ' . $batch->oname;
            $batch->datecreated = $batch->my_date_format('created_at');
            $batch->datereceived = $batch->my_date_format('datereceived');
            $batch->datedispatched = $batch->my_date_format('datedispatched');
            $batch->total = $total;
            $batch->rejected = $rej;
            $batch->result = $result;
            $batch->noresult = $noresult;
            $batch->status = $batch->batch_complete;
            $batch->approval = false;
            return $batch;
        });

        return view('tables.batches', ['batches' => $batches, 'myurl' => $myurl, 'myurl2' => $myurl2, 'pre' => '', 'batch_complete' => $batch_complete])->with('pageTitle', 'Samples by Batch');
    }

    public function facility_batches($facility_id, $batch_complete=4, $date_start=NULL, $date_end=NULL)
    {
        session(['facility_search' => $facility_id]);
        return $this->index($batch_complete, $date_start, $date_end);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function show(Batch $batch)
    {
        $samples = $batch->sample;
        $samples->load(['patient.mother']);
        $batch->load(['view_facility', 'receiver', 'creator.facility']);
        $data = Lookup::get_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

        return view('tables.batch_details', $data)->with('pageTitle', 'Batches');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function edit(Batch $batch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Batch $batch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Batch $batch)
    {
        //
    }

    public function batch_dispatch()
    {
        return $this->get_rows();
    }


    public function confirm_dispatch(Request $request)
    {
        $batches = $request->input('batches');
        $final_dispatch = $request->input('final_dispatch');
        if (empty($batches)){
            session(['toast_message' => "No batch selected<br /><br />Please select a batch",
                'toast_error' => 1]);
            return redirect('/batch/dispatch');
            // return redirect('/viralbatch/complete_dispatch');
        }
        if(!$final_dispatch) return $this->get_rows($batches);

        foreach ($batches as $key => $value) {
            $batch = Batch::find($value);
            $facility = Facility::find($batch->facility_id);
            // if($facility->email != null || $facility->email != '')
            // {
                // Mail::to($facility->email)->send(new EidDispatch($batch, $facility));
                $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
                // $mail_array = array('joelkith@gmail.com');
                Mail::to($mail_array)->send(new EidDispatch($batch, $facility));
            // }            
        }

        Batch::whereIn('id', $batches)->update(['datedispatched' => date('Y-m-d'), 'batch_complete' => 1]);

        return redirect('/batch');
    }

    public function get_rows($batch_list=NULL)
    {
        $batches = Batch::select('batches.*', 'facilitys.email', 'facilitys.name')
            ->join('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->when($batch_list, function($query) use ($batch_list){
                return $query->whereIn('batches.id', $batch_list);
            })
            ->where('batch_complete', 2)
            ->get();

        $subtotals = Misc::get_subtotals();
        $rejected = Misc::get_rejected();
        $date_modified = Misc::get_maxdatemodified();
        $date_tested = Misc::get_maxdatetested();

        $batches->transform(function($batch, $key) use ($subtotals, $rejected, $date_modified, $date_tested){
            $neg = $subtotals->where('batch_id', $batch->id)->where('result', 1)->first()->totals ?? 0;
            $pos = $subtotals->where('batch_id', $batch->id)->where('result', 2)->first()->totals ?? 0;
            $failed = $subtotals->where('batch_id', $batch->id)->where('result', 3)->first()->totals ?? 0;
            $redraw = $subtotals->where('batch_id', $batch->id)->where('result', 5)->first()->totals ?? 0;
            $noresult = $subtotals->where('batch_id', $batch->id)->where('result', 0)->first()->totals ?? 0;

            $rej = $rejected->where('batch_id', $batch->id)->first()->totals ?? 0;
            $total = $neg + $pos + $failed + $redraw + $noresult + $rej;

            $dm = $date_modified->where('batch_id', $batch->id)->first()->mydate ?? '';
            $dt = $date_tested->where('batch_id', $batch->id)->first()->mydate ?? '';

            $batch->negatives = $neg;
            $batch->positives = $pos;
            $batch->failed = $failed;
            $batch->redraw = $redraw;
            $batch->noresult = $noresult;
            $batch->rejected = $rej;
            $batch->total = $total;
            $batch->date_modified = $dm;
            $batch->date_tested = $dt;
            return $batch;
        });

        return view('tables.dispatch', ['batches' => $batches, 'pending' => $batches->count(), 'batch_list' => $batch_list, 'pageTitle' => 'Batch Dispatch']);
    }

    public function approve_site_entry()
    {
        $batches = Batch::selectRaw("batches.*, COUNT(samples.id) AS sample_count, facilitys.name, creator.name as creator")
            ->leftJoin('samples', 'batches.id', '=', 'samples.batch_id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->leftJoin('facilitys as creator', 'creator.id', '=', 'batches.user_id')
            ->whereRaw('(receivedstatus is null or received_by is null)')
            // ->whereNull('received_by')
            // ->whereNull('receivedstatus')
            ->where('site_entry', 1)
            ->groupBy('batches.id')
            ->paginate();

        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = Misc::get_subtotals($batch_ids, false);
        $rejected = Misc::get_rejected($batch_ids, false);

        $batches->transform(function($batch, $key) use ($subtotals, $rejected){

            $noresult = $subtotals->where('batch_id', $batch->id)->where('result', 0)->first()->totals ?? 0;
            $rej = $rejected->where('batch_id', $batch->id)->first()->totals ?? 0;
            $total = $noresult + $rej;

            $batch->delays = '';
            $batch->datecreated = $batch->my_date_format('created_at');
            $batch->datereceived = $batch->my_date_format('datereceived');
            $batch->total = $total;
            $batch->rejected = $rej;
            $batch->result = '';
            $batch->noresult = $noresult;
            $batch->status = $batch->batch_complete;
            $batch->approval = true;
            return $batch;
        });

        return view('tables.batches', ['batches' => $batches, 'site_approval' => true, 'pre' => '']);
    }



    public function site_entry_approval(Batch $batch)
    {
        $sample = Sample::where('batch_id', $batch->id)->whereNull('receivedstatus')->get()->first();

        if($sample){
            session(['site_entry_approval' => true]);
            $sample->load(['patient.mother', 'batch']);
            $data = Lookup::samples_form();
            $data['sample'] = $sample;
            $data['site_entry_approval'] = true;
            return view('forms.samples', $data); 
        }
        else{
            $batch->received_by = auth()->user()->id;
            $batch->save();
            return redirect('batch/site_approval');
        }
    }


    public function site_entry_approval_group(Batch $batch)
    {
        $samples = Sample::with(['patient.mother'])->where('batch_id', $batch->id)->whereNull('receivedstatus')->get();

        if($samples->count() > 0){            
            $data = Lookup::samples_form();
            $batch->load(['creator.facility', 'view_facility']);
            $data['batch'] = $batch;
            $data['samples'] = $samples;
            $data['pageTitle'] = "Approve batch";
            return view('forms.approve_batch', $data);
        }
        else{
            return redirect('batch/site_approval');
        }
    }

    public function site_entry_approval_group_save(Request $request, Batch $batch)
    {
        $sample_ids = $request->input('samples');
        $rejectedreason_array = $request->input('rejectedreason');
        $spots_array = $request->input('spots');
        $submit_type = $request->input('submit_type');

        if(!$sample_ids) return back();

        foreach ($sample_ids as $key => $value) {
            $sample = Sample::find($value
            if($sample->batch_id != $batch->id) continue;

            $sample->spots = $spots_array[$key] ?? 5;
            $sample->labcomment = $request->input('labcomment');


            if($submit_type == "accepted"){
                $sample->receivedstatus == 1;
            }else if($submit_type == "rejected"){
                $sample->receivedstatus == 3;
                $sample->rejectedreason = $rejectedreason_array[$key] ?? null;
            }
            $sample->save(););
            dd($sample);
        }

        $batch->received_by = auth()->user()->id;
        $batch->datereceived = $request->input('datereceived');
        $batch->save();

        session(['toast_message' => 'The selected samples have been ' . $submit_type]);

        $sample = Sample::where('batch_id', $batch->id)->whereNull('receivedstatus')->get()->first();
        if($sample) return back();
        return redirect('batch/site_approval');        
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function individual(Batch $batch)
    {
        if(!$batch->dateindividualresultprinted){
            $batch->dateindividualresultprinted = date('Y-m-d');
            $batch->pre_update();
        }

        $samples = $batch->sample;
        $samples->load(['patient.mother']);
        $batch->load(['facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

        return view('exports.samples', $data)->with('pageTitle', 'Individual Batch');
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function summary(Batch $batch)
    {
        if(!$batch->datebatchprinted){
            $batch->datebatchprinted = date('Y-m-d');
            $batch->pre_update();
        }

        $batch->load(['sample.patient.mother', 'facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_lookups();
        $data['batches'] = [$batch];
        $pdf = DOMPDF::loadView('exports.samples_summary', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('summary.pdf');
    }

    public function summaries(Request $request)
    {
        $batch_ids = $request->input('batch_ids');
        $batches = Batch::whereIn('id', $batch_ids)->with(['sample.patient.mother', 'facility', 'lab', 'receiver', 'creator'])->get();

        foreach ($batches as $key => $batch) {
            if(!$batch->datebatchprinted){
                $batch->datebatchprinted = date('Y-m-d');
                $batch->pre_update();
            }
        }

        $data = Lookup::get_lookups();
        $data['batches'] = $batches;
        $pdf = DOMPDF::loadView('exports.samples_summary', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('summary.pdf');
    }



    public function search(Request $request)
    {
        $user = auth()->user();
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";

        $search = $request->input('search');
        $batches = Batch::whereRaw("id like '" . $search . "%'")
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->paginate(10);
        return $batches;
    }

    public function checknull($var)
    {
        return $var->first()->totals ?? 0;
    }


}
