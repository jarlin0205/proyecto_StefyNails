<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Base query for notifications that require attention (pending)
        $query = Notification::whereHas('appointment', function($q) {
            $q->whereIn('status', ['pending_admin', 'pending_client']);
        });

        // Filter by professional if it's an employee
        if ($user->role === 'employee' && $user->professional) {
            $query->whereHas('appointment', function($q) use ($user) {
                $q->where('professional_id', $user->professional->id);
            });
        }

        // Mark only visible unread notifications as read to reset the bell for this user
        (clone $query)->where('is_read', false)->update(['is_read' => true]);

        // Paginate the filtered notifications
        $notifications = $query->with('appointment.service', 'appointment.professional')
            ->latest()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }
}
