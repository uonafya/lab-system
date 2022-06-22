<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use \App\MiscCovid;
use \App\CovidSample;
use Str;

class CovidWorksheetImport implements ToCollection
{
	protected $worksheet;
	protected $cancelled;
    protected $daterun;

	public function __construct($worksheet, $request)
	{
        $cancelled = false;
        if($worksheet->status_id == 4) $cancelled =  true;
        $worksheet->fill($request->except(['_token', 'upload']));
        $this->cancelled = $cancelled;
        $this->worksheet = $worksheet;
        $this->daterun = $request->input('daterun', date("Y-m-d"));
        if(!$this->daterun) $this->daterun = date("Y-m-d");
	}

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
    	$worksheet = $this->worksheet;
    	$cancelled = $this->cancelled;


        $today = $datemodified = $datetested = $this->daterun;
        $positive_control = $negative_control = null;

        $sample_array = $doubles = $wrong_worksheet = [];


        // C8800
        if($worksheet->machine_type == 3){
            foreach ($collection as $key => $value) 
            {
                if(!isset($value[1])) break;
                if($value[0] == 'Test') continue;
                $sample_id = $value[1];

                $target1 = $value[6];
                $target2 = $value[7];
                $flag = $value[3];

                $result_array = MiscCovid::roche_sample_result($target1, $target2, $flag);

                MiscCovid::dup_worksheet_rows($doubles, $sample_array, $sample_id, $result_array['result']);

                if(!is_numeric($sample_id)){
                    $control = $value[4];
                    if(Str::contains($control, ['+'])){
                        $positive_control = $result_array;                       
                    }else{
                        $negative_control = $result_array; 
                    }
                    continue;
                }

                $sample_id = (int) $sample_id;
                $sample = CovidSample::find($sample_id);
                if(!$sample) continue;

                $sample->datetested = $datetested;
                $sample->fill($result_array);
                if($cancelled) $sample->worksheet_id = $worksheet->id;
                else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;
                $sample->save();
            }
        }
        // Abbott
        else if($worksheet->machine_type == 2){
            $bool = false;
            foreach ($collection as $key => $value) {
                if($value[5] == "RESULT"){
                    $bool = true;
                    continue;
                }

                if($bool){
                    $sample_id = $value[1];
                    $interpretation = $value[5];
                    $error = $value[10];

                    $data_array = MiscCovid::sample_result($interpretation, $error);


                    MiscCovid::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                    // if($sample_id == "COV-2_NEG") $negative_control = $data_array;
                    // if($sample_id == "COV-2_POS") $positive_control = $data_array;

                    if(!is_numeric($sample_id)){
                        $s = strtolower($sample_id);

                        if(Str::contains($s, 'neg')) $negative_control = $data_array;
                        else if(Str::contains($s, 'pos')) $positive_control = $data_array;

                    }

                    $data_array = array_merge($data_array, ['datetested' => $today]);
                    // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    // Sample::where($search)->update($data_array);

                    $sample_id = (int) $sample_id;
                    $sample = CovidSample::find($sample_id);
                    if(!$sample) continue;

                    $sample->fill($data_array);
                    if($cancelled) $sample->worksheet_id = $worksheet->id;
                    else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;

                    $sample->save();
                }

                if($bool && $value[5] == "RESULT") break;
            }
        }
        // Manual
        else if($worksheet->machine_type == 0){
            foreach ($collection as $key => $value) {
                $raw_sample_id = $value[0];

                $sample_id = (int) $raw_sample_id;
                $sample = CovidSample::find($sample_id);
                if(!$sample && !Str::contains($raw_sample_id, ['Control'])) continue;

                $res = strtolower($value[1]);
                $result = [];

                if(Str::contains($res, ['pos'])){
                    $result['result'] = 2;
                    // $sample->result = 2;
                }else if(Str::contains($res, ['neg'])){
                    $result['result'] = 1;
                    // $sample->result = 1;
                }else if(Str::contains($res, ['fai', 'invalid'])){
                    $result = ['result' => 3, 'repeatt' => 1];
                    // $sample->result = 3;
                    // $sample->repeatt = 1;
                }else if(Str::contains($res, ['coll'])){
                    $result = ['result' => 5];
                    // $sample->result = 5;
                }else if(Str::contains($res, ['pass', 'valid'])){
                    $result = ['result' => 6];
                }else if(Str::contains($res, ['inconclusive'])){
                    $result = ['result' => 9];
                }

                if(Str::contains($raw_sample_id, ['Control'])){
                    if(Str::contains($raw_sample_id, ['Positive'])){
                        $positive_control = $result;
                    }
                    else if(Str::contains($raw_sample_id, ['Negative'])){
                        $negative_control = $result;
                    }
                    continue;
                }

                $sample->repeatt=0;
                $sample->datetested = $today;
                if($result) $sample->fill($result);

                if($cancelled) $sample->worksheet_id = $worksheet->id;
                else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;

                $sample->save();
            }

        }
        else{
            session(['toast_error' => 1, 'toast_message' => 'The worksheet type is not supported.']);
            return back();
        }

        CovidSample::where(['worksheet_id' => $worksheet->id])->whereNull('result')->update(['repeatt' => 1]);

        $worksheet->neg_control_interpretation = $negative_control['interpretation'] ?? null;
        $worksheet->neg_control_result = $negative_control['result'] ?? null;

        $worksheet->pos_control_interpretation = $positive_control['interpretation'] ?? null;
        $worksheet->pos_control_result = $positive_control['result'] ?? null;
        $worksheet->daterun = $datetested;
        $worksheet->uploadedby = auth()->user()->id ?? null;
        $worksheet->save();

        session(compact('doubles'));

        session(['toast_message' => "The worksheet has been updated with the results."]);
    }
}
