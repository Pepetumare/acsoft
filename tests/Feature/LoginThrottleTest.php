<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_bloquea_intentos_excesivos(): void
    {
        $user = User::factory()->create([
            'email' => 'rate-limit@example.com',
            'password' => 'password-correcta',
        ]);

        $credentials = [
            'email' => $user->email,
            'password' => 'password-incorrecta',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login.store'), $credentials)
                ->assertRedirect()
                ->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), $credentials)
            ->assertTooManyRequests();
    }
}
