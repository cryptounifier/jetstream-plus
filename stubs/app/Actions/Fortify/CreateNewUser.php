<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\{Hash, Validator};
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name'     => ['required', 'string', 'alpha_num', 'min:2', 'max:25', 'unique:users'],
            'email'    => ['required', 'string', 'email:rfc,strict,filter', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms'    => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();

        if (User::where('ip_address', optional(request())->ip())->count() > 8) {
            return back()->dangerBanner(__('Apparently you already have many accounts on the website.'));
        }

        return User::create([
            'name'       => $input['name'],
            'email'      => $input['email'],
            'password'   => Hash::make($input['password']),
            'ip_address' => optional(request())->ip(),
        ]);
    }
}
