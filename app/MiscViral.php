<?php

namespace App;

use App\Common;
use Carbon\Carbon;
use App\Viralsample;
use App\ViralsampleView;

use App\DrPatient;

class MiscViral extends Common
{

    protected $rcategories = [
        '0' => [],
        '1' => ['< LDL copies/ml', '< LDL copies', ],
        '2' => ['<550', '< 550 ', '<150', '<160', '<75', '<274', '<400', ' <400', '< 400', '<188', '<218', '<839', '< 21', '<40', '<20', '>20', '< 20', '22 cp/ml', '<218', '<1000'],
        '3' => ['>1000'],
        '4' => ['> 10000000', '>10,000,000', '>10000000', '>10000000'],
        '5' => ['Failed', 'Failed PREP_ABORT', 'Failed Test', 'Invalid', 'Collect New Sample', ]
    ];

    protected $compound_categories = [
        [
            'search_array' =>  ['Target  Not Detected', 'Target N ot Detected', 'Target Not  Detected', 'Target Not Detecetd', 'Target Not Detected', '< LDL copies/ml', '< LDL copies', 'Not Detected', '< LDL copies/ml', '<LDL copies/ml', '< LDL copies/ml', ' < LDL copies/ml', '< LDL'],
            'update_array' => ['rcategory' => 1, 'result' => '< LDL copies/ml', 'interpretation' => 'Target  Not Detected']
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
            'search_array' =>  ['REJECTED', 'Redraw New Sample', 'collect new samp', 'collect new saple', 'insufficient', 'Failed Collect New sample', 'Collect New Sample'],
            'update_array' => ['rcategory' => 5, 'result' => 'Collect New Sample', 'labcomment' => 'Failed Test']
        ],
    ];

	public static function requeue($worksheet_id)
	{
		$samples = Viralsample::where('worksheet_id', $worksheet_id)->get();

		// Default value for repeatt is 0

		foreach ($samples as $sample) {
			if($sample->result == "Failed" || $sample->result == "Invalid" || $sample->result == ""){
				$sample->repeatt = 1;
				$sample->save();
			}
		}
		return true;
	}

	public static function save_repeat($sample_id)
	{
        $original = Viralsample::find($sample_id);
        if($original->run == 4) return false;

		$sample = new Viralsample;        
        $fields = \App\Lookup::viralsamples_arrays();
        $sample->fill($original->only($fields['sample_rerun']));
        $sample->run++;
        if($original->parentid == 0) $sample->parentid = $original->id;
        
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
        if(in_array(env('APP_LAB'), $double_approval)){
            $where_query = "( receivedstatus=2 OR  (result IS NOT NULL AND result != 'Failed' AND repeatt = 0 AND approvedby IS NOT NULL AND approvedby2 IS NOT NULL) )";
        }
        else{
            $where_query = "( receivedstatus=2 OR  (result IS NOT NULL AND result != 'Failed' AND repeatt = 0 AND approvedby IS NOT NULL) )";
        }


		$total = Viralsample::where('batch_id', $batch_id)->where('parentid', 0)->get()->count();
		$tests = Viralsample::where('batch_id', $batch_id)
		->whereRaw($where_query)
		->get()
		->count();

		if($total == $tests){ 
            // DB::table('viralbatches')->where('id', $batch_id)->update(['batch_complete' => 2]);
			\App\Viralbatch::where('id', $batch_id)->update(['batch_complete' => 2]);
            return true;
            // self::save_tat(\App\SampleView::class, \App\Sample::class, $batch_id);
		}
        else{
            return false;
        }
	}

	public static function check_original($sample_id)
	{
		$lab = auth()->user()->lab_id;

		$sample = Viralsample::select('samples.*')
		->join('batches', 'samples.batch_id', '=', 'batches.id')
		->where(['batches.lab_id' => $lab, 'samples.id' => $sample_id])
		->get()
		->first();

		return $sample;
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
                    return $query->whereNull('result');
                }
                else if ($result == 1) {
                    return $query->where('result', '< LDL copies/ml');
                }
                else if ($result == 2) {
                    return $query->where('result', '!=', 'Failed')
                    ->where('result', '!=', 'Collect New Sample')
                    ->where('result', '!=', '< LDL copies/ml');
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
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public static function sample_result($result, $error)
    {
        if($result == 'Not Detected' || $result == 'Target Not Detected' || $result == 'Not detected' || $result == '<40 Copies / mL' || $result == '< 40Copies / mL ' || $result == '< 40 Copies/ mL')
        {
            $res= "< LDL copies/ml";
            $interpretation= $result;
            $units="";                        
        }

        else if($result == 'Collect New Sample')
        {
            $res= "Collect New Sample";
            $interpretation="Collect New Sample";
            $units="";                         
        }

        else if($result == 'Failed' || $result == '')
        {
            $res= "Failed";
            $interpretation = $error;
            $units="";                         
        }

        else{
            $res = preg_replace("/[^<0-9]/", "", $result);
            $interpretation = $result;
            $units="cp/mL";
        }

        return ['result' => $res, 'interpretation' => $interpretation, 'units' => $units];
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
        if($age > 0.00001 && $age < 2){ return 6; }
        else if($age >= 2 && $age < 10){ return 7; }
        else if($age >= 10 && $age < 15){ return 8; }
        else if($age >= 15 && $age < 19){ return 9; }
        else if($age >= 19 && $age < 24){ return 10; }
        else if($age >= 24){ return 11; }
        else{ return 0; }
    }

    public function set_rcategory($result, $repeatt=null)
    {
        if(!$result) return ['rcategory' => 0];
        if(is_numeric($result)){
            if($result > 0 && $result < 1001){
                return ['rcategory' => 2];
            }
            else if($result > 1000 && $result < 5001){
                return ['rcategory' => 3];
            }
            else if($result > 5000){
                return ['rcategory' => 4];
            }
        }
        $data = $this->get_rcategory($result);
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
        $min_date = Carbon::now()->subMonths(3)->toDateString();

        $samples = ViralsampleView::select('patient_id', 'datereceived', 'result', 'rcategory', 'age', 'pmtct', 'datetested')
            ->where('batch_complete', 1)
            ->whereIn('rcategory', [3, 4])
            ->where('datereceived', '>', $min_date)
            ->where('repeatt', 0)
            ->whereRaw("patient_id NOT IN (SELECT distinct patient_id from dr_patients)")
            ->get();

        foreach ($samples as $sample) {
            $data = $sample->only(['patient_id', 'datereceived', 'result', 'rcategory']);
            if($sample->age < 19){
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

        $sql = "SELECT * FROM viralsamples WHERE patient_id={$patient_id} AND datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$datetested}')
        "; 
        $sample = \DB::select($sql);

        if($sample->rcategory == 1 || $sample->rcategory == 2) return false;

        $recent_date = Carbon::parse($datetested);
        $prev_date = Carbon::parse($sample->datetested);

        $months = $recent_date->diffInMonths($prev_date);
        if($months < 3){

            $sql = "SELECT * FROM viralsamples WHERE patient_id={$patient_id} AND datetested=
                        (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$sample->datetested}')
            "; 
            $sample = \DB::select($sql);

            if($sample->rcategory == 3 || $sample->rcategory == 4) return true;
            return false;
        }
        else{
            return true;
        }
    }
    
}
