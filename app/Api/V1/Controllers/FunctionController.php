<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\EidRequest;
use App\Api\V1\Requests\BlankRequest;
use DB;

use App\Lookup;

class FunctionController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('jwt:auth', []);
    }

    public function eid(EidRequest $request)
    {
        $code = $request->input('mflCode');
        $facility = Lookup::facility_mfl($code);

    }

    public function api(BlankRequest $request)
     {
         $start_date = $request->input('start_date');
         $end_date = $request->input('end_date');
         $date_dispatched_start = $request->input('date_dispatched_start');
         $date_dispatched_end = $request->input('date_dispatched_end');
         $patients = $request->input('patient_id');
         $orders = $request->input('order_numbers');
         $test = $request->input('test');
         $location = $request->input('location');
 
         if($test == 1){
             $table_name = 'samples AS s';
             $batch_name = 'batches AS b';
             $patient_name = 'patients AS p';
         } 
         if($test == 2){
             $table_name = 'viralsamples AS s';
             $batch_name = 'viralbatches AS b';
             $patient_name = 'viralpatients AS p';
         } 
 
        $result = DB::table($table_name)
             ->select('s.*')
             ->join($batch_name, 'b.id', '=', 's.batch_id')
             ->join($patient_name, 's.patient_id', '=', 'p.id')
             ->join('facilitys AS f', 'f.id', '=', 'b.facility_id')
             ->when($patients, function($query) use($patients){
                 return $query->whereIn('p.patient', $patients);
             })
             ->when($orders, function($query) use($orders){
                 return $query->whereIn('s.order_no', $orders);
             })
             ->when($location, function($query) use($location){
                 return $query->where('s.amrs_location', $location);
             })
             ->when(($start_date && $end_date), function($query) use($start_date, $end_date){
                 return $query->whereBetween('s.datecollected', [$start_date, $end_date]);
             })
             ->when(($date_dispatched_start && $date_dispatched_end), function($query) use($date_dispatched_start, $date_dispatched_end){
                 return $query->whereBetween('b.datedispatched', [$date_dispatched_start, $date_dispatched_end]);
             })
             ->paginate(10);


        $result->transform(function ($item, $key){
            return ['patient age' => $item->age];
        });

        return $result;


 
     }




}
