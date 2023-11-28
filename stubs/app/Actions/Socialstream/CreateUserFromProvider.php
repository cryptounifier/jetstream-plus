<?php

namespace App\Actions\Socialstream;

use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\Contracts\{CreatesConnectedAccounts, CreatesUserFromProvider};
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Contracts\User as ProviderUser;

class CreateUserFromProvider implements CreatesUserFromProvider
{
    /**
     * The creates connected accounts instance.
     *
     * @var \JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts
     */
    public $createsConnectedAccounts;

    /**
     * Create a new action instance.
     */
    public function __construct(CreatesConnectedAccounts $createsConnectedAccounts)
    {
        $this->createsConnectedAccounts = $createsConnectedAccounts;
    }

    /**
     * Create a new user from a social provider user.
     *
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress RedundantConditionGivenDocblockType
     *
     * @return \App\Models\User
     */
    public function create(string $provider, ProviderUser $providerUser)
    {
        $userName = $providerUser->getName() ?? $providerUser->getNickname();
        $userName = Str::limit(Str::ucfirst(Str::camel(Str::slug($userName, ' '))), 25, '');

        if (! $userName || User::where('name', $userName)->count() > 0) {
            $userName = 'User' . Str::random(10);
        }

        return DB::transaction(function () use ($provider, $providerUser, $userName) {
            return tap(User::create([
                'name'       => $userName,
                'email'      => $providerUser->getEmail(),
                'ip_address' => optional(request())->ip(),
            ]), function (User $user) use ($provider, $providerUser): void {
                $user->markEmailAsVerified();

                if (Socialstream::hasProviderAvatarsFeature() && Jetstream::managesProfilePhotos() && $providerUser->getAvatar()) {
                    $user->setProfilePhotoFromUrl($providerUser->getAvatar());
                }

                $this->createsConnectedAccounts->create($user, $provider, $providerUser);
            });
        });
    }
}
