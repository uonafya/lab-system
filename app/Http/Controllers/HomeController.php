<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Synch;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home')->with('pageTitle', 'Home');
    }

    public function test()
    {
        // dd(Synch::synch_eid_patients());
        // echo Synch::synch_eid_patients();
        echo Synch::synch_eid_batches();
    }
}
