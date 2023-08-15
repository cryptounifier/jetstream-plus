<?php

namespace CryptoUnifier\JetstreamPlus;

use CryptoUnifier\JetstreamPlus\Actions\NotifySignInDetected;
use CryptoUnifier\JetstreamPlus\Actions\RedirectIfNewLocationConfirmationNeeded;
use CryptoUnifier\JetstreamPlus\Contracts\ConfirmNewLocationViewResponse;
use CryptoUnifier\JetstreamPlus\Http\Controllers\TwoFactorAuthenticatedSessionController;
use CryptoUnifier\JetstreamPlus\Http\Controllers\UserProfileController;

use Laravel\Fortify\{Features, Fortify};
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Actions\{AttemptToAuthenticate, EnsureLoginIsNotThrottled, PrepareAuthenticatedSession};

use Illuminate\Http\Request;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;

class JetstreamPlusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePublishing();
        $this->configureCommands();
        $this->configureRoutes();

        if (config('jetstream.stack') === 'inertia') {
            $this->bootInertia();
        }

        // Bind extra fortify provider...
        $this->fortifyServiceProviderBoot();

        // Bind extra jetstream provider...
        $this->jetstreamServiceProviderBoot();
    }

    /**
     * Configure publishing for the package.
     */
    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../database/migrations/2014_10_12_000000_create_users_table.php'        => database_path('migrations/2014_10_12_000000_create_users_table.php'),
        ], 'jetstream-plus-migrations');
    }

    /**
     * Configure the commands offered by the application.
     */
    protected function configureCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
        ]);
    }

    /**
     * Configure the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        if (Fortify::$registersRoutes) {
            Route::group([
                'namespace' => 'Laravel\Fortify\Http\Controllers',
                'domain' => config('fortify.domain', null),
                'prefix' => config('fortify.prefix'),
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
            });
        }
    }

    /**
     * Boot any Inertia related services.
     */
    protected function bootInertia(): void
    {
        $kernel = $this->app->make(Kernel::class);

        $kernel->appendMiddlewareToGroup('web', Http\Middleware\ShareInertiaData::class);
        $kernel->appendToMiddlewarePriority(Http\Middleware\ShareInertiaData::class);

        $kernel->appendMiddlewareToGroup('web', Http\Middleware\ExtraValidationOnAuthRoutes::class);
        $kernel->appendToMiddlewarePriority(Http\Middleware\ExtraValidationOnAuthRoutes::class);

        // Extra pages
        app()->singleton(ConfirmNewLocationViewResponse::class, function ()  {
            return new SimpleViewResponse(function () {
                return \Inertia\Inertia::render('Auth/ConfirmNewLocation');
            });
        });
    }

    /**
     * Fortify service provider boot.
     */
    public function fortifyServiceProviderBoot(): void
    {
        $this->app->singleton(\Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController::class, TwoFactorAuthenticatedSessionController::class);

        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
                Features::enabled('confirm-new-location') ? RedirectIfNewLocationConfirmationNeeded::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
                Features::enabled('sign-in-notification') ? NotifySignInDetected::class : null,
            ]);
        });
    }

    /**
     * Jetstream service provider boot.
     */
    public function jetstreamServiceProviderBoot(): void
    {
        $this->app->singleton(\Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController::class, UserProfileController::class);
    }
}
