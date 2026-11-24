<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_active_user_can_log_in_and_view_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'a-long-test-password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'a-long-test-password',
        ])->assertRedirect(route('dashboard'));

        $this->get('/dashboard')->assertOk()->assertSee('Dashboard');
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.test',
            'password' => 'a-long-test-password',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'a-long-test-password',
        ])->assertRedirect()->assertSessionHasErrors('email');
    }

    public function test_only_super_admin_can_open_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/users')->assertForbidden();

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)->get('/users')->assertOk()->assertSee('Manajemen pengguna');
    }

    public function test_super_admin_can_create_an_internal_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        $this->actingAs($superAdmin)->post('/users', [
            'name' => 'Admin Baru',
            'email' => 'adminbaru@example.test',
            'password' => 'password-awal-kuat-2026',
            'role' => 'admin',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'adminbaru@example.test', 'role' => 'admin', 'is_active' => 1]);
    }
}
