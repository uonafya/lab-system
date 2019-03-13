<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Sample;
use App\SampleView;
use App\Viralsample;
use App\ViralsampleView;
use App\Synch;
use App\User;
use App\Worksheet;
use App\Viralworksheet;

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
        $user = auth()->user();
        if(auth()->user()->user_type_id == 5){
            session(['toast_message' => 'Please make sure that your contact information is up to date.']);
            return redirect("/facility/{$user->facility_id}/edit");
        }
        else if (auth()->user()->user_type_id == 0 || auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4) {
            self::cacher();
            $chart = $this->getHomeGraph();
            $week_chart = $this->getHomeGraph('week');
            $month_chart = $this->getHomeGraph('month');

            return view('home.home', ['chart'=>$chart, 'week_chart' => $week_chart, 'month_chart' => $month_chart])->with('pageTitle', 'Home');
        } else if(auth()->user()->user_type_id == 2) {
            $data = ['eid_samples' =>[], 'vl_samples' =>[], 'eid_batches' => [], 'vl_batches' => [], 'eid_worksheets' => [], 'vl_worksheets' => []];
            
            $samples = SampleView::selectRaw("IF(site_entry = 1, 'site', 'lab') as `entered_at`, count(*) as `samples_logged`")->whereRaw("DATE(created_at) = CURDATE()")->groupBy()->get();
            if(!$samples->isEmpty()) {
                foreach ($samples as $key => $value) {
                    $data['eid_samples'][$value->entered_at] = $value->samples_logged;
                }
            }

            $vlsamples = ViralsampleView::selectRaw("IF(site_entry = 1, 'site', 'lab') as `entered_at`, count(*) as `samples_logged`")->whereRaw("DATE(created_at) = CURDATE()")->groupBy()->get();
            if(!$vlsamples->isEmpty()) {
                foreach ($vlsamples as $key => $value) {
                    $data['eid_samples'][$value->entered_at] = $value->samples_logged;
                }
            }

            // Batches values
            $eidbatches = SampleView::selectRaw("IF(site_entry = 1, 'site', 'lab') as `entered_at`, count(distinct batch_id) as `totals`")
                            ->whereNotNull('datetested')->whereNull('datedispatched')
                            ->groupBy('entered_at')->get();
            if(!$eidbatches->isEmpty()) {
                foreach ($eidbatches as $key => $value) {
                    $data['eid_batches'][$value->entered_at] = $value->totals;
                }
            }
            $vlbatches = ViralsampleView::selectRaw("IF(site_entry = 1, 'site', 'lab') as `entered_at`, count(distinct batch_id) as `totals`")
                            ->whereNotNull('datetested')->whereNull('datedispatched')
                            ->groupBy('entered_at')->get();
            if(!$vlbatches->isEmpty()) {
                foreach ($vlbatches as $key => $value) {
                    $data['vl_batches'][$value->entered_at] = $value->totals;
                }
            }

            // Worksheet values
            $eidworksheets = Worksheet::selectRaw("(CASE WHEN status_id = 1 THEN 'inprocess' WHEN status_id = 2 THEN 'tested' ELSE 'rest' END) as `types`, count(CASE WHEN status_id = 1 THEN 'inprocess' WHEN status_id = 2 THEN 'tested' ELSE 'rest' END) AS total")->groupBy('types')->get();
            if(!$eidworksheets->isEmpty()) {
                foreach ($eidworksheets as $key => $value) {
                    $data['eid_worksheets'][$value->types] = $value->total;
                }
            }
            $vlworksheets = Viralworksheet::selectRaw("(CASE WHEN status_id = 1 THEN 'inprocess' WHEN status_id = 2 THEN 'tested' ELSE 'rest' END) as `types`, count(CASE WHEN status_id = 1 THEN 'inprocess' WHEN status_id = 2 THEN 'tested' ELSE 'rest' END) AS total")->groupBy('types')->get();
            if(!$vlworksheets->isEmpty()) {
                foreach ($vlworksheets as $key => $value) {
                    $data['vl_worksheets'][$value->types] = $value->total;
                }
            }
            $data = (object)json_decode(json_encode($data));            
            return view('home.admin', compact('data'))->with('pageTitle', 'Home');
        }
    }

    public function getHomeGraph($period = 'day')
    {
        $testingSystem = 'eid';
        if (session('testingSystem') == 'Viralload') 
            $testingSystem = 'vl';
        $chart = [];
        $count = 0;
        $period = strtolower(trim($period));
        $entered = $testingSystem.$period.'entered';
        $received = $testingSystem.$period.'received';
        $tested = $testingSystem.$period.'tested';
        $dispatched = $testingSystem.$period.'dispatched';
        $rejected = $testingSystem.$period.'rejected';
        
        $data = ['Entered Samples' => Cache::get($entered),
                'Received Samples' => Cache::get($received),
                'Tested Samples' => Cache::get($tested),
                'Dispatched Samples' => Cache::get($dispatched),
                'Rejected Samples' => Cache::get($rejected),
            ];

        $chart['series']['name'] = 'Samples Progress';
        foreach ($data as $key => $value) {
            $chart['categories'][$count] = $key;
            $chart['series']['data'][$count] = (int) $value;
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
                        ->where('lab_id', '=', env('APP_LAB'))
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
                    ->whereNull('datedispatched')
                    ->where('datereceived', '>', '2017-12-31')
                    ->whereRaw("(result is null or result = '0')")
                    ->where('input_complete', '1')
                    ->where('lab_id', '=', env('APP_LAB'))
                    ->where('site_entry', '<>', 2)
                    ->where('viralsamples_view.flag', '1')
                    ->orderBy('parentid', 'desc')
                    ->orderBy('waitingtime', 'desc')->get();
        } else {
            $samples = SampleView::selectRaw('samples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                    ->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
                    ->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
                    ->whereNull('worksheet_id')
                    ->whereNull('datedispatched')
                    ->where('datereceived', '>', '2014-12-31')
                    ->whereIn('receivedstatus', [1, 3])
                    ->whereRaw("(result is null or result = '0')")
                    ->where('input_complete', '1')
                    ->where('lab_id', '=', env('APP_LAB'))
                    ->where('site_entry', '<>', 2)
                    ->where('flag', '1')
                    ->orderBy('parentid', 'desc')
                    ->orderBy('waitingtime', 'desc')->get();
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
                        ->where('receivedstatus', '<>', 2)->where('receivedstatus', '<>', 0)
                        ->whereNull('worksheet_id')
                        ->whereYear('datereceived', '>', '2015')
                        ->where('parentid', '>', 0)
                        ->where('lab_id', '=', env('APP_LAB'))
                        ->where('site_entry', '<>', 2)
                        // ->whereRaw("(result is null or result = '0' or result != 'Collect New Sample')")
                        ->whereRaw("(result is null or result = '0')")
                        ->where('input_complete', '=', '1')
                        ->where('flag', '=', '1')->get();
        } else {
            $samples = SampleView::selectRaw('samples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                        ->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
                        ->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
                        ->whereNull('worksheet_id')
                        ->whereYear('datereceived', '>', '2015')
                        ->where('lab_id', '=', env('APP_LAB'))
                        ->where('site_entry', '<>', 2)
                        ->where('receivedstatus', '<>', 2)->where('receivedstatus', '<>', 0)
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
                        ->where('lab_id', '=', env('APP_LAB'))
                        ->where('site_entry', '<>', 2)
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
                        ->where('site_entry', '<>', 2)
                        ->where('lab_id', '=', env('APP_LAB'))
                        ->whereNull('datedispatched')->get();
        }
        $noSamples = $samples->count();
        $pageTitle = "Rejected Samples for Dispatch [$noSamples]";

        return view('tables.pending', compact('samples'))->with('pageTitle', $pageTitle);
    }

    static function cacher() {
        $periods = ['day', 'week', 'month'];

        if (session('testingSystem') == 'Viralload') {
            if(Cache::has('vldayentered'))
                return true;
        } else {
            if(Cache::has('eiddayentered'))
                return true;
        }
        $minutes = 1;

        foreach ($periods as $periodkey => $periodvalue) {
            $testingSystem = 'eid';
            if (session('testingSystem') == 'Viralload') 
                $testingSystem = 'vl';
            Cache::put($testingSystem.$periodvalue."entered", self::__getEnteredSamples($periodvalue), $minutes);
            Cache::put($testingSystem.$periodvalue."received", self::__getReceivedSamples($periodvalue), $minutes);
            Cache::put($testingSystem.$periodvalue."tested", self::__getTestedSamples($periodvalue), $minutes);
            Cache::put($testingSystem.$periodvalue."dispatched", self::__getDispatchedSamples($periodvalue), $minutes);
            Cache::put($testingSystem.$periodvalue."rejected", self::__getRejectedSamples($periodvalue), $minutes);
        }
        
    }

    static function __getEnteredSamples($period = 'day') 
    {
        $param = self::starting_day($period);
        if (session('testingSystem') == 'Viralload') {
            return ViralsampleView::selectRaw("count(id) as total")
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('created_at', '>', $param);
                return $query->where('created_at', $param);
            })->get()->first()->total;
        } else {
            return SampleView::selectRaw("count(id) as total")
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('created_at', '>', $param);
                return $query->where('created_at', $param);
            })->get()->first()->total;
        }
    }

    static function __getReceivedSamples($period = 'day')
    {
        $param = self::starting_day($period);
        if (session('testingSystem') == 'Viralload') {
            return ViralsampleView::selectRaw("count(id) as total")
            ->where('repeatt', 0)
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
            ->whereIn('receivedstatus', [1, 3])
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('datereceived', '>', $param);
                return $query->where('datereceived', $param);
            })->get()->first()->total;
        } else {
            return SampleView::selectRaw("count(id) as total")
            ->where('repeatt', 0)
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
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
            return ViralsampleView::selectRaw("count(id) as total")
            ->where('receivedstatus', 2)
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('datereceived', '>', $param);
                return $query->where('datereceived', $param);
            })->get()->first()->total;
        } else {
            return SampleView::selectRaw("count(id) as total")
            ->where('receivedstatus', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('datereceived', '>', $param);
                return $query->where('datereceived', $param);
            })->get()->first()->total;
        }
    }

    static function __getTestedSamples($period = 'day')
    {
        $param = self::starting_day($period);
        if (session('testingSystem') == 'Viralload') {
            return ViralsampleView::selectRaw("count(id) as total")
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('datetested', '>', $param);
                return $query->where('datetested', $param);
            })->get()->first()->total;
        } else {
            return SampleView::selectRaw("count(id) as total")
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('datetested', '>', $param);
                return $query->where('datetested', $param);
            })->get()->first()->total;
        }
    }

    static function __getDispatchedSamples($period = 'day')
    {
        $param = self::starting_day($period);

        if (session('testingSystem') == 'Viralload') {
            return ViralsampleView::selectRaw("count(id) as total")
            ->where('repeatt', 0)
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('datedispatched', '>', $param);
                return $query->where('datedispatched', $param);
            })->get()->first()->total;
        } else {
            return SampleView::selectRaw("count(id) as total")
            ->where('repeatt', 0)
            ->where('lab_id', '=', env('APP_LAB'))
            ->where('site_entry', '<>', 2)
            ->when(true, function($query) use ($period, $param){
                if($period != 'day') return $query->where('datedispatched', '>', $param);
                return $query->where('datedispatched', $param);
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

    public function partnersearch(Request $request) {
        $search =  $request->input('search');
        $partner = DB::table('partners')->select('id', 'name')
            ->whereRaw("(name like '%" . $search . "%')")
            ->paginate(10);
        return $partner;
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
        // \App\Synch::synch_allocations_updates();
        // \App\Synch::synch_allocations();
        // // dd(Synch::synch_eid_patients());
        // // echo Synch::synch_eid_patients();
        // echo Synch::synch_eid_batches();
    }


}