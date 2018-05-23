<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('input_complete:eid', function(){
	\App\Common::input_complete_batches(\App\Batch::class);
})->describe();

Artisan::command('input_complete:vl', function(){
	\App\Common::input_complete_batches(\App\Viralbatch::class);
})->describe();
