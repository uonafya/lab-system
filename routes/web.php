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

// Route::get('reset_password/{token}', ['as' => 'password.reset', function($token)
// {
//     // implement your reset password route here!
// }]);

// Route::get('/', function () {
    // return view('auth.login');
    // return view('emergency');
// });

Route::redirect('/', '/login');

Route::get('/new', function(){
	// $mother = \App\Mother::find(1);
	// $mother->load('patient.sample');
	// return $mother->toJson();
	$batch = \App\Batch::find(1);
	$batch->load('sample.patient.mother');
	return $batch->toJson();
});

Route::get('/addsample', function () {
	return view('addsample');
});

Route::get('login/facility', 'Auth\\LoginController@fac_login')->name('login.facility');
Route::post('login/facility', 'Auth\\LoginController@facility_login');

Auth::routes();

Route::get('datatables', function () {
	return view('datatables');
});

Route::get('/checkboxes', function () {
	return view('checkbox');
});



Route::get('error', function(){
	return view('errors.error', ['code' => '500', 'title' => 'Internal server error', 'description' => 'Sorry, there was an internal server error that occured. Please try again later']);
});

// Route::get('/home', function () {
// 	return view('home');
// });
Route::get('/home', 'HomeController@index');

Route::middleware(['web', 'auth'])->group(function(){

	Route::get('search', function () {	return view('forms.search')->with('pageTitle', 'Search'); });

	Route::prefix('batch')->name('batch.')->group(function () {
		// Route::get('index/{batch_complete?}/{page?}/{date_start?}/{date_end?}', 'BatchController@index');
		Route::get('index/{batch_complete?}/{date_start?}/{date_end?}', 'BatchController@index');
		Route::get('dispatch/', 'BatchController@batch_dispatch');
		Route::post('complete_dispatch/', 'BatchController@confirm_dispatch');
		Route::get('site_approval/', 'BatchController@approve_site_entry');
		Route::get('site_approval/{batch}', 'BatchController@site_entry_approval');
		Route::get('summary/{batch}', 'BatchController@summary');
		Route::post('summaries', 'BatchController@summaries');
		Route::get('individual/{batch}', 'BatchController@individual');

		Route::post('search/', 'BatchController@search')->name('search');
	});
	Route::resource('batch', 'BatchController');

	Route::get('dashboard', 'DashboardController@index')->name('dashboard');
	
	Route::resource('district', 'DistrictController');

	Route::get('facility/served', 'FacilityController@served');
	Route::get('facility/withoutemails', 'FacilityController@withoutemails')->name('withoutemails');
	Route::get('facility/withoutG4S', 'FacilityController@withoutG4S')->name('withoutG4S');
	Route::post('facility/search/', 'FacilityController@search')->name('facility.search');
	Route::resource('facility', 'FacilityController');

	Route::prefix('viralbatch')->name('viralbatch.')->group(function () {
		// Route::get('index/{batch_complete?}/{page?}/{date_start?}/{date_end?}', 'ViralbatchController@index');
		Route::get('index/{batch_complete?}/{date_start?}/{date_end?}', 'ViralbatchController@index');
		Route::get('dispatch/', 'ViralbatchController@batch_dispatch');
		Route::post('complete_dispatch/', 'ViralbatchController@confirm_dispatch');
		Route::get('site_approval/', 'ViralbatchController@approve_site_entry');
		Route::get('site_approval/{batch}', 'ViralbatchController@site_entry_approval');
		Route::get('summary/{batch}', 'ViralbatchController@summary');
		Route::post('summaries', 'ViralbatchController@summaries');
		Route::get('individual/{batch}', 'ViralbatchController@individual');

		Route::post('search/', 'ViralbatchController@search')->name('search');
	});
	Route::resource('viralbatch', 'ViralbatchController');

	Route::get('/home', 'HomeController@index');


	Route::post('patient/search/', 'PatientController@search');
	Route::resource('patient', 'PatientController');
	
	Route::post('viralpatient/search/', 'ViralpatientController@search');
	Route::resource('viralpatient', 'ViralpatientController');


	Route::post('sample/new_patient', 'SampleController@new_patient');
	Route::get('sample/release/{sample}', 'SampleController@release_redraw');
	Route::get('sample/print/{sample}', 'SampleController@individual');
	Route::get('sample/runs/{sample}', 'SampleController@runs');
	Route::get('sample/create_poc', 'SampleController@create_poc');
	Route::resource('sample', 'SampleController');

	Route::post('viralsample/new_patient', 'ViralsampleController@new_patient');
	Route::get('viralsample/release/{viralsample}', 'ViralsampleController@release_redraw');
	Route::get('viralsample/print/{sample}', 'ViralsampleController@individual');
	Route::get('viralsample/runs/{sample}', 'ViralsampleController@runs');
	Route::resource('viralsample', 'ViralsampleController');



	Route::prefix('worksheet')->name('worksheet.')->group(function () {

		Route::get('index/{state?}/{date_start?}/{date_end?}', 'WorksheetController@index')->name('list');
		Route::get('create/{machine_type}', 'WorksheetController@create')->name('create_any');
		Route::get('print/{worksheet}', 'WorksheetController@print')->name('print');
		Route::get('cancel/{worksheet}', 'WorksheetController@cancel')->name('cancel');
		Route::get('cancel_upload/{worksheet}', 'WorksheetController@cancel_upload')->name('cancel_upload');
		Route::get('upload/{worksheet}', 'WorksheetController@upload')->name('upload');
		Route::put('upload/{worksheet}', 'WorksheetController@save_results')->name('save_results');
		Route::get('approve/{worksheet}', 'WorksheetController@approve_results')->name('approve_results');
		Route::put('approve/{worksheet}', 'WorksheetController@approve')->name('approve');

		Route::post('search/', 'WorksheetController@search')->name('search');
	});
	Route::get('worksheetserverside/', 'WorksheetController@getworksheetserverside')->name('worksheetserverside');

	Route::resource('worksheet', 'WorksheetController');

	// Route::post('viralsample/new_patient', 'ViralsampleController@new_patient');
	// Route::resource('viralsample', 'ViralsampleController');

	// Route::get('viralbatch/dispatch/', 'ViralbatchController@batch_dispatch');
	// Route::post('viralbatch/complete_dispatch/', 'ViralbatchController@confirm_dispatch');


	Route::prefix('viralworksheet')->name('viralworksheet.')->group(function () {

		Route::get('index/{state?}/{date_start?}/{date_end?}', 'ViralworksheetController@index')->name('list');
		Route::get('create/{machine_type}', 'ViralworksheetController@create')->name('create_any');		
		Route::get('print/{worksheet}', 'ViralworksheetController@print')->name('print');
		Route::get('cancel/{worksheet}', 'ViralworksheetController@cancel')->name('cancel');
		Route::get('cancel_upload/{worksheet}', 'ViralworksheetController@cancel_upload')->name('cancel_upload');
		Route::get('upload/{worksheet}', 'ViralworksheetController@upload')->name('upload');
		Route::put('upload/{worksheet}', 'ViralworksheetController@save_results')->name('save_results');
		Route::get('approve/{worksheet}', 'ViralworksheetController@approve_results')->name('approve_results');
		Route::put('approve/{worksheet}', 'ViralworksheetController@approve')->name('approve');

		Route::post('search/', 'ViralworksheetController@search')->name('search');

	});

	Route::resource('viralworksheet', 'ViralworksheetController', ['except' => ['edit']]);

	Route::get('test', 'FacilityController@test');

	Route::get('refresh_cache', function () {
		$lookup = \App\Lookup::refresh_cache();
		return back();
	});

	Route::get('sysswitch/{sys}', function($sys) {
		if($sys == 'EID'){
			$new = session(['testingSystem' => 'EID']);
		}else if ($sys == 'Viralload'){
			$new = session(['testingSystem' => 'Viralload']);
		}
		echo json_encode(session('testingSystem'));
	});

});
