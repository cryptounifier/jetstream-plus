<?php

use CryptoUnifier\JetstreamPlus\Http\Controllers\NewLocationAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\RoutePath;

Route::group(['middleware' => config('fortify.middleware', ['web'])], function () {
    $enableViews = config('fortify.views', true);

    $limiter = config('fortify.limiters.login');

    if (Features::enabled('confirm-new-location')) {
        if ($enableViews) {
            Route::get(RoutePath::for('confirm-new-location.login', '/confirm-new-location'), [NewLocationAuthenticatedSessionController::class, 'create'])
                ->middleware(['guest:'.config('fortify.guard')])
                ->name('confirm-new-location.login');
        }

        Route::post(RoutePath::for('confirm-new-location.login', '/confirm-new-location'), [NewLocationAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest:'.config('fortify.guard'),
                $limiter ? 'throttle:'.$limiter : null,
            ]));
    }
});