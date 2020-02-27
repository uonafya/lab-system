<?php

namespace App\Exports;

use DB;
use \App\DrSample;

class DrDetailedReportExport extends BaseExport
{
    function __construct($request)
    {
    	parent::__construct();
		$this->request = $request;

		$this->sql = "
            facilitycode AS `MFL Code`, view_facilitys.name AS `Facility`, patient AS `CCC Number`, dob AS `Date of Birth`,
            age, 
            datecollected, datereceived, datetested, datedispatched
		";
    }

    public function headings() : array
    {
		$row = DrSample::selectRaw($this->sql)
            ->leftJoin('viralpatients', 'dr_samples.patient_id', '=', 'viralpatients.id')
            ->leftJoin('view_facilitys', 'viralpatients.facility_id', '=', 'view_facilitys.id')
			->first();

		return collect($row)->keys()->all();
    }


    public function query()
    {		
        $string = $this->facility_query;

        return DrSample::selectRaw($this->sql)
            ->leftJoin('viralpatients', 'dr_samples.patient_id', '=', 'viralpatients.id')
            ->leftJoin('view_facilitys', 'viralpatients.facility_id', '=', 'view_facilitys.id')
            ->when($string, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->where(['status_id' => 1, 'control' => 0, 'repeatt' => 0])
            ->when(true, $this->date_filter($request, 'datetested'))
            ->when(true, $this->divisions_filter($request));
    }
}
