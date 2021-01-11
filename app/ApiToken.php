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
    	$t->fill(['organisation' => 'Ampath POC', 'token' => env('API_KEY')]);
    	$t->save();
    }
}
