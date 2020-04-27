<?php

namespace App\Http\Controllers;

use App\DrExtractionWorksheet;
use App\DrSample;
use App\DrSampleView;

use App\Lookup;
use App\MiscDr;

use Illuminate\Http\Request;

class DrExtractionWorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state=0, $date_start=NULL, $date_end=NULL, $worksheet_id=NULL)
    {
        $worksheets = DrExtractionWorksheet::with(['creator'])->withCount(['sample'])
            ->when($state, function ($query) use ($state){
                return $query->where('status_id', $state);
            })
            ->when($date_start, function($query) use ($date_start, $date_end){
                if($date_end)
                {
                    return $query->whereDate('dr_extraction_worksheets.created_at', '>=', $date_start)
                    ->whereDate('dr_extraction_worksheets.created_at', '<=', $date_end);
                }
                return $query->whereDate('dr_extraction_worksheets.created_at', $date_start);
            })
            ->orderBy('dr_extraction_worksheets.created_at', 'desc')
            ->get();

        $data = Lookup::get_dr();
        $data['worksheets'] = $worksheets;
        $data['myurl'] = url('dr_worksheet/index/' . $state . '/');
        return view('tables.dr_extraction_worksheets', $data)->with('pageTitle', 'Worksheets'); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($limit)
    {
        $data = Lookup::get_dr();
        $data = array_merge($data, MiscDr::get_extraction_worksheet_samples($limit));
        return view('forms.dr_extraction_worksheet', $data);        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        $worksheet = new DrExtractionWorksheet;
        $worksheet->fill($request->except(['_token', 'limit']));
        $worksheet->save();

        $positive_control = DrSample::create(['extraction_worksheet_id' => $worksheet->id, 'patient_id' => 0, 'control' => 2]);
        $negative_control = DrSample::create(['extraction_worksheet_id' => $worksheet->id, 'patient_id' => 0, 'control' => 1]);

        $data = MiscDr::get_extraction_worksheet_samples($request->input('limit'));

        if(!$data['create']){
            session(['toast_error' => 1, 'toast_message' => 'The extraction woksheet could not be created.']);
            return back();
        }
        $samples = $data['samples'];

        foreach ($samples as $s) {
            $sample = DrSample::find($s->id);
            $sample->extraction_worksheet_id = $worksheet->id;
            $sample->save();
        }
        return redirect('dr_extraction_worksheet');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrExtractionWorksheet  $drExtractionWorksheet
     * @return \Illuminate\Http\Response
     */
    public function show(DrExtractionWorksheet $drExtractionWorksheet)
    {

        $drExtractionWorksheet->load(['creator']);
        $sample_array = ViralsampleView::select('id')->where('worksheet_id', $Viralworksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        // $samples = Viralsample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();
        
        $samples = Viralsample::join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
                    ->with(['patient', 'batch.facility'])
                    ->select('viralsamples.*', 'viralbatches.facility_id')
                    ->whereIn('viralsamples.id', $sample_array)
                    ->orderBy('run', 'desc')
                    ->when(true, function($query){
                        if(in_array(env('APP_LAB'), [2])) return $query->orderBy('facility_id')->orderBy('batch_id', 'asc');
                        if(in_array(env('APP_LAB'), [3])) $query->orderBy('datereceived', 'asc');
                        if(!in_array(env('APP_LAB'), [8, 9, 1])) return $query->orderBy('batch_id', 'asc');
                    })
                    ->orderBy('viralsamples.id', 'asc')
                    ->get();

        $data = ['worksheet' => $Viralworksheet, 'samples' => $samples, 'i' => 0];

        if($print) $data['print'] = true;

        if($Viralworksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Other Worksheets');
        }
        else if($Viralworksheet->machine_type == 3){
            return view('worksheets.c-8800', $data)->with('pageTitle', 'C8800 Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Abbot Worksheets');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrExtractionWorksheet  $drExtractionWorksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(DrExtractionWorksheet $drExtractionWorksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrExtractionWorksheet  $drExtractionWorksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrExtractionWorksheet $drExtractionWorksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrExtractionWorksheet  $drExtractionWorksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrExtractionWorksheet $drExtractionWorksheet)
    {
        //
    }

    /**
     * Download the specified resource as csv.
     *
     * @param  \App\DrWorksheet $worksheet
     * @return \Illuminate\Http\Response
     */
    public function download(DrExtractionWorksheet $drExtractionWorksheet)
    {
        $samples = DrSample::with(['patient'])->where(['extraction_worksheet_id' => $drExtractionWorksheet->id, 'control' => 0])->get();
        $data = [];

        foreach ($samples as $key => $sample) {
            $data[] = [
                'NAT ID' => $sample->patient->nat,
                'Patient CCC' => $sample->patient->patient,
                'Project Name' => Lookup::retrieve_val('dr_projects', $sample->project),
                'Full Name' => $sample->patient->patient_name,
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

        $filename = 'bulk_template_extraction_' . $drExtractionWorksheet->id . '.csv';

        MiscDr::downloadCSV($data, $filename);

        /*Excel::create($filename, function($excel) use($data){
            $excel->sheet('Sheetname', function($sheet) use($data) {
                $sheet->fromArray($data);
            });
        })->download('csv');*/
    }


    public function gel_documentation_form(DrExtractionWorksheet $drExtractionWorksheet)
    {
        $data = Lookup::get_dr();
        $data['worksheet'] = $drExtractionWorksheet;
        $data['samples'] = $drExtractionWorksheet->sample_view;
        return view('forms.dr_gel_documentation', $data);
    }


    public function gel_documentation(Request $request, DrExtractionWorksheet $drExtractionWorksheet)
    {
        $drExtractionWorksheet->date_gel_documentation = date('Y-m-d');
        $drExtractionWorksheet->status_id = 3;
        $drExtractionWorksheet->save();

        $sample_ids = $request->input('samples');
        $cns = $request->input('cns');
        $reruns = $request->input('reruns');

        if($sample_ids && is_array($sample_ids)) DrSample::where('extraction_worksheet_id', $drExtractionWorksheet->id)->whereIn('id', $sample_ids)->update(['passed_gel_documentation' => true]);

        if($cns && is_array($cns)){
            DrSample::where('extraction_worksheet_id', $drExtractionWorksheet->id)
                ->whereNotIn('id', $sample_ids)
                ->whereIn('id', $cns)
                ->update([
                    'passed_gel_documentation' => false, 'collect_new_sample' => 1,
                    'approvedby' => auth()->user()->id, 'dateapproved' => date('Y-m-d') 
                ]);
        }

        $samples = DrSample::where('extraction_worksheet_id', $drExtractionWorksheet->id)
            ->when($sample_ids, function($query) use($sample_ids){
                return $query->whereNotIn('id', $sample_ids);
            })
            ->when($cns, function($query) use($cns){
                return $query->whereNotIn('id', $cns);
            })
            ->get();

        foreach ($samples as $key => $sample){
            $sample->passed_gel_documentation = 0;
            $sample->create_rerun();
        }

        session(['toast_message' => 'Gel documentation has been submitted.']);
        return redirect('dr_worksheet/create/' . $drExtractionWorksheet->id);
    }


    public function cancel(DrExtractionWorksheet $drExtractionWorksheet)
    {
        if($drExtractionWorksheet->status_id != 1){
            session(['toast_message' => 'The worksheet is not eligible to be cancelled.']);
            session(['toast_error' => 1]);
            return back();
        }
        DrSample::where(['extraction_worksheet_id' => $drExtractionWorksheet->id])->update(['extraction_worksheet_id' => null]);
        DrSample::where(['extraction_worksheet_id' => $drExtractionWorksheet->id])->delete();
        $drExtractionWorksheet->status_id = 4;
        $drExtractionWorksheet->save();



        session(['toast_message' => 'The worksheet has been cancelled.']);
        return back();
        // return redirect("/worksheet");
    }

    public function vl_worksheet(DrExtractionWorksheet $drExtractionWorksheet)
    {
        $samples = $drExtractionWorksheet->sample()->where(['passed_gel_documentation' => false])->get();

        foreach ($variable as $key => $value) {
            # code...
        }
    }
}
