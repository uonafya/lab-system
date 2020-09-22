<?php

namespace App\Api\V1\Requests;

use App\Api\V1\Requests\BaseRequest;
use App\Rules\BeforeOrEqual;

class NodeRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    
    public function rules()
    {
        return [];
    }
}
