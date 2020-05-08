<?php

namespace App;

use DB;

use Excel;

class MiscCovid extends Common
{


    public static function sample_result($target1, $target2, $error=null)
    {
        $target1 = trim(strtolower($target1));
        $target2 = trim(strtolower($target2));
        $repeatt = 0;

        if($target1 == 'positive'){
            $result = 2;
            $interpretation = 'Positive';
        }
        else if($target2 == 'positive'){
            $result = 2;
            $interpretation = 'Presumed Positive. Requires Rerun.';
        }
        else if($target1 == 'negative' && $target1 == $target2){
            $result = 1;
            $interpretation = 'Negative';
        }
        else if(in_array($target1, ['invalid', 'negative']) && in_array($target2, ['invalid', 'negative'])){
            $result = 3;
            $interpretation = 'Failed';
            $repeatt = 1;
        }
        else if($target1 == 'valid' && $target1 == $target2){
            $result = 6;
            $interpretation = 'Valid';            
        }
        else{
            return ['result' => 3, 'interpretation' => 'Failed'];
        }

        // return ['result' => $result, 'interpretation' => $interpretation, 'repeatt' => 0];
        return compact('result', 'interpretation', 'repeatt', 'target1', 'target2', 'error');
    }


    public static function save_repeat($sample_id)
    {
        $original = CovidSample::find($sample_id);
        if($original->run == 5){
            $original->repeatt=0;
            $original->save();
            return false;
        }

        $sample = $original->replicate(['national_sample_id', 'worksheet_id', 'interpretation', 'result', 'repeatt', 'datetested', 'dateapproved', 'dateapproved2', 'approvedby', 'approvedby2']); 
        $sample->run++;
        if($original->parentid == 0) $sample->parentid = $original->id;

        $s = CovidSample::where(['parentid' => $sample->parentid, 'run' => $sample->run])->first();
        if($s) return $s;
        
        $sample->save();
        return $sample;
    }


    public static function get_worksheet_samples($machine_type, $limit, $entered_by=null)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        $user = auth()->user();

        $temp_limit = $limit;     

        if($entered_by){
            $repeats = CovidSampleView::selectRaw("covid_sample_view.*, users.surname, users.oname")
                ->leftJoin('users', 'users.id', '=', 'covid_sample_view.user_id')
                ->where('datereceived', '>', date('Y-m-d', strtotime('-4 months')))
                ->where('parentid', '>', 0)
                ->whereNull('datedispatched')
                ->whereNull('worksheet_id')
                ->where('receivedstatus', 1)
                ->where('site_entry', '!=', 2)
                ->whereNull('result')
                ->orderBy('covid_sample_view.id', 'desc')
                ->limit($temp_limit)
                ->get();
            $temp_limit -= $repeats->count();
        }

        $samples = CovidSampleView::selectRaw("covid_sample_view.*, users.surname, users.oname")
            ->leftJoin('users', 'users.id', '=', 'covid_sample_view.user_id')
            ->where('datereceived', '>', date('Y-m-d', strtotime('-4 months')))
            ->when($entered_by, function($query) use ($entered_by){
            	$query->where('parentid', 0);
                if(is_array($entered_by)) return $query->whereIn('received_by', $entered_by);
                return $query->where('received_by', $entered_by);
            })
            ->whereNull('datedispatched')
            ->whereNull('worksheet_id')
            ->where('receivedstatus', 1)
            ->where('site_entry', '!=', 2)
            ->whereNull('result')            
            ->orderBy('run', 'desc')
            ->orderBy('datereceived', 'asc')
            ->when(in_array(env('APP_LAB'), [6]), function($query){
                return $query->orderBy('quarantine_site_id')->orderBy('facility_id');
            })
            ->orderBy('covid_sample_view.id', 'asc')     
            ->limit($temp_limit)
            ->get();

        // dd($samples);

        if($entered_by && $repeats->count() > 0) $samples = $repeats->merge($samples);
        $count = $samples->count();        

        $create = false;
        if($count == $limit) $create = true;
        if($count) $create = true;
        $covid = true;

        return compact('count', 'limit', 'create', 'machine_type', 'machine', 'samples', 'covid');
    }


}