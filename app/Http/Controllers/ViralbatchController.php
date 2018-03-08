<?php

namespace App\Http\Controllers;

use App\Viralbatch;
use App\Viralsample;
use App\MiscViral;

use DB;

use App\Mail\VlDispatch;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class ViralbatchController extends Controller
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
     * @param  \App\Viralbatch  $viralbatch
     * @return \Illuminate\Http\Response
     */
    public function show(Viralbatch $viralbatch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Viralbatch  $viralbatch
     * @return \Illuminate\Http\Response
     */
    public function edit(Viralbatch $viralbatch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Viralbatch  $viralbatch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Viralbatch $viralbatch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralbatch  $viralbatch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralbatch $viralbatch)
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
                Mail::to('joelkith@gmail.com')->send(new VlDispatch($batch, $facility));
            // }            
        }

        DB::table('viralbatches')->whereIn('id', $batches)->update(['datedispatched' => date('Y-m-d'), 'batch_complete' => 1]);
    }

    public function get_results()
    {

    }

    public function get_subtotals($batch_id=NULL)
    {
        $samples = Viralsample::selectRaw("count(viralsamples.id) as totals, batch_id, result")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
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

    public function get_totals($result, $batch_id=NULL)
    {
        $samples = Viralsample::selectRaw("count(*) as totals, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->whereNotNull('batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                return $query->where('batch_id', $batch_id);
            })
            ->when(true, function($query) use ($result){
                if ($result == 0) {
                    return $query->whereNull('result');
                }
                else if ($result == 1) {
                    return $query->where('result', '< LDL copies/ml');
                }
                else if ($result == 2) {
                    return $query->where('result', '!=', 'Failed')
                    ->where('result', '!=', 'Collect New Sample')
                    ->where('result', '!=', '< LDL copies/ml');
                }
                else if ($result == 3) {
                    return $query->where('result', 'Failed');
                } 
                else if ($result == 5) {
                    return $query->where('result', 'Collect New Sample');
                }               
            })
            ->where('batch_complete', 2)
            ->where('repeatt', 0)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public function get_rejected($batch_id=NULL)
    {
        $samples = Viralsample::selectRaw("count(viralsamples.id) as totals, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
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
        $samples = Viralsample::selectRaw("max(datemodified) as mydate, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                return $query->where('batch_id', $batch_id);
            })
            ->where('batch_complete', 2)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public function get_maxdatetested($batch_id=NULL)
    {
        $samples = Viralsample::selectRaw("max(datetested) as mydate, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                return $query->where('batch_id', $batch_id);
            })
            ->where('batch_complete', 2)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public function get_rows($batch_list=NULL)
    {
        $my = new MiscViral;

        $batches = Viralbatch::select('viralbatches.*', 'view_facilitys.email', 'view_facilitys.name')
            ->join('view_facilitys', 'view_facilitys.id', '=', 'viralbatches.facility_id')
            ->when($batch_list, function($query) use ($batch_list){
                return $query->whereIn('viralbatches.id', $batch_list);
            })
            ->where('batch_complete', 2)->get();


        $noresult_a = $this->get_totals(0);
        $redraw_a = $this->get_totals(5);
        $failed_a = $this->get_totals(3);
        $detected_a = $this->get_totals(2);
        $undetected_a = $this->get_totals(1);

        $rejected = $this->get_rejected();
        $date_modified = $this->get_maxdatemodified();
        $date_tested = $this->get_maxdatetested();
        $currentdate=date('d-m-Y');

        $table_rows = "";

        foreach ($batches as $key => $batch) {

            $noresult = $this->checknull($noresult_a->where('batch_id', $batch->id));
            $redraw = $this->checknull($redraw_a->where('batch_id', $batch->id));
            $failed = $this->checknull($failed_a->where('batch_id', $batch->id));
            $detected = $this->checknull($detected_a->where('batch_id', $batch->id));
            $undetected = $this->checknull($undetected_a->where('batch_id', $batch->id));
            $rej = $this->checknull($rejected->where('batch_id', $batch->id));

            $results = $undetected + $detected;
            $total = $noresult + $failed + $redraw + $results + $rej;

            $dm = $date_modified->where('batch_id', $batch->id)->first()->mydate;
            $dt = $date_tested->where('batch_id', $batch->id)->first()->mydate;

            $maxdate=date("d-M-Y",strtotime($dm));

            $delays = $my->working_days($maxdate, $currentdate);

            // switch ($batch->batch_complete) {
            //     case 0:
            //         $status = "In process";
            //         break;
            //     case 1:
            //         $status = "Dispatched";
            //         break;
            //     case 2:
            //         $status = "Awaiting Dispatch";
            //         break;
            //     default:
            //         break;
            // }

            $table_rows .= "<tr> 
            <td><div align='center'><input name='batches[]' type='checkbox' id='batches[]' value='{$batch->id}' /> </div></td>
            <td>{$batch->id}</td>
            <td>{$batch->name}</td>
            <td>{$batch->email}</td>
            <td>{$batch->datereceived}</td>
            <td>{$batch->created_at}</td>
            <td>{$delays}</td>
            <td>{$total}</td>
            <td>{$rej}</td>
            <td>{$results}</td>
            <td>{$failed}</td>
            <td>{$redraw}</td>
            <td>{$status}</td>
            <td><a href='" . url("/viralbatch/" . $batch->id) . "'>View</a> </td>
            </tr>";
        }


        return view('tables.dispatch_viral', ['rows' => $table_rows, 'pending' => $batches->count()]);

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
