<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    //

    public function index()
    {
    	return view('shared.reports')->with('pageTitle', 'Lab Reports');
    }

    public function generate(Request $request)
    {
    	dd($request);
    }
}
