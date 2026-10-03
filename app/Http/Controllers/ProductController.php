<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.products.index', ['products' => Product::with('category')->latest()->paginate(10)]);
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->safe()->except('image');
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        $product = Product::create($data);

        return to_route('admin.products.show', $product)->with('success', 'Produit créé.');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', ['product' => $product->load('category')]);
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->safe()->except('image');
        $oldImage = $product->image_path;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        $product->update($data);
        if (isset($data['image_path']) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return to_route('admin.products.show', $product)->with('success', 'Produit modifié.');
    }

    public function destroy(Product $product)
    {
        $image = $product->image_path;
        $product->delete();
        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return to_route('admin.products.index')->with('success', 'Produit supprimé.');
    }
}
