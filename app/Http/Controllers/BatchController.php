<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Sample;
use App\Misc;
use App\Lookup;

use DB;

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
    public function index()
    {
        return $this->display_batches();
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
        $lookup = new Lookup;
        $data = $lookup->get_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

        return view('tables.batch_details', $data);
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
                Mail::to('joelkith@gmail.com')->send(new EidDispatch($batch, $facility));
            // }            
        }

        DB::table('batches')->whereIn('id', $batches)->update(['datedispatched' => date('Y-m-d'), 'batch_complete' => 1]);
    }

    public function get_results()
    {

    }

    public function get_subtotals($batch_id=NULL, $complete=true)
    {

        $samples = Sample::selectRaw("count(samples.id) as totals, batch_id, result")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                else{
                    return $query->where('batch_id', $batch_id);
                }
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('repeatt', 0)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id', 'result')
            ->get();

        return $samples;
    }

    public function get_rejected($batch_id=NULL, $complete=true)
    {
        $samples = Sample::selectRaw("count(samples.id) as totals, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                return $query->where('batch_id', $batch_id);
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public function get_maxdatemodified($batch_id=NULL, $complete=true)
    {
        $samples = Sample::selectRaw("max(datemodified) as mydate, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                return $query->where('batch_id', $batch_id);
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public function get_maxdatetested($batch_id=NULL, $complete=true)
    {
        $samples = Sample::selectRaw("max(datetested) as mydate, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                return $query->where('batch_id', $batch_id);
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public function get_rows($batch_list=NULL)
    {
        $my = new Misc;

        $batches = Batch::select('batches.*', 'view_facilitys.email', 'view_facilitys.name')
            ->join('view_facilitys', 'view_facilitys.id', '=', 'batches.facility_id')
            ->when($batch_list, function($query) use ($batch_list){
                return $query->whereIn('batches.id', $batch_list);
            })
            ->where('batch_complete', 2)
            ->get();
        $get_subtotals = $this->get_subtotals();
        $rejected = $this->get_rejected();
        $date_modified = $this->get_maxdatemodified();
        $date_tested = $this->get_maxdatetested();
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


        return view('tables.dispatch', ['rows' => $table_rows, 'pending' => $batches->count()]);

    }

    public function display_batches($page=NULL, $date_start=NULL, $date_end=NULL)
    {
        $user = auth()->user();
        $test = false;
        if($user->user_type_id == 5) $test=true;

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
            ->when($test, function($query) use ($string){
                return $query->whereRaw($string);
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

        $batches = Batch::select('batches.*', 'view_facilitys.name')
            ->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'batches.facility_id')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('batches.datereceived', '>=', $date_start)
                    ->whereDate('batches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('batches.datereceived', $date_start);
            })
            ->when($test, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->limit($page_limit)
            ->offset($offset)
            ->get();

        if($batches->isEmpty()){
            return view('tables.batches', ['rows' => null, 'links' => null]);
        }

        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = $this->get_subtotals($batch_ids, false);
        $rejected = $this->get_rejected($batch_ids, false);
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

        $links = $my->page_links($page, $last_page, $date_start, $date_end);

        return view('tables.batches', ['rows' => $table_rows, 'links' => $links]);
    }

    public function approve_site_entry()
    {
        $batches = Batch::select('batches.*', 'view_facilitys.name')
            ->join('view_facilitys', 'view_facilitys.id', '=', 'batches.facility_id')
            ->whereNull('received_by')
            ->where('site_entry', 2)
            ->get();

        $my = new Misc;
        $batch_ids = $batches->pluck(['id'])->toArray();
        $subtotals = $this->get_subtotals($batch_ids, false);
        $rejected = $this->get_rejected($batch_ids, false);

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

        return view('tables.batches', ['rows' => $table_rows, 'links' => '']);

    }

    public function checknull($var)
    {
        if($var->isEmpty()){
            return 0;
        }else{
            // return $var->sum('totals');
            return $var->first()->totals;
        }
    }


}
