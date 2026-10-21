<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvisLivraison extends Model
{
    protected $table = 'avis_livraisons';

    public const SENTIMENTS = ['positif' => 'Positif', 'neutre' => 'Neutre', 'negatif' => 'Négatif'];

    public const THEMES = ['retard' => 'Retard', 'comportement' => 'Comportement du livreur', 'etat_colis' => 'État du colis', 'autre' => 'Autre'];

    protected $fillable = ['note', 'commentaire', 'sentiment', 'theme'];

    protected function casts(): array
    {
        return ['note' => 'integer'];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
