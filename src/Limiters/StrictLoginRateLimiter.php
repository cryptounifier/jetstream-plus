<?php

namespace CryptoUnifier\JetstreamPlus\Limiters;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Fortify\LoginRateLimiter as BaseLoginRateLimiter;

class StrictLoginRateLimiter extends BaseLoginRateLimiter
{
    /**
     * Determine if the user has too many failed login attempts.
     *
     * @return bool
     */
    public function tooManyAttempts(Request $request)
    {
        return $this->limiter->tooManyAttempts($this->throttleKey($request), 5);
    }

    /**
     * Increment the login attempts for the user.
     *
     * @return void
     */
    public function increment(Request $request)
    {
        $this->limiter->hit($this->throttleKey($request), 60 * 3);
    }

    /**
     * Get the throttle key for the given request.
     *
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return Str::transliterate(implode('.', ['login', $request->ip()]));
    }
}
