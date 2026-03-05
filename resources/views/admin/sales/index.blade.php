@extends('layouts.admin')

@section('title', 'Historial de Ventas POS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div class="p-6 bg-gradient-to-r from-pink-500 to-rose-500 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black text-white uppercase tracking-wider">Historial de Ventas POS</h1>
                <p class="text-pink-100 text-sm mt-1">Registro de ventas directas de productos</p>
            </div>
            <a href="{{ route('admin.pos') }}" class="bg-white text-pink-600 hover:bg-pink-50 font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center space-x-2 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>NUEVA VENTA</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">ID</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Cliente</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Detalle Productos</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Método</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Total</th>
                        <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-pink-50/30 transition-colors group">
                        <td class="px-6 py-4 text-center">
                            <span class="text-gray-400 font-mono text-xs">#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800">{{ $sale->customer_name ?: 'Venta Mostrador' }}</span>
                                @if($sale->customer_phone)
                                    <span class="text-[10px] text-gray-400 flex items-center mt-0.5">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2"/></svg>
                                        {{ $sale->customer_phone }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <ul class="space-y-1">
                                @foreach($sale->items as $item)
                                <li class="text-xs text-gray-600 flex items-center">
                                    <span class="font-black text-pink-500 mr-2">{{ $item->quantity }}x</span>
                                    <span class="truncate max-w-[200px]">{{ $item->product->name }}</span>
                                    <span class="ml-auto text-[10px] text-gray-400 font-medium">${{ number_format($item->subtotal, 2) }}</span>
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
                            <span class="px-2.5 py-1 rounded text-[9px] font-black uppercase tracking-tighter {{ $badge['class'] }}">
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
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-20">
                                <svg class="w-20 h-20 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2"/></svg>
                                <p class="text-xl font-bold">No hay ventas registradas aún</p>
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
