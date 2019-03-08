<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllocationDetailsBreakdown extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    public function breakdown()
    {
        return $this->morphTo();
    }
}
