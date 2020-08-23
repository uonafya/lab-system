<?php

namespace App\Http\Controllers;

use App\UlizaClinicalForm;
use App\UlizaClinicalVisit;
use App\UlizaTwg;
use App\County;
use DB;
use Illuminate\Http\Request;

class UlizaClinicalFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $forms = UlizaClinicalForm::with(['facility', 'twg'])
        ->when(true, function($query) use ($user){
            if($user->uliza_secretariat) return $query->where('twg_id', $user->twg_id);
            if($user->uliza_reviewer) return $query->where('reviewer_id', $user->id);
        })
        ->get();
        return view('uliza.tables.cases', compact('forms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $reasons = DB::table('uliza_reasons')->get();
        $regimens = DB::table('viralregimen')->get();
        return view('uliza.clinicalform', compact('reasons', 'regimens'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $form = new UlizaClinicalForm;
        $form->fill($request->except('clinical_visits'));
        $f = $form->view_facility;
        $county = County::find($f->county_id);
        $twg = UlizaTwg::where('default', 1)->first();
        $form->twg_id = $county->twg_id ?? $twg->id ?? null;
        $form->save();

        $visits = $request->input('clinical_visits');

        foreach ($visits as $key => $value) {            
            $visit = new UlizaClinicalVisit;
            $visit->fill(get_object_vars($value));
            $form->visit()->save($visit);
        }
        return response()->json(['status' => 'ok'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UlizaClinicalForm  $ulizaClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function show(UlizaClinicalForm $ulizaClinicalForm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UlizaClinicalForm  $ulizaClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function edit(UlizaClinicalForm $ulizaClinicalForm)
    {
        $reasons = DB::table('uliza_reasons')->get();
        $regimens = DB::table('viralregimen')->get();
        return view('uliza.clinicalform', compact('reasons', 'regimens', 'ulizaClinicalForm'));      
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UlizaClinicalForm  $ulizaClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UlizaClinicalForm $ulizaClinicalForm)
    {
        $form->fill($request->except('clinical_visits'));
        $form->save();
        return response()->json(['status' => 'ok'], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UlizaClinicalForm  $ulizaClinicalForm
     * @return \Illuminate\Http\Response
     */
    public function destroy(UlizaClinicalForm $ulizaClinicalForm)
    {
        //
    }
}
