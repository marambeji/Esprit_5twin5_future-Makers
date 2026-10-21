<?php

namespace App\Models;

use Database\Factories\TransformationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transformation extends Model
{
    /** @use HasFactory<TransformationFactory> */
    use HasFactory;

    /**
     * Procédés proposés, avec des facteurs indicatifs par kg transformé (ordres de grandeur pédagogiques,
     * utilisés pour l'estimation locale quand l'IA est indisponible) : kg CO₂, litres d'eau, kWh.
     */
    public const PROCEDES = [
        'pasteurisation'  => ['label' => 'Pasteurisation',              'co2' => 0.15, 'eau' => 2.0, 'energie' => 0.25],
        'fromagerie'      => ['label' => 'Fabrication fromagère',       'co2' => 0.90, 'eau' => 5.0, 'energie' => 1.20],
        'mouture'         => ['label' => 'Mouture (farine, semoule)',   'co2' => 0.05, 'eau' => 0.3, 'energie' => 0.10],
        'pressage'        => ['label' => 'Pressage / extraction d’huile', 'co2' => 0.40, 'eau' => 1.5, 'energie' => 0.35],
        'appertisation'   => ['label' => 'Mise en conserve',            'co2' => 0.50, 'eau' => 4.0, 'energie' => 0.80],
        'sechage'         => ['label' => 'Séchage',                     'co2' => 0.60, 'eau' => 0.5, 'energie' => 1.50],
        'surgelation'     => ['label' => 'Surgélation',                 'co2' => 0.70, 'eau' => 1.0, 'energie' => 1.20],
        'conditionnement' => ['label' => 'Tri et conditionnement',      'co2' => 0.10, 'eau' => 0.2, 'energie' => 0.15],
    ];

    /** Seuils d'intensité (kg CO₂ par kg transformé) pour l'éco-score de l'étape ; au-delà de D : E. */
    public const ECO_SCORES = ['A' => 0.2, 'B' => 0.5, 'C' => 1.0, 'D' => 2.0];

    public const SCORES = ['A', 'B', 'C', 'D', 'E'];

    public const VERDICTS = [
        'fiable'     => 'Allégation cohérente',
        'exageree'   => 'Allégation exagérée',
        'trompeuse'  => 'Allégation trompeuse',
        'non_evaluee' => 'Non évaluée',
    ];

    protected $fillable = [
        'transformateur_id', 'product_id', 'ferme_id', 'procede', 'date_transformation', 'quantite_kg',
        'co2_kg', 'eau_litres', 'energie_kwh', 'allegation', 'verdict_allegation', 'analyse_ia',
    ];

    protected static function booted(): void
    {
        // Référence TRF-AAAAMMJJ-NNNN, séquence journalière (même convention que les commandes).
        static::creating(function (Transformation $t) {
            $prefix = 'TRF-'.now()->format('Ymd').'-';
            $last = static::where('reference', 'like', $prefix.'%')->max('reference');
            $t->reference = $prefix.str_pad((string) ($last ? (int) substr($last, -4) + 1 : 1), 4, '0', STR_PAD_LEFT);
        });
        // L'éco-score est toujours recalculé à partir des données : il ne peut pas être saisi à la main.
        static::saving(fn (Transformation $t) => $t->eco_score = self::scoreFor($t->intensite()));
    }

    protected function casts(): array
    {
        return [
            'date_transformation' => 'date',
            'quantite_kg' => 'decimal:2',
            'co2_kg' => 'decimal:2',
            'eau_litres' => 'decimal:2',
            'energie_kwh' => 'decimal:2',
        ];
    }

    public function transformateur(): BelongsTo
    {
        return $this->belongsTo(Transformateur::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ferme(): BelongsTo
    {
        return $this->belongsTo(Ferme::class);
    }

    public function getProcedeLabelAttribute(): string
    {
        return self::PROCEDES[$this->procede]['label'] ?? $this->procede;
    }

    /** kg CO₂ par kg transformé, ou null si l'empreinte n'est pas renseignée. */
    public function intensite(): ?float
    {
        if ($this->co2_kg === null || (float) $this->quantite_kg <= 0) {
            return null;
        }

        return round((float) $this->co2_kg / (float) $this->quantite_kg, 3);
    }

    public static function scoreFor(?float $intensite): ?string
    {
        if ($intensite === null) {
            return null;
        }
        foreach (self::ECO_SCORES as $score => $max) {
            if ($intensite <= $max) {
                return $score;
            }
        }

        return 'E';
    }

    /** Couleur de l'éco-score (vert → rouge), utilisée par le composant x-eco-score. */
    public static function scoreHex(?string $score): string
    {
        return match ($score) {
            'A' => '#1e8f4e', 'B' => '#7ac143', 'C' => '#e0b400', 'D' => '#ef8a17', 'E' => '#e63e11', default => '#9aa0a6',
        };
    }

    /** Estimation locale à partir des facteurs indicatifs du procédé. */
    public static function estimationLocale(string $procede, float $quantiteKg): ?array
    {
        $f = self::PROCEDES[$procede] ?? null;
        if (! $f || $quantiteKg <= 0) {
            return null;
        }

        return [
            'co2_kg' => round($f['co2'] * $quantiteKg, 2),
            'eau_litres' => round($f['eau'] * $quantiteKg, 2),
            'energie_kwh' => round($f['energie'] * $quantiteKg, 2),
        ];
    }
}
