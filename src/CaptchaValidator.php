<?php

namespace CryptoUnifier\JetstreamPlus;

use Illuminate\Support\Facades\Http;

class CaptchaValidator
{
    public static function validate(string $token): bool
    {
        if (config('captcha.driver') === 'hcaptcha') {
            $captcha = (object) Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'secret'   => config('captcha.secret_key'),
                'response' => $token,
            ])->json();

            return optional($captcha)->success;
        }

        return false;
    }
}
