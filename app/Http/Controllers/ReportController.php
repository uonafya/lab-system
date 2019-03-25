<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sample;
use App\SampleView;
use App\Viralsample;
use App\ViralsampleView;
use App\Cd4SampleView;
use App\Abbotdeliveries;
use App\Taqmandeliveries;
use App\Abbotprocurement;
use App\Taqmanprocurement;
use Excel;
use Mpdf\Mpdf;
use App\ViewFacility;
use App\Lab;
use App\Batch;
use App\Viralbatch;

class ReportController extends Controller
{
    public static $parent = ['ending','wasted','issued','request','pos'];
    public static $suffix = ['received','damaged'];
    public static $quarters = ['Q1' => '1,2,3', 'Q2' => '4,5,6', 'Q3' => '7,8,9', 'Q4' => '10,11,12'];

    public function index($testtype = null) {
        if($testtype == null && auth()->user()->user_type_id == 5)
            $testtype = 'EID';
        $testtype = strtoupper($testtype);
        return view('reports.reports', compact('testtype'))->with('pageTitle', 'Lab Reports');
    }

    public function cd4reports(){
        return view('reports.cd4reports')->with('pageTitle', 'CD4 Reports');
    }

    public function dateselect(Request $request)
    {
        $dateString = '';
        if (session('testingSystem') == 'CD4') {
            $data = self::__getCD4Data($request, $dateString)->get();
            $this->__getExcel($data, $dateString, $request);
        } else {
            $data = self::__getDateData($request, $dateString)->get();
            $this->__getExcel($data, $dateString, $request);
        }
        
    	return back();
    }
    
    public static function __getCD4Data($request, &$title){
        $tbl = "cd4_samples_view";
        $columns = "$tbl.serial_no, view_facilitys.name as facilty, amrslocations.name as amrs, view_facilitys.county, view_facilitys.subcounty, $tbl.medicalrecordno, $tbl.patient_name, $tbl.provider_identifier, gender.gender_description, $tbl.dob, $tbl.datecollected, receivedstatus.name as receivedstatus, cd4rejectedreasons.name as rejectedreason, $tbl.datereceived, date($tbl.created_at) as datecreated, users.surname, $tbl.datetested, $tbl.dateresultprinted, $tbl.AVGCD3percentLymph, $tbl.AVGCD3AbsCnt, $tbl.AVGCD3CD4percentLymph, $tbl.AVGCD3CD4AbsCnt, $tbl.CD45AbsCnt";
        $model = Cd4SampleView::selectRaw($columns)->where('repeatt', '=', 0)->where('parentid', '=', 0)
                    ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$tbl.facility_id")
                    ->leftJoin('amrslocations', 'amrslocations.id', '=', "$tbl.amrs_location")
                    ->leftJoin('gender', 'gender.id', '=', "$tbl.sex")
                    ->leftJoin('receivedstatus', 'receivedstatus.id', '=', "$tbl.receivedstatus")
                    ->leftJoin('cd4rejectedreasons', 'cd4rejectedreasons.id', '=', "$tbl.rejectedreason")
                    ->leftJoin('users', 'users.id', '=', "$tbl.user_id");
        if(null !== $request->input('category')) {
            $title = "cd4 test outcome report ";
            $model = self::setCD4CategoryFilters($request, $model, $title);
            $model = self::setCD4DateFilters($request, $model, $title);
            $model = self::setCD4ReportType($request, $model, $title);
        } else if (null !== $request->input('specificDate') || null !== $request->input('fromDate')) {
            $title = "cd4 samples log book ";
            $model = self::setCD4DateFilters($request, $model, $title);
        } else {
            // dd($request->all());
        }
        
        return $model;
    }

    public static function setCD4DateFilters($request, $model, &$title){
        if($request->input('specificDate')){
            $datereceived = $request->input('specificDate');
            $title .= "for $datereceived ";
            $model = $model->where('datereceived', '=', $datereceived);
        } else if (null !== $request->input('period')) {
            if ($request->input('period') == 'range') {
                $fromDate = date('Y-m-d', strtotime($request->input('fromDate')));
                $toDate = date('Y-m-d', strtotime($request->input('toDate')));
                $title .= "between $fromDate & $toDate ";
                $model = $model->whereBetween('datetested', [$fromDate,$toDate]);
            } else if ($request->input('period') == 'monthly') {
                $year = $request->input('year');
                $month = $request->input('month');
                $title .= "for $year - $month ";
                $model = $model->whereYear('datetested', $year)->whereMonth('datetested', $month);
            } else if ($request->input('period') == 'quarterly') {
                $year = $request->input('year');
                $quarter = $request->input('quarter');
                $title .= "for $year - $quarter ";
                $months = self::$quarters[$quarter];
                $model = $model->whereRaw("MONTH(datetested) in ($months)");
            } else if ($request->input('period') == 'annually') {
                $year = $request->input('year');
                $title .= "for $year";
                $model = $model->whereYear('datetested', $year);
            }
        } else if ($request->input('fromDate')){
            $fromDate = date('Y-m-d', strtotime($request->input('fromDate')));
            $toDate = date('Y-m-d', strtotime($request->input('toDate')));
            $title .= "between $fromDate & $toDate";
            $model = $model->whereBetween('datereceived', [$fromDate,$toDate]);
        }
        return $model;
    }

    public static function setCD4ReportType($request, $model, &$title){
        if ($request->input('types') == 'all') {

        } else if ($request->input('types') == 'less500') {
            $title .= " for less 500 ";
            $model = $model->where('AVGCD3CD4AbsCnt', '<', 500);
        } else if ($request->input('types') == 'above500') {
            $title .= " for above 500 ";
            $model = $model->where('AVGCD3CD4AbsCnt', '>', 500);
        }
        return $model;
    }

    public static function setCD4CategoryFilters($request, $model, &$title){
        if($request->input('category') == 'overall') {

        } else if ($request->input('category') == 'county') {
            $category = $request->input('county');
            $model = $model->where('view_facilitys.county_id', '=', $category);
            $set = ViewFacility::where('county_id', '=', $category)->first()->county;
            $title .= " for $set county ";
        } else if ($request->input('category') == 'subcounty') {
            $category = $request->input('district');
            $model = $model->where('view_facilitys.subcounty_id', '=', $category);
            $set = ViewFacility::where('subcounty_id', '=', $category)->first()->subcounty;
            $title .= " for $set sub-county ";
        } else if ($request->input('category') == 'facility') {
            $category = $request->input('facility');
            $model = $model->where('view_facilitys.id', '=', $category);
            $set = ViewFacility::where('id', '=', $category)->first()->name;
            $title .= " for $set ";
        }
        return $model;
    }// This is in the working tree

    protected function generate_samples_manifest($request, $data, $dateString) {
        ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "3000");
        $export['samples'] = $data;
        $export['testtype'] = $request->input('testtype');
        $export['lab'] = Lab::find(env('APP_LAB'));
        $export['period'] = strtoupper($dateString);
        $filename = strtoupper("HIV MANIFEST " . $dateString) . ".pdf";
        $mpdf = new Mpdf();
        $view_data = view('exports.mpdf_samples_manifest', $export)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    }


    public function generate(Request $request)
    {
        $dateString = '';
        if (session('testingSystem') == 'CD4') {
            $data = self::__getCD4Data($request, $dateString)->get();
            $this->__getExcel($data, $dateString, $request);
        } else if (auth()->user()->user_type_id == 5) {
            $data = self::__getDateData($request,$dateString)->get();
            // dd($data);
            ini_set("memory_limit", "-1");
            ini_set("max_execution_time", "3000");
            if ($request->input('types') == 'manifest'){
                $batches = $data->unique('batch_id')->pluck('batch_id');
                dd($batches);
                if ($request->input('testtype') == 'EID')
                    $model = Batch::class;
                else
                    $model = Viralbatch::class;
                $dbbatches = $model::whereIn('id', $batches)->whereNull('datedispatchedfromfacility')->get();
                foreach($dbbatches as $batch) {
                    $datedispatched = date('Y-m-d');
                    if (null !== $batch->datereceived && $batch->datereceived < $datedispatched)
                        $datedispatched = $batch->created_at;
                    $batch->datedispatchedfromfacility = $datedispatched;
                    $batch->pre_update();
                }
                $this->generate_samples_manifest($request, $data, $dateString);
            } else 
                $this->__getExcel($data, $dateString, $request);
        }else {
            if($request->input('types') == 'remoteentry' || $request->input('types') == 'sitessupported' || $request->input('types') == 'remoteentrydoing') {
                $data = self::__getSiteEntryData($request,$dateString)->get();
                $this->__getSiteEntryExcel($data, $dateString, $request);
            } else if ($request->input('types') == 'tat') {
                $data = $this->__getTATData($request, $dateString)->get();
                $this->__getTATExcel($data, $dateString);
            }else {
                $data = self::__getDateData($request,$dateString)->get();
                $this->__getExcel($data, $dateString, $request);
            }
        }
        return back();
    }

    public function __getTATData($request, &$dateString) {
        if (session('testingSystem') == 'Viralload'){
            $dateString = 'VL TAT ';
            $table = "viralsamples_view";
            $model = ViralsampleView::groupBy('facility');
        } else if (session('testingSystem') == 'EID'){
            $dateString = 'EID TAT ';
            $table = "samples_view";
            $model = SampleView::groupBy('facility');
        } else if (session('testingSystem') == 'CD4') {
            return back();
        }
        $model = $model->selectRaw("view_facilitys.facilitycode, view_facilitys.name as facility, ROUND(AVG(tat1), 2) as tat1, ROUND(AVG(tat2), 2) as tat2, ROUND(AVG(tat3), 2) as tat3, ROUND(AVG(tat4), 2) as tat4")->join("view_facilitys", "view_facilitys.id", "=", "$table.facility_id")->where('repeatt', '=', 0)->where('parentid', '=', 0)
                    ->where("$table.lab_id", '=', env('APP_LAB'))->whereNotNull('tat1')->whereNotNull('tat2')
                    ->whereNotNull('tat3')->whereNotNull('tat4')->orderBy('tat2', 'asc')
                    ->orderBy('tat1', 'asc')->orderBy('tat3', 'asc')->orderBy('tat4', 'asc');

        $model = self::__getBelongingTo($request, $model, $dateString);
        $model = self::__getDateRequested($request, $model, $table, $dateString, false);

        return $model;
    }

    public static function __getSiteEntryData($request, &$dateString) {
        if(session('testingSystem') == 'Viralload') {
            $dateString = 'VL';
            $table = "viralsamples_view";
            $model = ViralsampleView::orderBy('totalsamples', 'desc')->where('viralsamples_view.lab_id', '=', env('APP_LAB'));
        } else if(session('testingSystem') == 'EID') {
            $dateString = 'EID';
            $table = "samples_view";
            $model = SampleView::orderBy('totalsamples', 'desc')->where('samples_view.lab_id', '=', env('APP_LAB'));
        }

        $majoritySelect = "$table.facilitycode, view_facilitys.name as facility, facilitys.name as enteredby,view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner,count(*) as totalsamples";
        $minoritySelect = "view_facilitys.facilitycode AS `facilitycode`, Subcounty AS `subcounty`, view_facilitys.name AS `facility`, COUNT(DISTINCT {$table}.facility_id) AS `Facilities Supported`,  COUNT({$table}.id) AS `totalsamples` ";

        if($request->input('types') == 'remoteentry') {
            $sql = $majoritySelect;
            $dateString .= ' site entry ';
        } else if ($request->input('types') == 'sitessupported') {
            $sql = $majoritySelect;
            $dateString .= ' suported sites ';
        } else if ($request->input('types') == 'remoteentrydoing') {
            $sql = $minoritySelect;
            $dateString .= ' sites doing remote entry ';
        }
        
        $model = $model->selectRaw($sql)
                    ->when($request, function($query) use ($request, $table) {
                        if ($request->input('types') == 'remoteentrydoing')
                            return $query->join('users', 'users.id', '=', "{$table}.user_id")
                                        ->join('view_facilitys', 'view_facilitys.id', '=', "users.facility_id")
                                        ->groupBy(['subcounty', 'facilitycode', 'facility']);
                        else 
                            return $query->join("view_facilitys", "view_facilitys.id", "=", "$table.facility_id")
                                        ->leftJoin('users', 'users.id', '=', "$table.user_id")
                                        ->leftJoin('facilitys', 'facilitys.id', '=', 'users.facility_id')
                                        ->groupBy(['facilitycode', 'facility', 'county', 'subcounty', 'partner']);
                    })->when(true, function($query) use ($request, $table){
                        if($request->input('types') == 'remoteentry')
                            return $query->where("$table.site_entry", "=", 1);
                    })->where('repeatt', '=', 0)->where('parentid', '=', 0);
        
        $model = self::__getBelongingTo($request, $model, $dateString);
        $model = self::__getDateRequested($request, $model, $table, $dateString);
        
        return $model;
    }

    public static function __getBelongingTo($request, $model, &$dateString) {
        $title = 'for ';
        if ($request->input('category') == 'county') {
            $model = $model->where('view_facilitys.county_id', '=', $request->input('county'));
            $county = ViewFacility::where('county_id', '=', $request->input('county'))->get()->first();
            $title .= $county->county.'county ';
        } else if ($request->input('category') == 'subcounty') {
            $model = $model->where('view_facilitys.subcounty_id', '=', $request->input('district'));
            $subc = ViewFacility::where('subcounty_id', '=', $request->input('district'))->get()->first();
            $title .= $subc->subcounty.' ';
        } else if ($request->input('category') == 'facility') {
            $model = $model->where('view_facilitys.id', '=', $request->input('facility'));
            $facility = ViewFacility::where('id', '=', $request->input('facility'))->get()->first();
            $title .= $facility->name.' ';
        } else if ($request->input('category') == 'partner') {
            $model = $model->where('view_facilitys.partner_id', '=', $request->input('partner'));
            $partner = ViewFacility::where('partner_id', '=', $request->input('partner'))->get()->first();
            $title .= $partner->name.' ';
        }
        if ($request->input('types') == 'manifest') {
            $facility = ViewFacility::find(auth()->user()->facility_id);
            $title .= $facility->name;
        }
        $dateString .= $title . ' ';
        return $model;
    }

    public static function __getDateRequested($request, $model, $table, &$dateString, $receivedOnly=true) {
        if($request->input('types') == 'manifest') { $column = 'created_at'; } 
        else if ($receivedOnly) { $column = 'datereceived'; } 
        else { $column = 'datetested'; }

        if (!$request->input('period') || $request->input('period') == 'range') {
            $dateString .= date('d-M-Y', strtotime($request->input('fromDate')))." - ".date('d-M-Y', strtotime($request->input('toDate')));
            $model = $model->when(true, function($query) use ($request, $table, $column) {
                                if ($request->input('fromDate') == $request->input('toDate'))
                                    return $query->whereRaw("date($table.$column) = '" . $request->input('fromDate') . "'");
                                else
                                    return $query->whereRaw("date($table.$column) BETWEEN '".$request->input('fromDate')."' AND '".$request->input('toDate')."'");
                            });
        } else if ($request->input('period') == 'monthly') {
            $dateString .= date("F", mktime(null, null, null, $request->input('month'))).' - '.$request->input('year');
            $model = $model->whereRaw("YEAR($table.$column) = '".$request->input('year')."' AND MONTH($table.$column) = '".$request->input('month')."'");
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
            $dateString .= $request->input('quarter').' - '.$request->input('year');
            $model = $model->whereRaw("YEAR($table.$column) = '".$request->input('year')."' AND MONTH($table.$column) BETWEEN '".$startQuarter."' AND '".$endQuarter."'");
        } else if ($request->input('period') == 'annually') {
            $dateString .= $request->input('year');
            $model = $model->whereRaw("YEAR($table.$column) = '".$request->input('year')."'");
        }
        
        return $model;
    }

    public function consumption(Request $request)
    {
        // dd($request->all());
        $data = [];
        $platform = $request->input('platform');
        if ($platform == 'abbott') {
            $model = Abbotprocurement::select('*')->where('lab_id', '=', env('APP_LAB'));
            $kits = Abbotdeliveries::select('*')->where('lab_id', '=', env('APP_LAB'));
            $sub = $this->abbottKits;
        }
        if ($platform == 'taqman') {
            $model = Taqmanprocurement::select('*')->where('lab_id', '=', env('APP_LAB'));
            $kits = Taqmandeliveries::select('*')->where('lab_id', '=', env('APP_LAB'));
            $sub = $this->taqmanKits;
        }


        if($request->input('types') == 'eid') {
            $model->where('testtype', '=', 1);
            $kits->where('testtype', '=', 1);
            $tests = Sample::selectRaw("count(*) as `tests`")->join("worksheets", "worksheets.id", "=", "samples.worksheet_id")->where('worksheets.lab_id', '=', env('APP_LAB'))
                        ->where('receivedstatus', '=', '1')
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
            $tests = Viralsample::selectRaw("count(*) as `tests`")->join("viralworksheets", "viralworksheets.id", "=", "viralsamples.worksheet_id")->where('viralworksheets.lab_id', '=', env('APP_LAB'))
                        ->where('receivedstatus', '=', '1')
                        ->when($platform, function($query) use ($platform) {
                            if ($platform == 'abbott')
                                return $query->where("viralworksheets.machine_type", "=", 2);
                            if ($platform == 'taqman')
                                return $query->whereIn("viralworksheets.machine_type", [1,3]);
                        });
            $type = 'VL';
        }
        $month = $request->input('month');
        $previousMonth = $month - 1;
        if ($month == 1)
            $previousMonth = 12;
        
        $monthName = date('F', mktime(0, 0, 0, $month, 10));
        $year = $request->input('year');
        $previousYear = $year;
        if ($previousMonth == 12)
            $previousYear = $year - 1;

        $model->whereRaw("(`year` = $year or `year` = $previousYear)");
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
        
        return view('reports.consumptionreport', compact('data', 'viewdata'))->with('pageTitle', 'Consumption Report');
    }

    public static function __getDateData($request, &$dateString)
    {
        ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "3000");
        
        $testtype = session('testingSystem');
        if (auth()->user()->user_type_id == 5) {
            if ($request->input('testtype') == 'VL')
                $testtype = 'Viralload';
            else if ($request->input('testtype') == 'EID')
                $testtype = 'EID';
        }

        $title = '';
    	if ($testtype == 'Viralload') {
            $table = 'viralsamples_view';
            $columns = "$table.id,$table.batch_id,$table.patient,$table.patient_name,$table.provider_identifier, labs.labdesc, view_facilitys.partner, view_facilitys.county, view_facilitys.subcounty, view_facilitys.name as facility, view_facilitys.facilitycode, order_no as order_number, amrslocations.name as amrs_location, gender.gender_description, $table.dob, $table.age, viralpmtcttype.name as pmtct, viralsampletype.name as sampletype, $table.datecollected,";
            
            if ($request->input('types') == 'manifest')
                $columns .= "$table.datedispatchedfromfacility,";
            $columns .= "receivedstatus.name as receivedstatus, viralrejectedreasons.name as rejectedreason, viralprophylaxis.name as regimen, $table.initiation_date, viraljustifications.name as justification, $table.datereceived, $table.created_at, $table.datetested, $table.dateapproved, $table.datedispatched, $table.result,  $table.entered_by";
            // $columns .= "receivedstatus.name as receivedstatus, viralrejectedreasons.name as rejectedreason, viralprophylaxis.name as regimen, $table.initiation_date, viraljustifications.name as justification, $table.datereceived, $table.created_at, $table.datetested, $table.dateapproved, $table.datedispatched, $table.result,  $table.entered_by, rec.surname as receiver";
            $model = ViralsampleView::selectRaw($columns)->where("$table.lab_id", '=', env('APP_LAB'))
                    ->leftJoin('users', 'users.id', '=', "$table.user_id")
                    // ->leftJoin('users as rec', 'rec.id', '=', "$table.received_by")
    				->leftJoin('labs', 'labs.id', '=', "$table.lab_id")
    				->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralsamples_view.facility_id')
                    ->leftJoin('amrslocations', 'amrslocations.id', '=', 'viralsamples_view.amrs_location')
    				->leftJoin('gender', 'gender.id', '=', 'viralsamples_view.sex')
    				->leftJoin('viralsampletype', 'viralsampletype.id', '=', 'viralsamples_view.sampletype')
    				->leftJoin('receivedstatus', 'receivedstatus.id', '=', 'viralsamples_view.receivedstatus')
    				->leftJoin('viralrejectedreasons', 'viralrejectedreasons.id', '=', 'viralsamples_view.rejectedreason')
    				->leftJoin('viralprophylaxis', 'viralprophylaxis.id', '=', 'viralsamples_view.prophylaxis')
    				->leftJoin('viraljustifications', 'viraljustifications.id', '=', 'viralsamples_view.justification')
                    ->leftJoin('viralpmtcttype', 'viralpmtcttype.id', '=', 'viralsamples_view.pmtct');
    	} else if ($testtype == 'EID') {
            $table = 'samples_view';
            $columns = "samples_view.id,samples_view.batch_id,samples_view.patient, samples_view.patient_name, labs.labdesc, view_facilitys.partner, view_facilitys.county, view_facilitys.subcounty, view_facilitys.name as facility, view_facilitys.facilitycode, order_no as order_number,";
            if($request->input('types') == 'manifest')
                $columns .= " $table.datedispatchedfromfacility,";
            $columns .= " gender.gender_description, samples_view.dob, samples_view.age, ip.name as infantprophylaxis, samples_view.datecollected, pcrtype.alias as pcrtype, samples_view.spots, receivedstatus.name as receivedstatus, rejectedreasons.name as rejectedreason, mr.name as motherresult, mp.name as motherprophylaxis, feedings.feeding, entry_points.name as entrypoint, samples_view.datereceived,samples_view.created_at, samples_view.datetested, samples_view.dateapproved, samples_view.datedispatched, ir.name as infantresult,  $table.entered_by";
            // $columns .= " gender.gender_description, samples_view.dob, samples_view.age, ip.name as infantprophylaxis, samples_view.datecollected, pcrtype.alias as pcrtype, samples_view.spots, receivedstatus.name as receivedstatus, rejectedreasons.name as rejectedreason, mr.name as motherresult, mp.name as motherprophylaxis, feedings.feeding, entry_points.name as entrypoint, samples_view.datereceived,samples_view.created_at, samples_view.datetested, samples_view.dateapproved, samples_view.datedispatched, ir.name as infantresult,  $table.entered_by, rec.surname as receiver";
    		$model = SampleView::selectRaw($columns)->where("$table.lab_id", '=', env('APP_LAB'))
                    ->leftJoin('users', 'users.id', '=', "$table.user_id")
                    // ->leftJoin('users as rec', 'rec.id', '=', "$table.received_by")
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

        $model = self::__getBelongingTo($request, $model, $dateString);

    	if ($request->input('specificDate')) {
    		$dateString = date('d-M-Y', strtotime($request->input('specificDate')));
    		$model = $model->where("$table.datereceived", '=', $request->input('specificDate'));
    	}else {
            $receivedOnly=false;
            if ($request->input('types') == 'rejected' || $request->input('samples_log') == 1 || $request->input('types') == 'manifest')
                $receivedOnly=true;
            
            $model = self::__getDateRequested($request, $model, $table, $dateString, $receivedOnly);
    	}

        $report = ($testtype == 'Viralload') ? 'VL ' : 'EID ';

        if ($request->input('types') == 'tested') {
            $model = $model->where("$table.receivedstatus", "<>", '2');
            $report .= 'tested outcomes ';
        } else if ($request->input('types') == 'rejected') {
            $model = $model->where("$table.receivedstatus", "=", '2');
            $report .= 'rejected outcomes ';
        } else if ($request->input('types') == 'positives') {
            $model = $model->where("$table.result", "=", 2);
            $report .= 'positive outcomes';
        } else if ($request->input('types') == 'poc') {
            $model = $model->where("$table.site_entry", '=', 2);
            $report .= 'poc tests';
        } else if ($request->input('types') == 'manifest') {
            $report .= 'sample manifest ';
        } else {
            $report .= 'samples log ';    
        }

        if(auth()->user()->user_type_id == 5) {
            if ($request->input('types') == 'manifest')
                $model = $model->where('site_entry', '=', 1)->whereRaw("(($table.user_id = " . auth()->user()->id . ") or ($table.facility_id = " . auth()->user()->facility_id . "))")->orderBy('created_at', 'asc');
            else
                $model = $model->where("$table.facility_id", '=', auth()->user()->facility_id);
        }
        
        $dateString = strtoupper($report . $title . ' ' . $dateString);
        
        return $model->orderBy('datereceived', 'asc')->where('repeatt', '=', 0);
    }

    public static function __getExcel($data, $title, $request = null)
    {
        $title = strtoupper($title);
        $dataArray = []; 
        $vlDataArray = ['Lab ID', 'Batch #', 'Patient CCC No', 'Patient Names', 'Provider Identifier', 'Testing Lab', 'Partner', 'County', 'Sub County', 'Facility Name', 'MFL Code', 'Order Number', 'AMRS location', 'Sex', 'DOB', 'Age', 'PMTCT', 'Sample Type', 'Collection Date', 'Received Status', 'Rejected Reason / Reason for Repeat', 'Current Regimen', 'ART Initiation Date', 'Justification',  'Date Received', 'Date Entered', 'Date of Testing', 'Date of Approval', 'Date of Dispatch', 'Viral Load', 'Entered By', 'Received By'];
        $eidDataArray = ['Lab ID', 'Batch #', 'Sample Code', 'Infant Name','Testing Lab', 'Partner', 'County', 'Sub County', 'Facility Name', 'MFL Code', 'Order Number', 'Sex',    'DOB', 'Age(m)', 'Infant Prophylaxis', 'Date of Collection', 'PCR Type', 'Spots', 'Received Status', 'Rejected Reason / Reason for Repeat', 'HIV Status of Mother', 'PMTCT Intervention', 'Breast Feeding', 'Entry Point',  'Date Received', 'Date Entered', 'Date of Testing', 'Date of Approval', 'Date of Dispatch', 'Test Result', 'Entered By', 'Received By'];
        $cd4DataArray = ['Lab Serial #', 'Facility', 'AMR Location', 'County', 'Sub-County', 'Ampath #', 'Patient Names', 'Provider ID', 'Sex', 'DOB', 'Date Collected/Drawn', 'Received Status', 'Rejected Reason( if Rejected)', 'Date Received', 'Date Registered', 'Registered By', 'Date Tested', 'Date Result Printed', 'CD3 %', 'CD3 abs', 'CD4 %', 'CD4 abs', 'Total Lymphocytes'];
        // $VLfacilityManifestArray = ['Lab ID', 'Patient CCC #', 'Batch #', 'County', 'Sub-County', 'Facility Name', 'Facility Code', 'Gender', 'DOB', 'Sample Type', 'Justification', 'Date Collected', 'Date Tested'];
        // $EIDfacilityManifestArray = ['Lab ID', 'HEI # / Patient CCC #', 'Batch #', 'County', 'Sub-County', 'Facility Name', 'Facility Code', 'Gender', 'DOB',  'PCR Type','Spots', 'Date Collected', 'Date Tested'];
        if (auth()->user()->user_type_id == 5) {
            $newArray = [];
            if ($request->input('types') == 'manifest') {
                // $dataArray[] = ($request->input('testtype') == 'VL') ? $VLfacilityManifestArray : $EIDfacilityManifestArray;

                // foreach ($data as $key => $new) {
                //     $newArray[] = [
                //         'lab_id' => $new->id, 'patient' => $new->patient, 'batch' => $new->batch_id,
                //         'county' => $new->county, 'subcounty' => $new->subcounty, 'facility' => $new->facility,
                //         'mfl' => $new->facilitycode, 'gender' => $new->gender_description,  'dob' => $new->dob,
                //         'types' => ($request->input('testtype') == 'VL') ? $new->sampletype : $new->pcrtype,
                //         'jus-spots' => ($request->input('testtype') == 'VL') ? $new->justification : $new->spots,
                //         'datecollected' => $new->datecollected, 'datetested' => $new->datetested
                //     ];
                // }
                // $data = collect($newArray);
            } else {
                if ($request->input('testtype') == 'VL')
                    $dataArray[] = $vlDataArray;
                else if ($request->input('testtype') == 'EID')
                    $dataArray[] = $eidDataArray;
            }
        } else {
            if (session('testingSystem') == 'Viralload')
                $dataArray[] = $vlDataArray;
            else if (session('testingSystem') == 'EID')
                $dataArray[] = $eidDataArray;
            else if (session('testingSystem') == 'CD4')
                $dataArray[] = $cd4DataArray;
        }
                
        ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "3000");
        
        if($data->isNotEmpty()) {
            foreach ($data as $report) {
                // if ($request->input('types') == 'manifest')
                //     $dataArray[] = $report;
                // else
                    $dataArray[] = $report->toArray();
            }
            
            Excel::create($title, function($excel) use ($dataArray, $title) {
                $excel->setTitle($title);
                $excel->setCreator(Auth()->user()->surname.' '.Auth()->user()->oname)->setCompany('EID/VL System');
                $excel->setDescription($title);

                $excel->sheet('Sheet1', function($sheet) use ($dataArray) {
                    $sheet->fromArray($dataArray, null, 'A1', false, false);
                });

            })->download('csv');
        } else {
            session(['toast_message' => 'No data available for the criteria provided']);
        }
    }

    public function __getSiteEntryExcel($data, $title, $request)
    {
        $title = strtoupper($title);
        if ($request->input('types') == 'remoteentrydoing')
            $dataArray[] = ['MFL Code', 'Sub-county', 'Facility', 'Facilities Supported', 'Samples Entered'];
        else
            $dataArray[] = ['MFL Code', 'Facility Name', 'Site Entered', 'County', 'Sub-County', 'Partner', 'Total Samples'];
        $this->generate_excel($data, $dataArray, $title);
    }

    public function __getTATExcel($data, $title) {
        $title = strtoupper($title);
        $dataArray[] = ['MFL Code', 'Facility Name', 'TAT1', 'TAT2', 'TAT3', 'TAT4'];
        $this->generate_excel($data, $dataArray, $title);
    }

    public function generate_excel($data, $dataArray, $title){
        ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "3000");
        
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

            })->download('csv');
        } else {
            session(['toast_message' => 'No data available for the criteria provided']);
        }
    }

}
