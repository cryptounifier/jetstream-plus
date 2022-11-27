<?php

namespace CryptoUnifier\JetstreamPlus;

use Illuminate\Support\Facades\Http;

class CaptchaValidator
{
    /**
     * Construct method.
     */
    public function __construct(protected string $driver, protected string $secretKey, protected string $siteKey)
    {
    }

    /**
     * Instantiate class with default driver and configs.
     */
    public static function defaultDriver(): static
    {
        return new static(config('captcha.driver'), config('captcha.secret_key'), config('captcha.site_key'));
    }

    /**
     * Validate captcha token response.
     */
    public function validate(string $token): bool
    {
        return match ($this->driver) {
            'hcaptcha' => $this->validateHcaptcha($token),
            'recaptcha' => $this->validateReCaptcha($token),
            'geetest' => $this->validateGeeTest($token),
            'turnstile' => $this->validateTurnstile($token),
            default => false,
        };
    }

    /**
     * Validate using hCaptcha driver.
     */
    protected function validateHCaptcha(string $token): bool
    {
        $captcha = (object) Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret'   => $this->secretKey,
            'response' => $token,
        ])->json();

        return optional($captcha)->success;
    }

    /**
     * Validate using reCaptcha driver.
     */
    protected function validateReCaptcha(string $token): bool
    {
        $captcha = (object) Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => $this->secretKey,
            'response' => $token,
        ])->json();

        return optional($captcha)->success;
    }

    /**
     * Validate using Turnstile driver.
     */
    protected function validateTurnstile(string $token): bool
    {
        $captcha = (object) Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => $this->secretKey,
            'response' => $token,
        ])->json();

        return optional($captcha)->success;
    }

    /**
     * Validate using GeeTest driver.
     */
    protected function validateGeeTest(string $token): bool
    {
        $token = explode('.', $token);

        if (count($token) !== 4) {
            return false;
        }

        $signToken = hash_hmac('sha256', $token[0], $this->secretKey);

        $captcha = (object) Http::asForm()->post('http://gcaptcha4.geetest.com/validate', [
            'lot_number'   => $token[0],
            'captcha_output' => $token[1],
            'pass_token' => $token[2],
            'gen_time'  => $token[3],
            'sign_token' => $signToken,
            'captcha_id' => $this->siteKey,
        ])->json();

        return optional($captcha)->result === 'success';
    }
}
