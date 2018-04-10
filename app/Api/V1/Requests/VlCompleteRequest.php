<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class VlCompleteRequest extends FormRequest
{
    public function rules()
    {
        $base = Config::get('boilerplate.sample_base'); 
        $vl = Config::get('boilerplate.vl'); 
        $complete = Config::get('boilerplate.complete_result'); 

        $semi = array_merge($base, $vl);

        return array_merge($semi, $complete);
    }

    public function authorize()
    {
    	return true;        
    }
}
