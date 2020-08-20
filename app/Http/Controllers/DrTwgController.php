<?php

namespace App\Http\Controllers;

use App\DrTwg;
use App\County;
use Illuminate\Http\Request;

class DrTwgController extends Controller
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
        $counties = County::orderBy('name', 'asc')->get();
        return view('uliza.forms.twg', compact('counties'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $drTwg = new DrTwg;
        $drTwg->fill($request->except(['counties']));
        $drTwg->save();

        $counties = $request->input('counties');
        if($counties) County::where(['twg_id' => $drTwg->id])->update(['twg_id' => null]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrTwg  $drTwg
     * @return \Illuminate\Http\Response
     */
    public function show(DrTwg $drTwg)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrTwg  $drTwg
     * @return \Illuminate\Http\Response
     */
    public function edit(DrTwg $drTwg)
    {
        $counties = County::orderBy('name', 'asc')->get();
        return view('uliza.forms.twg', compact('counties', 'drTwg'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrTwg  $drTwg
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrTwg $drTwg)
    {
        $drTwg->fill($request->except(['counties']));
        $drTwg->save();

        County::where(['twg_id' => $drTwg->id])->update(['twg_id' => null]);
        $counties = $request->input('counties');
        if($counties) County::where(['twg_id' => $drTwg->id])->update(['twg_id' => null]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrTwg  $drTwg
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrTwg $drTwg)
    {
        //
    }
}
