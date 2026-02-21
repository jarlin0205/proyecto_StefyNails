@extends('layouts.admin')

@section('header', 'Panel de Control')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-4 mb-6">
    @php
        $isAdmin = auth()->user()->isAdmin();
    @endphp

    <!-- Card: Producido (Nuevo) -->
    <div onclick="document.getElementById('modalProducido').classList.remove('hidden')" class="bg-white rounded-lg shadow p-4 border-l-4 border-pink-600 flex items-center min-w-0 cursor-pointer hover:shadow-md transition-shadow">
        <div class="flex-shrink-0 bg-pink-100 rounded-full p-2">
            <svg class="h-5 w-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="ml-3 min-w-0">
            <h2 class="text-[10px] font-semibold text-gray-400 uppercase truncate" title="Producido">Producido</h2>
            <p class="text-xl font-bold text-gray-800 truncate">${{ number_format($totalProduced, 2) }}</p>
        </div>
    </div>

    <!-- Card: Citas Pendientes -->
    <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500 hover:shadow-md transition-shadow flex items-center min-w-0">
        <div class="flex-shrink-0 bg-yellow-100 rounded-full p-2">
            <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="ml-3 min-w-0">
            <h2 class="text-[10px] font-semibold text-gray-400 uppercase truncate" title="Pendientes">Pendientes</h2>
            <p class="text-xl font-bold text-gray-800 truncate">{{ $pendingCount ?? 0 }}</p>
        </div>
    </a>

    <!-- Card: Citas Confirmadas -->
    <a href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500 hover:shadow-md transition-shadow flex items-center min-w-0">
        <div class="flex-shrink-0 bg-green-100 rounded-full p-2">
            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="ml-3 min-w-0">
            <h2 class="text-[10px] font-semibold text-gray-400 uppercase truncate" title="Confirmadas">Confirmadas</h2>
            <p class="text-xl font-bold text-gray-800 truncate">{{ $confirmedCount ?? 0 }}</p>
        </div>
    </a>

    <!-- Card: Citas Completadas -->
    <a href="{{ route('admin.appointments.index', ['status' => 'completed']) }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500 hover:shadow-md transition-shadow flex items-center min-w-0">
        <div class="flex-shrink-0 bg-blue-100 rounded-full p-2">
            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="ml-3 min-w-0">
            <h2 class="text-[10px] font-semibold text-gray-400 uppercase truncate" title="Completadas">Completadas</h2>
            <p class="text-xl font-bold text-gray-800 truncate">{{ $completedCount ?? 0 }}</p>
        </div>
    </a>

    @if(auth()->user()->isAdmin())
    <!-- Card: Servicios (Admin solo) -->
    <a href="{{ route('admin.services.index') }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-pink-500 hover:shadow-md transition-shadow flex items-center min-w-0">
        <div class="flex-shrink-0 bg-pink-100 rounded-full p-2">
            <svg class="h-5 w-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <div class="ml-3 min-w-0">
            <h2 class="text-[10px] font-semibold text-gray-400 uppercase truncate" title="Servicios">Servicios</h2>
            <p class="text-xl font-bold text-gray-800 truncate">{{ $servicesCount ?? 0 }}</p>
        </div>
    </a>
    @endif

    <!-- Card: Notificaciones -->
    <a href="{{ route('admin.notifications.index') }}" class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500 hover:shadow-md transition-shadow flex items-center min-w-0">
        <div class="flex-shrink-0 bg-purple-100 rounded-full p-2">
            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>
        <div class="ml-3 min-w-0">
            <h2 class="text-[10px] font-semibold text-gray-400 uppercase truncate" title="Notificaciones">Notificaciones</h2>
            <p class="text-xl font-bold text-gray-800 truncate">{{ isset($notifications) ? $notifications->count() : 0 }}</p>
        </div>
    </a>
</div>
 
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">
        {{ auth()->user()->isAdmin() ? 'Citas Recientes (Global)' : 'Mis Próximas Citas' }}
    </h3>
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Cliente
                    </th>
                    @if(auth()->user()->isAdmin())
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Profesional
                    </th>
                    @endif
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
                    $diffForHumans = $appointment->appointment_date->locale('es')->diffForHumans();
                @endphp
                <tr class="{{ $isVeryClose ? 'bg-red-50' : ($isPastDue ? 'bg-orange-50' : '') }} transition-colors">
                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                        <div class="flex items-center">
                            @if($appointment->reference_image_path)
                                <img src="{{ $appointment->reference_image_path }}" alt="Referencia" class="h-8 w-8 rounded-full object-cover mr-2">
                            @endif
                            <div>
                                <p class="text-gray-900 font-medium">{{ $appointment->customer_name }}</p>
                                @if($isVeryClose)
                                    <span class="text-[10px] font-bold text-red-600 animate-pulse">PRÓXIMA</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    @if(auth()->user()->isAdmin())
                    <td class="px-5 py-5 border-b border-gray-200 text-sm">
                        <span class="text-xs font-medium px-2 py-1 rounded bg-gray-100 text-gray-600">
                            {{ $appointment->professional ? $appointment->professional->name : 'Sin asignar' }}
                        </span>
                    </td>
                    @endif
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
                            onclick="openAppointmentModal({{ json_encode([
                                'id' => $appointment->id,
                                'customer_name' => $appointment->customer_name,
                                'customer_phone' => $appointment->customer_phone,
                                'service_name' => $appointment->service?->name ?? 'Servicio',
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
                    <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="px-5 py-12 text-center text-gray-500">
                        No hay citas pendientes o próximas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(auth()->user()->isAdmin())
<!-- Modal de Gastos (Admin solo) -->
<div id="modalGasto" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modalGasto').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.expenses.store') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Registrar Nuevo Gasto
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                                    <input type="text" name="description" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Ej: Esmaltes nuevos, Alquiler...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Monto ($)</label>
                                    <input type="number" name="amount" step="0.01" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha</label>
                                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Guardar Gasto
                    </button>
                    <button type="button" onclick="document.getElementById('modalGasto').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal de Detalle de Producido -->
<div id="modalProducido" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modalProducido').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full sm:my-8 sm:align-middle sm:max-w-2xl">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                        {{ $isAdmin ? 'Detalle de Servicios Realizados (Global)' : 'Detalle de Mis Servicios Realizados' }}
                    </h3>
                    <button type="button" onclick="document.getElementById('modalProducido').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="mt-2 max-h-[60vh] overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio / Cliente</th>
                                @if($isAdmin)
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesional</th>
                                @endif
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($completedAppointments as $app)
                                @php
                                    $price = $app->offered_price ?? ($app->service ? $app->service->price : 0);
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                        {{ $app->appointment_date->format('d/m/y') }}<br>
                                        {{ $app->appointment_date->format('h:i A') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ $app->service?->name ?? 'Servicio individual' }}</div>
                                        <div class="text-xs text-gray-500">{{ $app->customer_name }}</div>
                                    </td>
                                    @if($isAdmin)
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $app->professional?->name ?? 'Sin asignar' }}
                                    </td>
                                    @endif
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                        ${{ number_format($price, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 4 : 3 }}" class="px-4 py-8 text-center text-gray-500 text-sm">
                                        No hay servicios completados registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-between items-center border-t pt-4">
                    <div class="text-gray-600 text-sm italic">
                        * Incluye servicios completados y precios ofertados.
                    </div>
                    <div class="text-right">
                        <span class="text-gray-500 text-sm font-medium uppercase mr-2">{{ $isAdmin ? 'Total Global:' : 'Mi Total:' }}</span>
                        <span class="text-2xl font-black text-pink-600">${{ number_format($totalProduced, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="document.getElementById('modalProducido').classList.add('hidden')" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:w-auto sm:text-sm">
                    Cerrar Detalle
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
