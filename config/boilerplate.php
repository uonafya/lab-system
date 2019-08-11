<?php

use App\Rules\BeforeOrEqual;

return [

    // these options are related to the sign-up procedure
    'sign_up' => [

        // this option must be set to true if you want to release a token
        // when your user successfully terminates the sign-in procedure
        'release_token' => env('SIGN_UP_RELEASE_TOKEN', false),

        // here you can specify some validation rules for your sign-in request
        'validation_rules' => [
            'name' => 'required',
            'email' => 'required', 'email',
            'password' => 'required'
        ]
    ],

    // these options are related to the login procedure
    'login' => [

        // here you can specify some validation rules for your login request
        'validation_rules' => [
            'email' => 'required', 'email',
            'password' => 'required'
        ]
    ],

    // these options are related to the password recovery procedure
    'forgot_password' => [

        // here you can specify some validation rules for your password recovery procedure
        'validation_rules' => [
            'email' => 'required', 'email'
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
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed']
        ]
    ],

    'sample_base' => [
        // 'dob' => ['date_format:Y-m-d', 'required', new BeforeOrEqual($this->input('datecollected'), 'datecollected')],
        'dob' => ['required', 'before_or_equal:today', 'date_format:Y-m-d'],
        'datecollected' => ['required', 'before_or_equal:today', 'date_format:Y-m-d'],
        'patient_identifier' => 'required',
        'mflCode' => ['required', 'integer', 'digits:5', 'exists:facilitys,facilitycode'], 
        'sex' => ['required', 'integer', 'max:3'], 
        'lab' => 'integer',
        'amrs_location' => 'integer',

        // 'patient_phone_no' => ["regex:/[2][5][4][7][0-9]{8}/", 'digits:12', ], 
        'preferred_language' => ['integer', 'between:1,2'],

    ],

    'complete_result' => [
        
        'datereceived' => ['required', 'before_or_equal:today', 'date_format:Y-m-d'],
        'datetested' => ['date_format:Y-m-d', 'before_or_equal:today', 'required_if:receivedstatus,==,1'],
        'datedispatched' => ['date_format:Y-m-d', 'before_or_equal:today', 'required_if:receivedstatus,==,1'],

        'editted' => ['filled', 'integer'],
        'result' => 'required_if:receivedstatus,==,1',
        'lab' => ['required', 'integer'],
        // 'gender' => 'filled',
    ], 

    'eid' => [
        'hiv_status' => 'integer',
        'entry_point' => 'integer',
        'spots' => 'integer',
        'feeding' => ['required', 'integer'],
        'regimen' => ['required', 'integer', 'max:30'],
        'mother_prophylaxis' => ['required', 'integer', 'max:30'],
        'mother_age' => ['integer', 'between:10,70'],
        // 'pcrtype' => ['required', 'integer', 'between:1,5'], 
        'pcrtype' => ['integer', 'between:1,5'], 
        'redraw' => 'integer', 
    ],

    'vl' => [
        'initiation_date' => ['date_format:Y-m-d', 'before_or_equal:today','after_or_equal:1990-01-01'],
        'dateinitiatedonregimen' => ['date_format:Y-m-d', 'before_or_equal:today',],
        'prophylaxis' => ['required', 'integer', 'max:50'],
        // 'regimenline' => ['required', 'integer', 'max:10'],
        'sampletype' => ['required', 'integer', 'between:1,3'],
        'justification' => ['required', 'integer', 'max:15'],
        'pmtct' => ['integer', 'between:1,3', 'required_if:sex,==,2'],
    ],

    'cd4' => [
        // 'dob' => ['date_format:Y-m-d', 'required', new BeforeOrEqual($this->input('datecollected'), 'datecollected')],
        'dob' => ['required', 'before_or_equal:today', 'date_format:Y-m-d'],
        'datecollected' => ['required', 'before_or_equal:today', 'date_format:Y-m-d'],
        'mflCode' => ['required', 'integer', 'digits:5', 'exists:facilitys,facilitycode'], 
        'sex' => ['required', 'integer', 'max:3'], 
        'lab' => 'integer',
        'amrs_location' => 'integer',
    ],

    'form_base' => [
        'patient' => 'required',
        'facility_id' => ['required', 'integer'], 
        'dob' => ['required_without:age', 'before_or_equal:today', 'date_format:Y-m-d'],
        'datecollected' => ['required', 'after_or_equal:-6month', 'before_or_equal:today', 'date_format:Y-m-d'],
        'datedispatchedfromfacility' => ['after_or_equal:-6month', 'before_or_equal:+7days', 'date_format:Y-m-d'],
        'sex' => ['required', 'integer', 'between:1,2'], 
        'amrs_location' => 'integer',

        // 'patient_phone_no' => ["regex:/[2][5][4][7][0-9]{8}/", 'digits:12', ], 
        'preferred_language' => ['integer', 'between:1,2'],

    ],

    'lab_user' => [
        'datereceived' => ['required', 'after_or_equal:-6month', 'before_or_equal:today', 'date_format:Y-m-d'],
        'receivedstatus' => ['required', 'integer', 'between:1,2'],
        'rejectedreason' => ['required_if:receivedstatus,==,2'],        
    ],



];
