<?php

namespace App\Http\Controllers;

use App\Viralworksheet;
use App\Viralsample;
use App\User;
use App\MiscViral;
use App\Lookup;
use DB;
use Excel;
use Illuminate\Http\Request;

class ViralworksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state=0, $date_start=NULL, $date_end=NULL)
    {
        $worksheets = Viralworksheet::selectRaw('viralworksheets.*, count(viralsamples.id) AS samples_no, users.surname, users.oname')
            ->join('viralsamples', 'viralsamples.worksheet_id', '=', 'viralworksheets.id')
            ->join('users', 'users.id', '=', 'viralworksheets.createdby')
            ->when($state, function ($query) use ($state){
                return $query->where('status_id', $state);
            })
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('viralworksheets.created_at', '>=', $date_start)
                    ->whereDate('viralworksheets.created_at', '<=', $date_end);
                }
                return $query->whereDate('viralworksheets.created_at', $date_start);
            })
            ->orderBy('viralworksheets.created_at', 'desc')
            ->groupBy('viralworksheets.id')
            ->get();

        // return view('tables.viralworksheets', ['worksheets' => $worksheets, 'statuses' => $statuses, 'machines' => $machines]);
        
        // $statuses = collect($this->wstatus());
        // $machines = collect($this->wmachine());

        $data = Lookup::worksheet_lookups();
        $data['worksheets'] = $worksheets;
        $data['myurl'] = url('viralworksheet/index/' . $state . '/');
        return view('tables.viralworksheets', $data)->with('pageTitle', 'Worksheets');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($machine_type=2)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        if($machine == NULL || $machine->vl_limit == NULL)
        {
            return back();
        }

        $samples = Viralsample::selectRaw("viralsamples.*, viralpatients.patient, facilitys.name, viralbatches.datereceived, viralbatches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
            ->join('viralpatients', 'viralsamples.patient_id', '=', 'viralpatients.id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->whereYear('datereceived', '>', 2014)
            ->whereNull('worksheet_id')
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('viralsamples.id', 'asc')
            ->limit($machine->vl_limit)
            ->get();

        $count = $samples->count();

        if($count == $machine->vl_limit){
            return view('forms.viralworksheets', ['create' => true, 'machine_type' => $machine_type, 'samples' => $samples, 'machine' => $machine])->with('pageTitle', 'Add Worksheet');
        }

        return view('forms.viralworksheets', ['create' => false, 'machine_type' => $machine_type, 'count' => $count])->with('pageTitle', 'Add Worksheet');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $worksheet = new Viralworksheet;
        $worksheet->fill($request->except('_token'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $worksheet->machine_type)->first();

        $samples = Viralsample::selectRaw("viralsamples.*, viralpatients.patient, facilitys.name, viralbatches.datereceived, viralbatches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
            ->join('viralpatients', 'viralsamples.patient_id', '=', 'viralpatients.id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralbatches.facility_id')
            ->whereYear('datereceived', '>', 2014)
            ->whereNull('worksheet_id')
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('viralsamples.id', 'asc')
            ->limit(93)
            ->limit($machine->vl_limit)
            ->get();


        if($samples->count() != $machine->vl_limit){
            $worksheet->delete();
            return back();
        }

        $sample_ids = $samples->pluck('id');

        Viralsample::whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id]);

        return redirect()->route('viralworksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Viralworksheet $Viralworksheet)
    {
        $worksheet->load(['creator']);
        $samples = Sample::where('worksheet_id', $worksheet->id)->with(['patient'])->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples];

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Other Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Abbot Worksheets');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Viralworksheet $Viralworksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Viralworksheet $Viralworksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralworksheet  $Viralworksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralworksheet $Viralworksheet)
    {
        //
    }

    public function print(Viralworksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $samples = Viralsample::where('worksheet_id', $worksheet->id)->with(['patient'])->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples, 'print' => true];

        if($worksheet->machine_type == 2){
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Print Abbot Worksheet');
        }
        else{
            return view('worksheets.other-table', $data)->with('pageTitle', 'Print Other Worksheet');
        }
    }

    public function cancel(Viralworksheet $worksheet)
    {
        Viralsample::where('worksheet_id', $worksheet->id)->update(['worksheet_id' => null, 'result' => null]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        return redirect("/viralworksheet");
    }

    public function cancel_upload(Viralworksheet $worksheet)
    {
        Viralsample::where('worksheet_id', $worksheet->id)->update(['result' => null, 'interpretation' => null, 'datemodified' => null, 'datetested' => null]);
        $worksheet->status_id = 1;
        $worksheet->neg_control_interpretation = $worksheet->highpos_control_interpretation = $worksheet->lowpos_control_interpretation = $worksheet->neg_control_result = $worksheet->highpos_control_result = $worksheet->lowpos_control_result = $worksheet->daterun = null;
        $worksheet->save();

        return redirect("/viralworksheet/upload/" . $worksheet->id);
    }

    public function upload(Viralworksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $users = User::where('user_type_id', '<', 5)->get();
        return view('forms.upload_results', ['worksheet' => $worksheet, 'users' => $users, 'type' => 'viralload'])->with('pageTitle', 'Wroksheet Upload');
    }





    /**
     * Update the specified resource in storage with results file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function save_results(Request $request, Viralworksheet $worksheet)
    {
        $worksheet->fill($request->except(['_token', 'upload']));
        $file = $request->upload->path();
        $today = $dateoftest = date("Y-m-d");
        $nc = $nc_int = $lpc = $lpc_int = $hpc = $hpc_int = NULL;

        $path = $request->upload->store('results/vl');
        $my = new MiscViral;

        if($worksheet->machine_type == 2)
        {
            $dateoftest = $today;
            // config(['excel.import.heading' => false]);
            $data = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $bool = false;

            foreach ($data as $key => $value) {
                if($value[5] == "RESULT"){
                    $bool = true;
                    continue;
                }

                if($bool){
                    $sample_id = $value[1];
                    $result = $value[5];
                    $interpretation = $value[6];
                    $error = $value[10];

                    $result_array = MiscViral::sample_result($result, $error);

                    $data_array = ['datemodified' => $today, 'datetested' => $dateoftest, 'interpretation' => $result_array['interpretation'], 'result' => $result_array['result'], 'units' => $result_array['units']];
                    $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    Viralsample::where($search)->update($data_array);

                    if($sample_id == "HIV_NEG"){
                        $nc = $result_array['result'];
                        $nc_int = $result_array['interpretation']; 
                    }
                    else if($sample_id == "HIV_HIPOS"){
                        $hpc = $result_array['result'];
                        $hpc_int = $result_array['interpretation'];
                    }
                    else if($sample_id == "HIV_LOPOS"){
                        $lpc = $result_array['result'];
                        $lpc_int = $result_array['interpretation'];
                    }
                }
                if($bool && $value[5] == "RESULT") break;
            }
        }
        else
        {
            $handle = fopen($file, "r");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $dateoftest=date("Y-m-d", strtotime($data[3]));

                $sample_id = $value[4];
                $result = $value[8];
                $error = $value[10];

                $result_array = MiscViral::sample_result($result, $error);

                $data_array = ['datemodified' => $today, 'datetested' => $dateoftest, 'interpretation' => $result_array['interpretation'], 'result' => $result_array['result'], 'units' => $result_array['units']];
                $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                Viralsample::where($search)->update($data_array);

                $sample_type = $value[5];

                if($sample_type == "NC"){
                    $nc = $result_array['result'];
                    $nc_int = $result_array['interpretation']; 
                }
                else if($sample_type == "HPC"){
                    $hpc = $result_array['result'];
                    $hpc_int = $result_array['interpretation'];
                }
                else if($sample_type == "LPC"){
                    $lpc = $result_array['result'];
                    $lpc_int = $result_array['interpretation'];
                }

            }
            fclose($handle);

        }

        Viralsample::where(['worksheet_id' => $worksheet->id])->where('run', 0)->update(['run' => 1]);

        $worksheet->neg_control_interpretation = $nc_int;
        $worksheet->neg_control_result = $nc;

        $worksheet->highpos_control_interpretation = $hpc_int;
        $worksheet->highpos_control_result = $hpc;

        $worksheet->lowpos_control_interpretation = $lpc_int;
        $worksheet->lowpos_control_result = $lpc;

        $worksheet->daterun = $dateoftest;
        $worksheet->save();

        MiscViral::requeue($worksheet->id);

        return redirect('viralworksheet/approve/' . $worksheet->id);
    }


    public function approve_results(Viralworksheet $worksheet)
    {
        $worksheet->load(['reviewer', 'creator', 'runner', 'sorter', 'bulker']);

        $actions = DB::table('actions')->get();
        $dilutions = DB::table('viraldilutionfactors')->get();
        $samples = Viralsample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();

        $noresult = $this->checknull($this->get_worksheet_results(0, $worksheet->id));
        $failed = $this->checknull($this->get_worksheet_results(3, $worksheet->id));
        $detected = $this->checknull($this->get_worksheet_results(2, $worksheet->id));
        $undetected = $this->checknull($this->get_worksheet_results(1, $worksheet->id));

        $total = $detected + $undetected + $failed + $noresult;

        $subtotals = ['detected' => $detected, 'undetected' => $undetected, 'failed' => $failed, 'noresult' => $noresult, 'total' => $total];

        return view('tables.confirm_viral_results', ['actions' => $actions, 'dilutions' => $dilutions, 'samples' => $samples, 'subtotals' => $subtotals, 'worksheet' => $worksheet, 'double_approval' => Lookup::$double_approval])->with('pageTitle', 'Approve Results');
    }

    public function approve(Request $request, Viralworksheet $worksheet)
    {
        $double_approval = Lookup::$double_approval;
        $samples = $request->input('samples');
        $batches = $request->input('batches');
        $redraws = $request->input('redraws');
        $results = $request->input('results');
        $actions = $request->input('actions');
        $dilutions = $request->input('dilutionfactors');

        $today = date('Y-m-d');
        $approver = auth()->user()->id;

        $batch = array();

        foreach ($samples as $key => $value) {

            if(in_array(env('APP_LAB'), $double_approval) && $worksheet->reviewedby && !$worksheet->reviewedby2){
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

            $data['repeatt'] = $actions[$key];
            $data['dilutionfactor'] = $dilutions[$key];

            if(is_int($results[$key])){
                $data['result'] = $results[$key] * $dilutions[$key];
            }
            else{
                $data['result'] = $results[$key];
            }

            if(isset($redraws[$key])) $data['result'] = "Collect New Sample";

            Viralsample::where('id', $samples[$key])->update($data);

            if($actions[$key] == 1){
                MiscViral::save_repeat($samples[$key]);
            }
        }

        if(in_array(env('APP_LAB'), $double_approval)){
            if($worksheet->reviewedby && $worksheet->reviewedby != $approver){
                $batch = collect($batches);
                $b = $batch->unique();
                $unique = $b->values()->all();

                foreach ($unique as $value) {
                    MiscViral::check_batch($value);
                }

                $worksheet->status_id = 3;
                $worksheet->datereviewed2 = $today;
                $worksheet->reviewedby2 = $approver;
                $worksheet->save();

                return redirect('/viralbatch/dispatch');                 
            }
            else{
                $worksheet->datereviewed = $today;
                $worksheet->reviewedby = $approver;
                $worksheet->save();

                return redirect('/viralworksheet');
            }
        }

        else{
            $batch = collect($batches);
            $b = $batch->unique();
            $unique = $b->values()->all();

            foreach ($unique as $value) {
                MiscViral::check_batch($value);
            }

            $worksheet->status_id = 3;
            $worksheet->datereviewed = $today;
            $worksheet->reviewedby = $approver;
            $worksheet->save();

            return redirect('/viralbatch/dispatch');            
        }
    }




    public function wstatus()
    {
        $statuses = [
            collect(['status' => 1, 'string' => "<strong><font color='#FFD324'>In-Process</font></strong>"]),
            collect(['status' => 2, 'string' => "<strong><font color='#0000FF'>Tested</font></strong>"]),
            collect(['status' => 3, 'string' => "<strong><font color='#339900'>Approved</font></strong>"]),
            collect(['status' => 4, 'string' => "<strong><font color='#FF0000'>Cancelled</font></strong>"]),
        ];

        return $statuses;
    }

    public function wmachine()
    {
        $machines = [
            collect(['machine' => 1, 'string' => "<strong> TaqMan </strong>"]),
            collect(['machine' => 2, 'string' => "<strong><font color='#0000FF'> Abbott </font></strong>"]),
            collect(['machine' => 3, 'string' => "<strong> C8800 </strong>"]),
            collect(['machine' => 4, 'string' => "<strong><font color='#FF00FB'> Panther </font></strong>"]),
        ];

        return $machines;
    }

    public function get_worksheet_results($result, $worksheet_id=NULL)
    {
        $samples = Viralsample::selectRaw("count(*) as totals, worksheet_id")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->where('inworksheet', 1)
            ->where('receivedstatus', '!=', 2)
            ->when(true, function($query) use ($result){
                if ($result == 0) {
                    return $query->whereNull('result');
                }
                else if ($result == 1) {
                    return $query->where('result', '< LDL copies/ml');
                }
                else if ($result == 2) {
                    return $query->where('result', '!=', 'Failed')->where('result', '!=', '< LDL copies/ml');
                }
                else if ($result == 3) {
                    return $query->where('result', 'Failed');
                }                
            })
            ->groupBy('worksheet_id')
            ->get();

        return $samples;
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $worksheets = Viralworksheet::whereRaw("id like '" . $search . "%'")->paginate(10);
        return $worksheets;
    }

    public function checknull($var)
    {
        return $var->first()->totals ?? 0;
    }
}
