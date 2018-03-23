<?php

namespace App\Http\Controllers;

use App\Viralpatient;
use Illuminate\Http\Request;

class ViralpatientController extends Controller
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
     * @param  \App\Viralpatient  $viralpatient
     * @return \Illuminate\Http\Response
     */
    public function show(Viralpatient $viralpatient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Viralpatient  $viralpatient
     * @return \Illuminate\Http\Response
     */
    public function edit(Viralpatient $viralpatient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Viralpatient  $viralpatient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Viralpatient $viralpatient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Viralpatient  $viralpatient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Viralpatient $viralpatient)
    {
        //
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $facility_user = false;

        if($user->user_type_id == 5) $facility_user=true;
        $string = "(facility_id='{$user->facility_id}')";

        $search = $request->input('search');
        $patients = Viralpatient::select('id', 'patient')
            ->whereRaw("patient like '" . $search . "%'")
            ->when($facility_user, function($query) use ($string){
                return $query->whereRaw($string);
            })
            ->paginate(10);
        return $patients;
    }
}
