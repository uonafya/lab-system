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
            $platform = $request->input('platform');
            if ($platform == 'abbott') 
                $model = Abbotdeliveries::select('*');
            if ($platform == 'taqman')
                $model = Taqmandeliveries::select('*');
            
            if($request->input('types') == 'eid') 
                $model->where('testtype', '=', 1);
            if($request->input('types') == 'viralload') 
                $model->where('testtype', '=', 2);
            
            if($request->input('source') == 'scms') 
                $model->where('source', '=', 1);
            if($request->input('source') == 'lab') 
                $model->where('source', '=', 2);
            if ($request->input('source') == 'kemsa') 
                $model->where('source', '=', 3);

            $year = $request->input('year');
            $model->whereRaw("YEAR(datereceived) = $year");
            if ($request->input('period') == 'monthly') {
                $month = $request->input('month');
                $model->whereRaw("MONTH(datereceived) = $month");
            } else if ($request->input('period') == 'quarterly') {
                $quarter = parent::_getQuarterMonths($request->input('quarter'));
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
                    if ($request->input('format') == 'excel') {
                        
                        return back();
                    }
                    $data['abbottdata'] = (object) $this->abbottKits;
                    $data = (object) $data;
                    return view('reports.abbottkits', compact('data'))->with('pageTitle', '');
                }
                if ($platform == 'taqman'){
                    if ($request->input('format') == 'excel') {
                        
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
        // dd($request->all());
        $data = [];
        $platform = $request->input('platform');
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


        if($request->input('types') == 'eid') {
            $model->where('testtype', '=', 1);
            $kits->where('testtype', '=', 1);
            $tests = Sample::selectRaw("count(*) as `tests`")->join("worksheets", "worksheets.id", "=", "samples.worksheet_id")
                        ->where('rejectedreason', '=', '0')
                        ->when($platform, function($query) use ($platform) {
                            if ($platform == 'abbott')
                                return $query->where("worksheets.machine_type", "=", 2);
                            if ($platform == 'taqman')
                                return $query->whereIn("worksheets.machine_type", [1,3]);
                        });
            $type = 'EID';
        }
        if($request->input('types') == 'viralload') {
            $model->where('testtype', '=', 2);
            $kits->where('testtype', '=', 2);
            $tests = Viralsample::selectRaw("count(*) as `tests`")->join("viralworksheets", "viralworksheets.id", "=", "viralsamples.worksheet_id")
                        ->where('rejectedreason', '=', '0')
                        ->when($platform, function($query) use ($platform) {
                            if ($platform == 'abbott')
                                return $query->where("viralworksheets.machine_type", "=", 2);
                            if ($platform == 'taqman')
                                return $query->whereIn("viralworksheets.machine_type", [1,3]);
                        });
            $type = 'VL';
        }
        $month = $request->input('month');
        $previousMonth = $month -1;
        
        $monthName = date('F', mktime(0, 0, 0, $month, 10));
        $year = $request->input('year');

        $model->where('year', $year);
        $model->whereRaw("(`month` = $month or `month` = $previousMonth)");
        $tests->whereYear('datetested', $year);
        $tests->whereMonth('datetested', $month);
        // $model->where('lab_id', env('APP_LAB'));

        $kits->whereYear('datereceived', $year);
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
                        'type' => $type,
                        'platform' => $platform,
                        'month' => $monthName,
                        'year' => $year
                    ];
        // $reports = $newdata;
        
        return view('reports.consumptionreport', compact('data', 'viewdata'))->with('pageTitle', 'Consumption Report');
    }

    public static function __getDateData($request, &$dateString)
    {
        ini_set("memory_limit", "-1");
#enteredby
        $title = '';
    	if (session('testingSystem') == 'Viralload') {
    		$table = 'viralsamples_view';
    		$model = ViralsampleView::select('viralsamples_view.id','viralsamples_view.batch_id','viralsamples_view.patient','viralsamples_view.patient_name','viralsamples_view.provider_identifier', 'labs.labdesc', 'view_facilitys.county', 'view_facilitys.subcounty', 'view_facilitys.name as facility', 'view_facilitys.facilitycode', 'viralsamples_view.amrs_location', 'gender.gender_description', 'viralsamples_view.dob', 'viralsampletype.name as sampletype', 'viralsamples_view.datecollected', 'receivedstatus.name as receivedstatus', 'viralrejectedreasons.name as rejectedreason', 'viralprophylaxis.name as regimen', 'viralsamples_view.initiation_date', 'viraljustifications.name as justification', 'viralsamples_view.datereceived', 'viralsamples_view.created_at', 'viralsamples_view.datetested', 'viralsamples_view.dateapproved', 'viralsamples_view.datedispatched', 'viralsamples_view.result', 'users.surname', 'users.surname')
                    ->leftJoin('users', 'users.id', '=', "$table.user_id")
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
    		$model = SampleView::select('samples_view.id','samples_view.batch_id','samples_view.patient', 'labs.labdesc', 'view_facilitys.county', 'view_facilitys.subcounty', 'view_facilitys.name as facility', 'view_facilitys.facilitycode', 'gender.gender_description', 'samples_view.dob', 'samples_view.age', 'ip.name as infantprophylaxis', 'samples_view.datecollected', 'pcrtype.alias as pcrtype', 'samples_view.spots', 'receivedstatus.name as receivedstatus', 'rejectedreasons.name as rejectedreason', 'mr.name as motherresult', 'mp.name as motherprophylaxis', 'feedings.feeding', 'entry_points.name as entrypoint', 'samples_view.datereceived', 'samples_view.created_at', 'samples_view.datetested', 'samples_view.dateapproved', 'samples_view.datedispatched', 'ir.name as infantresult', 'users.surname')
                    ->leftJoin('users', 'users.id', '=', "$table.user_id")
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

        if ($request->input('category') == 'county') {
            $model = $model->where('view_facilitys.county_id', '=', $request->input('county'));
            $county = ViewFacility::where('county_id', '=', $request->input('county'))->get()->first();
            $title .= $county->county;
        } else if ($request->input('category') == 'subcounty') {
            $model = $model->where('view_facilitys.subcounty_id', '=', $request->input('district'));
            $subc = ViewFacility::where('subcounty_id', '=', $request->input('district'))->get()->first();
            $title .= $subc->subcounty;
        } else if ($request->input('category') == 'facility') {
            $model = $model->where('view_facilitys.id', '=', $request->input('facility'));
            $facility = ViewFacility::where('id', '=', $request->input('facility'))->get()->first();
            $title .= $facility->name;
        }

    	if ($request->input('specificDate')) {
    		$dateString = date('d-M-Y', strtotime($request->input('specificDate')));
    		$model = $model->where("$table.datereceived", '=', $request->input('specificDate'));
    	}else {
            if (!$request->input('period') || $request->input('period') == 'range') {
                $dateString = date('d-M-Y', strtotime($request->input('fromDate')))." - ".date('d-M-Y', strtotime($request->input('toDate')));
                if ($request->input('period')) { $column = 'datetested'; } 
                else { $column = 'datereceived'; }
                $model = $model->whereRaw("$table.$column BETWEEN '".$request->input('fromDate')."' AND '".$request->input('toDate')."'");
            } else if ($request->input('period') == 'monthly') {
                $dateString = date("F", mktime(null, null, null, $request->input('month'))).' - '.$request->input('year');
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->input('year')."' AND MONTH($table.datetested) = '".$request->input('month')."'");
            } else if ($request->input('period') == 'quarterly') {
                if ($request->input('quarter') == 'Q1') {
                    $startQuarter = 1;
                    $endQuarter = 3;
                } else if ($request->input('quarter') == 'Q2') {
                    $startQuarter = 4;
                    $endQuarter = 6;
                } else if ($request->input('quarter') == 'Q3') {
                    $startQuarter = 7;
                    $endQuarter = 9;
                } else if ($request->input('quarter') == 'Q4') {
                    $startQuarter = 10;
                    $endQuarter = 12;
                } else {
                    $startQuarter = 0;
                    $endQuarter = 0;
                }
                $dateString = $request->input('quarter').' - '.$request->input('year');
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->input('year')."' AND MONTH($table.datetested) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
            } else if ($request->input('period') == 'annually') {
                $dateString = $request->input('year');
                $model = $model->whereRaw("YEAR($table.datetested) = '".$request->input('year')."'");
            }
    	}

        $report = (session('testingSystem') == 'Viralload') ? 'VL ' : 'EID ';

        if ($request->input('types') == 'tested') {
            $model = $model->where("$table.receivedstatus", "<>", '2');
            $report .= 'tested outcomes ';
        } else if ($request->input('types') == 'rejected') {
            $model = $model->where("$table.receivedstatus", "=", '2');
            $report .= 'rejected outcomes ';
        }
        
        $dateString = strtoupper($report . $title . ' ' . $dateString);

        return $model->orderBy('datereceived', 'asc')->where('repeatt', '=', 0);
    }

    public static function __getExcel($data, $title)
    {
        $dataArray = []; 

        $dataArray[] = (session('testingSystem') == 'Viralload') ?
            ['Lab ID', 'Batch #', 'Patient CCC No', 'Patient Names', 'Provider Identifier', 'Testing Lab', 'County', 'Sub County', 'Facility Name', 'MFL Code', 'AMRS location', 'Sex', 'Age', 'Sample Type', 'Collection Date', 'Received Status', 'Rejected Reason / Reason for Repeat', 'Current Regimen', 'ART Initiation Date', 'Justification',  'Date Received', 'Date Entered', 'Date of Testing', 'Date of Approval', 'Date of Dispatch', 'Viral Load', 'Entered By'] :
            ['Lab ID', 'Batch #', 'Sample Code', 'Testing Lab', 'County', 'Sub County', 'Facility Name', 'MFL Code', 'Sex',    'DOB', 'Age(m)', 'Infant Prophylaxis', 'Date of Collection', 'PCR Type', 'Spots', 'Received Status', 'Rejected Reason / Reason for Repeat', 'HIV Status of Mother', 'PMTCT Intervention', 'Breast Feeding', 'Entry Point',  'Date Received', 'Date Entered', 'Date of Testing', 'Date of Approval', 'Date of Dispatch', 'Test Result', 'Entered By'];
        
        ini_set("memory_limit", "-1");
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
