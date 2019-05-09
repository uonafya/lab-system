<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Datatable;

class DatatableController extends Controller
{
	public $patient_sms_columns = [
		['db' => 'id', 'dt' => 'DT_RowId' ],
		['db' => 'facilityname', 'dt' => 1 ],
		['db' => 'patient', 'dt' => 2 ],
		['db' => 'patient_name', 'dt' => 3 ],
		['db' => 'age', 'dt' => 4 ],
		['db' => 'patient_phone_no', 'dt' => 5 ],
		['db' => 'datecollected', 'dt' => 6, ],
		['db' => 'datetested', 'dt' => 7, ],
		['db' => 'result', 'dt' => 8, ],
		['db' => 'datedispatched', 'dt' => 9, ],
		['db' => 'time_result_sms_sent', 'dt' => 10, ],
	];

	public function sms_view($param='eid')
	{
		$url = url()->current();
		if(strpos($url, 'viral')) $param = 'vl';
		$tl = strtoupper($param);
		return view('tables.sms_view', ['type' => $param])->with('pageTitle', "{$tl} Patient SMS Log");
	}



    public function sms_log(Request $request, $param)
    {
        $user = auth()->user();
        $string = "1";
        if($user->user_type_id == 5) $string = "(user_id='{$user->id}' OR facility_id='{$user->facility_id}' OR lab_id='{$user->facility_id}')";

        $class = \App\Synch::$synch_arrays[$param]['sampleview_class'];
        $query = $class::select(array_column($this->patient_sms_columns, 'db'))->whereRaw($string)->whereNotNull('time_result_sms_sent');
        Datatable::limit($request, $query);
        Datatable::order($request, $query, $this->patient_sms_columns);
        Datatable::filter($request, $query, $this->patient_sms_columns);

        $rows = $query->get();
        $data = [];

        $links = [
        	'eid' => 'sample',
        	'vl' => 'viralsample',
        ];

        foreach ($rows as $row) {
        	$d = [];
        	foreach ($row as $key => $value) {
        		if($key == 'id'){
        			$d['DT_RowId'] = 'row_' . $row->id; 
        		}
        		else{
        			$d[$key] = $value;
        		}
        	}
        	$d['action_link'] = "<a href='" . url($links[$param] . "/sms/{$row->id}") . "'>  </a> ";
        	$data[] = $d;
        }

    	/*$data->transform(function ($sample, $key) use ($param, $links){
    		if($param == 'eid') $sample->result = $sample->result_name;
    		$sample->action_link = "<a href='" url($links[$param] . "/sms/{$sample->id}") "'> ";

    		return $sample;
		});*/

        $draw = $request->input('draw');

        // Records total
        $recordsTotal = $class::select('COUNT(id) as my_count')->whereRaw($string)->whereNotNull('time_result_sms_sent')->first()->my_count;

        $query = $class::whereRaw($string)->whereNotNull('time_result_sms_sent');
        Datatable::filter($request, $query, $this->patient_sms_columns);
        $recordsFiltered = $query->first()->my_count;

        return [
        	'draw' => $draw ? intval($draw) : 0,
        	'recordsTotal' => $recordsTotal ?? 0,
        	'recordsFiltered' => $recordsFiltered ?? 0,
        	'data' => $data,
        ];
    }

}
