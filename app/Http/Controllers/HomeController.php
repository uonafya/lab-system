<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Sample;
use App\Viralsample;
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
