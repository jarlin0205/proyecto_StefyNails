<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Marcamos todo como leído al entrar para resetear el contador de la campana
        Notification::where('is_read', false)->update(['is_read' => true]);

        // Mostramos solo notificaciones de citas que requieren atención (pendientes)
        $notifications = Notification::whereHas('appointment', function($query) {
            $query->whereIn('status', ['pending_admin', 'pending_client']);
        })->with('appointment')->latest()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }
}
