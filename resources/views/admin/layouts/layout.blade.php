<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Administration — NutriTrace')</title>
@vite(['resources/mazer/compiled/css/app.css', 'resources/mazer/compiled/css/app-dark.css', 'resources/mazer/nutritrace.css'])
@stack('styles')
</head>
<body><div id="app">
@include('admin.partials.sidebar')
<div id="main">
@include('admin.partials.navbar')
<main class="page-content">@yield('content')</main>
@include('admin.partials.footer')
</div></div>
@vite('resources/mazer/mazer.js')
@stack('scripts')
</body></html>
