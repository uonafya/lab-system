<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionEnabled = true;
    protected $revisionCleanup = true; 
    protected $historyLimit = 500; 
    protected $guarded = [];

    protected $dates = ['datereceived', 'datedispatchedfromfacility', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'datemodified', 'dateapproved', 'dateapproved2', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted', 'datesynched'];

	public function sample()
    {
        return $this->hasMany('App\Sample');
    }

    // public function scopeGreatest($query)
    // {
    //     return $query->whereRaw('id', );
    // }
}
