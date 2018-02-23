<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Sample;
use App\Misc;

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
        //
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
        //
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

    public function get_subtotals($batch_id=NULL)
    {
        $samples = Sample::selectRaw("count(samples.id) as totals, batch_id, result")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                return $query->where('batch_id', $batch_id);
            })
            ->where('batch_complete', 2)
            ->where('repeatt', 0)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id', 'result')
            ->get();

        return $samples;
    }

    public function get_rejected($batch_id=NULL)
    {
        $samples = Sample::selectRaw("count(samples.id) as totals, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                return $query->where('batch_id', $batch_id);
            })
            ->where('batch_complete', 2)
            ->where('receivedstatus', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public function get_maxdatemodified($batch_id=NULL)
    {
        $samples = Sample::selectRaw("max(datemodified) as mydate, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                return $query->where('batch_id', $batch_id);
            })
            ->where('batch_complete', 2)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id', 'result')
            ->get();

        return $samples;
    }

    public function get_maxdatetested($batch_id=NULL)
    {
        $samples = Sample::selectRaw("max(datetested) as mydate, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                return $query->where('batch_id', $batch_id);
            })
            ->where('batch_complete', 2)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id', 'result')
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
            ->where('batch_complete', 2)->get();
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
