<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_changed_by_admin_can_be_used_with_username(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['username' => 'utilisateur_7', 'phone' => null]);
        $this->actingAs($admin, 'admin')->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'phone' => '',
            'email' => $user->email,
            'role' => 'user',
            'password' => 'nouveauSecret123',
            'password_confirmation' => 'nouveauSecret123',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.show', $user));

        $this->assertTrue(Hash::check('nouveauSecret123', $user->fresh()->password));
        $this->post('/login', ['username' => $user->username, 'password' => 'password'])->assertSessionHasErrors('username');
        $this->post('/login', ['username' => $user->username, 'password' => 'nouveauSecret123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}
