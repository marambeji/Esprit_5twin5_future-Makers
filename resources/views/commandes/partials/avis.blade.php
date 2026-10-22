@if ($commande->delivery && $commande->delivery->status === 'livree')
<div class="border rounded p-4 my-4">
    <h2 class="h4">Votre avis sur la livraison</h2>
    @if ($commande->delivery->avis)
        <p><strong>Note :</strong> {{ $commande->delivery->avis->note }}/5</p>
        <p style="white-space: pre-line">{{ $commande->delivery->avis->commentaire }}</p>
        <p class="text-muted">Merci pour votre avis. Une seule évaluation est possible par livraison.</p>
    @else
        @if ($errors->any())
            <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('deliveries.avis.store', $commande->delivery) }}">
            @csrf
            <label for="avis-note">Note sur 5</label>
            <select id="avis-note" name="note" class="form-control mb-3" required>
                <option value="">Choisir une note</option>
                @foreach (range(1, 5) as $note)<option value="{{ $note }}" @selected(old('note') == $note)>{{ $note }}/5</option>@endforeach
            </select>
            <label for="avis-commentaire">Commentaire</label>
            <textarea id="avis-commentaire" name="commentaire" class="form-control mb-2" rows="3" maxlength="1000" required>{{ old('commentaire') }}</textarea>
            <p class="text-muted small">Votre commentaire sera analysé par notre service d’IA pour améliorer les livraisons. Évitez les informations personnelles.</p>
            <button class="btn btn-success">Envoyer mon avis</button>
        </form>
    @endif
</div>
@endif
