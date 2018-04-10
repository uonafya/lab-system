<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class EidCompleteRequest extends FormRequest
{
    public function rules()
    {
        $base = Config::get('boilerplate.sample_base'); 
        $eid = Config::get('boilerplate.eid'); 
        $complete = Config::get('boilerplate.complete_result'); 

        $semi = array_merge($base, $eid);

        return array_merge($semi, $complete);
    }

    public function authorize()
    {
    	return true;        
    }
}
