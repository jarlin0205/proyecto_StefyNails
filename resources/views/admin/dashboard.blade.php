@extends('layouts.admin')

@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <!-- Card: Citas Pendientes -->
    <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 rounded-full p-2">
                <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <h2 class="text-xs font-semibold text-gray-500 uppercase">Pendientes</h2>
                <p class="text-2xl font-bold text-gray-800">{{ $pendingCount ?? 0 }}</p>
            </div>
        </div>
    </a>

    <!-- Card: Citas Confirmadas -->
    <a href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-full p-2">
                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <h2 class="text-xs font-semibold text-gray-500 uppercase">Confirmadas</h2>
                <p class="text-2xl font-bold text-gray-800">{{ $confirmedCount ?? 0 }}</p>
            </div>
        </div>
    </a>

    <!-- Card: Citas Completadas -->
    <a href="{{ route('admin.appointments.index', ['status' => 'completed']) }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-full p-2">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="ml-3">
                <h2 class="text-xs font-semibold text-gray-500 uppercase">Completadas</h2>
                <p class="text-2xl font-bold text-gray-800">{{ $completedCount ?? 0 }}</p>
            </div>
        </div>
    </a>

    <!-- Card: Citas Canceladas -->
    <a href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-100 rounded-full p-2">
                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="ml-3">
                <h2 class="text-xs font-semibold text-gray-500 uppercase">Canceladas</h2>
                <p class="text-2xl font-bold text-gray-800">{{ $cancelledCount ?? 0 }}</p>
            </div>
        </div>
    </a>

    <!-- Card: Servicios -->
    <a href="{{ route('admin.services.index') }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-pink-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-pink-100 rounded-full p-2">
                <svg class="h-5 w-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div class="ml-3">
                <h2 class="text-xs font-semibold text-gray-500 uppercase">Servicios</h2>
                <p class="text-2xl font-bold text-gray-800">{{ $servicesCount ?? 0 }}</p>
            </div>
        </div>
    </a>

    <!-- Card: Notificaciones -->
    <a href="{{ route('admin.notifications.index') }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 rounded-full p-2">
                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div class="ml-3">
                <h2 class="text-xs font-semibold text-gray-500 uppercase">Notificaciones</h2>
                <p class="text-2xl font-bold text-gray-800">{{ isset($notifications) ? $notifications->count() : 0 }}</p>
            </div>
        </div>
    </a>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Citas Recientes</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Cliente
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Diseño
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Servicio
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Fecha
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Estado
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestsAppointments ?? [] as $appointment)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="flex items-center">
                            <div class="ml-3">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    {{ $appointment->customer_name }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                         @if($appointment->reference_image_path)
                            <a href="{{ $appointment->reference_image_path }}" target="_blank">
                                <img src="{{ $appointment->reference_image_path }}" alt="Referencia" class="h-10 w-10 rounded-full object-cover border-2 border-pink-200 hover:scale-150 transition-transform">
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">N/A</span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 whitespace-no-wrap">{{ $appointment->service->name }}</p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 whitespace-no-wrap">
                            {{ $appointment->appointment_date->format('d/m/Y h:i A') }}
                        </p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <span class="relative inline-block px-3 py-1 font-semibold text-yellow-900 leading-tight">
                            <span aria-hidden class="absolute inset-0 bg-yellow-200 opacity-50 rounded-full"></span>
                            <span class="relative">{{ ucfirst($appointment->status) }}</span>
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                        No hay citas recientes.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
