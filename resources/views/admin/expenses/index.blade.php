@extends('layouts.admin')

@section('header', 'Gestión de Gastos')

@section('content')
<!-- Resumen Financiero -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Ingresos Brutos -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-green-50 p-3 rounded-lg">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded">Ingresos</span>
        </div>
        <h4 class="text-gray-500 text-sm font-medium">Ingresos Brutos</h4>
        <p class="text-2xl font-bold text-gray-800">${{ number_format($grossRevenue, 0, '', '') }}</p>
    </div>

    <!-- Gastos -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-red-50 p-3 rounded-lg">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded">Egresos</span>
        </div>
        <h4 class="text-gray-500 text-sm font-medium">Gastos Totales</h4>
        <p class="text-2xl font-bold text-gray-800">${{ number_format($totalExpenses, 0, '', '') }}</p>
    </div>

    <!-- Ganancia Neta -->
    <div class="bg-white rounded-xl shadow-sm p-6 border-2 border-pink-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-2">
            <div class="bg-pink-50 p-2 rounded-full">
                <svg class="h-4 w-4 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <h4 class="text-gray-500 text-sm font-medium">Ganancia Real (Neta)</h4>
        <p class="text-2xl font-bold text-pink-600">${{ number_format($netProfit, 0, '', '') }}</p>
        <div class="mt-2 flex items-center">
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-pink-500 h-1.5 rounded-full" style="width: {{ $grossRevenue > 0 ? ($netProfit / $grossRevenue) * 100 : 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Proyectado -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-blue-50 p-3 rounded-lg">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded">Futuro</span>
        </div>
        <h4 class="text-gray-500 text-sm font-medium">Proyectado (Confirmado)</h4>
        <p class="text-2xl font-bold text-gray-800">${{ number_format($projectedRevenue, 0, '', '') }}</p>
    </div>
</div>

<!-- Filtros y Acciones -->
<div class="mb-8 p-6 bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <form action="{{ route('admin.expenses.index') }}" method="GET" class="flex-grow">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-[10px] font-black text-gray-400 uppercase mb-1 tracking-wider">Desde</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full rounded-lg border-gray-200 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-200 focus:ring-opacity-50 text-sm font-medium">
                </div>
                <div>
                    <label for="end_date" class="block text-[10px] font-black text-gray-400 uppercase mb-1 tracking-wider">Hasta</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full rounded-lg border-gray-200 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-200 focus:ring-opacity-50 text-sm font-medium">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-grow bg-gray-900 text-white px-4 py-2 rounded-lg font-bold hover:bg-gray-800 transition-colors shadow-sm text-sm uppercase tracking-wider">
                        Filtrar
                    </button>
                    <a href="{{ route('admin.expenses.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200 transition-colors text-sm uppercase flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </a>
                </div>
            </div>
            
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" onclick="setExpensesPreset('today')" class="px-3 py-1 text-[10px] font-bold bg-pink-50 text-pink-700 rounded-full hover:bg-pink-100 transition-colors uppercase">Hoy</button>
                <button type="button" onclick="setExpensesPreset('week')" class="px-3 py-1 text-[10px] font-bold bg-pink-50 text-pink-700 rounded-full hover:bg-pink-100 transition-colors uppercase">Esta Semana</button>
                <button type="button" onclick="setExpensesPreset('month')" class="px-3 py-1 text-[10px] font-bold bg-pink-50 text-pink-700 rounded-full hover:bg-pink-100 transition-colors uppercase">Este Mes</button>
            </div>
        </form>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('admin.expenses.export', request()->all()) }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg font-bold hover:bg-gray-200 transition-colors flex items-center justify-center shadow-sm text-sm uppercase tracking-wider border border-gray-200">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Reporte (PDF)
            </a>
            <button onclick="document.getElementById('modalGasto').classList.remove('hidden')" class="bg-pink-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-pink-700 transition-colors flex items-center justify-center shadow-sm text-sm uppercase tracking-wider">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                + Registrar Gasto
            </button>
        </div>
    </div>
</div>

<div class="mb-6">
    <h3 class="text-xl font-bold text-gray-800 flex items-center">
        <span class="w-2 h-8 bg-pink-600 rounded-full mr-3"></span>
        Listado Detallado de Movimientos
    </h3>
</div>

<script>
function setExpensesPreset(type) {
    const today = new Date();
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    
    let start = new Date();
    let end = new Date();

    if (type === 'today') {
        // Hoy
    } else if (type === 'week') {
        const day = today.getDay();
        const diff = today.getDate() - day + (day === 0 ? -6 : 1);
        start = new Date(today.setDate(diff));
        end = new Date();
    } else if (type === 'month') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
        end = new Date();
    }

    startInput.value = start.toISOString().split('T')[0];
    endInput.value = end.toISOString().split('T')[0];
    
    startInput.closest('form').submit();
}
</script>


@if(session('success'))
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
    <p class="font-bold">¡Éxito!</p>
    <p>{{ session('success') }}</p>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
    <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Monto</th>
                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-4 border-b border-gray-200 text-sm italic text-gray-500">
                    {{ $expense->date->format('d/m/Y') }}
                </td>
                <td class="px-5 py-4 border-b border-gray-200 text-sm font-medium text-gray-900">
                    {{ $expense->description }}
                </td>
                <td class="px-5 py-4 border-b border-gray-200 text-sm text-right font-bold text-red-600">
                    ${{ number_format($expense->amount, 0, '', '') }}
                </td>
                <td class="px-5 py-4 border-b border-gray-200 text-sm text-center">
                    <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este gasto?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-10 border-b border-gray-200 bg-white text-sm text-center text-gray-400">
                    No hay gastos registrados aún.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
        {{ $expenses->links() }}
    </div>
</div>

@include('partials.expense_modal')
@endsection
