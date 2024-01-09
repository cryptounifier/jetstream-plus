<?php

namespace CryptoUnifier\JetstreamPlus\Http\Middleware;

use Closure;
use CryptoUnifier\Helpers\CaptchaValidator;
use CryptoUnifier\Helpers\IpAddress;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class ExtraValidationOnAuthRoutes
{
    protected array $routes = [
        'login', 'register', 'password.email', 'verification.send'
    ];

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Validate request method... && Validate request route...

        if ($request->method() === 'POST' && in_array($this->getCurrentRouteName($request), $this->routes)) {
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

        if (! CaptchaValidator::defaultDriver()->validate($captchaToken, $request->ip(), str_replace('.', '_', $this->getCurrentRouteName($request)))) {
            $messageBag = new MessageBag();
            $messageBag->add('captcha', __('Invalid captcha answer. Please complete the challenge correctly.'));

            return back()->withErrors($messageBag);
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
            $messageBag = new MessageBag();
            $messageBag->add('ip_address', __('VPS, VPN or Proxy detected! Please disable any type of service that may mask your IP to proceed.'));

            return back()->withErrors($messageBag);
        }
    }

    protected function getCurrentRouteName(Request $request): string
    {
        return $request->route()?->getName() ?? '';
    }
}
