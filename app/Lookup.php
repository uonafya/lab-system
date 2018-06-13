<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use DB;

use Carbon\Carbon;

class Lookup
{

    public static $double_approval = [2, 4, 5];
    public static $amrs = [3, 5];

    public static $api_data = ['s.id', 's.order_no', 'p.patient', 's.provider_identifier', 'f.facilitycode', 's.amrs_location', 'p.patient_name', 's.datecollected', 'b.datereceived', 's.datetested', 's.interpretation', 's.result', 'b.datedispatched', 'b.batch_complete', 's.receivedstatus', 's.approvedby', 's.repeatt'];



    public static function my_date_format($value)
    {
        if($value) return date('d-M-Y', strtotime($value));
        return '';
    }

    public static function get_gender($value)
    {
        $value = trim($value);
        if($value == 'M' || $value == 'm'){
            return 1;
        }
        else if($value == 'F' || $value == 'f'){
            return 2;
        }
        else if($value == 'No Data' || $value == 'No data'){
            return 3;
        }
        else if (is_int($value)){
            return $value;
        }
        else{
            return 3;
        }
    }

    public static function get_api()
    {
        self::cacher();
        return [
            'genders' => Cache::get('genders'),
            'amrs_locations' => Cache::get('amrs_locations'),
            'results' => Cache::get('results'),
            'received_statuses' => Cache::get('received_statuses'),
        ];
    }

    public static function facility_mfl($mfl)
    {
        self::cacher(); 
        $fac = Cache::get('facilities');       
        return $fac->where('facilitycode', $mfl)->first()->id;
    }

    public static function get_machines()
    {
        self::cacher();
        return Cache::get('machines');
    }

    public static function get_facilities()
    {
        self::cacher();
        return Cache::get('facilities');
    }



    public static function worksheet_lookups()
    {
        self::cacher();
        return [
            'machines' => Cache::get('machines'),
            'worksheet_statuses' => Cache::get('worksheet_statuses'),
        ];
    }

    public static function worksheet_approve_lookups()
    {
        self::cacher();
        return [
            'machines' => Cache::get('machines'),
            'worksheet_statuses' => Cache::get('worksheet_statuses'),
            'actions' => Cache::get('actions'),
            'dilutions' => Cache::get('dilutions'),
            'results' => Cache::get('results'),
            'double_approval' => self::$double_approval
        ];
    }

    public static function get_lookups()
    {
        self::cacher();
        return [
            'rejected_reasons' => Cache::get('rejected_reasons'),
            'genders' => Cache::get('genders'),
            'feedings' => Cache::get('feedings'),
            'iprophylaxis' => Cache::get('iprophylaxis'),
            'interventions' => Cache::get('interventions'),
            'entry_points' => Cache::get('entry_points'),
            'results' => Cache::get('results'),
            'received_statuses' => Cache::get('received_statuses'),
            'pcrtypes' => Cache::get('pcr_types'),
        ];
    }

	public static function samples_form()
	{
        self::cacher();
        return [
            // 'facilities' => DB::table('facilitys')->select('id', 'name')->get(),
            // 'facilities' => Cache::get('facilities'),
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

            'batch' => session('batch'),
            'facility_name' => session('facility_name', 0),
            'amrs' => self::$amrs,
        ];
	}

    public static function calculate_age($date_collected, $dob)
    {
        // $patient_age = $request->input('sample_months') + ( $request->input('sample_weeks') / 4 );
        // $dt = Carbon::today();
        // $dt->subMonths($request->input('sample_months'));
        // $dt->subWeeks($request->input('sample_weeks'));
        // $patient->dob = $dt->toDateString();

        // $dc = Carbon::createFromFormat('Y-m-d', $request->input('datecollected'));
        $dob = Carbon::parse( $dob );
        $dc = Carbon::parse( $date_collected );
        $months = $dc->diffInMonths($dob);
        $weeks = $dc->diffInWeeks($dob->copy()->addMonths($months));
        $total = $months + ($weeks / 4);
        if($total == 0) $total = 0.1;
        return $total;
    }

    public static function calculate_mother_dob($date_collected, $age = null)
    {
        if(!$age) return null;
        $dc = Carbon::parse( $date_collected );
        $dc->subYears($age);
        return $dc->toDateString();
    }

    public static function samples_arrays()
    {
        return [
            'batch' => ['datereceived', 'datedispatchedfromfacility', 'highpriority', 'facility_id', 'lab_id', 'site_entry'],

            'mother' => ['hiv_status', 'facility_id', 'ccc_no', 'mother_dob'],

            'patient' => ['sex', 'patient_name', 'facility_id', 'caregiver_phone', 'patient', 'dob', 'entry_point', 'patient_status'],

            'sample' => ['comments', 'labcomment', 'datecollected', 'spots', 'patient_id', 'rejectedreason', 'receivedstatus', 'mother_prophylaxis', 'mother_age', 'mother_last_result', 'feeding', 'regimen', 'redraw', 'pcrtype', 'enrollment_ccc_no', 'provider_identifier', 'amrs_location', 'sample_type', 'order_no'],

            'sample_rerun' => ['comments', 'labcomment', 'datecollected', 'spots', 'patient_id', 'rejectedreason', 'receivedstatus', 'mother_prophylaxis', 'mother_age', 'mother_last_result', 'feeding', 'regimen', 'redraw', 'pcrtype', 'enrollment_ccc_no', 'provider_identifier', 'amrs_location', 'sample_type', 'order_no', 'batch_id', 'patient_id', 'run', 'parentid'],

            'sample_except' => ['_token', 'patient_name', 'submit_type', 'facility_id', 'sex', 'sample_months', 'sample_weeks', 'entry_point', 'caregiver_phone', 'hiv_status', 'patient', 'new_patient', 'datereceived', 'datedispatchedfromfacility', 'dob', 'ccc_no', 'highpriority'],

            'sample_api' => ['labcomment', 'datecollected', 'datetested', 'patient_id', 'mother_prophylaxis', 'feeding', 'pcrtype', 'regimen', 'receivedstatus', 'rejectedreason', 'reason_for_repeat', 'result'],
        ]; 
    }

    public static function get_viral_lookups()
    {
        self::cacher();
        return [
            'viral_rejected_reasons' => Cache::get('viral_rejected_reasons'),
            'vl_result_guidelines' => Cache::get('vl_result_guidelines'),
            'genders' => Cache::get('genders'),
            'sample_types' => Cache::get('sample_types'),
            'received_statuses' => Cache::get('received_statuses'),
            'prophylaxis' => Cache::get('prophylaxis'),
            'justifications' => Cache::get('justifications'),
        ];        
    }

    public static function viralsample_form()
    {
        self::cacher();
        return [
            // 'facilities' => Cache::get('facilities'),
            'amrs_locations' => Cache::get('amrs_locations'),
            'genders' => Cache::get('genders'),
            'rejectedreasons' => Cache::get('viral_rejected_reasons'),
            'receivedstatuses' => Cache::get('received_statuses'),
            'pmtct_types' => Cache::get('pmtct_types'),
            'prophylaxis' => Cache::get('prophylaxis'),
            'justifications' => Cache::get('justifications'),
            'sampletypes' => Cache::get('sample_types'),
            'regimenlines' => Cache::get('regimen_lines'),

            'batch' => session('viral_batch'),
            'facility_name' => session('viral_facility_name', 0),
            'amrs' => self::$amrs,
        ];
    }

    public static function calculate_viralage($date_collected, $dob)
    {
        $dob = Carbon::parse( $dob );
        $dc = Carbon::parse( $date_collected );
        $years = $dc->diffInYears($dob, true);

        if($years == 0) $years = ($dc->diffInMonths($dob)/12);
        return $years;
    }


    public static function viral_regimen($value)
    {
        self::cacher();       
        $my_array = Cache::get('prophylaxis');       
        return $my_array->where('category', $val)->first()->id ?? 16;
    }    

    public static function justification($value)
    {
        self::cacher();       
        $my_array = Cache::get('justifications');       
        return $my_array->where('rank', $val)->first()->id ?? 8;
    }    

    public static function sample_type($value)
    {
        self::cacher();       
        $my_array = Cache::get('sample_types');       
        return $my_array->where('alias', $val)->first()->id;
    }

    public static function viralsamples_arrays()
    {
        return [
            'batch' => ['datereceived', 'datedispatchedfromfacility', 'highpriority', 'facility_id', 'lab_id', 'site_entry'],

            'patient' => ['sex', 'patient_name', 'facility_id', 'caregiver_phone', 'patient', 'dob', 'initiation_date', 'patient_status'],

            'sample' => ['comments', 'labcomment', 'datecollected', 'patient_id', 'rejectedreason', 'receivedstatus', 'pmtct', 'sampletype', 'prophylaxis', 'regimenline', 'justification', 'provider_identifier', 'amrs_location', 'vl_test_request_no', 'order_no'],

            'sample_rerun' => ['comments', 'labcomment', 'datecollected', 'patient_id', 'rejectedreason', 'receivedstatus', 'pmtct', 'sampletype', 'prophylaxis', 'regimenline', 'justification', 'provider_identifier', 'amrs_location', 'vl_test_request_no', 'order_no', 'batch_id', 'patient_id', 'run', 'parentid'],

            'sample_except' => ['_token', 'patient_name', 'submit_type', 'facility_id', 'sex', 'caregiver_phone', 'patient', 'new_patient', 'datereceived', 'datedispatchedfromfacility', 'dob', 'initiation_date', 'highpriority'],

            'sample_api' => ['labcomment', 'datecollected', 'datetested', 'patient_id', 'pmtct', 'sampletype', 'prophylaxis', 'regimenline', 'justification', 'receivedstatus', 'rejectedreason', 'reason_for_repeat', 'result'],
        ];
    }

	public static function cacher()
	{
        if(Cache::has('worksheet_statuses')){}

        else{
            // Common Lookup Data
            // $facilities = DB::table('facilitys')->select('id', 'name', 'facilitycode')->get();
            $amrs_locations = DB::table('amrslocations')->get();
            $genders = DB::table('gender')->where('id', '<', 3)->get();
            $received_statuses = DB::table('receivedstatus')->where('id', '<', 3)->get();

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
            $vl_result_guidelines = DB::table('vlresultsguidelines')->get();

            // Worksheet Lookup Data
            $machines = DB::table('machines')->get();
            $actions = DB::table('actions')->get();
            $dilutions = DB::table('viraldilutionfactors')->get();
            $worksheet_statuses = DB::table('worksheetstatus')->get();

            // Cache::put('facilities', $facilities, 60);
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
            Cache::put('vl_result_guidelines', $vl_result_guidelines, 60);

            Cache::put('machines', $machines, 60);
            Cache::put('actions', $actions, 60);
            Cache::put('dilutions', $dilutions, 60);
            Cache::put('worksheet_statuses', $worksheet_statuses, 60);
        }		
	}

    public static function clear_cache()
    {
        // Cache::forget('facilities');
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
        Cache::forget('vl_result_guidelines');
        Cache::forget('machines');
        Cache::forget('actions');
        Cache::forget('dilutions');
        Cache::forget('worksheet_statuses');
    }

    public static function refresh_cache()
    {
        self::clear_cache();
        self::cacher();
    }
}
