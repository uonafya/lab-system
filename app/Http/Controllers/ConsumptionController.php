<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Consumption;
use App\ConsumptionDetail;
use App\Machine;
use App\TestType;
use App\User;

class ConsumptionController extends Controller
{
    public function consumption (Request $request, $guide=null)
    {
        if ($guide != null)
            return redirect('http://lab-2.test.nascop.org/download/consumption');
        $model = new Consumption;
    	$period = collect($model->getMissingConsumptions())->first();

    	if ($request->method() == 'POST') {
    		$data = [
    				'machines' => Machine::whereIn('id', $request->input('machine'))->get(),
                	'period' => $period,
                	'types' => TestType::get(),
                	'users' => User::where('user_type_id', '<', 5)->get()
    			];
    		
    		return view('tasks.consumptions.platformkits', $data)->with('pageTitle', 'Kit Consumptions');
    	}

    	$machines = new Machine;
    	$data = [
                'machines' => $machines->missingConsumptions($period->year, $period->month),
                'period' => $period
            ];
            
        return view('tasks.consumptions.platformselection', $data)->with('pageTitle', 'Kit Consumptions');
    }

    public function saveconsumption(Request $request)
    {
    	$model = new Consumption;
    	$period = collect($model->getMissingConsumptions())->first();
    	$fields = ['begining_balance', 'positive_adjustment', 'negative_adjustment', 'wasted', 'ending_balance', 'issuedcomment', 'receivedcomment'];
    	$machines = Machine::whereIn('id', $request->input('machine'))->get();
    	$types = TestType::get();
    	$formdata = $request->only($fields);
    	foreach ($machines as $machineskey => $machine) {
    		foreach ($types as $typeskey => $typesvalue) {
    			$data[$machine->machine][$typeskey] = [
	    					'year' => $period->year,
	    					'month' => $period->month,
	    					'type' => $typesvalue->id,
	    					'machine' => $machine->id,
	    					'lab_id' => env('APP_LAB'),
	    					'submittedby' => auth()->user()->id,
	    					'datesubmitted' => date('Y-m-d'),
	    					'comments' => $formdata['receivedcomment'][$machine->machine][$typesvalue->name],
	    					'issuedcomments' => $formdata['issuedcomment'][$machine->machine][$typesvalue->name]
	    				];
	    		foreach ($fields as $fieldskey => $fieldsvalue) {
	    			if (!($fieldsvalue == 'issuedcomment' || $fieldsvalue == 'receivedcomment')){
	    				foreach ($machine->kits as $kitskey => $kitsvalue) {
		    				$data[$machine->machine][$typeskey]['details'][$kitsvalue->id]['kit_id'] = $kitsvalue->id;
		    				$data[$machine->machine][$typeskey]['details'][$kitsvalue->id][$fieldsvalue] = $formdata[$fieldsvalue][$machine->machine][$typesvalue->name][$kitsvalue->id];
		    			}
	    			}
	    		}
    		}
    	}

    	// Inserting the deliveries
    	foreach ($data as $machinekey => $machinevalue) {
    		foreach ($machinevalue as $consumptionkey => $consumption) {
	    		if (Consumption::duplicate($consumption['year'], $consumption['month'], $consumption['type'], $consumption['machine'], $consumption['lab_id'])->get()->isEmpty()) {
	    			$details = $consumption['details'];
		    		unset($consumption['details']);
		    		$saveconsumption = Consumption::create($consumption);
		    		$this->saveConsumptionDetails($saveconsumption, $details);
	    		}
	    	}
    	}

    	$model->submitNullConsumption($period->year, $period->month);
    	return redirect()->route('pending');

    }

    private function saveConsumptionDetails($consumption, $details)
    {
    	foreach ($details as $key => $detail) {
    		$detail['consumption_id'] = $consumption->id;
    		ConsumptionDetail::create($detail);
    	}
    	return true;
    }
}
