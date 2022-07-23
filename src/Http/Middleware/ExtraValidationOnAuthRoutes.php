<?php

namespace CryptoUnifier\JetstreamPlus\Http\Middleware;

use Closure;
use CryptoUnifier\JetstreamPlus\CaptchaValidator;
use CryptoUnifier\JetstreamPlus\IpAddress;
use Illuminate\Http\Request;

class ExtraValidationOnAuthRoutes
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Validate request method... && Validate request route...

        if ($request->method() === 'POST' && (in_array($request->segment(1), ['login', 'register', 'forgot-password']) || in_array($request->segment(2), ['verification-notification']))) {
            if (config('captcha.on_auth')) {
                if ($captchaResponse = $this->validateCaptcha($request)) {
                    return $captchaResponse;
                }
            }

            if (config('ip_address.on_auth')) {
                if ($ipResponse = $this->validateIpAddress($request)) {
                    return $ipResponse;
                }
            }
        }

        return $next($request);
    }

    /**
     * Validate captcha function.
     *
     * @return mixed
     */
    protected function validateCaptcha(Request $request)
    {
        $captchaToken = (string) $request->input('captcha_token');

        if (! CaptchaValidator::validate($captchaToken)) {
            return back()->withErrors(
                __('Invalid captcha answer. Please complete the challenge correctly.'),
                'captcha'
            );
        }
    }

    /**
     * Validate ip address function.
     *
     * @return mixed
     */
    protected function validateIpAddress(Request $request)
    {
        $isProxy = IpAddress::find($request->ip())->proxy;

        if ($isProxy) {
            return back()->withErrors(
                __('VPS, VPN or Proxy detected! Please disable any type of service that may mask your IP to proceed.'),
                'ipAddress'
            );
        }
    }
}
