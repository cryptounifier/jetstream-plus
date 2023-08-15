<?php

namespace CryptoUnifier\JetstreamPlus\Actions;

use CryptoUnifier\Helpers\IpAddress;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;

class RedirectIfNewLocationConfirmationNeeded extends RedirectIfTwoFactorAuthenticatable
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
        $user = $this->validateCredentials($request);

        $userInfo = IpAddress::find($user->last_ip_address ?? $user->ip_address);
        $requestIpInfo = IpAddress::currentRequest();

        if (!$userInfo->location || !$requestIpInfo->location || $userInfo->location !== $requestIpInfo->location) {
            return $this->newDeviceConfirmationResponse($request, $user);
        }

        return $next($request);
    }

     /**
     * Get the new device detected response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function newDeviceConfirmationResponse($request, $user)
    {
        $confirmationCode = strtoupper(Str::random(6));

        $request->session()->put([
            'login.confirmation.id' => $user->getKey(),
            'login.confirmation.remember' => $request->boolean('remember'),
            'login.confirmation.code' => $confirmationCode,
            'login.confirmation.expires_at' => now()->addMinutes(30),
        ]);

        $user->notify(new \CryptoUnifier\JetstreamPlus\Notifications\NewLocationConfirmation($request, $confirmationCode));

        return $request->wantsJson()
                    ? response()->json(['confirm_new_location' => true])
                    : redirect()->route('confirm-new-location.login');
    }
}