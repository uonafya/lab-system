<?php

namespace App\Http\Controllers;

use App\Worksheet;
use App\Sample;
use DB;
use Illuminate\Http\Request;

class WorksheetController extends Controller
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
        $samples = Sample::selectRaw("samples.id, patient_id, samples.parentid, batches.datereceived, batches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('samples.id', 'asc')
            ->limit(22)
            ->get();

        $count = $samples->count();

        if($count == 22){
            return view('forms.worksheets', ['create' => true, 'machine_type' => 1]);
        }

        return view('forms.worksheets', ['create' => false, 'machine_type' => 1, 'count' => $count]);
    }

    public function abbot()
    {
        $samples = Sample::selectRaw("samples.id, patient_id, samples.parentid, batches.datereceived, batches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result =0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('samples.id', 'asc')
            ->limit(94)
            ->get();

        $count = $samples->count();

        if($count == 94){
            return view('forms.worksheets', ['create' => true, 'machine_type' => 2]);
        }

        return view('forms.worksheets', ['create' => false, 'machine_type' => 2, 'count' => $count]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $worksheet = new Worksheet;
        $worksheet->fill($request->except('_token'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $samples = Sample::selectRaw("samples.id, patient_id, samples.parentid, batches.datereceived, batches.high_priority, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->join('batches', 'samples.batch_id', '=', 'batches.id')
            ->whereYear('datereceived', '>', 2014)
            ->where('inworksheet', 0)
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw('((result IS NULL ) OR (result = 0 ))')
            ->orderBy('isnull', 'asc')
            ->orderBy('high_priority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('samples.id', 'asc')
            ->when($worksheet, function($query) use ($worksheet){
                if($worksheet->machine_type == 1){
                    return $query->limit(22);
                }
                else{
                    return $query->limit(94);
                }
            });

        $sample_ids = $samples->pluck('id');

        DB::table('samples')->whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id, 'inworksheet' => true]);

        return redirect()->route('worksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Worksheet $worksheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Worksheet $worksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Worksheet $worksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Worksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Worksheet $worksheet)
    {
        //
    }

    public function print(Worksheet $worksheet)
    {
        $worksheet->load(['creator']);
        // $samples = $worksheet->sample;
        $samples = Sample::where('worksheet_id', $worksheet->id)->with(['patient'])->get();

        if($worksheet->machine_type == 1){
            return view('worksheets.other-table', ['worksheet' => $worksheet, 'samples' => $samples]);
        }
        else{
            return view('worksheets.abbot-table', ['worksheet' => $worksheet, 'samples' => $samples]);
        }
    }
}
