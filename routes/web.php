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
Route::get('labtrackertest', 'RandomController@testlabtracker');

// Route::get('/addsample', function () {
// 	return view('addsample');
// });

Route::get('/config', 'RandomController@config');
Route::get('/download_notice', 'RandomController@download_notice');

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
	Route::get('nhrl', 'RandomController@login_edarp')->name('nhrl');
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
		Route::get('delayed/', 'BatchController@delayed_batches');
		Route::post('index', 'BatchController@batch_search');
		Route::get('site_approval/', 'BatchController@approve_site_entry');
		Route::get('site_approval/{batch}', 'BatchController@site_entry_approval');
		Route::get('site_approval_group/{batch}', 'BatchController@site_entry_approval_group');
		Route::put('site_approval_group/{batch}', 'BatchController@site_entry_approval_group_save');

		Route::group(['middleware' => ['only_utype:1,4']], function () {

			Route::get('convert_from_poc/{batch}', 'BatchController@convert_to_site_entry');
			Route::post('destroy_multiple/', 'BatchController@destroy_multiple');
			
			Route::get('dispatch/', 'BatchController@batch_dispatch');
			Route::post('complete_dispatch/', 'BatchController@confirm_dispatch');

			Route::get('transfer/{batch}', 'BatchController@transfer')->name('get.transfer');
			Route::post('transfer/{batch}', 'BatchController@transfer_to_new_batch')->name('post.transfer');

			Route::get('sample_manifest', 'BatchController@sample_manifest')->name('sample_manifest');
			Route::post('sample_manifest', 'BatchController@sample_manifest')->name('post.sample_manifest');
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
			Route::get('facility/{facility}', 'Cd4SampleController@facility')->name('facility');
			Route::get('print/{sample}', 'Cd4SampleController@print')->name('print');
			Route::get('printresult/{sample}', 'Cd4SampleController@printresult')->name('printresult');
			Route::get('search/{sample}', 'Cd4SampleController@searchresult')->name('searchresult');
			Route::get('delete/{sample}', 'Cd4SampleController@destroy')->name('delete');
			Route::post('search', 'Cd4SampleController@search')->name('search');
		});
		Route::resource('sample', 'Cd4SampleController');
		Route::prefix('patient')->name('patient.')->group(function(){
			Route::post('new', 'Cd4PatientController@new_patient')->name('new');			
			Route::get('search_name/{patient_name}', 'Cd4PatientController@search_name')->name('search_name');
			Route::post('search_name', 'Cd4PatientController@search_name');
			Route::get('search_record_no/{recordno}', 'Cd4PatientController@search_record_no')->name('search_record_no');
			Route::post('search_record_no', 'Cd4PatientController@search_record_no');
		});
		Route::resource('patients', 'Cd4PatientController');

		Route::prefix('reports')->name('reports.')->group(function(){
			Route::get('/', 'ReportController@cd4reports')->name('cd4reports');
			Route::post('dateselect', 'ReportController@dateselect')->name('dateselect');
			Route::post('generate', 'ReportController@generate')->name('generate');
		});
		
		Route::prefix('worksheet')->name('worksheet.')->group(function(){
			Route::get('cancel/{worksheet}', 'Cd4WorksheetController@cancel')->name('cancel');
			Route::get('confirm/{worksheet}', 'Cd4WorksheetController@confirm_upload')->name('confirm');
			Route::put('save/{worksheet}', 'Cd4WorksheetController@save_upload');
			Route::post('search', 'Cd4WorksheetController@search')->name('search');
			Route::get('state/{state}', 'Cd4WorksheetController@state')->name('state');
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
		Route::get('delayed/', 'ViralbatchController@delayed_batches');
		Route::post('index', 'ViralbatchController@batch_search');
		Route::get('site_approval/', 'ViralbatchController@approve_site_entry');
		Route::get('site_approval/{batch}', 'ViralbatchController@site_entry_approval');
		Route::get('site_approval_group/{batch}', 'ViralbatchController@site_entry_approval_group');
		Route::put('site_approval_group/{batch}', 'ViralbatchController@site_entry_approval_group_save');

		Route::group(['middleware' => ['only_utype:1,4']], function () {

			Route::get('convert_from_poc/{batch}', 'ViralbatchController@convert_to_site_entry');
			Route::post('destroy_multiple/', 'ViralbatchController@destroy_multiple');

			Route::get('dispatch/', 'ViralbatchController@batch_dispatch');
			Route::post('complete_dispatch/', 'ViralbatchController@confirm_dispatch');

			Route::get('transfer/{viralbatch}', 'ViralbatchController@transfer')->name('get.transfer');
			Route::post('transfer/{batch}', 'ViralbatchController@transfer_to_new_batch')->name('post.transfer');

			Route::get('sample_manifest', 'ViralbatchController@sample_manifest')->name('sample_manifest');
			Route::post('sample_manifest', 'ViralbatchController@sample_manifest')->name('post.sample_manifest');
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
	Route::post('partner/search/', 'HomeController@partnersearch')->name('partner.search');

	Route::get('dashboard/{year?}/{month?}', 'DashboardController@index')->name('dashboard');
	Route::post('district/search/', 'DistrictController@search')->name('district.search');
	
	Route::get('downloads/{type}', 'HomeController@download')->name('downloads');

	Route::resource('district', 'DistrictController');

	// Start of Drug Resistance Routes

	Route::prefix('dr_sample')->name('dr_sample.')->group(function () {
		Route::group(['middleware' => ['utype:5']], function () {
			Route::post('index', 'DrSampleController@sample_search');
			Route::put('{drSample}', 'DrSampleController@update')->name('update');
			Route::get('results/{drSample}/{print?}', 'DrSampleController@results')->name('results');
			Route::get('download_results/{drSample}', 'DrSampleController@download_results')->name('download_results');
		});
	});

	Route::group(['middleware' => ['utype:4']], function () {
		Route::resource('dr', 'DrPatientController');

		Route::prefix('dr_sample')->name('dr_sample.')->group(function () {
			Route::get('create/{patient}', 'DrSampleController@create_from_patient');
			Route::get('report', 'DrSampleController@susceptability')->name('report');
			Route::get('index/{sample_status?}/{date_start?}/{date_end?}/{facility_id?}/{subcounty_id?}/{partner_id?}', 'DrSampleController@index');
		});

		Route::resource('dr_sample', 'DrSampleController', ['except' => ['update']]);


		Route::prefix('dr_extraction_worksheet')->name('dr_extraction_worksheet.')->group(function () {

			Route::get('index/{state?}/{date_start?}/{date_end?}', 'DrExtractionWorksheetController@index')->name('list');

			Route::get('create/{limit}', 'DrExtractionWorksheetController@create')->name('create_any');
			Route::get('gel_documentation/{drExtractionWorksheet}', 'DrExtractionWorksheetController@gel_documentation_form')->name('upload');
			Route::put('gel_documentation/{drExtractionWorksheet}', 'DrExtractionWorksheetController@gel_documentation')->name('upload');

			Route::get('cancel/{drExtractionWorksheet}', 'DrExtractionWorksheetController@cancel')->name('cancel');

		});
		Route::resource('dr_extraction_worksheet', 'DrExtractionWorksheetController');


		Route::prefix('dr_worksheet')->name('dr_worksheet.')->group(function () {

			Route::get('index/{state?}/{date_start?}/{date_end?}', 'DrWorksheetController@index')->name('list');

			Route::get('create/{extraction_worksheet_id}', 'DrWorksheetController@create')->name('create_any');
			Route::get('upload/{worksheet}', 'DrWorksheetController@upload')->name('upload');
			Route::put('upload/{worksheet}', 'DrWorksheetController@save_results')->name('save_results');

			Route::get('approve/{worksheet}', 'DrWorksheetController@approve_results')->name('approve_results');
			Route::put('approve/{worksheet}', 'DrWorksheetController@approve')->name('approve');
			
			Route::get('create_plate/{worksheet}', 'DrWorksheetController@create_plate')->name('create_plate');
			Route::get('get_plate_result/{worksheet}', 'DrWorksheetController@get_plate_result')->name('get_plate_result');

			Route::get('print/{worksheet}', 'DrWorksheetController@print')->name('print');
			Route::get('cancel/{worksheet}', 'DrWorksheetController@cancel')->name('cancel');
			Route::get('cancel_upload/{worksheet}', 'DrWorksheetController@cancel_upload')->name('cancel_upload');

		});
		Route::resource('dr_worksheet', 'DrWorksheetController');
	});

	// End of Drug Resistance Routes

	Route::group(['middleware' => ['only_utype:2']], function () {
		Route::prefix('email')->name('email.')->group(function () {
			Route::get('preview/{email}', 'EmailController@demo')->name('demo');
			Route::post('preview/{email}', 'EmailController@demo_email')->name('demo_email');
		});
		Route::resource('email', 'EmailController');
	});

	Route::group(['middleware' => ['only_utype:1']], function() {
		Route::get('lablogs/{year?}/{month?}', 'RandomController@lablogs')->name('lablogs');
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
	Route::post('reports/dateselect', 'ReportController@dateselect')->name('dateselect');
	Route::post('reports', 'ReportController@generate')->name('reports');
	Route::post('reports/kitsconsumption', 'ReportController@consumption');
	Route::get('facility/reports/{testtype?}', 'ReportController@index')->name('facility');

	Route::get('reports/kits', 'KitsController@kits')->name('report.kits');
	Route::post('reports/kitdeliveries', 'KitsController@kits');
	Route::get('report/allocation/{testtype?}/{year}/{month}/{approval?}', 'KitsController@allocation')->name('report.allocation');
	Route::put('kitallocation/{allocation}/edit', 'KitsController@editallocation');

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

	Route::get('allocation', 'TaskController@allocation')->name('allocation');
	Route::post('allocation', 'TaskController@allocation')->name('post.allocation');
	Route::get('consumption/{guide?}', 'TaskController@consumption')->name('consumption');
	Route::post('consumption', 'TaskController@consumption');
	Route::get('equipmentlog', 'TaskController@equipmentlog')->name('equipmentlog');
	Route::post('equipmentlog', 'TaskController@equipmentlog');
	Route::get('/pending', 'TaskController@index')->name('pending');
	Route::get('/performancelog', 'TaskController@performancelog')->name('performancelog');
	Route::post('/performancelog', 'TaskController@performancelog');
	Route::get('/kitsdeliveries/{platform?}', 'TaskController@addKitDeliveries')->name('kitsdeliveries');
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
		Route::post('similar', 'SampleController@similar');
		Route::get('release/{sample}', 'SampleController@release_redraw');
		Route::get('print/{sample}', 'SampleController@individual');
		
		Route::group(['middleware' => ['utype:4']], function () {
			Route::get('runs/{sample}', 'SampleController@runs');	
			Route::get('transfer/{sample}', 'SampleController@transfer');	
		});

		Route::group(['middleware' => ['only_utype:2']], function () {	
			Route::get('transfer_samples/{facility_id?}', 'SampleController@transfer_samples_form');	
			Route::post('transfer_samples', 'SampleController@transfer_samples');	
		});

		Route::get('upload', 'SampleController@site_sample_page');
		Route::post('upload', 'SampleController@upload_site_samples');

		Route::get('sms_log', 'SampleController@list_sms');
		Route::get('sms/{sample}', 'SampleController@send_sms');

		Route::get('create_poc', 'SampleController@create_poc');
		Route::get('list_poc/{param?}', 'SampleController@list_poc');
		Route::get('{sample}/edit_result', 'SampleController@edit_poc');
		Route::put('{sample}/edit_result', 'SampleController@save_poc');

		Route::post('search', 'SampleController@search');		
		Route::post('ord_no', 'SampleController@ord_no');		
	});
	Route::resource('sample', 'SampleController');

	Route::get('user/passwordReset/{user?}', 'UserController@passwordreset')->name('passwordReset');
	Route::get('user/switch_user/{user?}', 'UserController@switch_user')->name('switch_user');

	Route::group(['middleware' => ['only_utype:2']], function () {
		Route::get('users', 'UserController@index')->name('users');
		Route::get('user/add', 'UserController@create')->name('user.add');
		Route::get('user/status/{user}', 'UserController@delete')->name('user.delete');
		Route::get('users/activity/{user?}/{year?}/{month?}', 'UserController@activity')->name('user.activity');
	});
	Route::resource('user', 'UserController');	


	Route::prefix('viralsample')->name('viralsample.')->group(function () {

		Route::get('create/{sampletype?}', 'ViralsampleController@create');

		Route::get('nhrl', 'ViralsampleController@nhrl_samples')->name('nhrl');
		Route::post('nhrl', 'ViralsampleController@approve_nhrl');

		Route::get('upload', 'ViralsampleController@site_sample_page');
		Route::post('upload', 'ViralsampleController@upload_site_samples');

		Route::get('sms_log', 'ViralsampleController@list_sms');
		Route::get('sms/{sample}', 'ViralsampleController@send_sms');

		Route::post('new_patient', 'ViralsampleController@new_patient');
		Route::post('similar', 'ViralsampleController@similar');
		Route::get('release/{sample}', 'ViralsampleController@release_redraw');
		Route::get('print/{sample}', 'ViralsampleController@individual');

		Route::group(['middleware' => ['utype:4']], function () {
			Route::get('runs/{sample}', 'ViralsampleController@runs');		
			Route::get('transfer/{sample}', 'ViralsampleController@transfer');		
		});

		Route::group(['middleware' => ['only_utype:2']], function () {	
			Route::get('transfer_samples/{facility_id?}', 'ViralsampleController@transfer_samples_form');	
			Route::post('transfer_samples', 'ViralsampleController@transfer_samples');	
		});

		Route::get('create_poc', 'ViralsampleController@create_poc');
		Route::get('list_poc/{param?}', 'ViralsampleController@list_poc');
		Route::get('{sample}/edit_result', 'ViralsampleController@edit_poc');
		Route::put('{sample}/edit_result', 'ViralsampleController@save_poc');

		Route::post('search', 'ViralsampleController@search');		
		Route::post('ord_no', 'ViralsampleController@ord_no');

		Route::group(['middleware' => ['utype:0']], function(){
			Route::get('excelupload', 'ViralsampleController@excelupload');
			Route::post('excelupload', 'ViralsampleController@excelupload');
			Route::get('exceluploaddelete', 'ViralsampleController@deleteexcelupload');
			Route::post('exceluploaddelete', 'ViralsampleController@deleteexcelupload');
			Route::get('extractexcelresult', 'ViralsampleController@extract_excel_results');
			Route::post('extractexcelresult', 'ViralsampleController@extract_excel_results');
		});
	});
	Route::resource('viralsample', 'ViralsampleController');


	Route::group(['middleware' => ['utype:4']], function () {

		Route::prefix('worksheet')->name('worksheet.')->group(function () {

			Route::get('index/{state?}/{machine_type?}/{date_start?}/{date_end?}', 'WorksheetController@index')->name('list');
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

			Route::get('index/{state?}/{machine_type?}/{date_start?}/{date_end?}', 'ViralworksheetController@index')->name('list');

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
			

			Route::group(['middleware' => ['utype:0']], function() {
				Route::get('exceluploadworksheet', 'ViralworksheetController@exceluploadworksheet');
				Route::post('exceluploadworksheet', 'ViralworksheetController@exceluploadworksheet');
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

	Route::group(['middleware' => ['only_utype:0']], function(){
		Route::get('abbottdeliveries/{id}/recompute', 'AbbottDeliveriesController@recompute');
		Route::get('abbottdeliveries/{id}/restore', 'AbbottDeliveriesController@restore');
		Route::get('abbottdeliveries/{id}/delete', 'AbbottDeliveriesController@delete');
		Route::resource('abbottdeliveries', 'AbbottDeliveriesController');

		Route::get('abbottprocurement/{id}/recomputeending', 'AbbottProcurementController@recomputeending');
		Route::resource('abbottprocurement', 'AbbottProcurementController');

		Route::get('taqmandeliveries/{id}/recompute', 'TaqmanDeliveriesController@recompute');
		Route::get('taqmandeliveries/{id}/restore', 'TaqmanDeliveriesController@restore');
		Route::get('taqmandeliveries/{id}/delete', 'TaqmanDeliveriesController@delete');
		Route::resource('taqmandeliveries', 'TaqmanDeliveriesController');
		
		Route::get('taqmanprocurement/{id}/recomputeending', 'TaqmanProcurementController@recomputeending');
		Route::resource('taqmanprocurement', 'TaqmanProcurementController');
	});

});
