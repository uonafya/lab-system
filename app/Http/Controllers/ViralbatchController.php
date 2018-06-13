<?php

namespace App\Http\Controllers;

use App\Facility;
use App\Viralbatch;
use App\Viralsample;
use App\MiscViral;
use App\Lookup;

// use DOMPDF;
use Mpdf\Mpdf;

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
        $user = auth()->user();
        $facility_user = false;
        $date_column = "viralbatches.datereceived";
        if($batch_complete == 1) $date_column = "viralbatches.datedispatched";
        if($user->user_type_id == 5) $facility_user=true;

        $facility_id = session()->pull('facility_search');
        if($facility_id){ 
            $myurl = url("viralbatch/facility/{$facility_id}/{$batch_complete}"); 
            $myurl2 = url("viralbatch/facility/{$facility_id}"); 
        }
        else{ 
            $myurl = url('viralbatch/index/' . $batch_complete); 
            $myurl2 = url('viralbatch/index'); 
        }

        $string = "(user_id='{$user->id}' OR viralbatches.facility_id='{$user->facility_id}')";

        $batches = Viralbatch::select(['viralbatches.*', 'facilitys.name', 'users.surname', 'users.oname'])
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->leftJoin('users', 'users.id', '=', 'viralbatches.user_id')
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
                return $query->where('viralbatches.facility_id', $facility_id);
            })
            ->when(true, function($query) use ($batch_complete){
                if($batch_complete < 4) return $query->where('batch_complete', $batch_complete);
            })
            ->orderBy($date_column, 'desc')
            ->paginate();

        $batch_ids = $batches->pluck(['id'])->toArray();
        $noresult_a = MiscViral::get_totals(0, $batch_ids, false);
        $redraw_a = MiscViral::get_totals(5, $batch_ids, false);
        $failed_a = MiscViral::get_totals(3, $batch_ids, false);
        $detected_a = MiscViral::get_totals(2, $batch_ids, false);
        $undetected_a = MiscViral::get_totals(1, $batch_ids, false);

        $rejected = MiscViral::get_rejected($batch_ids, false);

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
            $batch->datedispatched = $batch->my_date_format('datedispatched');
            $batch->total = $total;
            $batch->rejected = $rej;
            $batch->result = $result;
            $batch->noresult = $noresult;
            $batch->status = $batch->batch_complete;
            $batch->approval = false;
            return $batch;
        });

        return view('tables.batches', ['batches' => $batches, 'myurl' => $myurl, 'myurl2' => $myurl2, 'pre' => 'viral', 'batch_complete' => $batch_complete])->with('pageTitle', 'Samples by Batch');
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
     * @param  \App\Viralbatch  $viralbatch
     * @return \Illuminate\Http\Response
     */
    public function show(Viralbatch $viralbatch)
    {
        $viralsamples = $viralbatch->sample;
        $viralsamples->load(['patient']);
        $viralbatch->load(['view_facility', 'receiver', 'creator.facility']);
        $data = Lookup::get_viral_lookups();
        $data['batch'] = $viralbatch;
        $data['samples'] = $viralsamples;

        return view('tables.viralbatch_details', $data)->with('pageTitle', 'Batches');
    }

    public function transfer(Viralbatch $viralbatch)
    {
        $viralsamples = $viralbatch->sample;
        $viralsamples->load(['patient']);
        $viralbatch->load(['view_facility', 'receiver', 'creator.facility']);
        $data = Lookup::get_viral_lookups();
        $data['batch'] = $viralbatch;
        $data['samples'] = $viralsamples;

        return view('tables.transfer_viralbatch_samples', $data)->with('pageTitle', 'Transfer Samples');
    }

    public function transfer_to_new_batch(Request $request, Viralbatch $batch)
    {
        $sample_ids = $request->input('samples');

        if(!$sample_ids){
            session(['toast_message' => "No samples have been selected."]);
            session(['toast_error' => 1]);
            return back();            
        }

        $new_batch = new Viralbatch;
        $new_batch->fill($batch->replicate(['synched', 'batch_full'])->toArray());
        $new_batch->id = (int) $batch->id + 0.5;
        $new_id = $batch->id + 0.5;
        if($new_batch->id == floor($new_batch->id)){
            session(['toast_message' => "The batch {$batch->id} cannot have its samples transferred."]);
            session(['toast_error' => 1]);
            return back();
        }
        $new_batch->save();

        $count = 0;

        foreach ($sample_ids as $key => $id) {
            $sample = Viralsample::find($id);
            if($sample->parentid) continue;
            if($sample->result) continue;
            $sample->batch_id = $new_id;
            $sample->pre_update();
            $count++;
        }

        MiscViral::check_batch($batch->id);
        MiscViral::check_batch($new_id);

        session(['toast_message' => "The batch {$batch->id} has had {$count} samples transferred to  batch {$new_id}."]);
        return redirect('viralbatch/' . $new_id);
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
        $final_dispatch = $request->input('final_dispatch');

        if (empty($batches)){
            session(['toast_message' => "No batch selected<br /><br />Please select a batch",
                'toast_error' => 1]);
            return redirect('/viralbatch/dispatch');
            // return redirect('/viralbatch/complete_dispatch');
        }
        if(!$final_dispatch) return $this->get_rows($batches);
        
        foreach ($batches as $key => $value) {
            $batch = Viralbatch::find($value);
            $facility = Facility::find($batch->facility_id);
            // if($facility->email != null || $facility->email != '')
            // {
                // Mail::to($facility->email)->send(new VlDispatch($batch));
                $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
                // $mail_array = array('joelkith@gmail.com');
                Mail::to($mail_array)->send(new VlDispatch($batch));
            // }            
        }

        Viralbatch::whereIn('id', $batches)->update(['datedispatched' => date('Y-m-d'), 'batch_complete' => 1]);
        
        return redirect('/viralbatch');
    }

    public function get_rows($batch_list=NULL)
    {
        $batches = Viralbatch::select('viralbatches.*', 'facilitys.email', 'facilitys.name')
            ->join('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->when($batch_list, function($query) use ($batch_list){
                return $query->whereIn('viralbatches.id', $batch_list);
            })
            ->where('batch_complete', 2)->get();

        $noresult_a = MiscViral::get_totals(0);
        $redraw_a = MiscViral::get_totals(5);
        $failed_a = MiscViral::get_totals(3);
        $detected_a = MiscViral::get_totals(2);
        $undetected_a = MiscViral::get_totals(1);

        $rejected = MiscViral::get_rejected();
        $date_modified = MiscViral::get_maxdatemodified();
        $date_tested = MiscViral::get_maxdatetested();
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
            $batch->result = $results;
            $batch->failed = $failed;
            $batch->rejected = $rej;
            $batch->date_modified = $dm;
            $batch->date_tested = $dt;
            $batch->status = $status;
            return $batch;
        });

        return view('tables.dispatch_viral', ['batches' => $batches, 'pending' => $batches->count(), 'batch_list' => $batch_list])->with('pageTitle', 'Batch Dispatch');

        // $table_rows = "";

        // foreach ($batches as $key => $batch) {

        //     $noresult = $this->checknull($noresult_a->where('batch_id', $batch->id));
        //     $redraw = $this->checknull($redraw_a->where('batch_id', $batch->id));
        //     $failed = $this->checknull($failed_a->where('batch_id', $batch->id));
        //     $detected = $this->checknull($detected_a->where('batch_id', $batch->id));
        //     $undetected = $this->checknull($undetected_a->where('batch_id', $batch->id));
        //     $rej = $this->checknull($rejected->where('batch_id', $batch->id));

        //     $results = $undetected + $detected;
        //     $total = $noresult + $failed + $redraw + $results + $rej;

        //     $dm = $date_modified->where('batch_id', $batch->id)->first()->mydate;
        //     $dt = $date_tested->where('batch_id', $batch->id)->first()->mydate;

        //     $maxdate=date("d-M-Y",strtotime($dm));

        //     $delays = MiscViral::working_days($maxdate, $currentdate);

        //     switch ($batch->batch_complete) {
        //         case 0:
        //             $status = "In process";
        //             break;
        //         case 1:
        //             $status = "Dispatched";
        //             break;
        //         case 2:
        //             $status = "Awaiting Dispatch";
        //             break;
        //         default:
        //             break;
        //     }

        //     $table_rows .= "<tr> 
        //     <td><div align='center'><input name='batches[]' type='checkbox' id='batches[]' value='{$batch->id}' /> </div></td>
        //     <td>{$batch->id}</td>
        //     <td>{$batch->name}</td>
        //     <td>{$batch->email}</td>
        //     <td>{$batch->datereceived}</td>
        //     <td>{$batch->created_at}</td>
        //     <td>{$delays}</td>
        //     <td>{$total}</td>
        //     <td>{$rej}</td>
        //     <td>{$results}</td>
        //     <td>{$failed}</td>
        //     <td>{$redraw}</td>
        //     <td>{$status}</td>
        //     <td><a href='" . url("/viralbatch/" . $batch->id) . "'>View</a> </td>
        //     </tr>";
        // }


        // return view('tables.dispatch_viral', ['rows' => $table_rows, 'pending' => $batches->count()])->with('pageTitle', 'Batch Dispatch');

    }

    public function approve_site_entry()
    {
        $batches = Viralbatch::selectRaw("viralbatches.*, COUNT(viralsamples.id) AS sample_count, facilitys.name, creator.name as creator")
            ->leftJoin('viralsamples', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->leftJoin('facilitys as creator', 'creator.id', '=', 'viralbatches.user_id')
            ->whereRaw('(receivedstatus is null or received_by is null)')
            // ->whereNull('received_by')
            // ->whereNull('receivedstatus')
            ->where('site_entry', 1)
            ->groupBy('viralbatches.id')
            ->paginate();
            
        $batch_ids = $batches->pluck(['id'])->toArray();

        $noresult_a = MiscViral::get_totals(0, $batch_ids, false);

        $rejected = MiscViral::get_rejected($batch_ids, false);

        $batches->transform(function($batch, $key) use ($noresult_a, $rejected){

            $noresult = $noresult_a->where('batch_id', $batch->id)->first()->totals ?? 0;
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
            $data['site_entry_approval'] = true;
            return view('forms.viralsamples', $data);
        }
        else{
            $batch->received_by = auth()->user()->id;
            $batch->save();
            return redirect('viralbatch/site_approval');
        }
    }


    public function site_entry_approval_group(Viralbatch $batch)
    {
        $samples = Viralsample::with(['patient'])->where('batch_id', $batch->id)->whereNull('receivedstatus')->get();

        if($samples->count() > 0){            
            $data = Lookup::viralsample_form();
            $batch->load(['creator.facility', 'view_facility']);
            $data['batch'] = $batch;
            $data['samples'] = $samples;
            $data['pageTitle'] = "Approve batch";
            return view('forms.approve_viralbatch', $data);
        }
        else{
            return redirect('batch/site_approval');
        }
    }

    public function site_entry_approval_group_save(Request $request, Viralbatch $batch)
    {
        $sample_ids = $request->input('samples');
        $rejectedreason_array = $request->input('rejectedreason');
        $submit_type = $request->input('submit_type');

        if(!$sample_ids) return back();

        foreach ($sample_ids as $key => $value) {
            $sample = Viralsample::find($value);
            if($sample->batch_id != $batch->id) continue;

            $sample->labcomment = $request->input('labcomment');

            if($submit_type == "accepted"){
                $sample->receivedstatus = 1;
            }else if($submit_type == "rejected"){
                $sample->receivedstatus = 3;
                $sample->rejectedreason = $rejectedreason_array[$key] ?? null;
            }
            $sample->save();
        }

        $batch->received_by = auth()->user()->id;
        $batch->datereceived = $request->input('datereceived');
        $batch->save();

        session(['toast_message' => 'The selected samples have been ' . $submit_type]);

        $sample = Viralsample::where('batch_id', $batch->id)->whereNull('receivedstatus')->get()->first();
        if($sample) return back();
        return redirect('viralbatch/site_approval');        
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Viralbatch  $batch
     * @return \Illuminate\Http\Response
     */
    public function individual(Viralbatch $batch)
    {
        if(!$batch->dateindividualresultprinted){
            $batch->dateindividualresultprinted = date('Y-m-d');
            $batch->pre_update();
        }

        $samples = $batch->sample;
        $samples->load(['patient']);
        $batch->load(['facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_viral_lookups();
        $data['batch'] = $batch;
        $data['samples'] = $samples;

        return view('exports.viralsamples', $data)->with('pageTitle', 'Individual Batches');
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Viralbatch  $batch
     * @return \Illuminate\Http\Response
     */
    public function summary(Viralbatch $batch)
    {
        if(!$batch->datebatchprinted){
            $batch->datebatchprinted = date('Y-m-d');
            $batch->pre_update();
        }

        $batch->load(['sample.patient', 'facility', 'lab', 'receiver', 'creator']);
        $data = Lookup::get_viral_lookups();
        $data['batches'] = [$batch];

        $mpdf = new Mpdf(['format' => 'A4-L']);
        $view_data = view('exports.mpdf_viralsamples_summary', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output('summary.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        // $mpdf->Output('summary.pdf', \Mpdf\Output\Destination::DOWNLOAD);



        $pdf = DOMPDF::loadView('exports.viralsamples_summary', $data)->setPaper('a4', 'landscape');
        return $pdf->stream('summary.pdf');
    }

    public function summaries(Request $request)
    {
        $batch_ids = $request->input('batch_ids');
        $batches = Viralbatch::whereIn('id', $batch_ids)->with(['sample.patient', 'facility', 'lab', 'receiver', 'creator'])->get();

        foreach ($batches as $key => $batch) {
            if(!$batch->datebatchprinted){
                $batch->datebatchprinted = date('Y-m-d');
                $batch->pre_update();
            }
        }
        
        $data = Lookup::get_viral_lookups();
        $data['batches'] = $batches;
        $mpdf = new Mpdf(['format' => 'A4-L']);
        $view_data = view('exports.mpdf_viralsamples_summary', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output('summary.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        // $mpdf->Output('summary.pdf', \Mpdf\Output\Destination::INLINE);
        
        // $pdf = DOMPDF::loadView('exports.viralsamples_summary', $data)->setPaper('a4', 'landscape');
        // return $pdf->stream('summary.pdf');
    }

    public function email(Viralbatch $batch)
    {
        $facility = Facility::find($batch->facility_id);
        // if($facility->email != null || $facility->email != '')
        // {
            // Mail::to($facility->email)->send(new VlDispatch($batch));
            $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
            // $mail_array = array('joelkith@gmail.com');
            Mail::to($mail_array)->send(new VlDispatch($batch));
        // }

        session(['toast_message' => "The batch {$batch->id} has had its results sent to the facility."]);
        return back();
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
