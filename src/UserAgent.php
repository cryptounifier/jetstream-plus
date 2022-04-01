<?php

namespace CryptoUnifier\JetstreamPlus;

use Jenssegers\Agent\Agent;

class UserAgent
{
    protected Agent $agent;

    public function __construct(string $userAgent, array $headers = [])
    {
        $this->agent = new Agent($headers, $userAgent);
    }

    public static function currentUser(): self
    {
        return new self(substr((string) optional(request())->header('User-Agent'), 0, 500), request()->header());
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->agent, $method)) {
            return call_user_func_array([$this->agent, $method], $arguments);
        }
    }

    public function platformName()
    {
        $platform = $this->agent->platform();
        $version = $this->agent->version($platform);

        return trim("{$platform} ".(($version) ?: __('Unknown')));
    }

    public function browserName()
    {
        $browser = $this->agent->browser();
        $version = $this->agent->version($browser);

        return trim("{$browser} ".(($version) ?: __('Unknown')));
    }
}
