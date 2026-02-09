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
            'image' => 'nullable|image|max:20480', // Main image
            'gallery.*' => 'image|max:20480', // Gallery images
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
            'image' => 'nullable|image|max:20480',
            'gallery.*' => 'image|max:20480',
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
        if ($image->image_path) {
            $path = str_replace('storage/', '', $image->image_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
        
        $image->delete();
        return back()->with('success', 'Imagen eliminada de la galería.');
    }

    public function destroyMainImage(Service $service)
    {
        if ($service->image_path) {
            $path = str_replace('storage/', '', $service->image_path);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            
            $service->update(['image_path' => null]);
            return back()->with('success', 'Imagen principal eliminada.');
        }

        return back()->with('error', 'No hay imagen principal para eliminar.');
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
