<?php

namespace App\Imports;

use Str;
use \App\Facility;
use \App\QuarantineSite;
use \App\CovidPatient;
use \App\CovidSample;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WRPCovidImport implements OnEachRow, WithHeadingRow
{
    
    public function onRow(Row $row)
    {
        $row = json_decode(json_encode($row->toArray()));
        

        if(!isset($row->mfl_code)){
            session(['toast_error' => 1, 'toast_message' => 'MFL Code column is not present.']);
            return;
        }
        if(!isset($row->patient_name)){
            session(['toast_error' => 1, 'toast_message' => 'Patient Name column is not present.']);
            return;
        }
        if(!isset($row->identifier)){
            session(['toast_error' => 1, 'toast_message' => 'Identifier column is not present.']);
            return;
        }
        if(!isset($row->age)){
            session(['toast_error' => 1, 'toast_message' => 'Age column is not present.']);
            return;
        }
        if(!isset($row->gender)){
            session(['toast_error' => 1, 'toast_message' => 'Gender column is not present.']);
            return;
        }

        $mfl = (int) $row->mfl_code;

        $fac = Facility::locate($mfl)->first();
        if(!$fac) return;
        $p = null;

        if($row->national_id) $p = CovidPatient::where(['national_id' => ($row->national_id ?? null)])->whereNotNull('national_id')->where('national_id', '!=', 'No Data')->first();
        if(!$p) $p = CovidPatient::where(['identifier' => $row->identifier, 'facility_id' => $fac->id])->first();


        if(!$p) $p = new CovidPatient;

        $p->fill([
            'identifier' => $row->identifier,
            'facility_id' => $fac->id ?? null,
            'patient_name' => $row->patient_name,
            'sex' => $row->gender,
            'national_id' => $row->national_id ?? null,
            'phone_no' => $row->phone_number ?? null,
            'county' => $row->county ?? null,
            'subcounty' => $row->subcounty ?? null,   
            'occupation' => $row->occupation ?? null,   
            'justification' => 3,             
        ]);
        $p->save();

        // $datecollected = $row->date_collected ?? date('Y-m-d');

        $datecollected = ($row->date_collected ?? null) ? date('Y-m-d', strtotime($row->date_collected)) : date('Y-m-d');
        $datereceived = ($row->date_received ?? null) ? date('Y-m-d', strtotime($row->date_received)) : date('Y-m-d');

        $sample = CovidSample::where(['patient_id' => $p->id, 'datecollected' => $datecollected])->first();
        if(!$sample) $sample = new CovidSample;

        $sample->fill([
            'patient_id' => $p->id,
            'lab_id' => env('APP_LAB'),
            'site_entry' => 0,
            'age' => $row->age,
            'test_type' => 1,
            'datecollected' => $datecollected,
            'datereceived' => $datereceived,
            'receivedstatus' => 1,
            'sample_type' => 1,
        ]);
        $sample->pre_update();

    }
}
