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
                $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
                Mail::to($mail_array)->send(new VlDispatch($batch, $facility));
            // }            
        }

        DB::table('viralbatches')->whereIn('id', $batches)->update(['datedispatched' => date('Y-m-d'), 'batch_complete' => 1]);
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

    public function display_batches($page=NULL, $date_start=NULL, $date_end=NULL)
    {
        $user = auth()->user();
        $my = new MiscViral;
        $test = false;

        if($user->user_type_id == 5) $test=true;

        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";
        
        $b = Viralbatch::selectRaw('count(id) as mycount')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('viralbatches.datereceived', '>=', $date_start)
                    ->whereDate('viralbatches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('viralbatches.datereceived', $date_start);
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

        $batches = Viralbatch::select('viralbatches.*', 'facilitys.name')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('viralbatches.datereceived', '>=', $date_start)
                    ->whereDate('viralbatches.datereceived', '<=', $date_end);
                }
                return $query->whereDate('viralbatches.datereceived', $date_start);
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

        $noresult_a = $my->get_totals(0, $batch_ids, false);
        $redraw_a = $my->get_totals(5, $batch_ids, false);
        $failed_a = $my->get_totals(3, $batch_ids, false);
        $detected_a = $my->get_totals(2, $batch_ids, false);
        $undetected_a = $my->get_totals(1, $batch_ids, false);

        $rejected = $my->get_rejected($batch_ids, false);
        $currentdate=date('d-m-Y');

        $table_rows = "";

        foreach ($batches as $key => $batch) {

            $undetected = $this->checknull($undetected_a->where('batch_id', $batch->id));
            $detected = $this->checknull($detected_a->where('batch_id', $batch->id));
            $failed = $this->checknull($failed_a->where('batch_id', $batch->id));
            $redraw = $this->checknull($redraw_a->where('batch_id', $batch->id));
            $noresult = $this->checknull($noresult_a->where('batch_id', $batch->id));

            $rej = $this->checknull($rejected->where('batch_id', $batch->id));
            $total = $undetected + $detected + $failed + $redraw + $noresult + $rej;

            $result = $detected + $undetected + $redraw + $failed;

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
        $batches = Viralbatch::select('viralbatches.*', 'facilitys.name')
            ->join('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->whereNull('received_by')
            ->where('site_entry', 2)
            ->get();

        $my = new MiscViral;
        $batch_ids = $batches->pluck(['id'])->toArray();

        $noresult_a = $my->get_totals(0, $batch_ids, false);

        $rejected = $my->get_rejected($batch_ids, false);

        $table_rows = "";

        foreach ($batches as $key => $batch) {

            $noresult = $this->checknull($noresult_a->where('batch_id', $batch->id));

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

    public function site_entry_approval(Viralbatch $batch)
    {
        $sample = Viralsample::where('batch_id', $batch->id)->whereNull('receivedstatus')->get()->first();

        if($sample){
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
        $samples = $batch->sample;
        $samples->load(['patient']);
        $batch->load(['facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_viral_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

        // $pdf = DOMPDF::loadView('exports.samples_summary', $data);
        // return $pdf->download('summary.pdf');

        return view('exports.samples_summary', $data);
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
