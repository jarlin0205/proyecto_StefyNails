@extends('layouts.admin')

@section('title', 'Ventas')

@section('header', 'Módulo de Ventas')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div class="p-6 bg-gradient-to-r from-pink-600 to-rose-600">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white uppercase tracking-wider">Historial de Ventas</h1>
                    <p class="text-black text-sm mt-1 font-bold">Monitoreo de ingresos por productos (POS y Servicios)</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.pos') }}" class="bg-white text-pink-600 hover:bg-pink-50 font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center space-x-2 active:scale-95">
                        <i class="fas fa-plus mr-1"></i>
                        <span>NUEVA VENTA</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="p-4 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
            <form action="{{ route('admin.sales.index') }}" method="GET" class="flex items-center gap-2">
                <label for="type" class="text-xs font-black text-gray-400 uppercase tracking-widest">Filtrar por:</label>
                <select name="type" id="type" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 py-1.5 pl-3 pr-10">
                    <option value="all" {{ $currentType === 'all' ? 'selected' : '' }}>Todas las Ventas</option>
                    <option value="pos" {{ $currentType === 'pos' ? 'selected' : '' }}>Ventas POS (Sin cita)</option>
                    <option value="appointment" {{ $currentType === 'appointment' ? 'selected' : '' }}>Ventas en Citas (Servicios)</option>
                </select>
            </form>
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter bg-white px-3 py-1.5 rounded-full border border-gray-100">
                Mostrando {{ $sales->count() }} de {{ $sales->total() }} registros
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">ID</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Tipo</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Cliente / Info</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Detalle Productos</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Pago</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Monto Prod.</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-pink-50/20 transition-colors group">
                        <td class="px-6 py-4 text-center">
                            <span class="text-gray-400 font-mono text-[10px]">#{{ $sale->type === 'pos' ? 'P' : 'C' }}-{{ str_pad($sale->original_id, 4, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-[9px] font-black uppercase tracking-tighter {{ $sale->type === 'pos' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $sale->type_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 text-sm">{{ $sale->customer }}</span>
                                @if(isset($sale->service))
                                    <span class="text-[9px] text-pink-500 font-black uppercase tracking-tight">Servicio: {{ $sale->service }}</span>
                                @endif
                                @if($sale->phone)
                                    <span class="text-[10px] text-gray-400 flex items-center mt-0.5">
                                        <i class="fas fa-phone-alt mr-1 text-[8px]"></i> {{ $sale->phone }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="max-h-24 overflow-y-auto scrollbar-hide">
                                <ul class="space-y-1">
                                    @foreach($sale->items as $item)
                                    <li class="text-[11px] text-gray-600 flex items-center">
                                        <span class="font-black text-pink-500 mr-2">{{ $item->quantity }}x</span>
                                        <span class="truncate max-w-[140px]">{{ $item->name }}</span>
                                        <span class="ml-auto text-[10px] text-gray-400 font-medium">${{ number_format($item->subtotal, 2) }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $badge = [
                                    'cash' => ['label' => 'EFECTIVO', 'class' => 'bg-green-100 text-green-700'],
                                    'transfer' => ['label' => 'TRANSFER', 'class' => 'bg-cyan-100 text-cyan-700'],
                                    'hybrid' => ['label' => 'HÍBRIDO', 'class' => 'bg-orange-100 text-orange-700'],
                                ][$sale->payment_method] ?? ['label' => $sale->payment_method ?: 'N/A', 'class' => 'bg-gray-100'];
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter {{ $badge['class'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-base font-black {{ $sale->type === 'pos' ? 'text-gray-900' : 'text-rose-600' }}">${{ number_format($sale->total, 2) }}</span>
                            @if($sale->type === 'appointment')
                                <p class="text-[8px] text-gray-400 uppercase font-bold tracking-tighter">Productos adicionales</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <span class="text-[10px] font-bold text-gray-500 block">{{ $sale->date->format('d/m/Y') }}</span>
                            <span class="text-[9px] text-gray-300">{{ $sale->date->format('h:i A') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <i class="fas fa-receipt text-5xl mb-4 text-gray-400"></i>
                                <p class="text-xl font-bold">No se encontraron ventas para este filtro</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($sales->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
