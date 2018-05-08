<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;

class BlankRequest extends FormRequest
{
    public function rules()
    {
        return [
            'test' => 'required|integer|max:2',
            'start_date' => 'date_format:Y-m-d',
            'end_date' => 'date_format:Y-m-d|required_with:start_date',
            'date_dispatched_start' => 'date_format:Y-m-d',
            'date_dispatched_end' => 'date_format:Y-m-d|required_with:date_dispatched_start',
            
        ];
    }

    public function authorize()
    {
    	$apikey = $this->headers->get('apikey');
    	// if($apikey == 'u57x5e3aw'){
    	// 	return true;
    	// }
    	// else{
    		// return false;
    	// }
    	return true;        
    }
}
