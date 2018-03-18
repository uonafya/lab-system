<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use DB;

class Lookup extends Model
{

    public function get_machines()
    {
        $this->cacher();
        return Cache::get('machines');
    }

    public function get_facilities()
    {
        $this->cacher();
        return Cache::get('facilities');
    }

    public function worksheet_lookups()
    {
        $this->cacher();
        return [
            'machines' => Cache::get('machines'),
            'worksheet_statuses' => Cache::get('worksheet_statuses'),
        ];
    }

    public function get_lookups()
    {
        $this->cacher();
        return [
            'rejected_reasons' => Cache::get('rejected_reasons'),
            'genders' => Cache::get('genders'),
            'feedings' => Cache::get('feedings'),
            'iprophylaxis' => Cache::get('iprophylaxis'),
            'interventions' => Cache::get('interventions'),
            'entry_points' => Cache::get('entry_points'),
            'results' => Cache::get('results'),
            'received_statuses' => Cache::get('received_statuses'),
        ];
    }

	public function samples_form()
	{
        $this->cacher();
        return [
            // 'facilities' => DB::table('facilitys')->select('id', 'name')->get(),
            'facilities' => Cache::get('facilities'),
            'amrs_locations' => Cache::get('amrs_locations'),
            'rejectedreasons' => Cache::get('rejected_reasons'),
            'genders' => Cache::get('genders'),
            'feedings' => Cache::get('feedings'),
            'iprophylaxis' => Cache::get('iprophylaxis'),
            'interventions' => Cache::get('interventions'),
            'entry_points' => Cache::get('entry_points'),
            'hiv_statuses' => Cache::get('hiv_statuses'),
            'pcrtypes' => Cache::get('pcr_types'),
            'receivedstatuses' => Cache::get('received_statuses'),

            'batch_no' => session('batch_no', 0),
            'batch_dispatch' => session('batch_dispatch', 0),
            'batch_dispatched' => session('batch_dispatched', 0),
            'batch_received' => session('batch_received', 0),

            'facility_id' => session('facility_id', 0),
            'facility_name' => session('facility_name', 0),
        ];
	}

    public function get_viral_lookups()
    {
        $this->cacher();
        return [
            'genders' => Cache::get('genders'),
            'sample_types' => Cache::get('sample_types'),
            'received_statuses' => Cache::get('received_statuses'),
            'prophylaxis' => Cache::get('prophylaxis'),
            'justifications' => Cache::get('justifications'),
        ];        
    }

    public function viralsample_form()
    {
        $this->cacher();
        return [
            'facilities' => Cache::get('facilities'),
            'amrs_locations' => Cache::get('amrs_locations'),
            'genders' => Cache::get('genders'),
            'rejectedreasons' => Cache::get('viral_rejected_reasons'),
            'receivedstatuses' => Cache::get('received_statuses'),
            'pmtct_types' => Cache::get('pmtct_types'),
            'prophylaxis' => Cache::get('prophylaxis'),
            'justifications' => Cache::get('justifications'),
            'sampletypes' => Cache::get('sample_types'),
            'regimenlines' => Cache::get('regimen_lines'),

            'batch_no' => session('viral_batch_no', 0),
            'batch_dispatch' => session('viral_batch_dispatch', 0),
            'batch_dispatched' => session('viral_batch_dispatched', 0),
            'batch_received' => session('viral_batch_received', 0),

            'facility_id' => session('viral_facility_id', 0),
            'facility_name' => session('viral_facility_name', 0),

            'message' => session()->pull('viral_message'),
        ];
    }

	public function cacher()
	{
        if(Cache::has('worksheet_statuses')){}

        else{
            // Common Lookup Data
            $facilities = DB::table('facilitys')->select('id', 'name')->get();
            $amrs_locations = DB::table('amrslocations')->get();
            $genders = DB::table('gender')->get();
            $received_statuses = DB::table('receivedstatus')->get();

            // Eid Lookup Data
            $rejected_reasons = DB::table('rejectedreasons')->get();
            $feedings = DB::table('feedings')->get();
            $iprophylaxis = DB::table('prophylaxis')->where(['ptype' => 2, 'flag' => 1])->where('rank', '>', 0)->orderBy('rank', 'asc')->get();
            $interventions = DB::table('prophylaxis')->where(['ptype' => 1, 'flag' => 1])->where('rank', '>', 0)->orderBy('rank', 'asc')->get();
            $entry_points = DB::table('entry_points')->get();
            $hiv_statuses = DB::table('results')->whereNotIn('id', [3, 5])->get();
            $pcr_types = DB::table('pcrtype')->get();
            $results = DB::table('results')->get();

            // Viralload Lookup Data
            $viral_rejected_reasons = DB::table('viralrejectedreasons')->get();
            $pmtct_types = DB::table('viralpmtcttype')->get();
            $prophylaxis = DB::table('viralprophylaxis')->orderBy('category', 'asc')->get();
            $justifications = DB::table('viraljustifications')->get();
            $sample_types = DB::table('viralsampletype')->where('flag', 1)->get();
            $regimen_lines = DB::table('viralregimenline')->where('flag', 1)->get();

            // Worksheet Lookup Data
            $machines = DB::table('machines')->get();
            $worksheet_statuses = DB::table('worksheetstatus')->get();

            Cache::put('facilities', $facilities, 60);
            Cache::put('amrs_locations', $amrs_locations, 60);
            Cache::put('genders', $genders, 60);
            Cache::put('received_statuses', $received_statuses, 60);

            Cache::put('rejected_reasons', $rejected_reasons, 60);
            Cache::put('feedings', $feedings, 60);
            Cache::put('iprophylaxis', $iprophylaxis, 60);
            Cache::put('interventions', $interventions, 60);
            Cache::put('entry_points', $entry_points, 60);
            Cache::put('hiv_statuses', $hiv_statuses, 60);
            Cache::put('pcr_types', $pcr_types, 60);
            Cache::put('results', $results, 60);

            Cache::put('viral_rejected_reasons', $viral_rejected_reasons, 60);
            Cache::put('pmtct_types', $pmtct_types, 60);
            Cache::put('prophylaxis', $prophylaxis, 60);
            Cache::put('interventions', $interventions, 60);
            Cache::put('justifications', $justifications, 60);
            Cache::put('sample_types', $sample_types, 60);
            Cache::put('regimen_lines', $regimen_lines, 60);

            Cache::put('machines', $machines, 60);
            Cache::put('worksheet_statuses', $worksheet_statuses, 60);
        }		
	}

    public function clear_cache()
    {
        Cache::forget('facilities');
        Cache::forget('amrs_locations');
        Cache::forget('genders');
        Cache::forget('received_statuses');
        Cache::forget('rejected_reasons');
        Cache::forget('feedings');
        Cache::forget('iprophylaxis');
        Cache::forget('interventions');
        Cache::forget('entry_points');
        Cache::forget('pcr_types');
        Cache::forget('results');
        Cache::forget('viral_rejected_reasons');
        Cache::forget('pmtct_types');
        Cache::forget('prophylaxis');
        Cache::forget('interventions');
        Cache::forget('justifications');
        Cache::forget('sample_types');
        Cache::forget('regimen_lines');
        Cache::forget('machines');
        Cache::forget('worksheet_statuses');
    }

    public function refresh_cache()
    {
        $this->clear_cache();
        $this->cacher();
    }
}
