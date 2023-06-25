<?php

namespace CryptoUnifier\JetstreamPlus\Http\Controllers;

use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController as BaseTwoFactorAuthenticatedSessionController;

class TwoFactorAuthenticatedSessionController extends BaseTwoFactorAuthenticatedSessionController
{
/**
     * Attempt to authenticate a new session using the two factor authentication code.
     *
     * @param  \Laravel\Fortify\Http\Requests\TwoFactorLoginRequest  $request
     * @return mixed
     */
    public function store(TwoFactorLoginRequest $request)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new RecoveryCodeReplaced($user, $code));
        } elseif (! $request->hasValidCode()) {
            return app(FailedTwoFactorLoginResponse::class)->toResponse($request);
        }

        // Overwrite the default controller to add the notification
        if (Features::enabled('sign-in-notification')) {
            $user->notify(new \CryptoUnifier\JetstreamPlus\Notifications\SignInDetected($request));
        }

        $this->guard->login($user, $request->remember());

        $request->session()->regenerate();

        return app(TwoFactorLoginResponse::class);
    }
}