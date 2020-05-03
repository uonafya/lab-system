<?php

namespace App\Http\Controllers;

use App\Abbotdeliveries;
use App\Abbotprocurement;
use App\Allocation;
use App\CovidConsumption;
use App\LabEquipmentTracker;
use App\LabPerformanceTracker;
use App\Taqmandeliveries;
use App\Taqmanprocurement;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // public function __construct() {
    //     // parent::__construct();
    //     // dd(session('pendingTasks')); 
    //     if (session('pendingTasks'))
    //         return redirect('pending');
    // }

    public $taqmanKits = [
        ['EIDname'=>"Ampliprep, HIV-1 Qualitative Test kits HIVQCAP", 'VLname'=>"Ampliprep, HIV-1 Quantitative Test kits HIVQCAP", 'alias'=>'qualkit', 'unit'=>'48 Tests' ,'factor'=>1, 'testFactor' => ['EID'=>44,'VL'=>42]],
        ['name'=>"Ampliprep Specimen Pre-Extraction Reagent", 'alias'=>'spexagent', 'unit'=>'350 Tests' ,'factor'=>0.15, 'testFactor' => 0.15],
        ['name'=>"Ampliprep Input S-tube", 'alias'=>'ampinput', 'unit'=>'12 * 24' ,'factor'=>0.2, 'testFactor' => 0.2],
        ['name'=>"Ampliprep SPU", 'alias'=>'ampflapless', 'unit'=>'12 * 24' ,'factor'=>0.2, 'testFactor' => 0.2],
        ['name'=>"Ampliprep K-Tips", 'alias'=>'ampktips', 'unit'=>'5.1L' ,'factor'=>0.15, 'testFactor' => 0.15],
        ['name'=>"Ampliprep Wash Reagent", 'alias'=>'ampwash', 'unit'=>'1.2mm, 12 * 36' ,'factor'=>0.5, 'testFactor' => 0.5],
        ['name'=>"TAQMAN K-Tubes", 'alias'=>'ktubes', 'unit'=>'12 * 96Pcs' ,'factor'=>0.05, 'testFactor' => 0.05],
        ['name'=>"CAP/CTM Consumable Bundles", 'alias'=>'consumables', 'unit'=>'2 * 2.5ml' ,'factor'=>0.5, 'testFactor' => 0.5]
                        ];
    public $abbottKits = [
        ['EIDname'=>"ABBOTT RealTime HIV-1 Qualitative Amplification Reagent Kit", 'VLname'=>"ABBOTT RealTime HIV-1 Quantitative Amplification Reagent Kit", 'alias'=>'qualkit','factor'=>['EID'=>1,'VL'=>1], 'testFactor' => ['EID'=>94,'VL'=>93]],
        ['name'=>"ABBOTT m2000rt Optical Calibration Kit", 'alias'=>'calibration','factor'=>['EID'=>0,'VL'=>0], 'testFactor' => ['EID'=>0,'VL'=>0]],
        ['name'=>"ABBOTT RealTime HIV-1 Quantitative Control Kit", 'alias'=>'control', 'factor'=>['EID'=>(2*(2/24)),'VL'=>(3/24)], 'testFactor' => ['EID'=>(2*(2/24)),'VL'=>(3/24)]],
        ['name'=>"Bulk mLysisDNA Buffer (for DBS processing only)", 'alias'=>'buffer','factor'=>['EID'=>1,'VL'=>1], 'testFactor' => ['EID'=>1,'VL'=>1]],
        ['name'=>"ABBOTT mSample Preparation System RNA", 'alias'=>'preparation','factor'=>['EID'=>1,'VL'=>1], 'testFactor' => ['EID'=>1,'VL'=>1]],
        ['name'=>"ABBOTT Optical Adhesive Covers", 'alias'=>'adhesive','factor'=>['EID'=>(2/100),'VL'=>(1/100)], 'testFactor' => ['EID'=>(2/100),'VL'=>(1/100)]],
        ['name'=>"ABBOTT 96-Deep-Well Plate", 'alias'=>'deepplate','factor'=>['EID'=>(2*(2/4)),'VL'=>(3/4)], 'testFactor' => ['EID'=>(2*(2/4)),'VL'=>(3/4)]],
        ['name'=>"Saarstet Master Mix Tube", 'alias'=>'mixtube','factor'=>['EID'=>(2*(1/25)),'VL'=>(1/25)], 'testFactor' => ['EID'=>(2*(1/25)),'VL'=>(1/25)]],
        ['name'=>"Saarstet 5ml Reaction Vessels", 'alias'=>'reactionvessels','factor'=>['EID'=>(192/500),'VL'=>(192/500)], 'testFactor' => ['EID'=>(192/500),'VL'=>(192/500)]],
        ['name'=>"200mL Reagent Vessels", 'alias'=>'reagent','factor'=>['EID'=>(2*(5/6)),'VL'=>(6/6)], 'testFactor' => ['EID'=>(2*(5/6)),'VL'=>(6/6)]],
        ['name'=>"ABBOTT 96-Well Optical Reaction Plate", 'alias'=>'reactionplate','factor'=>['EID'=>(192/500),'VL'=>(1/20)], 'testFactor' => ['EID'=>(192/500),'VL'=>(1/20)]],
        ['name'=>"1000 uL Eppendorf (Tecan) Disposable Tips (for 1000 tests)", 'alias'=>'1000disposable','factor'=>['EID'=>(2*(421/192)),'VL'=>(841/192)], 'testFactor' => ['EID'=>(2*(421/192)),'VL'=>(841/192)]],
        ['name'=>"200 ML Eppendorf (Tecan) Disposable Tips", 'alias'=>'200disposable','factor'=>['EID'=>(2*(48/192)),'VL'=>(96/192)], 'testFactor' => ['EID'=>(2*(48/192)),'VL'=>(96/192)]]
                        ];

    public function _columnBuilder($columns = null)
    {
        $column = '<tr>';
        if ($columns == null) {
            $column .= '<th><center>No Data available</center></th>';
        } else {
            foreach ($columns as $key => $value) {
                $column .= '<th>'.$value.'</th>';
            }
        }
        $column .= '</tr>';
        return $column;
    }

    public function pendingTasks()
    {
        $currentmonth = date('m');
        $prevmonth = date('m')-1;
        $year = date('Y');
        $prevyear = $year;
        if ($currentmonth == 1) {
            $prevmonth = 12;
            $prevyear -= 1;
        }
        $equipment = LabEquipmentTracker::where('year', $prevyear)->where('month', $prevmonth)->count();
        $performance = LabPerformanceTracker::where('year', $prevyear)->where('month', $prevmonth)->count();

        $labtracker = 0;
        if ($performance > 0 &&  $equipment > 0) 
            $labtracker=1;

        $abbot = \App\Lab::select('abbott')->where('id', auth()->user()->lab_id)->first()->abbott;
        $testype = [1,2];
        $taqman = [];
        $abbottproc = [];
        $abbottoday = null;
        $taqmantoday = null;
        $today = false;

        foreach ($testype as $key => $value) {
            if ($abbot == 1) {//Check for both abbot and taqman
                $abbottmodel = Abbotprocurement::where('month', $prevmonth)->where('year', $prevyear)->where('lab_id', '=', env('APP_LAB'))->where('testtype', $value);
                $abbottproc[] = $abbottmodel->count();
                $abbottoday = $abbottmodel->where('datesubmitted', '=', date('Y-m-d'))->count();
            }
                     
            $taqmanmodel = Taqmanprocurement::where('month', $prevmonth)->where('year', $prevyear)->where('lab_id', '=', env('APP_LAB'))->where('testtype', $value);
            $taqman[] = $taqmanmodel->count();
            $taqmantoday = $taqmanmodel->where('datesubmitted', '=', date('Y-m-d'))->count();
        }
        // dd($abbottproc);
        if ($abbot == 1) {
            //..if both taqman and abbott have been submitted; set $submittedstatus > 0
            if ( ($taqman[0] > 0 && $taqman[1] >0 ) && ($abbottproc[0] > 0 && $abbottproc[1]>0) ){
                $submittedstatus = 1;
            } else {
                $submittedstatus = 0;
            }
            

            // //..if only taqman has been submitted and not abbott; set $submittedstatus = 0; and only show the abbott link 
            // if ( ($taqman[0] > 0 && $taqman[1] >0) && ($abbottproc[0] == 0 || $abbottproc[1]==0 ) )
            //     $submittedstatus = 0;
            

            // //..if only abbott has been submitted and not taqman; set $submittedstatus = 0; and only show the taqman link
            // if ( ($taqman[0] == 0 || $taqman[1] ==0) && ($abbottproc[0] > 0 || $abbottproc[1]>0) )
            //     $submittedstatus = 0;
            

            // //..if only abbott has been submitted and not taqman; set $submittedstatus = 0; and only show the taqman link 
            // if ( ($taqman[0] == 0 && $taqman[1] ==0) && ($abbottproc[0] > 0 || $abbottproc[1]>0) )
            //     $submittedstatus = 0;
            

            // //..if none has been submitted; set $submittedstatus = 0; and only show the main link that requests both platforms to be submitted ***but also check whether lab has abbott machine*****
            // if ( ($taqman[0] == 0 || $taqman[1] ==0 ) && ($abbottproc[0] == 0  || $abbottproc[1]==0 ) )
            //     $submittedstatus = 0;
            
        } else {
            // dd($taqman);
            $submittedstatus = 1;
            if ($taqman[0] == 0 || $taqman[1] == 0)
                $submittedstatus = 0;
        }

        if ($abbottoday > 0 || $taqmantoday > 0)
            $today = true;
        
        $time = $this->getPreviousWeek();
        $covidsubmittedstatus = 1;
        if (CovidConsumption::whereDate('start_of_week', $time->week_start)->get()->isEmpty()) {
            $covidsubmittedstatus = 0;
        }
        return ['submittedstatus'=>$submittedstatus,'labtracker'=>$labtracker, 'filledtoday' => $today, 'covidkits' => $covidsubmittedstatus];
    }

    protected function getPreviousWeek()
    {
        $date = strtotime('-7 days', strtotime(date('Y-m-d')));
        return $this->getStartAndEndDate(date('W', $date),
                                date('Y', $date));
    }

    protected function getStartAndEndDate($week, $year) {
        $dto = new \DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d');
        $ret['week'] = date('W', strtotime($ret['week_start']));
        return (object)$ret;
    }

    public function pilotAllocation()
    {
        $return = false;
        $currentAllocation = Allocation::where('year', '=', date('Y'))->where('month', date('m'))->get();
        if ($currentAllocation->isEmpty())
            $return = true;
        return $return;
    }

    public static function _getMonthQuarter($month=1, &$range=null){
        if ($month >0 && $month <4 ) {
            $quota=1;
            $range='JAN-MAR';
        }
        if ($month >3 && $month <7 ) {
            $quota=2;
            $range='APR-JUN';
        }
        if ($month >6 && $month <10 ) {
            $quota=3;
            $range='JUL-SEP';
        }
        if ($month >9 && $month <13) {
            $quota=4;
            $range='OCT-DEC';
        }
        return $quota;
    }

    public function auth_user($usertypes)
    {
        $user_type_id = auth()->user()->user_type_id;
        if($user_type_id == 0) return null;
        $a = 1;
        if(is_array($usertypes)){
            if(!in_array($user_type_id, $usertypes)) abort(403);
        }
        else{
            if($user_type_id != $usertypes) abort(403);
        }
    }

     public static function _getQuarterMonths($quarter=1) {
        $quarter = (int) $quarter;
        if ($quarter == 1) 
            $months = [1, 2, 3];
        if ($quarter == 2) 
            $months = [4, 5, 6];
        if ($quarter == 3) 
            $months = [7, 8, 9];
        if ($quarter == 4) 
            $months = [10, 11, 12];
        
        return $months;
    }

}
