<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;

class BlankRequest extends FormRequest
{
    public function rules()
    {
        return [];
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
