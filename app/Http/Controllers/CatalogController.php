<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
    public function image(Product $product)
    {
        abort_unless($product->image_path && Storage::disk('public')->exists($product->image_path), 404);

        return response()->file(Storage::disk('public')->path($product->image_path), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function index(Request $request)
    {
        $filters = $request->validate(['category' => ['nullable', 'integer', 'exists:categories,id']]);
        $products = Product::with('category')->when($filters['category'] ?? null, fn ($query, $id) => $query->where('category_id', $id))->orderBy('name')->paginate(9)->withQueryString();

        return view('pages.catalog', ['products' => $products, 'categories' => Category::orderBy('name')->get()]);
    }

    public function show(Product $product)
    {
        return view('pages.product', ['product' => $product->load('category'), 'analyses' => $product->nutritionalAnalyses()->orderByDesc('analysed_at')->orderByDesc('id')->paginate(5)]);
    }

    public function legacy(Product $legacyProduct)
    {
        return redirect()->route('catalog.show', ['product' => $legacyProduct->slug], 301);
    }
}
