<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    public const STATUSES = ['planifiee' => 'Planifiée', 'en_cours' => 'En cours', 'livree' => 'Livrée', 'annulee' => 'Annulée'];

    protected $fillable = ['distributor_id', 'destination', 'delivery_date', 'status', 'notes'];

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
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
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
