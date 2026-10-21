{{-- Module Transformation (Zied) : parcours et empreinte des étapes de transformation du produit. --}}
@php($etapes = $product->transformations()->with(['transformateur', 'ferme'])->latest('date_transformation')->get())
@if ($etapes->isNotEmpty())
<div class="mt-5">
    <h2 class="h4 mb-3">Transformation et empreinte</h2>
    <div class="row">
        @foreach ($etapes as $t)
        <div class="col-md-6 mb-3">
            <div class="d-flex align-items-start p-3" style="background:#f6f7f1;border-radius:10px;">
                <x-eco-score :score="$t->eco_score" class="mr-3 flex-shrink-0" />
                <div>
                    <p class="mb-1"><strong>{{ $t->procede_label }}</strong> — <a class="text-success" href="{{ route('transformateurs.show', $t->transformateur) }}">{{ $t->transformateur->nom }}</a> ({{ $t->transformateur->ville }})</p>
                    <p class="small mb-1">{{ $t->ferme ? 'Matière première : '.$t->ferme->nom.' · ' : '' }}{{ $t->date_transformation->format('d/m/Y') }}</p>
                    <p class="small mb-0">{{ $t->intensite() !== null ? number_format($t->intensite(), 2, ',', ' ').' kg CO₂e par kg transformé' : 'Empreinte non renseignée' }}</p>
                    @if (in_array($t->verdict_allegation, ['exageree', 'trompeuse'], true))
                    <p class="small text-danger mb-0 mt-1"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Allégation « {{ $t->allegation }} » : {{ mb_strtolower(\App\Models\Transformation::VERDICTS[$t->verdict_allegation]) }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
