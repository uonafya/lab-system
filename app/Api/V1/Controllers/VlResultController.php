<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Controllers\BaseController;
use App\Api\V1\Requests\CovidRequest;

use App\MiscViral;
use App\ViralsampleView;
use App\Viralbatch;
use App\Viralpatient;
use App\Viralsample;

class VlResultController extends BaseController
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

    public function result(CovidRequest $request)
    {
        $sample_id = $request->input('Sample Id');
        $datetested = $request->input('Test Datetime');
        $result = $request->input('Result');
        $units = $request->input('Units');
        $error = $request->input('Test Error');

        $result_array = MiscViral::sample_result($result, $error, $units);
        $sample = Viralsample::find($sample_id);
        if(!$sample) continue;
        $sample->fill($result_array);
        $sample->datetested = date('Y-m-d', strtotime($datetested));
        if($sample->datetested == '1970-01-01') $sample->datetested = date('Y-m-d');
        $sample->save();
    }

}
