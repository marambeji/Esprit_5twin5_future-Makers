<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_templates_and_admin_access(): void
    {
        $this->get('/login')->assertOk()->assertSee('Connexion')->assertDontSee('sidebar-wrapper');
        $this->get('/admin/login')->assertOk()->assertSee('Connexion administrateur')->assertSee('btn-primary');
        $this->get('/register')->assertOk();
        foreach (['/admin', '/admin/dashboard', '/admin/users', '/admin/products', '/admin/categories'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->get('/admin/users')->assertRedirect('/admin/login');
        $this->post('/admin/users', [])->assertRedirect('/admin/login');
    }

    public function test_login_logout_and_invalid_credentials(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->assertGuest('admin');
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password', 'remember' => '1'])->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('web');
        $this->assertGuest('admin');
        $user = User::factory()->create();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->assertGuest('admin');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/catalogue');
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_registration_cannot_grant_admin_role(): void
    {
        $this->post('/register', ['name' => 'Maram', 'email' => 'maram@example.test', 'password' => 'secret123', 'password_confirmation' => 'secret123', 'role' => 'admin'])->assertRedirect('/catalogue');
        $user = User::where('email', 'maram@example.test')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertAuthenticatedAs($user, 'web');
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_admin_login_does_not_sign_in_the_public_site(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin/dashboard');
        $this->get('/')->assertOk()->assertSee('Connexion')->assertDontSee('Déconnexion');
        $this->get('/login')->assertOk();
        $this->get('/admin/dashboard')->assertOk()->assertSee('Déconnexion');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
    }

    public function test_each_logout_preserves_the_other_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/catalogue');
        $this->get('/')->assertSee('Déconnexion');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get('/')->assertDontSee('Déconnexion');
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($user, 'web');
        $this->get('/')->assertSee('Déconnexion');
        $this->get('/admin/login')->assertOk();
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_old_shared_admin_session_is_removed_from_public_guard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'web')->get('/')->assertSee('Connexion')->assertDontSee('Déconnexion');
        $this->assertGuest('web');
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->actingAs(User::factory()->create(), 'admin')->get('/admin/users')->assertForbidden();
    }

    public function test_repeated_failed_logins_are_limited(): void
    {
        $data = ['email' => 'limited@example.test', 'password' => 'incorrect'];
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', $data)->assertSessionHasErrors('email');
        }
        $this->post('/login', $data)->assertSessionHas('errors', fn ($errors) => str_starts_with($errors->first('email'), 'Trop de tentatives. Réessayez dans '));
        $this->assertGuest('web');
        $this->assertGuest('admin');
    }

    public function test_user_crud_validation_and_self_protection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'admin');
        $data = ['name' => 'Test User', 'email' => 'test@example.test', 'role' => 'user', 'password' => 'secret123', 'password_confirmation' => 'secret123'];
        $this->get('/admin/users/create')->assertOk();
        $this->post('/admin/users', $data)->assertSessionHasNoErrors();
        $user = User::where('email', $data['email'])->firstOrFail();
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->get('/admin/users')->assertOk()->assertSee('Test User');
        $this->get('/admin/users/'.$user->id)->assertOk()->assertDontSee($user->password);
        $this->get('/admin/users/'.$user->id.'/edit')->assertOk();
        $this->post('/admin/users', $data)->assertSessionHasErrors('email');
        $this->post('/admin/users', ['name' => 'Invalid', 'email' => 'invalid', 'password' => 'short', 'role' => 'root'])->assertSessionHasErrors(['email', 'password', 'role']);
        $hash = $user->password;
        $this->put('/admin/users/'.$user->id, ['name' => 'Updated', 'email' => $user->email, 'role' => 'admin', 'password' => ''])->assertSessionHasNoErrors();
        $this->assertSame($hash, $user->fresh()->password);
        $this->assertSame('admin', $user->fresh()->role);
        $this->put('/admin/users/'.$user->id, array_replace($data, ['password' => 'newsecret123', 'password_confirmation' => 'newsecret123']))->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('newsecret123', $user->fresh()->password));
        $this->put('/admin/users/'.$admin->id, ['name' => $admin->name, 'email' => $admin->email, 'role' => 'user'])->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);
        $this->delete('/admin/users/'.$admin->id)->assertSessionHas('error');
        $this->delete('/admin/users/'.$user->id)->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_seeder_preserves_existing_admin_password(): void
    {
        config(['nutritrace.admin_email' => 'admin@nutritrace.test', 'nutritrace.admin_password' => 'secret123']);
        $this->seed(UserSeeder::class);
        $admin = User::where('email', 'admin@nutritrace.test')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $hash = $admin->password;
        $this->seed(UserSeeder::class);
        $this->assertDatabaseCount('users', 6);
        $this->assertSame($hash, $admin->fresh()->password);
    }
}
