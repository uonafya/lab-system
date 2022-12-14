<?php

namespace App;

use DB;

use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use App\Mail\TestMail;
use App\Mail\UntestedSamples;


use Excel;


class Untested extends Common
{
    public static function download_excel()
    {
        ini_set("memory_limit", "-1");
        // ini_set("max_execution_time", "720");
        $columns=('samples_view.id ,samples_view.batch_id,samples_view.worksheet_id,
       machines.machine as platform,samples_view.patient,samples_view.patient_name,labs.name,
       view_facilitys.partner,view_facilitys.county,view_facilitys.subcounty,view_facilitys.name as facility,
       view_facilitys.facilitycode, order_no as order_number,samples_view.sex,samples_view.dob,samples_view.age,samples_view.datecollected,samples_view.pcrtype,samples_view.entry_point,
       samples_view.datereceived,samples_view.datetested,samples_view.dateapproved,samples_view.result,samples_view.entered_by');

        $samples = SampleView::selectRaw($columns)
            ->whereNull('datedispatched')
            ->whereNull('receivedstatus')
            ->whereNull('datetested')
//            ->leftJoin('users', 'users.id', '=', "samples_view.user_id")
            ->leftJoin('labs', 'labs.id', '=', 'samples_view.lab_id')
            ->leftJoin('view_facilitys as poc_lab', 'poc_lab.id', '=', "samples_view.lab_id")
            ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "samples_view.facility_id")
            ->leftJoin('gender', 'gender.id', '=', 'samples_view.sex')
            ->leftJoin('prophylaxis as ip', 'ip.id', '=', 'samples_view.regimen')
            ->leftJoin('prophylaxis as mp', 'mp.id', '=', 'samples_view.mother_prophylaxis')
            ->leftJoin('pcrtype', 'pcrtype.id', '=', 'samples_view.pcrtype')
            ->leftJoin('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
            ->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'samples_view.rejectedreason')
            ->leftJoin('feedings', 'feedings.id', '=', 'samples_view.feeding')
            ->leftJoin('entry_points', 'entry_points.id', '=', 'samples_view.entry_point')
            ->leftJoin('results as ir', 'ir.id', '=', 'samples_view.result')
            ->leftJoin('mothers', 'mothers.id', '=', 'samples_view.mother_id')
            ->leftJoin('results as mr', 'mr.id', '=', 'mothers.hiv_status')
            ->leftJoin('worksheets', 'worksheets.id', '=', 'samples_view.worksheet_id')
            ->leftJoin('machines', 'machines.id', '=', 'worksheets.machine_type')
            ->get();
//        dd($samples);

        extract(Lookup::untested_form());

        $data = [];

        foreach ($samples as $key => $sample) {
            $row = [
                'Lab ID' => $sample->id,
                'Batch #'=>$sample->batch_id,
                'Worksheet #' => $sample->worksheet_id,
                'Plaform'=>$sample->platform,
                'Sample Code' => $sample->patient,
                'Patient Name' => $sample->patient_name,
                'Testing Lab' => $sample->name,
                'Partner' => $sample->partner,
                'County' => $sample->countyname ?? $sample->county,
                'Subcounty' => $sample->sub_county ?? $sample->subcountyname ?? $sample->subcounty ?? '',
                'Facility Name' => $sample->facility,
                'MFL Code' => $sample->facilitycode,
                'Order Number' => $sample->order_number,
                'Gender' => $sample->get_prop_name($gender, 'sex', 'gender_description'),
                'DOB' => $sample->dob,
                'Age' => $sample->age,
                'PCR Type' => $sample->pcrtype,
                'Entry Point' => $sample->entry_point,
                'Date Collected' => $sample->my_date_format('datecollected'),
                'Date Received' => $sample->my_date_format('datereceived'),
                'Date Tested' => $sample->my_date_format('datetested'),
                'Received Status' => $sample->get_prop_name($receivedstatus, 'receivedstatus'),
                'Result' => $sample->get_prop_name($results, 'result'),
                'Entered By' => $sample->creator->full_name ?? null,
                'Date Entered' => $sample->my_date_format('created_at'),
            ];
//            if(env('APP_LAB') == 1) $row['Kemri ID'] = $sample->kemri_id;
//            if(env('APP_LAB') == 25) $row['AMREF ID'] = $sample->kemri_id;
            $data[] = $row;
        }
        $mail_array = ['fjepkoech@healthit.uonbi.ac.ke','sinjiri@healthit.uonbi.ac.ke'];
        $filename="EID-untested-samples";
        Untested::csv_download($data, 'EID-untested-samples', true, true);

        $data = [storage_path("exports/" . $filename . ".csv")];
        echo 'sending mail';
        Mail::to($mail_array)->send(new UntestedSamples($data));

    }

}
