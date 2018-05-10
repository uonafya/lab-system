<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;
use App\Rules\BeforeOrEqual;

class EidRequest extends FormRequest
{
    public function rules()
    {
        $base = Config::get('boilerplate.sample_base'); 
        $eid = Config::get('boilerplate.eid'); 

        $val = array_merge($base, $eid);
        $val['dob'] = array_merge($val['dob'], [new BeforeOrEqual($this->input('datecollected'), 'datecollected')]);
        return $val;
    }

    public function authorize()
    {
    	return true;        
    }
}
