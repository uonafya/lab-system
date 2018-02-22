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
    protected $dates = ['datecut', 'datereviewed', 'dateuploaded', 'datecancelled', 'daterun', 'dateapproved', 'dateapproved2', 'kitexpirydate',  'sampleprepexpirydate',  'bulklysisexpirydate',  'controlexpirydate',  'calibratorexpirydate',  'amplificationexpirydate', ];

    protected $dateFormat = 'Y-m-d';

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

    public function canceller()
    {
        return $this->belongsTo('App\User', 'cancelledby');
    }

    public function reviewer()
    {
        return $this->belongsTo('App\User', 'reviewedby');
    }

    public function approver()
    {
        return $this->belongsTo('App\User', 'approvedby');
    }

}
