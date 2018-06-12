<?php

namespace App\Http\Controllers;

use App\DrPatient;
use App\Lookup;
use Illuminate\Http\Request;

class DrPatientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Lookup::get_dr();
        $data['dr_patients'] = DrPatient::with(['patient'])->get();
        return view('tables.dr_patients', $data);
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
     * @param  \App\DrPatient  $drPatient
     * @return \Illuminate\Http\Response
     */
    public function show(DrPatient $drPatient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrPatient  $drPatient
     * @return \Illuminate\Http\Response
     */
    public function edit(DrPatient $drPatient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrPatient  $drPatient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrPatient $drPatient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrPatient  $drPatient
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrPatient $drPatient)
    {
        //
    }
}
