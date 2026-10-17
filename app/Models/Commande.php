<?php

namespace App\Models;

use App\Services\NutriAI;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Commande extends Model
{
    public const STATUSES = ['en_attente' => 'En attente', 'confirmee' => 'Confirmée', 'refusee' => 'Refusée', 'annulee' => 'Annulée'];

    protected $fillable = ['user_id', 'product_id', 'quantite', 'adresse_livraison', 'telephone', 'statut', 'motif_refus'];

    protected static function booted(): void
    {
        // Référence CMD-AAAAMMJJ-NNNN, séquence journalière.
        static::creating(function (Commande $commande) {
            $prefix = 'CMD-'.now()->format('Ymd').'-';
            $last = static::where('reference', 'like', $prefix.'%')->max('reference');
            $commande->reference = $prefix.str_pad((string) ($last ? (int) substr($last, -4) + 1 : 1), 4, '0', STR_PAD_LEFT);
        });
        // Notification e-mail au client quand l'admin confirme ou refuse.
        static::updated(function (Commande $commande) {
            if ($commande->wasChanged('statut') && in_array($commande->statut, ['confirmee', 'refusee'], true)) {
                $ok = $commande->statut === 'confirmee';
                $resume = "Commande {$commande->reference} : {$commande->product->name} x{$commande->quantite}";
                NutriAI::notify($commande->user, 'Votre commande '.$commande->reference.' est '.($ok ? 'confirmée' : 'refusée'),
                    $resume.', statut '.($ok ? 'confirmée, une livraison va être planifiée' : "refusée, motif : {$commande->motif_refus}"),
                    'Votre commande '.$commande->reference.' ('.$commande->product->name.' x'.$commande->quantite.') a été '.($ok ? 'confirmée. Une livraison va être planifiée.' : 'refusée. Motif : '.$commande->motif_refus));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function getStatutLabelAttribute(): string
    {
        return self::STATUSES[$this->statut] ?? $this->statut;
    }

    public function isPending(): bool
    {
        return $this->statut === 'en_attente';
    }
}
