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
        //
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
}
