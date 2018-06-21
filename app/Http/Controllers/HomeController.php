<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
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
        
        return view('home', ['chart'=>$chart])->with('pageTitle', 'Home');
    }

    public function getHomeGraph()
    {
        $chart = [];
        $count = 0;
        $data = ['Entered Samples' => self::__getEnteredSamples(),
                'Received Samples' => self::__getReceivedSamples(),
                'Tested Samples' => self::__getTestedSamples(),
                'Dispatched Samples' => self::__getDispatchedSamples()
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
            # code...
        } else {
            # code...
        }
    }

    public function pending($type = 'samples', $sampletypes = null) {
        if (session('testingSystem') == 'Viralload') {
            $samples = ViralsampleView::selectRaw('viralsamples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, viralsampletype.name as sampletype, datediff(curdate(), datereceived) as waitingtime')
                    ->join('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                    ->join('receivedstatus', 'receivedstatus.id', '=', 'viralsamples_view.receivedstatus')
                    ->join('viralsampletype', 'viralsampletype.id', '=', 'viralsamples_view.sampletype')
                    ->whereNotIn('receivedstatus', ['0', '2', '4'])
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
                    ->whereRaw("(result is null or result = 0 or result != 'Collect New Sample')")
                    ->where('input_complete', '1')
                    ->where('viralsamples_view.flag', '1')->get();
        } else {
            $samples = SampleView::selectRaw('samples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                    ->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
                    ->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
                    ->whereNull('worksheet_id')
                    ->where('datereceived', '>', '2014-12-31')
                    ->whereNotIn('receivedstatus', ['0', '2', '4'])
                    ->whereRaw("(result is null or result = 0)")
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
                        ->where('receivedstatus', 3)
                        ->whereNull('worksheet_id')
                        ->whereYear('datereceived', '>', '2015')
                        ->where('parentid', '>', 0)
                        ->whereRaw("(result is null or result = 0 or result != 'Collect New Sample')")
                        ->where('input_complete', '=', '1')
                        ->where('flag', '=', '1')->get();
        } else {
            $samples = SampleView::selectRaw('samples_view.*, view_facilitys.name as facility, view_facilitys.county, receivedstatus.name as receivedstatus, datediff(curdate(), datereceived) as waitingtime')
                        ->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
                        ->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
                        ->whereNull('worksheet_id')
                        ->where('receivedstatus', 3)
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

    static function __getEnteredSamples() 
    {
        if (session('testingSystem') == 'Viralload') {
            return Viralsample::whereRaw('DATE(created_at) = CURDATE()')->count();
        } else {
            return Sample::whereRaw('DATE(created_at) = CURDATE()')->count();
        }
    }

    static function __getReceivedSamples()
    {
        if (session('testingSystem') == 'Viralload') {
            return DB::table('viralsamples')->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')->whereRaw('DATE(viralbatches.datereceived) = CURDATE()')->count();
        } else {
            return DB::table('samples')->join('batches', 'batches.id', '=', 'samples.batch_id')->whereRaw('DATE(batches.datereceived) = CURDATE()')->count();
        }
    }

    static function __getTestedSamples()
    {
        if (session('testingSystem') == 'Viralload') {
            return Viralsample::whereRaw('DATE(datetested) = CURDATE()')->count();
        } else {
            return Sample::whereRaw('DATE(datetested) = CURDATE()')->count();
        }
    }

    static function __getDispatchedSamples()
    {
        if (session('testingSystem') == 'Viralload') {
            return DB::table('viralsamples')->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')->whereRaw('DATE(viralbatches.datedispatched) = CURDATE()')->count();
        } else {
            return DB::table('samples')->join('batches', 'batches.id', '=', 'samples.batch_id')->whereRaw('DATE(batches.datedispatched) = CURDATE()')->count();
        }
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