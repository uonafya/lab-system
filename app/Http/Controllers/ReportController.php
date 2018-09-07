<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sample;
use App\SampleView;
use App\Viralsample;
use App\ViralsampleView;
use App\Abbotdeliveries;
use App\Taqmandeliveries;
use App\Abbotprocurement;
use App\Taqmanprocurement;
use Excel;
use App\ViewFacility;

class ReportController extends Controller
{
    public static $parent = ['ending','wasted','issued','request','pos'];
    public static $suffix = ['received','damaged'];

    public function index()
    {
    	return view('reports.reports')->with('pageTitle', 'Lab Reports');
    }

    public function dateselect(Request $request)
    {
    	$dateString = '';

	    $data = self::__getDateData($request, $dateString)->get();
    	$this->__getExcel($data, $dateString);
    	
    	return back();
    }

    public function generate(Request $request)
    {
        $dateString = '';
        
        $data = self::__getDateData($request,$dateString)->get();
        $this->__getExcel($data, $dateString);
        
        return back();
    }

    public function kits(Request $request)
    {
        if($request->method() == 'POST') {
            $platform = $request->platform;
            if ($platform == 'abbott') 
                $model = Abbotdeliveries::select('*');
            if ($platform == 'taqman')
                $model = Taqmandeliveries::select('*');
            
            if($request->types == 'eid') 
                $model->where('testtype', '=', 1);
            if($request->types == 'viralload') 
                $model->where('testtype', '=', 2);
            
            if($request->source == 'scms') 
                $model->where('source', '=', 1);
            if($request->source == 'lab') 
                $model->where('source', '=', 2);
            if ($request->source == 'kemsa') 
                $model->where('source', '=', 3);

            $year = $request->year;
            $model->whereRaw("YEAR(datereceived) = $year");
            if ($request->period == 'monthly') {
                $month = $request->month;
                $model->whereRaw("MONTH(datereceived) = $month");
            } else if ($request->period == 'quarterly') {
                $quarter = parent::_getQuarterMonths($request->quarter);
                $in = "in (";
                foreach ($quarter as $key => $value) {
                    if ($key == 2) {
                        $in .= $value;
                    } else {
                        $in .= $value.",";
                    }
                }
                $in .= ")";
                $model->whereRaw("MONTH(datereceived) $in");
            }
            $kits = $model->get();
            $value = $kits->first();
            // dd($request->all());
            if ($value) {
                $data['kits'] = $kits;
                if ($platform == 'abbott') {
                    if ($request->format == 'excel') {
                        
                        return back();
                    }
                    $data['abbottdata'] = (object) $this->abbottKits;
                    $data = (object) $data;
                    return view('reports.abbottkits', compact('data'))->with('pageTitle', '');
                }
                if ($platform == 'taqman'){
                    if ($request->format == 'excel') {
                        
                        return back();
                    }
                    $data['taqmandata'] = (object) $this->taqmanKits;
                    $data = (object) $data;
                    return view('reports.taqmankits', compact('data'))->with('pageTitle', '');
                }
            } else {
                session(['toast_message'=>'No Kits Deliveries were submitted for the selected criteria']);
                return back();
            }
        }
        return view('reports.kitsreport')->with('pageTitle', 'Kits Reports');
    }

    public function consumption(Request $request)
    {
        $data = [];
        $platform = $request->platform;
        if ($platform == 'abbott') {
            $model = Abbotprocurement::select('*');
            $kits = Abbotdeliveries::select('*');
            $sub = $this->abbottKits;
        }
        if ($platform == 'taqman') {
            $model = Taqmanprocurement::select('*');
            $kits = Taqmandeliveries::select('*');
            $sub = $this->taqmanKits;
        }


        if($request->types == 'eid') {
            $model->where('testtype', '=', 1);
            $kits->where('testtype', '=', 1);
            $tests = Sample::selectRaw("count(*) as `tests`")->join("worksheets", "worksheets.id", "=", "samples.worksheet_id")
                        ->when($platform, function($query) use ($platform) {
                            if ($platform == 'abbott')
                                return $query->where("worksheets.machine_type", "=", 2);
                            if ($platform == 'taqman')
                                return $query->where("worksheets.machine_type", "=", 1);
                        });
            $type = 'EID';
        }
        if($request->types == 'viralload') {
            $model->where('testtype', '=', 2);
            $kits->where('testtype', '=', 2);
            $tests = Viralsample::selectRaw("count(*) as `tests`")->join("viralworksheets", "viralworksheets.id", "=", "viralsamples.worksheet_id")
                        ->when($platform, function($query) use ($platform) {
                            if ($platform == 'abbott')
                                return $query->where("viralworksheets.machine_type", "=", 2);
                            if ($platform == 'taqman')
                                return $query->where("viralworksheets.machine_type", "=", 1);
                        });
            $type = 'VL';
        }
        $month = $request->month;
        $previousMonth = $month -1;

        $model->where('year', $request->year);
        $model->where('month', $month)->orWhere('month', $previousMonth);
        $tests->whereYear('datetested', $request->year);
        $tests->whereMonth('datetested', $month);
        // $model->where('lab_id', env('APP_LAB'));

        $kits->whereYear('datereceived', $request->year);
        $kits->whereMonth('datereceived', $month);
        // $kits->where('lab_id', env('APP_LAB'));

        $report = $model->get();
        $kits = $kits->get();
        $tests = $tests->first()->tests;
        $data = json_decode(json_encode([
                    'parent' => self::$parent,
                    'child' => $sub,
                    'kitsuffix' => self::$suffix
                ]));
        $newdata = [];
        $prevnewdata = [];
        $kitsdata = [];
        foreach ($data->parent as $parentkey => $parentvalue) {
            foreach ($data->child as $childkey => $childvalue) {
                $newdata[$parentvalue.$childvalue->alias] = 0;
                $prevnewdata[$parentvalue.$childvalue->alias] = 0;
            }
        }
        foreach ($data->kitsuffix as $kitsuffixkey => $kitsuffixvalue) {
            foreach ($data->child as $childkey => $childvalue) {
                $kitsdata[$childvalue->alias.$kitsuffixvalue] = 0;
                $kitsdata[$childvalue->alias.'lotno'] = '';
            }
        }

        foreach ($data->parent as $parentkey => $parentvalue) {
            foreach ($data->child as $childkey => $childvalue) {
                foreach ($report as $reportkey => $reportvalue) {
                    $column = $parentvalue.$childvalue->alias;
                    if ($month == $reportvalue->month) {
                        $newdata[$parentvalue.$childvalue->alias] += $reportvalue->$column;
                    } else if ($previousMonth == $reportvalue->month) {
                        $prevnewdata[$parentvalue.$childvalue->alias] += $reportvalue->$column;
                    }
                }
            }
        }
        foreach ($data->kitsuffix as $kitsuffixkey => $kitsuffixvalue) {
            foreach ($data->child as $childkey => $childvalue) {
                foreach ($kits as $kitskey => $kitsvalue) {
                    $column = $childvalue->alias.$kitsuffixvalue;
                    $columnlot = $childvalue->alias.'lotno';
                    $kitsdata[$childvalue->alias.$kitsuffixvalue] += $kitsvalue->$column;
                    $kitsdata[$childvalue->alias.'lotno'] .= $kitsvalue->$columnlot;
                }
            }
        }
        $viewdata = (object)[
                        'reports' => $newdata,
                        'prevreport' => $prevnewdata,
                        'kitsreport' => $kitsdata,
                        'tests' => $tests,
                        'type' => $type
                    ];
        // $reports = $newdata;
        // dd($data);
        return view('reports.consumptionreport', compact('data', 'viewdata'))->with('pageTitle', 'Consumption Report');
    }

    public static function __getDateData($request, &$dateString)
    {
        $title = '';
    	if (session('testingSystem') == 'Viralload') {
    		$table = 'viralsamples_view';
    		$model = ViralsampleView::select('viralsamples_view.id','viralsamples_view.patient','viralsamples_view.patient_name','viralsamples_view.provider_identifier', 'labs.labdesc', 'view_facilitys.county', 'view_facilitys.subcounty', 'view_facilitys.name as facility', 'view_facilitys.facilitycode', 'viralsamples_view.amrs_location', 'gender.gender_description', 'viralsamples_view.dob', 'viralsampletype.name as sampletype', 'viralsamples_view.datecollected', 'receivedstatus.name as receivedstatus', 'viralrejectedreasons.name as rejectedreason', 'viralprophylaxis.name as regimen', 'viralsamples_view.initiation_date', 'viraljustifications.name as justification', 'viralsamples_view.datereceived', 'viralsamples_view.datetested', 'viralsamples_view.datedispatched', 'viralsamples_view.result')
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
    		$model = SampleView::select('samples_view.id','samples_view.patient', 'samples_view.batch_id', 'labs.labdesc', 'view_facilitys.county', 'view_facilitys.subcounty', 'view_facilitys.name as facility', 'view_facilitys.facilitycode', 'gender.gender_description', 'samples_view.dob', 'samples_view.age', 'ip.name as infantprophylaxis', 'samples_view.datecollected', 'pcrtype.alias as pcrtype', 'samples_view.spots', 'receivedstatus.name as receivedstatus', 'rejectedreasons.name as rejectedreason', 'mr.name as motherresult', 'mp.name as motherprophylaxis', 'feedings.feeding', 'entry_points.name as entrypoint', 'samples_view.datereceived', 'samples_view.datetested', 'samples_view.datedispatched', 'ir.name as infantresult')
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

        if ($request->category == 'county') {
            $model = $model->where('view_facilitys.county_id', '=', $request->county);
            $county = ViewFacility::where('county_id', '=', $request->county)->get()->first();
            $title .= $county->county;
        } else if ($request->category == 'subcounty') {
            $model = $model->where('view_facilitys.subcounty_id', '=', $request->district);
            $subc = ViewFacility::where('subcounty_id', '=', $request->district)->get()->first();
            $title .= $subc->subcounty;
        } else if ($request->category == 'facility') {
            $model = $model->where('view_facilitys.id', '=', $request->facility);
            $facility = ViewFacility::where('id', '=', $request->facility)->get()->first();
            $title .= $facility->name;
        }

    	if (isset($request->specificDate)) {
    		$dateString = date('d-M-Y', strtotime($request->specificDate));
    		$model = $model->where("$table.datereceived", '=', $request->specificDate);
    	}else {
            if (!isset($request->period) || $request->period == 'range') {
                $dateString = date('d-M-Y', strtotime($request->fromDate))." - ".date('d-M-Y', strtotime($request->toDate));
                if ($request->period) { $column = 'datetested'; } 
                else { $column = 'datereceived'; }
                $model = $model->whereRaw("$table.$column BETWEEN '".$request->fromDate."' AND '".$request->toDate."'");
            } else if ($request->period == 'monthly') {
                $dateString = date("F", mktime(null, null, null, $request->month)).' - '.$request->year;
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->year."' AND MONTH($table.datetested) = '".$request->month."'");
            } else if ($request->period == 'quarterly') {
                if ($request->quarter == 'Q1') {
                    $startQuarter = 1;
                    $endQuarter = 3;
                } else if ($request->quarter == 'Q2') {
                    $startQuarter = 4;
                    $endQuarter = 6;
                } else if ($request->quarter == 'Q3') {
                    $startQuarter = 7;
                    $endQuarter = 9;
                } else if ($request->quarter == 'Q4') {
                    $startQuarter = 10;
                    $endQuarter = 12;
                } else {
                    $startQuarter = 0;
                    $endQuarter = 0;
                }
                $dateString = $request->quarter.' - '.$request->year;
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->year."' AND MONTH($table.datetested) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
            } else if ($request->period == 'annually') {
                $dateString = $request->year;
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->year."'");
            }
    	}

        $report = (session('testingSystem') == 'Viralload') ? 'VL ' : 'EID ';

        if ($request->types == 'tested') {
            $model = $model->where("$table.receivedstatus", "<>", '2');
            $report .= 'tested outcomes ';
        } else {
            $model = $model->where("$table.receivedstatus", "=", '2');
            $report .= 'rejected outcomes ';
        }
        
        $dateString = strtoupper($report . $title . ' ' . $dateString);
        return $model->orderBy('datereceived', 'asc');
    }

    public static function __getExcel($data, $title)
    {
        $dataArray = []; 

        $dataArray[] = (session('testingSystem') == 'Viralload') ?
            ['Lab ID', 'Patient CCC No', 'Patient Names', 'Provider Identifier', 'Testing Lab', 'County', 'Sub County', 'Facility Name', 'MFL Code', 'AMRS location', 'Sex', 'Age', 'Sample Type', 'Collection Date', 'Received Status', 'Rejected Reason / Reason for Repeat', 'Current Regimen', 'ART Initiation Date', 'Justification',  'Date of Receiving', 'Date of Testing', 'Date of Dispatch', 'Viral Load'] :
            ['Lab ID', 'Sample Code', 'Batch No', 'Testing Lab', 'County', 'Sub County', 'Facility Name', 'MFL Code', 'Sex',    'DOB', 'Age(m)', 'Infant Prophylaxis', 'Date of Collection', 'PCR Type', 'Spots', 'Received Status', 'Rejected Reason / Reason for Repeat', 'HIV Status of Mother', 'PMTCT Intervention', 'Breast Feeding', 'Entry Point',  'Date of Receiving', 'Date of Testing', 'Date of Dispatch', 'Test Result'];
        
        if($data->isNotEmpty()) {
            foreach ($data as $report) {
                $dataArray[] = $report->toArray();
            }
            
            Excel::create($title, function($excel) use ($dataArray, $title) {
                $excel->setTitle($title);
                $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('WJ Gilmore, LLC');
                $excel->setDescription($title);

                $excel->sheet('Sheet1', function($sheet) use ($dataArray) {
                    $sheet->fromArray($dataArray, null, 'A1', false, false);
                });

            })->download('xlsx');
        } else {
            session(['toast_message' => 'No data available for the criteria provided']);
        }
    }

}
