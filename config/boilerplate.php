<?php

return [

    // these options are related to the sign-up procedure
    'sign_up' => [

        // this option must be set to true if you want to release a token
        // when your user successfully terminates the sign-in procedure
        'release_token' => env('SIGN_UP_RELEASE_TOKEN', false),

        // here you can specify some validation rules for your sign-in request
        'validation_rules' => [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]
    ],

    // these options are related to the login procedure
    'login' => [

        // here you can specify some validation rules for your login request
        'validation_rules' => [
            'email' => 'required|email',
            'password' => 'required'
        ]
    ],

    // these options are related to the password recovery procedure
    'forgot_password' => [

        // here you can specify some validation rules for your password recovery procedure
        'validation_rules' => [
            'email' => 'required|email'
        ]
    ],

    // these options are related to the password recovery procedure
    'reset_password' => [

        // this option must be set to true if you want to release a token
        // when your user successfully terminates the password reset procedure
        'release_token' => env('PASSWORD_RESET_RELEASE_TOKEN', false),

        // here you can specify some validation rules for your password recovery procedure
        'validation_rules' => [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]
    ],

    'sample_base' => [
        'dob' => 'required|date_format:Y-m-d',
        'datecollected' => 'required|date_format:Y-m-d',
        'patient_identifier' => 'required',
        'mflCode' => 'required|integer|digits:5', 
        'sex' => 'required|integer|max:3', 
    ],

    'complete_result' => [
        
        'datereceived' => 'required|date_format:Y-m-d',
        'datetested' => 'date_format:Y-m-d|required_if:receivedstatus,==,1',
        'datedispatched' => 'date_format:Y-m-d|required_if:receivedstatus,==,1',

        'editted' => 'filled|integer',
        'lab' => 'required|integer',
        'result' => 'required',
        'receivedstatus' => 'required|integer|max:5',
        'rejectedreason' => 'required_if:receivedstatus,==,2',
        // 'gender' => 'filled',
    ], 

    'eid' => [
        'hiv_status' => 'integer',
        'entry_point' => 'integer',
        'spots' => 'integer',
        'feeding' => 'required|integer',
        'regimen' => 'required|integer|max:30',
        'mother_prophylaxis' => 'required|integer|max:30',
        'pcrtype' => 'required|integer|max:7', 
        'redraw' => 'integer|max:1',       
    ],

    'vl' => [
        'initiation_date' => 'date_format:Y-m-d',
        'prophylaxis' => 'required|integer|max:25',
        'regimenline' => 'required|integer|max:10',
        'sampletype' => 'required|integer|max:10',
        'justification' => 'required|integer|max:15',
        'pmtct' => 'integer|max:3|required_if:sex,==,2',
    ],



];
