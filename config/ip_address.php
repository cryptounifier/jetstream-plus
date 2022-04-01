<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IpAddress On Authentication
    |--------------------------------------------------------------------------
    |
    | The proxy verification should be enabled on authentication routes.
    |
    */
    'on_auth' => env('IP_ADDRESS_ON_AUTH', false),

    /*
    |--------------------------------------------------------------------------
    | IpAddress Driver Name
    |--------------------------------------------------------------------------
    |
    | IPInfo driver service name.
    |
    */
    'driver' => env('IP_ADDRESS_DRIVER', 'proxycheck'),

    /*
    |--------------------------------------------------------------------------
    | IpAddress Authentication Key
    |--------------------------------------------------------------------------
    |
    | IPInfo generated private key.
    |
    */
    'key' => env('IP_ADDRESS_DRIVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | IpAddress Data Duration Time
    |--------------------------------------------------------------------------
    |
    | IPInfo data duration until it get revalidated (in seconds).
    |
    */
    'data_duration' => 60 * 60 * 24 * 7,
];
