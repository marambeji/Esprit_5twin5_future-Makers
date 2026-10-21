<div class="card mt-4"><div class="card-body">
<h2 class="h4">Évaluations des livraisons</h2>
<p><strong>Note moyenne :</strong> {{ $distributor->avis_count ? number_format($distributor->avis_avg_note, 1).'/5 — '.$distributor->avis_count.' avis' : 'Aucun avis' }}</p>
<h3 class="h5">Avis négatifs détectés par l’IA</h3>
<p class="text-muted">L’analyse IA peut se tromper. La moyenne inclut tous les avis, même ceux dont l’analyse est indisponible.</p>
@forelse ($negativeReviews as $avis)
<div class="border rounded p-3 mb-3">
    <a href="{{ route('admin.deliveries.show', $avis->delivery) }}">{{ $avis->delivery->reference }}</a>
    <span> — {{ $avis->note }}/5 — {{ $avis->created_at->format('d/m/Y H:i') }}</span>
    <p class="mb-1"><strong>Thème :</strong> {{ \App\Models\AvisLivraison::THEMES[$avis->theme] ?? 'Non déterminé' }}</p>
    <p class="mb-0" style="white-space: pre-line">{{ $avis->commentaire }}</p>
</div>
@empty
<p>Aucun avis classé négatif par l’IA.</p>
@endforelse
{{ $negativeReviews->links() }}
</div></div>
