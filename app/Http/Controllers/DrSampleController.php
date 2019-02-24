<?php

namespace App\Http\Controllers;

use App\DrSample;
use App\DrPatient;
use App\User;
use App\Lookup;
use App\MiscDr;

use DB;
use Excel;
use Mpdf\Mpdf;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\DrugResistance;


class DrSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sample_status=null, $date_start=NULL, $date_end=NULL, $facility_id=NULL, $subcounty_id=NULL, $partner_id=NULL)
    {
        $user = auth()->user();
        $date_column = "datereceived";
        $string = "(user_id='{$user->id}' OR batches.facility_id='{$user->facility_id}')";

        $data = Lookup::get_dr();
        $data['dr_samples'] = DrSample::with(['patient.facility'])
            ->where(['control' => 0])
            ->when(true, function($query) use ($user, $string){
                if($user->user_type_id == 5) return $query->whereRaw($string);
            })
            ->when($sample_status, function($query) use ($sample_status){
                return $query->where('status_id', $sample_status);
            })
            ->when($date_start, function($query) use ($date_column, $date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate($date_column, '>=', $date_start)
                    ->whereDate($date_column, '<=', $date_end);
                }
                return $query->whereDate($date_column, $date_start);
            })
            ->paginate();

        $data['dr_samples']->setPath(url()->current());
        return view('tables.dr_samples', $data)->with('pageTitle', 'Drug Resistance Samples');        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Lookup::get_dr();
        return view('forms.dr_samples', $data)->with('pageTitle', 'Drug Resistance Samples');  
    }

    public function create_from_patient(DrPatient $patient)
    {        
        $data = $patient->only(['patient_id', 'dr_reason_id']);
        $data['user_id'] = auth()->user()->id;
        // $sample = DrSample::create($data);
        $sample = new DrSample;
        $sample->fill($data);
        $facility = $sample->patient->facility;
        $sample->facility_id = $facility->id;
        $sample->save();      

        $patient->status_id=2;
        $patient->save();

        // if($facility->email_array)
        // {
            $mail_array = ['joelkith@gmail.com', 'tngugi@gmail.com', 'baksajoshua09@gmail.com', 'jlusike@clintonhealthaccess.org'];
            // if(env('APP_ENV') == 'production') $mail_array = [$facility->email];
            Mail::to($mail_array)->send(new DrugResistance($sample));
            session(['toast_message' => 'The sample has been created and the email has been sent to the facility.']);
        // }  
        // else
        // {
        //     session(['toast_message' => 'The sample has been created but the email has not been sent to the facility because the facility does not have an email address in the system.'])
        // } 

        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $drSample = new DrSample;
        if($request->input('submit_type') == 'cancel') return back();
        $viralsamples_arrays = Lookup::viralsamples_arrays();
        $data = $request->only($viralsamples_arrays['dr_sample']);
        $data['user_id'] = auth()->user()->id;
        if(auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4) $data['received_by'] = auth()->user()->id;
        $drSample->fill($data);

        $others = $request->input('other_medications_text');
        $other_medications = $request->input('other_medications');
        $others = explode(',', $others);
        $drSample->other_medications = array_merge($other_medications, $others);
        $drSample->save();

        session(['toast_message' => 'The sample has been created.']);
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrSample  $drSample
     * @return \Illuminate\Http\Response
     */
    public function show(DrSample $drSample)
    {
        $drSample->load(['patient.facility', 'warning', 'dr_call.call_drug', 'genotype']);
        $data = Lookup::get_dr();
        $data['sample'] = $drSample;
        return view('tables.dr_sample', $data)->with('pageTitle', 'Drug Resistance Samples'); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrSample  $drSample
     * @return \Illuminate\Http\Response
     */
    public function edit(DrSample $drSample)
    {
        $drSample->load(['patient.facility']);
        $data = Lookup::get_dr();
        $data['sample'] = $drSample;
        return view('forms.dr_samples', $data)->with('pageTitle', 'Drug Resistance Samples');  
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrSample  $drSample
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrSample $drSample)
    {
        if($request->input('submit_type') == 'cancel') return redirect('/dr_sample');
        $viralsamples_arrays = Lookup::viralsamples_arrays();
        $data = $request->only($viralsamples_arrays['dr_sample']);

        if((auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4) && !$drSample->received_by){
            $data['received_by'] = auth()->user()->id;
        }

        $drSample->fill($data);

        $others = $request->input('other_medications_text');
        $other_medications = $request->input('other_medications');
        $others = explode(',', $others);
        $drSample->other_medications = array_merge($other_medications, $others);
        $drSample->save();

        session(['toast_message' => 'The sample has been updated.']);
        if(auth()->user()->user_type_id == 5) return redirect('/viralbatch');
        return redirect('/dr_sample');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrSample  $drSample
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrSample $drSample)
    {
        //
    }

    public function facility_edit(Request $request, User $user, DrSample $sample)
    {
        if(Auth::user()) Auth::logout();
        Auth::login($user);

        $fac = $user->facility;
        session(['logged_facility' => $fac]);

        $sample->load(['patient', 'facility']);
        $data = Lookup::get_dr();
        $data['sample'] = $sample;
        // dd($request);
        return view('forms.dr_samples', $data)->with('pageTitle', 'Edit Drug Resistance Sample');
    }


    public function results(DrSample $drSample, $print=false)
    {
        $drSample->load(['dr_call.call_drug']);
        $data = Lookup::get_dr();
        $data['sample'] = $drSample;
        $data['print'] = $print;
        return view('exports.dr_result', $data);  
    }
    
    public function download_results(DrSample $drSample)
    {
        $drSample->load(['dr_call.call_drug']);
        $data = Lookup::get_dr();
        $data['sample'] = $drSample;
        $filename = "dr_result_printout_" . $drSample->id . ".pdf";
        $mpdf = new Mpdf();
        $view_data = view('exports.mpdf_dr_result', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    }


    public function susceptability()
    {
        $call_array = MiscDr::$call_array;
        $regimen_classes = DB::table('regimen_classes')->get();
        $samples = DrSample::where(['status_id' => 1, 'control' => 0])->with(['dr_call.call_drug', 'patient'])->get();

        $top = ['', 'Drug Classes', ];
        $second = ['Sequence ID', 'Original Sample ID', ];

        foreach ($regimen_classes as $key => $value) {
            $top[] = $value->drug_class;
            $second[] = $value->short_name;
        }

        $rows[0] = $top;
        $rows[1] = $second;

        foreach ($samples as $sample_key => $sample) {
            $patient_string = $sample->patient->patient ?? '';
            $row = [$sample->id, $patient_string];

            foreach ($regimen_classes as  $regimen_key => $regimen) {
                $call = '';

                foreach ($sample->dr_call as $dr_call) {
                    foreach ($dr_call->call_drug as $call_drug) {
                        if($call_drug->short_name_id == $regimen->id){
                            $call = $call_drug->call;
                            $call_array[$call]['cells'][] = chr(64 + 3 + $regimen_key) . ($sample_key + 4);
                            
                            // $beginning = '';

                            // $char_key = $regimen_key + 3;
                            // if($char_key > 26){
                            //     $a = (int) ($char_key / 26);
                            //     $beginning = chr(64 + $a);
                            //     $char_key = $char_key % 26;
                            // }

                            // $call_array[$call]['cells'][] = $beginning . chr(64 + $char_key) . ($sample_key + 4);
                        }
                    }
                }
                $row[] = $call;
            }
            $rows[] = $row;
        }

        // dd($call_array);

        Excel::create("susceptability_report", function($excel) use($rows, $call_array) {
            $excel->sheet('Sheetname', function($sheet) use($rows, $call_array) {
                $sheet->fromArray($rows);

                foreach ($call_array as $my_call) {
                    foreach ($my_call['cells'] as $my_cell) {
                        $sheet->cell($my_cell, function($cell) use ($my_call) {
                            $cell->setBackground($my_call['resistance_colour']);
                        });
                    }
                }
            });
        })->download('xlsx');
    }

}
