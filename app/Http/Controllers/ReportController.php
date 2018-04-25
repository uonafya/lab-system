<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SampleView;
use App\ViralsampleView;

class ReportController extends Controller
{
    //

    public function index()
    {
    	return view('shared.reports')->with('pageTitle', 'Lab Reports');
    }

    public function dateselect(Request $request)
    {
    	#	DOB	Age(m)	Infant Prophylaxis	Date of Collection	PCR Type	Spots	Received Status	Rejected Reason / Reason for Repeat	HIV Status of Mother	PMTCT Intervention	Breast Feeding	Entry Point	Date of Receiving	Date of Testing	Date of Dispatch	Test Result

    	$model = SampleView::select('samples_view.patient', 'labs.labdesc', 'view_facilitys.countyname', 'view_facilitys.subcounty', 'view_facilitys.name', 'view_facilitys.facilitycode', 'gender.name', 'samples_view.dob', 'samples_view.age')
    				->join('labs', 'labs.id', '=', 'samples_view.lab_id')
    				->join('view_facilitys', 'view_facilitys.id', '=', 'samples_view.facility_id')
    				->join('gender', 'gender.id', '=', 'samples_view.sex')
    				->join('prophylaxis as ip', 'ip.id', '=', 'samples_view.regimen')
    				->join('prophylaxis as mp', 'mp.id', '=', 'samples_view.mother_prophylaxis')
    				->join('pcrtype', 'pcrtype.id', '=', 'samples_view.pcrtype')
    				->join('receivedstatus', 'receivedstatus.id', '=', 'samples_view.receivedstatus')
    				->join('rejectedreasons', 'rejectedreasons.id', '=', 'samples_view.rejectedreason')
    				->join('feedings', 'feedings.id', '=', 'samples_view.feeding')
    				->join('entry_points', 'entry_points.id', '=', 'samples_view.entry_point')
    				->join('results as ir', 'ir.id', '=', 'samples_view.result')
    				->join('mothers', 'mothers.id', '=', 'samples_view.mother_id')
    				->join('results as mr', 'mr.id', '=', 'mothers.hiv_status')->paginate(10);

    	dd($model);
    	if (isset($request->specificDate)) {
    		
    	}else {

    	}
    	
    }

    public function generate(Request $request)
    {
    	dd($request);
    }
}
