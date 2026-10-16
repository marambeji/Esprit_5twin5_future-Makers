<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class FoodReferenceSearch
{
    public function search(string $query, string $state): array
    {
        $query = Str::lower(Str::squish($query));
        $terms = $this->words($query);
        if ($terms === []) {
            return [];
        }
        // Only SR Legacy: unbranded reference data expressed per 100 g.
        $response = Cache::remember('fdc-search-v2:'.hash('sha256', $query.'|'.$state), now()->addDay(), function () use ($query, $state) {
            $response = Http::acceptJson()->connectTimeout(10)->timeout(25)->get('https://api.nal.usda.gov/fdc/v1/foods/search', [
                'api_key' => config('nutrition_ai.usda_key'),
                'query' => $query.($state === 'processed' ? '' : ' '.$state),
                'dataType' => 'SR Legacy', 'pageSize' => 50,
            ]);
            if (! $response->successful()) {
                throw new RuntimeException($response->status() === 429 ? 'Le quota de la base USDA est atteint. Réessayez plus tard ou configurez une clé USDA gratuite.' : 'La base USDA est temporairement indisponible.');
            }

            return $response->json('foods', []);
        });
        $foods = [];
        foreach ($response as $food) {
            if (($food['dataType'] ?? '') !== 'SR Legacy' || empty($food['fdcId']) || empty($food['description'])) {
                continue;
            }
            $description = $food['description'];
            // USDA may return matches on "raw" alone. Require every food term,
            // comparing whole words with singular/plural normalization.
            if (array_diff($terms, $this->words($description)) !== []) {
                continue;
            }
            // Do not silently mix raw and cooked entries.
            if ($state === 'raw' && ! preg_match('/\braw\b/i', $description)) {
                continue;
            }
            if ($state === 'cooked' && ! preg_match('/\b(cooked|boiled|baked|roasted|steamed|fried)\b/i', $description)) {
                continue;
            }
            if ($state === 'processed' && preg_match('/\braw\b/i', $description)) {
                continue;
            }
            $nutrients = collect($food['foodNutrients'] ?? [])->keyBy('nutrientId');
            $value = function (int $id, string $unit) use ($nutrients): ?float {
                $entry = $nutrients->get($id);
                if (! $entry || strtolower($entry['unitName'] ?? '') !== strtolower($unit) || ! isset($entry['value']) || ! is_numeric($entry['value']) || $entry['value'] < 0) {
                    return null;
                }

                return (float) $entry['value'];
            };
            $sodium = $value(1093, 'mg');
            $foods[] = [
                'id' => (int) $food['fdcId'], 'description' => $description,
                'url' => 'https://fdc.nal.usda.gov/food-details/'.(int) $food['fdcId'].'/nutrients',
                'values' => ['energy_kcal' => $value(1008, 'kcal'), 'protein' => $value(1003, 'g'), 'carbohydrates' => $value(1005, 'g'), 'sugars' => $value(2000, 'g'), 'fat' => $value(1004, 'g'), 'salt' => $sodium === null ? null : round($sodium * 2.5 / 1000, 2)],
                'note' => 'Valeurs de référence pour 100 g de partie comestible, non mesurées sur le produit photographié. Sel calculé : sodium (mg) × 2,5 / 1000 ; arrondi à 0,01 g. Les valeurs manquantes restent à compléter.',
            ];
        }

        return $foods;
    }

    private function words(string $text): array
    {
        preg_match_all('/[a-z]+/', Str::lower(Str::ascii($text)), $matches);

        return array_values(array_unique(array_map(fn (string $word) => Str::singular($word), $matches[0])));
    }
}
