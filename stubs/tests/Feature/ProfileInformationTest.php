<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function testProfileInformationCanBeUpdated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put('/user/profile-information', [
            'name'  => 'TestName',
            'email' => 'test@example.com',
        ]);

        self::assertSame('TestName', $user->fresh()->name);
        self::assertSame('test@example.com', $user->fresh()->email);
    }
}
