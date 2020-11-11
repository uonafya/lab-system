<?php

namespace App\Http\Controllers;

use App\Traveller;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TravellerImport;
use Illuminate\Http\Request;

class TravellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $results = DB::table('results')->get();
        $samples = Traveller::orderBy('id', 'desc')->paginate();
        return view('tables.travellers', compact('samples', 'results'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('forms.upload_travellers');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        $filename_array = explode('.', $request->file('upload')->getClientOriginalName());
        $file_name =  \Str::random(40) . '.' . array_pop($filename_array);
        $path = $request->upload->storeAs('public/site_samples/traveller', $file_name); 
        $travel_import = new TravellerImport;
        Excel::import($travel_import, $path);
        if(session('toast_error')) return back();
        session(['toast_message' => 'The upload has been made.']);
        return redirect('/traveller'); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Traveller  $traveller
     * @return \Illuminate\Http\Response
     */
    public function show(Traveller $traveller)
    {
        $results = DB::table('results')->get();
        return view('exports.mpdf_traveller_samples', ['samples' => [$traveller], 'print' => true]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Traveller  $traveller
     * @return \Illuminate\Http\Response
     */
    public function edit(Traveller $traveller)
    {
        $results = DB::table('results')->get();
        return view('forms.traveller', compact('traveller', 'results'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Traveller  $traveller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Traveller $traveller)
    {
        $traveller->fill($request->all());
        $traveller->save();
        session(['toast_message' => 'The update has been made.']);
        return redirect('traveller');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Traveller  $traveller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Traveller $traveller)
    {
        $traveller->delete();
        session(['toast_message' => 'The deletion has been made.']);
        return redirect('traveller');
    }
}
