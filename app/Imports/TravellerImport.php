<?php

namespace App\Imports;

use Str;
use \App\Traveller;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TravellerImport implements OnEachRow, WithHeadingRow
{

    public function onRow(Row $row)
    {
        $row_array = $row->toArray();
        $row = json_decode(json_encode($row->toArray()));


        if(!property_exists($row, 'idpassport')){
            session(['toast_error' => 1, 'toast_message' => 'ID/PASSPORT column is not present.']);
            return;
        }
        if(!property_exists($row, 'name_3_names')){
            session(['toast_error' => 1, 'toast_message' => 'NAME (3 NAMES) column is not present.']);
            return;
        }
        if(!property_exists($row, 'gen')){
            session(['toast_error' => 1, 'toast_message' => 'GEN column is not present.']);
            return;
        }
        if(!property_exists($row, 'agein_years')){
            session(['toast_error' => 1, 'toast_message' => 'AGE(in Years) column is not present.']);
            return;
        }
        if(!property_exists($row, 'mobile_no')){
            session(['toast_error' => 1, 'toast_message' => 'MOBILE NO column is not present.']);
            return;
        }
        if(!property_exists($row, 'citizenship')){
            session(['toast_error' => 1, 'toast_message' => 'CITIZENSHIP column is not present.']);
            return;
        }
        if(!property_exists($row, 'pcr_result')){
            session(['toast_error' => 1, 'toast_message' => 'PCR Result column is not present.']);
            return;
        }
        if(!property_exists($row, 'igm_test_result')){
            session(['toast_error' => 1, 'toast_message' => 'IgM Test result column is not present.']);
            return;
        }
        if(!property_exists($row, 'iggigm_result')){
            session(['toast_error' => 1, 'toast_message' => 'IgG/IgM Result column is not present.']);
            return;
        }


        $datecollected = ($row->date_collected ?? null) ? date('Y-m-d', strtotime($row->date_collected)) : date('Y-m-d');
        $datereceived = ($row->date_received ?? null) ? date('Y-m-d', strtotime($row->date_received)) : date('Y-m-d');
        $datetested = ($row->date_tested ?? null) ? date('Y-m-d', strtotime($row->date_tested)) : date('Y-m-d');
        $datedispatched = ($row->date_dispatched ?? null) ? date('Y-m-d', strtotime($row->date_dispatched)) : date('Y-m-d');

        if($datecollected == '1970-01-01') $datecollected = date('Y-m-d');
        if($datereceived == '1970-01-01') $datereceived = date('Y-m-d');
        if($datetested == '1970-01-01') $datetested = date('Y-m-d');
        if($datedispatched == '1970-01-01') $datedispatched = date('Y-m-d');

        $t = new Traveller;
        $t->fill([
        	'id_passport' => $row->idpassport,
        	'patient_name' => $row->name_3_names,
        	'marriage_status' => $row->status ?? null,
        	'age' => $row->agein_years,
        	'phone_no' => $row->mobile_no,
        	'citizenship' => $row->citizenship ?? null,
        	'county' => $row->county ?? null,
        	'residence' => $row->estate ?? null,
        	'sex' => $row->gen,

        	'result' => $row->pcr_result,
        	'igm_result' => $row->igm_test_result,
        	'igg_igm_result' => $row->iggigm_result,

        	'datecollected' => $datecollected,
        	'datereceived' => $datereceived,
        	'datetested' => $datetested,
        	'datedispatched' => $datedispatched,
        ]);
        $t->save();

    }
}
