<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Base query for notifications: Generic alerts (stock, etc) OR any Appointment alert
        $query = Notification::query();

        // Filter by professional if it's an employee
        if ($user->role === 'employee' && $user->professional) {
            $query->whereHas('appointment', function($q) use ($user) {
                $q->where('professional_id', $user->professional->id);
            });
        }

        // Mark only visible unread notifications as read to reset the bell for this user
        (clone $query)->where('is_read', false)->update(['is_read' => true]);

        // Paginate the filtered notifications
        $notifications = $query->with('appointment.service', 'appointment.professional', 'product')
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Delete all notifications for the current user.
     */
    public function deleteAll()
    {
        $user = auth()->user();
        $query = Notification::query();

        if ($user->role === 'employee' && $user->professional) {
            $query->whereHas('appointment', function($q) use ($user) {
                $q->where('professional_id', $user->professional->id);
            });
        }

        $query->delete();

        return back()->with('success', 'Historial de notificaciones vaciado correctamente.');
    }
}
