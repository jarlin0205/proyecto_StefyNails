<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::with('category')->latest()->get();
        return view('admin.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.services.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price_display' => 'required|string',
            'price' => 'nullable|numeric',
            'duration' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // Main image
            'gallery.*' => 'image|max:2048', // Gallery images
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $validated['image_path'] = 'storage/' . $path;
        }

        $service = Service::create($validated);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $path = $image->store('services', 'public');
                $service->images()->create([
                    'image_path' => 'storage/' . $path
                ]);
            }
        }

        return redirect()->route('admin.services.index')->with('success', 'Servicio creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        $categories = Category::all();
        $service->load('images');
        return view('admin.services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price_display' => 'required|string',
            'price' => 'nullable|numeric',
            'duration' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'gallery.*' => 'image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old main image if exists ? (Optional, let's keep it simple for now or overwrite)
            $path = $request->file('image')->store('services', 'public');
            $validated['image_path'] = 'storage/' . $path;
        }

        $service->update($validated);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $path = $image->store('services', 'public');
                $service->images()->create([
                    'image_path' => 'storage/' . $path
                ]);
            }
        }

        return redirect()->route('admin.services.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroyImage(\App\Models\ServiceImage $image)
    {
        // Optional: Delete file from storage
        // Storage::disk('public')->delete(str_replace('storage/', '', $image->image_path));
        
        $image->delete();
        return back()->with('success', 'Imagen eliminada de la galería.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Servicio eliminado correctamente.');
    }
}
