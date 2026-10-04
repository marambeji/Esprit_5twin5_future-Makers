<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => User::orderBy('name')->paginate(10)]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'user'])]);
    }

    public function store(UserRequest $request)
    {
        $user = User::create($request->validated());

        return to_route('admin.users.show', $user)->with('success', 'Utilisateur créé.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        if ($request->user('admin')->is($user) && $data['role'] !== 'admin') {
            return back()->with('error', 'Vous ne pouvez pas retirer votre propre rôle administrateur.')->withInput($request->except('password', 'password_confirmation'));
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if ($data['email'] !== $user->email) {
            $user->email_verified_at = null;
        }
        $user->fill($data)->save();

        return to_route('admin.users.show', $user)->with('success', 'Utilisateur modifié.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user('admin')->is($user)) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        $user->delete();

        return to_route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }
}
