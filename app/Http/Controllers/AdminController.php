<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Notification;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pendingCount = Appointment::where('status', 'pending')->count();
        $confirmedCount = Appointment::where('status', 'confirmed')->count();
        $completedCount = Appointment::where('status', 'completed')->count();
        $cancelledCount = Appointment::where('status', 'cancelled')->count();
        
        $servicesCount = Service::count();
        $notifications = Notification::where('is_read', false)->get();
        
        $latestsAppointments = Appointment::with('service')
            ->whereIn('status', ['pending_admin', 'pending_client', 'confirmed'])
            ->get()
            ->sortBy(fn($appointment) => abs($appointment->appointment_date->timestamp - now()->timestamp))
            ->take(8);

        return view('admin.dashboard', compact(
            'pendingCount', 
            'confirmedCount', 
            'completedCount', 
            'cancelledCount', 
            'servicesCount', 
            'notifications', 
            'latestsAppointments'
        ));
    }
}
