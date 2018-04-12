<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Sample;
use App\Misc;
use App\Common;
use App\Lookup;

use DB;
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

        $myurl = url('batch/index/' . $batch_complete);
        $user = auth()->user();
        $facility_user = false;
        if($user->user_type_id == 5) $facility_user=true;

        $my = new Misc;

        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";

        $batches = Batch::select(['batches.*', 'facilitys.name', 'users.surname', 'users.oname'])
            ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->leftJoin('users', 'users.id', '=', 'batches.user_id')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('batches.datereceived', '>=', $date_start)
                    ->whereDate('batches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('batches.datereceived', $date_start);
            })
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->when(true, function($query) use ($batch_complete){
                if($batch_complete < 4) return $query->where('batch_complete', $batch_complete);
            })
            ->orderBy('datereceived', 'desc')
            ->paginate();

        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = $my->get_subtotals($batch_ids, false);
        $rejected = $my->get_rejected($batch_ids, false);

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
            $batch->total = $total;
            $batch->rejected = $rej;
            $batch->result = $result;
            $batch->noresult = $noresult;
            $batch->status = $batch->batch_complete;
            $batch->approval = false;
            return $batch;
        });

        return view('tables.batches', ['batches' => $batches, 'myurl' => $myurl, 'pre' => '', 'batch_complete' => $batch_complete])->with('pageTitle', 'Samples by Batch');
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
        $batch->load(['facility', 'receiver', 'creator']);
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


        foreach ($batches as $key => $value) {
            $batch = Batch::find($value);
            $facility = DB::table('facilitys')->where('id', $batch->facility_id)->get()->first();
            // if($facility->email != null || $facility->email != '')
            // {
                // Mail::to($facility->email)->send(new EidDispatch($batch, $facility));
                // $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
                $mail_array = array('joelkith@gmail.com');
                Mail::to($mail_array)->send(new EidDispatch($batch, $facility));
            // }            
        }

        DB::table('batches')->whereIn('id', $batches)->update(['datedispatched' => date('Y-m-d'), 'batch_complete' => 1]);

        return redirect('/batch');
    }

    public function get_rows($batch_list=NULL)
    {
        $my = new Misc;

        $batches = Batch::select('batches.*', 'facilitys.email', 'facilitys.name')
            ->join('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->when($batch_list, function($query) use ($batch_list){
                return $query->whereIn('batches.id', $batch_list);
            })
            ->where('batch_complete', 2)
            ->get();

        $subtotals = $my->get_subtotals();
        $rejected = $my->get_rejected();
        $date_modified = $my->get_maxdatemodified();
        $date_tested = $my->get_maxdatetested();

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

        return view('tables.dispatch', ['batches' => $batches, 'pending' => $batches->count()]);
    }

    public function approve_site_entry()
    {
        $batches = Batch::select(['batches.*', 'facilitys.name', 'creator.name as creator'])
            ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->leftJoin('facilitys as creator', 'creator.id', '=', 'batches.user_id')
            ->whereNull('received_by')
            ->where('site_entry', 1)
            ->paginate();

        $my = new Misc;

        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = $my->get_subtotals($batch_ids, false);
        $rejected = $my->get_rejected($batch_ids, false);

        $batches->transform(function($batch, $key) use ($subtotals, $rejected){

            $noresult = $subtotals->where('batch_id', $batch->id)->where('result', 0)->first()->totals ?? 0;
            $rej = $rejected->where('batch_id', $batch->id)->first()->totals ?? 0;
            $total = $noresult + $rej;

            $batch->delays = '';
            $batch->creator = $batch->creator;
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
            return view('forms.samples', $data);
        }
        else{
            $batch->received_by = auth()->user()->id;
            $batch->save();
            return redirect('batch/site_approval');
        }
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function individual(Batch $batch)
    {
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
