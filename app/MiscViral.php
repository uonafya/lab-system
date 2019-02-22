<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Excel;

use App\Common;
use App\Viralsample;
use App\ViralsampleView;

use App\Viralworksheet;

use App\DrPatient;
use App\Lookup;

use App\Mail\EdarpValidation as Edarp;

class MiscViral extends Common
{

    protected $rcategories = [
        '0' => [],
        '1' => [],
        '2' => ['<550', '< 550 ', '<150', '<160', '<75', '<274', '<400', ' <400', '< 400', '<188', '<218', '<839', '< 21', '<40', '<20', '>20', '< 20', '22 cp/ml', '<218', '<1000', '< LDL copies/ml', '< LDL copies', 'Target Not Detected', ],
        '3' => ['>1000'],
        '4' => ['> 10000000', '>10,000,000', '>10000000', '>10000000'],
        '5' => ['Failed', 'failed', 'Failed PREP_ABORT', 'Failed Test', 'Invalid', 'Collect New Sample', 'Collect New sample']
    ];

    protected $compound_categories = [
        [
            'search_array' =>  ['Target  Not Detected', 'Target N ot Detected', 'Target Not  Detected', 'Target Not Detecetd', 'Target Not Detected'],
            'update_array' => ['rcategory' => 1, 'result' => 'Target Not Detected', 'interpretation' => 'Target Not Detected']
        ],
        [
            'search_array' =>   ['< LDL copies/ml', '< LDL copies', 'Not Detected', '< LDL copies/ml', '<LDL copies/ml', '< LDL copies/ml', ' < LDL copies/ml', '< LDL'],
            'update_array' => ['rcategory' => 1, 'result' => '< LDL copies/ml', 'interpretation' => 'Target Not Detected']
        ],
        [
            'search_array' =>  ['Less than 20 copies/ml', 'Less than Low Detectable Level'],
            'update_array' => ['rcategory' => 1, 'result' => '< LDL copies/ml', 'interpretation' => 'Less than 20 copies/ml']
        ],
        [
            'search_array' =>  ['REJECTED'],
            'update_array' => ['rcategory' => 5, 'result' => 'Collect New Sample', 'interpretation' => 'REJECTED']
        ],
        [
            'search_array' =>  ['Aborted'],
            'update_array' => ['rcategory' => 5, 'result' => 'Collect New Sample', 'interpretation' => 'Aborted']
        ],
        [
            'search_array' =>  ['REJECTED', 'Redraw New Sample', 'collect new samp', 'collect new saple', 'insufficient', 'Failed Collect New sample', 'failed', 'Collect New Sample'],
            'update_array' => ['rcategory' => 5, 'result' => 'Collect New Sample', 'labcomment' => 'Failed Test']
        ],
    ];

	public static function requeue($worksheet_id)
	{
        $samples_array = ViralsampleView::where(['worksheet_id' => $worksheet_id])->where('site_entry', '!=', 2)->get()->pluck('id');
        $samples = Viralsample::whereIn('id', $samples_array)->get();

        Viralsample::where('worksheet_id', $worksheet_id)->update(['repeatt' => 0]);

		// Default value for repeatt is 0

		foreach ($samples as $sample) {
			if($sample->result == "Failed" || $sample->result == "Invalid" || $sample->result == "" || !$sample->result){
				$sample->repeatt = 1;
				$sample->save();
			}
		}
		return true;
	}

	public static function save_repeat($sample_id)
	{
        $original = Viralsample::find($sample_id);
        if($original->run == 5){
            $original->repeatt=0;
            $original->save();
            return false;
        }

		$sample = new Viralsample;        
        $fields = \App\Lookup::viralsamples_arrays();
        $sample->fill($original->only($fields['sample_rerun']));
        $sample->run++;
        if($original->parentid == 0) $sample->parentid = $original->id;

        $s = Viralsample::where(['parentid' => $sample->parentid, 'run' => $sample->run])->first();
        if($s) return $s;
        
		$sample->save();
		return $sample;
	}

	public static function check_batch($batch_id, $issample=FALSE)
	{		
        if($issample){
            $sample = Viralsample::find($batch_id);
            $batch_id = $sample->batch_id;
        }

        $double_approval = \App\Lookup::$double_approval; 

        // Viralsample::where(['batch_id' => $batch_id, 'result' => 'Failed', 'repeatt' => 0])->update(['result' => 'Collect New Sample']);

        Viralsample::whereRaw("(result is null or result = '' or result = 'Failed')")
            ->where('repeatt', 0)
            ->where('batch_id', $batch_id)
            ->whereNotNull('dateapproved')
            ->when((in_array(env('APP_LAB'), $double_approval)), function($query){
                return $query->whereNotNull('dateapproved2');
            })            
            ->update(['result' => 'Collect New Sample', 'labcomment' => 'Failed Test']);

        if(in_array(env('APP_LAB'), $double_approval)){
            $where_query = "( receivedstatus=2 OR  (result IS NOT NULL AND result != 'Failed' AND result != '' AND (repeatt = 0 or repeatt is null) AND ((approvedby IS NOT NULL AND approvedby2 IS NOT NULL) or (dateapproved IS NOT NULL AND dateapproved2 IS NOT NULL)) ))";
        }
        else{
            $where_query = "( receivedstatus=2 OR  (result IS NOT NULL AND result != 'Failed' AND result != '' AND (repeatt = 0 or repeatt is null) AND (approvedby IS NOT NULL OR dateapproved IS NOT NULL)) )";
        }


		$total = Viralsample::where('batch_id', $batch_id)->where('parentid', 0)->get()->count();
		$tests = Viralsample::where('batch_id', $batch_id)
		->whereRaw($where_query)
		->get()
		->count();

		if($total == $tests){ 
            // DB::table('viralbatches')->where('id', $batch_id)->update(['batch_complete' => 2]);
            Viralsample::where('batch_id', $batch_id)->whereNull('repeatt')->update(['repeatt' => 0]);
            $b = \App\Viralbatch::find($batch_id);
            if($b->batch_complete == 0){
                $b->batch_complete = 2; 
                $b->save();
                return true;
            }
            // self::save_tat(\App\ViralsampleView::class, \App\Viralsample::class, $batch_id);
		}
        else{
            return false;
        }
	}

	public static function check_previous($sample_id)
	{
		$lab = auth()->user()->lab_id;

		$samples = Viralsample::select('samples.*')
		->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
		->where(['lab_id' => $lab, 'parentid' => $sample_id])
		->get();

		return $samples;
	}

	public static function check_run($sample_id, $run=2)
	{
		$lab = auth()->user()->lab_id;

		$sample = Viralsample::select('samples.*')
		->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
		->where(['lab_id' => $lab, 'parentid' => $sample_id, 'run' => $run])
		->get()
		->first();

		return $sample;
	}


    public static function get_totals($result, $batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("count(*) as totals, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                else{
                    return $query->where('batch_id', $batch_id);
                }
            })
            ->when(true, function($query) use ($result){
                if ($result == 0) {
                    return $query->whereRaw("(result is null or result = '')");
                }
                else if ($result == 1) {
                    return $query->where('result', '< LDL copies/ml');
                }
                else if ($result == 2) {
                    return $query->where('result', '!=', 'Failed')
                    ->where('result', '!=', 'Collect New Sample')
                    ->where('result', '!=', '< LDL copies/ml')
                    ->where('result', '!=', '')
                    ->whereNotNull('result');
                }
                else if ($result == 3) {
                    return $query->where('result', 'Failed');
                } 
                else if ($result == 5) {
                    return $query->where('result', 'Collect New Sample');
                }               
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->whereRaw("(receivedstatus != 2 or receivedstatus is null)")
            ->where('repeatt', 0)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }
    

    public static function get_subtotals($batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("count(viralsamples.id) as totals, batch_id, rcategory")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
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
            ->groupBy('batch_id', 'rcategory')
            ->get();

        return $samples;
    }

    public static function sample_result($result, $error=null)
    {
        $str = strtolower($result);
        $units="";

        if(str_contains($result, ['e+'])){
            return self::exponential_result($result);
        }

        else if($result == 'Failed' || $result == 'Invalid' || $result == '' || str_contains($str, ['error']) || strlen($error) > 10)
        {
            $res= "Failed";
            $interpretation = $error ?? $result;       
        }

        // if($result == 'Not Detected' || $result == 'Target Not Detected' || $result == 'Not detected' || $result == '<40 Copies / mL' || $result == '< 40Copies / mL ' || $result == '< 40 Copies/ mL')
        else if(str_contains($result, ['<']) && str_contains($result, ['40', '30', '20', '21', '839', '150', '550']))
        {
            $res= "< LDL copies/ml";
            $interpretation= $result;       
        }
        else if(str_contains($result, ['<']))
        {
            $res= "< LDL copies/ml";
            $interpretation= $result;       
        }

        else if(str_contains($str, ['not detected']))
        {
            // $res="Target Not Detected";
            $res= "< LDL copies/ml";
            $interpretation=$result;
        }

        // else if($result == 'Collect New Sample')
        else if(str_contains($str, ['collect', 'new sample']))
        {
            $res= "Collect New Sample";
            $interpretation="Collect New Sample";
        }

        else if (str_contains($str, ['log'])) 
        {
            $interpretation = $result;
            if(str_contains($str, ['<'])){
                $res= "< LDL copies/ml";
            }
            else{
                $x = preg_replace("/[^<0-9.]/", "", $result);
                $res = round(pow(10, $x));
                $units="cp/mL";                 
            }           
        }

        else{
            $res = preg_replace("/[^<0-9]/", "", $result);
            $interpretation = $result;
            $units="cp/mL";
        }

        return ['result' => $res, 'interpretation' => $interpretation, 'units' => $units];
    }

    public static function correct_logs()
    {
        ini_set('memory_limit', -1);

        $samples = Viralsample::where('interpretation', 'like', '%log%')->get();
        $batches = [];

        foreach ($samples as $sample) {
            $result_array = self::sample_result($sample->interpretation, null);
            $result = $result_array['result'];
            $sample->result = $result;
            $sample->pre_update();

            if(!in_array($sample->batch_id, $batches)) $batches[] = $sample->batch_id;
        }

        foreach ($batches as $id) {
            $batch = Viralbatch::find($id);
            if($batch->batch_complete != 1) continue;

            $datedispatched = Carbon::parse($batch->datedispatched);
            $months = Carbon::now()->diffInMonths($datedispatched);

            if($months < 5) self::dispatch_batch($batch, 'emails.eid_resend');
        }
    }

    public static function exponential_result($result)
    {
        $units="";              
        if($result == 'Invalid'){
            $res= "Failed";
            $interpretation="Invalid";
        }
        else if($result == '< Titer min' || $result == 'Target Not Detected'){
            $res= "< LDL copies/ml";
            $interpretation= $result;
        }
        else if(str_contains($result, 'e+')){
            $a = explode('e+', $result);
            $u = explode(' ', $a[1]);
            $power = (int) $u[0];
            $res = $a[0] * (10**$power);
            $res = (int) round($res);
            $interpretation = $result;
            $units = $u[1] ?? 'cp/mL';
        }
        else{
            $res= "Failed";
            $interpretation = $result;                
        }

        return ['result' => $res, 'interpretation' => $interpretation, 'units' => $units];
    }

    public static function correct_exponential($worksheet_id)
    {
        $samples = Viralsample::where('worksheet_id', $worksheet_id)->where('result', '>', 1)->get();
        foreach ($samples as $key => $sample) {
            $r = self::exponential_result($sample->interpretation);
            $sample->result = $r['result'];
            $sample->pre_update();
        }
    }

    

    public static function get_rejected($batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("count(viralsamples.id) as totals, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
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
            ->where('receivedstatus', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public static function get_maxdatemodified($batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("max(datemodified) as mydate, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
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
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public static function get_maxdatetested($batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("max(datetested) as mydate, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
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
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public static function delete_empty_batches()
    {
        $batches = \App\Viralbatch::selectRaw("viralbatches.id, count(viralsamples.id) as mycount")
                        ->leftJoin('viralsamples', 'viralsamples.batch_id', '=', 'viralbatches.id')
                        ->groupBy('viralbatches.id')
                        ->having('mycount', 0)
                        ->get();

        // return $batches->count(); 

        foreach ($batches as $key => $batch) {
            $batch->delete();
        }
    }



    public function set_justification($justification = null)
    {
        if($justification == 0) return 8;
        return $justification;
    }

    public function set_prophylaxis($prophylaxis = null)
    {
        if($prophylaxis == 0) return 16;
        return $prophylaxis;
    }

    public function set_age_cat($age = null)
    {
        if($age > 0.00001 && $age < 2) return 6; 
        else if($age >= 2 && $age < 10) return 7; 
        else if($age >= 10 && $age < 15) return 8; 
        else if($age >= 15 && $age < 20) return 9; 
        else if($age >= 19 && $age < 25) return 10;
        else if($age >= 25) return 11;
        else{ return 0; }
    }

    public function set_rcategory($result, $repeatt=null)
    {
        if(!$result) return ['rcategory' => 0];
        $numeric_result = preg_replace('/[^0-9]/', '', $result);
        if(is_numeric($numeric_result)){
            $result = (int) $numeric_result;
            if($result > 0 && $result < 1001) return ['rcategory' => 2];
            else if($result > 1000 && $result < 5001) return ['rcategory' => 3];
            else if($result > 5000) return ['rcategory' => 4];
        }
        $str = strtolower($result);
        if(str_contains($str, ['not detected'])) return ['rcategory' => 1];
        if(str_contains($str, ['ldl'])) return ['rcategory' => 1];
        $data = $this->get_rcategory($result);
        if(!isset($data['rcategory'])) return [];
        if($repeatt == 0 && $data['rcategory'] == 5) $data['labcomment'] = 'Failed Test';
        return $data;
    }
    

    public function get_rcategory($result)
    {
        foreach ($this->compound_categories as $key => $value) {
            if(in_array($result, $value['search_array'])) return $value['update_array'];
        }

        foreach ($this->rcategories as $key => $value) {
            if(in_array($result, $value)) return ['rcategory' => $key];
        }
        return [];
    }

    public static function generate_dr_list()
    {
        ini_set("memory_limit", "-1");

        $min_date = Carbon::now()->subMonths(3)->toDateString();

        $samples = ViralsampleView::select('patient_id', 'datereceived', 'result', 'rcategory', 'age', 'pmtct', 'datetested')
            ->where('batch_complete', 1)
            ->whereIn('rcategory', [3, 4])
            ->where('datereceived', '>', $min_date)
            // ->whereYear('datereceived', date('Y'))
            ->where('repeatt', 0)
            ->whereRaw("patient_id NOT IN (SELECT distinct patient_id from dr_patients)")
            ->get();

        foreach ($samples as $sample) {
            $data = $sample->only(['patient_id', 'datereceived', 'result', 'rcategory']);
            if($sample->age < 10) continue;
            else if($sample->age < 19){
                $pat = new DrPatient;
                $pat->fill($data);
                $pat->dr_reason_id = 2;
                $pat->save();
                continue;
            }
            else if($sample->pmtct == 1 || $sample->pmtct == 2){
                $pat = new DrPatient;
                $pat->fill($data);
                $pat->dr_reason_id = 3;
                $pat->save();
                continue;
            }
            else{
                if(self::get_previous_test($sample->patient_id, $sample->datetested)){
                    $pat = new DrPatient;
                    $pat->fill($data);
                    $pat->dr_reason_id = 1;
                    $pat->save();
                    continue; 
                }
            }
        }
    }

    public static function get_previous_test($patient_id, $datetested)
    {
        /*$sql = "SELECT * FROM viralsamples WHERE patient_id={$patient_id} AND datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$datetested}')
        "; 


        $sample = \DB::select($sql)->first();*/

        $sample = Viralsample::where('patient_id', $patient_id)
                    ->whereRaw("datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$datetested}')")
                    ->get()->first();

        if(!$sample || $sample->rcategory == 1 || $sample->rcategory == 2) return false;

        $recent_date = Carbon::parse($datetested);
        $prev_date = Carbon::parse($sample->datetested);

        $months = $recent_date->diffInMonths($prev_date);
        if($months < 3){

            /*$sql = "SELECT * FROM viralsamples WHERE patient_id={$patient_id} AND datetested=
                        (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$sample->datetested}')
            "; 
            $sample = \DB::select($sql);*/

            $sample2 = Viralsample::where('patient_id', $patient_id)
                    ->whereRaw("datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$sample->datetested}')")
                    ->get()->first();

            if(!$sample2 || $sample2->rcategory == 1 || $sample2->rcategory == 2) return false;

            return true;
        }
        else{
            return true;
        }
        return false;
    }

    public static function patient_sms()
    {
        ini_set("memory_limit", "-1");
        $samples = ViralsampleView::whereNotNull('patient_phone_no')
                    ->where('patient_phone_no', '!=', '')
                    ->whereNull('time_result_sms_sent')
                    ->where('batch_complete', 1)
                    ->where('datereceived', '>', '2018-05-01')
                    ->get();

        foreach ($samples as $key => $sample) {
            if($sample->receivedstatus == 1 && !$sample->rcategory) continue;
            self::send_sms($sample);
        }
    }

    public static function send_sms($sample)
    {
        // English
        if($sample->preferred_language == 1){
            if($sample->rcategory == 1 || $sample->rcategory == 2){
                if($sample->age > 15 && $sample->age < 24){
                    $message = $sample->patient_name . ", Congratulations your VL is good, remember to keep your appointment date!!!";
                }
                else{
                    $message = $sample->patient_name . ", Congratulations!Your VL is good! Continue taking your drugs and keeping your appointment as instructed by the doctor.";                        
                }
            }
            else if($sample->rcategory == 3 || $sample->rcategory == 4){
                if($sample->age > 15 && $sample->age < 24){
                    $message = $sample->patient_name . ", Your VL results are ready. Please come to the facility as soon you can!";
                }
                else{
                    $message = $sample->patient_name . ", Your VL results are ready. Please visit the health facility as soon as you can.";                        
                }
            }
            else if($sample->rcategory == 5 || $sample->receivedstatus == 2){
                $message = $sample->patient_name . " Jambo,  please come to the clinic as soon as you can! Thank you.";
            }
        }
        // Kiswahili
        else{
            if($sample->rcategory == 1 || $sample->rcategory == 2){
                if($sample->age > 15 && $sample->age < 24){
                    $message = $sample->patient_name . ", Pongezi! Matokeo yako ya VL iko kiwango kizuri! Endelea kuzingatia maagizo!";
                }
                else{
                    $message = $sample->patient_name . ", Pongezi! Matokeo yako ya VL iko kiwango kizuri! Endelea kuzingatia maagizo ya daktari. Kumbuka tarehe yako ya kuja cliniki!";                        
                }
            }
            else if($sample->rcategory == 3 || $sample->rcategory == 4){
                if($sample->age > 15 && $sample->age < 24){
                    $message = $sample->patient_name . ", Matokeo yako ya VL yako tayari. Tafadhali tembelea kituo!";
                }
                else{
                    $message = $sample->patient_name . ", Matokeo yako ya VL yako tayari. Tafadhali tembelea kituo cha afya umwone daktari!";                        
                }
            }
            else if($sample->rcategory == 5 || $sample->receivedstatus == 2){
                $message = $sample->patient_name . " Jambo, kuja kliniki utakapoweza. Asante.";
            }             
        }

        if(!$message){
            print_r($sample);
            die();
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
            $s = Viralsample::find($sample->id);
            $s->time_result_sms_sent = date('Y-m-d H:i:s');
            $s->save();
        }
    }

    public static function get_worksheet_samples($machine_type, $calibration, $sampletype, $temp_limit=null)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        $test = in_array(env('APP_LAB'), Lookup::$worksheet_received);
        $user = auth()->user();
        \App\Viralbatch::where(['received_by' => $user->id, 'input_complete' => 0])->update(['input_complete' => 1]);

        if($machine == NULL || $machine->vl_limit == NULL) return false;
        // session(['toast_message' => 'An error has occurred.', 'toast_error' => 1]);

        $limit = $machine->vl_limit;

        if($temp_limit) $limit = $temp_limit;

        // if($calibration) $limit = $machine->vl_calibration_limit;
        // if($calibration) $limit = $temp_limit - 11;
        
        $year = date('Y') - 1;
        if(date('m') < 7) $year --;
        $date_str = $year . '-12-31';

        if($test){
            $repeats = ViralsampleView::selectRaw("viralsamples_view.*, facilitys.name, users.surname, users.oname, IF(parentid > 0 OR parentid=0, 0, 1) AS isnull")
                ->leftJoin('users', 'users.id', '=', 'viralsamples_view.user_id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'viralsamples_view.facility_id')
                ->where('datereceived', '>', $date_str)
                ->when($sampletype, function($query) use ($sampletype){
                    if($sampletype == 1) return $query->whereIn('sampletype', [3, 4]);
                    if($sampletype == 2) return $query->whereIn('sampletype', [1, 2, 5]);                    
                })
                ->where('site_entry', '!=', 2)
                ->where('parentid', '>', 0)
                ->whereNull('datedispatched')
                ->whereRaw("(worksheet_id is null or worksheet_id=0)")
                // ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw("(result IS NULL OR result='0')")
                ->orderBy('viralsamples_view.id', 'asc')
                ->limit($limit)
                ->get();
            $limit -= $repeats->count();
        }

        $samples = ViralsampleView::selectRaw("viralsamples_view.*, facilitys.name, users.surname, users.oname")
            ->leftJoin('users', 'users.id', '=', 'viralsamples_view.user_id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralsamples_view.facility_id')
            ->where('datereceived', '>', $date_str)
            ->when($test, function($query) use ($user){
                // return $query->where('received_by', $user->id)->where('parentid', 0);
                return $query->where('parentid', 0)
                    ->whereRaw("((received_by={$user->id} && sample_received_by IS NULL) OR  sample_received_by={$user->id})");
            })
            ->when($sampletype, function($query) use ($sampletype){
                if($sampletype == 1) return $query->whereIn('sampletype', [3, 4]);
                if($sampletype == 2) return $query->whereIn('sampletype', [1, 2, 5]);                    
            })
            ->where('site_entry', '!=', 2)
            ->whereNull('datedispatched')
            ->whereRaw("(worksheet_id is null or worksheet_id=0)")
            // ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw("(result IS NULL OR result='0')")
            // ->orderBy('isnull', 'asc')           
            ->orderBy('run', 'desc')
            ->orderBy('highpriority', 'desc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('site_entry', 'asc')
            ->orderBy('batch_id', 'asc')
            ->when((env('APP_LAB') == 2), function($query){
                return $query->orderBy('facilitys.id', 'asc');
            })            
            ->limit($limit)
            ->get();

        if($test && $repeats->count() > 0) $samples = $repeats->merge($samples);
        $count = $samples->count();

        $create = false; 
        if($count == $machine->vl_limit || ($calibration && $count == $machine->vl_calibration_limit)) $create = true;
        if($temp_limit && $count == $temp_limit) $create = true;
        if(in_array(env('APP_LAB'), [8])) $create = true;

        return [
            'count' => $count, 'limit' => $temp_limit,
            'create' => $create, 'machine_type' => $machine_type, 'calibration' => $calibration, 
            'sampletype' => $sampletype, 'machine' => $machine, 'samples' => $samples
        ];

    }

    public static function send_to_mlab()
    {
        ini_set('memory_limit', "-1");
        $min_date = date('Y-m-d', strtotime('-2 month'));
        $batches = \App\Viralbatch::join('facilitys', 'viralbatches.facility_id', '=', 'facilitys.id')
                ->select("viralbatches.*")
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
                        'result_type' => '1',
                        'request_id' => '',
                        'client_id' => $sample->patient->patient,
                        'age' => $sample->my_string_format('age'),
                        'gender' => $sample->patient->gender,
                        'result_content' => $sample->my_string_format('result', 'No Result'),
                        'units' => $sample->units ?? '',
                        'mfl_code' => "{$batch->facility->facilitycode}",
                        'lab_id' => "{$batch->lab_id}",
                        'date_collected' => $sample->datecollected ?? '0000-00-00',
                        'cst' => $sample->my_string_format('sampletype'),
                        'cj' => $sample->my_string_format('justification'),
                        'csr' =>  "{$sample->rejectedreason}",
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

    public static function dump_worksheet($worksheet_id)
    {
        $samples = ViralsampleView::where('worksheet_id', $worksheet_id)->get();

        $data = [];
        $failed = [];

        foreach ($samples as $key => $sample) {
            $res = strtolower($sample->result);
            if(str_contains($res, ['ldl', 'target'])) $res = 0.01;
            $row = [
                'Specimen Lab ID' => $sample->id,
                'IP Code' => $sample->patient,
                'Name' => $sample->facilityname,
                'Facility Code' => $sample->facilitycode,
                'Gender' => $sample->gender,
                'DOB' => $sample->dob,
                'Age' => $sample->age,
                'Sample Type' => $sample->sampletype,
                'Date Collected' => $sample->datecollected,
                'Prophylaxis' => $sample->prophylaxis,
                'Current ART Start Date' => $sample->dateinitiatedonregimen,
                'Initiation Date' => $sample->initiation_date,
                'Justification' => $sample->justification,
                'Date Received' => $sample->datereceived,
                'Date Run' => $sample->datetested,
                'Result' => $sample->interpretation,
                'Interpretation (Final Result)' => $sample->result,
                'Final Result (for IQC Upload)' => $res,
            ];

            if(env('APP_LAB') == 8){
                $row['Specimen Lab ID'] = $sample->label_id;
            }

            if(str_contains($res, ['failed', 'collect'])){
                $failed[] = $row;
                continue;
            }
            $data[] = $row;
        }

        foreach ($failed as $row) {
            $data[] = $row;
        }

        $filename = $worksheet_id;

        if(file_exists($filename)) unlink($filename);

        Excel::create($filename, function($excel) use($data) {
            $excel->sheet('Sheetname', function($sheet) use($data) {
                $sheet->fromArray($data);
            });
        })->download('csv');
    }

    public static function find_no_result()
    {
        ini_set("memory_limit", "-1");
        $samples = Viralsample::whereNotNull('interpretation')->whereRaw("(result is null or result = '')")->get();

        $cutoff = Carbon::parse('2018-11-01');

        foreach ($samples as $sample) {
            $data = self::sample_result($sample->interpretation);
            $sample->fill($data);
            if($sample->result != '' || $sample->result != 'Invalid' || $sample->result != 'Failed' || $sample->result != 'Collect New Sample'){
                $sample->repeatt = 0;
                $sample->save();
            }
            else{
                $datetested = Carbon::parse($sample->datetested);

                if($cutoff->greaterThan($datetested)){

                    $sample->result = "Collect New Sample";
                    $sample->labcomment = "Failed Test";
                    $sample->repeatt = 0;
                    $sample->save();

                }
                else{
                    $sample->repeatt = 1;
                    $sample->save();
                    self::save_repeat($sample->id);   
                }
            }
        }
    }

    public static function find_no_reruns()
    {
        ini_set("memory_limit", "-1");
        $samples = ViralsampleView::where(['repeatt' => 1])->whereNull('datedispatched')
                        ->whereBetween('datetested', ['2018-01-01', '2018-10-31'])
                        ->get();

        foreach ($samples as $s) {

            $sample = Viralsample::find($s->id);
            if($sample->has_rerun) continue;

            if($sample->result == 'Failed' || $sample->result == 'Invalid'){
                $sample->interpretation = $sample->result;
                $sample->result = "Collect New Sample";
                $sample->labcomment = "Failed Test";
                $sample->repeatt = 0;
                $sample->save();
            }
        }
    } 

    public static function find_not()
    {
        ini_set("memory_limit", "-1");
        $samples = Viralsample::where(['worksheet_id' => 148])->get();

        foreach ($samples as $key => $sample) {
            $sample->result = "Collect New Sample";
            $sample->labcomment = "Failed Test";
            $sample->repeatt = 0;
            $sample->save();
        }
    }

    public static function find_not_dispatched() {
        ini_set("memory_limit", "-1");
        $samples = ViralsampleView::where(['worksheet_id' => 148])->whereNull('datedispatched')
                        ->whereBetween('datetested', ['2018-01-01', '2018-10-31'])
                        ->get();

        foreach ($samples as $s) {

            $sample = Viralsample::find($s->id);
            if($sample->has_rerun) continue;

            if($sample->result == 'Failed' || $sample->result == 'Invalid'){
                $sample->interpretation = $sample->result;
                $sample->result = "Collect New Sample";
                $sample->labcomment = "Failed Test";
                $sample->repeatt = 0;
                $sample->save();
            }
        }
    } 

    public static function edarpsamplesforapproval() {
        $samples = Viralsample::selectRaw("count(if(date(created_at) = curdate(), 1, null)) as today, count(*) as total")->where('synched', 5)->first();
        $edarpUser = User::where('user_type_id', 8)->first();
        if ($edarpUser->count()){
            $form_url = URL::temporarySignedRoute(
                'nhrl', now()->addDays(1), ['user' => $edarpUser->id]
            );
            // This is because the application receives requests on http but forces it to https

            URL::forceScheme('http');

            $base_url = url('');
            if(env('APP_SECURE_PORT')) $base_url = str_before($base_url, ':' .  env('APP_SECURE_PORT'));

            URL::forceRootUrl($base_url);

            $url = URL::temporarySignedRoute('nhrl', now()->addDays(1), ['user' => $edarpUser->id]);

            if(env('APP_SECURE_PORT')) URL::forceRootUrl(url('') . ':' .  env('APP_SECURE_PORT'));
            if(env('APP_SECURE_URL')) URL::forceScheme('https');

            $new_signature = str_after($url, 'expires=');
            $old_signature = str_after($form_url, 'expires=');

            $form_url = str_replace($old_signature, $new_signature, $form_url);
        }
        $data = (object)['samples' => $samples, 'url' => $form_url];

        
        $mail_array = array('joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com');
        if(env('APP_ENV') == 'production') 
            $mail_array = ["David@edarp.org", "Jkarimi@edarp.org", "WilsonNdungu@edarp.org", "Chris@edarp.org", "Administrator@edarp.org", "mutewa@edarp.org", "Muma@edarp.org", "kouma@mgic.umaryland.edu", "EKirui@mgic.umaryland.edu", "tngugi@clintonhealthaccess.org", "tngugi@gmail.com", "Peter@edarp.org"];
        if(!$mail_array) return null;

        try {
            Mail::to($mail_array)->bcc(['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke'])
            ->send(new Edarp($data));
            
        } catch (Exception $e) {
            
        }
    }
    
}
