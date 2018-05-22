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

Route::get('/addsample', function () {
	return view('addsample');
});

Route::get('/config', function () {
	return phpinfo();
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


Route::post('facility/search/', 'FacilityController@search')->name('facility.search');

Route::get('error', function(){
	return view('errors.error', ['code' => '500', 'title' => 'Internal server error', 'description' => 'Sorry, there was an internal server error that occured. Please try again later']);
});

// Route::get('/home', function () {
// 	return view('home');
// });

Route::get('/home', 'HomeController@index');
Route::get('/synch', 'HomeController@test');

Route::middleware(['web', 'auth'])->group(function(){

	Route::get('/home', 'HomeController@index');

	Route::get('search', function () {	return view('forms.search')->with('pageTitle', 'Search'); });

	Route::prefix('batch')->name('batch.')->group(function () {
		// Route::get('index/{batch_complete?}/{page?}/{date_start?}/{date_end?}', 'BatchController@index');
		Route::get('index/{batch_complete?}/{date_start?}/{date_end?}', 'BatchController@index');
		Route::get('facility/{facility_id}/{batch_complete?}/{date_start?}/{date_end?}', 'BatchController@facility_batches');
		Route::get('dispatch/', 'BatchController@batch_dispatch');
		Route::post('complete_dispatch/', 'BatchController@confirm_dispatch');
		Route::get('site_approval/', 'BatchController@approve_site_entry');
		Route::get('site_approval/{batch}', 'BatchController@site_entry_approval');
		Route::get('site_approval_group/{batch}', 'BatchController@site_entry_approval_group');
		Route::put('site_approval_group/{batch}', 'BatchController@site_entry_approval_group_save');

		Route::get('summary/{batch}', 'BatchController@summary');
		Route::post('summaries', 'BatchController@summaries');
		Route::get('individual/{batch}', 'BatchController@individual');

		Route::post('search/', 'BatchController@search')->name('search');
	});
	Route::resource('batch', 'BatchController');


	Route::prefix('viralbatch')->name('viralbatch.')->group(function () {
		// Route::get('index/{batch_complete?}/{page?}/{date_start?}/{date_end?}', 'ViralbatchController@index');
		Route::get('index/{batch_complete?}/{date_start?}/{date_end?}', 'ViralbatchController@index');
		Route::get('facility/{facility_id}/{batch_complete?}/{date_start?}/{date_end?}', 'ViralbatchController@facility_batches');
		Route::get('dispatch/', 'ViralbatchController@batch_dispatch');
		Route::post('complete_dispatch/', 'ViralbatchController@confirm_dispatch');
		Route::get('site_approval/', 'ViralbatchController@approve_site_entry');
		Route::get('site_approval/{batch}', 'ViralbatchController@site_entry_approval');
		Route::get('site_approval_group/{batch}', 'ViralbatchController@site_entry_approval_group');
		Route::put('site_approval_group/{batch}', 'ViralbatchController@site_entry_approval_group_save');
		
		Route::get('summary/{batch}', 'ViralbatchController@summary');
		Route::post('summaries', 'ViralbatchController@summaries');
		Route::get('individual/{batch}', 'ViralbatchController@individual');

		Route::post('search/', 'ViralbatchController@search')->name('search');
	});
	Route::resource('viralbatch', 'ViralbatchController');

	Route::post('county/search/', 'HomeController@countysearch')->name('county.search');

	Route::get('dashboard', 'DashboardController@index')->name('dashboard');
	Route::post('district/search/', 'DistrictController@search')->name('district.search');
	
	Route::get('downloads/{type}', 'HomeController@download')->name('downloads');

	Route::resource('district', 'DistrictController');

	Route::get('facility/served', 'FacilityController@served');
	Route::get('facility/withoutemails', 'FacilityController@withoutemails')->name('withoutemails');
	Route::get('facility/withoutG4S', 'FacilityController@withoutG4S')->name('withoutG4S');
	Route::resource('facility', 'FacilityController');

	Route::get('/home', 'HomeController@index');

	Route::get('reports', 'ReportController@index')->name('reports');
	Route::post('reports/dateselect', 'ReportController@dateselect')->name('dateselect');
	Route::post('reports', 'ReportController@generate')->name('reports');

	Route::prefix('patient')->name('patient.')->group(function () {
		Route::post('search/{facility_id?}', 'PatientController@search');
		Route::get('index/{facility_id?}', 'PatientController@index');	

		// Merging of patients	
		Route::get('{patient}/merge', 'PatientController@merge');		
		Route::put('{patient}/merge', 'PatientController@merge_patients');	

		// Transfer patient to a new facility	
		Route::get('{patient}/transfer', 'PatientController@transfer');		
		Route::put('{patient}/transfer', 'PatientController@transfer_patient');
	});
	Route::resource('patient', 'PatientController');

	Route::prefix('viralpatient')->name('viralpatient.')->group(function () {
		Route::post('search/{facility_id?}/{female?}', 'ViralpatientController@search');
		Route::get('index/{facility_id?}', 'ViralpatientController@index');	

		// Merging of patients	
		Route::get('{patient}/merge', 'ViralpatientController@merge');		
		Route::put('{patient}/merge', 'ViralpatientController@merge_patients');	

		// Transfer patient to a new facility	
		Route::get('{patient}/transfer', 'ViralpatientController@transfer');		
		Route::put('{patient}/transfer', 'ViralpatientController@transfer_patient');
	});
	Route::resource('viralpatient', 'ViralpatientController');


	Route::prefix('sample')->name('sample.')->group(function () {
		Route::post('new_patient', 'SampleController@new_patient');
		Route::get('release/{sample}', 'SampleController@release_redraw');
		Route::get('print/{sample}', 'SampleController@individual');
		Route::get('runs/{sample}', 'SampleController@runs');

		Route::get('create_poc', 'SampleController@create_poc');
		Route::get('list_poc', 'SampleController@list_poc');
		Route::get('edit_poc/{sample}', 'SampleController@edit_poc');
		Route::put('edit_poc/{sample}', 'SampleController@save_poc');

		Route::post('search', 'SampleController@search');		
	});
	Route::resource('sample', 'SampleController');


	Route::prefix('viralsample')->name('viralsample.')->group(function () {

		Route::get('nhrl', 'ViralsampleController@nhrl_samples');
		Route::post('nhrl', 'ViralsampleController@approve_nhrl');

		Route::post('new_patient', 'ViralsampleController@new_patient');
		Route::get('release/{sample}', 'ViralsampleController@release_redraw');
		Route::get('print/{sample}', 'ViralsampleController@individual');
		Route::get('runs/{sample}', 'ViralsampleController@runs');
		Route::post('search', 'ViralsampleController@search');		
	});
	Route::resource('viralsample', 'ViralsampleController');



	Route::prefix('worksheet')->name('worksheet.')->group(function () {

		Route::get('index/{state?}/{date_start?}/{date_end?}', 'WorksheetController@index')->name('list');
		Route::get('create/{machine_type}', 'WorksheetController@create')->name('create_any');
		Route::get('find/{worksheet}', 'WorksheetController@find')->name('find');
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

	Route::resource('worksheet', 'WorksheetController', ['except' => ['edit']]);


	Route::prefix('viralworksheet')->name('viralworksheet.')->group(function () {

		Route::get('index/{state?}/{date_start?}/{date_end?}', 'ViralworksheetController@index')->name('list');
		Route::get('create/{machine_type}', 'ViralworksheetController@create')->name('create_any');		
		Route::get('find/{worksheet}', 'ViralworksheetController@find')->name('find');
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
