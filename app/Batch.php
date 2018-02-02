<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $guarded = [];

    protected $dates = ['datereceived', 'datedispatchedfromfacility', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'datemodified', 'dateapproved', 'dateapproved2', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted', 'datesynched'];

    protected $dateFormat = 'Y-m-d';

	public function sample()
    {
        return $this->hasMany('App\Sample');
    }

    // public function scopeGreatest($query)
    // {
    //     return $query->whereRaw('id', );
    // }
}
