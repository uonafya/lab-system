<?php

namespace App\Http\Controllers;

use App\CovidWorksheet;
use App\CovidSample;
use App\Lookup;
use App\MiscCovid;
use Illuminate\Http\Request;

class CovidWorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($machine_type=2, $limit=null)
    {
        $data = MiscCovid::get_worksheet_samples($machine_type, $limit);
        if(!$data){
            session(['toast_message' => 'An error has occurred.', 'toast_error' => 1]);
            return back();
        }
        // dd($data);
        return view('forms.worksheets', $data)->with('pageTitle', 'Create Worksheet');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $worksheet = new CovidWorksheet;
        $worksheet->fill($request->except('_token', 'limit'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $data = MiscCovid::get_worksheet_samples($worksheet->machine_type, $request->input('limit'));

        if(!$data || !$data['create']){
            $worksheet->delete();
            session(['toast_message' => "The worksheet could not be created.", 'toast_error' => 1]);
            return back();            
        }
        $samples = $data['samples'];
        $sample_ids = $samples->pluck('id');

        CovidSample::whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id]);

        return redirect()->route('covid_worksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CovidWorksheet  $covidWorksheet
     * @return \Illuminate\Http\Response
     */
    public function show(CovidWorksheet $covidWorksheet)
    {
        $samples = $covidWorksheet->sample()->orderBy('run', 'desc')->orderBy('id', 'asc')->get();

        $data = ['worksheet' => $covidWorksheet, 'samples' => $samples, 'i' => 0, 'covid' => true];

        if($print) $data['print'] = true;

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', $data)->with('pageTitle', 'Worksheets');
        }
        else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Worksheets');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CovidWorksheet  $covidWorksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(CovidWorksheet $covidWorksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CovidWorksheet  $covidWorksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CovidWorksheet $covidWorksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CovidWorksheet  $covidWorksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(CovidWorksheet $covidWorksheet)
    {
        //
    }

    public function labels(CovidWorksheet $worksheet)
    {
        $samples = $worksheet->sample;
        return view('worksheets.labels', ['samples' => $samples]);
    }

    public function print(CovidWorksheet $worksheet)
    {
        return $this->show($worksheet, true);
    }

    public function cancel(CovidWorksheet $worksheet)
    {
        if($worksheet->status_id != 1){
            session(['toast_message' => 'The worksheet is not eligible to be cancelled.']);
            session(['toast_error' => 1]);
            return back();
        }
        $worksheet->sample()->update(['worksheet_id' => null, 'result' => null]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        session(['toast_message' => 'The worksheet has been cancelled.']);
        return redirect("/covid_worksheet");
    }
}
