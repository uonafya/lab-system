<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('reset_password/{token}', ['as' => 'password.reset', function($token)
{
    // implement your reset password route here!
}]);

Route::get('/', function () {
    // return view('auth.login');
    return view('emergency');
});

Route::get('/addsample', function () {
	return view('addsample');
});

Route::get('batch/dispatch/', 'BatchController@batch_dispatch');
Route::post('batch/complete_dispatch/', 'BatchController@confirm_dispatch');

Route::get('/checkboxes', function () {
	return view('checkbox');
});

Route::get('datatables', function () {
	return view('datatables');
});

Route::get('facility/served', 'FacilityController@served');
Route::resource('facility', 'FacilityController');
// Route::get('/home', function () {
// 	return view('home');
// });
Route::get('/home', 'HomeController@index');

Route::get('sample/new_patient/{patient}/{facility_id}', 'SampleController@new_patient');
Route::get('sample/release/{sample}', 'SampleController@release_redraw');
Route::resource('sample', 'SampleController');

Route::get('viralsample/new_patient/{patient}/{facility_id}', 'ViralsampleController@new_patient');
Route::resource('viralsample', 'ViralsampleController');
Route::get('viralbatch/dispatch/', 'ViralbatchController@batch_dispatch');
Route::post('viralbatch/complete_dispatch/', 'ViralbatchController@confirm_dispatch');

Route::get('worksheet/create_abbot', 'WorksheetController@abbot')->name('worksheet.create_abbot');
Route::get('worksheet/print/{worksheet}', 'WorksheetController@print')->name('worksheet.print');
Route::get('worksheet/cancel/{worksheet}', 'WorksheetController@cancel')->name('worksheet.cancel');
Route::get('worksheet/upload/{worksheet}', 'WorksheetController@upload')->name('worksheet.upload');
Route::put('worksheet/upload/{worksheet}', 'WorksheetController@save_results')->name('worksheet.save_results');
Route::get('worksheet/approve/{worksheet}', 'WorksheetController@approve_results')->name('worksheet.approve_results');
Route::put('worksheet/approve/{worksheet}', 'WorksheetController@approve')->name('worksheet.approve');
Route::resource('worksheet', 'WorksheetController');

Route::get('viralsample/new_patient/{patient}/{facility_id}', 'ViralsampleController@new_patient');
Route::resource('viralsample', 'ViralsampleController');

Route::get('viralbatch/dispatch/', 'ViralbatchController@batch_dispatch');
Route::post('viralbatch/complete_dispatch/', 'ViralbatchController@confirm_dispatch');


Route::prefix('viralworksheet')->name('viralworksheet.')->group(function () {

	Route::get('create_abbot', 'ViralworksheetController@abbot')->name('create_abbot');
	Route::get('print/{worksheet}', 'ViralworksheetController@print')->name('print');
	Route::get('cancel/{worksheet}', 'ViralworksheetController@cancel')->name('cancel');
	Route::get('upload/{worksheet}', 'ViralworksheetController@upload')->name('upload');
	Route::put('upload/{worksheet}', 'ViralworksheetController@save_results')->name('save_results');

	Route::get('approve/{worksheet}', 'ViralworksheetController@approve_results')->name('approve_results');
	Route::put('approve/{worksheet}', 'ViralworksheetController@approve')->name('approve');

});

Route::resource('viralworksheet', 'ViralworksheetController');

Auth::routes();

Route::get('test', 'FacilityController@test');

Route::get('login/facility', 'Auth\\LoginController@fac_login')->name('login.facility');
Route::post('login/facility', 'Auth\\LoginController@facility_login');
