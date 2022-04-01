<?php

namespace CryptoUnifier\JetstreamPlus\Http\Middleware;

use Inertia\Inertia;

class ShareInertiaData
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param callable                 $next
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        Inertia::share([
            'app' => [
                'name' => config('app.name'),
            ],
            'captcha' => [
                'driver'  => config('captcha.driver'),
                'onAuth'  => config('captcha.on_auth'),
                'siteKey' => config('captcha.site_key'),
            ],
        ]);

        return $next($request);
    }
}
