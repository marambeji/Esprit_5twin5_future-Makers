<?php

namespace App\Models;

use Database\Factories\ProducteurFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producteur extends Model
{
    /** @use HasFactory<ProducteurFactory> */
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
    ];

    public function fermes(): HasMany
    {
        return $this->hasMany(Ferme::class);
    }
}
