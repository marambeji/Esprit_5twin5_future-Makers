@extends('admin.layouts.layout')
@section('title', $user->name . ' — NutriTrace')
@section('content')
<h1 class="h3 mb-4">{{ $user->name }}</h1><div class="card"><div class="card-body"><p><strong>E-mail :</strong> {{ $user->email }}</p><p><strong>Rôle :</strong> {{ $user->role === 'admin' ? 'Administrateur' : 'Utilisateur' }}</p><p><strong>Créé le :</strong> {{ $user->created_at->format('d/m/Y') }}</p><a class="btn btn-primary" href="{{ route('admin.users.edit', $user) }}">Modifier</a> <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Retour</a></div></div>
@endsection
