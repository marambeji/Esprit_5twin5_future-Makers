<header class="header-area nutritrace-header">
    <div class="container-fluid nutritrace-header-inner">
        <div class="logo"><a href="{{ route('home') }}">Nutri<span>trace</span></a></div>
        <nav class="site-navbar" aria-label="Navigation principale">
            <ul id="main-navigation">
                @foreach (['home' => 'Accueil', 'about' => 'À propos', 'service' => 'Services', 'catalog.index' => 'Catalogue', 'producteurs.index' => 'Producteurs', 'transformateurs.index' => 'Transformateurs', 'certifications.index' => 'Certifications', 'testimonials' => 'Témoignages', 'blog' => 'Actualités', 'contact' => 'Contact'] as $route => $label)
                <li><a class="{{ request()->routeIs($route === 'producteurs.index' ? ['producteurs.*', 'fermes.*'] : (str_ends_with($route, '.index') ? explode('.', $route)[0].'.*' : $route)) ? 'active' : '' }}" href="{{ route($route) }}">{{ $label }}</a></li>
                @endforeach
                @auth('web')
                <li><a class="{{ request()->routeIs('commandes.*') ? 'active' : '' }}" href="{{ route('commandes.index') }}">Mes commandes</a></li>
                @if (\App\Models\Distributor::where('user_id', auth('web')->id())->where('actif', true)->exists())
                <li><a class="{{ request()->routeIs('distributor-space.*') ? 'active' : '' }}" href="{{ route('distributor-space.index') }}">Mes livraisons</a></li>
                @endif
                <li><a href="{{ route('dashboard') }}">Mon compte</a></li>
                <li class="nutritrace-account"><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-success">Déconnexion</button></form></li>
                @else
                <li class="nutritrace-account"><a href="{{ route('login') }}">Connexion</a></li>
                @endauth
            </ul>
            <button class="nav-toggler" type="button" aria-label="Ouvrir le menu" aria-controls="main-navigation" aria-expanded="false"><span></span></button>
        </nav>
    </div>
</header>
