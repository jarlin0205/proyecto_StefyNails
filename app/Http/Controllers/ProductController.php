<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::all();
        $query = Product::with('category');
        if ($request->filled('category')) {
            $query->where('product_category_id', $request->category);
        }
        $products = $query->latest()->get();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = ProductCategory::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric|min:0',
            'stock'               => 'nullable|integer|min:0',
            'image'               => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['stock'] = $validated['stock'] ?? 0;
        Product::create($validated);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'price'               => 'required|numeric|min:0',
            'stock'               => 'nullable|integer|min:0',
            'image'               => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['stock'] = $validated['stock'] ?? 0;
        $product->update($validated);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    /**
     * JSON endpoint used by the appointment payment modal (admin + employees).
     */
    public function list()
    {
        $products = Product::with('category')
                           ->select('id', 'name', 'price', 'stock', 'product_category_id')
                           ->orderBy('name')
                           ->get()
                           ->map(fn ($p) => [
                               'id'       => $p->id,
                               'name'     => $p->name,
                               'price'    => (float) $p->price,
                               'stock'    => $p->stock,
                               'category' => $p->category->name ?? '',
                           ]);

        return response()->json($products);
    }
}
