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
        'superficie',
        'description',
    ];

    protected function casts(): array
    {
        return ['superficie' => 'decimal:2'];
    }

    public function producteur(): BelongsTo
    {
        return $this->belongsTo(Producteur::class);
    }
}
