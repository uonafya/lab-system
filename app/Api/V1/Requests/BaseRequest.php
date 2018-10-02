<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;

class BaseRequest extends FormRequest
{

    public function authorize()
    {
    	$apikey = $this->headers->get('apikey');
        $actual_key = env('API_KEY');
    	if($apikey != $actual_key || !$actual_key) return false;
    	else{
    		return true;
    	}    
    }

    public function messages()
    {
        return [
            'before_or_equal' => 'The :attribute field must be before or equal to today.'
        ];
    }
}
