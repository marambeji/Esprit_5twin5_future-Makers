<?php

namespace App\Models;

use Database\Factories\FermeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ferme extends Model
{
    /** @use HasFactory<FermeFactory> */
    use HasFactory;

    protected $fillable = [
        'producteur_id',
        'nom',
        'localisation',
        'latitude',
        'longitude',
        'superficie',
        'description',
        'boundary_geojson',
        'boundary_area_m2',
        'boundary_perimeter_m',
    ];

    protected function casts(): array
    {
        return [
            'superficie' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'boundary_geojson' => 'array',
            'boundary_area_m2' => 'float',
            'boundary_perimeter_m' => 'float',
        ];
    }

    public function producteur(): BelongsTo
    {
        return $this->belongsTo(Producteur::class);
    }
}
