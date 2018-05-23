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
})->describe('Mark eid batches as input completed.');

Artisan::command('input_complete:vl', function(){
	\App\Common::input_complete_batches(\App\Viralbatch::class);
})->describe('Mark vl batches as input completed.');


Artisan::command('synch:eid-patients', function(){
	\App\Synch::synch_eid_patients();
})->describe('Synch eid patients to the national database.');

Artisan::command('synch:vl-patients', function(){
	\App\Synch::synch_vl_patients();
})->describe('Synch vl patients to the national database.');


Artisan::command('synch:eid-batches', function(){
	\App\Synch::synch_batches('eid');
})->describe('Synch eid batches to the national database.');

Artisan::command('synch:vl-batches', function(){
	\App\Synch::synch_batches('vl');
})->describe('Synch vl batches to the national database.');


Artisan::command('synch:eid-worksheets', function(){
	\App\Synch::synch_worksheets('eid');
})->describe('Synch eid worksheets to the national database.');

Artisan::command('synch:vl-worksheets', function(){
	\App\Synch::synch_worksheets('vl');
})->describe('Synch vl worksheets to the national database.');


Artisan::command('synch:eid-deletes', function(){
	\App\Synch::synch_deletes('eid');
})->describe('Synch eid deletes to the national database.');

Artisan::command('synch:vl-deletes', function(){
	\App\Synch::synch_deletes('vl');
})->describe('Synch vl deletes to the national database.');



