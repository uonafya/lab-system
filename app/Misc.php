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

                    dd($second);

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

        $s = Sample::where(['parentid' => $sample->parentid, 'run' => $sample->run])->first();
        if($s) return $s;
		
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

        Sample::whereNull('result')
            ->where('repeatt', 0)
            ->where('batch_id', $batch_id)
            ->whereNotNull('dateapproved')
            ->when((in_array(env('APP_LAB'), $double_approval)), function($query){
                return $query->whereNotNull('dateapproved2');
            })            
            ->update(['result' => 5, 'labcomment' => 'Failed Test']);

		if(in_array(env('APP_LAB'), $double_approval)){
			$where_query = "( receivedstatus=2 OR  (result > 0 AND (repeatt = 0 or repeatt is null) AND approvedby IS NOT NULL AND approvedby2 IS NOT NULL) )";
		}
		else{
			$where_query = "( receivedstatus=2 OR  (result > 0 AND (repeatt = 0 or repeatt is null) AND approvedby IS NOT NULL) )";
		}
		$total = Sample::where('batch_id', $batch_id)->where('parentid', 0)->get()->count();
		$tests = Sample::where('batch_id', $batch_id)
		->whereRaw($where_query)
		->get()
		->count();

		if($total == $tests){
            Sample::where('batch_id', $batch_id)->whereNull('repeatt')->update(['repeatt' => 0]);
            $b = \App\Batch::find($batch_id);
            if($b->batch_complete == 0){
                $b->batch_complete = 2; 
                $b->save();
            }
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

    public static function clean_dob()
    {
    	$samples = Sample::where('age', '>', 36)->with(['patient'])->get();

    	foreach ($samples as $sample) {
    		// $patient = $sample->patient;
    		// $patient->dob = null;
    		// $patient->pre_update();

    		$sample->age=0;
    		$sample->pre_update();
    	}
    }

    public static function delete_empty_batches()
    {
    	$batches = \App\Batch::selectRaw("batches.id, count(samples.id) as mycount")
    					->leftJoin('samples', 'samples.batch_id', '=', 'batches.id')
    					->groupBy('batches.id')
    					->having('mycount', 0)
    					->get();

    	// return $batches->count();

    	foreach ($batches as $key => $batch) {
    		$batch->delete();
    	}
    }

    public static function patient_sms()
    {
        ini_set("memory_limit", "-1");
    	$samples = SampleView::whereNotNull('patient_phone_no')
    				->where('patient_phone_no', '!=', '')
    				->whereNull('time_result_sms_sent')
    				->where('batch_complete', 1)
    				->where('datereceived', '>', '2018-05-01')
    				->get();

    	foreach ($samples as $key => $sample) {
    		self::send_sms($sample);
    		// break;
    	}
    }

    public static function send_sms($sample)
    {
		// English
		if($sample->preferred_language == 1){
			if($sample->result == 2){
				$message = $sample->patient_name . " Jambo, baby's results are ready. Please come to the clinic when you can. Thank You";
			}
            if($sample->result == 1){
                $message = $sample->patient_name . "  Jambo, baby's results are ready. Remember to keep your appointment date! Thank you";
            }
			else if($sample->result == 3 || $sample->result == 5){
				$message = $sample->patient_name . " Jambo,  please come to the clinic with baby as soon as you can! Thank you ";
			}
			else{
				if($sample->receivedstatus == 2){
					$message = $sample->patient_name . " Jambo,  please come to the clinic with baby as soon as you can! Thank you ";
				}
				// else{
				// 	$message = $sample->patient_name . " Jambo, baby's results are ready. Remember to keep your appointment date! Thank you"; 	
				// }
			}
		}
		// Kiswahili
		else{
			if($sample->result == 2){
				$message = $sample->patient_name . " Jambo, matokeo ya mtoto yako tayari. Tafadhali kuja kliniki utakapoweza. Asante.";
			}
			else if($sample->result == 3 || $sample->result == 5){
				$message = $sample->patient_name . " Jambo, kuja kliniki na mtoto utakapoweza, asante";
			}
            if($sample->result == 1){
                $message = $sample->patient_name . "  Jambo, matokeo ya mtoto tayari. Kumbuka tarehe yako ya kuja cliniki, Asante";
            }
			else{
				if($sample->receivedstatus == 2){
					$message = $sample->patient_name . " Jambo, kuja kliniki na mtoto utakapoweza, asante";
				}
				// else{
				// 	$message = $sample->patient_name . " Jambo, kuja kliniki utakapoweza. Asante.";
				// }
			}    			
		}

		if(!$message){
			print_r($sample);
			return;
		}

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
			$s->save();
		}
    }

    /*public static function send_sms_person($sample, $tel)
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

        if(!$message){
            print_r($sample);
            return;
        }

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
            $s->save();
        }
    }*/

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

    public static function sms_random($number, $message)
    {
        $client = new Client(['base_uri' => self::$sms_url]);

        $response = $client->request('post', '', [
            'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
            'debug' => true,
            'http_errors' => false,
            'json' => [
                'sender' => env('SMS_SENDER_ID'),
                'recipient' => $number,
                'message' => $message,
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
        \App\Batch::where(['received_by' => $user->id, 'input_complete' => 0])->update(['input_complete' => 1]);

        if($machine == NULL || $machine->eid_limit == NULL) return false;

        $limit = $temp_limit ?? $machine->eid_limit;
        
        $year = date('Y') - 1;
        if(date('m') < 7) $year --;
        $date_str = $year . '-12-31';        

        if($test){
            $repeats = SampleView::selectRaw("samples_view.*, facilitys.name, users.surname, users.oname")
                ->leftJoin('users', 'users.id', '=', 'samples_view.user_id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'samples_view.facility_id')
                ->where('datereceived', '>', $date_str)
                ->where('site_entry', '!=', 2)
                ->where('parentid', '>', 0)
                ->whereNull('datedispatched')
                ->whereRaw("(worksheet_id is null or worksheet_id=0)")
                // ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw('((result IS NULL ) OR (result=0 ))')
                ->orderBy('samples_view.id', 'desc')
                ->limit($limit)
                ->get();
            $limit -= $repeats->count();
        }

        $samples = SampleView::selectRaw("samples_view.*, facilitys.name, users.surname, users.oname, IF(parentid > 0 OR parentid=0, 0, 1) AS isnull")
            ->leftJoin('users', 'users.id', '=', 'samples_view.user_id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'samples_view.facility_id')
            ->where('datereceived', '>', $date_str)
            ->when($test, function($query) use ($user){
                // return $query->where('received_by', $user->id)->where('parentid', 0);
                return $query->where('parentid', 0)
                	->whereRaw("((received_by={$user->id} && sample_received_by IS NULL) OR  sample_received_by={$user->id})");
            })
            ->where('site_entry', '!=', 2)
            ->whereNull('datedispatched')
            ->whereRaw("(worksheet_id is null or worksheet_id=0)")
            // ->where('input_complete', true)
            // ->where('parentid', '>', 0)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')            
            ->orderBy('run', 'desc')
            // ->orderBy('isnull', 'asc')
            ->orderBy('highpriority', 'desc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('site_entry', 'asc')
            ->orderBy('facilitys.id', 'asc')
            ->orderBy('batch_id', 'asc')
            ->limit($limit)
            ->get();

        // dd($samples);

        if($test && $repeats->count() > 0) $samples = $repeats->merge($samples);
        $count = $samples->count();        

        $create = false;
        if($count == $machine->eid_limit) $create = true;
        if($temp_limit && $count == $temp_limit) $create = true;

        return [
        	'count' => $count, 'limit' => $temp_limit,
            'create' => $create, 'machine_type' => $machine_type, 'machine' => $machine, 'samples' => $samples
        ];

    }

    public static function send_to_mlab()
    {
    	ini_set('memory_limit', "-1");
        $min_date = date('Y-m-d', strtotime('-1 month'));
    	$batches = \App\Batch::join('facilitys', 'batches.facility_id', '=', 'facilitys.id')
    			->select("batches.*")
    			->with(['facility'])
    			->where('sent_to_mlab', 0)
    			->where('smsprinter', 1)
    			->where('batch_complete', 1)
				->where('datedispatched', '>', $min_date)
    			->get();

    	foreach ($batches as $batch) {
    		$samples = $batch->sample;

    		foreach ($samples as $sample) {
    			if($sample->repeatt == 1) continue;

    			$client = new Client(['base_uri' => self::$mlab_url]);

    			$post_data = [
						'source' => '1',
						'result_id' => "{$sample->id}",
						'result_type' => '2',
						'request_id' => '',
						'client_id' => $sample->patient->patient,
						'age' => $sample->my_string_format('age'),
						'gender' => $sample->patient->gender,
						'result_content' => $sample->my_string_format('result'),
						'units' => '0',
						'mfl_code' => "{$batch->facility->facilitycode}",
						'lab_id' => "{$batch->lab_id}",
						'date_collected' => $sample->datecollected ?? '0000-00-00',
						'cst' => '0',
						'cj' => '0',
						'csr' => "{$sample->rejectedreason}",
						'lab_order_date' => $sample->datetested ?? '0000-00-00',
					];

				$response = $client->request('post', '', [
					// 'debug' => true,
					'http_errors' => false,
					'json' => $post_data,
				]);
				$body = json_decode($response->getBody());
				// print_r($body);
				if($response->getStatusCode() > 399){
					// print_r(json_decode($sample->toJson()));
					print_r($post_data);
					print_r($body);
					return null;
				}
    		}
    		$batch->sent_to_mlab = 1;
    		$batch->save();
    		// break;
    	}
    }

    public static function cpgh()
    {
        ini_set("memory_limit", "-1");

        $batches = Batch::where('datereceived', '<', '2018-01-01')->where('batch_complete', 0)->get();

        foreach ($batches as $batch) {
            $samples = $batch->sample;

            foreach ($samples as $sample) {
                if($sample->repeatt == 1 && !$sample->has_rerun){
                    $sample->repeatt = 0;
                    $sample->save();
                }
            }

            $batch->datedispatched = date('Y-m-d', strtotime($batch->datereceived . ' +2days'));
            $batch->batch_complete = 1;
            $batch->save();
        }
    }
}
