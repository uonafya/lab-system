<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Taqmandeliveries;
use App\Abbotdeliveries;
use App\LabEquipmentTracker;
use App\LabPerformanceTracker;
use App\Requisition;

class TaskController extends Controller
{
    

    public function index()
    {
    	$data = $this->getKitsEntered();

    	if (($data['eidtaqkits']  > 0 && $data['vltaqkits'] > 0) && ($data['eidabkits']  > 0 && $data['vlabkits'] > 0))
		{

		}else {
			$data['submittedstatus'] = 5;
		}
		
		$month = date('m')-1;
        $data['equipment'] = LabEquipmentTracker::where('year', date('Y'))->where('month', $month)->count();
        $data['performance'] = LabPerformanceTracker::where('year', date('Y'))->where('month', $month)->count();
        $data['requisitions'] = count($this->getRequisitions());

        $data = (object) $data;
    	return view('tasks.home' compact($data))->with('pageTitle', 'Lab Dashboard');
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
			$model = Taqmandeliveries::where('testtype', $testtype)->where('flag', 1)->where('source', '<>', 2)->where('quarter', $quarter)->where('year', $currentyear)->count();

		if ($platform==2)
			$model = Abbotdeliveries::where('testtype', $testtype)->where('flag', 1)->where('source', '<>', 2)->where('quarter', $quarter)->where('year', $currentyear)->count();
			
		return $model;
    }
}
