<?php

namespace CryptoUnifier\JetstreamPlus\Actions;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Actions\AttemptToAuthenticate as BaseAttemptToAuthenticate;
use Laravel\Fortify\Fortify;

class AttemptToAuthenticate extends BaseAttemptToAuthenticate
{
     /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        if (Fortify::$authenticateUsingCallback) {
            return $this->handleUsingCustomCallback($request, $next);
        }

        // Fix Socialstream credential validation
        if ($request->route('provider')) {
            $socialUser = app(ResolvesSocialiteUsers::class)
                ->resolve($request->route('provider'));

            $connectedAccount = tap(Socialstream::$connectedAccountModel::where('email', $socialUser->getEmail())->first(), function ($connectedAccount) use ($request, $socialUser) {
                if (! $connectedAccount) {
                    $this->fireFailedEvent($request, $connectedAccount->user);

                    $this->throwFailedAuthenticationException($request);
                }
            });

            $this->guard->login($connectedAccount->user, Socialstream::hasRememberSessionFeatures());

            return $next($request);
        }

        if ($this->guard->attempt(
            $request->only(Fortify::username(), 'password'),
            $request->boolean('remember'))
        ) {
            return $next($request);
        }

        $this->throwFailedAuthenticationException($request);
    }
}