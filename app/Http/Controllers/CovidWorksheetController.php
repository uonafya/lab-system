<?php

namespace App\Http\Controllers;

use App\CovidWorksheet;
use App\CovidSample;
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

        $worksheet_ids = $worksheets->pluck(['id'])->toArray();
        $samples = $this->get_worksheets($worksheet_ids);
        $reruns = $this->get_reruns($worksheet_ids);
        $data = Lookup::worksheet_lookups();

        $worksheets->transform(function($worksheet, $key) use ($samples, $reruns, $data){
            $status = $worksheet->status_id;
            $total = $worksheet->sample_count;

            if(($status == 2 || $status == 3) && $samples){
                $neg = $samples->where('worksheet_id', $worksheet->id)->where('result', 1)->first()->totals ?? 0;
                $pos = $samples->where('worksheet_id', $worksheet->id)->where('result', 2)->first()->totals ?? 0;
                $failed = $samples->where('worksheet_id', $worksheet->id)->where('result', 3)->first()->totals ?? 0;
                $redraw = $samples->where('worksheet_id', $worksheet->id)->where('result', 5)->first()->totals ?? 0;
                $noresult = $samples->where('worksheet_id', $worksheet->id)->where('result', 0)->first()->totals ?? 0;

                $rerun = $reruns->where('worksheet_id', $worksheet->id)->first()->totals ?? 0;
            }
            else{
                $neg = $pos = $failed = $redraw = $noresult = $rerun = 0;

                if($status == 1){
                    $noresult = $worksheet->sample_count;
                    $rerun = $reruns->where('worksheet_id', $worksheet->id)->first()->totals ?? 0;
                }
            }
            $worksheet->rerun = $rerun;
            $worksheet->neg = $neg;
            $worksheet->pos = $pos;
            $worksheet->failed = $failed;
            $worksheet->redraw = $redraw;
            $worksheet->noresult = $noresult;
            // $worksheet->mylinks = $this->get_links($worksheet->id, $status, $worksheet->datereviewed);
            $worksheet->machine = $data['machines']->where('id', $worksheet->machine_type)->first()->output ?? '';
            $worksheet->status = $data['worksheet_statuses']->where('id', $status)->first()->output ?? '';

            return $worksheet;
        });

        $data = Lookup::worksheet_lookups();
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
        $data['users'] = User::whereIn('user_type_id', [1, 4])->where('email', '!=', 'rufus.nyaga@ken.aphl.org')
            ->whereRaw(" id IN 
                (SELECT DISTINCT received_by FROM covid_samples WHERE site_entry != 2 AND receivedstatus = 1 and result IS NULL AND worksheet_id IS NULL AND datedispatched IS NULL AND parentid=0 )
                ")
            ->withTrashed()
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
        // $worksheet->save();

        $vars = $request->only(['machine_type', 'sampletype', 'limit', 'entered_by']);
        extract($vars);

        $data = MiscCovid::get_worksheet_samples($worksheet->machine_type, $request->input('limit'));

        if($worksheet->combined){
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
        if($covidWorksheet->combined) $samples->merge($covidWorksheet->other_samples());

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



    public function upload(CovidWorksheet $worksheet)
    {
        if(!in_array($worksheet->status_id, [1, 4])){
            session(['toast_error' => 1, 'toast_message' => 'You cannot update results for this worksheet.']);
            return back();
        }
        $worksheet->load(['creator']);
        $users = User::whereIn('user_type_id', [1, 4])->where('email', '!=', 'rufus.nyaga@ken.aphl.org')->get();
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
        $today = $datetested = date("Y-m-d");
        $positive_control = $negative_control = null;

        $sample_array = $doubles = [];

        if($worksheet->machine_type == 2)
        {
            $date_tested = $request->input('daterun');
            $datetested = MiscCovid::worksheet_date($date_tested, $worksheet->created_at);

            // config(['excel.import.heading' => false]);
            $data = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();

            $check = array();

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


                    Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                    $data_array = Misc::sample_result($interpretation, $error);

                    if($sample_id == "HIV_NEG") $negative_control = $data_array;
                    if($sample_id == "HIV_HIPOS") $positive_control = $data_array;

                    $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $today]);
                    // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    // Sample::where($search)->update($data_array);

                    $sample_id = (int) $sample_id;
                    $sample = Sample::find($sample_id);
                    if(!$sample) continue;

                    $sample->fill($data_array);
                    if($cancelled) $sample->worksheet_id = $worksheet->id;
                    else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;

                    $sample->save();
                }

                if($bool && $value[5] == "RESULT") break;
            }
        }
        // C8800
        else if($worksheet->machine_type == 3){
            $handle = fopen($file, "r");
            while (($value = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                if(!isset($value[1])) break;
                $sample_id = $value[1];
                $interpretation = $value[6];
                $result_array = MiscViral::sample_result($interpretation);

                Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                if(!is_numeric($sample_id)){
                    $control = $value[4];
                    if($control == 'HxV H (+) C'){
                        $hpc = $result_array['result'];
                        $hpc_int = $result_array['interpretation'];
                        $hpc_units = $result_array['units'];                        
                    }
                    else if($control == 'HxV L (+) C'){
                        $lpc = $result_array['result'];
                        $lpc_int = $result_array['interpretation'];
                        $lpc_units = $result_array['units'];
                    }
                    else if($control == '(-) C'){
                        $nc = $result_array['result'];
                        $nc_int = $result_array['interpretation']; 
                        $nc_units = $result_array['units']; 
                    }
                }

                $datetested = $today;

                try {
                    $dt = Carbon::parse($value[12]);
                    $date_tested = $dt->toDateString();                    
                    $datetested = MiscViral::worksheet_date($date_tested, $worksheet->created_at);
                } catch (Exception $e) {
                    $datetested = $today;
                }

                $data_array = array_merge(['datemodified' => $today, 'datetested' => $datetested], $result_array);


                $sample_id = (int) $sample_id;
                $sample = CovidSample::find($sample_id);
                if(!$sample) continue;

                $sample->fill($data_array);
                if($cancelled) $sample->worksheet_id = $worksheet->id;
                else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;
                $sample->save();
            }
        }
        else
        {
            $handle = fopen($file, "r");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
            {
                $interpretation = rtrim($data[8]);
                $control = rtrim($data[5]);

                $error = $data[10];

                $date_tested=date("Y-m-d", strtotime($data[3]));

                $datetested = Misc::worksheet_date($date_tested, $worksheet->created_at);

                $data_array = Misc::sample_result($interpretation, $error);

                if($control == "NC") $negative_control = $data_array;

                if($control == "LPC" || $control == "PC") $positive_control = $data_array;

                $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $datetested]);

                $sample_id = (int) trim($data[4]);  

                Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                // $sample_id = substr($sample_id, 0, -1);
                $sample = Sample::find($sample_id);
                if(!$sample) continue;

                $sample->fill($data_array);
                if($cancelled) $sample->worksheet_id = $worksheet->id;
                else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;
                    
                $sample->save();

            }
            fclose($handle);
        }

        if($doubles){
            session(['toast_error' => 1, 'toast_message' => "Worksheet {$worksheet->id} upload contains duplicate rows. Please fix and then upload again."]);
            $file = "Samples_Appearing_More_Than_Once_In_Worksheet_" . $worksheet->id;
        
            Excel::create($file, function($excel) use($doubles){
                $excel->sheet('Sheetname', function($sheet) use($doubles) {
                    $sheet->fromArray($doubles);
                });
            })->download('csv');
        }

        // $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Sample::where(['worksheet_id' => $worksheet->id, 'run' => 0])->update(['run' => 1]);
        Sample::where(['worksheet_id' => $worksheet->id])->whereNull('repeatt')->update(['repeatt' => 0]);
        Sample::where(['worksheet_id' => $worksheet->id])->whereNull('result')->update(['repeatt' => 1]);

        $worksheet->neg_control_interpretation = $negative_control['interpretation'];
        $worksheet->neg_control_result = $negative_control['result'];

        $worksheet->pos_control_interpretation = $positive_control['interpretation'];
        $worksheet->pos_control_result = $positive_control['result'];
        $worksheet->daterun = $datetested;
        $worksheet->uploadedby = auth()->user()->id;
        $worksheet->save();

        Misc::requeue($worksheet->id, $worksheet->daterun);
        session(['toast_message' => "The worksheet has been updated with the results."]);

        return redirect('worksheet/approve/' . $worksheet->id);
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

    public function checknull($var)
    {
        return $var->first()->totals ?? 0;
    }
}
