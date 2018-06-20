<?php

namespace App\Http\Controllers;

use App\DrWorksheet;
use App\DrPatient;
use App\DrResult;

use App\Lookup;
use Illuminate\Http\Request;

class DrWorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Lookup::get_dr();

        $patients = DrPatient::selectRaw("dr_patients.*")
                        ->join('drug_resistance_reasons', 'drug_resistance_reasons.id', '=', 'dr_patients.dr_reason_id')
                        ->orderBy('drug_resistance_reasons.rank', 'asc')
                        ->whereNull('worksheet_id')
                        ->limit(14)
                        ->get();
        $patients->load(['patient.facility']);
        $data['dr_patients'] = $patients;
        return view('forms.dr_worksheets', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dr_worksheet = new DrWorksheet;
        $dr_worksheet->lab_id = env('APP_LAB');
        $dr_worksheet->save();

        $patients = DrPatient::selectRaw("dr_patients.*")
                        ->join('drug_resistance_reasons', 'drug_resistance_reasons.id', '=', 'dr_patients.dr_reason_id')
                        ->orderBy('drug_resistance_reasons.rank', 'asc')
                        ->whereNull('worksheet_id')
                        ->limit(14)
                        ->get();
        $data = Lookup::get_dr();
        $dr_primers = $data['dr_primers'];

        foreach ($patients as $patient) {
            foreach ($dr_primers as $dr_primer) {
                $dr_result = new DrResult;
                $dr_result->patient_id = $patient->id;
                $dr_result->dr_primer_id = $dr_primer->id;
                $dr_result->save();
            }
        }
        return redirect('dr_worksheet/' . $dr_worksheet->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrWorksheet  $drWorksheet
     * @return \Illuminate\Http\Response
     */
    public function show(DrWorksheet $drWorksheet)
    {
        $patients = DrPatient::where('worksheet_id', $drWorksheet->id)->get();
        $patient_ids = $patients->pluck(['id'])->toArray();
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrWorksheet  $drWorksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(DrWorksheet $drWorksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrWorksheet  $drWorksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrWorksheet $drWorksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrWorksheet  $drWorksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrWorksheet $drWorksheet)
    {
        //
    }
}
