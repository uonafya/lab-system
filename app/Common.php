<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use App\Mail\CriticalResults;
use App\Mail\EidDispatch;
use App\Mail\VlDispatch;
use App\Mail\UrgentCommunication;
use App\Mail\NoDataReport;
use App\Mail\LabTracker;
use Carbon\Carbon;
use Exception;
use App\EquipmentMailingList as MailingList;

use App\Synch;

class Common
{
	public static $sms_url = 'http://sms.southwell.io/api/v1/messages';
	// public static $mlab_url = 'http://197.248.10.20:3001/api/results/results';
	public static $mlab_url = 'https://api.mhealthkenya.co.ke/api/vl_results';

	public static $my_classes = [
		'eid' => [
			'misc_class' => \App\Misc::class,
			'sample_class' => \App\Sample::class,
			'sampleview_class' => \App\SampleView::class,
			'batch_class' => \App\Batch::class,
			'worksheet_class' => \App\Worksheet::class,
			'patient_class' => \App\Patient::class,


			'view_table' => 'samples_view',
			'worksheets_table' => 'worksheets',
			'batch_table' => 'batches',
			'sample_table' => 'samples',
		],

		'vl' => [
			'misc_class' => \App\MiscViral::class,
			'sample_class' => \App\Viralsample::class,
			'sampleview_class' => \App\ViralsampleView::class,
			'batch_class' => \App\Viralbatch::class,
			'worksheet_class' => \App\Viralworksheet::class,
			'patient_class' => \App\Viralpatient::class,


			'view_table' => 'viralsamples_view',
			'worksheets_table' => 'viralworksheets',
			'batch_table' => 'viralbatches',
			'sample_table' => 'viralsamples',
		],
	];

    public static function test_email()
    {
        Mail::to(['joelkith@gmail.com'])->send(new TestMail());
    }

    public static function get_misc_class($type)
    {
    	if($type == 'eid') return \App\Misc::class;
    	return \App\MiscViral::class;
    }

    public static function get_batch_class($type)
    {
    	if($type == 'eid') return \App\Batch::class;
    	return \App\Viralbatch::class;
    }

    public static function get_patient_class($type)
    {
    	if($type == 'eid') return \App\Patient::class;
    	return \App\Viralpatient::class;
    }

	public static function get_days($start, $finish, $with_holidays=true)
	{
		if(!$start || !$finish) return null;
		// $workingdays= self::working_days($start, $finish);
		$s = Carbon::parse($start);
		$f = Carbon::parse($finish);
		$totaldays = $s->diffInWeekdays($f, false);

		if($totaldays < 0) return null;

		$start_time = strtotime($start);
		$month = (int) date('m', $start_time);
		$holidays = self::get_holidays($month);
		
		if($with_holidays) $totaldays -= $holidays;
		if ($totaldays < 1)		$totaldays=1;
		return $totaldays;
	}

    public static function get_holidays($month)
	{
		$holidays = [0 => 10, 1 => 1, 4 => 2, 5 => 1, 6 => 1, 8 => 1, 10 => 1, 12 => 3];
		return $holidays[$month] ?? 0;
	}

	public static function save_tat5($type)
	{
        ini_set("memory_limit", "-1");
        $batch_model = self::$my_classes[$type]['batch_class'];
		$batches = $batch_model::where(['batch_complete' => 1])->whereNull('tat5')->get();
		// $batches = $batch_model::where(['batch_complete' => 1])->get();

		foreach ($batches as $key => $batch) {
			$batch->tat5 = self::get_days($batch->datereceived, $batch->datedispatched, false);
			$batch->save();
		}
	}

	// $view_model will be \App\SampleView::class || \App\ViralsampleView::class
	// $sample_model will be \App\Sample::class || \App\Viralsample::class
	public function save_tat($view_model, $sample_model, $batch_id = NULL)
	{
        ini_set("memory_limit", "-1");
		$samples = $view_model::where(['batch_complete' => 1])
		->whereRaw("(synched = 0 or synched = 2 or (synched=1 and tat4=0))")
		->when($batch_id, function($query) use ($batch_id){
			return $query->where(['batch_id' => $batch_id]);
		})
		->get();

		foreach ($samples as $key => $sample) {
			$tat1 = self::get_days($sample->datecollected, $sample->datereceived);
			$tat2 = self::get_days($sample->datereceived, $sample->datetested);
			$tat3 = self::get_days($sample->datetested, $sample->datedispatched);
			$tat4 = self::get_days($sample->datecollected, $sample->datedispatched);
			// $tat4 = $tat1 + $tat2 + $tat3;
			$data = ['tat1' => $tat1, 'tat2' => $tat2, 'tat3' => $tat3, 'tat4' => $tat4];

			if($sample_model == "App\\Viralsample"){
				$viral_data = [
					'justification' => $this->set_justification($sample->justification),
					'prophylaxis' => $this->set_prophylaxis($sample->prophylaxis),
					'age_category' => $this->set_age_cat($sample->age),
				];
				$viral_data = array_merge($viral_data, $this->set_rcategory($sample->result, $sample->repeatt));
				$data = array_merge($data, $viral_data);				
			}
			if($sample->synched == 1) $data['synched'] = 2;
			$sample_model::where('id', $sample->id)->update($data);
		}
	}



	// $view_model will be \App\SampleView::class || \App\ViralsampleView::class
	// $sample_model will be \App\Sample::class || \App\Viralsample::class
	public function compute_tat($view_model, $sample_model)
	{
        ini_set("memory_limit", "-1");
        // $offset_value = 50000;
        $offset_value = 0;
        while(true){

			$samples = $view_model::where(['batch_complete' => 1])
			->limit(5000)->offset($offset_value)
			->get();
			if($samples->isEmpty()) break;

			foreach ($samples as $key => $sample) {
				$tat1 = self::get_days($sample->datecollected, $sample->datereceived);
				$tat2 = self::get_days($sample->datereceived, $sample->datetested);
				$tat3 = self::get_days($sample->datetested, $sample->datedispatched);
				$tat4 = self::get_days($sample->datecollected, $sample->datedispatched);
				// $tat4 = $tat1 + $tat2 + $tat3;
				$data = ['tat1' => $tat1, 'tat2' => $tat2, 'tat3' => $tat3, 'tat4' => $tat4];

				if($sample_model == "App\\Viralsample"){
					$viral_data = [
						'justification' => $this->set_justification($sample->justification),
						'prophylaxis' => $this->set_prophylaxis($sample->prophylaxis),
						'age_category' => $this->set_age_cat($sample->age),
					];
					$viral_data = array_merge($viral_data, $this->set_rcategory($sample->result, $sample->repeatt));	
					$data = array_merge($data, $viral_data);				
				}
				$sample_model::where('id', $sample->id)->update($data);
			}
	        $offset_value += 5000;
			echo "Completed clean at {$offset_value} " . date('d/m/Y h:i:s a', time()). "\n";
        }
	}



	// $view_model will be \App\SampleView::class || \App\ViralsampleView::class
	// $sample_model will be \App\Sample::class || \App\Viralsample::class
	public function compute_tat_sample($view_model, $sample_model, $sample_id=null)
	{
        ini_set("memory_limit", "-1");
        $offset_value = 0;

        $sample = $view_model::find($sample_id);

		$tat1 = self::get_days($sample->datecollected, $sample->datereceived);
		$tat2 = self::get_days($sample->datereceived, $sample->datetested);
		$tat3 = self::get_days($sample->datetested, $sample->datedispatched);
		$tat4 = self::get_days($sample->datecollected, $sample->datedispatched);
		// $tat4 = $tat1 + $tat2 + $tat3;
		$data = ['tat1' => $tat1, 'tat2' => $tat2, 'tat3' => $tat3, 'tat4' => $tat4];

		if($sample_model == "App\\Viralsample"){
			$viral_data = [
				'justification' => $this->set_justification($sample->justification),
				'prophylaxis' => $this->set_prophylaxis($sample->prophylaxis),
				'age_category' => $this->set_age_cat($sample->age),
			];
			$viral_data = array_merge($viral_data, $this->set_rcategory($sample->result, $sample->repeatt));	
			$data = array_merge($data, $viral_data);				
		}
		$sample_model::where('id', $sample->id)->update($data);

		dd($data);
	}


	public static function check_worklist($view_model, $worklist_id=null)
	{	
		if(!$worklist_id) return null;
        $samples = $view_model::where('worksheet_id', $worklist_id)
        	->where('site_entry', 2)
        	->whereNull('result')
        	->get();

        if($samples->isEmpty()){
        	$worklist = \App\Worklist::find($worklist_id);
        	$worklist->status_id = 3;
        	$worklist->pre_update();
        }
	}

	public static function input_complete_batches($type)
	{
		if($type == 'eid'){
			$batch_model = \App\Batch::class;
		}else{
			$batch_model = \App\Viralbatch::class;
		}
		$batch_model::where(['input_complete' => false])->update(['input_complete' => true]);
		$batch_model::whereNull('input_complete')->update(['input_complete' => true]);
		return "Batches of {$type} have been marked as input complete";
	}

	public static function check_batches($type)
	{
		if($type == 'eid'){
			$batch_model = \App\Batch::class;
			$misc_model = \App\Misc::class;
			$sample_model = \App\Sample::class;
		}else{
			$batch_model = \App\Viralbatch::class;
			$misc_model = \App\MiscViral::class;
			$sample_model = \App\Viralsample::class;
		}

		$sample_model::whereNull('repeatt')->update(['repeatt' => 0]);

		$batches = $batch_model::select('id')->where(['input_complete' => true, 'batch_complete' => 0])->get();
		foreach ($batches as $key => $batch) {
			$str = $misc_model::check_batch($batch->id);
			// if($str) echo $str . "\n";
		}
	}

	public static function delete_delayed_batches($type)
	{
		$batch_model = self::get_batch_class($type);
        $min_time = date('Y-m-d', strtotime("-14 days"));
        if(env('APP_LAB') == 3) $min_time = date('Y-m-d', strtotime("-28 days"));

		$batches = $batch_model::where(['site_entry' => 1, 'batch_complete' => 0, 'lab_id' => env('APP_LAB')])->where('created_at', '<', $min_time)->whereNull('datereceived')->whereNull('datedispatched')->get();

		foreach ($batches as $batch) {
			$batch->batch_delete();
		}
	}

    public static function delete_folder($path)
    {
        if(!ends_with($path, '/')) $path .= '/';
        $files = scandir($path);
        if(!$files) rmdir($path);
        else{
            foreach ($files as $file) {
            	if($file == '.' || $file == '..') continue;
            	$a=true;
                if(is_dir($path . $file)) self::delete_folder($path . $file);
                else{
                	unlink($path . $file);
                }              
            }
            rmdir($path);
        }
    }

    public static function dispatch_batch($batch, $view_name=null)
    {
    	$facility = $batch->facility; 
    	
        $mail_array = array('joelkith@gmail.com', 'tngugi@clintonhealthaccess.org', 'baksajoshua09@gmail.com');
        if(env('APP_ENV') == 'production') $mail_array = $facility->email_array;
        if(!$mail_array) return null;

        if(get_class($batch) == "App\\Batch") $mail_class = EidDispatch::class; 

        if(get_class($batch) == "App\\Viralbatch") $mail_class = VlDispatch::class;

        try {
        	if($view_name) $new_mail = new $mail_class($batch, $view_name);
        	else{
        		$new_mail = new $mail_class($batch);
        	}
        	Mail::to($mail_array)->bcc(['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke'])
        	->send($new_mail);
        	// $batch->save();
        } catch (Exception $e) {
        	
        }
    }

    public static function dispatch_results($type = 'eid')
    {
    	ini_set('memory_limit', "-1");
		if($type == 'eid'){
			$batch_model = \App\Batch::class;
		}else{
			$batch_model = \App\Viralbatch::class;
		}

		$min_date = date('Y-m-d', strtotime('-1 month'));

		$batches = $batch_model::where('batch_complete', 1)
		->where('sent_email', 0)
		->where('datedispatched', '>', $min_date)
		->get();

		foreach ($batches as $batch) {

            $batch->sent_email = true;
            $batch->dateemailsent = date('Y-m-d');
            $batch->save();

		 	self::dispatch_batch($batch);
		} 
    }

    public static function dup_worksheet_rows(&$doubles, &$sample_array, $sample_id, $interpretation)
    {
    	if(is_numeric($sample_id) && $sample_id > 100){
	        if(isset($sample_array[$sample_id])){
	            $doubles[] = ['duplicate lab id' => $sample_id, 'duplicate result' => $sample_array[$sample_id]];
	            $doubles[] = ['duplicate lab id' => $sample_id, 'duplicate result' => $interpretation];
	        }else{
	            $sample_array[$sample_id] = $interpretation;
	        }
        }
    }

    public static function create_facility_users()
    {
    	$facilities = \App\Facility::whereRaw("id not in (select facility_id from users where user_type_id = 5)")->get();
    	foreach ($facilities as $facility) {
    		$u = \App\User::create([
                'user_type_id' => 5,
                'surname' => '',
                'oname' => '',
                'lab_id' => env('APP_LAB'),
                'facility_id' => $facility->id,
                'email' => 'facility' . $facility->id . '@nascop-lab.com',
                'password' => encrypt($facility->name)
    		]);
    	}

    	$facilities = \App\Facility::whereRaw("id not in (select facility_id from facility_contacts)")->get();
    	foreach ($facilities as $facility) {
	        $contact_array = ['telephone', 'telephone2', 'fax', 'email', 'PostalAddress', 'contactperson', 'contacttelephone', 'contacttelephone2', 'physicaladdress', 'G4Sbranchname', 'G4Slocation', 'G4Sphone1', 'G4Sphone2', 'G4Sphone3', 'G4Sfax', 'ContactEmail'];

	        $contact = new FacilityContact();
	        $contact->fill($facility->only($contact_array));
	        $contact->facility_id = $facility->id;
	        $contact->save();
    	}
    }

    public static function nhrl($type)
    {
    	ini_set('memory_limit', "-1");

    	$batch_model = self::$my_classes[$type]['batch_class'];
    	$sample_model = self::$my_classes[$type]['sample_class'];

    	$batches = $batch_model::where(['synched' => 5])->whereIn('lab_id', [10])->get();

    	foreach ($batches as $batch) {
    		$sample = $sample_model::where(['batch_id' => $batch->id, 'synched' => 5])->first();
    		if(!$sample){
	    		$batch->synched=0;
	    		$batch->save();    			
    		}
    	}

    	$batches = $batch_model::where(['synched' => 5])->whereIn('lab_id', [7])->get();

    	foreach ($batches as $batch) {
    		$sample = $sample_model::where(['batch_id' => $batch->id, 'synched' => 5])->update(['synched' => 0]);
    		$batch->synched=0;
    		$batch->save();   
    	}
    }

    public static function transfer_delayed_samples($type, $not_received=true)
    {
    	ini_set('memory_limit', "-1");

    	$batch_model = self::$my_classes[$type]['batch_class'];
    	$batch_table = self::$my_classes[$type]['batch_table'];

    	$sample_class = self::$my_classes[$type]['sample_class'];
    	$sample_table = self::$my_classes[$type]['sample_table'];

    	$where_raw = '';

    	if($type == 'eid'){
    		if(in_array(env('APP_LAB'), \App\Lookup::$double_approval)){
    			$where_raw = "( receivedstatus=2 OR  (result > 0 AND (repeatt = 0 or repeatt is null) AND approvedby IS NOT NULL AND approvedby2 IS NOT NULL) )";
    		}
    		else{
    			$where_raw = "( receivedstatus=2 OR  (result > 0 AND (repeatt = 0 or repeatt is null) AND approvedby IS NOT NULL) )";
    		}
    	}
    	else{
    		if(in_array(env('APP_LAB'), \App\Lookup::$double_approval)){
    			$where_raw = "( (receivedstatus=2 and repeatt=0) OR  (result IS NOT NULL AND result != 'Failed' AND result != '' AND (repeatt = 0 or repeatt is null) AND ((approvedby IS NOT NULL AND approvedby2 IS NOT NULL) or (dateapproved IS NOT NULL AND dateapproved2 IS NOT NULL)) ))";
    		}
    		else{
    			$where_raw = "( (receivedstatus=2 and repeatt=0) OR  (result IS NOT NULL AND result != 'Failed' AND result != '' AND (repeatt = 0 or repeatt is null) AND (approvedby IS NOT NULL OR dateapproved IS NOT NULL)) )";
    		}
    	}


        $batches = $batch_model::selectRaw("{$batch_table}.*, COUNT({$sample_table}.id) AS `samples_count`")
            ->join("{$sample_table}", "{$batch_table}.id", '=', "{$sample_table}.batch_id")
            ->where(['batch_complete' => 0, "{$batch_table}.lab_id" => env('APP_LAB')])
            ->whereRaw($where_raw)
            ->groupBy("{$batch_table}.id")
            // ->having('samples_count', '>', 0)
            ->havingRaw("COUNT({$sample_table}.id) > 0")
            ->get();

    	foreach ($batches as $batch) {
    		if($not_received) $samples = $sample_class::where(['batch_id' => $batch->id])->whereNull('receivedstatus')->get();
    		else{
    			$samples = $sample_class::where(['batch_id' => $batch->id, 'repeatt' => 0])->whereNull('result')->get();
    		}
    		if($samples->count() > 0){
		        unset($batch->samples_count);
    			$sample_ids = $samples->pluck('id')->toArray();
    			echo "{$type} batch {$batch->id} \n ";
    			$batch->transfer_samples($sample_ids, 'new_facility');
    		}
    	}
    }


    public static function reject_delayed_samples($type)
    {
    	ini_set('memory_limit', "-1");

    	$sampleview_class = self::$my_classes[$type]['sampleview_class'];
    	$sample_class = self::$my_classes[$type]['sample_class'];

    	if($type == 'eid'){
    		$days = 14;
    		$rej = 18;
    	}
    	else{
    		$days = 28;
    		$rej = 17;
    	}

    	$sample_class = self::$my_classes[$type]['sample_class'];
    	$sample_table = self::$my_classes[$type]['sample_table'];

        $samples = $sampleview_class::whereNull('receivedstatus')
        	->where(['batch_complete' => 0, 'lab_id' => env('APP_LAB')])
        	->where('created_at', '<', date('Y-m-d H:i:s', strtotime("-{$days} days")))
            ->get();

        $sample_ids = $samples->pluck(['id'])->toArray();
        $sample_class::whereIn('id', $sample_ids)->update(['receivedstatus' => 2, 'rejectedreason' => $rej, 'updated_at' => date('Y-m-d H:i:s')]);
    }

	public static function fix_no_age($type)
	{
    	ini_set('memory_limit', "-1");
		if($type == 'eid'){
			$sample_model = \App\Sample::class;
			$view_model = \App\SampleView::class;
			$func_name = 'calculate_age';
		}else{
			$sample_model = \App\Viralsample::class;
			$view_model = \App\ViralsampleView::class;
			$func_name = 'calculate_viralage';
		}

		$samples = $view_model::select('id', 'dob', 'datecollected')
								->whereNotNull('dob')
								->whereRaw("(age is null or age=0)")
								->get();

		foreach ($samples as $sample) {
			$s = $sample_model::find($sample->id);
			$s->age = \App\Lookup::$func_name($sample->datecollected, $sample->dob);
			$s->pre_update();
		}
	}


	public static function worksheet_date($date_tested, $created_at, $default=null)
	{
		if(!$default) $default = date('Y-m-d');

		if((strtotime($date_tested) > strtotime($created_at)) && (strtotime($date_tested) < strtotime('now'))) return $date_tested;
		return $default;
	}

	public static function critical_results($type)
	{
		$sampleview_class = self::$my_classes[$type]['sampleview_class'];
		$view_table = self::$my_classes[$type]['view_table'];
		$dt = date('Y-m-d', strtotime('-1 day'));
		$q = 'rcategory IN (3, 4)';
		$lab = \App\Lab::find(env('APP_LAB'));
		if($type == 'eid') $q = 'result=2';

		$facilities = Facility::whereRaw("id IN (SELECT DISTINCT facility_id FROM {$view_table} WHERE datedispatched = '{$dt}' AND repeatt=0 AND {$q})")->get();

		foreach ($facilities as $key => $facility) {

	        $mail_array = ['joelkith@gmail.com', 'tngugi@clintonhealthaccess.org', 'baksajoshua09@gmail.com'];
	        if(env('APP_ENV') == 'production') $mail_array = $facility->email_array;

			$samples = $sampleview_class::whereRaw($q)
						->where(['datedispatched' => $dt, 'facility_id' => $facility->id, 'repeatt' => 0])
						->get();

			try {				
				$comm = new CriticalResults($facility, $type, $samples, $dt);
				Mail::to($mail_array)->cc([$lab->email])->bcc(['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke'])->send($comm);
			} catch (Exception $e) {
				dd($e->getMessage());
			}
		}
	}






	public static function no_data_report($type)
	{
		$noage = self::no_data($type, 'age');
		$nogender = self::no_data($type, 'gender');

		$comm = new NoDataReport($type, $noage, $nogender);

		Mail::to(['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke'])->send($comm);
	}

	public static function no_data($type, $param)
	{
    	ini_set('memory_limit', "-1");

		if($type == 'eid'){
			$view_model = \App\SampleView::class;
		}else{
			$view_model = \App\ViralsampleView::class;
		}

		$samples = $view_model::selectRaw("id as 'Lab ID', site_entry, facilitycode as 'MFL Code', facilityname AS 'Facility', patient, sex, age, dob, datecollected, datereceived, datetested, datedispatched ")
				->where('facility_id', '!=', 7148)
				->where('repeatt', 0)
				->where('datereceived', '>', '2018-01-01')
				->where('lab_id', env('APP_LAB'))	
				->when(true, function($query) use ($param){
					if($param == 'age') return $query->whereRaw("(age is null or age=0)");
					return $query->where('sex', 3);
				})			
				->get();

		$filename = storage_path("app/" . $type . "_no_" . $param . "_data_report.csv");
        if(file_exists($filename)) unlink($filename);

        $fp = fopen($filename, 'w');

        fputcsv($fp, ['Lab ID', 'Entry Type', 'MFL Code', 'Facility', 'Patient', 'Sex', 'Age', 'DOB', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched']);

        foreach ($samples as $key => $value) {
        	$val = $value->toArray();
        	// $val = get_object_vars($value);
        	$val['sex'] = $value->gender;
        	$val['site_entry'] = 'Lab Entry';
        	if($value->site_entry == 1) $val['site_entry'] = 'Site Entry';
        	fputcsv($fp, $val);
        }
        fclose($fp);
        return $filename;
	}

    // public static function send_communication()
    // {
    //     ini_set("memory_limit", "-1");
    //     $facilities = \App\Facility::where('flag', 1)->get();

    //     foreach ($facilities as $key => $facility) {
    //     	$mail_array = $facility->email_array;
    //     	// $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
    //     	$comm = new UrgentCommunication;
    //     	try {
	   //      	Mail::to($mail_array)->bcc(['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@gmail.com'])
	   //      	->send($comm);
	   //      } catch (Exception $e) {
        	
	   //      }
    //     	// break;
    //     }
    // }

    public static function send_communication()
    {
        $emails = \App\Email::where('sent', false)->where('time_to_be_sent', '<', date('Y-m-d H:i:s'))->get();

        foreach ($emails as $email) {
        	$email->dispatch();
        }
    }

    public static function correct_facility($mfl)
    {
        ini_set("memory_limit", "-1");

        $classes = \App\Synch::$synch_arrays;

        $facility = \App\Facility::locate($mfl)->first();

        foreach ($classes as $c) {

	        $sampleview_class = $c['sampleview_class'];
	        $patient_class = $c['patient_class'];
	        $batch_class = $c['batch_class'];

	        $samples = $sampleview_class::where('patient', 'like', "{mfl}%")->where('facility_id', '!=', $facility->id)->get();

	        foreach ($samples as $sample) {
	        	$batch = $batch_class::find($sample->batch_id);
	        	$batch->facility_id = $facility->id;
	        	$batch->pre_update();

	        	$patient = $patient_class::find($sample->patient_id);
	        	$patient->facility_id = $facility->id;
	        	$patient->pre_update();
	        }
        }
    }

    public static function find_facility_mismatch()
    {
        ini_set("memory_limit", "-1");
        $facilities = \App\OldModels\Facility::all();

        $classes = [
        	\App\Mother::class,
        	\App\Batch::class,
        	\App\Patient::class,


        	\App\Viralbatch::class,
        	\App\Viralpatient::class,
        ];

        $conflict = [];

        foreach ($facilities as $facility) {
        	$fac = \App\Facility::locate($facility->facilitycode)->first();
        	if(!$fac) continue;
        	if($facility->facilitycode == 0) continue;
         	// if($fac->id < 55000) continue;

        	if($fac->id != $facility->ID){

        		// dd([$fac->toArray(), $facility->toArray()]);

        		$new_fac = \App\ViewFacility::find($facility->ID);
        		// if($new_fac) dd([$fac->toArray(), $facility->toArray(), $new_fac->toArray()]);
        		if($new_fac){
        			$conflict[] = [
        				'id' => $new_fac->id,
        				'code' => $new_fac->facilitycode,
        				'name' => $new_fac->name,
        				'county' => $new_fac->county,
        			];
        			continue;
        		}

        		foreach ($classes as $class) {
        			$class::where(['facility_id' => $facility->ID, 'synched' => 1])->update(['facility_id' => $fac->id, 'synched' => 2]);
        			$class::where(['facility_id' => $facility->ID])->update(['facility_id' => $fac->id]);
        		}

        		if(env('APP_LAB') == 5) \App\Cd4Sample::where(['facility_id' => $facility->ID])->update(['facility_id' => $fac->id]);
        	}
        }

        dd($conflict);
    }


    public static function change_facility_id($old_id, $new_id, $also_facility=false, $created_at=false)
    {
        $classes = [
        	\App\Mother::class,
        	\App\Batch::class,
        	\App\Patient::class,


        	\App\Viralbatch::class,
        	\App\Viralpatient::class,
        ];

		if($also_facility){
			\App\Facility::where(['id' => $old_id])->update(['id' => $new_id]);
			\App\User::where(['facility_id' => $old_id])->update(['facility_id' => $new_id]);
			\App\FacilityContact::where(['facility_id' => $old_id])->update(['facility_id' => $new_id]);
		}
		
		foreach ($classes as $key => $class) {
			if($key < 5) $class::where(['facility_id' => $old_id, 'synched' => 1])
				->when($created_at, function($query) use ($created_at){
					return $query->whereDate('created_at', '>', $created_at);
				})
				->update(['facility_id' => $new_id, 'synched' => 2]);

			$class::where(['facility_id' => $old_id])->update(['facility_id' => $new_id]);
		}

		if(env('APP_LAB') == 5) \App\Cd4Sample::where(['facility_id' => $old_id])->update(['facility_id' => $new_id]);
    }


    public static function change_facility_id_two($old_id, $new_id, $also_facility=false, $created_at=false)
    {
        $classes = [
        	\App\Mother::class,
        	\App\Batch::class,
        	\App\Patient::class,


        	\App\Viralbatch::class,
        	\App\Viralpatient::class,
        ];

		if($also_facility){
			\App\Facility::where(['id' => $old_id])->update(['id' => $new_id]);
			\App\User::where(['facility_id' => $old_id])->update(['facility_id' => $new_id]);
			\App\FacilityContact::where(['facility_id' => $old_id])->update(['facility_id' => $new_id]);
		}

		foreach ($classes as $key => $class) {
			if($key < 5) $class::where(['facility_id' => $old_id, 'synched' => 1])
				->when($created_at, function($query) use ($created_at){
					return $query->whereBetween('created_at', $created_at);
				})
				->update(['facility_id' => $new_id, 'synched' => 2]);

			$class::where(['facility_id' => $old_id])
				->when($created_at, function($query) use ($created_at){
					return $query->whereBetween('created_at', $created_at);
				})->update(['facility_id' => $new_id]);
		}

		if(env('APP_LAB') == 5) \App\Cd4Sample::where(['facility_id' => $old_id])->update(['facility_id' => $new_id]);
    }

    public static function send_lab_tracker($year, $previousMonth) {
    	$data = Random::__getLablogsData($year, $previousMonth);

    	$mailinglist = ['joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com'];
        $mainRecepient = ['baksajoshua09@gmail.com'];
        if(env('APP_ENV') == 'production') {
        	$mainRecepient = MailingList::where('type', '=', 1)->pluck('email')->toArray(); 
    		$mailinglist = MailingList::where('type', '=', 2)->pluck('email')->toArray();
        }
        
        if(!$mainRecepient) 
        	return null;

        try {
        	Mail::to($mainRecepient)->cc($mailinglist)->bcc(['joshua.bakasa@dataposit.co.ke', 'joel.kithinji@dataposit.co.ke','bakasajoshua09@gmail.com'])
        	->send(new LabTracker($data));
        	$allemails = array_merge($mainRecepient, $mailinglist);
        	MailingList::whereIn('email', $allemails)->update(['datesent' => date('Y-m-d')]);
        } catch (Exception $exception) {
        	\Log::error($exception);
        	// print_r($exception);
        }
    }

    public static function transferconsumptions() {
    	$kits = \App\Kits::get();
    	$kitArray = [];
    	// Getting first the abbott consumptions
    	$abbottconsumptions = \App\Abbotprocurement::get();
    	foreach ($abbottconsumptions as $key => $consumption) {
    		foreach ($kits as $key => $kit) {
	    		if ($kit->machine_id == 2) {
	    			$ending = 'ending'.$kit->alias;
		    		$wasted = 'wasted'.$kit->alias;
		    		$issued = 'issued'.$kit->alias;
		    		$request = 'request'.$kit->alias;
		    		$pos = 'pos'.$kit->alias;
		    		$kitArray[] = [
		    			'month' => $consumption->month,
		    			'year' => $consumption->year,
						'testtype' => $consumption->testtype,
						'kit_id' => $kit->id,
						'ending' => $consumption->$ending,
						'wasted' => $consumption->$wasted,
						'issued' => $consumption->$issued,
						'pos' => $consumption->$pos,
						'request' => $consumption->$request,
						'datesubmitted' => $consumption->datesubmitted,
						'submittedby' => $consumption->submittedby,
						'lab_id' => $consumption->lab_id,
						'comments' => $consumption->comments,
						'issuedcomments' => $consumption->issuedcomments,
						'approve' => $consumption->approve,
						'disapprovereason' => $consumption->disapprovereason,
						'synched' => $consumption->synched,
						'datesynched' => $consumption->datesynched,
						// 'deleted_at' => $consumption->deleted_at,
						'created_at' => $consumption->created_at,
						'updated_at' => $consumption->updated_at,
		    		];
	    		}
	    	}
		}

		// Finally getting the Roche consumptions
		$months = [1,2,3,4,5,6,7,8,9,10,11,12];
		$years = \App\Taqmanprocurement::selectRaw("max(year) as maximum, min(year) minimum")->first();
		for ($i=$years->minimum; $i <=$years->maximum ; $i++) {
			foreach ($months as $key => $month) {
				$consumptions = \App\Taqmanprocurement::where('year', '=', $i)->where('month', '=', $month)->get();
				$vlsamples = \App\Viralsample::selectRaw("count(if(viralworksheets.machine_type = 1, 1, null)) as `taqman`, count(if(viralworksheets.machine_type = 3, 1, null)) as `C8800`")
							->join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples.worksheet_id')
							->whereIn('machine_type', [1,3])
							->whereYear('datetested', $i)->whereMonth('datetested', $month)->first();
				$eidsamples = \App\Sample::selectRaw("count(if(worksheets.machine_type = 1, 1, null)) as `taqman`, count(if(worksheets.machine_type = 3, 1, null)) as `C8800`")
							->join('worksheets', 'worksheets.id', '=', 'samples.worksheet_id')
							->whereIn('machine_type', [1,3])
							->whereYear('datetested', $i)->whereMonth('datetested', $month)->first();
				// dd($vlsamples);
				foreach ($consumptions as $key => $consumption) {
					if ($consumption->testtype == 1)
		    			$model = $eidsamples;
		    		else if ($consumption->testtype == 2)
		    			$model = $vlsamples;
		    		if ($model->taqman == 0 && $model->C8800 == 0) {

		    		} else {
		    			$total = $model->taqman + $model->C8800;
		    			$taqmanratio = ($model->taqman / $total);
		    			$C8800ratio = ($model->C8800 / $total);
		    			if ($model->taqman > 0) {
		    				foreach ($kits as $key => $kit) {
								if ($kit->machine_id == 1) {
									$ending = 'ending'.$kit->alias;
						    		$wasted = 'wasted'.$kit->alias;
						    		$issued = 'issued'.$kit->alias;
						    		$request = 'request'.$kit->alias;
						    		$pos = 'pos'.$kit->alias;

						    		$kitArray[] = [
						    			'month' => $consumption->month,
						    			'year' => $consumption->year,
										'testtype' => $consumption->testtype,
										'kit_id' => $kit->id,
										'ending' => ($consumption->$ending * $taqmanratio),
										'wasted' => ($consumption->$wasted * $taqmanratio),
										'issued' => ($consumption->$issued * $taqmanratio),
										'pos' => ($consumption->$pos * $taqmanratio),
										'request' => ($consumption->$request * $taqmanratio),
										'datesubmitted' => $consumption->datesubmitted,
										'submittedby' => $consumption->submittedby,
										'lab_id' => $consumption->lab_id,
										'comments' => $consumption->comments,
										'issuedcomments' => $consumption->issuedcomments,
										'approve' => $consumption->approve,
										'disapprovereason' => $consumption->disapprovereason,
										'synched' => $consumption->synched,
										'datesynched' => $consumption->datesynched,
										// 'deleted_at' => $consumption->deleted_at,
										'created_at' => $consumption->created_at,
										'updated_at' => $consumption->updated_at,
						    		];
								}
							}
		    			} else if ($model->C8800 > 0) {
		    				foreach ($kits as $key => $kit) {
								if ($kit->machine_id == 3) {
									$ending = 'ending'.$kit->alias;
						    		$wasted = 'wasted'.$kit->alias;
						    		$issued = 'issued'.$kit->alias;
						    		$request = 'request'.$kit->alias;
						    		$pos = 'pos'.$kit->alias;

						    		$kitArray[] = [
						    			'month' => $consumption->month,
						    			'year' => $consumption->year,
										'testtype' => $consumption->testtype,
										'kit_id' => $kit->id,
										'ending' => ($consumption->$ending * $C8800ratio),
										'wasted' => ($consumption->$wasted * $C8800ratio),
										'issued' => ($consumption->$issued * $C8800ratio),
										'pos' => ($consumption->$pos * $C8800ratio),
										'request' => ($consumption->$request * $C8800ratio),
										'datesubmitted' => $consumption->datesubmitted,
										'submittedby' => $consumption->submittedby,
										'lab_id' => $consumption->lab_id,
										'comments' => $consumption->comments,
										'issuedcomments' => $consumption->issuedcomments,
										'approve' => $consumption->approve,
										'disapprovereason' => $consumption->disapprovereason,
										'synched' => $consumption->synched,
										'datesynched' => $consumption->datesynched,
										// 'deleted_at' => $consumption->deleted_at,
										'created_at' => $consumption->created_at,
										'updated_at' => $consumption->updated_at,
						    		];
								}
							}
		    			}		    				
		    		}
				}
			}
		}
		foreach ($kitArray as $key => $consumption) {
			\App\Consumption::create($consumption);
		}
    }

    public static function transferdeliveries() {
    	$kits = \App\Kits::get();
    	$kitArray = [];
    	// Getting first the abbott deliveries
    	$abbottdeliveries = Abbotdeliveries::get();
    	foreach ($abbottdeliveries as $key => $delivery) {
    		foreach ($kits as $key => $kit) {
	    		if ($kit->machine_id == 2) {
	    			$lotno = $kit->alias.'lotno';
    				$expiry = $kit->alias.'expiry';
	    			$received = $kit->alias.'received';
		    		$damaged = $kit->alias.'damaged';
		    		$kitArray[] = [
		    			'quarter' => $delivery->quarter,
		    			'year' => $delivery->year,
						'testtype' => $delivery->testtype,
						'kit_id' => $kit->id,
						'source' => $delivery->source,
						'labfrom' => $delivery->labfrom,
						'lotno' => $delivery->$lotno ?? NULL,
						'expiry' => $delivery->$expiry ?? NULL,
						'received' => $delivery->$received,
						'damaged' => $delivery->$damaged,
						'datereceived' => $delivery->datereceived,
						'receivedby' => $delivery->receivedby,
						'lab_id' => $delivery->lab_id,
						'enteredby' => $delivery->enteredby,
						'dateentered' => $delivery->dateentered,
						'synched' => $delivery->synched,
						'datesynched' => $delivery->datesynched,
						'deleted_at' => $delivery->deleted_at,
						'created_at' => $delivery->created_at,
						'updated_at' => $delivery->updated_at,
		    		];
	    		}
	    	}
		}

		// Finally getting the Roche deliveries
		$quarters = [1=>'(1,2,3)', 2=>'(4,5,6)', 3=>'(7,8,9)', 4=>'(10,11,12)'];
		$years = \App\Taqmandeliveries::selectRaw("max(year) as maximum, min(year) minimum")->first();
		for ($i=$years->minimum; $i <=$years->maximum ; $i++) {
			foreach ($quarters as $key => $quarter) {
				$deliveries = \App\Taqmandeliveries::where('year', '=', $i)->where('quarter', '=', $key)->get();
				$vlsamples = \App\Viralsample::selectRaw("count(if(viralworksheets.machine_type = 1, 1, null)) as `taqman`, count(if(viralworksheets.machine_type = 3, 1, null)) as `C8800`")
							->join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples.worksheet_id')
							->whereIn('machine_type', [1,3])
							->whereYear('datetested', $i)->whereRaw("month(datetested) in $quarter")->first();
				$eidsamples = \App\Sample::selectRaw("count(if(worksheets.machine_type = 1, 1, null)) as `taqman`, count(if(worksheets.machine_type = 3, 1, null)) as `C8800`")
							->join('worksheets', 'worksheets.id', '=', 'samples.worksheet_id')
							->whereIn('machine_type', [1,3])
							->whereYear('datetested', $i)->whereRaw("month(datetested) in $quarter")->first();
				
				foreach ($deliveries as $key => $delivery) {
					if ($delivery->testtype == 1)
		    			$model = $eidsamples;
		    		else if ($delivery->testtype == 2)
		    			$model = $eidsamples;

		    		if ($model->taqman == 0 && $model->C8800 == 0) {
		    			foreach ($kits as $key => $kit) {
							if ($kit->machine_id == 1) {
				    			$received = $kit->alias.'received';
					    		$damaged = $kit->alias.'damaged';
					    		$kitArray[] = [
					    			'quarter' => $delivery->quarter,
					    			'year' => $delivery->year,
									'testtype' => $delivery->testtype,
									'kit_id' => $kit->id,
									'source' => $delivery->source,
									'labfrom' => $delivery->labfrom,
									'lotno' => $delivery->lotno ?? NULL,
									'expiry' => $delivery->expiry ?? NULL,
									'received' => ($delivery->$received / 2),
									'damaged' => ($delivery->$damaged / 2),
									'datereceived' => $delivery->datereceived,
									'receivedby' => $delivery->receivedby,
									'lab_id' => $delivery->lab_id,
									'enteredby' => $delivery->enteredby,
									'dateentered' => $delivery->dateentered,
									'synched' => $delivery->synched,
									'datesynched' => $delivery->datesynched,
									'deleted_at' => $delivery->deleted_at,
									'created_at' => $delivery->created_at,
									'updated_at' => $delivery->updated_at,
					    		];
							}
						}
		    		} else {
			    		$total = $model->taqman + $model->C8800;
		    			$taqmanratio = ($model->taqman / $total);
		    			$C8800ratio = ($model->C8800 / $total);
		    			if ($model->taqman > 0) {
		    				foreach ($kits as $key => $kit) {
								if ($kit->machine_id == 1) {
					    			$received = $kit->alias.'received';
						    		$damaged = $kit->alias.'damaged';
						    		$kitArray[] = [
						    			'quarter' => $delivery->quarter,
						    			'year' => $delivery->year,
										'testtype' => $delivery->testtype,
										'kit_id' => $kit->id,
										'source' => $delivery->source,
										'labfrom' => $delivery->labfrom,
										'lotno' => $delivery->lotno ?? NULL,
										'expiry' => $delivery->expiry ?? NULL,
										'received' => $delivery->$received * $taqmanratio,
										'damaged' => $delivery->$damaged * $taqmanratio,
										'datereceived' => $delivery->datereceived,
										'receivedby' => $delivery->receivedby,
										'lab_id' => $delivery->lab_id,
										'enteredby' => $delivery->enteredby,
										'dateentered' => $delivery->dateentered,
										'synched' => $delivery->synched,
										'datesynched' => $delivery->datesynched,
										'deleted_at' => $delivery->deleted_at,
										'created_at' => $delivery->created_at,
										'updated_at' => $delivery->updated_at,
						    		];
								}
							}
		    			} else if ($model->C8800 > 0) {
		    				foreach ($kits as $key => $kit) {
								if ($kit->machine_id == 3) {
									$received = $kit->alias.'received';
						    		$damaged = $kit->alias.'damaged';
						    		$kitArray[] = [
						    			'quarter' => $delivery->quarter,
						    			'year' => $delivery->year,
										'testtype' => $delivery->testtype,
										'kit_id' => $kit->id,
										'source' => $delivery->source,
										'labfrom' => $delivery->labfrom,
										'lotno' => $delivery->lotno ?? NULL,
										'expiry' => $delivery->expiry ?? NULL,
										'received' => $delivery->$received * $taqmanratio,
										'damaged' => $delivery->$damaged * $taqmanratio,
										'datereceived' => $delivery->datereceived,
										'receivedby' => $delivery->receivedby,
										'lab_id' => $delivery->lab_id,
										'enteredby' => $delivery->enteredby,
										'dateentered' => $delivery->dateentered,
										'synched' => $delivery->synched,
										'datesynched' => $delivery->datesynched,
										'deleted_at' => $delivery->deleted_at,
										'created_at' => $delivery->created_at,
										'updated_at' => $delivery->updated_at,
						    		];
								}
							}
		    			}		    				
		    		}
				}
			}
		}
		
		foreach ($kitArray as $key => $delivery) {
			\App\Deliveries::insert($delivery);
		}
    }
}
