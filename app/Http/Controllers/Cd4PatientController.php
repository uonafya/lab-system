<?php

namespace App\Http\Controllers;

use App\Cd4Patient;
use Illuminate\Http\Request;

class Cd4PatientController extends Controller
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
     * @param  \App\Cd4Patient  $cd4Patient
     * @return \Illuminate\Http\Response
     */
    public function show(Cd4Patient $cd4Patient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cd4Patient  $cd4Patient
     * @return \Illuminate\Http\Response
     */
    public function edit(Cd4Patient $cd4Patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cd4Patient  $cd4Patient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cd4Patient $cd4Patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cd4Patient  $cd4Patient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cd4Patient $cd4Patient)
    {
        //
    }

    public function search_name(Request $request) {
        $search = $request->input('search');
        $patients = Cd4Patient::where('patient_name', 'like', '%'.$search.'%')->paginate(10);
        
        $patients->setPath(url()->current());
        return $patients;
    }

    public function new_patient(Request $request)
    {
        echo json_encode(Cd4Patient::where('medicalrecordno', '=', $request->input('patient'))->first());
    }
}
