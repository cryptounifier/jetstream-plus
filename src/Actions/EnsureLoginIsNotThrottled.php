<?php

namespace CryptoUnifier\JetstreamPlus\Actions;

use CryptoUnifier\JetstreamPlus\Limiters\StrictLoginRateLimiter;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled as BaseEnsureLoginIsNotThrottled;

class EnsureLoginIsNotThrottled extends BaseEnsureLoginIsNotThrottled
{
    /**
     * The login rate limiter instance.
     *
     * @var \CryptoUnifier\JetstreamPlus\Limiters\StrictLoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new class instance.
     *
     * @param  \CryptoUnifier\JetstreamPlus\Limiters\StrictLoginRateLimiter $limiter
     * @return void
     */
    public function __construct(StrictLoginRateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }
}
