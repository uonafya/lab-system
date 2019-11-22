<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['namespace' => 'App\\Api\\V1\\Controllers'], function(Router $api) {
        $api->group(['prefix' => 'auth'], function(Router $api) {
            $api->post('signup', 'SignUpController@signUp');

            // $api->group(['middleware' => 'api.throttle', 'limit' => 1, 'expires' => 1], function(Router $api) {
                $api->post('login', 'LoginController@login');
            // });

            $api->post('recovery', 'ForgotPasswordController@sendResetEmail');
            $api->post('reset', 'ResetPasswordController@resetPassword');

            $api->post('logout', 'LogoutController@logout');
            $api->post('refresh', 'RefreshController@refresh');
            $api->get('me', 'UserController@me');
        });

        $api->get('hello', 'RandomController@hello');
        $api->get('hello_nascop', 'RandomController@hello_nascop');


        $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
            $api->get('protected', 'RandomController@protected_route');

            $api->group(['middleware' => 'jwt.refresh'], function(Router $api) {
                $api->get('refresh', 'RandomController@refresh_route');
            });

            $api->post('email', 'RandomController@email');
            $api->resource('facility', 'FacilityController');

            $api->resource('batch', 'BatchController');
            $api->resource('viralbatch', 'ViralbatchController');

            $api->resource('patient', 'PatientController');
            $api->resource('viralpatient', 'ViralpatientController');

            $api->post('sample/transfer', 'SampleController@transfer');
            $api->resource('sample', 'SampleController');
            
            $api->post('viralsample/transfer', 'ViralsampleController@transfer');
            $api->resource('viralsample', 'ViralsampleController');

            $api->put('allocation', 'AllocationController@update');
            $api->resource('allocation', 'AllocationController');
        });

        
        // $api->group(['middleware' => 'jwt.auth'], function(Router $api) {


            $api->post('eid', 'EidController@eid');    
            $api->post('eid_complete', 'EidController@complete_result');  

            $api->post('vl', 'VlController@vl');        
            $api->post('vl_complete', 'VlController@complete_result');

            $api->post('cd4', 'Cd4Controller@partial');
            $api->post('cd4_complete', 'Cd4Controller@complete_result');

            $api->post('crag', 'CragController@partial');
            // $api->post('crag_complete', 'CragController@complete_result');

            // $api->group(['middleware' => 'api.throttle', 'limit' => 100, 'expires' => 1], function(Router $api) {
                $api->post('function', 'FunctionController@data_functions');
            // });
            
        // });
    });


});