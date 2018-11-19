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
Route::redirect('/eid', '/login');
Route::redirect('/knh', '/login');
Route::redirect('/nyumbani', '/login');

Route::get('/eid/{param?}', 'RandomController@send_to_login')->where('param', '(.*\\.*)');

// Route::get('/addsample', function () {
// 	return view('addsample');
// });

Route::get('/config', 'RandomController@config');

Route::get('login/facility', 'Auth\\LoginController@fac_login')->name('login.facility');
Route::post('login/facility', 'Auth\\LoginController@facility_login');


Auth::routes();

// Route::get('datatables', function () {
// 	return view('datatables');
// });

// Route::get('/checkboxes', function () {
// 	return view('checkbox');
// });


Route::post('facility/search/', 'FacilityController@search')->name('facility.search');

// Route::get('error', function(){
// 	return view('errors.error', ['code' => '500', 'title' => 'Internal server error', 'description' => 'Sorry, there was an internal server error that occured. Please try again later']);
// });

Route::get('/synch', 'HomeController@test');
Route::get('download_api', 'RandomController@download_api');

Route::middleware(['signed'])->group(function(){
	Route::get('dr_sample/edit/{user}/{sample}', 'DrSampleController@facility_edit')->name('dr_sample.facility_edit');
});

Route::middleware(['auth'])->group(function(){

	Route::prefix('home')->name('home.')->group(function(){
		Route::get('/', 'HomeController@index');
		Route::get('overdue/{level?}', 'HomeController@overdue')->name('overdue');
		Route::get('pending/{type?}/{sampletype?}', 'HomeController@pending')->name('pending');
		Route::get('repeat', 'HomeController@repeat')->name('repeat');
		Route::get('rejected', 'HomeController@rejected')->name('rejected');
	});
	
	Route::get('search', 'RandomController@search');

	Route::get('refresh_cache', 'RandomController@refresh_cache');
	Route::get('refresh_dashboard', 'RandomController@refresh_dashboard');
	Route::get('sysswitch/{sys}', 'RandomController@sysswitch');

	Route::prefix('batch')->name('batch.')->group(function () {
		// Route::get('index/{batch_complete?}/{page?}/{date_start?}/{date_end?}', 'BatchController@index');
		Route::get('index/{batch_complete?}/{date_start?}/{date_end?}/{facility_id?}/{subcounty_id?}/{partner_id?}', 'BatchController@index');
		Route::get('to_print/{date_start?}/{date_end?}/{facility_id?}/{subcounty_id?}/{partner_id?}', 'BatchController@to_print');
		Route::get('facility/{facility_id}/{batch_complete?}/{date_start?}/{date_end?}', 'BatchController@facility_batches');
		Route::post('index', 'BatchController@batch_search');
		Route::get('site_approval/', 'BatchController@approve_site_entry');
		Route::get('site_approval/{batch}', 'BatchController@site_entry_approval');
		Route::get('site_approval_group/{batch}', 'BatchController@site_entry_approval_group');
		Route::put('site_approval_group/{batch}', 'BatchController@site_entry_approval_group_save');

		Route::group(['middleware' => ['only_utype:1,4']], function () {
			Route::get('dispatch/', 'BatchController@batch_dispatch');
			Route::post('complete_dispatch/', 'BatchController@confirm_dispatch');

			Route::get('transfer/{batch}', 'BatchController@transfer')->name('get.transfer');
			Route::post('transfer/{batch}', 'BatchController@transfer_to_new_batch')->name('post.transfer');
		});

		Route::get('summary/{batch}', 'BatchController@summary');
		Route::post('summaries', 'BatchController@summaries');
		Route::get('individual/{batch}', 'BatchController@individual');
		Route::get('individual_pdf/{batch}', 'BatchController@individual_pdf');
		Route::get('envelope/{batch}', 'BatchController@envelope')->name('envelope');
		Route::get('email/{batch}', 'BatchController@email')->name('email');

		Route::post('search/', 'BatchController@search')->name('search');
	});
	Route::resource('batch', 'BatchController');

	Route::prefix('cd4')->name('cd4.')->group(function(){
		Route::prefix('sample')->name('sample.')->group(function(){
			Route::get('dispatch/{state}', 'Cd4SampleController@dispatch')->name('dispatch');
		});
		Route::resource('sample', 'Cd4SampleController');
		Route::prefix('patient')->name('patient.')->group(function(){
			Route::post('new', 'Cd4PatientController@new_patient')->name('new');
		});
		Route::resource('patients', 'Cd4PatientController');
		
		Route::prefix('worksheet')->name('worksheet.')->group(function(){
			Route::get('cancel/{worksheet}', 'Cd4WorksheetController@cancel')->name('cancel');
			Route::get('confirm/{worksheet}', 'Cd4WorksheetController@confirm_upload')->name('confirm');
			Route::put('save/{worksheet}', 'Cd4WorksheetController@save_upload');
			Route::get('create/{limit}', 'Cd4WorksheetController@create');
			Route::get('index/{state}', 'Cd4WorksheetController@index')->name('index');
			Route::get('print/{worksheet}', 'Cd4WorksheetController@print')->name('print');
			Route::get('upload/{worksheet}', 'Cd4WorksheetController@upload')->name('upload');
			Route::put('upload/{worksheet}', 'Cd4WorksheetController@upload');
		});
		Route::resource('worksheet', 'Cd4WorksheetController');
	});

	Route::prefix('viralbatch')->name('viralbatch.')->group(function () {
		// Route::get('index/{batch_complete?}/{page?}/{date_start?}/{date_end?}', 'ViralbatchController@index');
		Route::get('index/{batch_complete?}/{date_start?}/{date_end?}/{facility_id?}/{subcounty_id?}/{partner_id?}', 'ViralbatchController@index');
		Route::get('to_print/{date_start?}/{date_end?}/{facility_id?}/{subcounty_id?}/{partner_id?}', 'ViralbatchController@to_print');
		Route::get('facility/{facility_id}/{batch_complete?}/{date_start?}/{date_end?}', 'ViralbatchController@facility_batches');
		Route::post('index', 'ViralbatchController@batch_search');
		Route::get('site_approval/', 'ViralbatchController@approve_site_entry');
		Route::get('site_approval/{batch}', 'ViralbatchController@site_entry_approval');
		Route::get('site_approval_group/{batch}', 'ViralbatchController@site_entry_approval_group');
		Route::put('site_approval_group/{batch}', 'ViralbatchController@site_entry_approval_group_save');

		Route::group(['middleware' => ['only_utype:1,4']], function () {

			Route::get('dispatch/', 'ViralbatchController@batch_dispatch');
			Route::post('complete_dispatch/', 'ViralbatchController@confirm_dispatch');

			Route::get('transfer/{viralbatch}', 'ViralbatchController@transfer')->name('get.transfer');
			Route::post('transfer/{batch}', 'ViralbatchController@transfer_to_new_batch')->name('post.transfer');
		});
		
		Route::get('summary/{batch}', 'ViralbatchController@summary');
		Route::post('summaries', 'ViralbatchController@summaries');
		Route::get('individual/{batch}', 'ViralbatchController@individual');
		Route::get('individual_pdf/{batch}', 'ViralbatchController@individual_pdf');
		Route::get('envelope/{batch}', 'ViralbatchController@envelope')->name('envelope');
		Route::get('email/{batch}', 'ViralbatchController@email')->name('email');

		Route::post('search/', 'ViralbatchController@search')->name('search');
	});
	Route::resource('viralbatch', 'ViralbatchController');

	Route::post('county/search/', 'HomeController@countysearch')->name('county.search');

	Route::get('dashboard/{year?}/{month?}', 'DashboardController@index')->name('dashboard');
	Route::post('district/search/', 'DistrictController@search')->name('district.search');
	
	Route::get('downloads/{type}', 'HomeController@download')->name('downloads');

	Route::resource('district', 'DistrictController');

	Route::group(['middleware' => ['utype:5']], function () {
		Route::put('dr_sample/{drSample}', 'DrSampleController@update')->name('dr_sample.update');
	});

	Route::group(['middleware' => ['utype:4']], function () {
		Route::resource('dr', 'DrPatientController');
		Route::get('dr_sample/create/{patient}', 'DrSampleController@create_from_patient');
		Route::resource('dr_sample', 'DrSampleController');


		Route::prefix('dr_worksheet')->name('dr_worksheet.')->group(function () {

			Route::get('index/{state?}/{date_start?}/{date_end?}', 'DrWorksheetController@index')->name('list');

			Route::get('upload/{worksheet}', 'DrWorksheetController@upload')->name('upload');
			Route::put('upload/{worksheet}', 'DrWorksheetController@save_results')->name('save_results');

			Route::get('print/{worksheet}', 'DrWorksheetController@print')->name('print');
			Route::get('cancel/{worksheet}', 'DrWorksheetController@cancel')->name('cancel');
			Route::get('cancel_upload/{worksheet}', 'DrWorksheetController@cancel_upload')->name('cancel_upload');

		});
		Route::resource('dr_worksheet', 'DrWorksheetController');
	});

	Route::group(['middleware' => ['only_utype:2']], function () {
		Route::prefix('email')->name('email.')->group(function () {
			Route::get('preview/{email}', 'EmailController@demo')->name('demo');
			Route::post('preview/{email}', 'EmailController@demo_email')->name('demo_email');
		});
		Route::resource('email', 'EmailController');
	});
	
	Route::group(['middleware' => ['utype:4']], function () {
		Route::get('facility/served', 'FacilityController@served');
		Route::get('facility/withoutemails', 'FacilityController@withoutemails')->name('withoutemails');
		Route::get('facility/withoutG4S', 'FacilityController@withoutG4S')->name('withoutG4S');
		Route::get('facility/contacts', 'FacilityController@filled_contacts')->name('facility.contacts');
		Route::get('facility/lab', 'FacilityController@lab')->name('facility.lab');
	});		
	Route::resource('facility', 'FacilityController');

	Route::get('/home', 'HomeController@index')->name('home');

	Route::get('reports', 'ReportController@index')->name('reports');
	Route::get('reports/kits', 'ReportController@kits')->name('report.kits');
	Route::post('reports/dateselect', 'ReportController@dateselect')->name('dateselect');
	Route::post('reports', 'ReportController@generate')->name('reports');
	Route::post('reports/kitdeliveries', 'ReportController@kits');
	Route::post('reports/kitsconsumption', 'ReportController@consumption');

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


	Route::get('consumption/{guide?}', 'TaskController@consumption')->name('consumption');
	Route::post('consumption', 'TaskController@consumption');
	Route::get('equipmentlog', 'TaskController@equipmentlog')->name('equipmentlog');
	Route::post('equipmentlog', 'TaskController@equipmentlog');
	Route::get('/pending', 'TaskController@index')->name('pending');
	Route::get('/performancelog', 'TaskController@performancelog')->name('performancelog');
	Route::post('/performancelog', 'TaskController@performancelog');
	Route::get('/kitsdeliveries', 'TaskController@addKitDeliveries')->name('kitsdeliveries');
	Route::post('/kitsdeliveries', 'TaskController@addKitDeliveries')->name('kitsdeliveries');
	
	Route::post('viralpatient/search/', 'ViralpatientController@search');
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
		
		Route::group(['middleware' => ['utype:4']], function () {
			Route::get('runs/{sample}', 'SampleController@runs');		
		});

		Route::get('upload', 'SampleController@site_sample_page');
		Route::post('upload', 'SampleController@upload_site_samples');

		Route::get('sms_log', 'SampleController@list_sms');
		Route::get('sms/{sample}', 'SampleController@send_sms');

		Route::get('create_poc', 'SampleController@create_poc');
		Route::get('list_poc', 'SampleController@list_poc');
		Route::get('{sample}/edit_result', 'SampleController@edit_poc');
		Route::put('{sample}/edit_result', 'SampleController@save_poc');

		Route::post('search', 'SampleController@search');		
	});
	Route::resource('sample', 'SampleController');

	Route::get('user/passwordReset/{user?}', 'UserController@passwordreset')->name('passwordReset');
	Route::get('user/switch_user/{user?}', 'UserController@switch_user')->name('switch_user');

	Route::group(['middleware' => ['only_utype:2']], function () {
		Route::get('users', 'UserController@index')->name('users');
		Route::get('user/add', 'UserController@create')->name('user.add');
		Route::get('user/status/{user}', 'UserController@delete')->name('user.delete');
		Route::get('users/activity/{user?}', 'UserController@activity')->name('user.activity');
	});
	Route::resource('user', 'UserController');	


	Route::prefix('viralsample')->name('viralsample.')->group(function () {

		Route::get('create/{sampletype?}', 'ViralsampleController@create');

		Route::get('nhrl', 'ViralsampleController@nhrl_samples');
		Route::post('nhrl', 'ViralsampleController@approve_nhrl');

		Route::get('upload', 'ViralsampleController@site_sample_page');
		Route::post('upload', 'ViralsampleController@upload_site_samples');

		Route::get('sms_log', 'ViralsampleController@list_sms');
		Route::get('sms/{sample}', 'ViralsampleController@send_sms');

		Route::post('new_patient', 'ViralsampleController@new_patient');
		Route::get('release/{sample}', 'ViralsampleController@release_redraw');
		Route::get('print/{sample}', 'ViralsampleController@individual');

		Route::group(['middleware' => ['utype:4']], function () {
			Route::get('runs/{sample}', 'ViralsampleController@runs');		
		});

		Route::get('create_poc', 'ViralsampleController@create_poc');
		Route::get('list_poc', 'ViralsampleController@list_poc');
		Route::get('{sample}/edit_result', 'ViralsampleController@edit_poc');
		Route::put('{sample}/edit_result', 'ViralsampleController@save_poc');

		Route::post('search', 'ViralsampleController@search');		
	});
	Route::resource('viralsample', 'ViralsampleController');


	Route::group(['middleware' => ['utype:4']], function () {

		Route::prefix('worksheet')->name('worksheet.')->group(function () {

			Route::get('index/{state?}/{date_start?}/{date_end?}', 'WorksheetController@index')->name('list');
			Route::get('create/{machine_type}/{limit?}', 'WorksheetController@create')->name('create_any');
			Route::get('find/{worksheet}', 'WorksheetController@find')->name('find');
			Route::get('print/{worksheet}', 'WorksheetController@print')->name('print');
			Route::get('cancel/{worksheet}', 'WorksheetController@cancel')->name('cancel');
			Route::get('convert/{machine_type}/{worksheet}', 'WorksheetController@convert_worksheet')->name('convert');

			Route::group(['middleware' => ['only_utype:1']], function () {
				Route::get('cancel_upload/{worksheet}', 'WorksheetController@cancel_upload')->name('cancel_upload');
				Route::get('reverse_upload/{worksheet}', 'WorksheetController@reverse_upload')->name('reverse_upload');
				Route::get('upload/{worksheet}', 'WorksheetController@upload')->name('upload');
				Route::put('upload/{worksheet}', 'WorksheetController@save_results')->name('save_results');
				Route::get('approve/{worksheet}', 'WorksheetController@approve_results')->name('approve_results');
				Route::put('approve/{worksheet}', 'WorksheetController@approve')->name('approve');
			});

			Route::post('search/', 'WorksheetController@search')->name('search');
		});
		Route::get('worksheetserverside/', 'WorksheetController@getworksheetserverside')->name('worksheetserverside');

		Route::resource('worksheet', 'WorksheetController');


		Route::prefix('viralworksheet')->name('viralworksheet.')->group(function () {

			Route::get('index/{state?}/{date_start?}/{date_end?}', 'ViralworksheetController@index')->name('list');

			Route::get('set_sampletype/{machine_type}/{calibration?}/{limit?}', 'ViralworksheetController@set_sampletype_form')->name('set_sampletype_form');
			Route::post('set_sampletype', 'ViralworksheetController@set_sampletype')->name('set_sampletype');

			Route::get('create/{sampletype}/{machine_type?}/{calibration?}/{limit?}', 'ViralworksheetController@create')->name('create_any');		
			Route::get('find/{worksheet}', 'ViralworksheetController@find')->name('find');
			Route::get('print/{worksheet}', 'ViralworksheetController@print')->name('print');
			Route::get('cancel/{worksheet}', 'ViralworksheetController@cancel')->name('cancel');
			Route::get('convert/{worksheet}/{machine_type}/', 'ViralworksheetController@convert_worksheet')->name('convert');

			Route::group(['middleware' => ['only_utype:1']], function () {
				Route::get('download_dump/{worksheet}', 'ViralworksheetController@download_dump')->name('download_dump');
				Route::get('cancel_upload/{worksheet}', 'ViralworksheetController@cancel_upload')->name('cancel_upload');
				Route::get('reverse_upload/{worksheet}', 'ViralworksheetController@reverse_upload')->name('reverse_upload');
				Route::get('upload/{worksheet}', 'ViralworksheetController@upload')->name('upload');
				Route::put('upload/{worksheet}', 'ViralworksheetController@save_results')->name('save_results');
				Route::get('approve/{worksheet}', 'ViralworksheetController@approve_results')->name('approve_results');
				Route::put('approve/{worksheet}', 'ViralworksheetController@approve')->name('approve');
			});

			Route::post('search/', 'ViralworksheetController@search')->name('search');

		});
		Route::resource('viralworksheet', 'ViralworksheetController');
	});

	Route::group(['middleware' => ['only_utype:5']], function () {

		Route::prefix('worklist')->name('worklist.')->group(function () {
			Route::get('index/{testtype?}', 'WorklistController@index')->name('list');
			Route::get('create/{testtype}', 'WorklistController@create')->name('create_any');
			Route::get('print/{worklist}', 'WorklistController@print')->name('print');
		});
		Route::resource('worklist', 'WorklistController', ['except' => ['edit']]);
	});

});
