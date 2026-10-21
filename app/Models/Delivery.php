<?php

namespace App\Models;

use App\Services\NutriAI;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delivery extends Model
{
    public const STATUSES = ['planifiee' => 'Planifiée', 'en_cours' => 'En cours', 'livree' => 'Livrée', 'annulee' => 'Annulée'];

    public const TRANSITIONS = ['planifiee' => ['en_cours'], 'en_cours' => ['livree']];

    protected $fillable = ['commande_id', 'distributor_id', 'destination', 'delivery_date', 'status', 'notes'];

    protected function casts(): array
    {
        return ['delivery_date' => 'date'];
    }

    protected static function booted(): void
    {
        // Référence LIV-AAAAMMJJ-NNNN, séquence journalière.
        static::creating(function (Delivery $delivery) {
            $prefix = 'LIV-'.now()->format('Ymd').'-';
            $last = static::where('reference', 'like', $prefix.'%')->max('reference');
            $delivery->reference = $prefix.str_pad((string) ($last ? (int) substr($last, -4) + 1 : 1), 4, '0', STR_PAD_LEFT);
        });
        // Notification e-mail au client quand la livraison démarre ou est terminée.
        static::updated(function (Delivery $delivery) {
            if ($delivery->commande && $delivery->wasChanged('status') && in_array($delivery->status, ['en_cours', 'livree'], true)) {
                $c = $delivery->commande;
                $label = $delivery->status === 'livree' ? 'livrée' : 'en cours de livraison';
                NutriAI::notify($c->user, 'Votre commande '.$c->reference.' est '.$label,
                    "Commande {$c->reference} : {$c->product->name} x{$c->quantite}, livraison {$delivery->reference} par le distributeur {$delivery->distributor->name}, statut : {$label}, date prévue ".$delivery->delivery_date->format('d/m/Y'),
                    'Votre commande '.$c->reference.' ('.$c->product->name.' x'.$c->quantite.') est '.$label.' (distributeur : '.$delivery->distributor->name.').');
            }
        });
    }

    public function avis(): HasOne
    {
        return $this->hasOne(AvisLivraison::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    /** Le distributeur avance d'une étape : Planifiée → En cours → Livrée. */
    public function nextStatus(): ?string
    {
        return self::TRANSITIONS[$this->status][0] ?? null;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function scopeSearch(Builder $query, ?string $term, ?string $status = null): Builder
    {
        $term = trim((string) $term);

        return $query->when($term !== '', fn ($q) => $q->where(fn ($q) => $q->where('reference', 'like', "%{$term}%")->orWhere('destination', 'like', "%{$term}%")->orWhereHas('distributor', fn ($d) => $d->where('name', 'like', "%{$term}%"))))
            ->when($status, fn ($q) => $q->where('status', $status));
    }
}
