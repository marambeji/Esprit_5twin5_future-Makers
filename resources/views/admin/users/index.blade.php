@extends('admin.layouts.layout')
@section('title', 'Utilisateurs — NutriTrace')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">Utilisateurs</h1><a class="btn btn-primary" href="{{ route('admin.users.create') }}">Ajouter un utilisateur</a></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Actions</th></tr></thead><tbody>
@forelse ($users as $user)<tr><td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td><td>{{ $user->email }}</td><td>{{ $user->role === 'admin' ? 'Administrateur' : 'Utilisateur' }}</td><td><div class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">Modifier</a>@unless(auth('admin')->id() === $user->id)<form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form>@endunless</div></td></tr>
@empty<tr><td colspan="4">Aucun utilisateur.</td></tr>@endforelse
</tbody></table></div>{{ $users->links() }}</div></div>
@endsection
