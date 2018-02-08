<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Worksheet extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionEnabled = true;
    protected $revisionCleanup = true; 
    protected $historyLimit = 500; 

    protected $guarded = [];

    public function sample()
    {
    	return $this->hasMany('App\Sample');
    }

    public function runner()
    {
    	return $this->belongsTo('App\User', 'runby');
    }

    public function creator()
    {
    	return $this->belongsTo('App\User', 'createdby');
    }

}
