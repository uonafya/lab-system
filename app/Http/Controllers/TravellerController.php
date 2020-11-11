<?php

namespace App\Http\Controllers;

use App\Traveller;
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
        //
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
        $travel_import = new TravellerImport;
        Excel::import($travel_import);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Traveller  $traveller
     * @return \Illuminate\Http\Response
     */
    public function show(Traveller $traveller)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Traveller  $traveller
     * @return \Illuminate\Http\Response
     */
    public function edit(Traveller $traveller)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Traveller  $traveller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Traveller $traveller)
    {
        //
    }
}
