<?php

namespace App\Http\Controllers;

use App\Viralworksheet;
use App\Viralsample;
use App\User;
use App\MiscViral;
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
    public function index()
    {
        $state = session()->pull('viral_worksheet_state', null);
        // $worksheets = Viralworksheet::with(['creator', 'sample'])
        // ->when($state, function ($query) use ($state){
        //     return $query->where('status_id', $state);
        // })
        // ->get();

        $worksheets = Viralworksheet::selectRaw('viralworksheets.*, count(viralsamples.id) AS samples_no, users.surname, users.oname')
            ->join('viralsamples', 'viralsamples.worksheet_id', '=', 'viralworksheets.id')
            ->join('users', 'users.id', '=', 'viralworksheets.createdby')
            // ->leftJoin('worksheetstatus', 'worksheetstatus.id', '=', 'viralworksheets.status_id') , worksheetstatus.state
            ->when($state, function ($query) use ($state){
                return $query->where('status_id', $state);
            })
            ->groupBy('viralworksheets.id')
            ->get();

        $statuses = collect($this->wstatus());
        $machines = collect($this->wmachine());

        // dd($statuses);

        return view('tables.viralworksheets', ['worksheets' => $worksheets, 'statuses' => $statuses, 'machines' => $machines]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $samples = Viralsample::selectRaw("viralsamples.*, viralpatients.patient, view_facilitys.name, viralbatches.datereceived, viralbatches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
            ->join('viralpatients', 'viralsamples.patient_id', '=', 'viralpatients.id')
            ->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralbatches.facility_id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('viralsamples.id', 'asc')
            ->limit(93)
            ->get();

        $count = $samples->count();

        if($count == 93){
            return view('forms.viralworksheets', ['create' => true, 'machine_type' => 2, 'samples' => $samples]);
        }

        return view('forms.viralworksheets', ['create' => false, 'machine_type' => 2, 'count' => $count]);
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

        $samples = Viralsample::selectRaw("viralsamples.*, viralpatients.patient, view_facilitys.name, viralbatches.datereceived, viralbatches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
            ->join('viralpatients', 'viralsamples.patient_id', '=', 'viralpatients.id')
            ->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralbatches.facility_id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('viralsamples.id', 'asc')
            ->limit(93)
            ->get();

        // if($samples->count() != 22 || $samples->count() != 94){
        //     return back();
        // }

        $sample_ids = $samples->pluck('id');

        DB::table('viralsamples')->whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id, 'inworksheet' => true]);

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
        //
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

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', ['worksheet' => $worksheet, 'samples' => $samples]);
        }
        else{
            return view('worksheets.abbot-table', ['worksheet' => $worksheet, 'samples' => $samples]);
        }
    }

    public function cancel(Viralworksheet $worksheet)
    {
        DB::table("viralsamples")->where('worksheet_id', $worksheet->id)->update(['worksheet_id' => 0, 'inworksheet' => 0, 'result' => 0]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        return redirect("/viralworksheet");
    }

    public function upload(Viralworksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $users = User::where('user_type_id', '<', 5)->get();
        return view('forms.upload_results', ['worksheet' => $worksheet, 'users' => $users, 'type' => 'viralload']);
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
        $positive_control;
        $negative_control;

        if($worksheet->machine_type == 2)
        {
            $dateoftest = $today;
            // config(['excel.import.heading' => false]);
            $data = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $check = array();

            // dd($data);

            $bool = false;
            $positive_control = $negative_control = "Passed";

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

                    if($result == 'Not Detected' || $result == 'Target Not Detected' || $result == 'Not detected' || $result == '<40 Copies / mL' || $result == '< 40Copies / mL ' || $result == '< 40 Copies/ mL')
                    {
                        $res= "< LDL copies/ml";
                        $interpret="Target Not Detected";
                        $units="";                        
                    }

                    else if($result == 'Collect New Sample')
                    {
                        $res= "Collect New Sample";
                        $interpret="Collect New Sample";
                        $units="";                         
                    }

                    else if($result == 'Failed' || $result == '')
                    {
                        $res= "Failed";
                        $interpret = $error;
                        $units="";                         
                    }

                    else{
                        $res = preg_replace("/[^<0-9]/", "", $result);
                        $interpret = $result;
                        $units="cp/mL";
                    }

                    $data_array = ['datemodified' => $today, 'datetested' => $today, 'interpretation' => $interpret, 'result' => $res, 'units' => $units];
                    $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    DB::table('viralsamples')->where($search)->update($data_array);

                    $check[] = $search;

                    if($sample_id == "HIV_NEG"){
                        $nc = $res;
                        $nc_int = $interpret;
                    }
                    else if($sample_id == "HIV_HIPOS"){
                        $hpc = $res;
                        $hpc_int = $interpret;
                    }
                    else if($sample_id == "HIV_LOPOS"){
                        $lpc = $res;
                        $lpc_int = $interpret;
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
                $interpretation = $data[8];
                $dateoftest=date("Y-m-d", strtotime($data[3]));

                $flag = $data[10];

                if($flag != NULL){
                    $interpretation = $flag;
                }

                if($interpretation == "Target Not Detected" || $interpretation == "Not Detected DBS")
                {
                    $result = 1;
                } 
                else if($interpretation == 1 || $interpretation == "1" || $interpretation == ">1" || $interpretation == ">1 " || $interpretation == "> 1" || $interpretation == "> 1 " || $interpretation == "1.00E+00" || $interpretation == ">1.00E+00" || $interpretation == ">1.00E+00 " || $interpretation == "> 1.00E+00")
                {
                    $result = 2;
                }
                else
                {
                    $result = 3;
                }

                $data_array = ['datemodified' => $today, 'datetested' => $dateoftest, 'interpretation' => $interpretation, 'result' => $result];

                $search = ['id' => $data[4], 'worksheet_id' => $worksheet->id];
                DB::table('samples')->where($search)->update($data_array);

                if($data[5] == "NC"){
                    // $worksheet->neg_control_interpretation = $interpretation;
                    $negative_control = $result;
                }
                if($data[5] == "LPC" || $data[5] == "PC"){
                    $positive_control = $result;
                }

            }
            fclose($handle);

            switch ($negative_control) {
                case 'Target Not Detected':
                    $neg_result = 1;
                    break;
                case 'Valid':
                    $neg_result = 6;
                    break;
                case 'Invalid':
                    $neg_result = 7;
                    break;
                case '5':
                    $neg_result = 5;
                    break;                
                default:
                    $neg_result = 3;
                    break;
            }

            if($positive_control == 1 || $positive_control == "1" || $positive_control == ">1" || $positive_control == "> 1 " || $positive_control == "> 1" || $positive_control == "1.00E+00" || $positive_control == ">1.00E+00" || $positive_control == "> 1.00E+00" || $positive_control == "> 1.00E+00 ")
            {
                $pos_result = 2;
            }
            else if($positive_control == "5")
            {
                $pos_result = 5;
            }
            else if($positive_control == "Valid")
            {
                $pos_result = 6;
            }
            else if($positive_control == "Invalid")
            {
                $pos_result = 7;
            }
            else
            {
                $pos_result = 3;
            }

        }

        DB::table('viralsamples')->where(['worksheet_id' => $worksheet->id])->where('run', 0)->update(['run' => 1]);

        $worksheet->neg_control_interpretation = $nc_int;
        $worksheet->neg_control_result = $nc;

        $worksheet->highpos_control_interpretation = $hpc_int;
        $worksheet->highpos_control_result = $hpc;

        $worksheet->lowpos_control_interpretation = $lpc_int;
        $worksheet->lowpos_control_result = $lpc;

        $worksheet->daterun = $dateoftest;
        $worksheet->save();

        $my = new MiscViral;
        $my->requeue($worksheet->id);

        // $path = $request->upload->storeAs('eid_results', 'dash.csv');

        return redirect('viralworksheet/approve/' . $worksheet->id);
    }


    public function approve_results(Viralworksheet $worksheet)
    {
        $worksheet->load(['reviewer', 'creator', 'runner']);

        $results = DB::table('results')->get();
        $actions = DB::table('actions')->get();
        $dilutions = DB::table('viraldilutionfactors')->get();
        $samples = Viralsample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();

        $noresult = $this->checknull($this->get_worksheets(0, $worksheet->id));
        $failed = $this->checknull($this->get_worksheets(3, $worksheet->id));
        $detected = $this->checknull($this->get_worksheets(2, $worksheet->id));
        $undetected = $this->checknull($this->get_worksheets(1, $worksheet->id));

        $total = $detected + $undetected + $failed + $noresult;

        $subtotals = ['detected' => $detected, 'undetected' => $undetected, 'failed' => $failed, 'noresult' => $noresult, 'total' => $total];


        return view('tables.confirm_viral_results', ['results' => $results, 'actions' => $actions, 'dilutions' => $dilutions, 'samples' => $samples, 'subtotals' => $subtotals, 'worksheet' => $worksheet]);
    }

    public function approve(Request $request, Viralworksheet $worksheet)
    {
        $samples = $request->input('samples');
        $batches = $request->input('batches');
        $redraws = $request->input('redraws');
        $actions = $request->input('actions');
        $dilutions = $request->input('dilutiontype');

        $today = date('Y-m-d');
        $approver = auth()->user()->id;

        $batch = array();
        $my = new MiscViral;

        foreach ($samples as $key => $value) {
            $data = [
                'approvedby' => $approver,
                'dateapproved' => $today,
                'repeatt' => $actions[$key],
                'dilutiontype' => $dilutions[$key],
            ];

            if(isset($redraws[$key])) $data['result'] = "Collect New Sample";

            DB::table('viralsamples')->where('id', $samples[$key])->update($data);

            if($actions[$key] == 1){
                $my->save_repeat($samples[$key]);
            }
        }

        $batch = collect($batches);
        $b = $batch->unique();
        $unique = $b->values()->all();

        foreach ($unique as $value) {
            $my->check_batch($value);
        }

        $worksheet->status_id = 3;
        $worksheet->datereviewed = $today;
        $worksheet->reviewedby = $approver;
        $worksheet->save();
        return redirect('/viralworksheet');

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

    public function get_worksheets($result, $worksheet_id=NULL)
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
