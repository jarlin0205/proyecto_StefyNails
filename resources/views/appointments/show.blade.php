@extends('layouts.admin')

@section('header', 'Detalle de la Cita')

@section('content')
<div class="bg-white rounded-lg shadowoverflow-hidden p-6 max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Cita #{{ $appointment->id }}</h2>
        <div class="space-x-2">
             <a href="{{ route('admin.appointments.edit', $appointment) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition font-bold shadow-sm">
                📅 Reprogramar Cita
            </a>
            <a href="{{ route('admin.appointments.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                Volver
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Status -->
        <div>
            @php
                $statusClasses = [
                    'pending' => 'bg-yellow-200 text-yellow-900',
                    'confirmed' => 'bg-green-200 text-green-900',
                    'completed' => 'bg-blue-200 text-blue-900',
                    'cancelled' => 'bg-red-200 text-red-900',
                ];
                $class = $statusClasses[$appointment->status] ?? 'bg-gray-200 text-gray-900';
            @endphp
            <span class="inline-block px-3 py-1 font-semibold text-sm leading-tight {{ $class}} rounded-full">
                {{ ucfirst($appointment->status) }}
            </span>
        </div>

        <!-- Customer Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-b pb-4">
            <div>
                <label class="block text-sm font-medium text-gray-500">Cliente</label>
                <p class="text-lg font-semibold text-gray-900">{{ $appointment->customer_name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500">Teléfono</label>
                <p class="text-lg text-gray-900">{{ $appointment->customer_phone }}</p>
            </div>
        </div>

        <!-- Service Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-b pb-4">
            <div>
                <label class="block text-sm font-medium text-gray-500">Servicio</label>
                <p class="text-lg font-semibold text-pink-600">{{ $appointment->service->name }}</p>
            </div>
            <div>
                 <label class="block text-sm font-medium text-gray-500">Fecha y Hora</label>
                 <p class="text-lg text-gray-900">{{ $appointment->appointment_date->format('d/m/Y h:i A') }}</p>
            </div>
        </div>
        
         <!-- Location & Price -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-b pb-4">
            <div>
                <label class="block text-sm font-medium text-gray-500">Ubicación</label>
                <p class="text-gray-900">
                    @if($appointment->location == 'salon') En el Salón @else A Domicilio @endif
                </p>
            </div>
            <div>
                 <label class="block text-sm font-medium text-gray-500">Precio Ofertado</label>
                 @if($appointment->offered_price)
                    <p class="text-lg font-bold text-green-600">${{ number_format($appointment->offered_price, 0) }}</p>
                 @else
                    <p class="text-gray-500">Precio de lista</p>
                 @endif
            </div>
        </div>

        <!-- Reference Design -->
        @if($appointment->reference_image_path)
        <div class="border-b pb-4">
            <label class="block text-sm font-medium text-gray-500 mb-2">Diseño de Referencia</label>
            <div class="w-full max-w-sm rounded-lg overflow-hidden shadow-lg border-2 border-pink-100">
                 <img src="{{ $appointment->reference_image_path }}" class="w-full object-cover">
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($appointment->notes)
        <div>
            <label class="block text-sm font-medium text-gray-500">Notas Adicionales</label>
            <p class="text-gray-700 bg-gray-50 p-3 rounded">{{ $appointment->notes }}</p>
        </div>
        @endif
        
        <!-- Actions -->
        <div class="flex justify-end pt-4 space-x-3">
             <form action="{{ route('admin.appointments.destroy', $appointment) }}" method="POST" onsubmit="return confirm('¿Eliminar esta cita?');">
                 @csrf
                 @method('DELETE')
                 <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
             </form>
        </div>
    </div>
</div>
@endsection
