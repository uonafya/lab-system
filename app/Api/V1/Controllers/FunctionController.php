<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\EidRequest;
use DB;

use App\Lookup;

class FunctionController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('jwt:auth', []);
    }

    public function eid(EidRequest $request)
    {
        $code = $request->input('mflCode');
        $facility = Lookup::facility_mfl($code);

    }




}
