<?php

namespace App\Http\Controllers;

use App\Abbotprocurement;
use App\Abbotdeliveries;
use Illuminate\Http\Request;

class AbbottProcurementController extends Controller
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function recomputeending($id) {
        $procurement = Abbotprocurement::find($id);
        $qualkit = $procurement->endingqualkit;
        if ($procurement->testtype == 1){
            $testtype = 'EID';
        } else if ($procurement->testtype == 2) {
            $testtype = 'VL';
        }
        foreach ($this->abbottKits as $key => $kits) {
            $column = 'ending'.strtolower($kits['alias']);
            $procurement->$column = $qualkit * $kits['factor'][$testtype];
        }
        $procurement->save();
        $string = "Update of abbot ending balances failed";
        if ($procurement)
            $string = "Update of abbot ending balances successful";

        print($string);

        // $previousyear = $year = $procurement->year;
        // $month = $procurement->month;
        // $testtype = $procurement->testtype;
        // $previousmonth = $month - 1;
        // if ($month == 1) { $previousyear -= 1; $previousmonth = 12; }
        
        // $prevprocurements = Abbotprocurement::where('month', '=', $previousmonth)->where('year', '=', $previousyear)
        //                         ->where('testtype', '=', $testtype)->first();
        
        // $deliveries = Abbotdeliveries::whereYear('datereceived', $year)->whereMonth('datereceived', $month)
        //                         ->where('testtype', '=', $testtype)->get();

        // $beginingqualkit = $prevprocurements->endingqualkit;
        // dd($beginingqualkit);
        // quotemeta(str)
    }
}
