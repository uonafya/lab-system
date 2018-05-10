<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;
use App\Rules\BeforeOrEqual;

class VlCompleteRequest extends FormRequest
{
    public function rules()
    {
        $base = Config::get('boilerplate.sample_base'); 
        $vl = Config::get('boilerplate.vl'); 
        $complete = Config::get('boilerplate.complete_result'); 

        $semi = array_merge($base, $vl);
        $val = array_merge($semi, $complete);
        $val['dob'] = array_merge($val['dob'], [new BeforeOrEqual($this->input('datecollected'), 'datecollected')]);
        $val['datereceived'] = array_merge($val['datereceived'], [new BeforeOrEqual($this->input('datetested'), 'datetested')]);
        $val['datetested'] = array_merge($val['datetested'], [new BeforeOrEqual($this->input('datedispatched'), 'datedispatched')]);
        return $val;
    }

    public function authorize()
    {
    	return true;        
    }
}
