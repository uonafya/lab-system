<?php

namespace App;

use App\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class Batch extends BaseModel
{

    // protected $dates = ['datereceived', 'datedispatchedfromfacility', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'datemodified', 'dateapproved', 'dateapproved2', 'dateindividualresultprinted', 'datebatchprinted', 'datesynched'];

    // protected $withCount = ['sample'];

    public $keyType = 'string';
    // public $incrementing = 'false';

    /*protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('siteentry', function(Builder $builder){
            $builder->where('site_entry', '!=', 2);
        });
    }*/

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
        $this->pre_update();
    }

    public function premature()
    {
        $this->input_complete = 1;
        $this->pre_update();
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

    public function view_facility()
    {
        return $this->belongsTo('App\ViewFacility', 'facility_id');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function facility_lab()
    {
        return $this->belongsTo('App\Facility', 'lab_id');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function setDatedispatchedfromfacilityAttribute($value)
    {
        if($value = '0000-00-00') $this->attributes['datedispatchedfromfacility'] = null;
        else{
            $this->attributes['datedispatchedfromfacility'] = $value;
        }
    }

    public function scopeExisting($query, $facility, $datereceived, $lab)
    {
        if(!$datereceived) return $query->where(['facility_id' => $facility, 'lab_id' => $lab, 'batch_full' => 0])->whereNull('datereceived');
        return $query->where(['facility_id' => $facility, 'datereceived' => $datereceived, 'lab_id' => $lab, 'batch_full' => 0]);
    }

    public function scopeEligible($query, $facility, $datereceived)
    {
        $user = auth()->user();
        $today = date('Y-m-d');
        if(!$datereceived){
            return $query->where(['facility_id' => $facility, 'user_id' => $user->id, 'batch_full' => 0])
                    ->whereDate('created_at', $today)->whereNull('datereceived');
        }
        return $query->where(['facility_id' => $facility, 'datereceived' => $datereceived, 'user_id' => $user->id, 'batch_full' => 0]);
    }

    public function scopeEditing($query)
    {
        return $query->where(['user_id' => auth()->user()->id, 'input_complete' => 0]);
    }

    /**
     * Get the batch's delete button
     *
     * @return string
     */
    public function getDeleteButtonAttribute()
    {
        // $min_time = strtotime("-1 month");
        $min_time = strtotime("-10 days");
        $created_at = strtotime($this->created_at);
        if($this->site_entry == 1 && !$this->datereceived && $created_at < $min_time && $this->batch_complete == 0){
            return "<form method='post' action='" . url('batch/' . $this->id) . "' onSubmit=\"return confirm('Are you sure you want to delete the following batch?');\">
                    " . csrf_field() . " 
                    <input name='_method' type='hidden' value='DELETE'>
                    <button type='submit' class='btn btn-xs btn-primary'>Delete</button>
                </form>
             ";
        }
        else{
            return null;
        }
    }
    
}
