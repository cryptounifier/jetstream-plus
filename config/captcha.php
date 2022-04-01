<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Captcha On Authentication
    |--------------------------------------------------------------------------
    |
    | The Captcha should be enabled & verified on authentication routes?
    |
    */
    'on_auth' => env('CAPTCHA_ON_AUTH', false),

    /*
    |--------------------------------------------------------------------------
    | Captcha Driver
    |--------------------------------------------------------------------------
    |
    | Captcha driver service name.
    |
    */
    'driver' => env('CAPTCHA_DRIVER', 'hcaptcha'),

    /*
    |--------------------------------------------------------------------------
    | Captcha Site Key
    |--------------------------------------------------------------------------
    |
    | Captcha generated site key.
    |
    */
    'site_key' => env('CAPTCHA_SITE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Captcha Secret Key
    |--------------------------------------------------------------------------
    |
    | Captcha generated secret key.
    |
    */
    'secret_key' => env('CAPTCHA_SECRET_KEY'),
];
