<header class="mb-4 d-flex justify-content-between align-items-center">
<a href="#" class="burger-btn d-block d-xl-none" aria-label="Ouvrir le menu"><i class="bi bi-justify fs-3"></i></a>
<span class="text-muted">De la ferme à l’assiette</span>
@auth('admin')<span>{{ auth('admin')->user()->name }}</span>@endauth
<a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i> Voir le site</a>
</header>
