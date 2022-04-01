<?php

namespace CryptoUnifier\JetstreamPlus\Actions;

class NotifySignInDetected
{
    public function handle($request, $next)
    {
        $request->user()->notify(new \CryptoUnifier\JetstreamPlus\Notifications\SignInDetected());

        return $next($request);
    }
}
