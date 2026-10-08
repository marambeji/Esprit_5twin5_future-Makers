<header class="header-area nutritrace-header">
    <div class="container-fluid nutritrace-header-inner">
        <div class="logo"><a href="{{ route('home') }}">Nutri<span>trace</span></a></div>
        <nav class="site-navbar" aria-label="Navigation principale">
            <ul id="main-navigation">
                @foreach (['home' => 'Accueil', 'about' => 'À propos', 'service' => 'Services', 'catalog.index' => 'Catalogue', 'distributors.index' => 'Distributeurs', 'deliveries.index' => 'Livraisons', 'testimonials' => 'Témoignages', 'blog' => 'Actualités', 'contact' => 'Contact'] as $route => $label)
                <li><a class="{{ request()->routeIs(str_ends_with($route, '.index') ? explode('.', $route)[0].'.*' : $route) ? 'active' : '' }}" href="{{ route($route) }}">{{ $label }}</a></li>
                @endforeach
                @auth('web')
                <li class="nutritrace-account"><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-success">Déconnexion</button></form></li>
                @else
                <li class="nutritrace-account"><a href="{{ route('login') }}">Connexion</a></li>
                @endauth
            </ul>
            <button class="nav-toggler" type="button" aria-label="Ouvrir le menu" aria-controls="main-navigation" aria-expanded="false"><span></span></button>
        </nav>
    </div>
</header>
