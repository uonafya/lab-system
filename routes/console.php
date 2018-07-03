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

Artisan::command('generate:dr-list', function(){
    $str = \App\MiscViral::generate_dr_list();
    $this->info($str);
})->describe('Generate a list of potential dr patients.');

Artisan::command('compute:eid-tat', function(){
    $my = new \App\Misc;
    $str = $my->compute_tat(\App\SampleView::class, \App\Sample::class);
    $str .= "Completed eid tat computation at " . date('d/m/Y h:i:s a', time()). "\n";
    $this->info($str);
})->describe('Compute Eid Tat.');

Artisan::command('compute:vl-tat', function(){
    $my = new \App\MiscViral;
    $str = $my->compute_tat(\App\ViralsampleView::class, \App\Viralsample::class);
    $str .= "Completed vl tat computation at " . date('d/m/Y h:i:s a', time()). "\n";
    $this->info($str);
})->describe('Compute Vl Tat.');

Artisan::command('compute:vl-stat {sample_id}', function($sample_id){
    $my = new \App\MiscViral;
    $str = $my->compute_tat_sample(\App\ViralsampleView::class, \App\Viralsample::class, $sample_id);
    $str .= "Completed vl at " . date('d/m/Y h:i:s a', time()). "\n";
    $this->info($str);
})->describe('Compute Vl Tat.');


Artisan::command('input_complete:eid', function(){
	$str = \App\Common::input_complete_batches(\App\Batch::class);
    $this->info($str);
})->describe('Mark eid batches as input completed.');

Artisan::command('input_complete:vl', function(){
	$str = \App\Common::input_complete_batches(\App\Viralbatch::class);
    $this->info($str);
})->describe('Mark vl batches as input completed.');


Artisan::command('synch:eid-patients', function(){
	$str = \App\Synch::synch_eid_patients();
    $this->info($str);
})->describe('Synch eid patients to the national database.');

Artisan::command('synch:vl-patients', function(){
	$str = \App\Synch::synch_vl_patients();
    $this->info($str);
})->describe('Synch vl patients to the national database.');


Artisan::command('synch:eid-batches', function(){
	$str = \App\Synch::synch_batches('eid');
    $this->info($str);
})->describe('Synch eid batches to the national database.');

Artisan::command('synch:vl-batches', function(){
	$str = \App\Synch::synch_batches('vl');
    $this->info($str);
})->describe('Synch vl batches to the national database.');


Artisan::command('synch:eid-worksheets', function(){
	$str = \App\Synch::synch_worksheets('eid');
    $this->info($str);
})->describe('Synch eid worksheets to the national database.');

Artisan::command('synch:vl-worksheets', function(){
	$str = \App\Synch::synch_worksheets('vl');
    $this->info($str);
})->describe('Synch vl worksheets to the national database.');


Artisan::command('synch:eid-deletes', function(){
	$str = \App\Synch::synch_deletes('eid');
    $this->info($str);
})->describe('Synch eid deletes to the national database.');

Artisan::command('synch:vl-deletes', function(){
	$str = \App\Synch::synch_deletes('vl');
    $this->info($str);
})->describe('Synch vl deletes to the national database.');





Artisan::command('copy:eid', function(){
	$str = \App\Copier::copy_eid();
    $this->info($str);
})->describe('Copy eid data from old database to new database.');

Artisan::command('copy:vl', function(){
	$str = \App\Copier::copy_vl();
    $this->info($str);
})->describe('Copy vl data from old database to new database.');

Artisan::command('copy:worksheet', function(){
	$str = \App\Copier::copy_worksheet();
    $this->info($str);
})->describe('Copy worksheet data from old database to new database.');

Artisan::command('copy:deliveries', function(){
    $str = \App\Copier::copy_deliveries();
    $this->info($str);
})->describe('Copy deliveries data from old database to new database.');



Artisan::command('match:eid-patients', function(){
    $str = \App\Synch::match_eid_patients();
    $this->info($str);
})->describe('Match eid patients with records on the national database.');

Artisan::command('match:vl-patients', function(){
    $str = \App\Synch::match_vl_patients();
    $this->info($str);
})->describe('Match vl patients with records on the national database.');


Artisan::command('match:eid-batches', function(){
    $str = \App\Synch::match_batches('eid');
    $this->info($str);
})->describe('Match eid batches with records on the national database.');

Artisan::command('match:vl-batches', function(){
    $str = \App\Synch::match_batches('vl');
    $this->info($str);
})->describe('Match vl batches with records on the national database.');




Artisan::command('test:email', function(){
	$str = \App\Common::test_email();
    $this->info($str);
})->describe('Send test email.');

Artisan::command('test:connection', function(){
    $str = \App\Synch::test_connection();
    $this->info($str);
})->describe('Check connection to lab-2.test.nascop.org.');




