<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use Str;
use \App\Facility;
use \App\QuarantineSite;
use \App\CovidPatient;
use \App\CovidSample;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KemriWRPImport implements OnEachRow, WithHeadingRow
{

    public function onRow(Row $row)
    {
        $row = json_decode(json_encode($row->toArray()));
        $column = 'quarantine_site_id';
        if($row->facility_name > 100) $column = 'facility_id';

        $p = CovidPatient::where(['identifier' => $row->identifier])->first();

        $mfl = $row->mfl ?? null;

        $fac = Facility::locate($mfl)->first();
        $quarantine_site = QuarantineSite::where('name', $row->facility_name)->first();

        if(!$p) $p = new CovidPatient;

        $p->fill([
            'identifier' => $row->identifier,
            'facility_id' => $fac->id ?? null,
            'quarantine_site_id' => $quarantine_site->id ?? null,
            'patient_name' => $row->full_name,
            'sex' => $row->gender,
            'national_id' => $row->id_passport,
            'phone_no' => $row->mobile_phone_no,
            'county' => $row->county_rep,
            'subcounty' => $row->sub_county_rep,                
        ]);
        $p->save();

        $sample_type = $row->specimen_source;
        if(Str::contains($sample_type, 'Oro') && Str::contains($sample_type, 'Naso')) $s = 1;
        else if(Str::contains($sample_type, 'Oro')) $s = 3;
        else if(Str::contains($sample_type, 'Naso')) $s = 2;
        else{
            $s = null;
        }

        $s = CovidSample::create([
            'patient_id' => $p->id,
            'lab_id' => 18,
            'kemri_id' => $row->kemri_id,
            'site_entry' => 0,
            'age' => $row->age,
            'test_type' => 1,
            'datecollected' => date('Y-m-d', strtotime($row->date_collected)),
            'datereceived' => date('Y-m-d', strtotime($row->date_received)),
            'datetested' => date('Y-m-d', strtotime($row->date_reported)),
            'datedispatched' => date('Y-m-d', strtotime($row->date_reported)),
            'dateapproved' => date('Y-m-d', strtotime($row->date_reported)),
            'receivedstatus' => 1,
            'sample_type' => $s,
            'result' => $row->preliminary_lab_results,
        ]);

    }
}
