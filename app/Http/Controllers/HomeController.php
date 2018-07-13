<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Sample;
use App\SampleView;
use App\Viralsample;
use App\ViralsampleView;
use App\Synch;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chart = $this->getHomeGraph();
        $week_chart = $this->getHomeGraph('week');
        $month_chart = $this->getHomeGraph('month');
        
        return view('home', ['chart'=>$chart, 'week_chart' => $week_chart, 'month_chart' => $month_chart])->with('pageTitle', 'Home');
    }

    public function getHomeGraph($period = 'day')
    {
        $chart = [];
        $count = 0;
        $data = ['Entered Samples' => self::__getEnteredSamples($period),
                'Received Samples' => self::__getReceivedSamples($period),
                'Tested Samples' => self::__getTestedSamples($period),
                'Dispatched Samples' => self::__getDispatchedSamples($period),
                'Rejected Samples' => self::__getRejectedSamples($period),
            ];

        $chart['series']['name'] = 'Samples Progress';
        foreach ($data as $key => $value) {
            $chart['categories'][$count] = $key;
            $chart['series']['data'][$count] = $value;
            $count++;
        }
        return $chart;
    }

    public function overdue($level = 'testing')
    {
        if (session('testingSystem') == 'Viralload') {
            $model = ViralsampleView::selectRaw('viralsamples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, viralsampletype.name as sampletype, datediff(curdate(), datereceived) as waitingtime')
                    ->join('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                    ->join('receivedstatus', 'receivedstatus.id', '=', 'viralsamples_view.receivedstatus')
                    ->join('viralsampletype', 'viralsampletype.id', '=', 'viralsamples_view.sampletype');
        } else {
            $model = SampleView::selectRaw('samples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                    ->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
                    ->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus');
        }
        $year = Date('Y')-2;

        if ($level == 'testing') {
            $model = $model->whereNull('worksheet_id')->whereIn('receivedstatus', [1, 3])->whereRaw("(result is null or result=0)");
        } else {
            $model = $model->whereNotNull('worksheet_id')->whereNull('datedispatched');
        }

        $samples = $model->where('repeatt', 0)
                        ->whereYear('datereceived', '>', $year)
                        ->whereRaw("datediff(curdate(), datereceived) > 14")
                        ->get();

        $noSamples = $samples->count();
        $pageTitle = "Samples overdue for $level [$noSamples]";
        // dd($samples);
        return view('tables.pending', compact('samples'))->with('pageTitle', $pageTitle);
        dd($samples);
    }

    public function pending($type = 'samples', $sampletypes = null) {
        if (session('testingSystem') == 'Viralload') {
            $samples = ViralsampleView::selectRaw('viralsamples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, viralsampletype.name as sampletype, datediff(curdate(), datereceived) as waitingtime')
                    ->join('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                    ->join('receivedstatus', 'receivedstatus.id', '=', 'viralsamples_view.receivedstatus')
                    ->join('viralsampletype', 'viralsampletype.id', '=', 'viralsamples_view.sampletype')
                    ->whereIn('receivedstatus', [1, 3])
                    ->when($sampletypes, function($query) use ($sampletypes){
                        if ($sampletypes == 'plasma') {
                            return $query->where('viralsamples_view.sampletype', '=', 1);
                        } else if ($sampletypes == 'EDTA') {
                            return $query->where('viralsamples_view.sampletype', '=', 2);
                        } else if ($sampletypes == 'DBS') {
                            return $query->whereBetween('viralsamples_view.sampletype', [3, 4]);
                        } else {
                            return $query->whereBetween('viralsamples_view.sampletype', [1, 4]);
                        }
                    })
                    ->whereNull('worksheet_id')
                    ->where('datereceived', '>', '2016-12-31')
                    ->whereRaw("(result is null or result = '0')")
                    ->where('input_complete', '1')
                    ->where('viralsamples_view.flag', '1')->get();
        } else {
            $samples = SampleView::selectRaw('samples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                    ->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
                    ->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
                    ->whereNull('worksheet_id')
                    ->where('datereceived', '>', '2014-12-31')
                    ->whereIn('receivedstatus', [1, 3])
                    ->whereRaw("(result is null or result = '0')")
                    ->where('input_complete', '1')
                    ->where('flag', '1')->get();
        }
        $noSamples = $samples->count();
        $pageTitle = "Samples awaiting testing [$noSamples]";
        // dd($samples);
        return view('tables.pending', compact('samples'))->with('pageTitle', $pageTitle);
    }

    public function repeat()
    {
        if(session('testingSystem') == 'Viralload') {
            $samples = ViralsampleView::selectRaw('viralsamples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                        ->join('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                        ->join('receivedstatus', 'receivedstatus.id', '=', 'viralsamples_view.receivedstatus')
                        ->whereBetween('sampletype', [1, 5])
                        // ->where('receivedstatus', 3)
                        ->whereNull('worksheet_id')
                        ->whereYear('datereceived', '>', '2015')
                        ->where('parentid', '>', 0)
                        ->whereRaw("(result is null or result = '0')")
                        ->where('input_complete', '=', '1')
                        ->where('flag', '=', '1')->get();
        } else {
            $samples = SampleView::selectRaw('samples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                        ->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
                        ->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
                        ->whereNull('worksheet_id')
                        // ->where('receivedstatus', 3)
                        ->where(function ($query) {
                            $query->whereNull('result')
                                  ->orWhere('result', '=', 0);
                        })
                        // ->where(DB::raw(('samples.result is null or samples.result = 0')))
                        ->where('flag', '=', '1')
                        ->where('parentid', '>', '0')->get();
        }
        $noSamples = $samples->count();
        $pageTitle = "Samples for Repeat [$noSamples]";

        return view('tables.pending', compact('samples'))->with('pageTitle', $pageTitle);
    }

    public function rejected()
    {
        $year = Date('Y')-3;
        if (session('testingSystem') == 'Viralload') {
            $samples = ViralsampleView::selectRaw('viralsamples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                        ->join('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                        ->join('receivedstatus', 'receivedstatus.id', '=', 'viralsamples_view.receivedstatus')
                        ->where('receivedstatus', 2)
                        ->where('flag', '=', 1)
                        ->whereYear('datereceived', '>', $year)
                        ->whereNotNull('datereceived')
                        ->whereNull('datedispatched')->get();
        } else {
            $samples = SampleView::selectRaw('samples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                        ->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
                        ->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
                        ->where('receivedstatus', 2)
                        ->whereYear('datereceived', '>', $year)
                        ->whereNotNull('datereceived')
                        ->whereNull('datedispatched')->get();
        }
        $noSamples = $samples->count();
        $pageTitle = "Rejected Samples for Dispatch [$noSamples]";

        return view('tables.pending', compact('samples'))->with('pageTitle', $pageTitle);
    }

    static function __getEnteredSamples($period = 'day') 
    {
        $param = self::starting_day($period);
        if (session('testingSystem') == 'Viralload') {
            return Viralsample::selectRaw("count(*) as total")
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('created_at', '>', $param);
                return $query->whereDate('created_at', $param);
            })->get()->first()->total;
        } else {
            return Sample::selectRaw("count(*) as total")
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('created_at', '>', $param);
                return $query->whereDate('created_at', $param);
            })->get()->first()->total;
        }
    }

    static function __getReceivedSamples($period = 'day')
    {
        $param = self::starting_day($period);
        if (session('testingSystem') == 'Viralload') {
            return ViralsampleView::selectRaw("count(*) as total")
            ->where('repeatt', 0)
            ->whereIn('receivedstatus', [1, 3])
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('datereceived', '>', $param);
                return $query->whereDate('datereceived', $param);
            })->get()->first()->total;
        } else {
            return SampleView::selectRaw("count(*) as total")
            ->where('repeatt', 0)
            ->whereIn('receivedstatus', [1, 3])
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('datereceived', '>', $param);
                return $query->whereDate('datereceived', $param);
            })->get()->first()->total;
        }
    }

    static function __getRejectedSamples($period = 'day')
    {
        $param = self::starting_day($period);
        if (session('testingSystem') == 'Viralload') {
            return ViralsampleView::selectRaw("count(*) as total")
            ->where('receivedstatus', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('datereceived', '>', $param);
                return $query->whereDate('datereceived', $param);
            })->get()->first()->total;
        } else {
            return SampleView::selectRaw("count(*) as total")
            ->where('receivedstatus', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('datereceived', '>', $param);
                return $query->whereDate('datereceived', $param);
            })->get()->first()->total;
        }
    }

    static function __getTestedSamples($period = 'day')
    {
        $param = self::starting_day($period);
        if (session('testingSystem') == 'Viralload') {
            return Viralsample::selectRaw("count(*) as total")
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('datetested', '>', $param);
                return $query->whereDate('datetested', $param);
            })->get()->first()->total;
        } else {
            return Sample::selectRaw("count(*) as total")
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('datetested', '>', $param);
                return $query->whereDate('datetested', $param);
            })->get()->first()->total;
        }
    }

    static function __getDispatchedSamples($period = 'day')
    {
        $param = self::starting_day($period);

        if (session('testingSystem') == 'Viralload') {
            return ViralsampleView::selectRaw("count(*) as total")
            ->where('repeatt', 0)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('datedispatched', '>', $param);
                return $query->whereDate('datedispatched', $param);
            })->get()->first()->total;
        } else {
            return SampleView::selectRaw("count(*) as total")
            ->where('repeatt', 0)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->whereDate('datedispatched', '>', $param);
                return $query->whereDate('datedispatched', $param);
            })->get()->first()->total;
        }
    }

    public static function starting_day($period)
    {
        if($period == 'day') $param = date('Y-m-d');
        else if($period == 'month'){
            $days = Carbon::now()->day;
            $param = Carbon::now()->subDays($days)->toDateString();
        }
        else{
            $days = Carbon::now()->dayOfWeek;
            $param = Carbon::now()->subDays($days)->toDateString();
        }
        return $param;
    }

    public function countysearch(Request $request)
    {
        $search = $request->input('search');
        $county = DB::table('countys')->select('id', 'name', 'letter as facilitycode')
            ->whereRaw("(name like '%" . $search . "%')")
            ->paginate(10);
        return $county;
    }

    public function download($type = 'EID')
    {
        if ($type == 'VL') {
            $filename = 'VL_REQUISITION_FORM.pdf';
        } elseif ($type == 'EID') {
            $filename = 'EID_REQUISITION_FORM.pdf';
        } elseif($type == 'POC') {
            $filename = 'POC_USERGUIDE.pdf';
        }
        $path = storage_path('app/downloads/' . $filename);

        return response()->download($path);
    }

    public function test()
    {
        // dd(Synch::synch_eid_patients());
        // echo Synch::synch_eid_patients();
        echo Synch::synch_eid_batches();
    }


}