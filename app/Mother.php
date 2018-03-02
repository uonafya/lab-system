<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mother extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionEnabled = true;
    protected $revisionCleanup = true; 
    protected $historyLimit = 500; 
    
    protected $guarded = [];
    protected $dates = ['datesynched'];


    public function patient()
    {
    	return $this->hasMany('App\Patient');
    }
}
