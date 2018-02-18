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
    return view('login');
});

Route::get('/home', function () {
	return view('layouts/master');
});

Route::get('/addsample', function () {
	return view('addsample');
});

Route::get('sample/new_patient/{patient}/{facility_id}', 'SampleController@new_patient');
Route::resource('sample', 'SampleController');

Route::get('worksheet/create_abbot', 'WorksheetController@abbot')->name('worksheet.create_abbot');
Route::get('worksheet/print/{worksheet}', 'WorksheetController@print')->name('worksheet.print');
Route::get('worksheet/cancel/{worksheet}', 'WorksheetController@cancel')->name('worksheet.cancel');
Route::get('worksheet/upload/{worksheet}', 'WorksheetController@upload')->name('worksheet.upload');
Route::put('worksheet/upload/{worksheet}', 'WorksheetController@save_results')->name('worksheet.save_results');

Route::get('worksheet/approve/{worksheet}', 'WorksheetController@approve_results')->name('worksheet.approve_results');
Route::put('worksheet/approve/{worksheet}', 'WorksheetController@approve')->name('worksheet.approve');

Route::resource('worksheet', 'WorksheetController');

Auth::routes();
