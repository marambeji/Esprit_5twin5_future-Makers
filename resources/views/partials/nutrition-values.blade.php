@if($analysis->reference_fdc_id)
<div class="alert alert-info"><strong>Valeurs de référence, vérifiées ou corrigées par l’utilisateur.</strong> Elles ne constituent pas une mesure du produit photographié.
<p class="mb-0">Référence : {{ $analysis->reference_description }} — <a href="https://fdc.nal.usda.gov/food-details/{{ (int) $analysis->reference_fdc_id }}/nutrients" target="_blank" rel="noopener noreferrer">USDA FoodData Central</a>. Sel de référence calculé à partir du sodium (× 2,5), puis arrondi.</p></div>
@endif
<p><strong>Date d’analyse :</strong> {{ $analysis->analysed_at->format('d/m/Y') }}</p>
@if($analysis->is_demo)<p class="text-warning"><strong>Données fictives de démonstration : elles ne décrivent pas la composition réelle du produit.</strong></p>@endif
<div class="table-responsive"><table class="table table-bordered"><caption>Valeurs pour 100 g de produit</caption><thead><tr><th scope="col">Élément</th><th scope="col">Quantité pour 100 g</th></tr></thead><tbody>
@foreach(\App\Models\NutritionalAnalysis::NUTRIENTS as $field => $label)<tr><th scope="row">{{ $label }}</th><td>{{ number_format((float) $analysis->$field, 2, ',', ' ') }}</td></tr>@endforeach
</tbody></table></div>
<p><strong>Source :</strong> {{ $analysis->source ?: 'Non renseignée' }}</p>
@if($analysis->notes)<p style="white-space:pre-line;overflow-wrap:anywhere">{{ $analysis->notes }}</p>@endif
