<?php

namespace App\Http\Controllers;

use App\Cd4Worksheet;
use App\Cd4Sample;
use App\Lookup;
use Excel;
use Illuminate\Http\Request;

class Cd4WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state=null)
    {
        $data = Lookup::worksheet_lookups();
        $data['worksheets'] = Cd4Worksheet::when($state, function($query) use ($state){
                                            return $query->where('status_id', '=', $state);
                                        })->orderBy('id', 'desc')->get();
        $data = (object) $data;
        
        return view('tables.cd4-worksheets', compact('data'))->with('pageTitle', 'Worksheets');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($limit)
    {
        $rerunsamples = $this->get_samples_for_rerun();

        if ($rerunsamples == 0) { // No rerun samples are available
            $samples = $this->get_samples_for_run($limit);
            $sampleCount = $samples->count();
            $worksheetCount = Cd4Worksheet::max('id')+1;
            $data['samples'] = $samples;
            $data['worksheet'] = $worksheetCount;
            $data['limit'] = $limit;
            $data = (object) $data;
            // dd($data->samples->first()->patient->patient_name);
            return view('forms.cd4worksheet', compact('data'))->with('pageTitle', "Create Worksheet ($limit)");
        } else {
            
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->except(['_token', 'limit']);
        $data['lab_id'] = env('APP_LAB');
        $data['createdby'] = auth()->user()->id;
        $data['status_id'] = 1;
        $worksheet = new Cd4Worksheet();
        $worksheet->fill($data);
        $worksheet->save();

        $samples = $this->get_samples_for_run($request->input('limit'));
        $sampleData = ['worksheet_id' => $worksheet->id, 'status_id' => 3];
        foreach ($samples as $key => $sample) {
            $sample->fill($sampleData);
            $sample->save();
        }

        return redirect()->route('cd4.worksheet.print', ['worksheet' => $worksheet->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cd4Worksheet  $cd4Worksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Cd4Worksheet $Worksheet)
    {
        $data['worksheet'] = $Worksheet;
        $data['samples'] = $Worksheet->samples;
        $data['view'] = true;
        $data = (object)$data;
        
        return view('forms.cd4worksheet', compact('data'))->with('pageTitle', "Worksheet No. $Worksheet->id Details");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cd4Worksheet  $cd4Worksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Cd4Worksheet $Worksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cd4Worksheet  $cd4Worksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cd4Worksheet $Worksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cd4Worksheet  $cd4Worksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cd4Worksheet $Worksheet)
    {
        //
    }

    public function upload(Request $request, Cd4Worksheet $worksheet){
        if ($request->method() == "PUT") {
            $file = $request->upload->path();
            // $path = $request->upload->store('public/results/cd4'); 
            $data = Excel::load($file, function($reader){
                $reader->toArray();
            })->get();
            dd($data);
            
        } else {
            return view('forms.cd4upload_results', compact('worksheet'))->with('pageTitle', "UPDATE TEST RESULTS FOR WORKSHEET NO $worksheet->id");    
        }
    }

    public function print(Cd4Worksheet $worksheet) {
        return view('worksheets.cd4', compact('worksheet'))->with('pageTitle', 'Worksheets');
    }

    public function cancel(Cd4Worksheet $worksheet){
        if($worksheet->status_id != 1){
            session(['toast_message' => 'The worksheet is not eligible to be cancelled.']);
            session(['toast_error' => 1]);
            return back();
        }
        $sample_array = Cd4Sample::select('id')->where('worksheet_id', $worksheet->id)->get()->pluck('id')->toArray();
        Cd4Sample::whereIn('id', $sample_array)->update(['worksheet_id' => null, 'result' => null, 'status_id' => 1]);
        $worksheet->status_id = 4;
        $worksheet->datecancelled = date("Y-m-d");
        $worksheet->cancelledby = auth()->user()->id;
        $worksheet->save();

        session(['toast_message' => 'The worksheet has been cancelled.']);
        return redirect("/cd4/worksheet");
    }

    public function get_samples_for_rerun(){
        return Cd4Sample::selectRaw("COUNT(*) as reruns")
                            ->whereNull('worksheet_id')
                            ->where('receivedstatus', '<>', 2)
                            ->where('status_id', '=', 1)
                            ->where('run', '>', 1)
                            ->where('parentid', '>', 0)->first()->reruns;

    }

    public function get_samples_for_run($limit){
        return Cd4Sample::whereNull('worksheet_id')->where('receivedstatus', '<>', 2)->where('status_id', '=', 1)
                                    ->orderBy('datereceived', 'asc')->orderBy('parentid', 'asc')->orderBy('id', 'asc')
                                    ->limit($limit)->get();
    }
}
