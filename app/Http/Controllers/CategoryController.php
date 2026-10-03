<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', ['categories' => Category::withCount('products')->orderBy('name')->paginate(10)]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return to_route('admin.categories.show', $category)->with('success', 'Catégorie créée.');
    }

    public function show(Category $category)
    {
        return view('admin.categories.show', ['category' => $category, 'products' => $category->products()->orderBy('name')->paginate(10)]);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return to_route('admin.categories.show', $category)->with('success', 'Catégorie modifiée.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Cette catégorie contient des produits. Déplacez ou supprimez ces produits avant de supprimer la catégorie.');
        }
        $category->delete();

        return to_route('admin.categories.index')->with('success', 'Catégorie supprimée.');
    }
}
