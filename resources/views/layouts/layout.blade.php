<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Nutritrace')</title>
    @vite(['resources/assets/css/bootstrap.min.css', 'resources/assets/css/style.css', 'resources/assets/css/responsive.css', 'resources/assets/css/owl.carousel.min.css', 'resources/assets/css/bootstrap-datepicker.min.css'])
</head>
<body class="@yield('body_class', 'main-layout')">
    <div class="loader_bg">
        <div class="loader"><img src="{{ Vite::asset('resources/assets/images/loading.gif') }}" alt="Chargement"></div>
    </div>
    @include('partials.aside')
    <div class="full_bg">
        @include('partials.navbar')
        @if (!request()->routeIs('home', 'dashboard'))
    </div>
        @endif
        @yield('content')
    @include('partials.footer')
    @vite(['resources/assets/js/images.js', 'resources/assets/js/agropro.js'])
</body>
</html>
