<?php

namespace App\Http\Controllers;

use App\DrClinicalForm;
use App\DrClinicalVisit;
use Illuminate\Http\Request;

class DrClinicalFormController extends Controller
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
        $form = new DrClinicalForm;
        $form->fill($request->except('clinical_visits'));
        $form->save();

        $visits = $request->input('clinical_visits');

        foreach ($visits as $key => $value) {            
            $visit = new DrClinicalVisit;
            $visit->fill(get_object_vars($value));
            $form->visit()->save($visit);
        }
        return response()->json(['status' => 'ok'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrClinicalForm  $drClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function show(DrClinicalForm $drClinicalForm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrClinicalForm  $drClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function edit(DrClinicalForm $drClinicalForm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrClinicalForm  $drClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrClinicalForm $drClinicalForm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrClinicalForm  $drClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrClinicalForm $drClinicalForm)
    {
        //
    }
}
