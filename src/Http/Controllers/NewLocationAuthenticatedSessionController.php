<?php

namespace CryptoUnifier\JetstreamPlus\Http\Controllers;

use CryptoUnifier\JetstreamPlus\Contracts\ConfirmNewLocationViewResponse;
use CryptoUnifier\JetstreamPlus\Http\Requests\ConfirmNewLocationLoginRequest;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class NewLocationAuthenticatedSessionController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Show the confirm new location view.
     *
     * @return mixed
     */
    public function create(ConfirmNewLocationLoginRequest $request)
    {
        if (! $request->hasChallengedUser()) {
            throw new HttpResponseException(redirect()->route('login'));
        }

        return app(ConfirmNewLocationViewResponse::class);
    }

    /**
     * Attempt to authenticate a new session using the confirm new location code.
     *
     * @return mixed
     */
    public function store(ConfirmNewLocationLoginRequest $request)
    {
        $user = $request->challengedUser();

        if (! $user) {
            throw new HttpResponseException(redirect()->route('login'));
        }

        if (! $request->isConfirmationCodeValid()) {
            [$key, $message] = ['code', __('The provided confirmation code was invalid.')];

            if ($request->wantsJson()) {
                throw ValidationException::withMessages([
                    $key => [$message],
                ]);
            }

            return redirect()->route('confirm-new-location.login')->withErrors([$key => $message]);
        }

        if (Features::enabled('sign-in-notification')) {
            $user->notify(new \CryptoUnifier\JetstreamPlus\Notifications\SignInDetected($request));
        }

        $this->guard->login($user, $request->remember());

        $request->forgetChallengeData();
        $request->session()->regenerate();

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended(Fortify::redirects('login'));
    }
}
