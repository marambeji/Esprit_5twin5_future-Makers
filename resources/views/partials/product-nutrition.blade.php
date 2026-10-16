<section class="mt-5" aria-labelledby="nutrition-title"><h2 id="nutrition-title" class="h3 mb-3">Analyses nutritionnelles</h2>
@forelse($analyses as $analysis)<article class="border rounded p-3 mb-4">@include('partials.nutrition-values')
@if($backOffice ?? false)<a class="btn btn-outline-primary btn-sm" href="{{ route('admin.nutrition.show', $analysis) }}">Gérer cette analyse</a>@endif
</article>@empty<p>Aucune analyse nutritionnelle disponible pour ce produit.</p>@endforelse
{{ $analyses->links(($backOffice ?? false) ? 'pagination::bootstrap-5' : 'pagination::bootstrap-4') }}
</section>
