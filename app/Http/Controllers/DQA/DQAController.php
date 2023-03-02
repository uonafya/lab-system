<?php

namespace App\Http\Controllers\DQA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DQAController extends Controller
{
    //
    
    function index()
    {
        return view('components.DQA.index');
    }
    
}
