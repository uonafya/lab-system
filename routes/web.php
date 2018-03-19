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

// Route::get('/', function () {
    // return view('auth.login');
    // return view('emergency');
// });

Route::redirect('/', '/login');

Route::get('/addsample', function () {
	return view('addsample');
});

Route::get('login/facility', 'Auth\\LoginController@fac_login')->name('login.facility');
Route::post('login/facility', 'Auth\\LoginController@facility_login');

Auth::routes();


Route::middleware(['web', 'auth'])->group(function(){

	Route::get('batch/dispatch/', 'BatchController@batch_dispatch');
	Route::post('batch/complete_dispatch/', 'BatchController@confirm_dispatch');
	Route::get('batch/approve/', 'BatchController@approve_site_entry');
	Route::get('batch/index/{page?}/{date_start?}/{date_end?}', 'BatchController@display_batches');
	Route::get('batch/summary/{batch}', 'BatchController@summary');
	Route::get('batch/individual/{batch}', 'BatchController@individual');
	Route::resource('batch', 'BatchController');

	Route::get('viralbatch/dispatch/', 'ViralbatchController@batch_dispatch');
	Route::post('viralbatch/complete_dispatch/', 'ViralbatchController@confirm_dispatch');
	Route::get('viralbatch/approve/', 'ViralbatchController@approve_site_entry');
	Route::get('viralbatch/index/{page?}/{date_start?}/{date_end?}', 'ViralbatchController@display_batches');
	Route::resource('viralbatch', 'ViralbatchController');

	Route::get('/checkboxes', function () {
		return view('checkbox');
	});

	Route::get('datatables', function () {
		return view('datatables');
	});

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

	Route::prefix('worksheet')->name('worksheet.')->group(function () {

		Route::get('index/{state?}/{date_start?}/{date_end?}', 'WorksheetController@index')->name('index_two');
		Route::get('create/{machine_type}', 'WorksheetController@create')->name('create_any');
		Route::get('print/{worksheet}', 'WorksheetController@print')->name('print');
		Route::get('cancel/{worksheet}', 'WorksheetController@cancel')->name('cancel');
		Route::get('upload/{worksheet}', 'WorksheetController@upload')->name('upload');
		Route::put('upload/{worksheet}', 'WorksheetController@save_results')->name('save_results');
		Route::get('approve/{worksheet}', 'WorksheetController@approve_results')->name('approve_results');
		Route::put('approve/{worksheet}', 'WorksheetController@approve')->name('approve');
	});

	Route::resource('worksheet', 'WorksheetController');

	Route::get('viralsample/new_patient/{patient}/{facility_id}', 'ViralsampleController@new_patient');
	Route::resource('viralsample', 'ViralsampleController');

	Route::get('viralbatch/dispatch/', 'ViralbatchController@batch_dispatch');
	Route::post('viralbatch/complete_dispatch/', 'ViralbatchController@confirm_dispatch');


	Route::prefix('viralworksheet')->name('viralworksheet.')->group(function () {

		Route::get('index/{state?}/{date_start?}/{date_end?}', 'ViralworksheetController@index')->name('index_two');
		Route::get('create_abbot', 'ViralworksheetController@abbot')->name('create_abbot');
		Route::get('print/{worksheet}', 'ViralworksheetController@print')->name('print');
		Route::get('cancel/{worksheet}', 'ViralworksheetController@cancel')->name('cancel');
		Route::get('upload/{worksheet}', 'ViralworksheetController@upload')->name('upload');
		Route::put('upload/{worksheet}', 'ViralworksheetController@save_results')->name('save_results');

		Route::get('approve/{worksheet}', 'ViralworksheetController@approve_results')->name('approve_results');
		Route::put('approve/{worksheet}', 'ViralworksheetController@approve')->name('approve');

	});

	Route::resource('viralworksheet', 'ViralworksheetController', ['except' => ['edit']]);

	Route::get('test', 'FacilityController@test');

	Route::get('refresh_cache', function () {
		$lookup = new \App\Lookup;
		$lookup->refresh_cache();
		return back();
	});

});
