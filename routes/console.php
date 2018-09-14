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


Artisan::command('dispatch:results {type}', function($type){
    $str = \App\Common::dispatch_results($type);
    $this->info($str);
})->describe('Send emails for dispatched batches.');


Artisan::command('input-complete {type}', function($type){
    $str = \App\Common::input_complete_batches($type);
    $this->info($str);
})->describe('Mark batches as input completed.');



Artisan::command('lablog {type}', function($type){
	$str = \App\Synch::labactivity($type);
    $this->info($str);
})->describe('Send lablog data to national.');

// Artisan::command('synch:vl-patients', function(){
// 	$str = \App\Synch::synch_vl_patients();
//     $this->info($str);
// })->describe('Synch vl patients to the national database.');


Artisan::command('send:sms {type}', function($type){
    if($type == 'eid') $str = \App\Misc::patient_sms();
    else { $str = \App\MiscViral::patient_sms(); }    
    $this->info($str);
})->describe('Send result sms.');


Artisan::command('send:weekly', function(){
    $str = \App\Misc::patient_sms();
    $this->info($str);
})->describe('Send out weekly sms alert.');



Artisan::command('synch:patients {type}', function($type){
    if($type == 'eid') $str = \App\Synch::synch_eid_patients();
    else { $str = \App\Synch::synch_vl_patients(); }    
    $this->info($str);
})->describe('Synch patients to the national database.');


Artisan::command('synch:batches {type}', function($type){
	$str = \App\Synch::synch_batches($type);
    $this->info($str);
})->describe('Synch batches to the national database.');


Artisan::command('synch:worksheets {type}', function($type){
	$str = \App\Synch::synch_worksheets($type);
    $this->info($str);
})->describe('Synch worksheets to the national database.');


Artisan::command('synch:updates {type}', function($type){
    $str = \App\Synch::synch_updates($type);
    $this->info($str);
})->describe('Synch updates to the national database.');


Artisan::command('synch:deletes {type}', function($type){
	$str = \App\Synch::synch_deletes($type);
    $this->info($str);
})->describe('Synch deletes to the national database.');





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

Artisan::command('copy:facility-contacts', function(){
    $str = \App\Copier::copy_facility_contacts();
    $this->info($str);
})->describe('Copy facility contacts from old database to new database.');



Artisan::command('match:eid-patients', function(){
    $str = \App\Synch::match_eid_patients();
    $this->info($str);
})->describe('Copy facility contacts from old database to new database.');



Artisan::command('match:patients {type}', function($type){
    if($type == 'eid') $str = \App\Synch::match_eid_patients();
    else { $str = \App\Synch::match_vl_patients(); }    
    $this->info($str);
})->describe('Match patients with records on the national database.');


Artisan::command('match:batches {type}', function($type){
    $str = \App\Synch::match_batches($type);
    $this->info($str);
})->describe('Match batches with records on the national database.');



Artisan::command('email:urgent', function(){
    $str = \App\Common::send_communication();
    $this->info($str);
})->describe('Send test email.');

Artisan::command('test:email', function(){
	$str = \App\Common::test_email();
    $this->info($str);
})->describe('Send test email.');

Artisan::command('test:connection', function(){
    $str = \App\Synch::test_connection();
    $this->info($str);
})->describe('Check connection to lab-2.test.nascop.org.');




