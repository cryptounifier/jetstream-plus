<?php

namespace App\Actions\Socialstream;

use App\Actions\Fortify\PasswordValidationRules;

use Illuminate\Support\Facades\{Hash, Validator};
use JoelButcher\Socialstream\Contracts\SetsUserPasswords;

class SetUserPassword implements SetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param mixed $user
     */
    public function set($user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validateWithBag('setPassword');

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
