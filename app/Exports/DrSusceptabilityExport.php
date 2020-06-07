<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use \Maatwebsite\Excel\Sheet;

use DB;
use \App\MiscDr;
use \App\DrSample;


class DrSusceptabilityExport implements FromArray, WithEvents, Responsable
{
    use Exportable;
    use RequestFilters;

    
	public $request;
    protected $fileName;

	public function __construct($request)
	{
        $this->fileName = $this->get_name('DR Susceptablity Report', $request) . '.xlsx';
        Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
            $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
        }); 
		$this->request = $request;
	}

    public function array(): array
    {
    	$request = $this->request;
        $cell_array = MiscDr::$call_array;
        // dd($cell_array);
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
                            $cell_array[$call]['cells'][] = chr(64 + 3 + $regimen_key) . ($sample_key + 3);
                            
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
        session(['cell_array' => $cell_array]);
        return $rows;
    }

    public function registerEvents(): array
    {
    	return [
    		AfterSheet::class => [self::class, 'afterSheet'],
    	];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $cell_array = session()->pull('cell_array');
        foreach ($cell_array as $my_call) {
            foreach ($my_call['cells'] as $my_cell) {
            	$colour = ltrim($my_call['resistance_colour'], '#');

            	$event->sheet->styleCells($my_cell, [
            		'fill' => [
            			'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_PATTERN_LIGHTUP,
            			'startColor' => [
            				'argb' => $colour,
            			],
            			'endColor' => [
            				'argb' => $colour,
            			],
            		]
            	]);
            }
        }
    }
}
