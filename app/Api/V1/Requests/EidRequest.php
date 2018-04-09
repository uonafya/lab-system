<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;

class EidRequest extends FormRequest
{
    public function rules()
    {
        return [
        	'datecollected' => 'date_format:Y-m-d'
        ];
    }

    public function authorize()
    {
    	return true;        
    }
}
