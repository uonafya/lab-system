<?php

namespace App\Http\Controllers;

use App\QuarantineSite;
use Illuminate\Http\Request;

class QuarantineSiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $quarantine_sites = QuarantineSite::all();
        return view('tables.quarantine_sites', compact('quarantine_sites'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return \Illuminate\Http\Response
     */
    public function show(QuarantineSite $quarantineSite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return \Illuminate\Http\Response
     */
    public function edit(QuarantineSite $quarantineSite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\QuarantineSite  $quarantineSite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QuarantineSite $quarantineSite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\QuarantineSite  $quarantineSite
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuarantineSite $quarantineSite)
    {
        //
    }
}
