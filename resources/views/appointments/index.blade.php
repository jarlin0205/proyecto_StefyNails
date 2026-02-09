@extends('layouts.admin')

@section('header', 'Listado de Citas')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">Listado de Citas</h2>
    <button onclick="openCreateModal()" class="bg-pink-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-pink-700 transition shadow-sm">
        + Agendar Nueva Cita
    </button>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">
            @if(request('status'))
                Citas: {{ ucfirst(request('status')) }}
            @else
                Listado de Citas
            @endif
        </h3>
        @if(request('status'))
            <a href="{{ route('admin.appointments.index') }}" class="text-pink-600 hover:text-pink-800 text-sm font-medium flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Quitar filtro (Ver todos)
            </a>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Fecha / Hora
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Cliente
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Servicio
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Estado
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Acción
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr class="hover:bg-pink-50 transition cursor-pointer" 
                        onclick="openAppointmentModalWrapper({{ json_encode([
                            'id' => $appointment->id,
                            'customer_name' => $appointment->customer_name,
                            'customer_phone' => $appointment->customer_phone,
                            'service_id' => $appointment->service_id,
                            'service_name' => $appointment->service->name,
                            'date' => $appointment->appointment_date->format('d/m/Y h:i A'),
                            'date_raw' => $appointment->appointment_date->format('Y-m-d H:i'),
                            'status' => $appointment->status,
                            'price' => $appointment->offered_price ?? ($appointment->service ? $appointment->service->price : 0),
                            'image' => $appointment->reference_image_path,
                            'notes' => $appointment->notes,
                            'edit_url' => route('admin.appointments.edit', $appointment),
                            'status_url' => route('admin.appointments.updateStatus', $appointment),
                            'delete_url' => route('admin.appointments.destroy', $appointment)
                        ]) }})">
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap font-medium">
                                {{ $appointment->appointment_date->format('d/m/Y h:i A') }}
                            </p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap font-bold">
                                {{ $appointment->customer_name }}
                            </p>
                            <p class="text-gray-500 text-xs">{{ $appointment->customer_phone }}</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $appointment->service->name }}</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
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
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
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
                                class="text-pink-600 hover:text-pink-800 font-bold transition-colors">
                                Ver Detalle
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                            No hay citas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-5 bg-white border-t">
        {{ $appointments->links() }}
    </div>
</div>

@endsection

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

