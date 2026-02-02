<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Ya no marcamos todo como leído automáticamente para que la campana 
        // sirva de recordatorio mientras haya citas pendientes.
        // Notification::where('is_read', false)->update(['is_read' => true]);

        $notifications = Notification::with('appointment')->latest()->paginate(10);
        return view('notifications.index', compact('notifications'));
    }
}
