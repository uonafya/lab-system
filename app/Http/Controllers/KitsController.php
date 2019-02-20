<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Abbotdeliveries;
use App\Taqmandeliveries;
use App\Allocation;
use App\AllocationDetail;

class KitsController extends Controller
{
    
    public function kits(Request $request)
    {
        if($request->method() == 'POST') {
            $platform = $request->input('platform');
            if ($platform == 'abbott') 
                $model = Abbotdeliveries::select('*')->where('lab_id', '=', env('APP_LAB'));
            if ($platform == 'taqman')
                $model = Taqmandeliveries::select('*')->where('lab_id', '=', env('APP_LAB'));
            
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
        $allocationSQL = "`year`, `month`, `testtype`,
						COUNT(IF(approve=0, 1, NULL)) AS `pending`,
						COUNT(IF(approve=1, 1, NULL)) AS `approved`,
						COUNT(IF(approve=2, 1, NULL)) AS `rejected`";
        $data = [
        				'allocations' => Allocation::selectRaw($allocationSQL)->groupBy(['year','month', 'testtype'])->orderBy('year', 'desc')->orderBy('month', 'desc')->get(),
        				'badge' => function($value, $type) {
        					$badge = "success";
        					if ($type == 1) {// Pending approval
        						if ($value > 0)
        							$badge = "warning";
        					} else if ($type == 2) { //Approved
        						if ($value == 0)
        							$badge = "warning";
        					} else if ($type == 3) { // Rejected
        						if ($value > 0)
        							$badge = "danger";
        					}
        					return $badge;
        				}
        			];
        // dd($data->badge{(1,1)});
        return view('reports.kitsreport', compact('data'))->with('pageTitle', 'Kits Reports');
    }

    public function allocation(Allocation $allocation) {

    }
}
