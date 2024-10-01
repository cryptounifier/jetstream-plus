<?php

namespace CryptoUnifier\JetstreamPlus\Actions;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as BaseRedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class RedirectIfTwoFactorAuthenticatable extends BaseRedirectIfTwoFactorAuthenticatable
{
    /**
     * Attempt to validate the incoming credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function validateCredentials($request)
    {
        if (Fortify::$authenticateUsingCallback) {
            return tap(call_user_func(Fortify::$authenticateUsingCallback, $request), function ($user) use ($request) {
                if (! $user) {
                    $this->fireFailedEvent($request);

                    $this->throwFailedAuthenticationException($request);
                }
            });
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

            return $connectedAccount->user;
        }

        $model = $this->guard->getProvider()->getModel();

        return tap($model::where(Fortify::username(), $request->{Fortify::username()})->first(), function ($user) use ($request) {
            if (! $user || ! $this->guard->getProvider()->validateCredentials($user, ['password' => $request->password])) {
                $this->fireFailedEvent($request, $user);

                $this->throwFailedAuthenticationException($request);
            }
        });
    }
}