<?php

namespace App;

use Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    // use \Venturecraft\Revisionable\RevisionableTrait;
    // protected $revisionEnabled = true;
    // protected $revisionCleanup = true; 
    // protected $historyLimit = 500; 
    
    use Notifiable;
    use SoftDeletes;


    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that are automatically mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Automatically creates hash for the user password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    

    /**
     * Get the user's full name
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->surname} {$this->oname}";
    }

    public function getIsLabUserAttribute()
    {
        if(in_array($this->user_type_id, [0, 1, 4])) return true;
        return false;
    }

    public function getIsAdminAttribute()
    {
        if(in_array($this->user_type_id, [0, 2])) return true;
        return false;
    }

    public function getIsFacilityAttribute()
    {
        if($this->user_type_id == 5) return true;
        return false;
    }

    public function getIsPartnerAttribute()
    {
        if($this->user_type_id == 10) return true;
        return false;
    }

    public function getIsNotLabUserAttribute()
    {
        if(in_array($this->user_type_id, [5,10])) return true;
        return false;
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function set_last_access()
    {
        $this->last_access = date('Y-m-d H:i:s');
        $this->save();
    }

    public function is_lab_user()
    {
        if(in_array($this->user_type_id, [0, 1, 4])) return true;
        return false;
    }

    public function is_admin()
    {
        if($this->user_type_id == 0 || $this->user_type_id == 2) return true;
        return false;
    }

    public function samples_entered($testtype, $year, $month=null) {
        $id = $this->id;
        if ($testtype == 'EID') {
            $model = SampleView::selectRaw("COUNT(*) as samples");
        } else if ($testtype == 'VL') {
            $model = ViralsampleView::selectRaw("COUNT(*) as samples");
        } else {
            return null;
        }
        $model = $model->whereYear('created_at', $year)
                    ->when($month, function($query) use ($month) {
                        return $query->whereMonth('datereceived', $month);
                    })->where('user_id', '=',$id);

        return number_format($model->first()->samples);
    }

    public function sitesamplesapproved($testtype, $year, $month=null) {
        $id = $this->id;
        if ($testtype == 'EID') {
            $model = SampleView::selectRaw("COUNT(*) as samples");
        } else if ($testtype == 'VL') {
            $model = ViralsampleView::selectRaw("COUNT(*) as samples");
        } else {
            return null;
        }
        $model = $model->whereYear('datereceived', $year)
                    ->when($month, function($query) use ($month) {
                        return $query->whereMonth('datereceived', $month);
                    })->where('received_by', '=',$id)->where('site_entry', '=', 1);

        return number_format($model->first()->samples);
    }

    public function uploaded($from_date, $to_date = null) {
        $user = $this->id;
        $eid_worksheets = \App\Worksheet::selectRaw("COUNT(*) AS worksheets")
                            ->where('uploadedby', $user)
                            ->when(true, function($query) use ($from_date, $to_date){
                                if (isset($to_date)) {
                                    $query->whereBetween('dateuploaded', [$from_date, $to_date]);
                                } else {
                                    $query->where('dateuploaded', $from_date);
                                }                                
                            })->first()->worksheets;

        $vl_worksheets = \App\Viralworksheet::selectRaw("COUNT(*) AS worksheets")
                            ->where('uploadedby', $user)
                            ->when(true, function($query) use ($from_date, $to_date){
                                if (isset($to_date)) {
                                    $query->whereBetween('dateuploaded', [$from_date, $to_date]);
                                } else {
                                    $query->where('dateuploaded', $from_date);
                                }                                
                            })->first()->worksheets;
        return (object)['eid' => $eid_worksheets, 'vl' => $vl_worksheets];
    }

    public function reviewed($from_date, $to_date = null) {
        $user = $this->id;
        $eid_worksheets = \App\Worksheet::selectRaw("COUNT(*) AS worksheets")
                            ->where('reviewedby', $user)->orWhere('reviewedby2', $user)
                            ->when(true, function($query) use ($from_date, $to_date){
                                if (isset($to_date)) {
                                    $query->whereBetween('dateuploaded', [$from_date, $to_date]);
                                } else {
                                    $query->where('dateuploaded', $from_date);
                                }                                
                            })->first()->worksheets;

        $vl_worksheets = \App\Viralworksheet::selectRaw("COUNT(*) AS worksheets")
                            ->where('reviewedby', $user)->orWhere('reviewedby2', $user)
                            ->when(true, function($query) use ($from_date, $to_date){
                                if (isset($to_date)) {
                                    $query->whereBetween('datereviewed', [$from_date, $to_date])->orWhereBetween('datereviewed', [$from_date, $to_date]);
                                } else {
                                    $query->where('datereviewed', $from_date)->orWhere('datereviewed', $from_date);
                                }                                
                            })->first()->worksheets;
        return (object)['eid' => $eid_worksheets, 'vl' => $vl_worksheets];
    }

    public function samples_recevied($from_date, $to_date = null){
        $user = $this->id;
        // $eid_sampels = \App\Sample::
    }

}
