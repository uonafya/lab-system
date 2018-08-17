<?php

namespace App\Http\Controllers;

use App\DrWorksheet;
use App\DrPatient;
use App\DrSample;
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
        $samples = DrSample::selectRaw("dr_samples.*")
                        ->join('drug_resistance_reasons', 'drug_resistance_reasons.id', '=', 'dr_samples.dr_reason_id')
                        ->orderBy('drug_resistance_reasons.rank', 'asc')
                        ->whereNull('worksheet_id')
                        ->limit(14)
                        ->get();

        $samples->load(['patient.facility']);
        $data['dr_samples'] = $samples;
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

        $samples = DrSample::selectRaw("dr_samples.*")
                        ->join('drug_resistance_reasons', 'drug_resistance_reasons.id', '=', 'dr_samples.dr_reason_id')
                        ->orderBy('drug_resistance_reasons.rank', 'asc')
                        ->whereNull('worksheet_id')
                        ->limit(14)
                        ->get();
        $data = Lookup::get_dr();
        $dr_primers = $data['dr_primers'];

        foreach ($samples as $sample) {
            foreach ($dr_primers as $dr_primer) {
                $dr_result = new DrResult;
                $dr_result->patient_id = $sample->id;
                $dr_result->dr_primer_id = $dr_primer->id;
                $dr_result->save();
            }
            $sample->worksheet_id = $dr_worksheet->id;
            $sample->save();
        }
        return redirect('dr_worksheet/print/' . $dr_worksheet->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrWorksheet  $drWorksheet
     * @return \Illuminate\Http\Response
     */
    public function show(DrWorksheet $drWorksheet, $print=false)
    {
        $data = Lookup::get_dr();

        $samples = $drWorksheet->sample;
        $sample_ids = $samples->pluck(['id'])->toArray();

        $dr_results = DrResult::whereIn('sample_id', $sample_ids)->orderBy('sample_id', 'asc')->orderBy('dr_primer_id', 'asc')->get();
        $data['dr_results'] = $dr_results;
        if($print) $data['print'] = true;
        return view('worksheets.dr', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrWorksheet  $drWorksheet
     * @return \Illuminate\Http\Response
     */
    public function print(DrWorksheet $drWorksheet)
    {
        $data = Lookup::get_dr();

        $samples = $drWorksheet->sample;
        $sample_ids = $samples->pluck(['id'])->toArray();

        $dr_results = DrResult::whereIn('sample_id', $sample_ids)->orderBy('sample_id', 'asc')->orderBy('dr_primer_id', 'asc')->get();
        $data['dr_results'] = $dr_results;
        $data['print'] = true;
        return view('worksheets.dr', $data);
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

    public function save_results(Request $request, DrWorksheet $worksheet)
    {
        $worksheet->fill($request->except(['_token', 'upload']));
        $file = $request->upload->path();
        $zip = new ZipArchive;
        $path = storage_path('app/public/results/dr/' . $worksheet->id . '/');
        mkdir($path, 0777, true);

        if($zip->open($file) === TRUE){
            $zip->extractTo($path);
            $zip->close();
        }
    }

    public function cancel_upload(DrWorksheet $worksheet)
    {
        $path = storage_path('app/public/results/dr/' . $worksheet->id . '/');
        \App\Common::delete_folder($path);
    }
}
