<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionEnabled = true;
    protected $revisionCleanup = true; 
    protected $historyLimit = 500; 
    protected $guarded = [];
    

    protected function date_modifier($value)
    {
    	if($value) return date('d-M-Y', strtotime($value));

    	return $value;
    }
}
