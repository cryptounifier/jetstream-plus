<?php

namespace CryptoUnifier\JetstreamPlus;

use Jenssegers\Agent\Agent;

class UserAgent
{
    public const DEVICE_TYPE_UNKNOWN = 0;
    public const DEVICE_TYPE_DESKTOP = 1;
    public const DEVICE_TYPE_MOBILE = 2;
    public const DEVICE_TYPE_TABLET = 3;

    protected Agent $agent;

    public function __construct(string $userAgent, array $headers = [])
    {
        $this->agent = new Agent($headers, $userAgent);
    }

    public static function currentRequest(): self
    {
        return new self(substr((string) optional(request())->header('User-Agent'), 0, 500), request()->header());
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->agent, $method)) {
            return call_user_func_array([$this->agent, $method], $arguments);
        }
    }

    public function platformName(): string
    {
        $platform = $this->agent->platform();
        $version = $this->agent->version($platform);

        return trim("{$platform} ".(($version) ?: __('Unknown')));
    }

    public function browserName(): string
    {
        $browser = $this->agent->browser();
        $version = $this->agent->version($browser);

        return trim("{$browser} ".(($version) ?: __('Unknown')));
    }

    public function deviceType(): int
    {
        if ($this->agent->isDesktop()) {
            return self::DEVICE_TYPE_DESKTOP;
        }

        if ($this->agent->isMobile()) {
            return self::DEVICE_TYPE_MOBILE;
        }

        if ($this->agent->isTablet()) {
            return self::DEVICE_TYPE_TABLET;
        }

        return self::DEVICE_TYPE_UNKNOWN;
    }
}
