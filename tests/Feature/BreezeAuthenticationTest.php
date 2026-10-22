<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BreezeAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_unique_username_and_phone_and_sends_verification(): void
    {
        Notification::fake();
        $existing = User::factory()->create();
        $data = ['name' => 'Maram', 'username' => $existing->username, 'phone' => $existing->phone, 'email' => 'nouveau@example.test', 'password' => 'secret123', 'password_confirmation' => 'secret123'];
        $this->post('/register', $data)->assertSessionHasErrors(['username', 'phone']);
        $data['username'] = 'maram';
        $data['phone'] = '+21622123456';
        $this->post('/register', $data)->assertRedirect('/dashboard');
        $user = User::where('username', 'maram')->firstOrFail();
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->get('/dashboard')->assertRedirect('/verify-email');
        $this->get('/verify-email')->assertOk();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertRedirect('/dashboard');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get('/dashboard')->assertOk()->assertSee('Mon compte');
    }

    public function test_invalid_verification_link_does_not_verify_user(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user, 'web')->get('/verify-email/'.$user->id.'/invalid')->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_reset_password_requires_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->get('/forgot-password')->assertOk();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $data = ['email' => $user->email, 'token' => 'invalid', 'password' => 'newsecret123', 'password_confirmation' => 'newsecret123'];
            $this->post('/reset-password', $data)->assertSessionHasErrors('email');
            $this->get('/reset-password/'.$notification->token.'?email='.urlencode($user->email))->assertOk();
            $data['token'] = $notification->token;
            $this->post('/reset-password', $data)->assertRedirect('/login');

            return true;
        });
        $this->assertTrue(Hash::check('newsecret123', $user->fresh()->password));
    }

    public function test_profile_update_resets_email_verification_and_password_requires_current_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web')->get('/profile')->assertOk();
        $this->patch('/profile', ['name' => 'Nouveau nom', 'username' => 'nouveau_nom', 'phone' => '22123456', 'email' => 'nouveau@example.test'])->assertRedirect('/profile');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->put('/password', ['current_password' => 'invalid', 'password' => 'newsecret123', 'password_confirmation' => 'newsecret123'])->assertSessionHasErrors('current_password', null, 'updatePassword');
        $this->put('/password', ['current_password' => 'password', 'password' => 'newsecret123', 'password_confirmation' => 'newsecret123'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('newsecret123', $user->fresh()->password));
        $this->delete('/profile', ['password' => 'invalid'])->assertSessionHasErrors('password', null, 'userDeletion');
        $this->delete('/profile', ['password' => 'newsecret123'])->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
