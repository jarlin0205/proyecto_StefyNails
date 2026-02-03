@extends('layouts.admin')

@section('header', 'Categorías')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.categories.create') }}" class="bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold shadow hover:bg-pink-700 transition inline-flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nueva Categoría
    </a>
</div>

@if($categories->isEmpty())
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay categorías registradas</h3>
        <p class="text-gray-500 mb-6">Comienza creando tu primera categoría para organizar tus servicios.</p>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-pink-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Crear Primera Categoría
        </a>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($categories as $category)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->services_count }} {{ $category->services_count == 1 ? 'servicio' : 'servicios' }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-600 hover:text-blue-800 p-2 rounded hover:bg-blue-50 transition" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 p-2 rounded hover:bg-red-50 transition" title="Eliminar" {{ $category->services_count > 0 ? 'disabled' : '' }}>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                
                @if($category->description)
                    <p class="text-gray-600 text-sm mb-4">{{ $category->description }}</p>
                @else
                    <p class="text-gray-400 text-sm italic mb-4">Sin descripción</p>
                @endif

                <div class="pt-4 border-t border-gray-100">
                    <span class="inline-block bg-pink-100 text-pink-800 text-xs px-3 py-1 rounded-full font-medium">
                        {{ $category->slug }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
