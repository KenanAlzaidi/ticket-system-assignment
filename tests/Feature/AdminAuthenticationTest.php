<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase; // Valid to use here as User model uses default connection

    public function test_admin_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Admin Portal Login');
    }

    public function test_admin_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create([
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.tickets.index'));
    }

    public function test_admin_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_admin_can_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}

