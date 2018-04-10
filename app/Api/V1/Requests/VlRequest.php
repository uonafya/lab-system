<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class VlRequest extends FormRequest
{
    public function rules()
    {
        $base = Config::get('boilerplate.sample_base'); 
        $vl = Config::get('boilerplate.vl'); 

        return array_merge($base, $vl);
    }

    public function authorize()
    {
    	return true;        
    }
}
