<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use App\Mail\EidDispatch;
use App\Mail\VlDispatch;
use App\Mail\UrgentCommunication;
use Carbon\Carbon;
use Exception;

class Common
{
	public static $sms_url = 'http://sms.southwell.io/api/v1/messages';
	public static $mlab_url = 'http://197.248.10.20:3001/api/results/results';

    public static function test_email()
    {
        Mail::to(['joelkith@gmail.com'])->send(new TestMail());
    }


	public static function get_days($start, $finish)
	{
		if(!$start || !$finish) return null;
		// $workingdays= self::working_days($start, $finish);
		$s = Carbon::parse($start);
		$f = Carbon::parse($finish);
		$workingdays = $s->diffInWeekdays($f);

		$start_time = strtotime($start);
		$month = (int) date('m', $start_time);
		$holidays = self::get_holidays($month);

		$totaldays = $workingdays - $holidays;
		if ($totaldays < 1)		$totaldays=1;
		return $totaldays;
	}

	public static function working_days($startDate,$endDate){

	    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
	    //We add one to inlude both dates in the interval.
	    $days = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;

	    $no_full_weeks = floor($days / 7);

	    $no_remaining_days = fmod($days, 7);

	    //It will return 1 if it's Monday,.. ,7 for Sunday
	    $the_first_day_of_week = date("N",strtotime($startDate));

	    $the_last_day_of_week = date("N",strtotime($endDate));
	    // echo              $the_last_day_of_week;
	    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
	    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
	    if ($the_first_day_of_week <= $the_last_day_of_week){
	        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
	        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
	    }

	    else{
	        if ($the_first_day_of_week <= 6) {
	        //In the case when the interval falls in two weeks, there will be a Sunday for sure
	            $no_remaining_days--;
	        }
	    }

	    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
		//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
	   	$workingDays = $no_full_weeks * 5;
	    if ($no_remaining_days > 0 )
	    {
	      $workingDays += $no_remaining_days;
	    }

	    //We subtract the holidays
		/*    foreach($holidays as $holiday){
	        $time_stamp=strtotime($holiday);
	        //If the holiday doesn't fall in weekend
	        if (strtotime($startDate) <= $time_stamp && $time_stamp <= strtotime($endDate) && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
	            $workingDays--;
	    }*/

	    return $workingDays;
	}

    public static function get_holidays($month)
	{
		switch ($month) {
			case 0:
				$totalholidays=10;
				break;
			case 1:
				$totalholidays=1;
				break;
			case 4:
				$totalholidays=2;
				break;
			case 5:
				$totalholidays=1;
				break;
			case 6:
				$totalholidays=1;
				break;
			case 8:
				$totalholidays=1;
				break;
			case 10:
				$totalholidays=1;
				break;
			case 12:
				$totalholidays=3;
				break;
			default:
				$totalholidays=0;
				break;
		}
		return $totalholidays;
	}

	// $view_model will be \App\SampleView::class || \App\ViralsampleView::class
	// $sample_model will be \App\Sample::class || \App\Viralsample::class
	public function save_tat($view_model, $sample_model, $batch_id = NULL)
	{
        ini_set("memory_limit", "-1");
		$samples = $view_model::where(['batch_complete' => 1])
		->whereRaw("(synched = 0 or synched = 2)")
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
			$sample_model::where('id', $sample->id)->update($data);
		}
	}



	// $view_model will be \App\SampleView::class || \App\ViralsampleView::class
	// $sample_model will be \App\Sample::class || \App\Viralsample::class
	public function compute_tat($view_model, $sample_model)
	{
        ini_set("memory_limit", "-1");
        $offset_value = 50000;
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
		return "Batches of {$type} have been marked as input complete";
	}

	public static function check_batches($type)
	{
		if($type == 'eid'){
			$batch_model = \App\Batch::class;
			$misc_model = \App\Misc::class;
		}else{
			$batch_model = \App\Viralbatch::class;
			$misc_model = \App\MiscViral::class;
		}

		$batches = $batch_model::select('id')->where(['input_complete' => true, 'batch_complete' => 0])->get();
		foreach ($batches as $key => $batch) {
			$str = $misc_model::check_batch($batch->id);
			// if($str) echo $str . "\n";
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
    	
        $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
        if(env('APP_ENV') == 'production') $mail_array = $facility->email_array;
        if(!$mail_array) return null;

        if(get_class($batch) == "App\\Batch") $mail_class = EidDispatch::class; 

        if(get_class($batch) == "App\\Viralbatch") $mail_class = VlDispatch::class;

        try {
        	if($view_name) $new_mail = new $mail_class($batch, $view_name);
        	else{
        		$new_mail = new $mail_class($batch);
        	}
        	Mail::to($mail_array)->bcc(['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@gmail.com'])
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

		$min_date = date('Y-m-d', strtotime('-1 years'));

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

    public static function dispatch_delayed($type = 'eid')
    {
		if($type == 'eid'){
			$batch_model = \App\Batch::class;
		}else{
			$batch_model = \App\Viralbatch::class;
		}

		$batches = $batch_model::where('batch_complete', 1)
		->where('datedispatched', '>', '2018-08-27')
		->where('datedispatched', '<', '2018-09-03')
		->get();

		foreach ($batches as $batch) {
		 	self::dispatch_batch($batch);
		 	break;
		} 
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

    public static function ampath_all()
    {
    	self::ampath(\App\Batch::class);
    	self::ampath(\App\Viralbatch::class);
    }

    public static function ampath($class)
    {
        ini_set("memory_limit", "-1");

        $batches = $class::where(['batch_complete' => '2'])->where('datereceived', '<', '2018-01-01')->get();

        foreach ($batches as $key => $batch) {
            $batch->datedispatched = date('Y-m-d', strtotime($batch->datereceived . ' +5days'));
            $batch->batch_complete = 1;
            $batch->pre_update();
        }
    }



}
