<?php

namespace App\Http\Controllers;

use App\Viralbatch;
use App\Viralsample;
use App\MiscViral;
use App\Lookup;

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

    public function index($batch_complete=4, $date_start=NULL, $date_end=NULL)
    {
        $myurl = url('viralbatch/index/' . $batch_complete);
        $user = auth()->user();
        $facility_user = false;
        if($user->user_type_id == 5) $facility_user=true;

        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";

        $my = new MiscViral;

        $batches = Viralbatch::select(['viralbatches.*', 'facilitys.name', 'users.surname', 'users.oname'])
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->leftJoin('users', 'users.id', '=', 'viralbatches.user_id')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('viralbatches.datereceived', '>=', $date_start)
                    ->whereDate('viralbatches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('viralbatches.datereceived', $date_start);
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
        $noresult_a = $my->get_totals(0, $batch_ids, false);
        $redraw_a = $my->get_totals(5, $batch_ids, false);
        $failed_a = $my->get_totals(3, $batch_ids, false);
        $detected_a = $my->get_totals(2, $batch_ids, false);
        $undetected_a = $my->get_totals(1, $batch_ids, false);

        $rejected = $my->get_rejected($batch_ids, false);

        $batches->transform(function($batch, $key) use ($undetected_a, $detected_a, $failed_a, $redraw_a, $noresult_a, $rejected){

            $undetected = $undetected_a->where('batch_id', $batch->id)->first()->totals ?? 0;
            $detected = $detected_a->where('batch_id', $batch->id)->first()->totals ?? 0;
            $failed = $failed_a->where('batch_id', $batch->id)->first()->totals ?? 0;
            $redraw = $redraw_a->where('batch_id', $batch->id)->first()->totals ?? 0;
            $noresult = $noresult_a->where('batch_id', $batch->id)->first()->totals ?? 0;

            $rej = $rejected->where('batch_id', $batch->id)->first()->totals ?? 0;
            $total = $undetected + $detected + $failed + $redraw + $noresult + $rej;

            $result = $detected + $undetected + $redraw + $failed;

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

        return view('tables.batches', ['batches' => $batches, 'myurl' => $myurl, 'pre' => 'viral', 'batch_complete' => $batch_complete]);
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
        $viralsamples = $viralbatch->sample;
        $viralsamples->load(['patient']);
        $viralbatch->load(['facility', 'receiver', 'creator']);
        $data = Lookup::get_viral_lookups();
        $data['batch'] = $viralbatch;
        $data['samples'] = $viralsamples;
        // dd($data);

        return view('tables.viralbatch_details', $data);
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
            $batch = Viralbatch::find($value);
            $facility = DB::table('facilitys')->where('id', $batch->facility_id)->get()->first();
            // if($facility->email != null || $facility->email != '')
            // {
                // Mail::to($facility->email)->send(new VlDispatch($batch, $facility));
                // $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
                $mail_array = array('joelkith@gmail.com');
                Mail::to($mail_array)->send(new VlDispatch($batch, $facility));
            // }            
        }

        DB::table('viralbatches')->whereIn('id', $batches)->update(['datedispatched' => date('Y-m-d'), 'batch_complete' => 1]);
        
        return redirect('/viralbatch');
    }

    public function get_rows($batch_list=NULL)
    {
        $my = new MiscViral;

        $batches = Viralbatch::select('viralbatches.*', 'facilitys.email', 'facilitys.name')
            ->join('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->when($batch_list, function($query) use ($batch_list){
                return $query->whereIn('viralbatches.id', $batch_list);
            })
            ->where('batch_complete', 2)->get();

        $noresult_a = $my->get_totals(0);
        $redraw_a = $my->get_totals(5);
        $failed_a = $my->get_totals(3);
        $detected_a = $my->get_totals(2);
        $undetected_a = $my->get_totals(1);

        $rejected = $my->get_rejected();
        $date_modified = $my->get_maxdatemodified();
        $date_tested = $my->get_maxdatetested();
        $currentdate=date('d-m-Y');

        $batches->transform(function($batch, $key) use ($noresult_a, $redraw_a, $failed_a, $detected_a, $undetected_a, $rejected, $date_modified, $date_tested){

            $noresult = $noresult_a->where('batch_id', $batch->id)->first()->totals ?? 0;
            $redraw = $redraw_a->where('batch_id', $batch->id)->first()->totals ?? 0;
            $failed = $failed_a->where('batch_id', $batch->id)->first()->totals ?? 0;
            $detected = $detected_a->where('batch_id', $batch->id)->first()->totals ?? 0;
            $undetected = $undetected_a->where('batch_id', $batch->id)->first()->totals ?? 0;

            $rej = $rejected->where('batch_id', $batch->id)->first()->totals ?? 0;
            $results = $undetected + $detected;
            $total = $noresult + $failed + $redraw + $results + $rej;

            $dm = $date_modified->where('batch_id', $batch->id)->first()->mydate ?? '';
            $dt = $date_tested->where('batch_id', $batch->id)->first()->mydate ?? '';

            switch ($batch->batch_complete) {
                case 0:
                    $status = "In process";
                    break;
                case 1:
                    $status = "Dispatched";
                    break;
                case 2:
                    $status = "Awaiting Dispatch";
                    break;
                default:
                    break;
            }

            $batch->total = $total;
            $batch->redraw = $redraw;
            $batch->noresult = $noresult;
            $batch->result = $result;
            $batch->failed = $failed;
            $batch->rejected = $rej;
            $batch->date_modified = $dm;
            $batch->date_tested = $dt;
            $batch->status = $status;
            return $batch;
        });

        return view('tables.dispatch_viral', ['batches' => $batches, 'pending' => $batches->count()]);

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

            $delays = MiscViral::working_days($maxdate, $currentdate);

            switch ($batch->batch_complete) {
                case 0:
                    $status = "In process";
                    break;
                case 1:
                    $status = "Dispatched";
                    break;
                case 2:
                    $status = "Awaiting Dispatch";
                    break;
                default:
                    break;
            }

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

    public function approve_site_entry()
    {
        $batches = Viralbatch::select(['viralbatches.*', 'facilitys.name', 'creator.name as creator'])
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->leftJoin('facilitys as creator', 'creator.id', '=', 'viralbatches.user_id')
            ->whereNull('received_by')
            ->where('site_entry', 1)
            ->paginate();

        $my = new MiscViral;
        $batch_ids = $batches->pluck(['id'])->toArray();

        $noresult_a = $my->get_totals(0, $batch_ids, false);

        $rejected = $my->get_rejected($batch_ids, false);

        $batches->transform(function($batch, $key) use ($noresult_a, $rejected){

            $noresult = $noresult_a->where('batch_id', $batch->id)->first()->totals ?? 0;
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

        return view('tables.batches', ['batches' => $batches, 'site_approval' => true, 'pre' => 'viral']);
    }

    public function site_entry_approval(Viralbatch $batch)
    {
        $viralsample = Viralsample::where('batch_id', $batch->id)->whereNull('receivedstatus')->get()->first();

        if($viralsample){
            session(['site_entry_approval' => true]);
            $viralsample->load(['patient', 'batch']);
            $data = Lookup::viralsample_form();
            $data['viralsample'] = $viralsample;
            return view('forms.viralsamples', $data);
        }
        else{
            $batch->received_by = auth()->user()->id;
            $batch->save();
            return redirect('viralbatch/site_approval');
        }
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Viralbatch  $batch
     * @return \Illuminate\Http\Response
     */
    public function individual(Viralbatch $batch)
    {
        $samples = $batch->sample;
        $samples->load(['patient']);
        $batch->load(['facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_viral_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

        return view('exports.viralsamples', $data);
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Viralbatch  $batch
     * @return \Illuminate\Http\Response
     */
    public function summary(Viralbatch $batch)
    {
        $batch->load(['sample.patient', 'facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_viral_lookups();
        $data['batches'] = [$batch];
        $pdf = DOMPDF::loadView('exports.viralsamples_summary', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('summary.pdf');
    }

    public function batches_summary(Request $request)
    {
        $batch_ids = $request->input('batch_ids');
        $batches = Viralbatch::whereIn('id', $batch_ids)->with(['sample.patient', 'facility', 'lab', 'receiver', 'creator'])->get();
        $data = Lookup::get_viral_lookups();
        $data['batches'] = $batches;
        $pdf = DOMPDF::loadView('exports.viralsamples_summary', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('summary.pdf');
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";

        $search = $request->input('search');
        $batches = Viralbatch::whereRaw("id like '" . $search . "%'")
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
