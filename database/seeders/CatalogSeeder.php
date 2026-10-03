<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::samples() as $name => $products) {
            if (! Category::where('name', $name)->exists()) {
                Category::factory()->has(Product::factory()->count(3)->sequence(...array_map(fn ($product) => ['name' => $product], $products)))->create(['name' => $name, 'description' => 'Catégorie de démonstration : '.$name]);
            }
        }
    }

    public static function samples(): array
    {
        return [
            'Fruits' => ['Dattes Deglet Nour', 'Pommes', 'Oranges'],
            'Légumes' => ['Tomates', 'Carottes', 'Pommes de terre'],
            'Céréales' => ['Blé dur', 'Orge', 'Avoine'],
            'Produits laitiers' => ['Lait', 'Yaourt nature', 'Fromage frais'],
            'Huiles' => ['Huile d’olive', 'Huile de tournesol', 'Huile de sésame'],
        ];
    }
}
