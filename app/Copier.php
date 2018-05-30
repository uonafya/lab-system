<?php

namespace App;

use Carbon\Carbon;

use App\OldModels\SampleView;
use App\OldModels\ViralsampleView;

use App\OldModels\WorksheetView;
use App\OldModels\ViralworksheetView;

use App\Mother;
use App\Worksheet;
use App\Patient;
use App\Batch;
use App\Sample;

use App\Viralworksheet;
use App\Viralpatient;
use App\Viralbatch;
use App\Viralsample;

class Copier
{
    private static $limit = 10000;

    public static function copy_eid()
    {
        $start = Sample::max('id');
        ini_set("memory_limit", "-1");
        $fields = self::samples_arrays(); 
        $date_array = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2'];
        $offset_value = 0;
        while(true)
        {
            $samples = SampleView::when($start, function($query) use ($start){
                return $query->where('id', '>', $start);
            })->limit(self::$limit)->offset($offset_value)->get();
            if($samples->isEmpty()) break;

            foreach ($samples as $key => $value) {
                $patient = Patient::existing($value->facility_id, $value->patient)->get()->first();

                if(!$patient){
                    $mother = new Mother($value->only($fields['mother']));
                    $mother->save();
                    $patient = new Patient($value->only($fields['patient']));
                    $patient->mother_id = $mother->id;
                    $patient->dob = self::calculate_dob($value->datecollected, 0, $value->age);
                    $patient->sex = self::resolve_gender($value->gender);
                    $patient->ccc_no = $value->enrollment_ccc_no;
                    $patient->save();
                }

                $batch = Batch::find($value->original_batch_id);

                if(!$batch){
                    $batch = new Batch($value->only($fields['batch']));
                    $batch->id = $value->original_batch_id;
                    $batch->save();
                }

                $sample = new Sample($value->only($fields['sample']));
                foreach ($date_array as $date_field) {
                    $sample->$date_field = self::clean_date($sample->$date_field);
                }
                $sample->batch_id = $batch->id;
                $sample->patient_id = $patient->id;
                $sample->save();
            }
            $offset_value += self::$limit;
            echo "Completed eid {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
        }
    }


    public static function copy_vl()
    {
        $start = Viralsample::max('id');
        ini_set("memory_limit", "-1");
        $fields = self::viralsamples_arrays();  
        $date_array = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2'];  
        $offset_value = 0;
        while(true)
        {
            $samples = ViralsampleView::when($start, function($query) use ($start){
                return $query->where('id', '>', $start);
            })->limit(self::$limit)->offset($offset_value)->get();
            if($samples->isEmpty()) break;

            foreach ($samples as $key => $value) {
                $patient = Viralpatient::existing($value->facility_id, $value->patient)->get()->first();

                if(!$patient){
                    $patient = new Viralpatient($value->only($fields['patient']));
                    $patient->dob = self::calculate_dob($value->datecollected, $value->age, 0);
                    $patient->sex = self::resolve_gender($value->gender);
                    $patient->save();
                }

                $batch = Viralbatch::find($value->original_batch_id);

                if(!$batch){
                    $batch = new Viralbatch($value->only($fields['batch']));
                    $batch->id = $value->original_batch_id;
                    $batch->save();
                }

                $sample = new Viralsample($value->only($fields['sample']));
                foreach ($date_array as $date_field) {
                    $sample->$date_field = self::clean_date($sample->$date_field);
                }
                $sample->batch_id = $batch->id;
                $sample->patient_id = $patient->id;
                $sample->save();
            }
            $offset_value += self::$limit;
            echo "Completed vl {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
        }
    }

    public static function copy_worksheet()
    {
        $work_array = [
            'eid' => ['model' => Worksheet::class, 'view' => WorksheetView::class],
            'vl' => ['model' => Viralworksheet::class, 'view' => ViralworksheetView::class],
        ];

        $date_array = ['kitexpirydate', 'sampleprepexpirydate', 'bulklysisexpirydate', 'controlexpirydate', 'calibratorexpirydate', 'amplificationexpirydate', 'datecut', 'datereviewed', 'datereviewed2', 'datecancelled', 'daterun'];

        foreach ($work_array as $key => $value) {
            $model = $value['model'];
            $view = $value['view'];

            $start = $model::max('id');
            ini_set("memory_limit", "-1");  

            $offset_value = 0;
            while(true)
            {
                $worksheets = $view::when($start, function($query) use ($start){
                    return $query->where('id', '>', $start);
                })->limit(self::$limit)->offset($offset_value)->get();
                if($worksheets->isEmpty()) break;

                foreach ($worksheets as $worksheet_key => $worksheet) {
                    $work = new $model;
                    $work->fill($worksheet->except(['none']));
                    foreach ($date_array as $date_field) {
                        $work->$date_field = self::clean_date($work->$date_field);
                    }
                    $work->save();
                }
                $offset_value += self::$limit;
                echo "Completed {$key} worksheet {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
            }
        }
    }

    public static function clean_date($mydate)
    {
        if(!$mydate) return null;

        try {
            $my = Carbon::parse($mydate);
            return $my->toDateString();
        } catch (Exception $e) {
            return null;
        }
    }

    public static function calculate_age($date_collected, $dob)
    {
    	if(!$dob) return 0;
        $dob = Carbon::parse( $dob );
        $dc = Carbon::parse( $date_collected );
        $months = $dc->diffInMonths($dob);
        $weeks = $dc->diffInWeeks($dob->copy()->addMonths($months));
        $total = $months + ($weeks / 4);
        if($total == 0) $total = 0.1;
        return $total;
    }

    public static function calculate_viralage($date_collected, $dob)
    {
    	if(!$dob) return 0;
        $dob = Carbon::parse( $dob );
        $dc = Carbon::parse( $date_collected );
        $years = $dc->diffInYears($dob, true);

        if($years == 0) $years = ($dc->diffInMonths($dob)/12);
        return $years;
    }

    public static function calculate_dob($date_collected, $years, $months)
    {
    	if((!$years && !$months) || !$date_collected ) return null;
        // if(Carbon::createFromFormat('Y-m-d', $date_collected) !== false){            
        //     $dc = Carbon::createFromFormat('Y-m-d', $date_collected);
        //     $dc->subYears($years);
        //     $dc->subMonths($months);
        //     return $dc->toDateString();
        // }
        // return null;

        try {           
            $dc = Carbon::createFromFormat('Y-m-d', $date_collected);
            $dc->subYears($years);
            $dc->subMonths($months);
            return $dc->toDateString();
            
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    public static function resolve_gender($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        if(str_contains($value, ['m', '1'])){
            return 1;
        }
        else if(str_contains($value, ['f', '2'])){
            return 2;
        }
        // else if($value == 'No Data' || $value == 'no data'){
        //     return 3;
        // }
        // else if (is_numeric($value)){
        //     $value = (int) $value;
        //     if($value < 3) return $value;
        //     return $value;
        // }
        else{
            return 3;
        }
    }


    public static function samples_arrays()
    {
        return [

            'batch' => ['highpriority', 'input_complete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted'],

            'mother' => ['hiv_status', 'facility_id', 'ccc_no'],

            'patient' => ['patient', 'patient_name', 'sex', 'facility_id', 'caregiver_phone', 'dob', 'entry_point', 'dateinitiatedontreatment'],

            'sample' => ['id', 'amrs_location', 'provider_identifier', 'order_no', 'sample_type', 'receivedstatus', 'age', 'redraw', 'pcrtype', 'regimen', 'mother_prophylaxis', 'feeding', 'spots', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'worksheet_id', 'hei_validation', 'enrollment_ccc_no', 'enrollment_status', 'referredfromsite', 'otherreason', 'flag', 'run', 'repeatt', 'eqa', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'previous_positive', 'synched', 'datesynched', 'mother_last_result', 'mother_age' ], 
        ];
    }

    public static function viralsamples_arrays()
    {
        return [

            'batch' => ['highpriority', 'input_complete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted'],

            'patient' => ['patient', 'sex', 'patient_name', 'facility_id', 'caregiver_phone', 'patient', 'dob', 'initiation_date'],

            'sample' => ['id', 'amrs_location', 'provider_identifier', 'order_no', 'vl_test_request_no', 'receivedstatus', 'age', 'age_category', 'justification', 'other_justification', 'sampletype', 'prophylaxis', 'regimenline', 'pmtct', 'dilutionfactor', 'dilutiontype', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'rcategory', 'units', 'worksheet_id', 'flag', 'run', 'repeatt', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'previous_nonsuppressed', 'synched', 'datesynched' ],
            
        ];
    }
}
