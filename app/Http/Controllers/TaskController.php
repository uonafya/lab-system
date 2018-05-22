<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Taqmandeliveries;
use App\Abbotdeliveries;
use App\LabEquipmentTracker;
use App\LabPerformanceTracker;

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
    	return view('tasks.home')->with('pageTitle', 'Lab Dashboard');
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
    	
    }

    public static function __getifKitsEntered($testtype,$platform,$quarter,$currentyear){

    	if ($platform==1)
			$model = Taqmandeliveries::where('testtype', $testtype)->where('flag', 1)->where('source', '<>', 2)->where('quarter', $quarter)->where('year', $currentyear)->count();

		if ($platform==2)
			$model = Abbotdeliveries::where('testtype', $testtype)->where('flag', 1)->where('source', '<>', 2)->where('quarter', $quarter)->where('year', $currentyear)->count();
			
		return $model;
    }
}
