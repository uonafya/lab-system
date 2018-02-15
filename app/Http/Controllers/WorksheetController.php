<?php

namespace App\Http\Controllers;

use App\Worksheet;
use App\Sample;
use App\User;
use DB;
use Excel;
use Illuminate\Http\Request;

class WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $state = session()->pull('worksheet_state', null);
        $worksheets = Worksheet::with(['creator'])
        ->when($state, function ($query) use ($state){
            return $query->where('status_id', $state);
        })
        ->get();

        $samples = Sample::selectRaw("count(*) as totals, worksheet_id, result")
            ->whereNotNull('worksheet_id')
            ->whereNotNull('result')
            ->where('inworksheet', 1)
            ->groupBy('worksheet_id', 'result')
            ->get();

        $table_rows = "";

        foreach ($worksheets as $key => $worksheet) {
            $new_key = $key+1;
            $table_rows .= "<tr> <td>{$new_key}</td> <td>" . $worksheet->created_at->toFormattedDateString() . "</td><td> " . $worksheet->creator->full_name . "</td><td>" . $this->mtype($worksheet->machine_type) . "</td><td>";
            $status = $worksheet->status_id;
            $table_rows .= $this->wstatus($status) . "</td><td>";

            if($status == 2 || $status == 3){
                $neg = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 1));
                $pos = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 2));
                $failed = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 3));
                $redraw = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 5));
                $noresult = $this->checknull($samples->where('worksheet_id', $worksheet->id)->where('result', 0));
                $total = $neg + $pos + $failed + $redraw + $noresult;


            }
            else{
                $neg = $pos = $failed = $redraw = $noresult = $total = 0;

                if($status == 1){
                    $noresult = $total = $this->checknull($samples->where('worksheet_id', $worksheet->id));
                }
            }

            $table_rows .= "{$pos}</td><td>{$neg}</td><td>{$failed}</td><td>{$redraw}</td><td>{$noresult}</td><td>{$total}</td><td>" . $worksheet->daterun . "</td><td>" . $worksheet->dateuploaded . "</td><td>" . $worksheet->datereviewed . "</td><td>" . $this->get_links($worksheet->id, $status) . "</td></tr>";

        }

        return view('tables.worksheets', ['rows' => $table_rows]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $samples = Sample::selectRaw("samples.id, patient_id, samples.parentid, batches.datereceived, batches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('samples.id', 'asc')
            ->limit(22)
            ->get();

        $count = $samples->count();

        if($count == 22){
            return view('forms.worksheets', ['create' => true, 'machine_type' => 1]);
        }

        return view('forms.worksheets', ['create' => false, 'machine_type' => 1, 'count' => $count]);
    }

    public function abbot()
    {
        $samples = Sample::selectRaw("samples.id, patient_id, samples.parentid, batches.datereceived, batches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('samples.id', 'asc')
            ->limit(94)
            ->get();

        $count = $samples->count();

        if($count == 94){
            return view('forms.worksheets', ['create' => true, 'machine_type' => 2]);
        }

        return view('forms.worksheets', ['create' => false, 'machine_type' => 2, 'count' => $count]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $worksheet = new Worksheet;
        $worksheet->fill($request->except('_token'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $samples = Sample::selectRaw("samples.id, patient_id, samples.parentid, batches.datereceived, batches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result = 0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('samples.id', 'asc')
            ->when($worksheet, function($query) use ($worksheet){
                if($worksheet->machine_type == 1){
                    return $query->limit(22);
                }
                else{
                    return $query->limit(94);
                }
            });

        $sample_ids = $samples->pluck('id');

        DB::table('samples')->whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id, 'inworksheet' => true]);

        return redirect()->route('worksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Worksheet $worksheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Worksheet $worksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Worksheet $worksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Worksheet $worksheet)
    {
        // DB::table("samples")->where('worksheet_id', $worksheet->id)->update(['worksheet_id' => 0, 'inworksheet' => 0, 'result' => 0]);
        // $worksheet->status_id = 4;
        // $worksheet->save();

        // return redirect("/worksheet");
    }

    public function print(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $samples = Sample::where('worksheet_id', $worksheet->id)->with(['patient'])->get();

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', ['worksheet' => $worksheet, 'samples' => $samples]);
        }
        else{
            return view('worksheets.abbot-table', ['worksheet' => $worksheet, 'samples' => $samples]);
        }
    }

    public function cancel(Worksheet $worksheet)
    {
        DB::table("samples")->where('worksheet_id', $worksheet->id)->update(['worksheet_id' => 0, 'inworksheet' => 0, 'result' => 0]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        return redirect("/worksheet");
    }

    public function upload(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        $users = User::all();
        return view('forms.upload_results', ['worksheet' => $worksheet, 'users' => $users]);
    }




    /**
     * Update the specified resource in storage with results file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function save_results(Request $request, Worksheet $worksheet)
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
                    $interpretation = $value[5];
                    $error = $value[10];

                    switch ($interpretation) {
                        case 'Not Detected':
                            $result = 1;
                            break;
                        case 'HIV-1 Detected':
                            $result = 2;
                            break;
                        case 'Detected':
                            $result = 2;
                            break;
                        case 'Collect New Sample':
                            $result = 5;
                            break;
                        default:
                            $result = 3;
                            $interpretation = $error;
                            break;
                    }

                    $data_array = ['datemodified' => $today, 'datetested' => $today, 'interpretation' => $interpretation, 'result' => $result];
                    $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    DB::table('samples')->where($search)->update($data_array);

                    $check[] = $search;

                    if($sample_id == "HIV_NEG"){
                        $negative_control = $result;
                    }
                    else if($sample_id == "HIV_HIPOS"){
                        $positive_control = $result;
                    }

                }

                if($bool && $value[5] == "RESULT") break;
            }

            if($positive_control == "Passed"){
                $pos_result = 6;
            }
            else{
                $pos_result = 7;
            }

            if($negative_control == "Passed"){
                $neg_result = 6;
            }
            else{
                $neg_result = 7;
            }
        }
        else
        {
            $handle = fopen($file, "r");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $interpretation = $data[8];
                $dateoftest=date("Y-m-d", strtotime($data[3]));

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

        DB::table('samples')->where(['worksheet_id' => $worksheet->id])->whereNull('run')->update(['run' => 1]);

        $worksheet->neg_control_interpretation = $negative_control;
        $worksheet->neg_control_result = $neg_result;

        $worksheet->pos_control_interpretation = $positive_control;
        $worksheet->pos_control_result = $pos_result;
        $worksheet->daterun = $dateoftest;
        $worksheet->save();

        // $path = $request->upload->storeAs('eid_results', 'dash.csv');

        return redirect('worksheet');
    }

    public function mtype($machine)
    {
        if($machine == 1){
            return "<strong> TaqMan </strong>";
        }
        else{
            return " <strong><font color='#0000FF'> Abbott </font></strong> ";
        }
    }

    public function wstatus($status)
    {
        switch ($status) {
            case 1:
                return "<strong><font color='#FFD324'>In-Process</font></strong>";
                break;
            case 2:
                return "<strong><font color='#0000FF'>Tested</font></strong>";
                break;
            case 3:
                return "<strong><font color='#339900'>Approved</font></strong>";
                break;
            case 4:
                return "<strong><font color='#FF0000'>Cancelled</font></strong>";
                break;            
            default:
                break;
        }
    }

    public function get_links($worksheet_id, $status)
    {
        if($status == 1)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to view Samples in this Worksheet' target='_blank'>Details</a> | "
                . "<a href='" . url('worksheet/print/' . $worksheet_id) . "' title='Click to Print this Worksheet' target='_blank'>Print</a> | "
                . "<a href='" . url('worksheet/cancel/' . $worksheet_id) . "' title='Click to Cancel this Worksheet' onClick=\"return confirm('Are you sure you want to Cancel Worksheet {$worksheet_id}\" >Cancel</a> | "
                . "<a href='" . url('worksheet/upload/' . $worksheet_id) . "' title='Click to Upload Results File for this Worksheet'>Update Results</a>";
        }
        else if($status == 2)
        {
            $d = "<a href='" . url('worksheet/approve/' . $worksheet_id) . "' title='Click to Approve Samples Results in worksheet for Rerun or Dispatch' target='_blank'> Approve Worksheet Results</a>";

        }
        else if($status == 3)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to view Samples in this Worksheet' target='_blank'>Details</a> | "
                . "<a href='" . url('worksheet/approve/' . $worksheet_id) . "' title='Click to View Approved Results & Action for Samples in this Worksheet' target='_blank'>View Results</a> | "
                . "<a href='" . url('worksheet/print/' . $worksheet_id) . "' title='Click to Print this Worksheet' target='_blank'>Print</a> | ";

        }
        else if($status == 4)
        {
            $d = "<a href='" . url('worksheet/' . $worksheet_id) . "' title='Click to View Cancelled Worksheet Details' target='_blank'>Details</a> | ";

        }
        return $d;
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
