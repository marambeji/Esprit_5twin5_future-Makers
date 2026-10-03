<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'origin', 'category_id', 'image_path'];

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
}
