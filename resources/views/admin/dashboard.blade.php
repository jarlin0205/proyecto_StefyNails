@extends('layouts.admin')

@section('header', 'Panel de Control')

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
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Estado
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Acción
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestsAppointments ?? [] as $appointment)
                @php
                    $isVeryClose = $appointment->appointment_date->isFuture() && $appointment->appointment_date->diffInMinutes(now()) <= 30;
                    $isPastDue = $appointment->appointment_date->isPast() && in_array($appointment->status, ['pending_admin', 'pending_client', 'confirmed']);
                    $diffForHumans = $appointment->appointment_date->diffForHumans();
                @endphp
                <tr class="{{ $isVeryClose ? 'bg-red-50' : ($isPastDue ? 'bg-orange-50' : '') }} transition-colors">
                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                        <div class="flex items-center">
                            <div class="ml-3">
                                <p class="text-gray-900 font-medium">
                                    {{ $appointment->customer_name }}
                                </p>
                                @if($isVeryClose)
                                    <span class="flex items-center text-[10px] font-bold text-red-600 animate-pulse">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        PRÓXIMA
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                         @if($appointment->reference_image_path)
                            <a href="{{ $appointment->reference_image_path }}" target="_blank">
                                <img src="{{ $appointment->reference_image_path }}" alt="Referencia" class="h-10 w-10 rounded-full object-cover border-2 border-pink-200 hover:scale-150 transition-transform">
                            </a>
                        @else
                            <span class="text-gray-400 text-xs">N/A</span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                        <p class="text-gray-900 whitespace-no-wrap">{{ $appointment->service->name }}</p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                        <div class="flex flex-col">
                            <span class="text-gray-900 font-medium">
                                {{ $appointment->appointment_date->format('d/m/Y h:i A') }}
                            </span>
                            <span class="text-xs {{ $isPastDue ? 'text-red-500 font-semibold' : 'text-gray-500' }}">
                                {{ $diffForHumans }}
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 text-sm text-center">
                        @php
                            $statusColors = [
                                'pending_admin' => 'bg-orange-100 text-orange-800',
                                'pending_client' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-green-100 text-green-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            $label = [
                                'pending_admin' => 'Esperando Admin',
                                'pending_client' => 'Esperando Cliente',
                                'confirmed' => 'Confirmada',
                                'completed' => 'Completada',
                                'cancelled' => 'Cancelada',
                            ][$appointment->status] ?? $appointment->status;
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusColors[$appointment->status] ?? 'bg-gray-100' }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 text-sm text-center">
                        <button 
                            onclick="openAppointmentModalWrapper({{ json_encode([
                                'id' => $appointment->id,
                                'customer_name' => $appointment->customer_name,
                                'customer_phone' => $appointment->customer_phone,
                                'service_name' => $appointment->service->name,
                                'date' => $appointment->appointment_date->format('d/m/Y h:i A'),
                                'status' => $appointment->status,
                                'price' => $appointment->offered_price ?? ($appointment->service ? $appointment->service->price : 0),
                                'image' => $appointment->reference_image_path,
                                'notes' => $appointment->notes,
                                'edit_url' => route('admin.appointments.edit', $appointment),
                                'status_url' => route('admin.appointments.updateStatus', $appointment),
                                'delete_url' => route('admin.appointments.destroy', $appointment)
                            ]) }})"
                            class="text-pink-600 hover:text-pink-800 font-bold">
                            Ver Detalle
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                        No hay citas pendientes o próximas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@push('scripts')
<script>
    function openAppointmentModalWrapper(data) {
        if (typeof openAppointmentModal === 'function') {
            openAppointmentModal(data);
        } else {
            console.error('La función global openAppointmentModal no está definida.');
        }
    }
</script>
@endpush
@endsection
