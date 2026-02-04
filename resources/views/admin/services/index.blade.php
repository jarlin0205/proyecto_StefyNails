@extends('layouts.admin')

@section('header', 'Gestión de Servicios')

@section('content')
<div class="mb-4 text-right">
    <a href="{{ route('admin.services.create') }}" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded">
        + Nuevo Servicio
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Nombre
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Categoría
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Precio
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Duración
                </th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Acciones
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
            <tr>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <div class="flex items-center">
                        <div class="ml-3">
                            <p class="text-gray-900 whitespace-no-wrap font-semibold">
                                {{ $service->name }}
                            </p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <p class="text-gray-900 whitespace-no-wrap">{{ $service->category->name }}</p>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <p class="text-gray-900 whitespace-no-wrap">
                        {{ $service->price_display }}
                    </p>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <p class="text-gray-600 whitespace-no-wrap">
                        {{ $service->duration ?? 'N/A' }}
                    </p>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <a href="{{ route('admin.services.edit', $service) }}" class="text-blue-600 hover:text-blue-900 mr-4">Editar</a>
                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                    No hay servicios registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
