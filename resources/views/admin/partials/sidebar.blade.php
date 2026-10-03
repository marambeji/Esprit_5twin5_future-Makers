<div id="sidebar"><div class="sidebar-wrapper active">
<div class="sidebar-header position-relative">
<div class="d-flex justify-content-between align-items-center">
<div class="logo"><a class="nutritrace-logo" href="{{ route('admin.dashboard') }}">NutriTrace</a></div>
<div class="sidebar-toggler x"><a href="#" class="sidebar-hide d-xl-none d-block" aria-label="Fermer le menu"><i class="bi bi-x bi-middle"></i></a></div>
</div>
<div class="theme-toggle mt-3"><i class="bi bi-sun" aria-hidden="true"></i><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="toggle-dark" aria-label="Activer le mode sombre"></div><i class="bi bi-moon" aria-hidden="true"></i></div>
</div>
<nav class="sidebar-menu" aria-label="Menu du Back Office"><ul class="menu">
<li class="sidebar-title">Administration NutriTrace</li>
<li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><a href="{{ route('admin.dashboard') }}" class="sidebar-link"><i class="bi bi-grid-fill"></i><span>Tableau de bord</span></a></li>
<li class="sidebar-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><a href="{{ route('admin.categories.index') }}" class="sidebar-link"><i class="bi bi-tags"></i><span>Catégories</span></a></li>
<li class="sidebar-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><a href="{{ route('admin.products.index') }}" class="sidebar-link"><i class="bi bi-basket"></i><span>Produits</span></a></li>
<li class="sidebar-item"><a href="{{ route('admin.dashboard') }}#parcours" class="sidebar-link"><i class="bi bi-truck"></i><span>Parcours alimentaire</span></a></li>
<li class="sidebar-item"><a href="{{ route('admin.dashboard') }}#certifications" class="sidebar-link"><i class="bi bi-patch-check-fill"></i><span>Certifications</span></a></li>
<li class="sidebar-item"><a href="{{ route('admin.dashboard') }}#environnement" class="sidebar-link"><i class="bi bi-tree-fill"></i><span>Environnement</span></a></li>
<li class="sidebar-title">Site public</li>
<li class="sidebar-item"><a href="{{ route('home') }}" class="sidebar-link"><i class="bi bi-house-fill"></i><span>Front Office</span></a></li>
</ul></nav></div></div>
