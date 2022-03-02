<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('login_register');
});
<<<<<<< HEAD

Route::get('patients','ClientRegistry@getPatients');
Route::get('ccc_no','ClientRegistry@getCCC_No');

Route::get('search/{patient}','ClientRegistry@search');
=======
>>>>>>> 970e835be223bdbb5ec7bc2ae3147254b71af7be
