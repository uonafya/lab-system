<?php

namespace App\Http\Controllers;

use App\CancerPatient;
use App\CancerSample;
use App\CancerSampleView;
use App\Lookup;
use DB;
use Illuminate\Http\Request;

class CancerSampleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($param=null)
    {
        $user = auth()->user();
        $samples = CancerSampleView::with(['facility'])->where('facility_id', $user->facility_id)
                                ->orWhere('user_id', $user->id)
                                ->when($param, function($query) use ($param){
                                    return $query->whereNull('result')->where('receivedstatus', 1);
                                })->paginate();
        $data['samples'] = $samples;
        // dd($samples);
        return view('tables.cancer_samples', $data)->with('pageTitle', 'Eid POC Samples');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Lookup::cancersample_form();
        return view('forms.cancersamples', $data)->with('pageTitle', 'Add Cervical Cancer Sample');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $submit_type = $request->input('submit_type');
        $user = auth()->user();
        $patient_string = $request->input('patient');
        
        DB::beginTransaction();
        try {
            $cancerpatient = CancerPatient::existing($request->input('facility_id'), $patient_string)->first();
            if(!$cancerpatient) 
                $cancerpatient = new CancerPatient;

            $data = $request->only(['facility_id', 'patient', 'patient_name',
                                'dob', 'sex', 'entry_point', 'hiv_status']);
            if(!$data['dob'])
                $data['dob'] = Lookup::calculate_dob($request->input('datecollected'), $request->input('age'), 0);
            $cancerpatient->fill($data);
            $cancerpatient->patient = $patient_string;
            $cancerpatient->pre_update();

            $data = $request->only(['facility_id', 'sampletype', 'datecollected', 'justification', 'datereceived', 'receivedstatus', 'rejectedreason', 'age']);
            if (!isset($data['age'])){
                $diff = abs(strtotime($request->input('datecollected')) - strtotime($request->input('dob')));
                $data['age'] = floor($diff / (365*60*60*24));
            }
            
            $cancersample = new CancerSample;
            $cancersample->fill($data);
            $cancersample->sample_type = $cancersample->sampletype;
            unset($cancersample->sampletype);
            $cancersample->patient_id = $cancerpatient->id;
            $cancersample->user_id = $user->id;
            $cancersample->save();

            DB::commit();
            if ($submit_type == 'add') {
                session(['toast_message' => "Cervical Cancer Sample added successfully."]);
                return back();
            } else if ($submit_type == 'release') {
                session(['toast_message' => "Cervical Cancer Sample added successfully."]);
                return redirect('cancersample');
            }
        } catch(\Exception $e) {
            DB::rollback();
            throw $e;
            // session(['toast_error' => true, 'toast_message' => "An error occured while saving cervical cancer sample. {$e}"]);
            // return back();
        }
    }

    public function save_result(Request $request, CancerSample $sample)
    {
        $data = $request->only(["approvedby", "approvedby2", "dateapproved", "dateapproved2",
        "datemodified", "datetested", "lab_id", "result", "action"]);
        $sample->fill($data);
        $sample->datedispatched = $data['datetested'];
        $sample->dateapproved = $data['datetested'];
        $sample->dateapproved2 = $data['datetested'];
        $sample->pre_update();

        session(['toast_message' => 'Cancer Result sample updated successfully']);
        return redirect('cancersample');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Lookup::cancersample_form();
        $data['sample'] = CancerSample::find($id);
        return view('forms.cancersamples', $data)->with('pageTitle', 'Add Cervical Cancer Sample');
    }

    /**
     * Show the form for updating the specified resource.
     *
     * @param  CancerSample  $sample
     * @return \Illuminate\Http\Response
     */
    public function edit_result(CancerSample $sample)
    {
        $sample->load(['patient', 'facility']);
        $data = Lookup::cancer_lookups();
        $data['sample'] = $sample;
        return view('forms.cancer_result', $data)->with('pageTitle', 'Edit Cancer Result');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $cancersample = CancerSample::find($id);
            $cancerpatient = $cancersample->patient;
            $patient_string = $request->input('patient');
            
            if($cancerpatient->patient != $request->input('patient')){
                $cancerpatient = CancerPatient::existing($request->input('facility_id'), $request->input('patient'))->first();
                $different_patient = true;

                if(!$cancerpatient){
                    $cancerpatient = new CancerPatient;
                    $created_patient = true;
                }
            }
            $patient_update_data = $request->only(['facility_id', 'patient', 'patient_name',
                                'dob', 'sex', 'entry_point', 'hiv_status']);

            if(!$patient_update_data['dob'])
                $patient_update_data['dob'] = Lookup::calculate_dob($request->input('datecollected'), $request->input('age'), 0);
            $cancerpatient->fill($patient_update_data);
            $cancerpatient->patient = $patient_string;
            $cancerpatient->pre_update();

            $data = $request->only(['facility_id', 'sampletype', 'datecollected', 'justification', 'datereceived', 'receivedstatus', 'rejectedreason', 'age']);
            if (!isset($data['age'])){
                $diff = abs(strtotime($request->input('datecollected')) - strtotime($request->input('dob')));
                $data['age'] = floor($diff / (365*60*60*24));
            }
            
            $cancersample->fill($data);
            $cancersample->sample_type = $cancersample->sampletype;
            unset($cancersample->sampletype);
            $cancersample->patient_id = $cancerpatient->id;
            $cancersample->save();

            DB::commit();
            session(['toast_message' => "Cervical Cancer Sample updated successfully."]);
            return redirect('cancersample');
        } catch(\Exception $e) {
            DB::rollback();
            throw $e;
            // session(['toast_error' => true, 'toast_message' => "An error occured while saving cervical cancer sample. {$e}"]);
            // return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Print the specified resource.
     *
     * @param  \App\Batch  $batch
     * @return \Illuminate\Http\Response
     */
    public function print(CancerSample $sample)
    {
        $data = Lookup::cancer_lookups();
        $sample->load(['patient', 'facility']);
        $data['samples'] = [$sample];

        return view('exports.mpdf_cancersamples', $data)->with('pageTitle', 'Individual Samples');
    }
}
