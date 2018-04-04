<?php

namespace App;

use App\BaseModel;

class Viralworksheet extends BaseModel
{
    // protected $dates = ['datecut', 'datereviewed', 'datereviewed2', 'dateuploaded', 'datecancelled', 'daterun', 'kitexpirydate',  'sampleprepexpirydate',  'bulklysisexpirydate',  'controlexpirydate',  'calibratorexpirydate',  'amplificationexpirydate', ];
    

    public function getDayCutAttribute()
    {
        return $this->date_modifier($this->datecut);
    }

    public function getDayReviewedAttribute()
    {
        return $this->date_modifier($this->datereviewed);
    }

    public function getDayReviewed2Attribute()
    {
        return $this->date_modifier($this->datereviewed2);
    }

    public function getDayUploadedAttribute()
    {
        return $this->date_modifier($this->dateuploaded);
    }

    public function getDayCancelledAttribute()
    {
        return $this->date_modifier($this->datecancelled);
    }

    public function getDayRunAttribute()
    {
        return $this->date_modifier($this->daterun);
    }

    public function getKitExpiryDayAttribute()
    {
        return $this->date_modifier($this->kitexpirydate);
    }

    public function getSamplePrepExpiryDayAttribute()
    {
        return $this->date_modifier($this->sampleprepexpirydate);
    }

    public function getBulklysisExpiryDayAttribute()
    {
        return $this->date_modifier($this->bulklysisexpirydate);
    }

    public function getControlExpiryDayAttribute()
    {
        return $this->date_modifier($this->controlexpirydate);
    }

    public function getCalibratorExpiryDayAttribute()
    {
        return $this->date_modifier($this->calibratorexpirydate);
    }

    public function getAmplificationExpiryDayAttribute()
    {
        return $this->date_modifier($this->amplificationexpirydate);
    }


    

    public function sample()
    {
    	return $this->hasMany('App\Viralsample', 'worksheet_id');
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

    public function reviewer2()
    {
        return $this->belongsTo('App\User', 'reviewedby2');
    }
}
