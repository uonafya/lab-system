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
        $mail_array = ['fjepkoech@healthit.uonbi.ac.ke'];
        $min_date = date('Y-m-d', strtotime('-7 days'));
        $filename='';
        // ini_set("max_execution_time", "720");
        $columns=('viralsamples_view.id,viralsamples_view.batch_id,viralsamples_view.worksheet_id,machines.machine as platform,viralsamples_view.patient,viralsamples_view.patient_name,viralsamples_view.provider_identifier,labs.name,IF(viralsamples_view.site_entry = 2, poc_lab.name, labs.labdesc) as `labdesc`,
        view_facilitys.partner,view_facilitys.county,view_facilitys.subcounty,view_facilitys.name as facility,view_facilitys.facilitycode,order_no as order_number,amrslocations.name as amrs_location,viralsamples_view.sex,recency_number,gender.gender_description,viralsamples_view.dob,viralsamples_view.age,
        viralpmtcttype.name as pmtct,viralsampletype.name as sampletype,viralsamples_view.datecollected,receivedstatus.name as receivedstatus,viralrejectedreasons.name as rejectedreason,viralregimen.name as regimen,viralsamples_view.initiation_date,viraljustifications.name as justification,
        viralsamples_view.datereceived,viralsamples_view.created_at,viralsamples_view.datetested,viralsamples_view.dateapproved,viralsamples_view.datedispatched,viralsamples_view.result,viralsamples_view.entered_by,users.surname');

        $viralsamples = ViralsampleView::selectRaw($columns)
            ->whereNull('datedispatched')
            ->whereNull('receivedstatus')
            ->whereNull('datetested')
            ->leftjoin ('users', 'users.id', '=', "viralsamples_view.user_id")
            ->leftjoin ('labs', 'labs.id', '=' ,"viralsamples_view.lab_id")
            ->leftjoin ('view_facilitys as poc_lab', 'poc_lab.id', '=', "viralsamples_view.lab_id")
            ->leftjoin ('view_facilitys','view_facilitys.id', '=', "viralsamples_view.facility_id")
            ->leftjoin ('amrslocations', 'amrslocations.id', '=', "viralsamples_view.amrs_location")
            ->leftjoin ('gender', 'gender.id', '=', "viralsamples_view.sex")
            ->leftjoin ('viralsampletype', 'viralsampletype.id', '=', "viralsamples_view.sampletype")
            ->leftjoin ('receivedstatus', 'receivedstatus.id', '=', "viralsamples_view.receivedstatus")
            ->leftjoin ('viralrejectedreasons', 'viralrejectedreasons.id', '=', "viralsamples_view.rejectedreason")
            ->leftjoin ('viralregimen', 'viralregimen.id', '=', "viralsamples_view.prophylaxis")
            ->leftjoin ('viraljustifications', 'viraljustifications.id', '=', "viralsamples_view.justification")
            ->leftjoin ('viralpmtcttype','viralpmtcttype.id', '=', "viralsamples_view.pmtct")
            ->leftjoin ('viralworksheets', 'viralworksheets.id', '=', "viralsamples_view.worksheet_id")
            ->leftjoin ('machines','machines.id', '=', "viralworksheets.machine_type")
            ->get();

        extract(Lookup::untested_vl_form());

        $data = [];

        foreach ($viralsamples as $key => $viralsample) {
            $row = [
                'Lab ID' => $viralsample->id,
                'Batch #'=>$viralsample->batch_id,
                'Worksheet #' => $viralsample->worksheet_id,
                'Plaform'=>$viralsample->platform,
                'Patient CCC No' => $viralsample->patient,
                'Patient Names' => $viralsample->patient_name,
                'Provider Identifier' => $viralsample->provider_identifier,
                'Testing Lab' => $viralsample->name,
                'Partner' => $viralsample->partner,
                'County' => $viralsample->countyname ?? $viralsample->county,
                'Subcounty' => $viralsample->sub_county ?? $viralsample->subcountyname ?? $viralsample->subcounty ?? '',
                'Facility Name' => $viralsample->facility,
                'MFL Code' => $viralsample->facilitycode,
                'Order Number' => $viralsample->order_number,
                'AmrsLocation' => $viralsample->get_prop_name($amrslocations, 'amrs_location', 'name'),
                'Recency Number' => $viralsample->recency_number,
                'Sex' => $viralsample->get_prop_name($gender, 'sex', 'gender_description'),
                'DOB' => $viralsample->dob,
                'Age' => $viralsample->age,
                'PMTCT' => $viralsample->get_prop_name($viralpmtcttype, 'pmtct','name'),
                'Sample Type' => $viralsample->get_prop_name($viralsampletype, 'sampletype','name'),
                'Entry Point' => $viralsample->entry_point,
                'Collection Date' => $viralsample->my_date_format('datecollected'),
                'Received Status' => $viralsample->get_prop_name($receivedstatus, 'receivedstatus','name'),
                'Rejected Reason / Reason for Repeat' => $viralsample->get_prop_name($viralrejectedreasons, 'rejectedreason','name'),
                'Current Regimen' => $viralsample->get_prop_name($viralregimen, 'regimen', 'name'),
                'ART Initiation Date' => $viralsample->my_date_format('initiation_date'),
                'Justification' => $viralsample->get_prop_name($viraljustifications, 'justification', 'name'),
                'Date Received' => $viralsample->my_date_format('datereceived'),
                'Date Entered' => $viralsample->my_date_format('created_at'),
                'Date of Tested' => $viralsample->my_date_format('datetested'),
                'Date of Approval' => $viralsample->my_date_format('created_at'),
                'Date of Dispatch' => $viralsample->my_date_format('created_at'),
                'Viral Load' => $viralsample->get_prop_name($results, 'result','name'),
                'Entered By' => $viralsample->entered_by,
                'Received By' => $viralsample->received_by,
                'Entry' => $viralsample->creator->full_name ?? null,
            ];
            $data[] = $row;
        }
        $filename="VL-untested-samples";
        Untested::csv_download($data, 'VL-untested-samples', true, true);

        $data = [storage_path("exports/" . $filename . ".csv")];
        //eid
        $columns=('samples_view.id ,samples_view.batch_id,samples_view.worksheet_id,
       machines.machine as platform,samples_view.patient,samples_view.patient_name,labs.name,
       view_facilitys.partner,view_facilitys.county,view_facilitys.subcounty,view_facilitys.name as facility,view_facilitys.facilitycode, order_no as order_number,samples_view.amrs_location,samples_view.sex,samples_view.dob,samples_view.age,samples_view.datecollected,samples_view.pcrtype,samples_view.spots,samples_view.entry_point,
       samples_view.mother_age,samples_view.datereceived,samples_view.datetested,samples_view.dateapproved,samples_view.feeding,samples_view.datedispatched,samples_view.result,samples_view.entered_by,users.surname');

        $samples = SampleView::selectRaw($columns)
            ->whereNull('datedispatched')
            ->whereNull('receivedstatus')
            ->whereNull('datetested')
            ->leftJoin('users', 'users.id', '=', "samples_view.user_id")
            ->leftJoin('labs', 'labs.id', '=', 'samples_view.lab_id')
            ->leftJoin('view_facilitys as poc_lab', 'poc_lab.id', '=', "samples_view.lab_id")
            ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "samples_view.facility_id")
            ->leftJoin('amrslocations', 'amrslocations.id', '=', 'samples_view.amrs_location')
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

        extract(Lookup::untested_form());

        $data = [];

        foreach ($samples as $key => $sample) {
            $row = [
                'Lab ID' => $sample->id,
                'Batch #'=>$sample->batch_id,
                'Worksheet #' => $sample->worksheet_id,
                'Plaform'=>$sample->platform,
                'Sample Code' => $sample->patient,
                'Infant Name' => $sample->patient_name,
                'Testing Lab' => $sample->name,
                'Partner' => $sample->partner,
                'County' => $sample->countyname ?? $sample->county,
                'Subcounty' => $sample->sub_county ?? $sample->subcountyname ?? $sample->subcounty ?? '',
                'Facility Name' => $sample->facility,
                'MFL Code' => $sample->facilitycode,
                'Order Number' => $sample->order_number,
                'AmrsLocation' => $sample->get_prop_name($amrslocations, 'amrs_location', 'name'),
                'Sex' => $sample->get_prop_name($gender, 'sex', 'gender_description'),
                'DOB' => $sample->dob,
                'Age' => $sample->age,
                'Infant Prophylaxis'=>$sample->get_prop_name($prophylaxis, 'regimen','name'),
                'Date Of Collection' => $sample->my_date_format('datecollected'),
                'PCR Type' => $sample->get_prop_name($pcrtype, 'pcrtype', 'alias'),
                'Spots' => $sample->spots,
                'Received Status' => $sample->get_prop_name($receivedstatus, 'receivedstatus','name'),
                'Rejected Reason / Reason for Repeat' => $sample->get_prop_name($rejectedreasons, 'rejectedreason','name'),
                'HIV Status of Mother' => $sample->get_prop_name($results, 'mother_last_result','name'),
                'Mother Age' => $sample->mother_age,
//                'PMTCT Intervention' => $sample->age,
                'Breast Feeding' => $sample->get_prop_name($feedings, 'feeding','feeding'),
                'Entry Point' => $sample->get_prop_name($entry_points, 'entry_point','name'),
                'Date Received' => $sample->my_date_format('datereceived'),
                'Date Entered' => $sample->my_date_format('created_at'),
                'Date Tested' => $sample->my_date_format('datetested'),
                'Date of Approval' => $sample->my_date_format('dateapproved'),
                'Date of Dispatch' => $sample->my_date_format('datedispatched'),
                'Test Result' => $sample->get_prop_name($results, 'result','name'),
                'Entered By' => $sample->entered_by,
                'Received By' => $sample->received_by,
                'Entry' => $sample->get_prop_name($users, 'surname', 'surname')
                ];
            $data[] = $row;
        }
        $filename2="EID-untested-samples";
        Untested::csv_download($data, 'EID-untested-samples', true, true);

        $data = [storage_path("exports/" . $filename . ".csv"),storage_path("exports/" . $filename2 . ".csv")];
        echo 'sending mail';
        Mail::to($mail_array)->send(new UntestedSamples($data));
        unlink(storage_path("exports/" . $filename . ".csv"));
        unlink(storage_path("exports/" . $filename2 . ".csv"));

    }

}
