<?php

namespace CryptoUnifier\JetstreamPlus;

use CryptoUnifier\JetstreamPlus\Actions\NotifySignInDetected;
use CryptoUnifier\JetstreamPlus\Http\Controllers\UserProfileController;

use Laravel\Fortify\{Features, Fortify};
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Actions\{AttemptToAuthenticate, EnsureLoginIsNotThrottled, PrepareAuthenticatedSession};

use Illuminate\Http\Request;

use Illuminate\Contracts\Http\Kernel;

use Illuminate\Support\ServiceProvider;

class JetstreamPlusServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/captcha.php', 'captcha');
        $this->mergeConfigFrom(__DIR__ . '/../config/ip_address.php', 'ip_address');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePublishing();
        $this->bootInertia();

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
            __DIR__ . '/../config/captcha.php'    => config_path('captcha.php'),
            __DIR__ . '/../config/ip_address.php' => config_path('ip_address.php'),
        ], 'jetstream-utils-configs');

        $this->publishes([
            __DIR__ . '/../database/migrations/2014_10_12_000000_create_users_table.php'        => database_path('migrations/2014_10_12_000000_create_users_table.php'),
            __DIR__ . '/../database/migrations/2021_06_28_133032_create_ip_addresses_table.php' => database_path('migrations/2021_06_28_133032_create_ip_addresses_table.php'),
        ], 'jetstream-utils-migrations');
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
    }

    /**
     * Fortify service provider boot.
     */
    public function fortifyServiceProviderBoot(): void
    {
        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
                Features::enabled('sign-in-notification') ? NotifySignInDetected::class : null,
            ]);
        });
    }

    /**
     * JetStream service provider boot.
     */
    public function jetstreamServiceProviderBoot(): void
    {
        $this->app->singleton(\Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController::class, UserProfileController::class);
    }
}
