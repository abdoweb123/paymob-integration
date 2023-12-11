<?php


return [

    /*
    |--------------------------------------------------------------------------
    | PayMob username and password
    |--------------------------------------------------------------------------
    |
    | This is your PayMob username and password to make auth request.
    |
    */

    'username' => env('PAYMOB_USERNAME', 'default'),
    'password' => env('PAYMOB_PASSWORD', 'default'),
    'integration_id' => env('PAYMOB_INTEGRATION_ID', 'default'),
    'hmac' => env('PAYMOB_HMAC', 'default'),

    /*
    |--------------------------------------------------------------------------
    | PayMob integration id
    |--------------------------------------------------------------------------
    |
    | This is your PayMob integration id.
    |
    */

//    'integration_id' => env('PayMob_Integration_Id'),
];
