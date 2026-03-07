@extends('layouts.admin')

@section('header', 'Notificaciones')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Bandeja de Entrada</h2>
    @if($notifications->count() > 0)
        <form action="{{ route('admin.notifications.deleteAll') }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres borrar todo el historial de notificaciones? Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex items-center space-x-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors border border-red-200">
                <i class="fas fa-trash-alt"></i>
                <span class="font-bold text-sm">Borrar Todo el Historial</span>
            </button>
        </form>
    @endif
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Mensaje
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Fecha
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <div class="flex justify-between items-start">
                                <div>
                                    @if($notification->action_url)
                                        <a href="{{ $notification->action_url }}" class="flex items-start p-2 rounded hover:bg-gray-50 transition border border-transparent hover:border-pink-100">
                                            @if($notification->type === 'warning' || $notification->product_id)
                                                <div class="flex-shrink-0 bg-yellow-100 p-2 rounded-full mr-3 text-yellow-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="flex-shrink-0 bg-pink-100 p-2 rounded-full mr-3 text-pink-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="text-pink-600 font-black text-sm uppercase tracking-tight">{{ $notification->title }}</p>
                                                <p class="text-gray-600 text-xs">{{ $notification->message }}</p>
                                            </div>
                                        </a>
                                    @else
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 bg-gray-100 p-2 rounded-full mr-3 text-gray-400">
                                                <i class="fas fa-bell"></i>
                                            </div>
                                            <div>
                                                <p class="text-gray-900 font-semibold">{{ $notification->title }}</p>
                                                <p class="text-gray-600 text-xs">{{ $notification->message }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                         <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                            No hay notificaciones.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-5 bg-white border-t">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
