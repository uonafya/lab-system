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
	$this->info(\App\Common::input_complete_batches(\App\Batch::class));
})->describe('Mark eid batches as input completed.');

Artisan::command('input_complete:vl', function(){
	$this->info(\App\Common::input_complete_batches(\App\Viralbatch::class));
})->describe('Mark vl batches as input completed.');


Artisan::command('synch:eid-patients', function(){
	$this->info(\App\Synch::synch_eid_patients());
})->describe('Synch eid patients to the national database.');

Artisan::command('synch:vl-patients', function(){
	$this->info(\App\Synch::synch_vl_patients());
})->describe('Synch vl patients to the national database.');


Artisan::command('synch:eid-batches', function(){
	$this->info(\App\Synch::synch_batches('eid'));
})->describe('Synch eid batches to the national database.');

Artisan::command('synch:vl-batches', function(){
	$this->info(\App\Synch::synch_batches('vl'));
})->describe('Synch vl batches to the national database.');


Artisan::command('synch:eid-worksheets', function(){
	$this->info(\App\Synch::synch_worksheets('eid'));
})->describe('Synch eid worksheets to the national database.');

Artisan::command('synch:vl-worksheets', function(){
	$this->info(\App\Synch::synch_worksheets('vl'));
})->describe('Synch vl worksheets to the national database.');


Artisan::command('synch:eid-deletes', function(){
	$this->info(\App\Synch::synch_deletes('eid'));
})->describe('Synch eid deletes to the national database.');

Artisan::command('synch:vl-deletes', function(){
	$this->info(\App\Synch::synch_deletes('vl'));
})->describe('Synch vl deletes to the national database.');





Artisan::command('copy:eid', function(){
	$this->info(\App\Copier::copy_eid());

})->describe('Copy eid data from old database to new database.');

Artisan::command('copy:vl', function(){
	$this->info(\App\Copier::copy_vl());
})->describe('Copy vl data from old database to new database.');

Artisan::command('copy:worksheet', function(){
	$this->info(\App\Copier::copy_worksheet());
})->describe('Copy worksheet data from old database to new database.');




Artisan::command('email:test', function(){
	$this->info(\App\Resolver::test_email());
})->describe('Send test email.');




