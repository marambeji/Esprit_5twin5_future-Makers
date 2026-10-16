<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'origin', 'category_id', 'image_path'];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $base = Str::slug($product->name) ?: 'produit';
            if (ctype_digit($base)) {
                $base = 'produit-'.$base;
            }
            $slug = $base;
            $suffix = 2;
            while (static::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            $product->slug = $slug;
        });
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return route('catalog.image', $this);
        }

        return asset('images/products/'.($this->image_credit['file'] ?? 'placeholder.svg'));
    }

    public function getImageCreditAttribute(): ?array
    {
        if ($this->image_path) {
            return null;
        }

        static $photos;
        $photos ??= json_decode(file_get_contents(resource_path('data/product-images.json')), true, 512, JSON_THROW_ON_ERROR);

        return $photos[$this->name] ?? null;
    }

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function nutritionalAnalyses(): HasMany
    {
        return $this->hasMany(NutritionalAnalysis::class);
    }
}
