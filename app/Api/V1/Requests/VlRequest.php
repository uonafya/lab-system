<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;
use App\Rules\BeforeOrEqual;

class VlRequest extends FormRequest
{
    public function rules()
    {
        $base = Config::get('boilerplate.sample_base'); 
        $vl = Config::get('boilerplate.vl'); 

        $val = array_merge($base, $vl);
        $val['dob'] = array_merge($val['dob'], [new BeforeOrEqual($this->input('datecollected'), 'datecollected')]);
        return $val;
    }

    public function authorize()
    {
    	return true;        
    }
}
