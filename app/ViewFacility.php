<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewFacility extends Model
{
    //
    protected $table = "view_facilitys";

    public function facility_user()
    {
        return $this->hasOne('App\User', 'facility_id');
    }

}
