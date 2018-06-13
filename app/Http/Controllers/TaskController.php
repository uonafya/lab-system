<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Taqmandeliveries;
use App\Taqmanprocurement;
use App\Abbotdeliveries;
use App\Abbotprocurement;
use App\LabEquipmentTracker;
use App\LabPerformanceTracker;
use App\Requisition;
use App\User;
use App\SampleView;
use App\ViralsampleView;

use DB;

class TaskController extends Controller
{
    public $taqmanKits = [
                            ['name'=>"Ampliprep, HIV-1 Qualitative Test kits HIVQCAP", 'alias'=>'qualkit', 'unit'=>'48 Tests'],
                            ['name'=>"Ampliprep Specimen Pre-Extraction Reagent", 'alias'=>'spexagent', 'unit'=>'350 Tests' ,'factor'=>0.15],
                            ['name'=>"Ampliprep Input S-tube", 'alias'=>'ampinput', 'unit'=>'12 * 24' ,'factor'=>0.2],
                            ['name'=>"Ampliprep SPU", 'alias'=>'ampflapless', 'unit'=>'12 * 24' ,'factor'=>0.2],
                            ['name'=>"Ampliprep K-Tips", 'alias'=>'ampktips', 'unit'=>'5.1L' ,'factor'=>0.15],
                            ['name'=>"Ampliprep Wash Reagent", 'alias'=>'ampwash', 'unit'=>'1.2mm, 12 * 36' ,'factor'=>0.5],
                            ['name'=>"TAQMAN K-Tubes", 'alias'=>'ktubes', 'unit'=>'12 * 96Pcs' ,'factor'=>0.05],
                            ['name'=>"CAP/CTM Consumable Bundles", 'alias'=>'consumables', 'unit'=>'2 * 2.5ml' ,'factor'=>0.5]
                        ];
    public $abbottKits = [
                            ['name'=>"ABBOTT RealTime HIV-1 Quantitative Amplification Reagent Kit", 'alias'=>'qualkit'],
                            ['name'=>"ABBOTT m2000rt Optical Calibration Kit", 'alias'=>'calibration','factor'=>['EID'=>0,'VL'=>0]],
                            ['name'=>"ABBOTT RealTime HIV-1 Quantitative Control Kit", 'alias'=>'control', 'factor'=>['EID'=>(2*(2/24)),'VL'=>(3/24)]],
                            ['name'=>"Bulk mLysisDNA Buffer (for DBS processing only)", 'alias'=>'buffer','factor'=>['EID'=>1,'VL'=>1]],
                            ['name'=>"ABBOTT mSample Preparation System RNA", 'alias'=>'Preparation','factor'=>['EID'=>1,'VL'=>1]],
                            ['name'=>"ABBOTT Optical Adhesive Covers", 'alias'=>'adhesive','factor'=>['EID'=>(2/100),'VL'=>(1/100)]],
                            ['name'=>"ABBOTT 96-Deep-Well Plate", 'alias'=>'deepplate','factor'=>['EID'=>(2*(2/4)),'VL'=>(3/4)]],
                            ['name'=>"Saarstet Master Mix Tube", 'alias'=>'mixtube','factor'=>['EID'=>(2*(1/25)),'VL'=>(1/25)]],
                            ['name'=>"Saarstet 5ml Reaction Vessels", 'alias'=>'reactionvessels','factor'=>['EID'=>(192/500),'VL'=>(192/500)]],
                            ['name'=>"200mL Reagent Vessels", 'alias'=>'reagent','factor'=>['EID'=>(2*(5/6)),'VL'=>(6/6)]],
                            ['name'=>"ABBOTT 96-Well Optical Reaction Plate", 'alias'=>'reactionplate','factor'=>['EID'=>(192/500),'VL'=>(1/20)]],
                            ['name'=>"1000 uL Eppendorf (Tecan) Disposable Tips (for 1000 tests)", 'alias'=>'1000disposable','factor'=>['EID'=>(2*(421/192)),'VL'=>(841/192)]],
                            ['name'=>"200 ML Eppendorf (Tecan) Disposable Tips", 'alias'=>'200disposable','factor'=>['EID'=>(2*(48/192)),'VL'=>(96/192)]]
                        ];

    public function index() 
    {
        $tasks = $this->pendingTasks();
        // dd($tasks);
        if ($tasks['submittedstatus'] > 0 && $tasks['labtracker'] > 0) {
            session(['pendingTasks'=> false]);
            return redirect()->route('home');
        }

    	$data['kits'] = (object)$this->getKitsEntered();
        
    	if (($data['kits']->eidtaqkits  > 0 && $data['kits']->vltaqkits > 0) && ($data['kits']->eidabkits  > 0 && $data['kits']->vlabkits > 0))
		{
            $data['submittedkits'] = 1;
            $data['consumption'] = (object)$this->getConsumption();
		}else {
			$data['submittedkits'] = 0;
		}
		
		$month = date('m')-1;
        $range = '';
        $quarter = parent::_getMonthQuarter(date('m'),$range);
        session(['range'=>$range, 'quarter'=>$quarter]);
        $data['equipment'] = LabEquipmentTracker::where('year', date('Y'))->where('month', $month)->count();
        $data['performance'] = LabPerformanceTracker::where('year', date('Y'))->where('month', $month)->count();
        $data['requisitions'] = count($this->getRequisitions());

        $data = (object) $data;
        // dd($data);
    	return view('tasks.home', compact('data'))->with('pageTitle', 'Pending Tasks');
    }

    public function addKitDeliveries(Request $request)
    {
        if ($request->saveTaqman) {
            $receivedby = $request->receivedby;
            $datereceived = $request->datereceived;
            $vreceivedby = $request->vreceivedby;
            $vdatereceived = $request->vdatereceived;
            $lab = auth()->user()->lab_id;
            $quarter = $request->quarter;
            $status = 1;
            $source = 3;
            $year = date('Y');
            $eidData = [
                        'testtype' => 1,'lab' => $lab,
                        'quarter' => $quarter,'year' => $year,'source' => $source,
                        'kitlotno' => $request->kitlotno,'expirydate' => $request->expirydate,
                        'qualkitreceived' => $request->rqualkit,'spexagentreceived' => $request->rspexagent,
                        'ampinputreceived' => $request->rampinput,'ampflaplessreceived' => $request->rampflapless,
                        'ampktipsreceived' => $request->rampktips,'ampwashreceived' => $request->rampwash,
                        'ktubesreceived' => $request->rktubes,'qualkitdamaged' => $request->dqualkit,
                        'spexagentdamaged' => $request->dspexagent,'ampinputdamaged' => $request->dampinput,
                        'ampflaplessdamaged' => $request->dampflapless,'ampktipsdamaged' => $request->dampktips,
                        'ampwashdamaged' => $request->dampwash,'ktubesdamaged' => $request->dktubes,
                        'receivedby' => $receivedby,'datereceived' => $datereceived,'status' => $status,
                        'enteredby' => auth()->user()->id,'dateentered' => date('Y-m-d')
                    ];
            $save = Taqmandeliveries::create($eidData);
            $vlData = [
                        'testtype' => 2,'lab' => $lab,
                        'quarter' => $quarter,'year' => $year,'source' => $source,
                        'kitlotno' => $request->vkitlotno,'expirydate' => $request->vexpirydate,
                        'qualkitreceived' => $request->vrqualkit,'spexagentreceived' => $request->vrspexagent,
                        'ampinputreceived' => $request->vrampinput,'ampflaplessreceived' => $request->vrampflapless,
                        'ampktipsreceived' => $request->vrampktips,'ampwashreceived' => $request->vrampwash,
                        'ktubesreceived' => $request->vrktubes,'qualkitdamaged' => $request->vdqualkit,
                        'spexagentdamaged' => $request->vdspexagent,'ampinputdamaged' => $request->vdampinput,
                        'ampflaplessdamaged' => $request->vdampflapless,'ampktipsdamaged' => $request->vdampktips,
                        'ampwashdamaged' => $request->vdampwash,'ktubesdamaged' => $request->vdktubes,
                        'receivedby' => $vreceivedby,'datereceived' => $vdatereceived,'status' => $status,
                        'enteredby' => auth()->user()->id,'dateentered' => date('Y-m-d')
                    ];
            $save = Taqmandeliveries::create($vlData);

            session(['toast_message'=>'The KIT delivery has EID/VL Taqman KITS SAVED SUCCESSFULLY.']);
        } else if ($request->saveAbbott) {
            // dd($request->all());
            $receivedby = $request->areceivedby;
            $datereceived = $request->adatereceived;
            $vreceivedby = $request->vareceivedby;
            $vdatereceived = $request->vadatereceived;
            $lab = auth()->user()->lab_id;
            $quarter = $request->quarter;
            $status = 1;
            $source = 3;
            $year = date('Y');

            $eidData = [
                "testtype" => 1,"lab" => $lab,
                "quarter" => $quarter,"year" => $year,"source" => $source,
                "qualkitlotno" => $request->vaqualkitlotno,"qualkitexpiry" => $request->aqualkitexpiry,
                "qualkitreceived" => $request->arqualkit,"qualkitdamaged" => $request->adqualkit,
                "controllotno" => $request->acontrollotno,"controlexpiry" => $request->acontrolexpiry,
                "controlreceived" => $request->arcontrol,"controldamaged" => $request->adcontrol,
                "bufferlotno" => $request->abufferlotno,"bufferexpiry" => $request->abufferexpiry,
                "bufferreceived" => $request->arbuffer,"bufferdamaged" => $request->adbuffer,
                "preparationlotno" => $request->apreparationlotno,"preparationexpiry" => $request->apreparationexpiry,
                "preparationreceived" => $request->arpreparation,"preparationdamaged" => $request->adpreparation,
                "adhesivereceived" => $request->aradhesive,"adhesivedamaged" => $request->adadhesive,
                "deepplatereceived" => $request->ardeepplate,"deepplatedamaged" => $request->addeepplate,
                "mixtubereceived" => $request->armixtube,"mixtubedamaged" => $request->admixtube,
                "reactionvesselsreceived" => $request->arreactionvessels,"reactionvesselsdamaged" => $request->adreactionvessels,
                "reagentreceived" => $request->arreagent,"reagentdamaged" => $request->adreagent,
                "reactionplatereceived" => $request->arreactionplate,"reactionplatedamaged" => $request->adreactionplate,
                "1000disposablereceived" => $request->ar1000disposable,"1000disposabledamaged" => $request->ad1000disposable,
                "200disposablereceived" => $request->ar200disposable,"200disposabledamaged" => $request->ad200disposable,
                "receivedby" => $receivedby,"datereceived" => $datereceived,"status" => $status,
                "enteredby" => auth()->user()->id,"dateentered" => date('Y-m-d')
            ];
            $save = Abbotdeliveries::create($eidData);

            $vlData = [
                "testtype" => 2,"lab" => $lab,
                "quarter" => $quarter,"year" => $year,"source" => $source,
                "qualkitlotno" => $request->vaqualkitlotno,"qualkitexpiry" => $request->vaqualkitexpiry,
                "qualkitreceived" => $request->varqualkit,"qualkitdamaged" => $request->vadqualkit,
                "controllotno" => $request->vacontrollotno,"controlexpiry" => $request->vacontrolexpiry,
                "controlreceived" => $request->varcontrol,"controldamaged" => $request->vadcontrol,
                "bufferlotno" => $request->vabufferlotno,"bufferexpiry" => $request->vabufferexpiry,
                "bufferreceived" => $request->varbuffer,"bufferdamaged" => $request->vadbuffer,
                "preparationlotno" => $request->vapreparationlotno,"preparationexpiry" => $request->vapreparationexpiry,
                "preparationreceived" => $request->varpreparation,"preparationdamaged" => $request->vadpreparation,
                "adhesivereceived" => $request->varadhesive,"adhesivedamaged" => $request->vadadhesive,
                "deepplatereceived" => $request->vardeepplate,"deepplatedamaged" => $request->vaddeepplate,
                "mixtubereceived" => $request->varmixtube,"mixtubedamaged" => $request->vadmixtube,
                "reactionvesselsreceived" => $request->varreactionvessels,
                "reactionvesselsdamaged" => $request->vadreactionvessels,
                "reagentreceived" => $request->varreagent,"reagentdamaged" => $request->vadreagent,
                "reactionplatereceived" => $request->varreactionplate,"reactionplatedamaged" => $request->vadreactionplate,
                "1000disposablereceived" => $request->var1000disposable,"1000disposabledamaged" => $request->vad1000disposable,
                "200disposablereceived" => $request->var200disposable,"200disposabledamaged" => $request->vad200disposable,
                "receivedby" => $receivedby,"datereceived" => $datereceived,"status" => $status,
                "enteredby" => auth()->user()->id,"dateentered" => date('Y-m-d')
            ];

            $save = Abbotdeliveries::create($vlData);
            session(['toast_message'=>'The KIT delivery has EID/VL ABBOTT KITS SAVED SUCCESSFULLY.']);
        }
        $taqdeliveries = Taqmandeliveries::where('quarter', parent::_getMonthQuarter(date('m')))->count();
        $abbottdeliveries = Abbotdeliveries::where('quarter', parent::_getMonthQuarter(date('m')))->count();

        if ($taqdeliveries > 0 && $abbottdeliveries > 0) {
            return redirect()->route('pending');
        }

        $users = User::where('user_type_id', '<', 5)->get();
        return view('tasks.kitsdeliveries', compact('users'))->with('pageTitle', 'Kit Deliveries');
    }

    public function consumption (Request $request, $guide=null)
    {
        if ($guide != null) {
            $filename = 'CONSUMPTION_GUIDE.pdf';
            $path = storage_path('app/downloads/' . $filename);

            return response()->download($path);
        }

        $data['testtypes'] = ['EID', 'VL'];
        $previousMonth = date('m')-1;
        $year = date('Y');

        if ($request->saveTaqman || $request->saveAbbott)
        {
            $insertData = [];
            $sub = ['ending','wasted','issued','request','pos'];
            if ($request->platform == 1 || $request->platform == '1') {
                $platform = 'taqman';
                foreach ($data['testtypes'] as $k => $v) {
                    $testtype = 2;
                    if ($v == 'EID') 
                        $testtype = 1;
                    $tests = $platform.$v.'tests';
                    $insertData[$v]['testtype'] = $testtype;
                    $insertData[$v]['tests'] = $request->$tests;
                    $insertData[$v]['month'] = $previousMonth;
                    $insertData[$v]['year'] = $year;
                    $insertData[$v]['datesubmitted'] = date('Y-m-d');
                    $insertData[$v]['submittedBy'] = Auth()->user()->id;
                    $insertData[$v]['lab_id'] = Auth()->user()->lab_id;
                    foreach ($sub as $key => $value) {
                        foreach ($this->taqmanKits as $keykit => $valuekit) {
                            $formValue = $platform.$v.$value.$valuekit['alias'];
                            $insertData[$v][$value.$valuekit['alias']] = $request->$formValue;
                        }
                    }
                    $comments = $platform.$v.'receivedcomment';
                    $issuedcomments = $platform.$v.'issuedcomment';
                    $insertData[$v]['comments'] = $request->$comments;
                    $insertData[$v]['issuedcomments'] = $request->$issuedcomments;
                }
                foreach ($insertData as $key => $value) {
                    $save = Taqmanprocurement::create($value);
                }
                $insertData = [];
            } else if ($request->platform == 2 || $request->platform == '2') {
                $platform = 'abbott';
                foreach ($data['testtypes'] as $k => $v) {
                    $testtype = 2;
                    if ($v == 'EID') 
                        $testtype = 1;
                    $tests = $platform.$v.'tests';
                    $insertData[$v]['testtype'] = $testtype;
                    $insertData[$v]['tests'] = $request->$tests;
                    $insertData[$v]['month'] = $previousMonth;
                    $insertData[$v]['year'] = $year;
                    $insertData[$v]['datesubmitted'] = date('Y-m-d');
                    $insertData[$v]['submittedBy'] = Auth()->user()->id;
                    $insertData[$v]['lab_id'] = Auth()->user()->lab_id;
                    foreach ($sub as $key => $value) {
                        foreach ($this->abbottKits as $keykit => $valuekit) {
                            $formValue = $platform.$v.$value.$valuekit['alias'];
                            $insertData[$v][$value.$valuekit['alias']] = $request->$formValue;
                        }
                    }
                    $comments = $platform.$v.'receivedcomment';
                    $issuedcomments = $platform.$v.'issuedcomment';
                    $insertData[$v]['comments'] = $request->$comments;
                    $insertData[$v]['issuedcomments'] = $request->$issuedcomments;
                }
                
                foreach ($insertData as $key => $value) {
                    $save = Abbotprocurement::create($value);
                }
                $insertData = [];
            }
        }

        $taqproc = Taqmanprocurement::where('month', $previousMonth)->where('year', $year)->count();
        $abbottproc = Abbotprocurement::where('month', $previousMonth)->where('year', $year)->count();

        if ($taqproc > 0 && $abbottproc > 0)
            return redirect()->route('pending');
        
        $data['taqmanKits'] = $this->taqmanKits;
        $data['abbottKits'] = $this->abbottKits;
        $data['EIDteststaq'] = SampleView::join('worksheets', 'worksheets.id', '=', 'samples_view.worksheet_id')->whereRaw("YEAR(datetested) = $year")->whereRaw("MONTH(datetested) = $previousMonth")->where('samples_view.lab_id', Auth()->user()->lab_id)->whereNull('rejectedreason')->whereIn('worksheets.machine_type',[1,3])->count();
        $data['EIDtestsabbott'] = SampleView::join('worksheets', 'worksheets.id', '=', 'samples_view.worksheet_id')->whereRaw("YEAR(datetested) = $year")->whereRaw("MONTH(datetested) = $previousMonth")->where('samples_view.lab_id', Auth()->user()->lab_id)->whereNull('rejectedreason')->where('worksheets.machine_type',2)->count();

        $data['VLteststaq'] = ViralsampleView::join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples_view.worksheet_id')->whereRaw("YEAR(datetested) = $year")->whereRaw("MONTH(datetested) = $previousMonth")->where('viralsamples_view.lab_id', Auth()->user()->lab_id)->whereNull('rejectedreason')->whereIn('viralworksheets.machine_type',[1,3])->count();
        $data['VLtestsabbott'] = ViralsampleView::join('viralworksheets', 'viralworksheets.id', '=', 'viralsamples_view.worksheet_id')->whereRaw("YEAR(datetested) = $year")->whereRaw("MONTH(datetested) = $previousMonth")->where('viralsamples_view.lab_id', Auth()->user()->lab_id)->whereNull('rejectedreason')->where('viralworksheets.machine_type',2)->count();
        
        foreach ($data['testtypes'] as $key => $value) {
            $data['prevabbott'.$value] = NULL;
            $data['prevtaqman'.$value] = NULL;
            $data['abbottdeliveries'.$value] = NULL;
            $data['taqmandeliveries'.$value] = NULL;
            $type = $key+1;
            foreach(Abbotprocurement::where('month', $previousMonth-1)->where('testtype', $type)->get() as $key1 => $value1) {
                $data['prevabbott'.$value] = $value;
            }
            foreach(Taqmanprocurement::where('month', $previousMonth-1)->where('testtype', $type)->get() as $key1 => $value1) {
                $data['prevtaqman'.$value] = $value1;
            }
            foreach (Taqmandeliveries::whereRaw("YEAR(datereceived) = $year")->whereRaw("MONTH(datereceived) = $previousMonth")->where('testtype', $type)->get() as $key1 => $value1) {
                $data['taqmandeliveries'.$value] = $value1;
            }
            foreach (Abbotdeliveries::whereRaw("YEAR(datereceived) = $year")->whereRaw("MONTH(datereceived) = $previousMonth")->where('testtype', $type)->get() as $key1 => $value1) {
                $data['abbottdeliveries'.$value] = $value1;
            }
        }
        

        $data = (object) $data;
        // dd($data);
        return view('tasks.consumption', compact('data'))->with('pageTitle', 'Lab Consumption::'.date("F", mktime(null, null, null, $previousMonth)).', '.date('Y'));
    }

    public function performancelog(Request $request)
    {
        if ($request->submit) {
            $lab = Auth()->user()->lab_id;
            $month = date('m')-1;
            $year = date('Y');
            $today = date('Y-m-d');
            $user = Auth()->user()->id;
            
            $eidData = [
                    'lab_id' => $lab,
                    'month' => $month,
                    'year' => $year,
                    'dateemailsent' => NULL,
                    'datesubmitted' => $today,
                    'submittedBy' => $user,
                    'testtype' => 1,
                    'sampletype' => 1,
                    'received' => $request->EIDreceived,
                    'rejected' => $request->EIDrejected,
                    'loggedin' => $request->EIDlogged,
                    'notlogged' => $request->EIDnotlogged,
                    'tested' => $request->EIDtested,
                    'reasonforbacklog' => $request->EIDreason
                ];
            $save = LabPerformanceTracker::create($eidData);

            $plasmaData = [
                    'lab_id' => $lab,
                    'month' => $month,
                    'year' => $year,
                    'dateemailsent' => NULL,
                    'datesubmitted' => $today,
                    'submittedBy' => $user,
                    'testtype' => 2,
                    'sampletype' => 1,
                    'received' => $request->Plasmareceived,
                    'rejected' => $request->Plasmarejected,
                    'loggedin' => $request->Plasmalogged,
                    'notlogged' => $request->Plasmanotlogged,
                    'tested' => $request->Plasmatested,
                    'reasonforbacklog' => $request->Plasmareason
                ];
            $save = LabPerformanceTracker::create($plasmaData);

            $dbsData = [
                    'lab_id' => $lab,
                    'month' => $month,
                    'year' => $year,
                    'dateemailsent' => NULL,
                    'datesubmitted' => $today,
                    'submittedBy' => $user,
                    'testtype' => 2,
                    'sampletype' => 2,
                    'received' => $request->DBSreceived,
                    'rejected' => $request->DBSrejected,
                    'loggedin' => $request->DBSlogged,
                    'notlogged' => $request->DBSnotlogged,
                    'tested' => $request->DBStested,
                    'reasonforbacklog' => $request->DBSreason
                ];
            $save = LabPerformanceTracker::create($dbsData);
            session(['toast_message'=>'Lab Activity Log Successfully Submitted.']);
            $performance = LabPerformanceTracker::where('month', $month)->where('year', $year)->where('lab_id', $lab)->count();

            if ($performance > 0) {
                return redirect()->route('pending');
            }
        }

        $data['sampletypes'] = (object)['EID', 'Plasma', 'DBS'];
        $data['logs'] = (object)self::__getLabperformanceLog($data['sampletypes']);
        $data = (object) $data;
        // dd($data);
        $month = date('m')-1;
        return view('tasks.performancelog', compact('data'))->with('pageTitle', 'Lab Performance Log::'.date("F", mktime(null, null, null, $month)).', '.date('Y'));
    }

    public function equipmentlog(Request $request) {
        $month = date('m')-1;
        if ($request->submit) {
            $tracker = [];
            foreach ($request->equipmentid as $key => $value) {
                $tracker[] = [
                        'month' => $month,
                        'year' => date('Y'),
                        'lab_id' => Auth()->user()->lab_id,
                        'equipment_id' => $value,
                        'datesubmitted' => date('Y-m-d'),
                        'submittedBy' => Auth()->user()->id,
                        'datebrokendown' => ($request->datebrokendown[$key] == "") ? null : $request->datebrokendown[$key],
                        'datereported' => ($request->datereported[$key] == "") ? null : $request->datereported[$key],
                        'datefixed' => ($request->datefixed[$key] == "") ? null : $request->datefixed[$key],
                        'downtime' => ($request->downtime[$key] == "") ? null : $request->downtime[$key],
                        'samplesnorun' => ($request->samplesnorun[$key] == "") ? null : $request->samplesnorun[$key],
                        'failedruns' => ($request->failedruns[$key] == "") ? null : $request->failedruns[$key],
                        'reagentswasted' => ($request->reagentswasted[$key] == "") ? null : $request->reagentswasted[$key],
                        'breakdownreason' => ($request->breakdownreason[$key] == "") ? null : $request->breakdownreason[$key],
                        'othercomments' => ($request->otherreasons == "") ? null : $request->otherreasons
                    ];
            }
            
            foreach ($tracker as $key => $value) {
                $save = LabEquipmentTracker::create($value);
            }
            session(['toast_message'=>'Lab Equipment Log Successfully Submitted.']);
            $equipment = LabEquipmentTracker::where('month', $month)->where('year', date('Y'))->where('lab_id', Auth()->user()->lab_id)->count();

            if ($equipment > 0)
                return redirect()->route('pending');
            
        }
        $data = DB::table('lab_equipment_mapping')->where('lab', '=', Auth()->user()->lab_id)->get();
        // dd($data);
        return view('tasks.equipmentlog', compact('data'))->with('pageTitle', 'Lab Equipment Log::'.date("F", mktime(null, null, null, $month)).', '.date('Y'));
    }

    public function getKitsEntered(){
    	$quarter = parent::_getMonthQuarter(date('m'));
    	$currentyear = date('Y');
    	return [
    		'eidtaqkits' => self::__getifKitsEntered(1,1,$quarter,$currentyear),
			'vltaqkits' => self::__getifKitsEntered(2,1,$quarter,$currentyear),
			'eidabkits' => self::__getifKitsEntered(1,2,$quarter,$currentyear),
			'vlabkits' => self::__getifKitsEntered(2,2,$quarter,$currentyear)
		];
	}

    public function getConsumption()
    {
        $previousMonth = date('m')-1;
        $currentyear = date('Y');
        return [
            'eidtaqconsumption' => self::__getifConsumptionEntered(1,1,$previousMonth,$currentyear),
            'vltaqconsumption' => self::__getifConsumptionEntered(2,1,$previousMonth,$currentyear),
            'eidabconsumption' => self::__getifConsumptionEntered(1,2,$previousMonth,$currentyear),
            'vlabconsumption' => self::__getifConsumptionEntered(2,2,$previousMonth,$currentyear)
        ];
    }

    public function getRequisitions()
    {
    	$currentmonth = date('m');
    	$currentyear = date('Y');

    	$model = Requisition::whereRaw("MONTH(dateapproved) <> $currentmonth")->where('status', 1)->where('flag', 1)
    						->whereRaw("YEAR(dateapproved) = $currentyear")->whereNull('datesubmitted')->get();
    	return $model;
    }

    public static function __getifKitsEntered($testtype,$platform,$quarter,$currentyear){

    	if ($platform==1)
            $model = Taqmandeliveries::where('testtype', $testtype)->where('flag', 1)->where('source', '<>', 2)->where('quarter', $quarter)->whereRaw("YEAR(dateentered) = $currentyear");

        if ($platform==2)
            $model = Abbotdeliveries::where('testtype', $testtype)->where('flag', 1)->where('source', '<>', 2)->where('quarter', $quarter)->whereRaw("YEAR(dateentered) = $currentyear");

        return $model->count();
    }

    public static function __getifConsumptionEntered($testtype,$platform,$month,$currentyear){

        if ($platform==1)
            $model = Taqmanprocurement::where('testtype', $testtype)->where('month', $month)->where('year', '=', $currentyear);

        if ($platform==2)
            $model = Abbotprocurement::where('testtype', $testtype)->where('month', $month)->where('year', '=', $currentyear);

        return $model->count();
    }

    public static function __getLabperformanceLog($data) {
        $return = [];
        $data = (array)$data;
        foreach ($data as $key => $value) {
            if ($value == 'EID') {
                $return[$value] = (object)self::__getLogs('EID');
            } else {
                if ($value == 'Plasma') {
                    $array = [1,2];
                } else if ($value == 'DBS'){
                    $array = [3,4];
                }
                $return[$value] = (object)self::__getLogs(null, $array);
            }
        }
        return $return;
    }

    public static function __getLogs($type=null, $sampletypes=null)
    {
        $types = ['received', 'rejected', 'tested', 'logged'];
        $year = date('Y');
        $month = date('m')-1;
        $result = [];

        foreach ($types as $key => $value) {
            if ($type == 'EID') {
                $model = SampleView::where('flag', 1);
            } else {
                $model = ViralsampleView::where('flag', 1)->whereBetween('sampletype', $sampletypes);
            }
                        
            if ($value == 'received') {
                $column = 'datereceived';
                $model = $model->whereRaw("((parentid=0)||(parentid IS NULL))");
            } else if ($value == 'rejected') {
                $column = 'datereceived';
                $model = $model->where('receivedstatus', '=', 2)->where('repeatt', '=', 0);
            } else if ($value == 'tested') {
                $column = 'datetested';
            } else if ($value == 'logged') {
                $column = 'created_at';
                $model = $model->whereRaw("((parentid=0)||(parentid IS NULL))");
            }
            $result[$value] = $model->whereRaw("YEAR($column) = $year")->whereRaw("MONTH($column) = $month")->count();
        }
        return $result;
    }
}
