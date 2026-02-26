@extends('layouts.admin')

@section('header', 'Editar Categoría de Producto')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.product-categories.update', $productCategory) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Categoría</label>
                <input type="text" name="name" id="name" value="{{ old('name', $productCategory->name) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-pink-500 focus:ring-pink-500" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.product-categories.index') }}" class="px-4 py-2 text-gray-700 font-medium hover:text-gray-900 transition">Cancelar</a>
                <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg font-bold shadow hover:bg-pink-700 transition">Actualizar Categoría</button>
            </div>
        </form>
    </div>
</div>
@endsection
