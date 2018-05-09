<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\EidRequest;
use App\Api\V1\Requests\BlankRequest;
use DB;

use App\Lookup;
use App\Sample;
use App\Viralsample;

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
        $test = $request->input('test');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $date_dispatched_start = $request->input('date_dispatched_start');
        $date_dispatched_end = $request->input('date_dispatched_end');
        $patients = $request->input('patient_id');
        $facility = $request->input('facility_code');
        $orders = $request->input('order_numbers');
        $location = $request->input('location');

        $batch_array = ['b.national_batch_id', 'b.datereceived', 'b.datedispatched', 'f.facilitycode', 'p.national_patient_id', 'p.patient', 'patient_name'];
 
        if($test == 1){
            $table_name = 'samples AS s';
            $batch_name = 'batches AS b';
            $patient_name = 'patients AS p';
            $select_array = array_merge(['s.*'], $batch_array);
        } 
        if($test == 2){
            $table_name = 'viralsamples AS s';
            $batch_name = 'viralbatches AS b';
            $patient_name = 'viralpatients AS p';
            $select_array = array_merge(['s.*'], $batch_array);
        } 

        if($patients){
            $patients = str_replace(' ', '', $patients);
            $patients = explode(',', $patients);
        }
        if($orders){
            $orders = str_replace(' ', '', $orders);
            $orders = explode(',', $orders);
        } 
 
        $result = DB::table($table_name)
            ->select($select_array)
            ->join($batch_name, 'b.id', '=', 's.batch_id')
            ->join($patient_name, 's.patient_id', '=', 'p.id')
            ->join('facilitys AS f', 'f.id', '=', 'b.facility_id')
            ->when($facility, function($query) use($facility){
                return $query->where('f.facilitycode', $facility);
            })
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


        $result->transform(function ($sample, $key) use ($test){
            // return ['patient age' => $item->age];

            if($sample->receivedstatus == 2){
                $sample->sample_status = "Rejected";
            }
            else{
                if($test == 1){
                    if($sample->approvedby && ($sample->result > 0 || $sample->result) && $sample->repeatt == 0){
                        $sample->sample_status = "Complete";
                    }
                    else{
                        $sample->sample_status = "Incomplete";
                    }
                }
                if($test == 2){
                    if($sample->approvedby && ($sample->result > 0 || $sample->result) && $sample->repeatt == 0){
                        $sample->sample_status = "Complete";
                    }
                    else{
                        $sample->sample_status = "Incomplete";
                    }
                }
            }
            $approved = false;
            if($sample->approvedby) $approved = true;

            return [
                'id' => $sample->id,
                'order_number' => $sample->order_no,
                'patient' => $sample->patient,
                'provider_identifier' => $sample->provider_identifier,
                'facility_code' => $sample->facilitycode,
                'AMRs_location' => $sample->amrs_location,
                'full_names' => $sample->patient_name,
                'date_collected' => $sample->my_date_format('datecollected'),
                'date_received' => $sample->my_date_format('datereceived'),
                'date_tested' => $sample->my_date_format('datetested'),
                'interpretation' => $sample->interpretation,
                'result' => $sample->result,
                'date_dispatched' => $sample->my_date_format('datedispatched'),
                'sample_status' => $sample->sample_status
            ];
        });

        return $result;
 
     }




}
