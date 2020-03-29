<?php

namespace App;

use DB;

use Excel;

class MiscCovid extends Common
{


    public static function get_worksheet_samples($machine_type, $limit)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        // $test = in_array(env('APP_LAB'), Lookup::$worksheet_received);
        $test = false;
        $user = auth()->user();

        // if($machine == NULL || $machine->eid_limit == NULL) return false;

        $temp_limit = $limit;     

        if($test){
            $repeats = CovidSample::selectRaw("covid_samples.*, covid_patients.identifier, facilitys.name, users.surname, users.oname")
            	->join('covid_patients', 'covid_patients.id', '=', 'covid_samples.patient_id')
                ->leftJoin('users', 'users.id', '=', 'covid_samples.user_id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'covid_patients.facility_id')
                ->where('datereceived', '>', date('Y-m-d', strtotime('-4 months')))
                ->where('parentid', '>', 0)
                ->whereNull('datedispatched')
                ->whereNull('worksheet_id')
                ->where('receivedstatus', 1)
                ->where('site_entry', '!=', 2)
                ->whereNull('result')
                ->orderBy('covid_samples.id', 'desc')
                ->limit($temp_limit)
                ->get();
            $temp_limit -= $repeats->count();
        }

        $samples = CovidSample::selectRaw("covid_samples.*, covid_patients.identifier, facilitys.name, users.surname, users.oname")
        	->join('covid_patients', 'covid_patients.id', '=', 'covid_samples.patient_id')
            ->leftJoin('users', 'users.id', '=', 'covid_samples.user_id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'covid_samples.facility_id')
            ->where('datereceived', '>', date('Y-m-d', strtotime('-4 months')))
            ->when($test, function($query) use ($user){
                // return $query->where('received_by', $user->id)->where('parentid', 0);
                return $query->where('parentid', 0)
                	->where("received_by",  $user->id);
            })
            ->whereNull('datedispatched')
            ->whereNull('worksheet_id')
            ->where('receivedstatus', 1)
            ->where('site_entry', '!=', 2)
            ->whereNull('result')            
            ->orderBy('run', 'desc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('covid_samples.id', 'asc')     
            ->limit($temp_limit)
            ->get();

        // dd($samples);

        if($test && $repeats->count() > 0) $samples = $repeats->merge($samples);
        $count = $samples->count();        

        $create = false;
        if($count == $limit) $create = true;
        $covid = true;

        return compact('count', 'limit', 'create', 'machine_type', 'machine', 'samples', 'covid');
    }


}
