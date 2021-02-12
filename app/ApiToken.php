<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiToken extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public static function createToken(){
    	$t = new ApiToken;
    	$t->fill(['organisation' => 'MHealth', 'token' => 'fhMpadW3q6NM5u7x5H91s3yf1PlqksyPJd1HZdE0']);
    	$t->save();
    }
}
