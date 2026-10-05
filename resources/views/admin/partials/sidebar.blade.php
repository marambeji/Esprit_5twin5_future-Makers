<div id="sidebar"><div class="sidebar-wrapper active">
<div class="sidebar-header position-relative">
<div class="d-flex justify-content-between align-items-center">
<div class="logo"><a class="nutritrace-logo" href="{{ route('admin.dashboard') }}">NutriTrace</a></div>
<div class="sidebar-toggler x"><a href="#" class="sidebar-hide d-xl-none d-block" aria-label="Fermer le menu"><i class="bi bi-x bi-middle"></i></a></div>
</div>
<div class="theme-toggle mt-3"><i class="bi bi-sun" aria-hidden="true"></i><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="toggle-dark" aria-label="Activer le mode sombre"></div><i class="bi bi-moon" aria-hidden="true"></i></div>
</div>
<nav class="sidebar-menu" aria-label="Menu de l’administration"><ul class="menu">
<li class="sidebar-title">Administration NutriTrace</li>
<li class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><a href="{{ route('admin.users.index') }}" class="sidebar-link"><i class="bi bi-people-fill"></i><span>Utilisateurs</span></a></li>
<li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><a href="{{ route('admin.dashboard') }}" class="sidebar-link"><i class="bi bi-grid-fill"></i><span>Tableau de bord</span></a></li>
<li class="sidebar-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><a href="{{ route('admin.categories.index') }}" class="sidebar-link"><i class="bi bi-tags"></i><span>Catégories</span></a></li>
<li class="sidebar-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><a href="{{ route('admin.products.index') }}" class="sidebar-link"><i class="bi bi-basket"></i><span>Produits</span></a></li>
<li class="sidebar-item"><a href="{{ route('admin.dashboard') }}#parcours" class="sidebar-link"><i class="bi bi-truck"></i><span>Parcours alimentaire</span></a></li>
<li class="sidebar-item {{ request()->routeIs('admin.certifications.*') ? 'active' : '' }}"><a href="{{ route('admin.certifications.index') }}" class="sidebar-link"><i class="bi bi-patch-check-fill"></i><span>Certifications</span></a></li>
<li class="sidebar-item"><a href="{{ route('admin.dashboard') }}#environnement" class="sidebar-link"><i class="bi bi-tree-fill"></i><span>Environnement</span></a></li>
<li class="sidebar-title">Site public</li>
<li class="sidebar-item"><a href="{{ route('home') }}" class="sidebar-link"><i class="bi bi-house-fill"></i><span>Site public</span></a></li>
@auth('admin')
<li class="sidebar-title">Mon compte</li>
<li class="sidebar-item"><form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="sidebar-link border-0 w-100 text-start"><i class="bi bi-box-arrow-right"></i><span>Déconnexion</span></button></form></li>
@endauth
</ul></nav></div></div>
