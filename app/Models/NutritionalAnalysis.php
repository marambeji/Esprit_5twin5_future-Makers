<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionalAnalysis extends Model
{
    use HasFactory;

    public const NUTRIENTS = ['energy_kcal' => 'Énergie (kcal)', 'protein' => 'Protéines (g)', 'carbohydrates' => 'Glucides (g)', 'sugars' => 'Dont sucres (g)', 'fat' => 'Lipides (g)', 'salt' => 'Sel (g)'];

    protected $fillable = ['reference_fdc_id', 'reference_description', 'product_id', 'analysed_at', 'energy_kcal', 'protein', 'carbohydrates', 'sugars', 'fat', 'salt', 'source', 'notes', 'is_demo'];

    protected function casts(): array
    {
        return ['analysed_at' => 'date', 'is_demo' => 'boolean'] + array_fill_keys(array_keys(self::NUTRIENTS), 'decimal:2');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
