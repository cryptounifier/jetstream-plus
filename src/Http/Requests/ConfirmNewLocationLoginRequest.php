<?php

namespace CryptoUnifier\JetstreamPlus\Http\Requests;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class ConfirmNewLocationLoginRequest extends FormRequest
{
    /**
     * The user attempting the two factor challenge.
     *
     * @var mixed
     */
    protected $challengedUser;

    /**
     * Indicates if the user wished to be remembered after login.
     *
     * @var bool
     */
    protected $remember;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'nullable|string',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the valid confirmation code if one exists on the request.
     */
    public function isConfirmationCodeValid(): bool
    {
        if (! $this->code) {
            return false;
        }

        if (! $this->session()->has('login.confirmation.code')) {
            return false;
        }

        $isValid = $this->session()->get('login.confirmation.code') === $this->code;

        if ($isValid) {
            $this->forgetChallenge();
        }

        return $isValid;
    }

    /**
     * Determine if there is a challenged user in the current session.
     */
    public function hasChallengedUser(): bool
    {
        if ($this->challengedUser) {
            return true;
        }

        if (! $this->session()->has('login.confirmation.expires_at')) {
            return false;
        }
        
        if ($this->session()->get('login.confirmation.expires_at') < now()) {
            $this->forgetChallenge();

            return false;   
        }

        $model = app(StatefulGuard::class)->getProvider()->getModel();

        return $this->session()->has('login.confirmation.id') &&
            $model::find($this->session()->get('login.confirmation.id'));
    }

    /**
     * Get the user that is attempting the two factor challenge.
     */
    public function challengedUser(): mixed
    {
        if ($this->challengedUser) {
            return $this->challengedUser;
        }

        $model = app(StatefulGuard::class)->getProvider()->getModel();

        if (! $this->session()->has('login.confirmation.id') ||
            ! $user = $model::find($this->session()->get('login.confirmation.id'))) {
            return null;
        }

        return $this->challengedUser = $user;
    }

    /**
     * Determine if the user wanted to be remembered after login.
     */
    public function remember(): bool
    {
        if (! $this->remember) {
            $this->remember = $this->session()->pull('login.confirmation.remember', false);
        }

        return $this->remember;
    }

    /**
     * Forget challenge from session.
     */
    protected function forgetChallenge(): void
    {
        $this->session()->forget([
            'login.confirmation.id', 
            'login.confirmation.code',
            'login.confirmation.remember',
            'login.confirmation.expires_at',
        ]);
    }
}
