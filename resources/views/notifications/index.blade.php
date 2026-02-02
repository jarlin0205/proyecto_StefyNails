@extends('layouts.admin')

@section('header', 'Notificaciones')

@section('content')
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
                                        <a href="{{ $notification->action_url }}" class="block hover:bg-gray-50 transition">
                                            <p class="text-pink-600 font-bold whitespace-no-wrap hover:underline">{{ $notification->title }}</p>
                                            <p class="text-gray-600">{{ $notification->message }}</p>
                                        </a>
                                    @else
                                        <p class="text-gray-900 whitespace-no-wrap font-semibold">{{ $notification->title }}</p>
                                        <p class="text-gray-600">{{ $notification->message }}</p>
                                    @endif
                                </div>

                                @if($notification->appointment_id && $notification->appointment && ($notification->appointment->status === 'pending' || $notification->appointment->status === 'confirmed'))
                                    <div class="flex space-x-2 ml-4">
                                        <form action="{{ route('admin.appointments.updateStatus', $notification->appointment_id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="confirmed">
                                            <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                            <button type="submit" class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold hover:bg-green-200 transition border border-green-200 shadow-sm">
                                                Confirmar
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('admin.appointments.updateStatus', $notification->appointment_id) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que quieres rechazar esta cita? Se enviará el mensaje amable automáticamente.')">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                            <button type="submit" class="bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold hover:bg-red-100 transition border border-red-100 shadow-sm">
                                                Rechazar
                                            </button>
                                        </form>
                                    </div>
                                @endif
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
