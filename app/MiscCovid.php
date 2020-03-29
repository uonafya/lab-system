<?php

namespace App;

use DB;

use Excel;

class MiscCovid extends Common
{


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
            ->orderBy('covid_sample_view.id', 'asc')     
            ->limit($temp_limit)
            ->get();

        // dd($samples);

        if($entered_by && $repeats->count() > 0) $samples = $repeats->merge($samples);
        $count = $samples->count();        

        $create = false;
        if($count == $limit) $create = true;
        $covid = true;

        return compact('count', 'limit', 'create', 'machine_type', 'machine', 'samples', 'covid');
    }


}
