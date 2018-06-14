<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use DB;

use App\Batch;
use App\Viralbatch;
use App\Facility;
use App\SampleView;
use App\ViralsampleView;
use App\Worksheet;
use App\Viralworksheet;

class DashboardCacher
{




    public static function tasks()
    {
    	self::tasks_cacher();

    	return [
    		'labname' => Cache::get('labname'),
    		'facilityServed' => Cache::get('facilityServed'),
    		'facilitiesWithoutEmails' => Cache::get('facilitiesWithoutEmails'),
    		'facilitiesWithoutG4s' => Cache::get('facilitiesWithoutG4s'),
    	];
    }

    public static function tasks_cacher()
    {
    	if(Cache::has('labname')) return true;

    	$minutes = 15;
    	$min_year = date('Y') - 2;
    	$lab_id = env('APP_LAB');
    	$labname =  \App\Lab::find(env('APP_LAB'))->name;

    	$facilityServed = Viralbatch::selectRaw("COUNT(DISTINCT facility_id) AS total")
    						->whereIn('site_entry', [1, 2])
    						->where('lab_id', $lab_id)
    						->whereYear('datereceived', '>', $min_year)
    						->get()->first()->total;

    	$facilitiesWithoutEmails = Facility::selectRaw('COUNT(*) as total')
    		->whereRaw("( (email = '' or email is null) AND (ContactEmail = '' or ContactEmail is null) )")
    		->whereRaw("id in (SELECT DISTINCT facility_id FROM viralbatches WHERE site_entry in (1, 2) AND year(datereceived) > {$min_year} AND lab_id = {$lab_id})")
    		->get()->first()->total;

    	$facilitiesWithoutG4s = Facility::selectRaw('COUNT(*) as total')
    		->whereRaw("(G4Sbranchname is null or G4Sbranchname = '')")
    		->whereRaw("(G4Slocation is null or G4Slocation = '')")
    		->whereRaw("id in (SELECT DISTINCT facility_id FROM viralbatches WHERE site_entry in (1, 2) AND year(datereceived) > {$min_year} AND lab_id = {$lab_id})")
    		->get()->first()->total;



        Cache::put('labname', $labname, $minutes);
        Cache::put('facilityServed', $facilityServed, $minutes);
        Cache::put('facilitiesWithoutEmails', $facilitiesWithoutEmails, $minutes);
        Cache::put('facilitiesWithoutG4s', $facilitiesWithoutG4s, $minutes);
    }

    public static function dashboard()
    {
    	self::cacher();

        if ($testingSystem == 'Viralload') {
        	return [
        		'pendingSamples' => Cache::get('vl_pendingSamples'),
        		'pendingSamplesOverTen' => Cache::get('vl_pendingSamplesOverTen'),
        		'batchesForApproval' => Cache::get('vl_batchesForApproval'),
        		'batchesNotReceived' => Cache::get('vl_batchesNotReceived'),
        		'batchesForDispatch' => Cache::get('vl_batchesForDispatch'),
        		'samplesForRepeat' => Cache::get('vl_samplesForRepeat'),
        		'rejectedForDispatch' => Cache::get('vl_rejectedForDispatch'),
        		'resultsForUpdate' => Cache::get('vl_resultsForUpdate'),
        	];
        }
        else{
            return [
                'pendingSamples' => Cache::get('eid_pendingSamples'),
                'pendingSamplesOverTen' => Cache::get('eid_pendingSamplesOverTen'),
                'batchesForApproval' => Cache::get('eid_batchesForApproval'),
                'batchesNotReceived' => Cache::get('eid_batchesNotReceived'),
                'batchesForDispatch' => Cache::get('eid_batchesForDispatch'),
                'samplesForRepeat' => Cache::get('eid_samplesForRepeat'),
                'rejectedForDispatch' => Cache::get('eid_rejectedForDispatch'),
                'resultsForUpdate' => Cache::get('eid_resultsForUpdate'),
            ];

        }
    }



	public static function pendingSamplesAwaitingTesting($over = false, $testingSystem = 'Viralload')
	{
        if ($testingSystem == 'Viralload') {
            if ($over == true) {
                $model = ViralsampleView::selectRaw('COUNT(id) as total')->whereNull('worksheet_id')
                                ->whereRaw("datediff(datereceived, datetested) > 10")->get()->first()->total;
            } else {
                $sampletype = ['plasma'=>[1,1],'EDTA'=>[2,2],'DBS'=>[3,4],'all'=>[1,4]];
                foreach ($sampletype as $key => $value) {
                    $model[$key] = ViralsampleView::selectRaw('COUNT(id) as total')
                        ->whereNotIn('receivedstatus', ['0', '2', '4'])
                        ->whereBetween('sampletype', [$value[0], $value[1]])
                        ->whereNull('worksheet_id')
                        ->where('datereceived', '>', '2016-12-31')
                        ->whereRaw("(result is null or result = 0 or result != 'Collect New Sample')")
                        ->where('input_complete', '1')
                        ->where('flag', '1')->get()->first()->total; 
                }
            }
        } else {
            if ($over == true) {
                $model = SampleView::selectRaw('COUNT(id) as total')
                                ->whereNull('worksheet_id')
                                ->whereRaw("datediff(datereceived, datetested) > 10")->get()->first()->total;
            } else {
                $model = SampleView::selectRaw('COUNT(id) as total')
                    ->whereNull('worksheet_id')
                    ->where('datereceived', '>', '2014-12-31')
                    ->whereNotIn('receivedstatus', ['0', '2', '4'])
                    ->whereRaw("(result is null or result = 0)")
                    ->where('input_complete', '1')
                    ->where('flag', '1')->get()->first()->total;
            }
        }
        
        return $model;
	}

	public static function siteBatchesAwaitingApproval($testingSystem = 'Viralload')
	{
        if ($testingSystem == 'Viralload') {
            $model = ViralsampleView::selectRaw('COUNT(id) as total')
                        ->where('lab_id', '=', Auth()->user()->lab_id)
                        ->where('flag', '=', '1')
                        ->where('repeatt', '=', '0')
                        ->whereNull('receivedstatus')
                        ->where('site_entry', '=', '1');
        } else {
            $model = SampleView::selectRaw('COUNT(id) as total')
                    ->where('lab_id', '=', Auth()->user()->lab_id)
                    ->where('flag', '=', '1')
                    ->where('repeatt', '=', '0')
                    ->whereNull('receivedstatus')
                    ->where('site_entry', '=', '1');
        }
        return $model->get()->first()->total ?? 0;
	}

	public static function batchCompleteAwaitingDispatch($testingSystem = 'Viralload')
	{
        if ($testingSystem == 'Viralload') {
            $model = Viralbatch::class;
        } else {
            $model = Batch::class;
        }
        return $model::selectRaw('COUNT(*) as total')->where('lab_id', '=', Auth()->user()->lab_id)->where('batch_complete', '=', '2')->get()->first()->total;
	}

	public static function samplesAwaitingRepeat($testingSystem = 'Viralload')
	{
        if($testingSystem == 'Viralload') {
            $model = ViralsampleView::selectRaw('COUNT(*) as total')
                        ->whereBetween('sampletype', [1, 5])
                        ->whereNotIn('receivedstatus', ['0', '2'])
                        ->whereNull('worksheet_id')
                        ->whereYear('datereceived', '>', '2015')
                        ->where('parentid', '>', 0)
                        ->whereRaw("(result is null or result = 0 or result != 'Collect New Sample')")
                        ->where('input_complete', '=', '1')
                        ->where('flag', '=', '1');
        } else {
            $model = SampleView::selectRaw('COUNT(*) as total')
                        ->whereNull('worksheet_id')
                        ->whereNotIn('receivedstatus', ['0', '2'])
                        ->where(function ($query) {
                            $query->whereNull('result')
                                  ->orWhere('result', '=', 0);
                        })
                        // ->where(DB::raw(('samples.result is null or samples.result = 0')))
                        ->where('flag', '=', '1')
                        ->where('parentid', '>', '0');
        }
		return $model->get()->first()->total;
	}

	public static function rejectedSamplesAwaitingDispatch($testingSystem = 'Viralload')
	{
        $year = Date('Y')-3;
        if ($testingSystem == 'Viralload') {
            $model = ViralsampleView::selectRaw('count(*) as total')
                        ->where('receivedstatus', 2)
                        ->where('flag', '=', 1)
                        ->whereYear('datereceived', '>', $year)
                        ->whereNotNull('datereceived')
                        ->whereNull('datedispatched');
                        // ->where('datedispatched', '=', '')
                        // ->orWhere('datedispatched', '=', '0000-00-00')
                        // ->orWhere('datedispatched', '=', '1970-01-01')
                        // ->orWhereNotNull('datedispatched');
        } else {
            $model = SampleView::selectRaw('count(*) as total')
                        ->where('receivedstatus', 2)
                        ->whereYear('datereceived', '>', $year)
                        ->whereNotNull('datereceived')
                        ->whereNull('datedispatched');
        }
        
		return $model->get()->first()->total ?? 0;
	}

    public static function batchesMarkedNotReceived($testingSystem = 'Viralload')
    {
        $model = 0;
        if ($testingSystem == 'Viralload') {
            $model = ViralsampleView::selectRaw('count(distinct batch_id) as total')
                        ->where('receivedstatus', '=', '4')
                        ->orWhereNull('receivedstatus')->get()->first();
        } else {
            # code...
        }
        
        return $model->total ?? 0;
    }

    public static function resultsAwaitingpdate($testingSystem = 'Viralload')
    {
        if ($testingSystem == 'Viralload') {
            $model = Viralworksheet::with(['creator']);
        } else {
            $model = Worksheet::with(['creator']);
        }

        return $model->selectRaw('count(*) as total')->where('status_id', '=', '1')->get()->first()->total ?? 0;
    }

    public static function cacher()
    {
    	if(Cache::has('vl_pendingSamples')) return true;

    	$minutes = 5;

		$pendingSamples = self::pendingSamplesAwaitingTesting();
        $pendingSamplesOverTen = self::pendingSamplesAwaitingTesting(true);
		$batchesForApproval = self::siteBatchesAwaitingApproval();
        $batchesNotReceived = self::batchesMarkedNotReceived();
		$batchesForDispatch = self::batchCompleteAwaitingDispatch();
		$samplesForRepeat = self::samplesAwaitingRepeat();
		$rejectedForDispatch = self::rejectedSamplesAwaitingDispatch();
        $resultsForUpdate = self::resultsAwaitingpdate();

        $pendingSamples2 = self::pendingSamplesAwaitingTesting(false, 'Eid');
        $pendingSamplesOverTen2 = self::pendingSamplesAwaitingTesting(true, 'Eid');
        $batchesForApproval2 = self::siteBatchesAwaitingApproval('Eid');
        $batchesNotReceived2 = self::batchesMarkedNotReceived('Eid');
        $batchesForDispatch2 = self::batchCompleteAwaitingDispatch('Eid');
        $samplesForRepeat2 = self::samplesAwaitingRepeat('Eid');
        $rejectedForDispatch2 = self::rejectedSamplesAwaitingDispatch('Eid');
        $resultsForUpdate2 = self::resultsAwaitingpdate('Eid');

        
        Cache::put('vl_pendingSamples', $pendingSamples, $minutes);
        Cache::put('vl_pendingSamplesOverTen', $pendingSamplesOverTen, $minutes);
        Cache::put('vl_batchesForApproval', $batchesForApproval, $minutes);
        Cache::put('vl_batchesNotReceived', $batchesNotReceived, $minutes);
        Cache::put('vl_batchesForDispatch', $batchesForDispatch, $minutes);
        Cache::put('vl_samplesForRepeat', $samplesForRepeat, $minutes);
        Cache::put('vl_rejectedForDispatch', $rejectedForDispatch, $minutes);
        Cache::put('vl_resultsForUpdate', $resultsForUpdate, $minutes);

        
        Cache::put('eid_pendingSamples', $pendingSamples2, $minutes);
        Cache::put('eid_pendingSamplesOverTen', $pendingSamplesOverTen2, $minutes);
        Cache::put('eid_batchesForApproval', $batchesForApproval2, $minutes);
        Cache::put('eid_batchesNotReceived', $batchesNotReceived2, $minutes);
        Cache::put('eid_batchesForDispatch', $batchesForDispatch2, $minutes);
        Cache::put('eid_samplesForRepeat', $samplesForRepeat2, $minutes);
        Cache::put('eid_rejectedForDispatch', $rejectedForDispatch2, $minutes);
        Cache::put('eid_resultsForUpdate', $resultsForUpdate2, $minutes);
    }

    public static function clear_cache()
    {
    	Cache::forget('vl_pendingSamples');
    	Cache::forget('vl_pendingSamplesOverTen');
    	Cache::forget('vl_batchesForApproval');
    	Cache::forget('vl_batchesNotReceived');
    	Cache::forget('vl_batchesForDispatch');
    	Cache::forget('vl_samplesForRepeat');
    	Cache::forget('vl_rejectedForDispatch');
    	Cache::forget('vl_resultsForUpdate');
        
        Cache::forget('eid_pendingSamples');
        Cache::forget('eid_pendingSamplesOverTen');
        Cache::forget('eid_batchesForApproval');
        Cache::forget('eid_batchesNotReceived');
        Cache::forget('eid_batchesForDispatch');
        Cache::forget('eid_samplesForRepeat');
        Cache::forget('eid_rejectedForDispatch');
        Cache::forget('eid_resultsForUpdate');
    }


    public static function refresh_cache()
    {
        self::clear_cache();
        self::cacher();
    }

}
