<?php

namespace App\Services;

use App\Models\NutritionalAnalysis;
use Illuminate\Support\Collection;

class NutritionBasket
{
    public function calculate(array $items, Collection $analyses, int $portions): array
    {
        $totals = array_fill_keys(array_keys(NutritionalAnalysis::NUTRIENTS), 0.0);
        $weight = 0.0;
        $rows = [];
        foreach ($items as $item) {
            $analysis = $analyses->get($item['analysis_id']);
            $grams = (float) $item['grams'];
            $values = [];
            foreach ($totals as $key => $unused) {
                $values[$key] = (float) $analysis->$key * $grams / 100;
                $totals[$key] += $values[$key];
            }
            $weight += $grams;
            $rows[] = compact('analysis', 'grams', 'values');
        }

        // Keep precision throughout the calculation; round only when displaying.
        return compact('totals', 'weight', 'rows', 'portions');
    }
}
