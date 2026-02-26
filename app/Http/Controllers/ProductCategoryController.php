<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')->latest()->get();
        return view('admin.product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.product-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:product_categories,name']);
        ProductCategory::create($request->only('name'));
        return redirect()->route('admin.product-categories.index')
                         ->with('success', 'Categoría de producto creada.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('admin.product-categories.edit', compact('productCategory'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate(['name' => 'required|string|max:255|unique:product_categories,name,' . $productCategory->id]);
        $productCategory->update($request->only('name'));
        return redirect()->route('admin.product-categories.index')
                         ->with('success', 'Categoría actualizada.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->products()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría con productos.');
        }
        $productCategory->delete();
        return back()->with('success', 'Categoría eliminada.');
    }
}
