<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'label_id',
        'nom',
        'numero_certificat',
        'date_obtention',
        'date_expiration',
        'statut',
        'description'
    ];

    public function label()
    {
        return $this->belongsTo(Label::class);
    }
}
