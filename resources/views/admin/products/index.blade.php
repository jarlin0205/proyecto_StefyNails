@extends('layouts.admin')

@section('header', 'Catálogo de Productos')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <a href="{{ route('admin.products.create') }}" class="bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold shadow hover:bg-pink-700 transition inline-flex items-center w-fit">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo Producto
    </a>

    <form action="{{ route('admin.products.index') }}" method="GET" class="flex items-center gap-2">
        <select name="category" class="border-gray-300 rounded-lg text-sm focus:ring-pink-500 focus:border-pink-500" onchange="this.form.submit()">
            <option value="">Todas las categorías</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @if(request('category'))
            <a href="{{ route('admin.products.index') }}" class="text-xs text-gray-500 hover:text-pink-600">Limpiar</a>
        @endif
    </form>
</div>

@if($products->isEmpty())
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay productos registrados</h3>
        <p class="text-gray-500">Agrega productos que vendas en tu local para incluirlos en las citas y facturas.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            <div class="bg-white rounded-lg shadow overflow-hidden group">
                @if($product->image)
                    <div class="h-48 overflow-hidden relative">
                        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="{{ $product->name }}">
                        <div class="absolute top-2 right-2 bg-pink-600 text-white text-[10px] font-bold px-2 py-1 rounded shadow uppercase tracking-wider">
                            ${{ number_format($product->price, 0, ',', '.') }}
                        </div>
                    </div>
                @else
                    <div class="h-48 bg-gray-100 flex items-center justify-center text-gray-400 relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div class="absolute top-2 right-2 bg-pink-600 text-white text-[10px] font-bold px-2 py-1 rounded shadow uppercase tracking-wider">
                            ${{ number_format($product->price, 0, ',', '.') }}
                        </div>
                    </div>
                @endif

                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-[10px] font-bold text-pink-500 uppercase tracking-widest block">{{ $product->category->name }}</span>
                            <h3 class="text-lg font-bold text-gray-800 leading-tight">{{ $product->name }}</h3>
                        </div>
                        <div class="flex space-x-1">
                            <a href="{{ route('admin.products.edit', $product) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Eiminar este producto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($product->description)
                        <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ $product->description }}</p>
                    @endif

                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="flex-1">
                            <div class="flex items-center justify-between text-[10px] text-gray-500 mb-1">
                                <span>Costo: <span class="text-gray-800 font-bold">${{ number_format($product->purchase_price ?? 0, 0, ',', '.') }}</span></span>
                                <span>Ganancia: <span class="text-green-600 font-bold">${{ number_format(($product->price - ($product->purchase_price ?? 0)), 0, ',', '.') }}</span></span>
                            </div>
                            <div class="flex items-center text-[10px] text-gray-500 font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Stock: <span class="ml-1 {{ $product->stock <= 2 ? 'text-red-600' : 'text-gray-800' }}">{{ $product->stock }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
