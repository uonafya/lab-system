<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use \App\Misc;
use \App\Sample;

class WorksheetImport implements ToCollection
{
	protected $worksheet;
	protected $cancelled;
    protected $daterun;

	public function __construct($worksheet, $request)
	{
        $cancelled = false;
        if($worksheet->status_id == 4) $cancelled =  true;
        $this->cancelled = $cancelled;
        $this->daterun = $request->input('daterun');
	}

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
    	$worksheet = $this->worksheet;
    	$cancelled = $this->cancelled;
        $today = $datetested = date("Y-m-d");
        $positive_control = $negative_control = null;

        $sample_array = $doubles = [];

        if($worksheet->machine_type == 2)
        {
            $date_tested = $this->daterun;
            $datetested = Misc::worksheet_date($date_tested, $worksheet->created_at);

            $check = array();

            $bool = false;
            $positive_control = $negative_control = "Passed";

            foreach ($collection as $key => $value) {
                if($value[5] == "RESULT"){
                    $bool = true;
                    continue;
                }

                if($bool){
                    $sample_id = $value[1];
                    $interpretation = $value[5];
                    $error = $value[10];


                    Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                    $data_array = Misc::sample_result($interpretation, $error);

                    if($sample_id == "HIV_NEG") $negative_control = $data_array;
                    if($sample_id == "HIV_HIPOS") $positive_control = $data_array;

                    $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $today]);
                    // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
                    // Sample::where($search)->update($data_array);

                    $sample_id = (int) $sample_id;
                    $sample = Sample::find($sample_id);
                    if(!$sample) continue;

                    $sample->fill($data_array);
                    if($cancelled) $sample->worksheet_id = $worksheet->id;
                    else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;

                    $sample->save();
                }

                if($bool && $value[5] == "RESULT") break;
            }
        }
        else
        {
            foreach ($collection as $key => $data) 
            {
                $interpretation = rtrim($data[8]);
                $control = rtrim($data[5]);

                $error = $data[10];

                $date_tested=date("Y-m-d", strtotime($data[3]));

                $datetested = Misc::worksheet_date($date_tested, $worksheet->created_at);

                $data_array = Misc::sample_result($interpretation, $error);

                if($control == "NC") $negative_control = $data_array;

                if($control == "LPC" || $control == "PC") $positive_control = $data_array;

                $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $datetested]);

                $sample_id = (int) trim($data[4]);  

                Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

                // $sample_id = substr($sample_id, 0, -1);
                $sample = Sample::find($sample_id);
                if(!$sample) continue;

                $sample->fill($data_array);
                if($cancelled) $sample->worksheet_id = $worksheet->id;
                else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;
                    
                $sample->save();

            }
        }

        /*if($doubles){
            session(['toast_error' => 1, 'toast_message' => "Worksheet {$worksheet->id} upload contains duplicate rows. Please fix and then upload again."]);
            $file = "Samples_Appearing_More_Than_Once_In_Worksheet_" . $worksheet->id;
        
            Excel::create($file, function($excel) use($doubles){
                $excel->sheet('Sheetname', function($sheet) use($doubles) {
                    $sheet->fromArray($doubles);
                });
            })->download('csv');
        }*/

        // $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        Sample::where(['worksheet_id' => $worksheet->id, 'run' => 0])->update(['run' => 1]);
        Sample::where(['worksheet_id' => $worksheet->id])->whereNull('repeatt')->update(['repeatt' => 0]);
        Sample::where(['worksheet_id' => $worksheet->id])->whereNull('result')->update(['repeatt' => 1]);

        $worksheet->neg_control_interpretation = $negative_control['interpretation'];
        $worksheet->neg_control_result = $negative_control['result'];

        $worksheet->pos_control_interpretation = $positive_control['interpretation'];
        $worksheet->pos_control_result = $positive_control['result'];
        $worksheet->daterun = $datetested;
        $worksheet->uploadedby = auth()->user()->id;
        $worksheet->save();

        Misc::requeue($worksheet->id, $worksheet->daterun);
        session(['toast_message' => "The worksheet has been updated with the results."]);
    }
}
