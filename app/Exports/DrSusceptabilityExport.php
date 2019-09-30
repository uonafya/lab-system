<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use DB;
use \App\MiscDr;
use \App\DrSample;


class DrSusceptabilityExport extends BaseExport implements FromArray, WithEvents
{
	public $cell_array;
	public $request;

	public function __construct($request)
	{
		$this->request = $request;
		$this->cell_array = [];
	}

    public function array(): array
    {
    	$request = $this->request;
        $cell_array = MiscDr::$call_array;
        $regimen_classes = DB::table('regimen_classes')->get();
        $date_column = "datedispatched";
        $user = auth()->user();
        $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}')";

        $samples = DrSample::select('dr_samples.*')
            ->where(['status_id' => 1, 'control' => 0, 'repeatt' => 0])
            ->leftJoin('viralpatients', 'dr_samples.patient_id', '=', 'viralpatients.id')
            ->leftJoin('view_facilitys', 'viralpatients.facility_id', '=', 'view_facilitys.id')
            ->with(['dr_call.call_drug', 'patient'])
            ->when(($user->user_type_id == 5), function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->when(true, $this->date_filter($request, $date_column))
            ->when(true, $this->divisions_filter($request))
            ->get();

        $top = ['', 'Drug Classes', ];
        $second = ['Sequence ID', 'Original Sample ID', ];

        foreach ($regimen_classes as $key => $value) {
            $top[] = $value->drug_class;
            $second[] = $value->short_name;
        }

        $rows[0] = $top;
        $rows[1] = $second;

        foreach ($samples as $sample_key => $sample) {
            $patient_string = $sample->patient->patient ?? '';
            $row = [$sample->id, $patient_string];

            foreach ($regimen_classes as  $regimen_key => $regimen) {
                $call = '';

                foreach ($sample->dr_call as $dr_call) {
                    foreach ($dr_call->call_drug as $call_drug) {
                        if($call_drug->short_name_id == $regimen->id){
                            $call = $call_drug->call;
                            $cell_array[$call]['cells'][] = chr(64 + 3 + $regimen_key) . ($sample_key + 4);
                            
                            // $beginning = '';

                            // $char_key = $regimen_key + 3;
                            // if($char_key > 26){
                            //     $a = (int) ($char_key / 26);
                            //     $beginning = chr(64 + $a);
                            //     $char_key = $char_key % 26;
                            // }

                            // $call_array[$call]['cells'][] = $beginning . chr(64 + $char_key) . ($sample_key + 4);
                        }
                    }
                }
                $row[] = $call;
            }
            $rows[] = $row;
        }
        $this->cell_array = $cell_array;
        dd($cell_array);
        return $rows;
    }

    public function registerEvents(): array
    {
        $cell_array = $this->cell_array;
        dd($cell_array);
    	return [
    		AfterSheet::class => function(AfterSheet $event) use ($cell_array){
                foreach ($cell_array as $my_call) {
                    foreach ($my_call['cells'] as $my_cell) {
                    	$event->sheet->getActiveSheet()->getStyle($my_cell)->getFill()->getStartColor()->setARGB($my_call['resistance_colour']);
                    }
                }    			
    		},
    	];
    }
}
