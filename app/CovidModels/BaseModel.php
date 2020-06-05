<?php

namespace App\CovidModels;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

	protected $connection = 'covid';
    protected $guarded = ['id', 'created_at', 'updated_at', '_token', '_method'];

    public function get_prop_name($coll, $attr, $attr2='name')
    {
        if(!$this->$attr) return '';
        foreach ($coll as $value) {
            if($value->id == $this->$attr) return $value->$attr2;
        }
        return '';
    }
}
