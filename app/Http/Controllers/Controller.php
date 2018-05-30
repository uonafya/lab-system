<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\LabEquipmentTracker;
use App\LabPerformanceTracker;
use App\Taqmanprocurement;
use App\Abbotprocurement;
use App\Taqmandeliveries;
use App\Abbotdeliveries;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
        $month = date('m');
        $prevmonth = $month-1;
        $equipment = LabEquipmentTracker::where('year', date('Y'))->where('month', $prevmonth)->count();
        $performance = LabPerformanceTracker::where('year', date('Y'))->where('month', $prevmonth)->count();

        $labtracker = 0;
        if ($performance > 0 &&  $equipment > 0) 
            $labtracker=1;

        $abbot = \App\Lab::select('abbott')->where('id', Auth()->user()->lab_id)->get();

        $testype = [1,2];
        $taqman = [];
        $taqmandels = [];
        $abbot = [];
        $abbotdels = [];
        
        foreach ($testype as $key => $value) {
            if ($abbot == 1) {//Check for both abbot and taqman
                $abbot[] = Abbotprocurement::where('month', $prevmonth)->where('year', date('Y'))->where('lab_id', Auth()->user()->lab_id)->where('testtype', $value)->count();
                $abbotdels[] = Abbotdeliveries::where('quarter', self::_getMonthQuarter($month))->where('year', date('Y'))->where('lab', Auth()->user()->lab_id)->where('testtype', $value)->count();
            }
                               
            $taqman[] = Taqmanprocurement::where('month', $prevmonth)->where('year', date('Y'))->where('lab_id', Auth()->user()->lab_id)->where('testtype', $value)->count();
            $taqmandels[] = Taqmandeliveries::where('quarter', self::_getMonthQuarter($month))->where('year', date('Y'))->where('lab', Auth()->user()->lab_id)->where('testtype', $value)->count();
            
        }

        if ($abbot == 1) {
            //..if both taqman and abbott have been submitted; set $submittedstatus > 0
            if ( ($taqman[0] > 0 && $taqman[1] >0 ) && ($abbot[0] > 0 && $abbot[1]>0) )
                $submittedstatus = 1;
            if ( ($taqmandels[0] > 0 && $taqmandels[1] >0 ) && ($abbotdels[0] > 0 && $abbotdels[1]>0) )
                $deliverystatus = 1;

            //..if only taqman has been submitted and not abbott; set $submittedstatus = 0; and only show the abbott link 
            if ( ($taqman[0] > 0 && $taqman[1] >0) && ($abbot[0] == 0 || $abbot[1]==0 ) )
                $submittedstatus = 0;
            if ( ($taqmandels[0] > 0 && $taqmandels[1] >0) && ($abbotdels[0] == 0 || $abbotdels[1]==0 ) )
                $deliverystatus = 0;

            //..if only abbott has been submitted and not taqman; set $submittedstatus = 0; and only show the taqman link
            if ( ($taqman[0] == 0 || $taqman[1] ==0) && ($abbot[0] > 0 || $abbot[1]>0) )
                $submittedstatus = 0;
            if ( ($taqmandels[0] == 0 || $taqmandels[1] ==0) && ($abbotdels[0] > 0 || $abbotdels[1]>0) )
                $deliverystatus = 0;

            //..if only abbott has been submitted and not taqman; set $submittedstatus = 0; and only show the taqman link 
            if ( ($taqman[0] == 0 && $taqman[1] ==0) && ($abbot[0] > 0 || $abbot[1]>0) )
                $submittedstatus = 0;
            if ( ($taqmandels[0] == 0 && $taqmandels[1] ==0) && ($abbotdels[0] > 0 || $abbotdels[1]>0) )
                $deliverystatus = 0;

            //..if none has been submitted; set $submittedstatus = 0; and only show the main link that requests both platforms to be submitted ***but also check whether lab has abbott machine*****
            if ( ($taqman[0] == 0 || $taqman[1] ==0 ) && ($abbot[0] == 0  || $abbot[1]==0 ) )
                $submittedstatus = 0;
            if ( ($taqmandels[0] == 0 || $taqmandels[1] ==0 ) && ($abbotdels[0] == 0  || $abbotdels[1]==0 ) )
                $deliverystatus = 0;
        } else {
            $submittedstatus = 1;
            $deliverystatus = 1;
            if ($taqman[0] == 0 || $taqman[1] ==0)
                $submittedstatus = 0;
            if ($taqmandels[0] == 0 || $taqmandels[1] ==0)
                $deliverystatus = 0;
        }

        return ['submittedstatus'=>$submittedstatus,'labtracker'=>$labtracker,'deliverystatus'=>$deliverystatus];
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

}
