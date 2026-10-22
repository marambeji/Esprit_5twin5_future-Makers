<?php

namespace App\Http\Controllers;

use App\Http\Requests\NutritionalAnalysisRequest;
use App\Models\Category;
use App\Models\NutritionalAnalysis;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NutritionalAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['product_id' => ['nullable', 'integer', 'exists:products,id']]);
        $analyses = NutritionalAnalysis::query()->join('products', 'products.id', '=', 'nutritional_analyses.product_id')
            ->select('nutritional_analyses.*', 'products.name as product_name')
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->orderByDesc('analysed_at')->orderByDesc('nutritional_analyses.id')->paginate(10)->withQueryString();

        return view('admin.nutrition.index', ['analyses' => $analyses, 'categories' => Category::orderBy('name')->get(), 'products' => Product::orderBy('name')->get()]);
    }

    public function create(Request $request)
    {
        $data = $request->validate(['product_id' => ['nullable', 'integer', 'exists:products,id']]);

        return view('admin.nutrition.form', ['analysis' => new NutritionalAnalysis(['product_id' => $data['product_id'] ?? null, 'analysed_at' => today(), 'is_demo' => false]), 'categories' => Category::orderBy('name')->get(), 'products' => Product::orderBy('name')->get()]);
    }

    public function store(NutritionalAnalysisRequest $request)
    {
        $analysis = DB::transaction(function () use ($request) {
            $data = $request->persistenceData();
            if ($request->boolean('create_product')) {
                $data['product_id'] = Product::create($request->validated('new_product'))->id;
            }

            return NutritionalAnalysis::create($data);
        });

        return to_route('admin.nutrition.show', $analysis)->with('success', 'Analyse nutritionnelle créée.');
    }

    public function show(NutritionalAnalysis $analysis)
    {
        return view('admin.nutrition.show', ['analysis' => $analysis->load('product')]);
    }

    public function edit(NutritionalAnalysis $analysis)
    {
        return view('admin.nutrition.form', ['analysis' => $analysis, 'categories' => Category::orderBy('name')->get(), 'products' => Product::orderBy('name')->get()]);
    }

    public function update(NutritionalAnalysisRequest $request, NutritionalAnalysis $analysis)
    {
        DB::transaction(function () use ($request, $analysis) {
            $data = $request->persistenceData();
            if ($request->boolean('create_product')) {
                $data['product_id'] = Product::create($request->validated('new_product'))->id;
            }
            $analysis->update($data);
        });

        return to_route('admin.nutrition.show', $analysis)->with('success', 'Analyse nutritionnelle modifiée.');
    }

    public function destroy(NutritionalAnalysis $analysis)
    {
        $analysis->delete();

        return to_route('admin.nutrition.index')->with('success', 'Analyse nutritionnelle supprimée.');
    }
}
