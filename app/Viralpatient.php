<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Viralpatient extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionEnabled = true;
    protected $revisionCleanup = true; 
    protected $historyLimit = 500; 

    protected $guarded = [];
    // protected $dates = ['datesynched', 'dob'];

    public function sample()
    {
    	return $this->hasMany('App\Viralsample', 'patient_id');
    }
}
