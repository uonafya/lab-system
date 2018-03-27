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
    public function index($batch_complete=4, $page=NULL, $date_start=NULL, $date_end=NULL)
    {
        $myurl = url('batch/index/' . $batch_complete . '/' . $page . '/');
        $user = auth()->user();
        $facility_user = false;
        if($user->user_type_id == 5) $facility_user=true;

        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";

        $my = new Misc;
        $b = Batch::selectRaw('count(id) as mycount')
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
            ->get()
            ->first();

        $page_limit = env('PAGE_LIMIT', 10);

        if($page == NULL || $page == 'null'){
            $page=1;
        }

        $last_page = ceil($b->mycount / $page_limit);
        $last_page = (int) $last_page;

        $offset = ($page-1) * $page_limit;

        $batches = Batch::select('batches.*', 'facilitys.name')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
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
            ->orderBy('created_at', 'desc')
            ->limit($page_limit)
            ->offset($offset)
            ->get();

        if($batches->isEmpty()){
            return view('tables.batches', ['rows' => null, 'links' => null, 'myurl' => $myurl, 'pre' => '']);
        }

        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = $my->get_subtotals($batch_ids, false);
        $rejected = $my->get_rejected($batch_ids, false);
        $currentdate=date('d-m-Y');

        $table_rows = "";

        foreach ($batches as $key => $batch) {

            $neg = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 1));
            $pos = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 2));
            $failed = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 3));
            $redraw = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 5));
            $noresult = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 0));

            $rej = $this->checknull($rejected->where('batch_id', $batch->id));
            $total = $neg + $pos + $failed + $redraw + $noresult + $rej;

            $result = $pos + $neg + $redraw + $failed;

            $datereceived=date("d-M-Y",strtotime($batch->datereceived));

            if($batch->batch_complete == 0){
                $max = $currentdate;
            }
            else{
                $max=date("d-M-Y",strtotime($batch->datedispatched));
            }

            $delays = $my->working_days($datereceived, $max);

            $table_rows .= "<tr> 
            <td>{$batch->id}</td>
            <td>{$batch->name}</td>
            <td>{$batch->datereceived}</td>
            <td>" . $batch->created_at->toDateString() . "</td>
            <td>{$delays}</td>
            <td></td>
            <td>{$total}</td>
            <td>{$rej}</td>
            <td>{$result}</td>
            <td>{$noresult}</td>" . $my->batch_status($batch->id, $batch->batch_complete) . "
            </tr>";
        }
        $base = '/batch/index/' . $batch_complete;

        $links = $my->page_links($base, $page, $last_page, $date_start, $date_end);

        return view('tables.batches', ['rows' => $table_rows, 'links' => $links, 'myurl' => $myurl, 'pre' => ''])->with('pageTitle', 'Batches');
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
        $get_subtotals = $my->get_subtotals();
        $rejected = $my->get_rejected();
        $date_modified = $my->get_maxdatemodified();
        $date_tested = $my->get_maxdatetested();
        $currentdate=date('d-m-Y');

        $table_rows = "";

        foreach ($batches as $key => $batch) {

            $neg = $this->checknull($get_subtotals->where('batch_id', $batch->id)->where('result', 1));
            $pos = $this->checknull($get_subtotals->where('batch_id', $batch->id)->where('result', 2));
            $failed = $this->checknull($get_subtotals->where('batch_id', $batch->id)->where('result', 3));
            $redraw = $this->checknull($get_subtotals->where('batch_id', $batch->id)->where('result', 5));
            $noresult = $this->checknull($get_subtotals->where('batch_id', $batch->id)->where('result', 0));

            $rej = $this->checknull($rejected->where('batch_id', $batch->id));
            $total = $neg + $pos + $failed + $redraw + $noresult + $rej;

            $dm = $date_modified->where('batch_id', $batch->id)->first()->mydate;
            $dt = $date_tested->where('batch_id', $batch->id)->first()->mydate;

            $maxdate=date("d-M-Y",strtotime($dm));

            $delays = $my->working_days($maxdate, $currentdate);

            $table_rows .= "<tr> 
            <td><div align='center'><input name='batches[]' type='checkbox' id='batches[]' value='{$batch->id}' /> </div></td>
            <td>{$batch->id}</td>
            <td>{$batch->name}</td>
            <td>{$batch->email}</td>
            <td>{$batch->datereceived}</td>
            <td>{$total}</td>
            <td>{$rej}</td>
            <td>{$dt}</td>
            <td>{$dm}</td>
            <td>{$pos}</td>
            <td>{$neg}</td>
            <td>{$redraw}</td>
            <td>{$failed}</td>
            <td>{$delays}</td>
            </tr>";
        }


        return view('tables.dispatch', ['rows' => $table_rows, 'pending' => $batches->count()])->with('pageTitle', 'Batches Awaiting Dispatch');

    }

    public function approve_site_entry()
    {
        $batches = Batch::select('batches.*', 'facilitys.name')
            ->join('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            ->whereNull('received_by')
            ->where('site_entry', 1)
            ->get();

        $my = new Misc;
        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = $my->get_subtotals($batch_ids, false);
        $rejected = $my->get_rejected($batch_ids, false);

        $table_rows = "";

        foreach ($batches as $key => $batch) {

            $noresult = $this->checknull($subtotals->where('batch_id', $batch->id)->where('result', 0));

            $rej = $this->checknull($rejected->where('batch_id', $batch->id));
            $total = $noresult + $rej;

            $result = $noresult = $datereceived = '';

            $table_rows .= "<tr> 
            <td>{$batch->id}</td>
            <td>{$batch->name}</td>
            <td>{$batch->datereceived}</td>
            <td>" . $batch->created_at->toDateString() . "</td>
            <td></td>
            <td></td>
            <td>{$total}</td>
            <td>{$rej}</td>
            <td>{$result}</td>
            <td>{$noresult}</td>" . $my->batch_status($batch->id, $batch->batch_complete, true) . "
            </tr>";
        }
        return view('tables.batches', ['rows' => $table_rows, 'links' => ''])->with('pageTitle', 'Site Entry Approval');

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
        $samples = $batch->sample;
        $samples->load(['patient.mother']);
        $batch->load(['facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

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
        if($var->isEmpty()){
            return 0;
        }else{
            return $var->first()->totals;
        }
    }


}
