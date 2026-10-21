<?php

namespace App\Models;

use Database\Factories\TransformateurFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transformateur extends Model
{
    /** @use HasFactory<TransformateurFactory> */
    use HasFactory;

    public const TYPES = [
        'laiterie'     => 'Laiterie / fromagerie',
        'huilerie'     => 'Huilerie',
        'minoterie'    => 'Minoterie / moulin',
        'conserverie'  => 'Conserverie',
        'conditionnement' => 'Station de conditionnement',
        'boulangerie'  => 'Boulangerie industrielle',
        'autre'        => 'Autre',
    ];

    protected $fillable = ['nom', 'type_activite', 'email', 'telephone', 'ville', 'adresse', 'description'];

    public function transformations(): HasMany
    {
        return $this->hasMany(Transformation::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type_activite] ?? $this->type_activite;
    }

    /** Empreinte carbone moyenne (kg CO₂ par kg transformé) sur les transformations renseignées. */
    public function intensiteMoyenne(): ?float
    {
        $rows = $this->transformations->whereNotNull('co2_kg')->where('quantite_kg', '>', 0);
        if ($rows->isEmpty()) {
            return null;
        }

        return round($rows->sum('co2_kg') / $rows->sum('quantite_kg'), 3);
    }
}
