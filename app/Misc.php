<?php

namespace App;

use GuzzleHttp\Client;

use App\Common;
use App\Sample;
use App\SampleView;
use App\Lookup;


class Misc extends Common
{

	public static function requeue($worksheet_id)
	{
		$samples = Sample::where('worksheet_id', $worksheet_id)->get();

        Sample::where('worksheet_id', $worksheet_id)->update(['repeatt' => 0]);

		// Default value for repeatt is 0

		foreach ($samples as $sample) {
			if($sample->parentid == 0){
				if($sample->result == 2 || $sample->result == 3){
					$sample->repeatt = 1;
					$sample->save();
				}
			}
			else{
				$original = self::check_original($sample->id);

				if($sample->run == 2){
					if( ($sample->result == 3 && $original->result == 3) || 
						($sample->result == 2 && $original->result == 3) || 
						($sample->result != 2 && $original->result == 2) )
					{
						$sample->repeatt = 1;
						$sample->save();
					}
				}

				else if($sample->run == 3){
					$second = self::check_run($original->id, 2);

					if( ($sample->result == 3 && $second->result == 3 && $original->result == 3) ||
						($sample->result == 3 && $second->result == 2 && $original->result == 3) ||
						($original->result == 2 && $second->result == 1 && $sample->result == 2) ||
						($original->result == 2 && $second->result == 3 && $sample->result == 3) )
					{
						$sample->repeatt = 1;
						$sample->save();
					}
				}
				else if($sample->run == 4){
					$second = self::check_run($original->id, 2);
					$third = self::check_run($original->id, 3);
					if( ($sample->result == 3 && $second->result == 3 && $third->result == 3 && $original->result == 3))
					{
						$sample->repeatt = 1;
						$sample->save();
					}
				}
                else{
                    if($sample->result == 3) $sample->result=5;
                    $sample->save();
                }
			}
		}
		return true;
	}

	public static function save_repeat($sample_id)
	{
		$original = Sample::find($sample_id);
		if($original->run == 5) return false;

		$sample = new Sample;
		$fields = \App\Lookup::samples_arrays();
		$sample->fill($original->only($fields['sample_rerun']));
		$sample->run++;
		if($sample->parentid == 0) $sample->parentid = $original->id;
		
		$sample->save();
		return $sample;
	}

	public static function check_batch($batch_id, $issample=FALSE)
	{
		if($issample){
			$sample = Sample::find($batch_id);
			$batch_id = $sample->batch_id;
		}
		$double_approval = \App\Lookup::$double_approval; 
		if(in_array(env('APP_LAB'), $double_approval)){
			$where_query = "( receivedstatus=2 OR  (result > 0 AND repeatt = 0 AND approvedby IS NOT NULL AND approvedby2 IS NOT NULL) )";
		}
		else{
			$where_query = "( receivedstatus=2 OR  (result > 0 AND repeatt = 0 AND approvedby IS NOT NULL) )";
		}
		$total = Sample::where('batch_id', $batch_id)->where('parentid', 0)->get()->count();
		$tests = Sample::where('batch_id', $batch_id)
		->whereRaw($where_query)
		->get()
		->count();

		if($total == $tests){
			// DB::table('batches')->where('id', $batch_id)->update(['batch_complete' => 2]);
			\App\Batch::where('id', $batch_id)->update(['batch_complete' => 2]);
			// self::save_tat(\App\SampleView::class, \App\Sample::class, $batch_id);
		}
	}

	public static function check_original($sample_id)
	{
		$lab = auth()->user()->lab_id;

		$sample = Sample::select('samples.*')
		->join('batches', 'samples.batch_id', '=', 'batches.id')
		->where(['batches.lab_id' => $lab, 'samples.id' => $sample_id])
		->get()
		->first();

		return $sample;
	}

	public static function check_previous($sample_id)
	{
		$lab = auth()->user()->lab_id;
		$samples = Sample::select('samples.*')
		->join('batches', 'samples.batch_id', '=', 'batches.id')
		->where(['batches.lab_id' => $lab, 'samples.parentid' => $sample_id])
		->get();

		return $samples;
	}

	public static function check_run($sample_id, $run=2)
	{
		$lab = auth()->user()->lab_id;
		$sample = Sample::select('samples.*')
		->join('batches', 'samples.batch_id', '=', 'batches.id')
		->where(['batches.lab_id' => $lab, 'samples.parentid' => $sample_id, 'run' => $run])
		->get()
		->first();

		return $sample;
	}
	

    public static function get_subtotals($batch_id=NULL, $complete=true)
    {

        $samples = Sample::selectRaw("count(samples.id) as totals, batch_id, result")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                else{
                    return $query->where('batch_id', $batch_id);
                }
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('repeatt', 0)
            ->whereRaw("(receivedstatus != 2 or receivedstatus is null)")
            ->groupBy('batch_id', 'result')
            ->get();

        return $samples;
    }

    public static function get_rejected($batch_id=NULL, $complete=true)
    {
        $samples = Sample::selectRaw("count(samples.id) as totals, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                return $query->where('batch_id', $batch_id);
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }



    public static function get_maxdatemodified($batch_id=NULL, $complete=true)
    {
        $samples = Sample::selectRaw("max(datemodified) as mydate, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                return $query->where('batch_id', $batch_id);
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public static function get_maxdatetested($batch_id=NULL, $complete=true)
    {
        $samples = Sample::selectRaw("max(datetested) as mydate, batch_id")
            ->join('batches', 'batches.id', '=', 'samples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                return $query->where('batch_id', $batch_id);
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public static function patient_sms()
    {
        ini_set("memory_limit", "-1");
    	$samples = SampleView::whereNotNull('patient_phone_no')
    				->whereNull('time_result_sms_sent')
    				->where('batch_complete', 1)
    				->where('datereceived', '>', '2018-05-01')
    				->get();

    	foreach ($samples as $key => $sample) {
    		self::send_sms($sample);
    	}
    }

    public static function send_sms($sample)
    {
		// English
		if($sample->preferred_language == 1){
			if($sample->result == 2){
				$message = $sample->patient_name . " Jambo, baby's results are ready. Please come to the clinic when you can. Thank You";
			}
			else if($sample->result == 3 || $sample->result == 5){
				$message = $sample->patient_name . " Jambo,  please come to the clinic as soon as you can! Thank you";
			}
			else{
				if($sample->receivedstatus == 2){
					$message = $sample->patient_name . " Jambo,  please come to the clinic as soon as you can! Thank you";
				}
				else{
					$message = $sample->patient_name . " Jambo,  please come to the clinic as soon as you can! Thank you"; 	
				}
			}
		}
		// Kiswahili
		else{
			if($sample->result == 2){
				$message = $sample->patient_name . " Jambo, matokeo ya mtoto yako tayari. Tafadhali kuja kliniki utakapoweza. Asante.";
			}
			else if($sample->result == 3 || $sample->result == 5){
				$message = $sample->patient_name . " Jambo, kuja kliniki utakapoweza. Asante.";
			}
			else{
				if($sample->receivedstatus == 2){
					$message = $sample->patient_name . " Jambo, kuja kliniki utakapoweza. Asante.";
				}
				else{
					$message = $sample->patient_name . " Jambo, kuja kliniki utakapoweza. Asante.";
				}
			}    			
		}

		if(!$message) return;

        $client = new Client(['base_uri' => self::$sms_url]);

		$response = $client->request('post', '', [
			'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
			'http_errors' => false,
			'json' => [
				'sender' => env('SMS_SENDER_ID'),
				'recipient' => $sample->patient_phone_no,
				'message' => $message,
			],
		]);

		$body = json_decode($response->getBody());
		if($response->getStatusCode() == 201){
			$s = Sample::find($sample->id);
			$s->time_result_sms_sent = date('Y-m-d H:i:s');
			$s->pre_update();
		}
    }

    public static function sms_test()
    {
        $client = new Client(['base_uri' => self::$sms_url]);

		$response = $client->request('post', '', [
			'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
			'debug' => true,
			'http_errors' => false,
			'json' => [
				'sender' => env('SMS_SENDER_ID'),
				'recipient' => '254702266217',
				'message' => 'This is a successful test.',
			],

		]);

		$body = json_decode($response->getBody());
		echo 'Status code is ' . $response->getStatusCode();
		// dd($body);
    }

    public static function get_worksheet_samples($machine_type, $temp_limit=null)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        $test = in_array(env('APP_LAB'), Lookup::$worksheet_received);
        $user = auth()->user();

        if($machine == NULL || $machine->eid_limit == NULL) return false;

        $limit = $temp_limit ?? $machine->eid_limit;
        
        $year = date('Y') - 1;
        if(date('m') < 7) $year --;
        $date_str = $year . '-12-31';        

        if($test){
            // $repeats = Sample::selectRaw("samples.*, patients.patient, facilitys.name, batches.datereceived, batches.highpriority, batches.site_entry, users.surname, users.oname, IF(parentid > 0 OR parentid=0, 0, 1) AS isnull")
            //     ->join('batches', 'samples.batch_id', '=', 'batches.id')
            //     ->leftJoin('users', 'users.id', '=', 'batches.user_id')
            //     ->join('patients', 'samples.patient_id', '=', 'patients.id')
            //     ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
            //     ->where('datereceived', '>', $date_str)
            //     ->where('site_entry', '!=', 2)
            //     ->having('isnull', 0)
            //     ->whereRaw("(worksheet_id is null or worksheet_id=0)")
            //     ->where('input_complete', true)
            //     ->whereIn('receivedstatus', [1, 3])
            //     ->whereRaw('((result IS NULL ) OR (result=0 ))')
            //     ->orderBy('samples.id', 'asc')
            //     ->limit($limit)
            //     ->get();

            $repeats = SampleView::selectRaw("samples_view.*, facilitys.name, users.surname, users.oname")
                ->leftJoin('users', 'users.id', '=', 'samples_view.user_id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'samples_view.facility_id')
                ->where('datereceived', '>', $date_str)
                ->where('site_entry', '!=', 2)
                ->where('parentid', '>', 0)
                ->whereRaw("(worksheet_id is null or worksheet_id=0)")
                ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw('((result IS NULL ) OR (result=0 ))')
                ->orderBy('samples_view.id', 'asc')
                ->limit($limit)
                ->get();
            $limit -= $repeats->count();
        }

        // $samples = Sample::selectRaw("samples.*, patients.patient, facilitys.name, batches.datereceived, batches.highpriority, batches.site_entry, users.surname, users.oname, IF(parentid > 0 OR parentid=0, 0, 1) AS isnull")
        //     ->join('batches', 'samples.batch_id', '=', 'batches.id')
        //     ->leftJoin('users', 'users.id', '=', 'batches.user_id')
        //     ->join('patients', 'samples.patient_id', '=', 'patients.id')
        //     ->leftJoin('facilitys', 'facilitys.id', '=', 'batches.facility_id')
        //     ->where('datereceived', '>', $date_str)
        //     ->when($test, function($query) use ($user){
        //         return $query->where('received_by', $user->id)->where('parentid', 0);
        //     })
        //     ->where('site_entry', '!=', 2)
        //     ->whereRaw("(worksheet_id is null or worksheet_id=0)")
        //     ->where('input_complete', true)
        //     ->whereIn('receivedstatus', [1, 3])
        //     ->whereRaw('((result IS NULL ) OR (result =0 ))')
        //     ->orderBy('isnull', 'asc')
        //     ->orderBy('highpriority', 'desc')
        //     ->orderBy('datereceived', 'asc')
        //     ->orderBy('site_entry', 'asc')
        //     ->orderBy('samples.id', 'asc')
        //     ->limit($limit)
        //     ->get();

        $samples = SampleView::selectRaw("samples_view.*, facilitys.name, users.surname, users.oname, IF(parentid > 0 OR parentid=0, 0, 1) AS isnull")
            ->leftJoin('users', 'users.id', '=', 'samples_view.user_id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'samples_view.facility_id')
            ->where('datereceived', '>', $date_str)
            ->when($test, function($query) use ($user){
                return $query->where('received_by', $user->id)->where('parentid', 0);
            })
            ->where('site_entry', '!=', 2)
            ->whereRaw("(worksheet_id is null or worksheet_id=0)")
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('highpriority', 'desc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('site_entry', 'asc')
            ->orderBy('samples_view.id', 'asc')
            ->limit($limit)
            ->get();

        if($test && $repeats->count() > 0) $samples = $repeats->merge($samples);
        $count = $samples->count();        

        $create = false;
        if($count == $machine->eid_limit) $create = true;

        return [
        	'count' => $count,
            'create' => $create, 'machine_type' => $machine_type, 'machine' => $machine, 'samples' => $samples
        ];

    }
}
