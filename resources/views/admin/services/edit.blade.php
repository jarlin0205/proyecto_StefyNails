@extends('layouts.admin')

@section('header', 'Editar Servicio')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <form action="{{ route('admin.services.update', $service) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="image">
                Imagen Principal
            </label>
            @if($service->image_path)
                <div class="mb-2">
                    <img src="{{ asset($service->image_path) }}" alt="Current Image" class="h-32 w-auto object-cover rounded shadow">
                </div>
            @endif
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="image" type="file" name="image" accept="image/*">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Galería de Imágenes
            </label>
            @if($service->images->count() > 0)
                <div class="grid grid-cols-3 gap-4 mb-3">
                    @foreach($service->images as $img)
                        <div class="relative group">
                            <img src="{{ asset($img->image_path) }}" class="h-24 w-full object-cover rounded shadow">
                            <button type="button" onclick="deleteImage({{ $img->id }})" class="absolute top-0 right-0 bg-red-600 text-white rounded-full p-1 m-1 opacity-75 hover:opacity-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
            <label class="block text-gray-600 text-xs mb-1">Agregar más fotos:</label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="gallery" type="file" name="gallery[]" multiple accept="image/*">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                Nombre del Servicio
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" type="text" name="name" value="{{ $service->name }}" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="category_id">
                Categoría
            </label>
            <select class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="category_id" name="category_id" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $service->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="price_display">
                    Precio Mostrado
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="price_display" type="text" name="price_display" value="{{ $service->price_display }}" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="price">
                    Precio Numérico
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="price" type="number" step="0.01" name="price" value="{{ $service->price }}">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="duration">
                Duración
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="duration" type="text" name="duration" value="{{ $service->duration }}">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                Descripción
            </label>
            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" rows="3">{{ $service->description }}</textarea>
        </div>

        <div class="flex items-center justify-between">
            <button class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                Actualizar Servicio
            </button>
            <a class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800" href="{{ route('admin.services.index') }}">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<form id="delete-image-form" action="" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
<script>
    function deleteImage(id) {
        if(confirm('¿Eliminar esta imagen de la galería?')) {
            let form = document.getElementById('delete-image-form');
            form.action = '/admin/services/images/' + id; // We need to define this route
            form.submit();
        }
    }
</script>
@endsection
