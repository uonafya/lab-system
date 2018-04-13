<?php

namespace App;

use App\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class Batch extends BaseModel
{

    // protected $dates = ['datereceived', 'datedispatchedfromfacility', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'datemodified', 'dateapproved', 'dateapproved2', 'dateindividualresultprinted', 'datebatchprinted', 'datesynched'];

    // protected $withCount = ['sample'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('siteentry', function(Builder $builder){
            $builder->where('site_entry', '!=', 2);
        });
    }

    public function tat()
    {
        if(!$this->datereceived) return '';

        $max;
        if($this->batch_complete == 1){
            $max = $this->datedispatched;
        }
        else{
            $max = date('Y-m-d');
        }
        return \App\Misc::working_days($this->datereceived, $max);
    }

    public function full_batch()
    {
        $this->input_complete = 1;
        $this->batch_full = 1;
        $this->save();
    }

    public function premature()
    {
        $this->input_complete = 1;
        $this->save();
    }

    public function outdated()
    {
        $now = \Carbon\Carbon::now();

        if($now->diffInMonths($this->created_at) > 6) return true;
        return false;
    }


	public function sample()
    {
        return $this->hasMany('App\Sample');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }


    public function scopeExisting($query, $facility, $datereceived, $lab)
    {
        return $query->where(['facility_id' => $facility, 'datereceived' => $datereceived, 'lab_id' => $lab]);
    }

    public function scopeEditing($query)
    {
        return $query->where(['user_id' => auth()->user()->id, 'input_complete' => 0]);
    }
    
}
