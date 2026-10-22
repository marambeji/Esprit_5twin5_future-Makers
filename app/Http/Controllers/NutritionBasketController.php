<?php

namespace App\Http\Controllers;

use App\Models\NutritionalAnalysis;
use App\Services\NutritionBasket;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NutritionBasketController extends Controller
{
    public function __invoke(Request $request, NutritionBasket $calculator)
    {
        $result = null;
        $items = [['analysis_id' => '', 'grams' => '100']];
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'items' => ['required', 'array', 'min:1', 'max:20'],
                'items.*.analysis_id' => ['required', 'integer', 'distinct', 'exists:nutritional_analyses,id'],
                'items.*.grams' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:10000'],
                'portions' => ['required', 'integer', 'min:1', 'max:100'],
            ], [
                'items.*.analysis_id.required' => 'Choisissez un produit et sa référence nutritionnelle.',
                'items.*.analysis_id.distinct' => 'Cette référence est déjà sélectionnée : regroupez les quantités.',
                'items.*.analysis_id.exists' => 'Cette analyse n’est plus disponible.',
                'items.*.grams.min' => 'La quantité doit être supérieure à zéro.',
                'items.*.grams.max' => 'La quantité maximale est de 10 000 g par ligne.',
                'items.*.grams.decimal' => 'La quantité accepte au maximum deux décimales.',
            ]);
            $items = $data['items'];
            $selected = NutritionalAnalysis::with('product')->whereIn('id', array_column($items, 'analysis_id'))->get()->keyBy('id');
            foreach ($items as $index => $item) {
                $analysis = $selected->get($item['analysis_id']);
                if (! $analysis || ! $analysis->product || collect(array_keys(NutritionalAnalysis::NUTRIENTS))->contains(fn ($key) => $analysis->$key === null)) {
                    throw ValidationException::withMessages(["items.$index.analysis_id" => 'Cette analyse est incomplète ou indisponible. Choisissez une autre référence.']);
                }
            }
            $result = $calculator->calculate($items, $selected, (int) $data['portions']);
        }

        return view('pages.nutrition-basket', [
            'analyses' => NutritionalAnalysis::with('product.category')->whereHas('product')->orderByDesc('analysed_at')->orderByDesc('id')->get()
                ->groupBy(fn ($analysis) => $analysis->product->category?->name ?? 'Sans catégorie')->sortKeys(),
            'nutrients' => NutritionalAnalysis::NUTRIENTS,
            'items' => $items,
            'result' => $result,
        ]);
    }
}
