<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_a_farm_owner_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Taylor Farmer',
            'email' => 'taylor@example.test',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'taylor@example.test')
            ->assertJsonPath('data.user.roles.0', 'Farm Owner')
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email', 'roles']]]);
    }

    public function test_user_can_login_and_revoke_their_token(): void
    {
        $user = User::factory()->create(['password' => 'correct-password']);
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);
        $login->assertOk()->assertJsonStructure(['data' => ['token']]);

        $token = $login->json('data.token');
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk();
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $tokenId = explode('|', $token, 2)[0];
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_invalid_login_does_not_authenticate(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'existing@example.test',
            'password' => 'not-the-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_validates_password_confirmation_and_unique_email(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Taylor Farmer',
            'email' => 'taylor@example.test',
            'password' => 'strong-password',
            'password_confirmation' => 'different-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }
}
