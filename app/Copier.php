<?php

namespace App;

use Carbon\Carbon;
use DB;

use App\OldModels\SampleView;
use App\OldModels\ViralsampleView;
use App\OldModels\FormerViralsampleView;

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

use App\Lookup;
use App\Common;
use App\Misc;
use App\MiscViral;

class Copier
{
    private static $limit = 5000;

    public static function copy_eid()
    {
        $start = Sample::max('id');
        ini_set("memory_limit", "-1");
        $fields = self::samples_arrays(); 
        $sample_date_array = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'created_at'];
        $batch_date_array = ['datedispatchedfromfacility', 'datereceived', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted', 'created_at'];
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
                    if($patient->dob) $patient->dob = self::clean_date($patient->dob);

                    if(!$patient->dob) $patient->dob = self::previous_dob(SampleView::class, $value->patient, $value->facility_id);

                    if(!$patient->dob){
                        $patient->dob = self::calculate_dob($value->datecollected, 0, $value->age, SampleView::class, $value->patient, $value->facility_id);
                    }
                    $patient->sex = self::resolve_gender($value->gender, SampleView::class, $value->patient, $value->facility_id);
                    $patient->ccc_no = $value->enrollment_ccc_no;
                    $patient->save();
                }

                $value->original_batch_id = self::set_batch_id($value->original_batch_id);
                $batch = Batch::find($value->original_batch_id);

                if(!$batch){
                    $batch = new Batch($value->only($fields['batch']));
                    foreach ($batch_date_array as $date_field) {
                        $batch->$date_field = self::clean_date($value->$date_field);
                    }
                    $batch->id = $value->original_batch_id;
                    // Temporarily use 
                    $batch->received_by = $value->user_id;
                    $batch->save();
                }

                $sample = new Sample($value->only($fields['sample']));
                foreach ($sample_date_array as $date_field) {
                    $sample->$date_field = self::clean_date($value->$date_field);
                }
                $sample->batch_id = $value->original_batch_id;
                $sample->patient_id = $patient->id;

                if($sample->age == 0 && $batch->datecollected && $patient->dob){
                    $sample->age = Lookup::calculate_age($batch->datecollected, $patient->dob);
                }
                if($sample->worksheet_id == 0) $sample->worksheet_id = null;

                $sample->save();
            }
            $offset_value += self::$limit;
            echo "Completed eid {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
        }

        $my = new Misc;
        $my->compute_tat(\App\SampleView::class, Sample::class);
        echo "Completed eid clean at " . date('d/m/Y h:i:s a', time()). "\n";
    }


    public static function copy_vl()
    {
        $start = Viralsample::max('id');
        ini_set("memory_limit", "-1");
        $fields = self::viralsamples_arrays();  
        $sample_date_array = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'created_at'];
        $batch_date_array = ['datedispatchedfromfacility', 'datereceived', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted', 'created_at'];
        $offset_value = 0;
        $sample_class = FormerViralsampleView::class;

        while(true)
        {
            // If the samples table already has values, then the former table has already been copied
            if(!$start) $sample_class = ViralsampleView::class;
            $samples = $sample_class::when($start, function($query) use ($start){
                return $query->where('id', '>', $start);
            })->limit(self::$limit)->offset($offset_value)->get();

            
            if($samples->isEmpty()){
                // If the samples table has been copied, exit the loop
                if($sample_class == 'App\OldModels\ViralsampleView') break;
                // Else, has finished copying the former table
                // Switch to the one in use and commence copying
                $sample_class = ViralsampleView::class;
                continue;
            }

            foreach ($samples as $key => $value) {
                $patient = Viralpatient::existing($value->facility_id, $value->patient)->get()->first();

                if(!$patient){
                    $patient = new Viralpatient($value->only($fields['patient']));
                    if($patient->dob) $patient->dob = self::clean_date($patient->dob);

                    if(!$patient->dob) $patient->dob = self::previous_dob(ViralsampleView::class, $value->patient, $value->facility_id);
                    
                    if(!$patient->dob){
                        $patient->dob = self::calculate_dob($value->datecollected, $value->age, 0, ViralsampleView::class, $value->patient, $value->facility_id);
                    }
                    $patient->sex = self::resolve_gender($value->gender, ViralsampleView::class, $value->patient, $value->facility_id);
                    $patient->initiation_date = self::clean_date($patient->initiation_date);
                    $patient->save();
                }

                $value->original_batch_id = self::set_batch_id($value->original_batch_id);
                $batch = Viralbatch::find($value->original_batch_id);

                if(!$batch){
                    $batch = new Viralbatch($value->only($fields['batch']));
                    foreach ($batch_date_array as $date_field) {
                        $batch->$date_field = self::clean_date($value->$date_field);
                    }
                    $batch->id = $value->original_batch_id;
                    // Temporarily use 
                    $batch->received_by = $value->user_id;
                    $batch->save();
                }

                $sample = new Viralsample($value->only($fields['sample']));
                foreach ($sample_date_array as $date_field) {
                    $sample->$date_field = self::clean_date($value->$date_field);
                }
                $sample->batch_id = $value->original_batch_id;
                $sample->patient_id = $patient->id;

                if($sample->age == 0 && $batch->datecollected && $patient->dob){
                    $sample->age = Lookup::calculate_viralage($batch->datecollected, $patient->dob);
                }
                if($sample->worksheet_id == 0) $sample->worksheet_id = null;

                $sample->save();
            }
            $offset_value += self::$limit;
            echo "Completed vl {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
        }

        $my = new MiscViral;
        $my->compute_tat(\App\ViralsampleView::class, Viralsample::class);
        echo "Completed vl clean at " . date('d/m/Y h:i:s a', time()). "\n";
    }

    private static function set_batch_id($batch_id)
    {
        if($batch_id == floor($batch_id)) return $batch_id;
        return (floor($batch_id) + 0.5);
    }

    public static function copy_worksheet()
    {
        $work_array = [
            'eid' => ['model' => Worksheet::class, 'view' => WorksheetView::class],
            'vl' => ['model' => Viralworksheet::class, 'view' => ViralworksheetView::class],
        ];

        $date_array = ['kitexpirydate', 'sampleprepexpirydate', 'bulklysisexpirydate', 'controlexpirydate', 'calibratorexpirydate', 'amplificationexpirydate', 'datecut', 'datereviewed', 'datereviewed2', 'datecancelled', 'daterun', 'created_at'];

        ini_set("memory_limit", "-1");

        foreach ($work_array as $key => $value) {
            $model = $value['model'];
            $view = $value['view'];

            $start = $model::max('id');              

            $offset_value = 0;
            while(true)
            {
                $worksheets = $view::when($start, function($query) use ($start){
                    return $query->where('id', '>', $start);
                })->limit(self::$limit)->offset($offset_value)->get();
                if($worksheets->isEmpty()) break;

                foreach ($worksheets as $worksheet_key => $worksheet) {
                    $duplicate = $worksheet->replicate();
                    $work = new $model;                    
                    $work->fill($duplicate->toArray());
                    foreach ($date_array as $date_field) {
                        $work->$date_field = self::clean_date($worksheet->$date_field);
                    }
                    $work->id = $worksheet->id;
                    $work->save();
                }
                $offset_value += self::$limit;
                echo "Completed {$key} worksheet {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
            }
        }
    }

    public static function copy_deliveries()
    {
        ini_set("memory_limit", "-1");
        $deliveries = self::deliveries();
        $unset_array = ['synchronized', 'datesynchronized', 'submitted', 'emailsent', 'lab', 'approve', 'testsdone', 'yearofrecordset', 'monthofrecordset', 'equipmentid', 'disposable1000received', 'disposable1000damaged', 'disposable200received', 'disposable200damaged'];

        foreach ($deliveries as $key => $value) {
            $offset_value = 0;
            $start = $value['class']::max('id'); 

            while(true)
            {
                $rows = DB::connection('old')->table($value['table'])
                ->when($start, function($query) use ($start){
                    return $query->where('id', '>', $start);
                })
                ->limit(self::$limit)->offset($offset_value)->get();
                if($rows->isEmpty()) break;

                foreach ($rows as $row) {
                    $del = new $value['class'];
                    $del->fill(get_object_vars($row));
                    foreach ($unset_array as $u) {
                        unset($del->$u);
                    }
                    $del->lab_id = $row->lab;

                    foreach ($value['dates'] as $date_field) {
                        $del->$date_field = self::clean_date($del->$date_field);
                    }
                    if(isset($row->approve) && $row->approve == 'Y') $del->approve = 1;
                    if(isset($row->testsdone)) $del->tests = $row->testsdone;
                    if(isset($row->yearofrecordset)) $del->year = $row->yearofrecordset;
                    if(isset($row->monthofrecordset)) $del->month = $row->monthofrecordset;
                    if(isset($row->equipmentid)) $del->equipment_id = $row->equipmentid;
                    
                    if(isset($row->disposable1000received)) $del->{'1000disposablereceived'} = $row->disposable1000received;
                    if(isset($row->disposable1000damaged)) $del->{'1000disposabledamaged'} = $row->disposable1000damaged;
                    if(isset($row->disposable200received)) $del->{'200disposablereceived'} = $row->disposable200received;
                    if(isset($row->disposable200damaged)) $del->{'200disposabledamaged'} = $row->disposable200damaged;

                    $del->save();
                }
                $offset_value += self::$limit;
                echo "Completed {$key} {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
            }
        }
    }

    public static function copy_facility_contacts()
    {
        $contact_array = ['telephone', 'telephone2', 'fax', 'email', 'PostalAddress', 'contactperson', 'contacttelephone', 'contacttelephone2', 'physicaladdress', 'G4Sbranchname', 'G4Slocation', 'G4Sphone1', 'G4Sphone2', 'G4Sphone3', 'G4Sfax', 'ContactEmail'];
        $facilities = \App\Facility::all();
        foreach ($facilities as $key => $facility) {
            $old = \App\OldModels\Facility::find($facility->id);
            // $old = \App\OldModels\Facility::locate($facility->facilitycode)->get()->first();
            $contact = new \App\FacilityContact();
            if($old) $contact->fill($old->only($contact_array));
            $contact->facility_id = $facility->id;
            $contact->save();
        }
    }


    public static function previous_dob($class_name=null, $patient=null, $facility_id=null)
    {
        $row = $class_name::where(['patient' => $patient, 'facility_id' => $facility_id])
                    ->whereNotIn('dob', ['0000-00-00', ''])
                    ->whereNotNull('dob')
                    ->first();
        $dob = $row->dob ?? null;
        return self::clean_date($dob);
    }


    public static function clean_date($mydate)
    {
        $mydate = preg_replace("/[^<0-9-\/]/", "", $mydate);
        if(!$mydate || $mydate == '0000-00-00' || $mydate == '(NULL)') return null;

        try {
            $my = Carbon::parse($mydate);
            return $my->toDateString();
        } catch (Exception $e) {
            return null;
        }
    }

    public static function clean_createdat($mydate)
    {
        if(!$mydate) return null;

        try {
            $my = Carbon::parse($mydate);
            return $my->toDateString() . ' 00:00:01';
        } catch (Exception $e) {
            return null;
        }
    }

    public static function clean_preferred_language($val)
    {
        if(!$val) return null;

        $val = (int) $val;
        if($val == 0) return null;
        return $val;
    }


    public static function calculate_dob($datecollected, $years, $months, $class_name=null, $patient=null, $facility_id=null)
    {
        // if(Carbon::createFromFormat('Y-m-d', $date_collected) !== false){            
        //     $dc = Carbon::createFromFormat('Y-m-d', $date_collected);
        //     $dc->subYears($years);
        //     $dc->subMonths($months);
        //     return $dc->toDateString();
        // }
        // return null;

        
        $datecollected = self::clean_date($datecollected);

        if((!$years && !$months) || !$datecollected || $datecollected == '0000-00-00'){
            if(!$class_name) return null;
            $row = $class_name::where(['patient' => $patient, 'facility_id' => $facility_id])
                        ->where('age', '!=', 0)
                        ->whereNotIn('datecollected', ['0000-00-00', ''])
                        ->whereNotNull('datecollected')
                        ->get()->first();
            if($row){
                $mydate = self::clean_date($row->datecollected);
                if(!$mydate) return null;

                if($class_name == "App\OldModels\ViralsampleView"){ 
                    return self::calculate_dob($row->datecollected, $row->age, 0);
                }
                return self::calculate_dob($row->datecollected, 0, $row->age);
            }   
            return null;         
        }

        try {           
            $dc = Carbon::createFromFormat('Y-m-d', $datecollected);
            $dc->subYears($years);
            $dc->subMonths($months);
            return $dc->toDateString();
            
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    public static function resolve_gender($value, $class_name=null, $patient=null, $facility_id=null)
    {
        $value = trim($value);
        $value = strtolower($value);
        if(str_contains($value, ['m', '1'])){
            return 1;
        }
        else if(str_contains($value, ['f', '2'])){
            return 2;
        }

        else{
            $row = $class_name::where(['patient' => $patient, 'facility_id' => $facility_id])
                        ->whereRaw("(gender = 'M' or gender = 'F')")->get()->first();
            if($row) return self::resolve_gender($row->gender);
            return 3;
        }
    }


    public static function samples_arrays()
    {
        return [

            'batch' => ['highpriority', 'input_complete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'synched', 'datesynched' ],

            'mother' => ['hiv_status', 'facility_id', 'ccc_no', 'synched', 'datesynched' ],

            'patient' => ['patient', 'patient_name', 'sex', 'facility_id', 'dob', 'entry_point', 'hei_validation', 'enrollment_ccc_no', 'enrollment_status', 'referredfromsite', 'otherreason', 'dateinitiatedontreatment', 'caregiver_phone', 'patient_phone_no', 'preferred_language', 'synched', 'datesynched' ],

            'sample' => ['id', 'amrs_location', 'provider_identifier', 'order_no', 'sample_type', 'receivedstatus', 'age', 'redraw', 'pcrtype', 'regimen', 'mother_prophylaxis', 'feeding', 'spots', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'worksheet_id', 'flag', 'run', 'repeatt', 'eqa', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'synched', 'datesynched', 'mother_last_result', 'mother_age', 'time_result_sms_sent' ], 
        ];
    }

    public static function viralsamples_arrays()
    {
        return [

            'batch' => ['highpriority', 'input_complete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'synched', 'datesynched' ],

            'patient' => ['patient', 'sex', 'patient_name', 'facility_id', 'patient', 'dob', 'initiation_date', 'caregiver_phone', 'patient_phone_no', 'preferred_language', 'synched', 'datesynched' ],

            'sample' => ['id', 'amrs_location', 'provider_identifier', 'order_no', 'vl_test_request_no', 'receivedstatus', 'age', 'age_category', 'justification', 'other_justification', 'sampletype', 'prophylaxis', 'regimenline', 'pmtct', 'dilutionfactor', 'dilutiontype', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'rcategory', 'units', 'worksheet_id', 'flag', 'run', 'repeatt', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'synched', 'datesynched', 'time_result_sms_sent' ],
            
        ];
    }

    public static function deliveries()
    {
        return [
            'taqmanprocurements' => ['table' => 'taqmanprocurement', 'class' => \App\Taqmanprocurement::class, 'dates' => ['datesubmitted']],
            'abbotprocurements' => ['table' => 'abbottprocurement', 'class' => \App\Abbotprocurement::class, 'dates' => ['datesubmitted']],

            'lab_equipment_trackers' => ['table' => 'lab_equipment_tracker', 'class' => \App\LabEquipmentTracker::class, 'dates' => ['datesubmitted', 'dateemailsent', 'datebrokendown', 'datereported', 'datefixed']],
            'lab_performance_trackers' => ['table' => 'lab_perfomance_tracker', 'class' => \App\LabPerformanceTracker::class, 'dates' => ['datesubmitted', 'dateemailsent']],

            'abbotdeliveries' => ['table' => 'abbottdeliveries', 'class' => \App\Abbotdeliveries::class, 'dates' => ['datereceived', 'dateentered']],
            'taqmandeliveries' => ['table' => 'taqmandeliveries', 'class' => \App\Taqmandeliveries::class, 'dates' => ['datereceived', 'dateentered']],

            // 'requisitions' => ['table' => 'taqmandeliveries', 'dates' => ['datereceived', 'dateentered']],
        ];
    }




}
