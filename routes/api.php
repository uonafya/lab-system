<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['namespace' => 'App\\Api\\V1\\Controllers'], function(Router $api) {
        $api->group(['prefix' => 'auth'], function(Router $api) {
            $api->post('signup', 'SignUpController@signUp');
            $api->post('login', 'LoginController@login');

            $api->post('recovery', 'ForgotPasswordController@sendResetEmail');
            $api->post('reset', 'ResetPasswordController@resetPassword');

            $api->post('logout', 'LogoutController@logout');
            $api->post('refresh', 'RefreshController@refresh');
            $api->get('me', 'UserController@me');
        });

        $api->get('hello', 'RandomController@hello');


        $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
            $api->get('protected', 'RandomController@protected_route');

            $api->group(['middleware' => 'jwt.refresh'], function(Router $api) {
                $api->get('refresh', 'RandomController@refresh_route');
            });
        });

        
        // $api->group(['middleware' => 'jwt.auth'], function(Router $api) {


            $api->post('eid', 'EidController@eid');        
            $api->post('eid_complete', 'EidController@complete_result');  

            $api->post('vl', 'VlController@vl');        
            $api->post('vl_complete', 'VlController@complete_result');

            $api->post('cd4', 'Cd4Controller@partial');

            $api->post('function', 'FunctionController@api');

            $api->resource('facility', 'FacilityController');
        // });
    });


});
