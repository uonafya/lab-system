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

        dd($row);


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
        	'patient_name' => $row->name,

        	'datecollected' => $datecollected,
        	'datereceived' => $datereceived,
        	'datetested' => $datetested,
        	'datedispatched' => $datedispatched,
        ]);
        $t->save();

    }
}
