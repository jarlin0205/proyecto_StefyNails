@extends('layouts.admin')

@section('title', 'Ventas')

@section('header', 'Módulo de Ventas')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100" x-data="{ activeTab: 'pos' }">
        <div class="p-6 bg-gradient-to-r from-pink-600 to-rose-600">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-black text-white uppercase tracking-wider">Historial de Ventas</h1>
                    <p class="text-pink-100 text-sm mt-1">Monitoreo de ingresos por productos</p>
                </div>
                <div class="flex items-center space-x-2 bg-pink-800/30 p-1 rounded-xl border border-white/10">
                    <button @click="activeTab = 'pos'" :class="activeTab === 'pos' ? 'bg-white text-pink-600 shadow-sm' : 'text-white hover:bg-white/10'" class="px-6 py-2 rounded-lg font-bold text-xs uppercase transition-all">
                        VENTAS POS
                    </button>
                    <button @click="activeTab = 'appointments'" :class="activeTab === 'appointments' ? 'bg-white text-pink-600 shadow-sm' : 'text-white hover:bg-white/10'" class="px-6 py-2 rounded-lg font-bold text-xs uppercase transition-all">
                        VENTAS EN CITAS
                    </button>
                </div>
                <a href="{{ route('admin.pos') }}" class="bg-white text-pink-600 hover:bg-pink-50 font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center space-x-2 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>NUEVA VENTA</span>
                </a>
            </div>
        </div>

        <!-- TAB: VENTAS POS -->
        <div x-show="activeTab === 'pos'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">ID</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Cliente / Info</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Detalle Productos</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Método</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Total</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($posSales as $sale)
                        <tr class="hover:bg-pink-50/30 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-400 font-mono text-xs">#P-{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800">{{ $sale->customer_name ?: 'Venta Directa' }}</span>
                                    @if($sale->customer_phone)
                                        <span class="text-[10px] text-gray-400 flex items-center mt-0.5">
                                            <i class="fas fa-phone-alt mr-1 text-[8px]"></i> {{ $sale->customer_phone }}
                                        </span>
                                    @endif
                                    <span class="text-[9px] text-pink-400 font-bold mt-1 uppercase tracking-tighter">Venta de Mostrador</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <ul class="space-y-1">
                                    @foreach($sale->items as $item)
                                    <li class="text-[11px] text-gray-600 flex items-center">
                                        <span class="font-black text-pink-500 mr-2">{{ $item->quantity }}x</span>
                                        <span class="truncate max-w-[150px]">{{ $item->product->name }}</span>
                                        <span class="ml-auto text-gray-400 font-medium">${{ number_format($item->subtotal, 2) }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badge = [
                                        'cash' => ['label' => 'EFECTIVO', 'class' => 'bg-green-100 text-green-700'],
                                        'transfer' => ['label' => 'TRANSFER', 'class' => 'bg-blue-100 text-blue-700'],
                                        'hybrid' => ['label' => 'HÍBRIDO', 'class' => 'bg-purple-100 text-purple-700'],
                                    ][$sale->payment_method] ?? ['label' => $sale->payment_method, 'class' => 'bg-gray-100'];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-base font-black text-gray-900">${{ number_format($sale->total, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-[10px] font-bold text-gray-400 block">{{ $sale->created_at->format('d/m/Y') }}</span>
                                <span class="text-[9px] text-gray-300">{{ $sale->created_at->format('h:i A') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-20 text-center text-gray-400 italic">No hay ventas POS registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($posSales->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $posSales->appends(['active_tab' => 'pos'])->links() }}
                </div>
            @endif
        </div>

        <!-- TAB: VENTAS EN CITAS -->
        <div x-show="activeTab === 'appointments'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Cita ID</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Cliente / Servicio</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Productos Adicionales</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Método</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Venta Prod.</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($appointmentSales as $app)
                        <tr class="hover:bg-pink-50/30 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <span class="text-gray-400 font-mono text-xs">#C-{{ str_pad($app->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800">{{ $app->customer_name }}</span>
                                    <span class="text-[10px] text-pink-500 font-bold uppercase tracking-tight">
                                        Serv: {{ $app->service ? $app->service->name : 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <ul class="space-y-1">
                                    @foreach($app->products as $product)
                                    <li class="text-[11px] text-gray-600 flex items-center">
                                        <span class="font-black text-rose-500 mr-2">{{ $product->pivot->quantity }}x</span>
                                        <span class="truncate max-w-[150px]">{{ $product->name }}</span>
                                        <span class="ml-auto text-gray-400 font-medium">${{ number_format($product->pivot->unit_price * $product->pivot->quantity, 2) }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badge = [
                                        'cash' => ['label' => 'EFECTIVO', 'class' => 'bg-green-100 text-green-700'],
                                        'transfer' => ['label' => 'TRANSFER', 'class' => 'bg-blue-100 text-blue-700'],
                                        'hybrid' => ['label' => 'HÍBRIDO', 'class' => 'bg-purple-100 text-purple-700'],
                                    ][$app->payment_method] ?? ['label' => $app->payment_method ?: 'N/A', 'class' => 'bg-gray-100'];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-base font-black text-rose-600">${{ number_format($app->products_total, 2) }}</span>
                                <p class="text-[8px] text-gray-400 uppercase font-black tracking-tighter">Solo productos</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-[10px] font-bold text-gray-400 block">{{ $app->appointment_date->format('d/m/Y') }}</span>
                                <span class="text-[9px] text-gray-300">Cita Finalizada</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-20 text-center text-gray-400 italic">No hay ventas en citas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($appointmentSales->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $appointmentSales->appends(['active_tab' => 'appointments'])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@if(request('active_tab'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Alpine data auto-initialization will happen, but we can force state if needed
        // Or just let Alpine handle it from the request if we were using a more complex setup.
        // For now, this is a simple X-DATA.
    });
</script>
@endif
@endsection
