<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string'], 'remember' => ['sometimes', 'boolean']], [], ['email' => 'adresse e-mail', 'password' => 'mot de passe']);
        $guard = $request->routeIs('admin.login.store') ? 'admin' : 'web';
        $key = $guard.'|'.Str::lower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($key).' secondes.']);
        }
        $credentials = ['email' => $data['email'], 'password' => $data['password']];
        $credentials['role'] = $guard === 'admin' ? 'admin' : 'user';
        if (! Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Identifiants incorrects ou accès non autorisé.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        if ($guard === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return to_route('catalog.index');
    }

    public function register(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users'], 'password' => ['required', 'string', 'min:8', 'confirmed']], [], ['name' => 'nom', 'email' => 'adresse e-mail', 'password' => 'mot de passe']);
        $user = User::create($data + ['role' => 'user']);
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return to_route('catalog.index');
    }

    public function logout(Request $request)
    {
        $guard = $request->routeIs('admin.logout') ? 'admin' : 'web';
        Auth::guard($guard)->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return to_route($guard === 'admin' ? 'admin.login' : 'login');
    }
}
