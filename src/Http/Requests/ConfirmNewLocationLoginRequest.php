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
     *
     * @return string|null
     */
    public function validConfirmationCode()
    {
        if (! $this->code) {
            return;
        }

        $isValid = $this->session()->has('login.confirmation.code');

        if ($isValid) {
            $this->session()->forget('login.confirmation.id');
            $this->session()->forget('login.confirmation.code');
        }

        return $isValid;
    }

    /**
     * Determine if there is a challenged user in the current session.
     *
     * @return bool
     */
    public function hasChallengedUser()
    {
        if ($this->challengedUser) {
            return true;
        }

        $model = app(StatefulGuard::class)->getProvider()->getModel();

        return $this->session()->has('login.confirmation.id') &&
            $model::find($this->session()->get('login.confirmation.id'));
    }

    /**
     * Get the user that is attempting the two factor challenge.
     *
     * @return mixed
     */
    public function challengedUser()
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
     *
     * @return bool
     */
    public function remember()
    {
        if (! $this->remember) {
            $this->remember = $this->session()->pull('login.confirmation.remember', false);
        }

        return $this->remember;
    }
}
