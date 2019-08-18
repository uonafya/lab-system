<?php

namespace App\Http\Controllers;

use App\DrBulkRegistration;
use App\DrSample;
use App\Lookup;
use Illuminate\Http\Request;
use Excel;

class DrBulkRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $templates = DrBulkRegistration::withCount(['sample'])->get();
        return view('tables.dr_bulk_registration', ['templates' => $templates]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $samples = DrSample::whereNull('bulk_registration_id')
        ->whereNull('worksheet_id')
        ->whereNull('extraction_worksheet_id')
        ->where('datereceived', '>', date('Y-m-d', strtotime('-1 year')))
        ->where(['receivedstatus' => 1, 'control' => 0])
        ->orderBy('run', 'desc')
        ->orderBy('datereceived', 'asc')
        ->orderBy('id', 'asc')
        ->get();

        if($samples->first()){
            $b = DrBulkRegistration::create(['createdby' => auth()->user()->id, 'lab_id' => env('APP_LAB')]);
            DrSample::whereNull('bulk_registration_id')
                ->whereNull('worksheet_id')
                ->whereNull('extraction_worksheet_id')
                ->where('datereceived', '>', date('Y-m-d', strtotime('-1 year')))
                ->where(['receivedstatus' => 1, 'control' => 0])
                ->update(['bulk_registration_id' => $b->id]);

            session(['toast_message' => 'The bulk registration template has been created.']);
            return back();
        }

        session(['toast_error' => 1, 'toast_message' => 'The bulk registration template could not be created.']);
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
        $data = MiscDr::get_bulk_registration_samples($request->input('samples'), 16);

        if(!$data['create']){
            session(['toast_error' => 1, 'toast_message' => 'The sequencing woksheet could not be created.']);
            return back();
        }

        $drBulkRegistration = new DrBulkRegistration;
        $drBulkRegistration->fill($request->except(['_token', 'samples']));
        $drBulkRegistration->save();        

        $samples = $data['samples'];

        foreach ($samples as $s) {
            $sample = DrSample::find($s->id);
            $sample->bulk_registration_id = $drBulkRegistration->id;
            $sample->save();
        }
        return redirect('dr_worksheet/print/' . $dr_worksheet->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrBulkRegistration  $drBulkRegistration
     * @return \Illuminate\Http\Response
     */
    public function show(DrBulkRegistration $drBulkRegistration)
    {
        $samples = DrSample::with(['patient'])->where(['bulk_registration_id' => $drBulkRegistration->id])->get();
        $data = [];

        foreach ($samples as $key => $sample) {
            $data[] = [
                'Patient ID' => $sample->patient->nat,
                'Project Name' => Lookup::retrieve_val('dr_projects', $sample->project),
                'Full Name' => $sample->patient->patient_names,
                'DOB' => $sample->patient->dob,
                'Sex' => $sample->patient->gender,
                'Date of Sample Collection' => $sample->datecollected,
                'Sample Type' => Lookup::retrieve_val('sample_types', $sample->sampletype),
                'Most Current HIV VL Result (copies/mL)' => $sample->vl_result1,
                'Most Current HIV VL Result Date' => $sample->vl_date_result1,
                'Patient Regimen' => Lookup::retrieve_val('prophylaxis', $sample->prophylaxis),
                'Most Recent CD4 Count' => $sample->cd4_result,
                'Patient Current Age' => $sample->age,
                'Amount' => $sample->sample_amount,
                'Amount Unit' => Lookup::retrieve_val('amount_units', $sample->amount_unit),
                'Container Type' => Lookup::retrieve_val('container_types', $sample->container_type),
                'Location Barcode' => '',
            ];
        }

        $filename = 'bulk_template_' . $drBulkRegistration->id;

        Excel::create($filename, function($excel) use($data){
            $excel->sheet('Sheetname', function($sheet) use($data) {
                $sheet->fromArray($data);
            });
        })->download('csv');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrBulkRegistration  $drBulkRegistration
     * @return \Illuminate\Http\Response
     */
    public function edit(DrBulkRegistration $drBulkRegistration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrBulkRegistration  $drBulkRegistration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrBulkRegistration $drBulkRegistration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrBulkRegistration  $drBulkRegistration
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrBulkRegistration $drBulkRegistration)
    {
        //
    }
}
