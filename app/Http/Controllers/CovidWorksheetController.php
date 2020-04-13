<?php

namespace App\Http\Controllers;

use App\CovidWorksheet;
use App\CovidSample;
use App\CovidSampleView;
use App\Lookup;
use App\MiscCovid;
use App\Misc;
use App\MiscViral;
use App\User;
use App\Sample;
use App\Viralsample;

use Carbon\Carbon;
use Excel;

use Illuminate\Http\Request;

class CovidWorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state=0, $date_start=NULL, $date_end=NULL, $worksheet_id=NULL)
    {
        $worksheets = CovidWorksheet::with(['creator'])->withCount(['sample'])
        ->when($worksheet_id, function ($query) use ($worksheet_id){
            return $query->where('id', $worksheet_id);
        })
        ->when($state, function ($query) use ($state){
            if($state == 1 || $state == 12) $query->orderBy('id', 'asc');
            if($state == 12){
                return $query->where('status_id', 1)->whereRaw("id in (
                    SELECT DISTINCT worksheet_id
                    FROM samples_view
                    WHERE parentid > 0 AND site_entry != 2
                )");
            }
            return $query->where('status_id', $state);
        })
        ->when($date_start, function($query) use ($date_start, $date_end){
            if($date_end)
            {
                return $query->whereDate('created_at', '>=', $date_start)
                ->whereDate('created_at', '<=', $date_end);
            }
            return $query->whereDate('created_at', $date_start);
        })
        ->orderBy('created_at', 'desc')
        ->paginate();

        $worksheets->setPath(url()->current());

        $data = Lookup::worksheet_lookups();

        $worksheet_ids = $worksheets->pluck(['id'])->toArray();
        $samples = $this->get_worksheets($worksheet_ids);
        $reruns = $this->get_reruns($worksheet_ids);

        $worksheets->transform(function($worksheet, $key) use ($samples, $reruns, $data){
            $status = $worksheet->status_id;
            $total = $worksheet->sample_count;

            if(($status == 2 || $status == 3) && $samples){
                $neg = $samples->where('worksheet_id', $worksheet->id)->where('result', 1)->first()->totals ?? 0;
                $pos = $samples->where('worksheet_id', $worksheet->id)->where('result', 2)->first()->totals ?? 0;
                $presumed_pos = $samples->where('worksheet_id', $worksheet->id)->where('result', 8)->first()->totals ?? 0;
                $failed = $samples->where('worksheet_id', $worksheet->id)->where('result', 3)->first()->totals ?? 0;
                $redraw = $samples->where('worksheet_id', $worksheet->id)->where('result', 5)->first()->totals ?? 0;
                $noresult = $samples->where('worksheet_id', $worksheet->id)->where('result', 0)->first()->totals ?? 0;

                $rerun = $reruns->where('worksheet_id', $worksheet->id)->first()->totals ?? 0;
            }
            else{
                $neg = $pos = $failed = $redraw = $noresult = $presumed_pos = $rerun = 0;

                if($status == 1){
                    $noresult = $worksheet->sample_count;
                    $rerun = $reruns->where('worksheet_id', $worksheet->id)->first()->totals ?? 0;
                }
            }
            $worksheet->rerun = $rerun;
            $worksheet->neg = $neg;
            $worksheet->pos = $pos;
            $worksheet->presumed_pos = $presumed_pos;
            $worksheet->failed_samples = $failed;
            $worksheet->redraw = $redraw;
            $worksheet->noresult = $noresult;
            // $worksheet->mylinks = $this->get_links($worksheet->id, $status, $worksheet->datereviewed);
            $worksheet->machine = $data['machines']->where('id', $worksheet->machine_type)->first()->output ?? '';
            $worksheet->status = $data['worksheet_statuses']->where('id', $status)->first()->output ?? '';

            return $worksheet;
        });
        
        $data['status_count'] = CovidWorksheet::selectRaw("count(*) AS total, status_id, machine_type")
            ->groupBy('status_id', 'machine_type')
            ->orderBy('status_id', 'asc')
            ->orderBy('machine_type', 'asc')
            ->get();
        $data['worksheets'] = $worksheets;
        $data['myurl'] = url('covid_worksheet/index/' . $state . '/');
        $data['link_extra'] = 'covid_';

        return view('tables.covid_worksheets', $data)->with('pageTitle', 'Worksheets');        
    }

    public function set_details_form()
    {
        $data = Lookup::worksheet_lookups();
        $data['users'] = User::withTrashed()
            ->whereRaw(" id IN 
                (SELECT DISTINCT received_by FROM covid_samples WHERE site_entry != 2 AND receivedstatus = 1 and result IS NULL AND worksheet_id IS NULL AND datedispatched IS NULL AND parentid=0 )
                ")
            // ->labUser()
            ->get();

        return view('forms.set_covidworksheet', $data)->with('pageTitle', 'Set Worksheet Details');
    }


    public function set_details(Request $request)
    {
        $combined = $request->input('combined');
        $machine_type = $request->input('machine_type');
        $limit = $request->input('limit', 0);
        $entered_by = $request->input('entered_by');
        $sampletype = $request->input('sampletype');
        // return redirect("/viralworksheet/create/{$sampletype}/{$machine_type}/{$calibration}/{$limit}/{$entered_by}");

        return $this->create($machine_type, $limit, $combined, $entered_by, $sampletype);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($machine_type, $limit, $combined=0, $entered_by=null, $sampletype=null)
    {
        $data = MiscCovid::get_worksheet_samples($machine_type, $limit, $entered_by);
        if(!$data){
            session(['toast_message' => 'An error has occurred.', 'toast_error' => 1]);
            return back();
        }
        if(!$data['count']){
            session(['toast_message' => 'There are no covid samples for testing.', 'toast_error' => 1]);
            return back();            
        }
        if($combined){
            $new_limit = $limit - $data['count'];
            if($combined == 1){
                $new_data = Misc::get_worksheet_samples($machine_type, $new_limit);
            }else{
                $new_data = MiscViral::get_worksheet_samples($machine_type, false, $sampletype, $new_limit, $entered_by);                
            }
            if($new_data && $new_data['count']){
                $data['count'] += $new_data['count'];
                if($data['count'] == $limit) $data['create'] = true;
                // $data['samples'] = array_merge($data['samples'], $new_data['samples']);
                $data['samples'] = $data['samples']->merge($new_data['samples']);
                $data['sampletype'] = $sampletype;
            }
        }
        $data['combined'] = $combined;
        $data['entered_by'] = $entered_by;
        $data['sampletype'] = $sampletype;
        return view('forms.worksheets', $data)->with('pageTitle', 'Create Worksheet');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $worksheet = new CovidWorksheet;
        $worksheet->fill($request->except(['_token', 'limit', 'entered_by', 'sampletype']));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $vars = $request->only(['machine_type', 'sampletype', 'limit', 'entered_by']);
        extract($vars);

        $data = MiscCovid::get_worksheet_samples($worksheet->machine_type, $request->input('limit'), $request->input('entered_by'));

        if($worksheet->combined && !$data['create']){
            $new_limit = $limit - $data['count'];
            if($worksheet->combined == 1){
                $new_data = Misc::get_worksheet_samples($machine_type, $new_limit);
                $class = Sample::class;
            }else{
                $new_data = MiscViral::get_worksheet_samples($machine_type, false, $sampletype, $new_limit, $request->input('entered_by'));  
                $class = Viralsample::class;              
            }
            if(!$new_data || !$new_data['create']){
                $worksheet->delete();
                session(['toast_message' => "The worksheet could not be created.", 'toast_error' => 1]);
                return redirect('covid_worksheet');            
            }

            $new_samples = $new_data['samples'];
            $new_sample_ids = $new_samples->pluck('id')->toArray();
            $class::whereIn('id', $new_sample_ids)
                ->whereNull('worksheet_id')->whereNull('result')
                ->update(['worksheet_id' => $worksheet->id, 'updated_at' => now()]);
        }else{
            if(!$data || !$data['create']){
                $worksheet->delete();
                session(['toast_message' => "The worksheet could not be created.", 'toast_error' => 1]);
                return redirect('covid_worksheet');            
            }
        }
        
        $samples = $data['samples'];
        $sample_ids = $samples->pluck('id')->toArray();
        CovidSample::whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id]);

        return redirect()->route('covid_worksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CovidWorksheet  $covidWorksheet
     * @return \Illuminate\Http\Response
     */
    public function show(CovidWorksheet $covidWorksheet, $print=false)
    {
        $samples = $covidWorksheet->sample()->orderBy('run', 'desc')->orderBy('id', 'asc')->get();
        if($covidWorksheet->combined) $samples = $samples->merge($covidWorksheet->other_samples());

        $data = ['worksheet' => $covidWorksheet, 'samples' => $samples, 'i' => 0, 'covid' => true];

        if($print) $data['print'] = true;

        if($covidWorksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Other Worksheets');
        }
        else if($covidWorksheet->machine_type == 3){
            return view('worksheets.c-8800', $data)->with('pageTitle', 'C8800 Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Abbot Worksheets');
        }
    }

    public function find(CovidWorksheet $worksheet)
    {
        session(['toast_message' => 'Found 1 worksheet.']);
        return $this->index(0, null, null, $worksheet->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CovidWorksheet  $covidWorksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(CovidWorksheet $covidWorksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CovidWorksheet  $covidWorksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CovidWorksheet $covidWorksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CovidWorksheet  $covidWorksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(CovidWorksheet $covidWorksheet)
    {
        //
    }

    public function labels(CovidWorksheet $worksheet)
    {
        $samples = $worksheet->sample;
        return view('worksheets.labels', ['samples' => $samples]);
    }

    public function print(CovidWorksheet $worksheet)
    {
        return $this->show($worksheet, true);
    }

    public function cancel(CovidWorksheet $worksheet)
    {
        if($worksheet->status_id != 1){
            session(['toast_error' => 1, 'toast_message' => 'The worksheet is not eligible to be cancelled.']);
            return back();
        }
        $worksheet->sample()->update(['worksheet_id' => null, 'result' => null]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        if($worksheet->combined){
            $worksheet->update_other_samples(['worksheet_id' => null, 'result' => null]);
        }

        session(['toast_message' => 'The worksheet has been cancelled.']);
        return redirect("/covid_worksheet");
    }

    public function cancel_upload(CovidWorksheet $worksheet)
    {
        if($worksheet->status_id != 2){
            session(['toast_message' => 'The upload for this worksheet cannot be reversed.']);
            session(['toast_error' => 1]);
            return back();
        }

        if($worksheet->uploadedby != auth()->user()->id && auth()->user()->user_type_id != 0){
            session(['toast_message' => 'Only the user who uploaded the results can reverse the upload.']);
            session(['toast_error' => 1]);
            return back();
        }

        $samples = CovidSample::where(['repeatt' => 1, 'worksheet_id' => $worksheet->id])->get();

        foreach ($samples as $sample) {
            $sample->remove_rerun();
        }

        CovidSample::where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->update(['result' => null, 'interpretation' => null, 'datetested' => null, 'repeatt' => 0, 'dateapproved' => null, 'approvedby' => null]);
        $worksheet->status_id = 1;
        $worksheet->neg_control_interpretation = $worksheet->pos_control_interpretation = $worksheet->neg_control_result = $worksheet->pos_control_result = $worksheet->daterun = $worksheet->dateuploaded = $worksheet->uploadedby = $worksheet->datereviewed = $worksheet->reviewedby = $worksheet->datereviewed2 = $worksheet->reviewedby2 = null;
        $worksheet->save();

        session(['toast_message' => 'The upload has been reversed.']);
        return redirect($worksheet->route_name . "/upload/" . $worksheet->id);
    }



    public function upload(CovidWorksheet $worksheet)
    {
        if(!in_array($worksheet->status_id, [1, 4])){
            session(['toast_error' => 1, 'toast_message' => 'You cannot update results for this worksheet.']);
            return back();
        }
        $worksheet->load(['creator']);
        $users = User::labUser()->get();
        return view('forms.upload_results', ['worksheet' => $worksheet, 'users' => $users])->with('pageTitle', 'Worksheet Upload');
    }

    public function save_results(Request $request, CovidWorksheet $worksheet)
    {
        if(!in_array($worksheet->status_id, [1, 4])){
            session(['toast_error' => 1, 'toast_message' => 'You cannot update results for this worksheet.']);
            return back();
        }

        $cancelled = false;
        if($worksheet->status_id == 4) $cancelled =  true;

        $worksheet->fill($request->except(['_token', 'upload']));
        $file = $request->upload->path();
        $path = $request->upload->store('public/results/covid'); 
        $today = $datemodified = $datetested = date("Y-m-d");
        $positive_control = $negative_control = null;

        $sample_array = $doubles = [];

        // C8800
        if($worksheet->machine_type == 3){
            $handle = fopen($file, "r");
            while (($value = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                if(!isset($value[1])) break;
                if($value[0] == 'Test') continue;
                $sample_id = $value[1];

                // $interpretation = $value[6];

                $target1 = $value[6];
                $target2 = $value[7];
                $flag = $value[3];

                $result_array = MiscCovid::sample_result($target1, $target2, $flag);

                if(!is_numeric($sample_id)){
                    $control = $value[4];
                    if(str_contains($control, ['+'])){
                        $positive_control = $result_array;                       
                    }else{
                        $negative_control = $result_array; 
                    }
                    continue;
                }

                $data_array = array_merge(compact('datetested'), $result_array);

                $sample_id = (int) $sample_id;
                $sample = CovidSample::find($sample_id);
                if(!$sample) continue;

                $sample->fill($data_array);
                if($cancelled) $sample->worksheet_id = $worksheet->id;
                else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;
                $sample->save();
            }
        }else{
            session(['toast_error' => 1, 'toast_message' => 'The worksheet type is not supported.']);
            return back();
        }


        /*if($doubles){
            session(['toast_error' => 1, 'toast_message' => "Worksheet {$worksheet->id} upload contains duplicate rows. Please fix and then upload again."]);
            $file = "Samples_Appearing_More_Than_Once_In_Worksheet_" . $worksheet->id;
        
            Excel::create($file, function($excel) use($doubles){
                $excel->sheet('Sheetname', function($sheet) use($doubles) {
                    $sheet->fromArray($doubles);
                });
            })->download('csv');
        }*/

        CovidSample::where(['worksheet_id' => $worksheet->id])->whereNull('result')->update(['repeatt' => 1]);

        $worksheet->neg_control_interpretation = $negative_control['interpretation'];
        $worksheet->neg_control_result = $negative_control['result'];

        $worksheet->pos_control_interpretation = $positive_control['interpretation'];
        $worksheet->pos_control_result = $positive_control['result'];
        $worksheet->daterun = $datetested;
        $worksheet->uploadedby = auth()->user()->id;
        $worksheet->save();

        session(['toast_message' => "The worksheet has been updated with the results."]);

        return redirect($worksheet->route_name . '/approve/' . $worksheet->id);
    }




    public function approve_results(CovidWorksheet $worksheet)
    {        
        $worksheet->load(['reviewer', 'creator', 'runner', 'sorter', 'bulker']);

        // $samples = Sample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();
        
        $samples = CovidSample::with(['approver', 'final_approver', 'patient'])
                    ->where('worksheet_id', $worksheet->id) 
                    ->where('site_entry', '!=', 2) 
                    ->orderBy('run', 'desc')
                    ->when(true, function($query){
                        if(in_array(env('APP_LAB'), [2])) return $query->orderBy('facility_id')->orderBy('batch_id', 'asc');
                        if(in_array(env('APP_LAB'), [3])) $query->orderBy('datereceived', 'asc');
                    })
                    ->orderBy('id', 'asc')
                    ->get();

        $s = $this->get_worksheets($worksheet->id);

        $neg = $s->where('result', 1)->first()->totals ?? 0;
        $pos = $s->where('result', 2)->first()->totals ?? 0;
        $failed = $s->where('result', 3)->first()->totals ?? 0;
        $redraw = $s->where('result', 5)->first()->totals ?? 0;
        $noresult = $s->where('result', 0)->first()->totals ?? 0;

        $total = $neg + $pos + $failed + $redraw + $noresult;

        $subtotals = ['neg' => $neg, 'pos' => $pos, 'failed' => $failed, 'redraw' => $redraw, 'noresult' => $noresult, 'total' => $total];

        $data = Lookup::worksheet_approve_lookups();
        $data['samples'] = $samples;
        $data['subtotals'] = $subtotals;
        $data['worksheet'] = $worksheet;
        $data['covid'] = true;

        return view('tables.confirm_results', $data)->with('pageTitle', 'Approve Results');
    }

    public function approve(Request $request, CovidWorksheet $worksheet)
    {
        $double_approval = Lookup::$double_approval;
        $actions = $request->input('actions');
        $samples = $request->input('samples');

        $today = date('Y-m-d');
        $approver = auth()->user()->id;

        if(in_array(env('APP_LAB'), $double_approval) && $worksheet->reviewedby == $approver){
            session(['toast_message' => "You are not permitted to do the second approval.", 'toast_error' => 1]);
            return redirect($worksheet->route_name);            
        }

        foreach ($samples as $key => $value) {

            if(in_array(env('APP_LAB'), $double_approval) && $worksheet->reviewedby && !$worksheet->reviewedby2 && $worksheet->reviewedby != $approver){
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
            
            $sample = CovidSample::find($samples[$key]);
            $sample->fill($data);
            if($sample->result == 3 &&  $sample->repeatt == 0){
                $sample->result = 5;
                $sample->labcomment = 'Failed Run';
            }
            $sample->pre_update();

            if($sample->repeatt == 1) MiscCovid::save_repeat($sample->id);
        }

        if(in_array(env('APP_LAB'), $double_approval)){
            if($worksheet->reviewedby && $worksheet->reviewedby != $approver){
                $worksheet->status_id = 3;
                $worksheet->datereviewed2 = $today;
                $worksheet->reviewedby2 = $approver;
                $worksheet->save();
                session(['toast_message' => "The worksheet has been approved."]);
            }
            else{
                $worksheet->datereviewed = $today;
                $worksheet->reviewedby = $approver;
                $worksheet->save();
                session(['toast_message' => "The worksheet has been approved. It is awaiting the second approval before the results can be prepared for dispatch."]);
            }
        }
        else{
            $worksheet->status_id = 3;
            $worksheet->datereviewed = $today;
            $worksheet->reviewedby = $approver;
            $worksheet->save();
            session(['toast_message' => "The worksheet has been approved."]);
        }
        return redirect($worksheet->route_name);    
    }



    public function rerun_worksheet(CovidWorksheet $worksheet)
    {
        if($worksheet->status_id != 2 || !$worksheet->failed){
            session(['toast_error' => 1, 'toast_message' => "The worksheet is not eligible for rerun."]);
            return back();
        }
        $worksheet->status_id = 5;
        $worksheet->save();

        $new_worksheet = $worksheet->replicate(['national_worksheet_id', 'status_id',
            'neg_control_result', 'pos_control_result', 
            'neg_control_interpretation', 'pos_control_interpretation',
            'datecut', 'datereviewed', 'datereviewed2', 'dateuploaded', 'datecancelled', 'daterun',
        ]);
        $new_worksheet->save();

        
        $samples = CovidSample::where(['worksheet_id' => $worksheet->id])
                    ->where('site_entry', '!=', 2) 
                    ->get();

        foreach ($samples as $key => $sample) {
            $sample->repeatt = 1;
            $sample->pre_update();
            $rsample = MiscCovid::save_repeat($sample->id);
            $rsample->worksheet_id = $new_worksheet->id;
            $rsample->save();
        }
        session(['toast_message' => "The worksheet has been marked as failed as is ready for rerun."]);
        return redirect($worksheet->route_name);  
    }



    public function get_worksheets($worksheet_id=NULL)
    {
        if(!$worksheet_id) return false;
        $samples = CovidSample::selectRaw("count(*) as totals, worksheet_id, result")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){                
                if (is_array($worksheet_id)) {
                    return $query->whereIn('worksheet_id', $worksheet_id);
                }
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->where('receivedstatus', '!=', 2)
            ->groupBy('worksheet_id', 'result')
            ->get();

        return $samples;
    }

    public function get_reruns($worksheet_id=NULL)
    {
        if(!$worksheet_id) return false;
        $samples = CovidSample::selectRaw("count(*) as totals, worksheet_id")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){                
                if (is_array($worksheet_id)) {
                    return $query->whereIn('worksheet_id', $worksheet_id);
                }
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->where('parentid', '>', 0)
            ->where('receivedstatus', '!=', 2)
            ->groupBy('worksheet_id')
            ->get();

        return $samples;
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $worksheets = CovidWorksheet::whereRaw("id like '" . $search . "%'")->paginate(10);
        $worksheets->setPath(url()->current());
        return $worksheets;
    }

}
