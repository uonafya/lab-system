<?php

namespace App\Http\Controllers;

use App\CovidWorksheet;
use App\CovidSample;
use App\Lookup;
use App\MiscCovid;
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

        return view('tables.worksheets', $data)->with('pageTitle', 'Worksheets');        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($machine_type=2, $limit=null)
    {
        $data = MiscCovid::get_worksheet_samples($machine_type, $limit);
        if(!$data){
            session(['toast_message' => 'An error has occurred.', 'toast_error' => 1]);
            return back();
        }
        // dd($data);
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
        $worksheet->fill($request->except('_token', 'limit'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $data = MiscCovid::get_worksheet_samples($worksheet->machine_type, $request->input('limit'));

        if(!$data || !$data['create']){
            $worksheet->delete();
            session(['toast_message' => "The worksheet could not be created.", 'toast_error' => 1]);
            return back();            
        }
        $samples = $data['samples'];
        $sample_ids = $samples->pluck('id');

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

        $data = ['worksheet' => $covidWorksheet, 'samples' => $samples, 'i' => 0, 'covid' => true];

        if($print) $data['print'] = true;

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Worksheets');
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
            session(['toast_message' => 'The worksheet is not eligible to be cancelled.']);
            session(['toast_error' => 1]);
            return back();
        }
        $worksheet->sample()->update(['worksheet_id' => null, 'result' => null]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        session(['toast_message' => 'The worksheet has been cancelled.']);
        return redirect("/covid_worksheet");
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
