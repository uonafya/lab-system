<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SampleView;
use App\ViralsampleView;
use Excel;

class ReportController extends Controller
{
    //

    public function index()
    {
    	return view('shared.reports')->with('pageTitle', 'Lab Reports');
    }

    public function dateselect(Request $request)
    {
    	// Execute the query used to retrieve the data. In this example
	    // we're joining hypothetical users and payments tables, retrieving
	    // the payments table's primary key, the user's first and last name, 
	    // the user's e-mail address, the amount paid, and the payment
	    // timestamp.
	    $data = self::__getDateData($request)->get();
    	
    	// Initialize the array which will be passed into the Excel
	    // generator.
	    $dataArray = []; 

	    // Define the Excel spreadsheet headers
	    $dataArray[] = (session('testingSystem') == 'Viralload') ?
	    	['Lab ID', 'Patient CCC No', 'Patient Names', 'Provider Identifier', 'Testing Lab',	'County', 'Sub County',	'Facility Name', 'MFL Code', 'AMRS location', 'Sex', 'Age',	'Sample Type', 'Collection Date', 'Received Status', 'Rejected Reason / Reason for Repeat',	'Current Regimen', 'ART Initiation Date', 'Justification',	'Date of Receiving', 'Date of Testing',	'Date of Dispatch',	'Viral Load'] :
	    	['Lab ID', 'Sample Code', 'Batch No', 'Testing Lab', 'County', 'Sub County', 'Facility Name', 'MFL Code', 'Sex',	'DOB', 'Age(m)', 'Infant Prophylaxis', 'Date of Collection', 'PCR Type', 'Spots', 'Received Status', 'Rejected Reason / Reason for Repeat',	'HIV Status of Mother',	'PMTCT Intervention', 'Breast Feeding', 'Entry Point',	'Date of Receiving', 'Date of Testing',	'Date of Dispatch',	'Test Result'];

	    // Convert each member of the returned collection into an array,
	    // and append it to the payments array.
	    foreach ($data as $report) {
	        $dataArray[] = $report->toArray();
	    }
	    
	    $report = (session('testingSystem') == 'Viralload') ? 'TESTOUTCOME REPORT FOR VL' : 'TESTOUTCOME REPORT FOR EID';
	    // Generate and return the spreadsheet
	    Excel::create($report, function($excel) use ($dataArray) {
	    	$newreport = (session('testingSystem') == 'Viralload') ? 'TESTOUTCOME REPORT FOR VL' : 'TESTOUTCOME REPORT FOR EID';
	        // Set the spreadsheet title, creator, and description
	        $excel->setTitle($newreport);
	        $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('WJ Gilmore, LLC');
	        $excel->setDescription('Report');

	        // Build the spreadsheet, passing in the payments array
	        $excel->sheet($newreport, function($sheet) use ($dataArray) {
	            $sheet->fromArray($dataArray, null, 'A1', false, false);
	        });

	    })->download('xlsx');
    	
    	return back();
    }

    public function generate(Request $request)
    {
    	dd($request);
    }

    public static function __getDateData($request)
    {
    	if (session('testingSystem') == 'Viralload') {
    		$table = 'viralsamples_view';
    		$model = ViralsampleView::select('viralsamples_view.id','viralsamples_view.patient','viralsamples_view.patient_name','viralsamples_view.provider_identifier', 'labs.labdesc', 'view_facilitys.countyname', 'view_facilitys.subcounty', 'view_facilitys.name as facility', 'view_facilitys.facilitycode', 'viralsamples_view.amrs_location', 'gender.gender', 'viralsamples_view.dob', 'viralsampletype.name as sampletype', 'viralsamples_view.datecollected', 'receivedstatus.name as receivedstatus', 'viralrejectedreasons.name as rejectedreason', 'viralprophylaxis.name as regimen', 'viralsamples_view.initiation_date', 'viraljustifications.name as justification', 'viralsamples_view.datereceived', 'viralsamples_view.datetested', 'viralsamples_view.datedispatched', 'viralsamples_view.result')
    				->leftJoin('labs', 'labs.id', '=', 'viralsamples_view.lab_id')
    				->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
    				->leftJoin('gender', 'gender.id', '=', 'viralsamples_view.sex')
    				->leftJoin('viralsampletype', 'viralsampletype.id', '=', 'viralsamples_view.sampletype')
    				->leftJoin('receivedstatus', 'receivedstatus.id', '=', 'viralsamples_view.receivedstatus')
    				->leftJoin('viralrejectedreasons', 'viralrejectedreasons.id', '=', 'viralsamples_view.rejectedreason')
    				->leftJoin('viralprophylaxis', 'viralprophylaxis.id', '=', 'viralsamples_view.prophylaxis')
    				->leftJoin('viraljustifications', 'viraljustifications.id', '=', 'viralsamples_view.justification');
    	} else {
    		$table = 'samples_view';
    		$model = SampleView::select('samples_view.id','samples_view.patient', 'samples_view.batch_id', 'labs.labdesc', 'view_facilitys.countyname', 'view_facilitys.subcounty', 'view_facilitys.name as facility', 'view_facilitys.facilitycode', 'gender.gender', 'samples_view.dob', 'samples_view.age', 'ip.name as infantprophylaxis', 'samples_view.datecollected', 'pcrtype.alias as pcrtype', 'samples_view.spots', 'receivedstatus.name as receivedstatus', 'rejectedreasons.name as rejectedreason', 'mr.name as motherresult', 'mp.name as motherprophylaxis', 'feedings.feeding', 'entry_points.name as entrypoint', 'samples_view.datereceived', 'samples_view.datetested', 'samples_view.datedispatched', 'ir.name as infantresult')
    				->leftJoin('labs', 'labs.id', '=', 'samples_view.lab_id')
    				->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
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
    				->leftJoin('results as mr', 'mr.id', '=', 'mothers.hiv_status');
    	}
    	
    	if (isset($request->specificDate)) {
    		$model = $model->where("$table.datereceived", '=', $request->specificDate);
    	}else {
    		$model = $model->whereRaw("$table.datereceived BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
    	}

    	return $model;
    }
}
